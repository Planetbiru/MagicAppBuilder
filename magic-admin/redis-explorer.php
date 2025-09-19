<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use AppBuilder\Util\RedisExplorer;
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

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "redis", $appLanguage->getRedisExplorer());
$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

if (isset($_POST['logout']) || isset($_GET['logout'])) {
	unset($_SESSION['rc']);
	header("Location: ?");
	exit();
}

if (isset($_POST['login'])) {
	$_SESSION['rc'] = [
		'h'     => trim($_POST['redis_host']),
		'p'     => (int)$_POST['redis_port'],
		's' => trim($_POST['redis_password']),
		'd'       => isset($_POST['redis_db']) ? (int)$_POST['redis_db'] : 0,
		't'  => isset($_POST['redis_timeout']) ? (float)$_POST['redis_timeout'] : 5.0,
	];

	header("Location: ?");
	exit();
}


// =====================
// Load config from session
// =====================
$cfg = isset($_SESSION['rc']) ? $_SESSION['rc'] : array();

$redisHost     = isset($cfg['h']) ? $cfg['h'] : 'localhost';
$redisPort     = isset($cfg['p']) ? (int)$cfg['p'] : 6379;
$redisPassword = isset($cfg['s']) ? $cfg['s'] : '';
$defaultDb     = isset($cfg['d']) ? (int)$cfg['d'] : 0;
$redisTimeout  = isset($cfg['t']) ? (float)$cfg['t'] : 5.0;

$selectedDb = isset($_GET['redis_db']) ? $_GET['redis_db'] : (isset($_POST['redis_db']) ? $_POST['redis_db'] : $defaultDb);
$selectedDb = max(0, (int)$selectedDb);

if($selectedDb != $defaultDb)
{
	$_SESSION['rc']['d'] = $selectedDb;
}

// =====================
// Require login
// =====================
if (empty($_SESSION['rc'])) {
  // display login form
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
      <form method="post">
        <div class="form-group">
          <label><?php echo $appLanguage->getRedisHost();?></label>
          <input type="text" name="redis_host" class="form-control" value="localhost" required>
        </div>
        <div class="form-group">
          <label><?php echo $appLanguage->getRedisPort();?></label>
          <input type="number" name="redis_port" class="form-control" value="6379" min="1" required>
        </div>
        <div class="form-group">
          <label><?php echo $appLanguage->getRedisPassword();?></label>
          <input type="password" name="redis_password" class="form-control">
        </div>
        <div class="form-group">
          <label><?php echo $appLanguage->getDefaultDatabase();?></label>
          <input type="number" name="redis_db" class="form-control" value="0" min="0">
        </div>
        <div class="form-group">
          <label><?php echo $appLanguage->getTimeOut();?></label>
          <input type="number" step="0.1" min="0" name="redis_timeout" class="form-control" value="5.0">
        </div>
        <button type="submit" name="login" value="1" class="btn btn-primary"><?php echo $appLanguage->getButtonConnect();?></button>
      </form>
    </div>
<?php
require_once $appInclude->mainAppFooter(__DIR__);
  exit();
}

// =====================
// Initialize Redis connection
// =====================
$redisConnection = null;
try {
  if (!extension_loaded('redis')) {
    throw new Exception('The Redis extension is not loaded. Please install and enable it in your PHP configuration.');
  }
  $redisConnection = new \Redis();
  if (!$redisConnection->connect($redisHost, $redisPort, $redisTimeout)) {
	require_once $appInclude->mainAppHeader(__DIR__);
    RedisExplorer::renderError($appLanguage, "Failed to connect to Redis server at {$redisHost}:{$redisPort}");
	require_once $appInclude->mainAppFooter(__DIR__);
	exit();
  }

  if (!empty($redisPassword) && !$redisConnection->auth($redisPassword)) {
	require_once $appInclude->mainAppHeader(__DIR__);
    RedisExplorer::renderError($appLanguage, 'Redis authentication failed.');
	require_once $appInclude->mainAppFooter(__DIR__);
	exit();
  }


  if (!$redisConnection->select($selectedDb)) {
	require_once $appInclude->mainAppHeader(__DIR__);
    RedisExplorer::renderError($appLanguage, "Failed to select Redis database index: {$selectedDb}");
	require_once $appInclude->mainAppFooter(__DIR__);
	exit();
  }

  $redisConnection->setOption(\Redis::OPT_READ_TIMEOUT, $redisTimeout);
} catch (Exception $e) {
  require_once $appInclude->mainAppHeader(__DIR__);
  RedisExplorer::renderError($appLanguage, 'Error: ' . $e->getMessage());
  require_once $appInclude->mainAppFooter(__DIR__);
  exit();
}

// =====================
// Request parameters
// =====================
$keyPattern = isset($_GET['filter']) ? $_GET['filter'] : '*';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize    = $dataControlConfig->getPageSize();
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
	$redisKey = $inputPost->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
	$redisType = $inputPost->getType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
	$redisData = $inputPost->getData(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
	$redisTtl = $inputPost->getTtl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
    
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
    $redisKey = $inputPost->getAppBuilderNewPkKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
    $redisType = $inputPost->getType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
    $redisData = $inputPost->getData(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
    $redisTtl = $inputPost->getTtl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

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
						<td><?php echo $appLanguage->getRedisDatabase();?></td>
						<td>
							<select class="form-control mr-2" name="redis_db" id="redis_db">
							<?php for ($redis_db = 0; $redis_db < 16; $redis_db++) { ?>
								<option value="<?php echo $redis_db; ?>" <?php if ($selectedDb == $redis_db) echo 'selected'; ?>>
								<?php echo $redis_db; ?>
								</option>
							<?php } ?>
							</select>
						</td>
					</tr>
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
						<td><?php echo $appLanguage->getRedisDatabase();?></td>
						<td>
							<select class="form-control mr-2" name="redis_db" id="redis_db">
							<?php for ($redis_db = 0; $redis_db < 16; $redis_db++) { ?>
								<option value="<?php echo $redis_db; ?>" <?php if ($selectedDb == $redis_db) echo 'selected'; ?>>
								<?php echo $redis_db; ?>
								</option>
							<?php } ?>
							</select>
						</td>
					</tr>
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
	$specification = $inputGet->getKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
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
						<td><?php echo $appLanguage->getRedisDatabase();?></td>
						<td>
							<?php echo $selectedDb;?>
						</td>
					</tr>
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
							<button type="button" class="btn btn-primary" id="update_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->key, $redis->getKey(), ['redis_db'=>$selectedDb]);?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
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

$redisExplorer = new RedisExplorer($redisConnection);

/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);


?>
<style>
	.pagination{
		text-align: center;
	}
	.pagination .page-item{
		display: inline-block;
	}
</style>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getDatabase();?></span>
					<span class="filter-control">
					<select class="form-control mr-2" name="redis_db" id="redis_db" onchange="this.form.submit()">
					<?php for ($redis_db = 0; $redis_db < 16; $redis_db++) { ?>
						<option value="<?php echo $redis_db; ?>" <?php if ($selectedDb == $redis_db) echo 'selected'; ?>>
						<?php echo $redis_db; ?>
						</option>
					<?php } ?>
					</select>
					</span>
				</span>

			
                <span class="filter-group">
					<span class="filter-label"><?php echo $appLanguage->getRedisKey();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="filter" value="<?php echo $inputGet->getFilter(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedCreate()){ ?>
		
				<span class="filter-group">
					<button type="button" class="btn btn-primary" id="add_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::CREATE);?>'"><?php echo $appLanguage->getButtonAdd();?></button>
				</span>
				<span class="filter-group">
					<button class="btn btn-danger" type="button" onclick="window.location='?logout'"><?php echo $appLanguage->getButtonDisconnect();?></button>
				</span>
				<?php } ?>
			</form>
		</div>
		<div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
			<?php } /*ajaxSupport*/ ?>
			<?php try{
				if(!empty($keys))
				{		
				    
			?>
			<nav>
				<ul class="pagination">
					<?php for ($p = 1; $p <= $totalPages; $p++) { ?><li class="page-item<?php echo $p == $currentPage ? ' active' : ''; ?>"><a class="page-link" href="?filter=<?php echo urlencode($keyPattern); ?>&page=<?php echo $p; ?>&db=<?php echo $selectedDb; ?>">
						<?php echo $p; ?></a></li><?php } ?>
				</ul>
			</nav>
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
					
						<tbody data-offset="<?php echo 0;?>">
							<?php 
							$dataIndex = 0;
							foreach($keys as $key)
							{
								
								$dataIndex++;
								$redis = $redisExplorer->findOne($key);
								
							?>
		
							<tr data-number="<?php echo 0 + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="key">
									<input type="checkbox" class="checkbox check-slave checkbox-key" name="checked_row_id[]" value="<?php echo $redis->getKey();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td class="data-editor">
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->key, $redis->getKey(), ['redis_db'=>$selectedDb]);?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-viewer">
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->key, $redis->getKey(), ['redis_db'=>$selectedDb]);?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo 0 + $dataIndex;?></td>
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

