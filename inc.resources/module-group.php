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
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicApp\AppUserPermission;
use MagicAppTemplate\AppIncludeImpl;
use MagicAppTemplate\Entity\App\AppModuleGroupImpl;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "module-group", $appLanguage->getModuleGroup());
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
	$moduleGroup = new AppModuleGroupImpl(null, $database);
	$moduleGroup->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$moduleGroup->setUrl($inputPost->getUrl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$moduleGroup->setTarget($inputPost->getTarget(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$moduleGroup->setIcon($inputPost->getIcon(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$moduleGroup->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$moduleGroup->setDefaultData($inputPost->getDefaultData(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$moduleGroup->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$moduleGroup->setAdminCreate($currentAction->getUserId());
	$moduleGroup->setTimeCreate($currentAction->getTime());
	$moduleGroup->setIpCreate($currentAction->getIp());
	$moduleGroup->setAdminEdit($currentAction->getUserId());
	$moduleGroup->setTimeEdit($currentAction->getTime());
	$moduleGroup->setIpEdit($currentAction->getIp());
	try
	{
		$moduleGroup->insert();
		$newId = $moduleGroup->getModuleGroupId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->module_group_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->moduleGroupId, $inputPost->getModuleGroupId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$moduleGroup = new AppModuleGroupImpl(null, $database);
	$updater = $moduleGroup->where($specification)
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setUrl($inputPost->getUrl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setTarget($inputPost->getTarget(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setIcon($inputPost->getIcon(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setDefaultData($inputPost->getDefaultData(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$newId = $inputPost->getModuleGroupId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->module_group_id, $newId);
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
			$moduleGroup = new AppModuleGroupImpl(null, $database);
			try
			{
				$moduleGroup->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleGroupId, $rowId))
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
			$moduleGroup = new AppModuleGroupImpl(null, $database);
			try
			{
				$moduleGroup->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleGroupId, $rowId))
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleGroupId, $rowId))
					->addAnd($dataFilter)
					;
				$moduleGroup = new AppModuleGroupImpl(null, $database);
				$moduleGroup->where($specification)
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleGroupId, $rowId))
					->addAnd($dataFilter)
					;
				$moduleGroup = new AppModuleGroupImpl(null, $database);
				$moduleGroup->where($specification)
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
$appEntityLanguage = new AppEntityLanguage(new AppModuleGroupImpl(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-insert">
	<div class="jambi-wrapper">
		<form name="createform" id="createform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input type="text" class="form-control" name="url" id="url" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTarget();?></td>
						<td>
							<input type="text" class="form-control" name="target" id="target" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIcon();?></td>
						<td>
							<input type="text" class="form-control" name="icon" id="icon" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input type="number" step="1" class="form-control" name="sort_order" id="sort_order" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDefaultData();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="default_data" id="default_data" value="1"/> <?php echo $appEntityLanguage->getDefaultData();?></label>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->moduleGroupId, $inputGet->getModuleGroupId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$moduleGroup = new AppModuleGroupImpl(null, $database);
	try{
		$moduleGroup->findOne($specification);
		if($moduleGroup->issetModuleGroupId())
		{
$appEntityLanguage = new AppEntityLanguage(new AppModuleGroupImpl(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-update">
	<div class="jambi-wrapper">
		<form name="updateform" id="updateform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="<?php echo $moduleGroup->getName();?>" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input type="text" class="form-control" name="url" id="url" value="<?php echo $moduleGroup->getUrl();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTarget();?></td>
						<td>
							<input type="text" class="form-control" name="target" id="target" value="<?php echo $moduleGroup->getTarget();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIcon();?></td>
						<td>
							<input type="text" class="form-control" name="icon" id="icon" value="<?php echo $moduleGroup->getIcon();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input type="number" step="1" class="form-control" name="sort_order" id="sort_order" value="<?php echo $moduleGroup->getSortOrder();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDefaultData();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="default_data" id="default_data" value="1" <?php echo $moduleGroup->createCheckedDefaultData();?>/> <?php echo $appEntityLanguage->getDefaultData();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $moduleGroup->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
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
							<input type="hidden" name="module_group_id" id="primary_key_value" value="<?php echo $moduleGroup->getModuleGroupId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->moduleGroupId, $inputGet->getModuleGroupId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$moduleGroup = new AppModuleGroupImpl(null, $database);
	try{
		$subqueryMap = null;
		$moduleGroup->findOne($specification, null, $subqueryMap);
		if($moduleGroup->issetModuleGroupId())
		{
$appEntityLanguage = new AppEntityLanguage(new AppModuleGroupImpl(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($moduleGroup->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $moduleGroup->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td><?php echo $moduleGroup->getName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td><?php echo $moduleGroup->getUrl();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTarget();?></td>
						<td><?php echo $moduleGroup->getTarget();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIcon();?></td>
						<td><?php echo $moduleGroup->getIcon();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td><?php echo $moduleGroup->getSortOrder();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDefaultData();?></td>
						<td><?php echo $moduleGroup->optionDefaultData($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $moduleGroup->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" id="update_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->module_group_id, $moduleGroup->getModuleGroupId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="module_group_id" id="primary_key_value" value="<?php echo $moduleGroup->getModuleGroupId();?>"/>
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
$appEntityLanguage = new AppEntityLanguage(new AppModuleGroupImpl(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"name" => PicoSpecification::filter("name", "fulltext")
);
$sortOrderMap = array(
	"name" => "name",
	"url" => "url",
	"target" => "target",
	"icon" => "icon",
	"sortOrder" => "sortOrder",
	"defaultData" => "defaultData",
	"active" => "active"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, array(
	array(
		"sortBy" => "sortOrder", 
		"sortType" => PicoSort::ORDER_TYPE_ASC
	)
));

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new AppModuleGroupImpl(null, $database);

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
					<span class="filter-label"><?php echo $appEntityLanguage->getName();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="name" value="<?php echo $inputGet->getName();?>" autocomplete="off"/>
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
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-header"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-controll data-selector" data-key="module_group_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-module-group-id"/>
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
								<td data-col-name="name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getName();?></a></td>
								<td data-col-name="url" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getUrl();?></a></td>
								<td data-col-name="target" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTarget();?></a></td>
								<td data-col-name="icon" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getIcon();?></a></td>
								<td data-col-name="sort_order" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSortOrder();?></a></td>
								<td data-col-name="default_data" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getDefaultData();?></a></td>
								<td data-col-name="active" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getActive();?></a></td>
							</tr>
						</thead>
					
						<tbody class="data-table-manual-sort" data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($moduleGroup = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-primary-key="<?php echo $moduleGroup->getModuleGroupId();?>" data-sort-order="<?php echo $moduleGroup->getSortOrder();?>" data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>" data-active="<?php echo $moduleGroup->optionActive('true', 'false');?>">
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-body data-sort-handler"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="module_group_id">
									<input type="checkbox" class="checkbox check-slave checkbox-module-group-id" name="checked_row_id[]" value="<?php echo $moduleGroup->getModuleGroupId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->module_group_id, $moduleGroup->getModuleGroupId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->module_group_id, $moduleGroup->getModuleGroupId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="name"><?php echo $moduleGroup->getName();?></td>
								<td data-col-name="url"><?php echo $moduleGroup->getUrl();?></td>
								<td data-col-name="target"><?php echo $moduleGroup->getTarget();?></td>
								<td data-col-name="icon"><?php echo $moduleGroup->getIcon();?></td>
								<td data-col-name="sort_order" class="data-sort-order-column"><?php echo $moduleGroup->getSortOrder();?></td>
								<td data-col-name="default_data"><?php echo $moduleGroup->optionDefaultData($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="active"><?php echo $moduleGroup->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
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
						<button type="submit" class="btn btn-success" name="user_action" id="activate_selected" value="activate"><?php echo $appLanguage->getButtonActivate();?></button>
						<button type="submit" class="btn btn-warning" name="user_action" id="deactivate_selected" value="deactivate"><?php echo $appLanguage->getButtonDeactivate();?></button>
						<?php } ?>
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" id="delete_selected" value="delete" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>"><?php echo $appLanguage->getButtonDelete();?></button>
						<?php } ?>
						<?php if($userPermission->isAllowedSortOrder()){ ?>
						<button type="submit" class="btn btn-primary" name="user_action" id="save_current_order" value="sort_order" disabled="disabled"><?php echo $appLanguage->getButtonSaveCurrentOrder();?></button>
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

