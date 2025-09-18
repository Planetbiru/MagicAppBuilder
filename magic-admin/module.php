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
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\Module;
use MagicAdmin\Entity\Data\ApplicationMin;
use MagicAdmin\Entity\Data\AdminMin;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "module", $appLanguage->getModule());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

if($inputPost->getUserAction() == UserAction::DELETE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS) as $rowId)
		{
			try
			{
				$specification = PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleId, $rowId))
					->addAnd($dataFilter)
					;
				$module = new Module(null, $database);
				$module->where($specification)
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->moduleId, $inputGet->getModuleId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$module = new Module(null, $database);
	try{
		$subqueryMap = array(
		"applicationId" => array(
			"columnName" => "application_id",
			"entityName" => "ApplicationMin",
			"tableName" => "application",
			"primaryKey" => "application_id",
			"objectName" => "application",
			"propertyName" => "name"
		), 
		"adminCreate" => array(
			"columnName" => "admin_create",
			"entityName" => "Admin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "creator",
			"propertyName" => "name"
		), 
		"adminEdit" => array(
			"columnName" => "admin_edit",
			"entityName" => "Admin",
			"tableName" => "admin",
			"primaryKey" => "admin_id",
			"objectName" => "editor",
			"propertyName" => "name"
		)
		);
		$module->findOne($specification, null, $subqueryMap);
		if($module->issetModuleId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new Module(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($module->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $module->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td><?php echo $module->issetApplication() ? $module->getApplication()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td><?php echo $module->getName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getModuleCode();?></td>
						<td><?php echo $module->getModuleCode();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getFileName();?></td>
						<td><?php echo $module->getFileName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDirectoryName();?></td>
						<td><?php echo $module->getDirectoryName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getReferenceValue();?></td>
						<td><?php echo $module->getReferenceValue();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $module->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $module->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $module->issetCreator() ? $module->getCreator()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $module->issetEditor() ? $module->getEditor()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $module->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $module->getIpEdit();?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
		
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="module_id" id="primary_key_value" value="<?php echo $module->getModuleId();?>"/>
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
$appEntityLanguage = new AppEntityLanguageImpl(new Module(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"applicationId" => PicoSpecification::filter("applicationId", "fulltext"),
	"name" => PicoSpecification::filter("name", "fulltext"),
	"moduleCode" => PicoSpecification::filter("moduleCode", "fulltext"),
	"adminCreate" => PicoSpecification::filter("adminCreate", "fulltext"),
	"adminEdit" => PicoSpecification::filter("adminEdit", "fulltext")
);
$sortOrderMap = array(
	"applicationId" => "application.name",
	"name" => "name",
	"moduleCode" => "moduleCode",
	"fileName" => "fileName",
	"directoryName" => "directoryName",
	"timeCreate" => "timeCreate",
	"timeEdit" => "timeEdit"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, array(
	array(
		"sortBy" => "application.timeCreate", 
		"sortType" => PicoSort::ORDER_TYPE_DESC
	),
	array(
		"sortBy" => "timeCreate", 
		"sortType" => PicoSort::ORDER_TYPE_DESC
	)
));

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new Module(null, $database);

$subqueryMap = array(
"applicationId" => array(
	"columnName" => "application_id",
	"entityName" => "ApplicationMin",
	"tableName" => "application",
	"primaryKey" => "application_id",
	"objectName" => "application",
	"propertyName" => "name"
), 
"adminCreate" => array(
	"columnName" => "admin_create",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "creator",
	"propertyName" => "name"
), 
"adminEdit" => array(
	"columnName" => "admin_edit",
	"entityName" => "AdminMin",
	"tableName" => "admin",
	"primaryKey" => "admin_id",
	"objectName" => "editor",
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
					<span class="filter-label"><?php echo $appEntityLanguage->getApplication();?></span>
					<span class="filter-control">
							<select class="form-control" name="application_id" onchange="this.form.submit();">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new ApplicationMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->timeCreate, PicoSort::ORDER_TYPE_DESC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->applicationId, Field::of()->name, $inputGet->getApplicationId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getName();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="name" value="<?php echo $inputGet->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getModuleCode();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="module_code" value="<?php echo $inputGet->getModuleCode(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAdminCreate();?></span>
					<span class="filter-control">
							<select class="form-control" name="admin_create" onchange="this.form.submit();">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $inputGet->getAdminCreate())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAdminEdit();?></span>
					<span class="filter-control">
							<select class="form-control" name="admin_edit" onchange="this.form.submit();">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminId, Field::of()->name, $inputGet->getAdminEdit())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>

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
								<td class="data-controll data-selector" data-key="module_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-module-id"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-controll data-viewer">
									<span class="fa fa-folder"></span>
								</td>
								<?php } ?>
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="application_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getApplication();?></a></td>
								<td data-col-name="name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getName();?></a></td>
								<td data-col-name="module_code" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getModuleCode();?></a></td>
								<td data-col-name="file_name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getFileName();?></a></td>
								<td data-col-name="directory_name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getDirectoryName();?></a></td>
								<td data-col-name="time_create" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTimeCreate();?></a></td>
								<td data-col-name="time_edit" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getTimeEdit();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($module = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="module_id">
									<input type="checkbox" class="checkbox check-slave checkbox-module-id" name="checked_row_id[]" value="<?php echo $module->getModuleId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-viewer">
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->module_id, $module->getModuleId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="application_id" class="data-column"><?php echo $module->issetApplication() ? $module->getApplication()->getName() : "";?></td>
								<td data-col-name="name" class="data-column"><?php echo $module->getName();?></td>
								<td data-col-name="module_code" class="data-column"><?php echo $module->getModuleCode();?></td>
								<td data-col-name="file_name" class="data-column"><?php echo $module->getFileName();?></td>
								<td data-col-name="directory_name" class="data-column"><?php echo $module->getDirectoryName();?></td>
								<td data-col-name="time_create" class="data-column"><?php echo $module->getTimeCreate();?></td>
								<td data-col-name="time_edit" class="data-column"><?php echo $module->getTimeEdit();?></td>
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

