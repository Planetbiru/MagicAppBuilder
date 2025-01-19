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
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicApp\AppUserPermission;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\Entity\Data\Reference;
use MagicAdmin\Entity\Data\AdminMin;
use MagicAdmin\Entity\Data\ApplicationMin;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "reference", "Reference");
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
	$reference = new Reference(null, $database);
	$reference->setAdminId($inputPost->getAdminId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$reference->setApplicationId($inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$reference->setColumnName($inputPost->getColumnName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$reference->setReferenceKey($inputPost->getReferenceKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$reference->setReferenceValue($inputPost->getReferenceValue(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$reference->setAdminCreate($currentAction->getUserId());
	$reference->setTimeCreate($currentAction->getTime());
	$reference->setIpCreate($currentAction->getIp());
	$reference->setAdminEdit($currentAction->getUserId());
	$reference->setTimeEdit($currentAction->getTime());
	$reference->setIpEdit($currentAction->getIp());
	try
	{
		$reference->insert();
		$newId = $reference->getReferenceId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->reference_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->referenceId, $inputPost->getReferenceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$reference = new Reference(null, $database);
	$updater = $reference->where($specification)
		->setAdminId($inputPost->getAdminId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setApplicationId($inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setColumnName($inputPost->getColumnName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setReferenceKey($inputPost->getReferenceKey(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setReferenceValue($inputPost->getReferenceValue(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$newId = $inputPost->getReferenceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->reference_id, $newId);
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
			$reference = new Reference(null, $database);
			try
			{
				$reference->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->referenceId, $rowId))
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
			$reference = new Reference(null, $database);
			try
			{
				$reference->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->referenceId, $rowId))
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->referenceId, $rowId))
					->addAnd($dataFilter)
					;
				$reference = new Reference(null, $database);
				$reference->where($specification)
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
$appEntityLanguage = new AppEntityLanguage(new Reference(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getAdmin();?></td>
						<td>
							<select class="form-control" name="admin_id" id="admin_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td>
							<select class="form-control" name="application_id" id="application_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new ApplicationMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->applicationId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getColumnName();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="column_name" id="column_name"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceKey();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="reference_key" id="reference_key"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceValue();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="reference_value" id="reference_value"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->referenceId, $inputGet->getReferenceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$reference = new Reference(null, $database);
	try{
		$reference->findOne($specification);
		if($reference->issetReferenceId())
		{
$appEntityLanguage = new AppEntityLanguage(new Reference(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getAdmin();?></td>
						<td>
							<select class="form-control" name="admin_id" id="admin_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $reference->getAdminId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td>
							<select class="form-control" name="application_id" id="application_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new ApplicationMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->applicationId, Field::of()->name, $reference->getApplicationId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getColumnName();?></td>
						<td>
							<input class="form-control" type="text" name="column_name" id="column_name" value="<?php echo $reference->getColumnName();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceKey();?></td>
						<td>
							<input class="form-control" type="text" name="reference_key" id="reference_key" value="<?php echo $reference->getReferenceKey();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceValue();?></td>
						<td>
							<input class="form-control" type="text" name="reference_value" id="reference_value" value="<?php echo $reference->getReferenceValue();?>" autocomplete="off"/>
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
							<input type="hidden" name="reference_id" value="<?php echo $reference->getReferenceId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->referenceId, $inputGet->getReferenceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$reference = new Reference(null, $database);
	try{
		$subqueryMap = array(
		"adminId" => array(
			"columnName" => "admin_id",
			"entityName" => "AdminMin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "admin",
			"propertyName" => "name"
		), 
		"applicationId" => array(
			"columnName" => "application_id",
			"entityName" => "ApplicationMin",
			"tableName" => "application",
			"primaryKey" => "application_id",
			"objectName" => "application",
			"propertyName" => "name"
		)
		);
		$reference->findOne($specification, null, $subqueryMap);
		if($reference->issetReferenceId())
		{
$appEntityLanguage = new AppEntityLanguage(new Reference(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($reference->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $reference->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getAdmin();?></td>
						<td><?php echo $reference->issetAdmin() ? $reference->getAdmin()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td><?php echo $reference->issetApplication() ? $reference->getApplication()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getColumnName();?></td>
						<td><?php echo $reference->getColumnName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceKey();?></td>
						<td><?php echo $reference->getReferenceKey();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceValue();?></td>
						<td><?php echo $reference->getReferenceValue();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $reference->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $reference->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $reference->getAdminCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $reference->getAdminEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $reference->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $reference->getIpEdit();?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->reference_id, $reference->getReferenceId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="reference_id" value="<?php echo $reference->getReferenceId();?>"/>
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
$appEntityLanguage = new AppEntityLanguage(new Reference(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"adminId" => PicoSpecification::filter("adminId", "fulltext"),
	"applicationId" => PicoSpecification::filter("applicationId", "fulltext")
);
$sortOrderMap = array(
	"adminId" => "adminId",
	"applicationId" => "applicationId",
	"columnName" => "columnName",
	"referenceKey" => "referenceKey",
	"referenceValue" => "referenceValue"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, null);

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new Reference(null, $database);

$subqueryMap = array(
"adminId" => array(
	"columnName" => "admin_id",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "admin",
	"propertyName" => "name"
), 
"applicationId" => array(
	"columnName" => "application_id",
	"entityName" => "ApplicationMin",
	"tableName" => "application",
	"primaryKey" => "application_id",
	"objectName" => "application",
	"propertyName" => "name"
)
);

/*ajaxSupport*/
if(!$currentAction->isRequestViaAjax()){
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAdmin();?></span>
					<span class="filter-control">
							<select class="form-control" name="admin_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $inputGet->getAdminId())
								; ?>
							</select>
					</span>
				</span>
				
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
				    ->setRange($dataControlConfig->getPageRange())
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
								<td class="data-controll data-selector" data-key="reference_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-reference-id"/>
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
								<td data-col-name="admin_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAdmin();?></a></td>
								<td data-col-name="application_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getApplication();?></a></td>
								<td data-col-name="column_name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getColumnName();?></a></td>
								<td data-col-name="reference_key" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getReferenceKey();?></a></td>
								<td data-col-name="reference_value" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getReferenceValue();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($reference = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="reference_id">
									<input type="checkbox" class="checkbox check-slave checkbox-reference-id" name="checked_row_id[]" value="<?php echo $reference->getReferenceId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->reference_id, $reference->getReferenceId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->reference_id, $reference->getReferenceId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="admin_id"><?php echo $reference->issetAdmin() ? $reference->getAdmin()->getName() : "";?></td>
								<td data-col-name="application_id"><?php echo $reference->issetApplication() ? $reference->getApplication()->getName() : "";?></td>
								<td data-col-name="column_name"><?php echo $reference->getColumnName();?></td>
								<td data-col-name="reference_key"><?php echo $reference->getReferenceKey();?></td>
								<td data-col-name="reference_value"><?php echo $reference->getReferenceValue();?></td>
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

