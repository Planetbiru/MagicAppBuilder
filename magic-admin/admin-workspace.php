<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\MagicObject;
use MagicObject\SetterGetter;
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
use MagicAdmin\Entity\Data\AdminWorkspace;
use MagicAdmin\Entity\Data\AdminMin;
use MagicAdmin\Entity\Data\WorkspaceMin;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "admin-workspace", "Admin Workspace");
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
	$adminWorkspace = new AdminWorkspace(null, $database);
	$adminWorkspace->setAdminId($inputPost->getAdminId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$adminWorkspace->setWorkspaceId($inputPost->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$adminWorkspace->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$adminWorkspace->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$adminWorkspace->setAdminCreate($currentAction->getUserId());
	$adminWorkspace->setTimeCreate($currentAction->getTime());
	$adminWorkspace->setIpCreate($currentAction->getIp());
	$adminWorkspace->setAdminEdit($currentAction->getUserId());
	$adminWorkspace->setTimeEdit($currentAction->getTime());
	$adminWorkspace->setIpEdit($currentAction->getIp());
	try
	{
		$adminWorkspace->insert();
		$newId = $adminWorkspace->getAdminWorkspaceId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->admin_workspace_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminWorkspaceId, $inputPost->getAdminWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$adminWorkspace = new AdminWorkspace(null, $database);
	$updater = $adminWorkspace->where($specification)
		->setAdminId($inputPost->getAdminId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setWorkspaceId($inputPost->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$newId = $inputPost->getAdminWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->admin_workspace_id, $newId);
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
			$adminWorkspace = new AdminWorkspace(null, $database);
			try
			{
				$adminWorkspace->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminWorkspaceId, $rowId))
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
			$adminWorkspace = new AdminWorkspace(null, $database);
			try
			{
				$adminWorkspace->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminWorkspaceId, $rowId))
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminWorkspaceId, $rowId))
					->addAnd($dataFilter)
					;
				$adminWorkspace = new AdminWorkspace(null, $database);
				$adminWorkspace->where($specification)
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
else if($inputPost->getUserAction() == UserAction::SORT_ORDER)
{
	if($inputPost->getNewOrder() != null && $inputPost->countableNewOrder())
	{
		foreach($inputPost->getNewOrder() as $dataItem)
		{
			try
			{
				if(is_string($dataItem))
				{
					$dataItem = new SetterGetter(json_decode($dataItem));
				}
				$rowId = $dataItem->getPrimaryKey();
				$sortOrder = intval($dataItem->getSortOrder());
				$specification = PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminWorkspaceId, $rowId))
					->addAnd($dataFilter)
					;
				$adminWorkspace = new AdminWorkspace(null, $database);
				$adminWorkspace->where($specification)
					->setSortOrder($sortOrder)
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
if($inputGet->getUserAction() == UserAction::CREATE)
{
$appEntityLanguage = new AppEntityLanguage(new AdminWorkspace(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getWorkspace();?></td>
						<td>
							<select class="form-control" name="workspace_id" id="workspace_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new WorkspaceMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->workspaceId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="number" step="1" name="sort_order" id="sort_order"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminWorkspaceId, $inputGet->getAdminWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$adminWorkspace = new AdminWorkspace(null, $database);
	try{
		$adminWorkspace->findOne($specification);
		if($adminWorkspace->issetAdminWorkspaceId())
		{
$appEntityLanguage = new AppEntityLanguage(new AdminWorkspace(), $appConfig, $currentUser->getLanguageId());
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
								Field::of()->adminId, Field::of()->name, $adminWorkspace->getAdminId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getWorkspace();?></td>
						<td>
							<select class="form-control" name="workspace_id" id="workspace_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new WorkspaceMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->workspaceId, Field::of()->name, $adminWorkspace->getWorkspaceId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input class="form-control" type="number" step="1" name="sort_order" id="sort_order" value="<?php echo $adminWorkspace->getSortOrder();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $adminWorkspace->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
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
							<input type="hidden" name="admin_workspace_id" value="<?php echo $adminWorkspace->getAdminWorkspaceId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminWorkspaceId, $inputGet->getAdminWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$adminWorkspace = new AdminWorkspace(null, $database);
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
		"workspaceId" => array(
			"columnName" => "workspace_id",
			"entityName" => "WorkspaceMin",
			"tableName" => "workspace",
			"primaryKey" => "workspace_id",
			"objectName" => "workspace",
			"propertyName" => "name"
		)
		);
		$adminWorkspace->findOne($specification, null, $subqueryMap);
		if($adminWorkspace->issetAdminWorkspaceId())
		{
$appEntityLanguage = new AppEntityLanguage(new AdminWorkspace(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($adminWorkspace->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $adminWorkspace->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getAdmin();?></td>
						<td><?php echo $adminWorkspace->issetAdmin() ? $adminWorkspace->getAdmin()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getWorkspace();?></td>
						<td><?php echo $adminWorkspace->issetWorkspace() ? $adminWorkspace->getWorkspace()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td><?php echo $adminWorkspace->getSortOrder();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $adminWorkspace->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $adminWorkspace->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $adminWorkspace->getAdminCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $adminWorkspace->getAdminEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $adminWorkspace->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $adminWorkspace->getIpEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $adminWorkspace->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->admin_workspace_id, $adminWorkspace->getAdminWorkspaceId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="admin_workspace_id" value="<?php echo $adminWorkspace->getAdminWorkspaceId();?>"/>
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
$appEntityLanguage = new AppEntityLanguage(new AdminWorkspace(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"adminId" => PicoSpecification::filter("adminId", "fulltext"),
	"workspaceId" => PicoSpecification::filter("workspaceId", "fulltext")
);
$sortOrderMap = array(
	"adminId" => "adminId",
	"workspaceId" => "workspaceId",
	"sortOrder" => "sortOrder",
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
$dataLoader = new AdminWorkspace(null, $database);

$subqueryMap = array(
"adminId" => array(
	"columnName" => "admin_id",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "admin",
	"propertyName" => "name"
), 
"workspaceId" => array(
	"columnName" => "workspace_id",
	"entityName" => "WorkspaceMin",
	"tableName" => "workspace",
	"primaryKey" => "workspace_id",
	"objectName" => "workspace",
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
					<span class="filter-label"><?php echo $appEntityLanguage->getWorkspace();?></span>
					<span class="filter-control">
							<select class="form-control" name="workspace_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new WorkspaceMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->workspaceId, Field::of()->name, $inputGet->getWorkspaceId())
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
				    ->setMargin($dataControlConfig->getPageMargin())
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
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-header"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-controll data-selector" data-key="admin_workspace_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-admin-workspace-id"/>
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
								<td data-col-name="workspace_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getWorkspace();?></a></td>
								<td data-col-name="sort_order" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSortOrder();?></a></td>
								<td data-col-name="active" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getActive();?></a></td>
							</tr>
						</thead>
					
						<tbody class="data-table-manual-sort" data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($adminWorkspace = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-primary-key="<?php echo $adminWorkspace->getAdminWorkspaceId();?>" data-sort-order="<?php echo $adminWorkspace->getSortOrder();?>" data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>" data-active="<?php echo $adminWorkspace->optionActive('true', 'false');?>">
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-body data-sort-handler"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="admin_workspace_id">
									<input type="checkbox" class="checkbox check-slave checkbox-admin-workspace-id" name="checked_row_id[]" value="<?php echo $adminWorkspace->getAdminWorkspaceId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->admin_workspace_id, $adminWorkspace->getAdminWorkspaceId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->admin_workspace_id, $adminWorkspace->getAdminWorkspaceId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="admin_id"><?php echo $adminWorkspace->issetAdmin() ? $adminWorkspace->getAdmin()->getName() : "";?></td>
								<td data-col-name="workspace_id"><?php echo $adminWorkspace->issetWorkspace() ? $adminWorkspace->getWorkspace()->getName() : "";?></td>
								<td data-col-name="sort_order" class="data-sort-order-column"><?php echo $adminWorkspace->getSortOrder();?></td>
								<td data-col-name="active"><?php echo $adminWorkspace->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
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
						<?php if($userPermission->isAllowedSortOrder()){ ?>
						<button type="submit" class="btn btn-primary" name="user_action" value="sort_order" disabled="disabled"><?php echo $appLanguage->getSaveCurrentOrder();?></button>
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

