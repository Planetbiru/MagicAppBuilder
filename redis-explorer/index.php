<?php

/**
 * Class RedisExplorer
 *
 * Provides utility methods for exploring Redis keys and values using the SCAN command.
 * This class supports counting keys, paginating keys, and retrieving values based on key type.
 * 
 */
class RedisExplorer
{
  /**
   * Count the total number of keys matching a pattern using SCAN.
   *
   * This method uses the Redis SCAN command to avoid blocking the server when iterating over large datasets.
   *
   * @param Redis  $redis   Redis client instance
   * @param string $pattern Pattern to match keys (e.g., 'user:*')
   * @return int            Total number of matching keys
   */
  public static function countKeysWithScan($redis, $pattern)
  {
    $count = 0;
    $it = null;

    // Iterate over keys in batches using SCAN
    while (($keys = $redis->scan($it, $pattern)) !== false) {
      $count += count($keys);
    }

    return $count;
  }

  /**
   * Get a list of keys for a specific page using SCAN.
   *
   * Useful for paginating through keys without loading all keys into memory at once.
   *
   * @param Redis  $redis   Redis client instance
   * @param string $pattern Pattern to match keys
   * @param int    $offset  Offset from the start of the result set
   * @param int    $limit   Maximum number of keys to return
   * @return array          Array of matching keys for the given page
   */
  public static function getKeysByPageWithScan($redis, $pattern, $offset, $limit)
  {
    $it = null;
    $found = array();

    // Iterate through keys in batches
    while (($keys = $redis->scan($it, $pattern)) !== false) {
      foreach ($keys as $key) {
        // Stop when we have reached the desired page range
        if (count($found) >= $offset + $limit) {
          break 2;
        }
        $found[] = $key;
      }
    }

    // Return only the keys in the requested offset/limit range
    return array_slice($found, $offset, $limit);
  }

  /**
   * Get the value of a Redis key based on its type.
   *
   * Supports STRING, LIST, SET, SORTED SET (ZSET), and HASH.
   *
   * @param Redis  $redis Redis client instance
   * @param string $key   Redis key name
   * @param int    $type  Redis key type (e.g., Redis::REDIS_STRING)
   * @return string       Formatted value as a string or JSON-encoded representation
   */
  public static function getRedisValue($redis, $key, $type)
  {
    $value = "";

    // Retrieve the value depending on the key type
    if ($type === Redis::REDIS_STRING) {
      $value = $redis->get($key);
    } elseif ($type === Redis::REDIS_LIST) {
      $value = implode(', ', $redis->lRange($key, 0, -1));
    } elseif ($type === Redis::REDIS_SET) {
      $value = implode(', ', $redis->sMembers($key));
    } elseif ($type === Redis::REDIS_ZSET) {
      $value = json_encode($redis->zRange($key, 0, -1, true));
    } elseif ($type === Redis::REDIS_HASH) {
      $value = json_encode($redis->hGetAll($key));
    } else {
      $value = '(unknown type)';
    }
    return $value;
  }
}

session_start();

// =====================
// Handle login/logout
// =====================
if (isset($_POST['logout']) || isset($_GET['logout'])) {
  unset($_SESSION['redis_config']);
  header("Location: ?");
  exit();
}

if (isset($_POST['login'])) {
  $_SESSION['redis_config'] = [
    'host'     => trim($_POST['redis_host']),
    'port'     => (int)$_POST['redis_port'],
    'password' => trim($_POST['redis_password']),
    'db'       => isset($_POST['redis_db']) ? (int)$_POST['redis_db'] : 0,
    'timeout'  => isset($_POST['redis_timeout']) ? (float)$_POST['redis_timeout'] : 5.0,
  ];

  header("Location: ?");
  exit();
}

function renderError($message)
{
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <link type="image/x-icon" rel="icon" href="../favicon.ico" />
    <link type="image/x-icon" rel="shortcut icon" href="../favicon.ico" />
    <title>Redis Explorer - Error</title>
    <link rel="stylesheet" href="../lib.assets/bootstrap/css/bootstrap.min.css">
  </head>

  <body>
    <div class="container py-5">
      <div class="alert alert-danger">
        <h4 class="alert-heading">Error</h4>
        <p><?php echo htmlspecialchars($message); ?></p>
        <hr>
        <form method="post">
          <button type="submit" name="logout" value="1" class="btn btn-primary">Back</button>
        </form>
      </div>
    </div>
  </body>

  </html>
<?php
  exit();
}


// =====================
// Require login
// =====================
if (empty($_SESSION['redis_config'])) {
  // display login form
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <link type="image/x-icon" rel="icon" href="../favicon.ico" />
    <link type="image/x-icon" rel="shortcut icon" href="../favicon.ico" />
    <title>Redis Explorer - Login</title>
    <link rel="stylesheet" href="../lib.assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../lib.assets/css/fontawesome/css/all.min.css" />
  </head>

  <body>
    <div class="container py-3">
      <form method="post">
        <div class="form-group">
          <label>Host</label>
          <input type="text" name="redis_host" class="form-control" value="localhost" required>
        </div>
        <div class="form-group">
          <label>Port</label>
          <input type="number" name="redis_port" class="form-control" value="6379" min="1" required>
        </div>
        <div class="form-group">
          <label>Password (optional)</label>
          <input type="password" name="redis_password" class="form-control">
        </div>
        <div class="form-group">
          <label>Default DB</label>
          <input type="number" name="redis_db" class="form-control" value="0" min="0">
        </div>
        <div class="form-group">
          <label>Timeout (seconds)</label>
          <input type="number" step="0.1" min="0" name="redis_timeout" class="form-control" value="5.0">
        </div>
        <button type="submit" name="login" value="1" class="btn btn-primary">Connect</button>
      </form>
    </div>
  </body>

  </html>
<?php
  exit();
}

// =====================
// Load config from session
// =====================
$cfg = isset($_SESSION['redis_config']) ? $_SESSION['redis_config'] : array();

$redisHost     = isset($cfg['host'])     ? $cfg['host']     : 'localhost';
$redisPort     = isset($cfg['port'])     ? (int)$cfg['port'] : 6379;
$redisPassword = isset($cfg['password']) ? $cfg['password'] : '';
$defaultDb     = isset($cfg['db'])       ? (int)$cfg['db']   : 0;
$redisTimeout  = isset($cfg['timeout'])  ? (float)$cfg['timeout'] : 5.0;

$selectedDb = isset($_GET['db']) ? $_GET['db'] : (isset($_POST['db']) ? $_POST['db'] : $defaultDb);
$selectedDb = max(0, (int)$selectedDb);

// =====================
// Initialize Redis connection
// =====================
$redisConnection = new Redis();
try {
  if (!extension_loaded('redis')) {
    throw new Exception('The Redis extension is not loaded. Please install and enable it in your PHP configuration.');
  }
  if (!$redisConnection->connect($redisHost, $redisPort, $redisTimeout)) {
    renderError("Failed to connect to Redis server at {$redisHost}:{$redisPort}");
  }

  if (!empty($redisPassword) && !$redisConnection->auth($redisPassword)) {
    renderError('Redis authentication failed.');
  }



  if (!$redisConnection->select($selectedDb)) {
    renderError("Failed to select Redis database index: {$selectedDb}");
  }

  $redisConnection->setOption(Redis::OPT_READ_TIMEOUT, $redisTimeout);
} catch (Exception $e) {
  renderError('Error: ' . $e->getMessage());
}


// =====================
// Request parameters
// =====================
$keyPattern = isset($_GET['filter']) ? $_GET['filter'] : '*';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = 3;
$offset      = ($currentPage - 1) * $pageSize;

// =====================
// Data operations
// =====================
$totalKeys  = RedisExplorer::countKeysWithScan($redisConnection, $keyPattern);
$totalPages = max(1, ceil($totalKeys / $pageSize));
$keys       = RedisExplorer::getKeysByPageWithScan($redisConnection, $keyPattern, $offset, $pageSize);

// =====================
// Handle requests
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Insert
  if (isset($_POST['insert'])) {
    $newKey   = trim($_POST['new_key']);
    $newValue = $_POST['new_value'];
    if ($newKey !== '') {
      $redisConnection->set($newKey, $newValue);
    }
  }

  // Update
  if (isset($_POST['update'])) {
    $updateKey   = $_POST['update'];
    $updateValue = $_POST['update_value'];
    $redisConnection->set($updateKey, $updateValue);
  }

  // Delete single key
  if (isset($_POST['delete'])) {
    $deleteKey = $_POST['delete'];
    $redisConnection->del($deleteKey);
  }

  // Delete single key
  if (isset($_POST['delete_ajax'])) {
    $deleteKey = $_POST['delete_ajax'];
    $redisConnection->del($deleteKey);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
  }

  // Delete all keys
  if (isset($_POST['delete_all'])) {
    $allKeys = RedisExplorer::getKeysByPageWithScan($redisConnection, $keyPattern, 0, $totalKeys);
    foreach ($allKeys as $k) {
      $redisConnection->del($k);
    }
    $currentPage = 1;
  }

  // Redirect after action
  header("Location: ?filter=" . urlencode($keyPattern) . "&page={$currentPage}&db={$selectedDb}");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
  <link type="image/x-icon" rel="icon" href="../favicon.ico" />
  <link type="image/x-icon" rel="shortcut icon" href="../favicon.ico" />
  <title>Redis Explorer</title>
  <link rel="stylesheet" href="../lib.assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../lib.assets/css/fontawesome/css/all.min.css" />
  <script src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
  <script src="../lib.assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    nav .pagination {
      justify-content: center;
    }

    .filter-section {
      margin-bottom: 1rem;
      padding: 0.5rem 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div class="container-fluid py-3">

    <div class="filter-section">
      <form method="get" class="form-inline">
        <label for="db" class="mr-2">Select DB:</label>
        <select class="form-control mr-2" name="db" id="db" onchange="this.form.submit()">
          <?php for ($dbIndex = 0; $dbIndex < 16; $dbIndex++) { ?>
            <option value="<?php echo $dbIndex; ?>" <?php if ($selectedDb == $dbIndex) echo 'selected'; ?>>
              DB <?php echo $dbIndex; ?>
            </option>
          <?php } ?>
        </select>

        <input type="hidden" name="page" value="<?php echo $currentPage; ?>" />

        <label for="filter" class="mr-2">Key filter</label>
        <input type="text" id="filter" name="filter" class="form-control mr-2"
          value="<?php echo htmlspecialchars($keyPattern); ?>" autocomplete="off" />

        <button type="submit" class="btn btn-primary mr-2">Search</button>
        <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#insertModal">Insert New</button>
        <button class="btn btn-danger" type="button" onclick="window.location='?logout'">Logout</button>
      </form>
    </div>

    <nav>
      <ul class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++) { ?>
          <li class="page-item<?php echo $p == $currentPage ? ' active' : ''; ?>">
            <a class="page-link" href="?filter=<?php echo urlencode($keyPattern); ?>&page=<?php echo $p; ?>&db=<?php echo $selectedDb; ?>">
              <?php echo $p; ?>
            </a>
          </li>
        <?php } ?>
      </ul>
    </nav>
    <div class="data-section">
      <?php if (!empty($keys)) { ?>
        <table class="table table-row table-sort-by-column table-sm">
          <thead>
            <tr>
              <th width="30" class="text-center">#</th>
              <th>Key</th>
              <th>Type</th>
              <th>Value</th>
              <th>TTL</th>
              <th width="160">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($keys as $i => $key) {
              $keyType  = $redisConnection->type($key);
              $keyTtl   = $redisConnection->ttl($key);
              $keyValue = RedisExplorer::getRedisValue($redisConnection, $key, $keyType);
            ?>
              <tr>
                <td class="text-right"><?php echo $offset + $i + 1; ?></td>
                <td><?php echo htmlspecialchars($key); ?></td>
                <td><?php echo $keyType; ?></td>
                <td><?php echo htmlspecialchars($keyValue); ?></td>
                <td><?php echo $keyTtl >= 0 ? $keyTtl . 's' : '∞'; ?></td>
                <td>
                  <button class="btn btn-sm btn-info"
                    data-toggle="modal"
                    data-target="#updateModal"
                    data-key="<?php echo htmlspecialchars($key); ?>"
                    data-value="<?php echo htmlspecialchars($keyValue); ?>">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger btn-delete" data-key="<?php echo htmlspecialchars($key); ?>">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <button class="btn btn-danger btn-delete-all" type="button">Delete All</button>

    </div>
    <form method="post" class="d-inline" name="deleteAllForm">
      <input type="hidden" name="delete_all" value="1" />
      <input type="hidden" name="db" value="<?php echo $selectedDb; ?>" />
      <input type="hidden" name="filter" value="<?php echo htmlspecialchars($keyPattern); ?>" />
    </form>

  <?php } else if (!empty($keyPattern)) { ?>
    <div class="alert alert-warning">No keys found for pattern <code><?php echo htmlspecialchars($keyPattern); ?></code>.</div>
  <?php } else { ?>
    <div class="alert alert-warning">No keys found.</div>
  <?php } ?>
  </div>

  <div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Insert New Key</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Key</label>
            <input type="text" name="new_key" class="form-control" autocomplete="off" required>
          </div>
          <div class="form-group">
            <label>Value</label>
            <input type="text" name="new_value" class="form-control" autocomplete="off" required>
          </div>
          <input type="hidden" name="db" value="<?php echo $selectedDb; ?>" />
        </div>
        <div class="modal-footer">
          <button type="submit" name="insert" value="1" class="btn btn-success">Insert</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Value</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="update" id="updateKey" />
          <div class="form-group">
            <label>Key</label>
            <input type="text" id="updateKeyDisplay" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label>Value</label>
            <input type="text" name="update_value" id="updateValue" class="form-control" required>
          </div>
          <input type="hidden" name="db" value="<?php echo $selectedDb; ?>" />
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded">
        <div class="modal-header">
          <h5 class="modal-title">Delete Confirmation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this key?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="confirmDeleteAllModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded">
        <div class="modal-header">
          <h5 class="modal-title">Delete Confirmation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete all keys matching the current filter?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" id="confirmDeleteAllBtn" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let keyToDelete = null;
    document.addEventListener('DOMContentLoaded', function() {

      // Pass data to update modal
      $('#updateModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var key = button.data('key');
        var value = button.data('value');
        var modal = $(this);
        modal.find('#updateKey').val(key);
        modal.find('#updateKeyDisplay').val(key);
        modal.find('#updateValue').val(value);
      });

      document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
          keyToDelete = this.dataset.key;
          $('#confirmDeleteModal').modal('show');
        });
      });

      document.querySelectorAll('.btn-delete-all').forEach(btn => {
        btn.addEventListener('click', function() {
          keyToDelete = this.dataset.key;
          $('#confirmDeleteAllModal').modal('show');
        });
      });

      document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (keyToDelete) {
          // url is current page
          const url = window.location.href;
          fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'delete_ajax=' + encodeURIComponent(keyToDelete)
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                // delete the row from the table
                document.querySelector(`[data-key="${keyToDelete}"]`).closest('tr').remove();
              }
              $('#confirmDeleteModal').modal('hide');
              keyToDelete = null;
            });
        }
      });

      document.getElementById('confirmDeleteAllBtn').addEventListener('click', function() {
        $('form[name="deleteAllForm"]').submit();
        $('#confirmDeleteAllModal').modal('hide');
      });
    });
  </script>
</body>

</html>