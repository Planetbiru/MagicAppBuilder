<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\MagicObject;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\AppEntityLanguage;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicApp\AppUserPermission;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\Entity\Data\ErrorCache;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "error-cache", "Error Cache");
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

if($inputPost->getUserAction() == UserAction::CREATE)
{
	$errorCache = new ErrorCache(null, $database);
	$errorCache->setFileName($inputPost->getFileName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$errorCache->setFilePath($inputPost->getFilePath(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$errorCache->setModificationTime($inputPost->getModificationTime(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$errorCache->setErrorCode($inputPost->getErrorCode(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$errorCache->setMessage($inputPost->getMessage(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$errorCache->setLineNumber($inputPost->getLineNumber(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$errorCache->setLastResetPassword($inputPost->getLastResetPassword(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$errorCache->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$errorCache->setAdminCreate($currentAction->getUserId());
	$errorCache->setTimeCreate($currentAction->getTime());
	$errorCache->setIpCreate($currentAction->getIp());
	$errorCache->setAdminEdit($currentAction->getUserId());
	$errorCache->setTimeEdit($currentAction->getTime());
	$errorCache->setIpEdit($currentAction->getIp());
	try
	{
		$errorCache->insert();
		$newId = $errorCache->getErrorCacheId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->error_cache_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->errorCacheId, $inputPost->getErrorCacheId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$errorCache = new ErrorCache(null, $database);
	$updater = $errorCache->where($specification)
		->setFileName($inputPost->getFileName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setFilePath($inputPost->getFilePath(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setModificationTime($inputPost->getModificationTime(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setErrorCode($inputPost->getErrorCode(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setMessage($inputPost->getMessage(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setLineNumber($inputPost->getLineNumber(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setLastResetPassword($inputPost->getLastResetPassword(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$newId = $inputPost->getErrorCacheId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->error_cache_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			$errorCache = new ErrorCache(null, $database);
			try
			{
				$errorCache->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->errorCacheId, $rowId))
					->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->active, true))
					->addAnd($dataFilter)
				)
				->setAdminEdit($currentAction->getUserId())
				->setTimeEdit($currentAction->getTime())
				->setIpEdit($currentAction->getIp())
				->setActive(true)
				->update();
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
else if($inputPost->getUserAction() == UserAction::DEACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			$errorCache = new ErrorCache(null, $database);
			try
			{
				$errorCache->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->errorCacheId, $rowId))
					->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->active, false))
					->addAnd($dataFilter)
				)
				->setAdminEdit($currentAction->getUserId())
				->setTimeEdit($currentAction->getTime())
				->setIpEdit($currentAction->getIp())
				->setActive(false)
				->update();
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
else if($inputPost->getUserAction() == UserAction::DELETE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			try
			{
				$specification = PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->errorCacheId, $rowId))
					->addAnd($dataFilter)
					;
				$errorCache = new ErrorCache(null, $database);
				$errorCache->where($specification)
					->delete();
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
$appEntityLanguage = new AppEntityLanguage(new ErrorCache(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getFileName();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="file_name" id="file_name"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getFilePath();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="file_path" id="file_path"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getModificationTime();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="datetime-local" name="modification_time" id="modification_time"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getErrorCode();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="number" step="1" name="error_code" id="error_code"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getMessage();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="message" id="message"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLineNumber();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="number" step="1" name="line_number" id="line_number"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLastResetPassword();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="datetime-local" name="last_reset_password" id="last_reset_password"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1"/> <?php echo $appEntityLanguage->getActive();?></label>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" value="create"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->errorCacheId, $inputGet->getErrorCacheId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$errorCache = new ErrorCache(null, $database);
	try{
		$errorCache->findOne($specification);
		if($errorCache->issetErrorCacheId())
		{
$appEntityLanguage = new AppEntityLanguage(new ErrorCache(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getFileName();?></td>
						<td>
							<input class="form-control" type="text" name="file_name" id="file_name" value="<?php echo $errorCache->getFileName();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getFilePath();?></td>
						<td>
							<input class="form-control" type="text" name="file_path" id="file_path" value="<?php echo $errorCache->getFilePath();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getModificationTime();?></td>
						<td>
							<input class="form-control" type="datetime-local" name="modification_time" id="modification_time" value="<?php echo $errorCache->getModificationTime();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getErrorCode();?></td>
						<td>
							<input class="form-control" type="number" step="1" name="error_code" id="error_code" value="<?php echo $errorCache->getErrorCode();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getMessage();?></td>
						<td>
							<input class="form-control" type="text" name="message" id="message" value="<?php echo $errorCache->getMessage();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLineNumber();?></td>
						<td>
							<input class="form-control" type="number" step="1" name="line_number" id="line_number" value="<?php echo $errorCache->getLineNumber();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLastResetPassword();?></td>
						<td>
							<input class="form-control" type="datetime-local" name="last_reset_password" id="last_reset_password" value="<?php echo $errorCache->getLastResetPassword();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $errorCache->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="submit" class="btn btn-success" name="user_action" value="update"><?php echo $appLanguage->getButtonSave();?></button>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonCancel();?></button>
							<input type="hidden" name="error_cache_id" value="<?php echo $errorCache->getErrorCacheId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->errorCacheId, $inputGet->getErrorCacheId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$errorCache = new ErrorCache(null, $database);
	try{
		$subqueryMap = null;
		$errorCache->findOne($specification, null, $subqueryMap);
		if($errorCache->issetErrorCacheId())
		{
$appEntityLanguage = new AppEntityLanguage(new ErrorCache(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($errorCache->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $errorCache->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getFileName();?></td>
						<td><?php echo $errorCache->getFileName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getFilePath();?></td>
						<td><?php echo $errorCache->getFilePath();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getModificationTime();?></td>
						<td><?php echo $errorCache->getModificationTime();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getErrorCode();?></td>
						<td><?php echo $errorCache->getErrorCode();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getMessage();?></td>
						<td><?php echo $errorCache->getMessage();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLineNumber();?></td>
						<td><?php echo $errorCache->getLineNumber();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLastResetPassword();?></td>
						<td><?php echo $errorCache->getLastResetPassword();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $errorCache->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $errorCache->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $errorCache->getAdminCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $errorCache->getAdminEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $errorCache->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $errorCache->getIpEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $errorCache->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->error_cache_id, $errorCache->getErrorCacheId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="error_cache_id" value="<?php echo $errorCache->getErrorCacheId();?>"/>
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
$appEntityLanguage = new AppEntityLanguage(new ErrorCache(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	
);
$sortOrderMap = array(
	"fileName" => "fileName",
	"filePath" => "filePath",
	"modificationTime" => "modificationTime",
	"errorCode" => "errorCode",
	"message" => "message",
	"lineNumber" => "lineNumber",
	"lastResetPassword" => "lastResetPassword",
	"active" => "active"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, null);

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new ErrorCache(null, $database);

$subqueryMap = null;

/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<button type="submit" class="btn btn-success"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedCreate()){ ?>
		
				<span class="filter-group">
					<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::CREATE);?>'"><?php echo $appLanguage->getButtonAdd();?></button>
				</span>
				<?php } ?>
			</form>
		</div>
		<div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
			<?php } /*ajaxSupport*/ ?>
			<?php try{
				$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_FETCH_DATA);
				if($pageData->getTotalResult() > 0)
				{		
				    $pageControl = $pageData->getPageControl(Field::of()->page, $currentModule->getSelf())
				    ->setNavigation(
				        $dataControlConfig->getPrev(), $dataControlConfig->getNext(),
				        $dataControlConfig->getFirst(), $dataControlConfig->getLast()
				    )
				    ->setMargin($dataControlConfig->getPageRange())
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
								<td class="data-controll data-selector" data-key="error_cache_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-error-cache-id"/>
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
								<td data-col-name="file_name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getFileName();?></a></td>
								<td data-col-name="file_path" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getFilePath();?></a></td>
								<td data-col-name="modification_time" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getModificationTime();?></a></td>
								<td data-col-name="error_code" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getErrorCode();?></a></td>
								<td data-col-name="message" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getMessage();?></a></td>
								<td data-col-name="line_number" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLineNumber();?></a></td>
								<td data-col-name="last_reset_password" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLastResetPassword();?></a></td>
								<td data-col-name="active" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getActive();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($errorCache = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>" data-active="<?php echo $errorCache->optionActive('true', 'false');?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="error_cache_id">
									<input type="checkbox" class="checkbox check-slave checkbox-error-cache-id" name="checked_row_id[]" value="<?php echo $errorCache->getErrorCacheId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->error_cache_id, $errorCache->getErrorCacheId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->error_cache_id, $errorCache->getErrorCacheId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="file_name"><?php echo $errorCache->getFileName();?></td>
								<td data-col-name="file_path"><?php echo $errorCache->getFilePath();?></td>
								<td data-col-name="modification_time"><?php echo $errorCache->getModificationTime();?></td>
								<td data-col-name="error_code"><?php echo $errorCache->getErrorCode();?></td>
								<td data-col-name="message"><?php echo $errorCache->getMessage();?></td>
								<td data-col-name="line_number"><?php echo $errorCache->getLineNumber();?></td>
								<td data-col-name="last_reset_password"><?php echo $errorCache->getLastResetPassword();?></td>
								<td data-col-name="active"><?php echo $errorCache->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
							</tr>
							<?php 
							}
							?>
		
						</tbody>
					</table>
				</div>
				<div class="button-wrapper">
					<div class="button-area">
						<?php if($userPermission->isAllowedUpdate()){ ?>
						<button type="submit" class="btn btn-success" name="user_action" value="activate"><?php echo $appLanguage->getButtonActivate();?></button>
						<button type="submit" class="btn btn-warning" name="user_action" value="deactivate"><?php echo $appLanguage->getButtonDeactivate();?></button>
						<?php } ?>
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" value="delete" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>"><?php echo $appLanguage->getButtonDelete();?></button>
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

