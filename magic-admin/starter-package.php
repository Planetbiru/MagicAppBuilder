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
use MagicAppTemplate\AppIncludeImpl;
use MagicAppTemplate\AppUserPermissionImpl;
use MagicAdmin\Entity\Data\StarterPackage;
use MagicAdmin\Entity\Data\StarterPackageTrash;
use MagicAdmin\Entity\Data\License;
use MagicAdmin\Entity\Data\Currency;
use MagicApp\XLSX\DocumentWriter;
use MagicApp\XLSX\XLSXDataFormat;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "starter-package", $appLanguage->getStarterPackage());
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
	$starterPackage = new StarterPackage(null, $database);
	$starterPackage->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$starterPackage->setDescription($inputPost->getDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$starterPackage->setLicenseId($inputPost->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$starterPackage->setPrice($inputPost->getPrice(PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT, false, false, true));
	$starterPackage->setCurrencyId($inputPost->getCurrencyId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$starterPackage->setRating($inputPost->getRating(PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT, false, false, true));
	$starterPackage->setVersion($inputPost->getVersion(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true));
	$starterPackage->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true));
	$starterPackage->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true));
	$starterPackage->setAdminCreate($currentAction->getUserId());
	$starterPackage->setTimeCreate($currentAction->getTime());
	$starterPackage->setIpCreate($currentAction->getIp());
	$starterPackage->setAdminEdit($currentAction->getUserId());
	$starterPackage->setTimeEdit($currentAction->getTime());
	$starterPackage->setIpEdit($currentAction->getIp());
	try
	{
		$starterPackage->insert();
		$newId = $starterPackage->getStarterPackageId();
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->starter_package_id, $newId);
	}
	catch(Exception $e)
	{
		$currentModule->redirectToItself();
	}
}
else if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->starterPackageId, $inputPost->getStarterPackageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$starterPackage = new StarterPackage(null, $database);
	$updater = $starterPackage->where($specification);
	$updater->with()
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setDescription($inputPost->getDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setLicenseId($inputPost->getLicenseId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setPrice($inputPost->getPrice(PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT, false, false, true))
		->setCurrencyId($inputPost->getCurrencyId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setRating($inputPost->getRating(PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT, false, false, true))
		->setVersion($inputPost->getVersion(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setSortOrder($inputPost->getSortOrder(PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$newId = $inputPost->getStarterPackageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->starter_package_id, $newId);
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
			$starterPackage = new StarterPackage(null, $database);
			try
			{
				$starterPackage->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->starterPackageId, $rowId))
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
			$starterPackage = new StarterPackage(null, $database);
			try
			{
				$starterPackage->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->starterPackageId, $rowId))
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->starterPackageId, $rowId))
					->addAnd($dataFilter)
					;
				$starterPackage = new StarterPackage(null, $database);
				$starterPackage->findOne($specification);
				if($starterPackage->issetStarterPackageId())
				{
					$starterPackageTrash = new StarterPackageTrash($starterPackage, $database);
					$starterPackageTrash->setAdminDelete($currentAction->getUserId());
					$starterPackageTrash->setTimeDelete($currentAction->getTime());
					$starterPackageTrash->setIpDelete($currentAction->getIp());
					$starterPackageTrash->insert();
					$starterPackage->delete();
				}
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->starterPackageId, $rowId))
					->addAnd($dataFilter)
					;
				$starterPackage = new StarterPackage(null, $database);
				$starterPackage->where($specification)
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
$appEntityLanguage = new AppEntityLanguageImpl(new StarterPackage(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td>
							<textarea class="form-control" name="description" id="description" spellcheck="false"></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicense();?></td>
						<td>
							<select class="form-control" name="license_id" id="license_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new License(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->licenseId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPrice();?></td>
						<td>
							<input type="number" step="any" class="form-control" name="price" id="price" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getCurrency();?></td>
						<td>
							<select class="form-control" name="currency_id" id="currency_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new Currency(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->currencyId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getRating();?></td>
						<td>
							<input type="number" step="any" class="form-control" name="rating" id="rating" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getVersion();?></td>
						<td>
							<input type="text" class="form-control" name="version" id="version" value="" autocomplete="off"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->starterPackageId, $inputGet->getStarterPackageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$starterPackage = new StarterPackage(null, $database);
	try{
		$starterPackage->findOne($specification);
		if($starterPackage->issetStarterPackageId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new StarterPackage(), $appConfig, $currentUser->getLanguageId());
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
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td>
							<input type="text" class="form-control" name="name" id="name" value="<?php echo $starterPackage->getName();?>" autocomplete="off" required="required"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td>
							<textarea class="form-control" name="description" id="description" spellcheck="false"><?php echo $starterPackage->getDescription();?></textarea>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicense();?></td>
						<td>
							<select class="form-control" name="license_id" id="license_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new License(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->licenseId, Field::of()->name, $starterPackage->getLicenseId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPrice();?></td>
						<td>
							<input type="number" step="any" class="form-control" name="price" id="price" value="<?php echo $starterPackage->getPrice();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getCurrency();?></td>
						<td>
							<select class="form-control" name="currency_id" id="currency_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new Currency(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->currencyId, Field::of()->name, $starterPackage->getCurrencyId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getRating();?></td>
						<td>
							<input type="number" step="any" class="form-control" name="rating" id="rating" value="<?php echo $starterPackage->getRating();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getVersion();?></td>
						<td>
							<input type="text" class="form-control" name="version" id="version" value="<?php echo $starterPackage->getVersion();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td>
							<input type="number" step="1" class="form-control" name="sort_order" id="sort_order" value="<?php echo $starterPackage->getSortOrder();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $starterPackage->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
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
							<input type="hidden" name="starter_package_id" id="primary_key_value" value="<?php echo $starterPackage->getStarterPackageId();?>"/>
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
	$specification = PicoSpecification::getInstanceOf(Field::of()->starterPackageId, $inputGet->getStarterPackageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
	$specification->addAnd($dataFilter);
	$starterPackage = new StarterPackage(null, $database);
	try{
		$subqueryMap = array(
		"licenseId" => array(
			"columnName" => "license_id",
			"entityName" => "License",
			"tableName" => "license",
			"primaryKey" => "license_id",
			"objectName" => "license",
			"propertyName" => "name"
		), 
		"currencyId" => array(
			"columnName" => "currency_id",
			"entityName" => "Currency",
			"tableName" => "currency",
			"primaryKey" => "currency_id",
			"objectName" => "currency",
			"propertyName" => "name"
		)
		);
		$starterPackage->findOne($specification, null, $subqueryMap);
		if($starterPackage->issetStarterPackageId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new StarterPackage(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($starterPackage->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $starterPackage->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td><?php echo $starterPackage->getName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getDescription();?></td>
						<td><?php echo $starterPackage->getDescription();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLicense();?></td>
						<td><?php echo $starterPackage->issetLicense() ? $starterPackage->getLicense()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPrice();?></td>
						<td><?php echo $starterPackage->getPrice();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getCurrency();?></td>
						<td><?php echo $starterPackage->issetCurrency() ? $starterPackage->getCurrency()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getRating();?></td>
						<td><?php echo $starterPackage->getRating();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getVersion();?></td>
						<td><?php echo $starterPackage->getVersion();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getSortOrder();?></td>
						<td><?php echo $starterPackage->getSortOrder();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $starterPackage->getAdminCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $starterPackage->getAdminEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $starterPackage->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $starterPackage->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $starterPackage->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $starterPackage->getIpEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $starterPackage->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<?php if($userPermission->isAllowedUpdate()){ ?>
							<button type="button" class="btn btn-primary" id="update_data" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->starter_package_id, $starterPackage->getStarterPackageId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
							<?php } ?>
		
							<button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location='<?php echo $currentModule->getRedirectUrl();?>';"><?php echo $appLanguage->getButtonBackToList();?></button>
							<input type="hidden" name="starter_package_id" id="primary_key_value" value="<?php echo $starterPackage->getStarterPackageId();?>"/>
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
$appEntityLanguage = new AppEntityLanguageImpl(new StarterPackage(), $appConfig, $currentUser->getLanguageId());

$specMap = array(
	"name" => PicoSpecification::filter("name", "fulltext"),
	"licenseId" => PicoSpecification::filter("licenseId", "string"),
	"currencyId" => PicoSpecification::filter("currencyId", "string")
);
$sortOrderMap = array(
	"name" => "name",
	"licenseId" => "licenseId",
	"price" => "price",
	"currencyId" => "currencyId",
	"rating" => "rating",
	"version" => "version",
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
$dataLoader = new StarterPackage(null, $database);

$subqueryMap = array(
"licenseId" => array(
	"columnName" => "license_id",
	"entityName" => "License",
	"tableName" => "license",
	"primaryKey" => "license_id",
	"objectName" => "license",
	"propertyName" => "name"
), 
"currencyId" => array(
	"columnName" => "currency_id",
	"entityName" => "Currency",
	"tableName" => "currency",
	"primaryKey" => "currency_id",
	"objectName" => "currency",
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
		$appEntityLanguage->getStarterPackageId() => $headerFormat->getStarterPackageId(),
		$appEntityLanguage->getName() => $headerFormat->getName(),
		$appEntityLanguage->getDescription() => $headerFormat->asString(),
		$appEntityLanguage->getLicense() => $headerFormat->asString(),
		$appEntityLanguage->getPrice() => $headerFormat->getPrice(),
		$appEntityLanguage->getCurrency() => $headerFormat->asString(),
		$appEntityLanguage->getRating() => $headerFormat->getRating(),
		$appEntityLanguage->getVersion() => $headerFormat->getVersion(),
		$appEntityLanguage->getSortOrder() => $headerFormat->getSortOrder(),
		$appEntityLanguage->getAdminCreate() => $headerFormat->getAdminCreate(),
		$appEntityLanguage->getAdminEdit() => $headerFormat->getAdminEdit(),
		$appEntityLanguage->getTimeCreate() => $headerFormat->getTimeCreate(),
		$appEntityLanguage->getTimeEdit() => $headerFormat->getTimeEdit(),
		$appEntityLanguage->getIpCreate() => $headerFormat->getIpCreate(),
		$appEntityLanguage->getIpEdit() => $headerFormat->getIpEdit(),
		$appEntityLanguage->getActive() => $headerFormat->asString()
	), 
	function($index, $row) use ($appLanguage) {
		return array(
			sprintf("%d", $index + 1),
			$row->getStarterPackageId(),
			$row->getName(),
			$row->getDescription(),
			$row->issetLicense() ? $row->getLicense()->getName() : "",
			$row->getPrice(),
			$row->issetCurrency() ? $row->getCurrency()->getName() : "",
			$row->getRating(),
			$row->getVersion(),
			$row->getSortOrder(),
			$row->getAdminCreate(),
			$row->getAdminEdit(),
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
					<span class="filter-label"><?php echo $appEntityLanguage->getLicense();?></span>
					<span class="filter-control">
							<select class="form-control" name="license_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new License(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->licenseId, Field::of()->name, $inputGet->getLicenseId())
								; ?>
							</select>
					</span>
				</span>
				
				<span class="filter-group">
					<span class="filter-label"><?php echo $appEntityLanguage->getCurrency();?></span>
					<span class="filter-control">
							<select class="form-control" name="currency_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new Currency(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->currencyId, Field::of()->name, $inputGet->getCurrencyId())
								; ?>
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
								<td class="data-controll data-selector" data-key="starter_package_id">
									<input type="checkbox" class="checkbox check-master" data-selector=".checkbox-starter-package-id"/>
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
								<td data-col-name="license_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getLicense();?></a></td>
								<td data-col-name="price" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getPrice();?></a></td>
								<td data-col-name="currency_id" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getCurrency();?></a></td>
								<td data-col-name="rating" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getRating();?></a></td>
								<td data-col-name="version" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getVersion();?></a></td>
								<td data-col-name="sort_order" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getSortOrder();?></a></td>
								<td data-col-name="active" class="order-controll"><a href="#"><?php echo $appEntityLanguage->getActive();?></a></td>
							</tr>
						</thead>
					
						<tbody class="data-table-manual-sort" data-offset="<?php echo $pageData->getDataOffset();?>">
							<?php 
							$dataIndex = 0;
							while($starterPackage = $pageData->fetch())
							{
								$dataIndex++;
							?>
		
							<tr data-primary-key="<?php echo $starterPackage->getStarterPackageId();?>" data-sort-order="<?php echo $starterPackage->getSortOrder();?>" data-number="<?php echo $pageData->getDataOffset() + $dataIndex;?>" data-active="<?php echo $starterPackage->optionActive('true', 'false');?>">
								<?php if($userPermission->isAllowedSortOrder()){ ?>
								<td class="data-sort data-sort-body data-sort-handler"></td>
								<?php } ?>
								<?php if($userPermission->isAllowedBatchAction()){ ?>
								<td class="data-selector" data-key="starter_package_id">
									<input type="checkbox" class="checkbox check-slave checkbox-starter-package-id" name="checked_row_id[]" value="<?php echo $starterPackage->getStarterPackageId();?>"/>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedUpdate()){ ?>
								<td>
									<a class="edit-control" href="<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->starter_package_id, $starterPackage->getStarterPackageId());?>"><span class="fa fa-edit"></span></a>
								</td>
								<?php } ?>
								<?php if($userPermission->isAllowedDetail()){ ?>
								<td>
									<a class="detail-control field-master" href="<?php echo $currentModule->getRedirectUrl(UserAction::DETAIL, Field::of()->starter_package_id, $starterPackage->getStarterPackageId());?>"><span class="fa fa-folder"></span></a>
								</td>
								<?php } ?>
								<td class="data-number"><?php echo $pageData->getDataOffset() + $dataIndex;?></td>
								<td data-col-name="name"><?php echo $starterPackage->getName();?></td>
								<td data-col-name="license_id"><?php echo $starterPackage->issetLicense() ? $starterPackage->getLicense()->getName() : "";?></td>
								<td data-col-name="price"><?php echo $starterPackage->getPrice();?></td>
								<td data-col-name="currency_id"><?php echo $starterPackage->issetCurrency() ? $starterPackage->getCurrency()->getName() : "";?></td>
								<td data-col-name="rating"><?php echo $starterPackage->getRating();?></td>
								<td data-col-name="version"><?php echo $starterPackage->getVersion();?></td>
								<td data-col-name="sort_order" class="data-sort-order-column"><?php echo $starterPackage->getSortOrder();?></td>
								<td data-col-name="active"><?php echo $starterPackage->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
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

