<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\MagicObject;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicAdmin\AppIncludeImpl;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAppTemplate\AppUserPermissionImpl;

require_once __DIR__ . "/inc.app/auth.php";

class RedisExplorer
{
    private $redis;

    public function __construct($connection)
    {
        $this->redis = $connection;
    }

    public function insert($key, $type, $data, $ttl)
    {
        // Insert logic here
        $this->redis->set($key, $data);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
    }

    public function findAll($specification, $pageable, $sortable, $withTotalResult = false, $fields = null, $findOption = MagicObject::FIND_OPTION_FETCH_DATA)
    {
        // Find all logic here
        $keys = $this->redis->keys('*'); // Simplified for example
        $results = [];
        foreach ($keys as $key) {
            $data = $this->redis->get($key);
            $results[] = new MagicObject([
                'key' => $key,
                'data' => $data,
                'type' => 'string', // Simplified for example
                'ttl' => $this->redis->ttl($key)
            ]);
        }
        return new MagicObject([
            'totalResult' => count($results),
            'data' => $results
        ]);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @return MagicObject
     */
    public function findOne($key)
    {
        // Find one logic here
        $data = $this->redis->get($key);
        if ($data !== false) {
            new MagicObject([
                'key' => $key,
                'data' => $data,
                'type' => 'string', // Simplified for example
                'ttl' => $this->redis->ttl($key)
            ]);
        }
        return new MagicObject();
    }

    public function delete($key)
    {
        // Delete logic here
        return $this->redis->del($key);
    }

    public function issetKey($key)
    {
        return $this->redis->exists($key);
    }
    public static function renderError($message)
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

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "redis", $appLanguage->getRedis());
$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

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
// Initialize Redis connection
// =====================
$redisConnection = new Redis();
try {
  if (!extension_loaded('redis')) {
    throw new Exception('The Redis extension is not loaded. Please install and enable it in your PHP configuration.');
  }
  if (!$redisConnection->connect($redisHost, $redisPort, $redisTimeout)) {
    RedisExplorer::renderError("Failed to connect to Redis server at {$redisHost}:{$redisPort}");
  }

  if (!empty($redisPassword) && !$redisConnection->auth($redisPassword)) {
    RedisExplorer::renderError('Redis authentication failed.');
  }



  if (!$redisConnection->select($selectedDb)) {
    RedisExplorer::renderError("Failed to select Redis database index: {$selectedDb}");
  }

  $redisConnection->setOption(Redis::OPT_READ_TIMEOUT, $redisTimeout);
} catch (Exception $e) {
  RedisExplorer::renderError('Error: ' . $e->getMessage());
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

$dataFilter = null;

if($inputPost->getUserAction() == UserAction::CREATE)
{
	$redisKey = ($inputPost->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$redisType = ($inputPost->getType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$redisData = ($inputPost->getData(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$redisTtl = ($inputPost->getTtl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
    
	try
	{
		$redisConnection->set($redisKey, $redisData);
        if ($redisTtl > 0) {
            $redisConnection->expire($redisKey, $redisTtl);
        }
		$newId = $redisKey;
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->key, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{

    $redisKey = ($inputPost->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
    $redisType = ($inputPost->getType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
    $redisData = ($inputPost->getData(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
    $redisTtl = ($inputPost->getTtl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));

	try
	{
        $redisConnection->set($redisKey, $redisData);
        if ($redisTtl > 0) {
            $redisConnection->expire($redisKey, $redisTtl);
        }
		$newId = $redisKey;
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->key, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::DELETE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			try
			{
				$redisConnection->del($rowId);
			}
			catch(Exception $e)
			{
				// Do something here to handle exception
				error_log($e->getMessage());
			}
		}
	}
	$currentModule->redirectToItself();
}





if($inputGet->getUserAction() == UserAction::CREATE)
{

require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
						
		<?php if($currentModule->hasErrorField())
						{
						?>
		
						
		<div class="alert alert-danger">
			<?php echo $currentModule->getErrorMessage(); ?>
		</div>
		
						
		<?php $currentModule->restoreFormData($currentModule->getFormData(), $currentModule->getErrorField(), "#createform");
						}
						?>
		
						
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appLanguage->getRedisKey();?></td>
						<td>
							<input type="text" class="form-control" name="key" id="key" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisType();?></td>
						<td>
							<input type="text" class="form-control" name="type" id="type" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisData();?></td>
						<td>
							<textarea class="form-control" name="data" id="data" spellcheck="false"></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisTtl();?></td>
						<td>
							<input type="text" class="form-control" name="ttl" id="ttl" value="" autocomplete="off"/>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" id="create_new_data" value="create"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
}
else if($inputGet->getUserAction() == UserAction::UPDATE)
{
	$specification = $inputGet->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
	$redisExplorer = new RedisExplorer($redisConnection);
	try{
		$redis = $redisExplorer->findOne($specification);
		if($redis->issetKey())
		{

require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
						
		<?php if($currentModule->hasErrorField())
						{
						?>
		
						
		<div class="alert alert-danger">
			<?php echo $currentModule->getErrorMessage(); ?>
		</div>
		
						
		<?php $currentModule->restoreFormData($currentModule->getFormData(), $currentModule->getErrorField(), "#updateform");
						}
						?>
		
						
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appLanguage->getRedisKey();?></td>
						<td>
							<input type="text" class="form-control" name="app_builder_new_pk_key" id="key" value="<?php echo $redis->getKey();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisType();?></td>
						<td>
							<input type="text" class="form-control" name="type" id="type" value="<?php echo $redis->getType();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisData();?></td>
						<td>
							<textarea class="form-control" name="data" id="data" spellcheck="false"><?php echo $redis->getData();?></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisTtl();?></td>
						<td>
							<input type="text" class="form-control" name="ttl" id="ttl" value="<?php echo $redis->getTtl();?>" autocomplete="off"/>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" id="update_data" value="update"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
							<input type="hidden" name="key" id="primary_key_value" value="<?php echo $redis->getKey();?>"/>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
		}
		else
		{
			// Do somtething here when data is not found
			?>
			<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			<?php 
		}
require_once $appInclude->mainAppFooter(__DIR__);
	}
	catch(Exception $e)
	{
require_once $appInclude->mainAppHeader(__DIR__);
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once $appInclude->mainAppFooter(__DIR__);
	}
}
else if($inputGet->getUserAction() == UserAction::DETAIL)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->key, $inputGet->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$redisExplorer = new RedisExplorer($redisConnection);
	try{
		$redis = $redisExplorer->findOne($specification);
		if($redis->issetKey())
		{

require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($redis->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $redis->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appLanguage->getRedisKey();?></td>
						<td><?php echo $redis->getKey();?></td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisType();?></td>
						<td><?php echo $redis->getType();?></td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisData();?></td>
						<td><?php echo $redis->getData();?></td>
					</tr>
					<tr>
						<td><?php echo $appLanguage->getRedisTtl();?></td>
						<td><?php echo $redis->getTtl();?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" id="update_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->key, $redis->getKey());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="key" id="primary_key_value" value="<?php echo $redis->getKey();?>"/>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
		}
		else
		{
			// Do somtething here when data is not found
			?>
			<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			<?php 
		}
	}
	catch(Exception $e)
	{
require_once $appInclude->mainAppHeader(__DIR__);
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once $appInclude->mainAppFooter(__DIR__);
	}
}
else 
{


$specMap = array(
	
);
$sortOrderMap = array(
	"key" => "key",
	"type" => "type",
	"data" => "data",
	"ttl" => "ttl"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, null);

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new RedisExplorer($redisConnection);


/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
                <span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getRedisKey();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="key" value="<?php echo $inputGet->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedCreate()){ ?>
		
				<span class="filter-group">
					<button type="button" class="btn btn-primary" id="add_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::CREATE);?>'"><?php echo $appLanguage->getButtonAdd();?></button>
				</span>
				<?php } ?>
			</form>
		</div>
		<div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
			<?php } /*ajaxSupport*/ ?>
			<?php try{
				$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, null, MagicObject::FIND_OPTION_NO_FETCH_DATA);
				if($pageData->getTotalResult() > 0)
				{		
				    $pageControl = $pageData->getPageControl(Field::of()->page, $currentModule->getSelf())
				    ->setNavigation(
				        $dataControlConfig->getPrev(), $dataControlConfig->getNext(),
				        $dataControlConfig->getFirst(), $dataControlConfig->getLast()
				    )
				    ->setPageRange($dataControlConfig->getPageRange())
				    ;
			?>
			<div class="pagination pagination-top">
			    <div class="pagination-number">
			    <?php echo $pageControl; ?>
			    </div>
			</div>
			<form action="" method="post" class="data-form">
				<div class="data-wrapper">
					<table class="table table-row table-sort-by-column">
						<thead>
							<tr>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-controll data-selector" data-key="key">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-key"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td class="data-controll data-editor">
									<span class="fa fa-edit"></span>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-controll data-viewer">
									<span class="fa fa-folder"></span>
								</td>
								<?php } ?>
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="key" class="order-controll"><a href="#"><?php echo $appLanguage->getRedisKey();?></a></td>
								<td data-col-name="type" class="order-controll"><a href="#"><?php echo $appLanguage->getRedisType();?></a></td>
								<td data-col-name="data" class="order-controll"><a href="#"><?php echo $appLanguage->getRedisData();?></a></td>
								<td data-col-name="ttl" class="order-controll"><a href="#"><?php echo $appLanguage->getRedisTtl();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($redis = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="key">
									<input type="checkbox" class="checkbox check-slave checkbox-key" name="checked_row_id[]" value="<?php echo $redis->getKey();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td class="data-editor">
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->key, $redis->getKey());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-viewer">
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->key, $redis->getKey());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="key" class="data-column"><?php echo $redis->getKey();?></td>
								<td data-col-name="type" class="data-column"><?php echo $redis->getType();?></td>
								<td data-col-name="data" class="data-column"><?php echo $redis->getData();?></td>
								<td data-col-name="ttl" class="data-column"><?php echo $redis->getTtl();?></td>
							</tr>
							<?php 
							}
							?>
		
						</tbody>
					</table>
				</div>
				<div class="button-wrapper">
					<div class="button-area">
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" id="delete_selected" value="delete" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleDeleteConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>"><?php echo $appLanguage->getButtonDelete();?></button>
						<?php } ?>
					</div>
				</div>
			</form>
			<div class="pagination pagination-bottom">
			    <div class="pagination-number">
			    <?php echo $pageControl; ?>
			    </div>
			</div>
			
			<?php 
			}
			else
			{
			    ?>
			    <div class="alert alert-info"><?php echo $appLanguage->getMessageDataNotFound();?></div>
			    <?php
			}
			?>
			
			<?php
			}
			catch(Exception $e)
			{
			    ?>
			    <div class="alert alert-danger"><?php echo $appInclude->printException($e);?></div>
			    <?php
			} 
			?>
			<?php /*ajaxSupport*/ if(!$currentAction->isRequestViaAjax()){ ?>
		</div>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
}
/*ajaxSupport*/
}

