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
use MagicAdmin\AppEntityLanguageImpl;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\ApplicationMin;
use MagicAdmin\Entity\Data\ErrorCache;
use MagicApp\AppFormBuilder;
use MagicApp\XLSX\DocumentWriter;
use MagicApp\XLSX\XLSXDataFormat;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "error-cache", $appLanguage->getErrorCache());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
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
if($inputGet->getUserAction() == UserAction::DETAIL)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->errorCacheId, $inputGet->getErrorCacheId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$errorCache = new ErrorCache(null, $database);
	try{
		$subqueryMap = null;
		$errorCache->findOne($specification, null, $subqueryMap);
		if($errorCache->issetErrorCacheId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new ErrorCache(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td><?php echo $errorCache->issetApplication() ? $errorCache->getApplication()->getName() : "";?></td>
					</tr>
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
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $errorCache->dateFormatTimeCreate($appConfig->getDateFormatDetail());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $errorCache->dateFormatTimeEdit($appConfig->getDateFormatDetail());?></td>
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
		}
		else
		{
			// Do somtething here when data is not found
			?>
			<div class="page page-jambi">
				<div class="jambi-wrapper">
					<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>
				</div>
			</div>
			<?php 
		}
	}
	catch(Exception $e)
	{
		// Do somtething here when exception
		?>
		<div class="page page-jambi">
			<div class="jambi-wrapper">
				<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
			</div>
		</div>
		<?php 
	}
require_once $appInclude->mainAppFooter(__DIR__);
}
else 
{
$appEntityLanguage = new AppEntityLanguageImpl(new ErrorCache(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"applicationId" => PicoSpecification::filter("applicationId", "string"),
	"fileName" => PicoSpecification::filter("fileName", "fulltext")
);
$sortOrderMap = array(
	"applicationId" => "applicationId",
	"fileName" => "fileName",
	"filePath" => "filePath",
	"modificationTime" => "modificationTime",
	"errorCode" => "errorCode",
	"message" => "message",
	"lineNumber" => "lineNumber",
	"active" => "active"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);

if($inputGet->getError() == "with-error")
{
	$specification->addAnd(PicoPredicate::getInstance()->greaterThanOrEquals(Field::of()->lineNumber, 0));
}
else if($inputGet->getError() == "without-error")
{
	$specification->addAnd(PicoPredicate::getInstance()->equals(Field::of()->lineNumber, -1));
}

// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, array(
	array(
		"sortBy" => "modificationTime", 
		"sortType" => PicoSort::ORDER_TYPE_DESC
	),
	array(
		"sortBy" => "fileName", 
		"sortType" => PicoSort::ORDER_TYPE_ASC
	)
));

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new ErrorCache(null, $database);

$subqueryMap = array(
	"applicationId" => array(
	"columnName" => "application_id",
	"entityName" => "ApplicationMin",
	"tableName" => "application",
	"primaryKey" => "application_id",
	"objectName" => "application",
	"propertyName" => "name"
)
);

if($inputGet->getUserAction() == UserAction::EXPORT)
{
	$exporter = DocumentWriter::getCSVDocumentWriter($appLanguage);
	$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".csv";
	$sheetName = "Sheet 1";

	$headerFormat = new XLSXDataFormat($dataLoader, 3);
	$pageData = $dataLoader->findAll($specification, null, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
	$exporter->write($pageData, $fileName, $sheetName, array(
		$appLanguage->getNumero() => $headerFormat->asNumber(),
		$appEntityLanguage->getErrorCacheId() => $headerFormat->getErrorCacheId(),
		$appEntityLanguage->getApplication() => $headerFormat->getApplication(),
		$appEntityLanguage->getFileName() => $headerFormat->getFileName(),
		$appEntityLanguage->getFilePath() => $headerFormat->getFilePath(),
		$appEntityLanguage->getModificationTime() => $headerFormat->getModificationTime(),
		$appEntityLanguage->getErrorCode() => $headerFormat->getErrorCode(),
		$appEntityLanguage->getMessage() => $headerFormat->getMessage(),
		$appEntityLanguage->getLineNumber() => $headerFormat->getLineNumber(),
		$appEntityLanguage->getTimeCreate() => $headerFormat->getTimeCreate(),
		$appEntityLanguage->getTimeEdit() => $headerFormat->getTimeEdit(),
		$appEntityLanguage->getAdminCreate() => $headerFormat->getAdminCreate(),
		$appEntityLanguage->getAdminEdit() => $headerFormat->getAdminEdit(),
		$appEntityLanguage->getIpCreate() => $headerFormat->getIpCreate(),
		$appEntityLanguage->getIpEdit() => $headerFormat->getIpEdit(),
		$appEntityLanguage->getActive() => $headerFormat->asString()
	), 
	function($index, $row) use ($appLanguage){
		
		return array(
			sprintf("%d", $index + 1),
			$row->getErrorCacheId(),
			$row->issetApplication() ? $row->getApplication()->getName() : "",
			$row->getFileName(),
			$row->getFilePath(),
			$row->getModificationTime(),
			$row->getErrorCode(),
			$row->getMessage(),
			$row->getLineNumber(),
			$row->getTimeCreate(),
			$row->getTimeEdit(),
			$row->getAdminCreate(),
			$row->getAdminEdit(),
			$row->getIpCreate(),
			$row->getIpEdit(),
			$row->optionActive($appLanguage->getYes(), $appLanguage->getNo())
		);
	});
	exit();
}
/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getApplication();?></span>
					<span class="filter-control">
							<select class="form-control" name="application_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new ApplicationMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->applicationId, Field::of()->name, $inputGet->getApplicationId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getError();?></span>
					<span class="filter-control">
						<select class="form-control" name="error">
							<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
							<option value="with-error"<?php echo $inputGet->getError() == "with-error" ? " selected" : "";?>><?php echo $appLanguage->getLabelOptionWithError();?></option>
							<option value="without-error"<?php echo $inputGet->getError() == "without-error" ? " selected" : "";?>><?php echo $appLanguage->getLabelOptionWithoutError();?></option>
						</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getFileName();?></span>
					<span class="filter-control">
						<input type="text" name="file_name" class="form-control" value="<?php echo $inputGet->getFileName();?>" autocomplete="off"/>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedDetail()){ ?>
		
				<span class="filter-group">
					<button type="submit" name="user_action" value="export" class="btn btn-success"><?php echo $appLanguage->getButtonExport();?></button>
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
								<td class="data-controll data-selector" data-key="error_cache_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-error-cache-id"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-controll data-viewer">
									<span class="fa fa-folder"></span>
								</td>
								<?php } ?>
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="application_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getApplication();?></a></td>
								<td data-col-name="file_name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getFileName();?></a></td>
								<td data-col-name="file_path" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getFilePath();?></a></td>
								<td data-col-name="modification_time" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getModificationTime();?></a></td>
								<td data-col-name="error_code" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getErrorCode();?></a></td>
								<td data-col-name="message" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getMessage();?></a></td>
								<td data-col-name="line_number" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLineNumber();?></a></td>
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
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->error_cache_id, $errorCache->getErrorCacheId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="application_id"><?php echo $errorCache->issetApplication() ? $errorCache->getApplication()->getName() : "";?></td>
								<td data-col-name="file_name"><?php echo $errorCache->getFileName();?></td>
								<td data-col-name="file_path"><?php echo $errorCache->getFilePath();?></td>
								<td data-col-name="modification_time"><?php echo $errorCache->getModificationTime();?></td>
								<td data-col-name="error_code"><?php echo $errorCache->getErrorCode();?></td>
								<td data-col-name="message"><?php echo $errorCache->getMessage();?></td>
								<td data-col-name="line_number"><?php echo $errorCache->getLineNumber();?></td>
							</tr>
							<?php 
							}
							?>
		
						</tbody>
					</table>
				</div>
				<div class="button-wrapper">
					<div class="form-control-container button-area">
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" value="delete" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleDeleteConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>"><?php echo $appLanguage->getButtonDelete();?></button>
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

