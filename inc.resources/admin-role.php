<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

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
use MagicAppTemplate\AppIncludeImpl;
use MagicAppTemplate\Entity\App\AppAdminLevelMinImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;

require_once __DIR__ . "/inc.app/auth.php";

/**
 * Sorts the modules by group and module order.
 *
 * @param MagicObject[] $modules The array of modules to be sorted.
 * @return void
 */
function sortModulesByGroupAndModuleOrder(&$modules) // NOSONAR
{
    usort($modules, function ($a, $b) // NOSONAR
	{
        // Get groupSort and module->sortOrder for both modules
        $aGroupSort = isset($a->groupModule) && isset($a->groupModule->module) && isset($a->groupModule->module->sortOrder)
            ? $a->groupModule->module->sortOrder
            : null;

        $bGroupSort = isset($b->groupModule) && isset($b->groupModule->module) && isset($b->groupModule->module->sortOrder)
            ? $b->groupModule->module->sortOrder
            : null;

        // Compare groupSort values
        if ($aGroupSort !== null && $bGroupSort !== null) {
            if ($aGroupSort < $bGroupSort) return -1;
            if ($aGroupSort > $bGroupSort) return 1;
            $cmp = 0;
        } elseif ($aGroupSort !== null) {
            $cmp = -1;
        } elseif ($bGroupSort !== null) {
            $cmp = 1;
        } else {
            $cmp = 0;
        }

        // If groupSort values are equal, compare module->sortOrder
        if ($cmp === 0) {
            $aSort = isset($a->module) && isset($a->module->sortOrder) ? $a->module->sortOrder : null;
            $bSort = isset($b->module) && isset($b->module->sortOrder) ? $b->module->sortOrder : null;

            if ($aSort !== null && $bSort !== null) {
                if ($aSort < $bSort) return -1;
                if ($aSort > $bSort) return 1;
                return 0;
            } elseif ($aSort !== null) {
                return -1;
            } elseif ($bSort !== null) {
                return 1;
            } else {
                return 0;
            }
        }

        return $cmp;
    });
}


$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "admin-role", $appLanguage->getAdminRole());
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;


if ($inputPost->getUserAction() == UserAction::UPDATE && isset($_POST['admin_role_id']) && is_array($_POST['admin_role_id'])) {
	$database->statTransaction();
	try {
		// Mulai transaksi untuk memastikan data konsisten
		$pdo->beginTransaction();

		foreach ($_POST['admin_role_id'] as $index => $adminRoleId) {
			// Cek dan ambil nilai dari setiap checkbox
			$allowedList = isset($_POST['allowed_list']) && isset($_POST['allowed_list'][$adminRoleId]) ? 1 : 0;
			$allowedDetail = isset($_POST['allowed_detail']) && isset($_POST['allowed_detail'][$adminRoleId]) ? 1 : 0;
			$allowedCreate = isset($_POST['allowed_create']) && isset($_POST['allowed_create'][$adminRoleId]) ? 1 : 0;
			$allowedUpdate = isset($_POST['allowed_update']) && isset($_POST['allowed_update'][$adminRoleId]) ? 1 : 0;
			$allowedDelete = isset($_POST['allowed_delete']) && isset($_POST['allowed_delete'][$adminRoleId]) ? 1 : 0;
			$allowedApprove = isset($_POST['allowed_approve']) && isset($_POST['allowed_approve'][$adminRoleId]) ? 1 : 0;
			$allowedSortOrder = isset($_POST['allowed_sort_order']) && isset($_POST['allowed_sort_order'][$adminRoleId]) ? 1 : 0;
			$allowedExport = isset($_POST['allowed_export']) && isset($_POST['allowed_export'][$adminRoleId]) ? 1 : 0;
			
			// Create a new instance of AppAdminRoleImpl
			// and set the database connection
			// to the instance
			// This is a placeholder, replace with actual database connection
			$adminRole = new AppAdminRoleImpl(null, $database);
			
			// Set the values for the adminRole object
			// and update the database
			$adminRole->where(PicoSpecification::getInstance()->addAnd(new PicoPredicate(Field::of()->adminRoleId, $adminRoleId)))
			->setAllowedList($allowedList)
			->setAllowedDetail($allowedDetail)
			->setAllowedCreate($allowedCreate)
			->setAllowedUpdate($allowedUpdate)
			->setAllowedDelete($allowedDelete)
			->setAllowedApprove($allowedApprove)
			->setAllowedSortOrder($allowedSortOrder)
			->setAllowedExport($allowedExport)
			->update();
			
		}

		$database->commit();
		
	} catch (PDOException $e) {
		$database->rollBack();
	}
	
	$currentModule->redirectToItself();
}

if($inputGet->getUserAction() == 'generate')
{
	// Generate admin role
	// for all active modules
	// for the selected admin level
	// and set the database connection
	// to the instance
	$adminRole = new AppAdminRoleImpl(null, $database);
	$moduleFinder = new AppModuleImpl(null, $database);
	$specification1 = PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true));
	$adminLevelId = $inputGet->getAdminLevelId(PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERIC);
	if($adminLevelId != "")
	{
		try
		{
			// Find all modules
			// that are active
			$pageData = $moduleFinder->findAll($specification1);
			foreach($pageData->getResult() as $module)
			{
				$moduleId = $module->getModuleId();
				$moduleCode = $module->getModuleCode();
				$specification2 = PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals(Field::of()->moduleId, $moduleId))
				->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminLevelId, $adminLevelId));
				$adminRole = new AppAdminRoleImpl(null, $database);
				try
				{
					// Check if the admin role already exists
					$adminRole->findOne($specification2);
				}
				catch(Exception $e)
				{
					// Not found
					// Create a new admin role
					// and set the database connection
					$adminRole = new AppAdminRoleImpl(null, $database);
					$adminRole->setModuleId($moduleId)
					->setAdminLevelId($adminLevelId)
					->setModuleCode($moduleCode)
					->setAllowedList(1)
					->setAllowedDetail(0)
					->setAllowedCreate(0)
					->setAllowedUpdate(0)
					->setAllowedDelete(0)
					->setAllowedApprove(0)
					->setAllowedSortOrder(0)
					->setAllowedExport(0)
					->setActive(1)
					->insert();
				}
			}
		}
		catch(Exception $e)
		{
			// Do nothing
		}
	}
	header("Location: " . $currentModule->getSelf() . "?admin_level_id=" . $adminLevelId);
	exit();
}



$appEntityLanguage = new AppEntityLanguage(new AppAdminRoleImpl(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"adminLevelId" => PicoSpecification::filter("adminLevelId", "fulltext")
);
$sortOrderMap = array(
	"moduleId" => "moduleId",
	"allowedList" => "allowedList",
	"allowedDetail" => "allowedDetail",
	"allowedCreate" => "allowedCreate",
	"allowedUpdate" => "allowedUpdate",
	"allowedDelete" => "allowedDelete",
	"allowedApprove" => "allowedApprove",
	"allowedSortOrder" => "allowedSortOrder",
	"allowedExport" => "allowedExport"
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);


// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, null);

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new AppAdminRoleImpl(null, $database);

$subqueryMap = array(
"adminLevelId" => array(
	"columnName" => "admin_level_id",
	"entityName" => "AdminLevelMin",
	"tableName" => "admin_level",
	"primaryKey" => "admin_level_id",
	"objectName" => "admin_level",
	"propertyName" => "name"
), 
"moduleId" => array(
	"columnName" => "module_id",
	"entityName" => "ModuleMin",
	"tableName" => "module",
	"primaryKey" => "module_id",
	"objectName" => "module",
	"propertyName" => "name"
)
);


require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi page-list">
	<div class="jambi-wrapper">
		<div class="filter-section">
			<form action="" method="get" class="filter-form">
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAdminLevel();?></span>
					<span class="filter-control">
							<select class="form-control" name="admin_level_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AppAdminLevelMinImpl(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminLevelId, Field::of()->name, $inputGet->getAdminLevelId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				
				<span class="filter-group">
					<button type="submit" name="user_action" value="generate" class="btn btn-success" id="generate_data"><?php echo $appLanguage->getButtonGenerate();?></button>
				</span>

			</form>
		</div>
		<?php
		if($inputGet->getAdminLevelId() != "")
		{
		?>
		<div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
			<?php try{
				$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true);
				if($pageData->getTotalResult() > 0)
				{		
				    $pageControl = $pageData->getPageControl(Field::of()->page, $currentModule->getSelf())
				    ->setNavigation(
				        $dataControlConfig->getPrev(), $dataControlConfig->getNext(),
				        $dataControlConfig->getFirst(), $dataControlConfig->getLast()
				    )
				    ->setPageRange($dataControlConfig->getPageRange())
				    ;
					
					$sortedModule = $pageData->getResult();
					
					
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
								<td class="data-controll data-number"><?php echo $appLanguage->getNumero();?></td>
								<td data-col-name="module_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getModule();?></a></td>
								<td data-col-name="allowed_list" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedList();?></a></td>
								<td data-col-name="allowed_detail" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedDetail();?></a></td>
								<td data-col-name="allowed_create" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedCreate();?></a></td>
								<td data-col-name="allowed_update" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedUpdate();?></a></td>
								<td data-col-name="allowed_delete" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedDelete();?></a></td>
								<td data-col-name="allowed_approve" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedApprove();?></a></td>
								<td data-col-name="allowed_sort_order" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedSortOrder();?></a></td>
								<td data-col-name="allowed_export" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowedExport();?></a></td>
							</tr>
						</thead>
					
						<tbody data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							foreach($sortedModule as $idx=>$adminRole)
							{
								// Get the module name
								$moduleName = $adminRole->issetModule() ? $adminRole->getModule()->getName() : "";
								
								// Get the admin level name
								$adminLevelName = $adminRole->issetAdminLevel() ? $adminRole->getAdminLevel()->getName() : "";
								
								// Increment data index
							
								$dataIndex++;
								
								$id = $adminRole->getAdminRoleId();
							?>
		
							<tr data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>">
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?>
									<input type="hidden" name="admin_role_id[<?php echo $idx;?>]" value="<?php echo $id;?>">
								</td>
								
								<!-- Module Name -->
								<td data-col-name="module_id">
									<?php echo $adminRole->issetModule() ? $adminRole->getModule()->getName() : "";?>
								</td>

								<!-- Allowed List (checkbox) -->
								<td data-col-name="allowed_list">
									<label>
										<input type="checkbox" name="allowed_list[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedList(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Detail (checkbox) -->
								<td data-col-name="allowed_detail">
									<label>
										<input type="checkbox" name="allowed_detail[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedDetail(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Create (checkbox) -->
								<td data-col-name="allowed_create">
									<label>
										<input type="checkbox" name="allowed_create[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedCreate(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Update (checkbox) -->
								<td data-col-name="allowed_update">
									<label>
										<input type="checkbox" name="allowed_update[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedUpdate(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Delete (checkbox) -->
								<td data-col-name="allowed_delete">
									<label>
										<input type="checkbox" name="allowed_delete[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedDelete(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Approve (checkbox) -->
								<td data-col-name="allowed_approve">
									<label>
										<input type="checkbox" name="allowed_approve[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedApprove(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Sort Order (checkbox) -->
								<td data-col-name="allowed_sort_order">
									<label>
										<input type="checkbox" name="allowed_sort_order[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedSortOrder(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>

								<!-- Allowed Export (checkbox) -->
								<td data-col-name="allowed_export">
									<label>
										<input type="checkbox" name="allowed_export[<?php echo $id;?>]" value="1" 
											<?php echo $adminRole->optionAllowedExport(' checked="checked"', "");?>> 
										<?php echo $appLanguage->getYes();?>
									</label>
								</td>
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
						<button type="submit" class="btn btn-success" name="user_action" id="update" value="update"><?php echo $appLanguage->getButtonUpdate();?></button>
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
		</div>
		<?php
		}
		else
		{
			?>
			<div class="alert alert-info"><?php echo $appLanguage->getMessageSelectFilter();?></div>
			<?php
		}
		?>
	</div>
</div>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
/*ajaxSupport*/
