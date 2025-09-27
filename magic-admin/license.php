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
use MagicAdmin\AppEntityLanguageImpl;
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAdmin\AppIncludeImpl;
use MagicAppTemplate\AppUserPermissionImpl;
use MagicAdmin\Entity\Data\License;
use MagicObject\Exceptions\InvalidValueException;
use MagicAdmin\AppValidatorMessage;
use MagicApp\XLSX\DocumentWriter;
use MagicApp\XLSX\XLSXDataFormat;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "license", $appLanguage->getLicense());
$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}


$dataFilter = null;

if($inputPost->getUserAction() == UserAction::CREATE)
{
	$license = new License(null, $database);
	$license->setLicenseId($inputPost->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$license->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$license->setLicenseType($inputPost->getLicenseType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$license->setDescription($inputPost->getDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$license->setUrl($inputPost->getUrl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$license->setAllowCommercialUse($inputPost->getAllowCommercialUse(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$license->setAllowModification($inputPost->getAllowModification(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$license->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$license->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$license->setAdminCreate($currentAction->getUserId());
	$license->setTimeCreate($currentAction->getTime());
	$license->setIpCreate($currentAction->getIp());
	$license->setAdminEdit($currentAction->getUserId());
	$license->setTimeEdit($currentAction->getTime());
	$license->setIpEdit($currentAction->getIp());
	try
	{
		$license->insert();
		$newId = $license->getLicenseId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->license_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->licenseId, $inputPost->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$license = new License(null, $database);
	$updater = $license->where($specification);
	$updater->with()
		->setLicenseId($inputPost->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setLicenseType($inputPost->getLicenseType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setDescription($inputPost->getDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setUrl($inputPost->getUrl(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setAllowCommercialUse($inputPost->getAllowCommercialUse(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
		->setAllowModification($inputPost->getAllowModification(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
		->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();

		// Update primary key value
		$newId = $inputPost->getAppBuilderNewPkLicenseId();
		$license = new License(null, $database);
		$license->where($specification)->setLicenseId($newId)->update();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->license_id, $newId);
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
			$license = new License(null, $database);
			try
			{
				$license->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->licenseId, $rowId))
					->addAnd(
						PicoSpecification::getInstance()
							->addOr(PicoPredicate::getInstance()->equals(Field::of()->active, null))
							->addOr(PicoPredicate::getInstance()->notEquals(Field::of()->active, true))
					)
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
			$license = new License(null, $database);
			try
			{
				$license->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->licenseId, $rowId))
					->addAnd(
						PicoSpecification::getInstance()
							->addOr(PicoPredicate::getInstance()->equals(Field::of()->active, null))
							->addOr(PicoPredicate::getInstance()->notEquals(Field::of()->active, false))
					)
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->licenseId, $rowId))
					->addAnd($dataFilter)
					;
				$license = new License(null, $database);
				$license->where($specification)
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->licenseId, $rowId))
					->addAnd($dataFilter)
					;
				$license = new License(null, $database);
				$license->where($specification)
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
$appEntityLanguage = new AppEntityLanguageImpl(new License(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getLicenseId();?></td>
						<td>
							<input type="text" class="form-control" name="license_id" id="license_id" value="" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicenseType();?></td>
						<td>
							<input type="text" class="form-control" name="license_type" id="license_type" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td>
							<textarea class="form-control" name="description" id="description" spellcheck="false"></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input type="url" class="form-control" name="url" id="url" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowCommercialUse();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="allow_commercial_use" id="allow_commercial_use" value="1"/> <?php echo $appEntityLanguage->getAllowCommercialUse();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowModification();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="allow_modification" id="allow_modification" value="1"/> <?php echo $appEntityLanguage->getAllowModification();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input type="number" step="1" class="form-control" name="sort_order" id="sort_order" value="" autocomplete="off"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->licenseId, $inputGet->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$license = new License(null, $database);
	try{
		$license->findOne($specification);
		if($license->issetLicenseId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new License(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getLicenseId();?></td>
						<td>
							<input type="text" class="form-control" name="app_builder_new_pk_license_id" id="license_id" value="<?php echo $license->getLicenseId();?>" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="<?php echo $license->getName();?>" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicenseType();?></td>
						<td>
							<input type="text" class="form-control" name="license_type" id="license_type" value="<?php echo $license->getLicenseType();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td>
							<textarea class="form-control" name="description" id="description" spellcheck="false"><?php echo $license->getDescription();?></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td>
							<input type="url" class="form-control" name="url" id="url" value="<?php echo $license->getUrl();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowCommercialUse();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="allow_commercial_use" id="allow_commercial_use" value="1" <?php echo $license->createCheckedAllowCommercialUse();?>/> <?php echo $appEntityLanguage->getAllowCommercialUse();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowModification();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="allow_modification" id="allow_modification" value="1" <?php echo $license->createCheckedAllowModification();?>/> <?php echo $appEntityLanguage->getAllowModification();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input type="number" step="1" class="form-control" name="sort_order" id="sort_order" value="<?php echo $license->getSortOrder();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $license->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
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
							<input type="hidden" name="license_id" id="primary_key_value" value="<?php echo $license->getLicenseId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->licenseId, $inputGet->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$license = new License(null, $database);
	try{
		$subqueryMap = array(
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
		$license->findOne($specification, null, $subqueryMap);
		if($license->issetLicenseId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new License(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($license->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $license->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getLicenseId();?></td>
						<td><?php echo $license->getLicenseId();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td><?php echo $license->getName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicenseType();?></td>
						<td><?php echo $license->getLicenseType();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td><?php echo $license->getDescription();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUrl();?></td>
						<td><a href="<?php echo $license->getUrl();?>" target="_blank"><?php echo $license->getUrl();?></a></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowCommercialUse();?></td>
						<td><?php echo $license->optionAllowCommercialUse($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAllowModification();?></td>
						<td><?php echo $license->optionAllowModification($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td><?php echo $license->getSortOrder();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $license->issetCreator() ? $license->getCreator()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $license->issetEditor() ? $license->getEditor()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $license->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $license->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $license->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $license->getIpEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $license->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" id="update_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->license_id, $license->getLicenseId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="license_id" id="primary_key_value" value="<?php echo $license->getLicenseId();?>"/>
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
$appEntityLanguage = new AppEntityLanguageImpl(new License(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"name" => PicoSpecification::filter("name", "fulltext"),
	"allowCommercialUse" => PicoSpecification::filter("allowCommercialUse", "boolean"),
	"allowModification" => PicoSpecification::filter("allowModification", "boolean")
);
$sortOrderMap = array(
	"licenseId" => "licenseId",
	"name" => "name",
	"licenseType" => "licenseType",
	"description" => "description",
	"url" => "url",
	"allowCommercialUse" => "allowCommercialUse",
	"allowModification" => "allowModification",
	"sortOrder" => "sortOrder",
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
$dataLoader = new License(null, $database);

$subqueryMap = array(
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

if($inputGet->getUserAction() == UserAction::EXPORT)
{
	$exporter = DocumentWriter::getXLSXDocumentWriter();
	$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".xlsx";
	$sheetName = "Sheet 1";

	$headerFormat = new XLSXDataFormat($dataLoader, 3);
	$pageData = $dataLoader->findAll($specification, null, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
	$exporter->write($pageData, $fileName, $sheetName, array(
		$appLanguage->getNumero() => $headerFormat->asNumber(),
		$appEntityLanguage->getLicenseId() => $headerFormat->getLicenseId(),
		$appEntityLanguage->getName() => $headerFormat->getName(),
		$appEntityLanguage->getLicenseType() => $headerFormat->getLicenseType(),
		$appEntityLanguage->getDescription() => $headerFormat->asString(),
		$appEntityLanguage->getUrl() => $headerFormat->getUrl(),
		$appEntityLanguage->getAllowCommercialUse() => $headerFormat->asString(),
		$appEntityLanguage->getAllowModification() => $headerFormat->asString(),
		$appEntityLanguage->getSortOrder() => $headerFormat->getSortOrder(),
		$appEntityLanguage->getAdminCreate() => $headerFormat->asString(),
		$appEntityLanguage->getAdminEdit() => $headerFormat->asString(),
		$appEntityLanguage->getTimeCreate() => $headerFormat->getTimeCreate(),
		$appEntityLanguage->getTimeEdit() => $headerFormat->getTimeEdit(),
		$appEntityLanguage->getIpCreate() => $headerFormat->getIpCreate(),
		$appEntityLanguage->getIpEdit() => $headerFormat->getIpEdit(),
		$appEntityLanguage->getActive() => $headerFormat->asString()
	), 
	function($index, $row) use ($appLanguage) {
		return array(
			sprintf("%d", $index + 1),
			$row->getLicenseId(),
			$row->getName(),
			$row->getLicenseType(),
			$row->getDescription(),
			$row->getUrl(),
			$row->optionAllowCommercialUse($appLanguage->getYes(), $appLanguage->getNo()),
			$row->optionAllowModification($appLanguage->getYes(), $appLanguage->getNo()),
			$row->getSortOrder(),
			$row->issetCreator() ? $row->getCreator()->getName() : "",
			$row->issetEditor() ? $row->getEditor()->getName() : "",
			$row->getTimeCreate(),
			$row->getTimeEdit(),
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
					<span class="filter-label"><?php echo $appEntityLanguage->getName();?></span>
					<span class="filter-control">
						<input type="text" class="form-control" name="name" value="<?php echo $inputGet->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, false, true);?>" autocomplete="off"/>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAllowCommercialUse();?></span>
					<span class="filter-control">
							<select class="form-control" name="allow_commercial_use" data-value="<?php echo $inputGet->getAllowCommercialUse();?>">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<option value="yes" <?php echo AppFormBuilder::selected($inputGet->getAllowCommercialUse(), 'yes');?>><?php echo $appLanguage->getLabelOptionYes();?></option>
								<option value="no" <?php echo AppFormBuilder::selected($inputGet->getAllowCommercialUse(), 'no');?>><?php echo $appLanguage->getLabelOptionNo();?></option>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getAllowModification();?></span>
					<span class="filter-control">
							<select class="form-control" name="allow_modification" data-value="<?php echo $inputGet->getAllowModification();?>">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<option value="yes" <?php echo AppFormBuilder::selected($inputGet->getAllowModification(), 'yes');?>><?php echo $appLanguage->getLabelOptionYes();?></option>
								<option value="no" <?php echo AppFormBuilder::selected($inputGet->getAllowModification(), 'no');?>><?php echo $appLanguage->getLabelOptionNo();?></option>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<button type="submit" class="btn btn-success" id="show_data"><?php echo $appLanguage->getButtonSearch();?></button>
				</span>
				<?php if($userPermission->isAllowedExport()){ ?>
		
				<span class="filter-group">
					<button type="submit" name="user_action" id="export_data" value="export" class="btn btn-success"><?php echo $appLanguage->getButtonExport();?></button>
				</span>
				<?php } ?>
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
								<td class="data-controll data-selector" data-key="license_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-license-id"/>
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
								<td data-col-name="license_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLicenseId();?></a></td>
								<td data-col-name="name" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getName();?></a></td>
								<td data-col-name="license_type" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLicenseType();?></a></td>
								<td data-col-name="url" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getUrl();?></a></td>
								<td data-col-name="allow_commercial_use" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowCommercialUse();?></a></td>
								<td data-col-name="allow_modification" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getAllowModification();?></a></td>
								<td data-col-name="sort_order" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSortOrder();?></a></td>
								<td data-col-name="active" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getActive();?></a></td>
							</tr>
						</thead>
					
						<tbody class="data-table-manual-sort" data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($license = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-primary-key="<?php echo $license->getLicenseId();?>" data-sort-order="<?php echo $license->getSortOrder();?>" data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>" data-active="<?php echo $license->optionActive('true', 'false');?>">
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-body data-sort-handler"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="license_id">
									<input type="checkbox" class="checkbox check-slave checkbox-license-id" name="checked_row_id[]" value="<?php echo $license->getLicenseId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td class="data-editor">
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->license_id, $license->getLicenseId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td class="data-viewer">
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->license_id, $license->getLicenseId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="license_id" class="data-column"><?php echo $license->getLicenseId();?></td>
								<td data-col-name="name" class="data-column"><?php echo $license->getName();?></td>
								<td data-col-name="license_type" class="data-column"><?php echo $license->getLicenseType();?></td>
								<td data-col-name="url" class="data-column"><a href="<?php echo $license->getUrl();?>" target="_blank"><?php echo $license->getUrl();?></a></td>
								<td data-col-name="allow_commercial_use" class="data-column"><?php echo $license->optionAllowCommercialUse($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="allow_modification" class="data-column"><?php echo $license->optionAllowModification($appLanguage->getYes(), $appLanguage->getNo());?></td>
								<td data-col-name="sort_order" class="data-sort-order-column"><?php echo $license->getSortOrder();?></td>
								<td data-col-name="active" class="data-column"><?php echo $license->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
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
						<button type="submit" class="btn btn-success" name="user_action" id="activate_selected" value="activate" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleActivateConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningActivateConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>"><?php echo $appLanguage->getButtonActivate();?></button>
						<button type="submit" class="btn btn-warning" name="user_action" id="deactivate_selected" value="deactivate" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleDeactivateConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeactivateConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>"><?php echo $appLanguage->getButtonDeactivate();?></button>
						<?php } ?>
						<?php if($userPermission->isAllowedDelete()){ ?>
						<button type="submit" class="btn btn-danger" name="user_action" id="delete_selected" value="delete" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleDeleteConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>"><?php echo $appLanguage->getButtonDelete();?></button>
						<?php } ?>
						<?php if($userPermission->isAllowedSortOrder()){ ?>
						<button type="submit" class="btn btn-primary" name="user_action" id="save_current_order" value="sort_order" data-confirmation="true" data-event="false" data-onclik-title="<?php echo htmlspecialchars($appLanguage->getTitleSortOrderConfirmation());?>" data-onclik-message="<?php echo htmlspecialchars($appLanguage->getWarningSortOrderConfirmation());?>" data-ok-button-label="<?php echo htmlspecialchars($appLanguage->getButtonOk());?>" data-cancel-button-label="<?php echo htmlspecialchars($appLanguage->getButtonCancel());?>" disabled="disabled"><?php echo $appLanguage->getButtonSaveCurrentOrder();?></button>
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

