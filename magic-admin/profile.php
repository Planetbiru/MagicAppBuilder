<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

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
use MagicAdmin\Entity\Data\Admin;
use MagicAdmin\Entity\Data\AdminLevelMin;
use MagicAdmin\Entity\Data\AdminWorkspace;
use MagicAdmin\Entity\Data\ApplicationMin;
use MagicAdmin\Entity\Data\WorkspaceMin;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

/**
 * Sets or creates an admin workspace relationship in the database.
 * 
 * This function checks if an existing admin workspace entry exists for the provided
 * admin ID and workspace ID. If it doesn't exist, a new entry is created with the provided
 * information, including the current admin ID, timestamps, and IP address.
 *
 * @param object $database The database connection object to interact with the database.
 * @param int $adminId The ID of the admin user.
 * @param int $workspaceId The ID of the workspace to associate with the admin.
 * @param int $currentAdminId The ID of the current admin performing the action, used for tracking who created/edited the record.
 * 
 * @return void
 * 
 * @throws Exception If there is an issue with finding or inserting the admin workspace record.
 */
function setAdminWorkspace($database, $adminId, $workspaceId, $currentAdminId)
{
	$adminWorkspace = new AdminWorkspace(null, $database);
	try
	{
		$adminWorkspace->findByAdminIdAndWorkspaceId($adminId, $workspaceId);
	}
	catch(Exception $e)
	{
		$now = date("Y-m-d H:i:s");
		$adminWorkspace = new AdminWorkspace(null, $database);
		$adminWorkspace->setAdminCreate($currentAdminId);
		$adminWorkspace->setAdminEdit($currentAdminId);
		$adminWorkspace->setTimeCreate($now);
		$adminWorkspace->setTimeEdit($now);
		$adminWorkspace->setIpCreate($_SERVER['REMOTE_ADDR']);
		$adminWorkspace->setIpEdit($_SERVER['REMOTE_ADDR']);
		$adminWorkspace->setActive(true);
		$adminWorkspace->insert();
	}
}

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "profile", "Profile");
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = PicoSpecification::getInstance()
	->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $entityAdmin->getAdminId()));

if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminId, $entityAdmin->getAdminId());
	$admin = new Admin(null, $database);
	$updater = $admin->where($specification)
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setPassword($hashPassword)
		->setAdminLevelId($inputPost->getAdminLevelId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setGender($inputPost->getGender(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setBirthDay($inputPost->getBirthDay(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setEmail($inputPost->getEmail(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setPhone($inputPost->getPhone(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setApplicationId($inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setWorkspaceId($inputPost->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setBloked($inputPost->getBloked(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
		->setActive($inputPost->getActive(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	try
	{
		$updater->update();
		$adminId = $entityAdmin->getAdminId();

        $plainPassword = trim($inputPost->getPassword(PicoFilterConstant::FILTER_DEFAULT, false, false, true), " \t\r\n ");
        if(!empty($plainPassword))
        {
            $hashPassword = sha1(sha1($plainPassword));
            $updater = $admin->where($specification);
            $updater->setPassword($hashPassword)->update();
            $sessions->userPassword = sha1($plainPassword);
        }

        $username = trim($inputPost->getUsername(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true), " \t\r\n ");
        if(!empty($username))
        {
            try
            {
                $adminTest = new Admin(null, $database);
                $testSpecs = PicoSpecification::getInstance()
                    ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->username, $username))
                    ->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->adminId, $adminId))
                    ;
                $adminTest->findAll($testSpecs);
            }
            catch(Exception $e)
            {
                // Not found
                $updater = $admin->where($specification);
                $updater->setUsername($username)->update();
                $sessions->username = $username;
            }
        }

		if($admin->getWorkspaceId() != "")
		{
			setAdminWorkspace($database, $adminId, $admin->getWorkspaceId(), $entityAdmin->getAdminId());
		}
		$currentModule->redirectTo(UserAction::DETAIL, Field::of()->admin_id, $adminId);
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
			$admin = new Admin(null, $database);
			try
			{
				$admin->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $rowId))
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
			$admin = new Admin(null, $database);
			try
			{
				$admin->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $rowId))
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
					->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $rowId))
					->addAnd($dataFilter)
					;
				$admin = new Admin(null, $database);
				$admin->where($specification)
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
$appEntityLanguage = new AppEntityLanguage(new Admin(), $appConfig, $currentUser->getLanguageId());
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
							<input autocomplete="off" class="form-control" type="text" name="name" id="name"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUsername();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="username" id="username"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPassword();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="password" name="password" id="password"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminLevel();?></td>
						<td>
							<select class="form-control" name="admin_level_id" id="admin_level_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminLevelMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminLevelId, Field::of()->name)
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getGender();?></td>
						<td>
							<select class="form-control" name="gender" id="gender">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<option value="M">Man</option>
								<option value="W">Woman</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBirthDay();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="date" name="birth_day" id="birth_day"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getEmail();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="email" name="email" id="email"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPhone();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="tel" name="phone" id="phone"/>
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
						<td><?php echo $appEntityLanguage->getLanguageId();?></td>
						<td>
							<input autocomplete="off" class="form-control" type="text" name="language_id" id="language_id"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBloked();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="bloked" id="bloked" value="1"/> <?php echo $appEntityLanguage->getBloked();?></label>
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
	$specification = PicoSpecification::getInstance();
	$specification->addAnd($dataFilter);
	$admin = new Admin(null, $database);
	try{
		$admin->findOne($specification);
		if($admin->issetAdminId())
		{
$appEntityLanguage = new AppEntityLanguage(new Admin(), $appConfig, $currentUser->getLanguageId());
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
							<input class="form-control" type="text" name="name" id="name" value="<?php echo $admin->getName();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUsername();?></td>
						<td>
							<input class="form-control" type="text" name="username" id="username" value="<?php echo $admin->getUsername();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPassword();?></td>
						<td>
							<input class="form-control" type="password" name="password" id="password" value="" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminLevel();?></td>
						<td>
							<select class="form-control" name="admin_level_id" id="admin_level_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new AdminLevelMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false)), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->adminLevelId, Field::of()->name, $admin->getAdminLevelId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getGender();?></td>
						<td>
							<select class="form-control" name="gender" id="gender" data-value="<?php echo $admin->getGender();?>">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<option value="M" <?php echo AppFormBuilder::selected($admin->getGender(), 'M');?>>Man</option>
								<option value="W" <?php echo AppFormBuilder::selected($admin->getGender(), 'W');?>>Woman</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBirthDay();?></td>
						<td>
							<input class="form-control" type="date" name="birth_day" id="birth_day" value="<?php echo $admin->getBirthDay();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getEmail();?></td>
						<td>
							<input class="form-control" type="email" name="email" id="email" value="<?php echo $admin->getEmail();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPhone();?></td>
						<td>
							<input class="form-control" type="tel" name="phone" id="phone" value="<?php echo $admin->getPhone();?>" autocomplete="off"/>
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
								Field::of()->applicationId, Field::of()->name, $admin->getApplicationId())
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
								Field::of()->workspaceId, Field::of()->name, $admin->getWorkspaceId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLanguageId();?></td>
						<td>
							<input class="form-control" type="text" name="language_id" id="language_id" value="<?php echo $admin->getLanguageId();?>" autocomplete="off"/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBloked();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="bloked" id="bloked" value="1" <?php echo $admin->createCheckedBloked();?>/> <?php echo $appEntityLanguage->getBloked();?></label>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td>
							<label><input class="form-check-input" type="checkbox" name="active" id="active" value="1" <?php echo $admin->createCheckedActive();?>/> <?php echo $appEntityLanguage->getActive();?></label>
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
else
{
	$specification = PicoSpecification::getInstance();
	$specification->addAnd($dataFilter);
	$admin = new Admin(null, $database);
	try{
		$subqueryMap = array(
		"adminLevelId" => array(
			"columnName" => "admin_level_id",
			"entityName" => "AdminLevelMin",
			"tableName" => "admin_level",
			"primaryKey" => "admin_level_id",
			"objectName" => "admin_level",
			"propertyName" => "name"
		), 
		"applicationId" => array(
			"columnName" => "application_id",
			"entityName" => "ApplicationMin",
			"tableName" => "application",
			"primaryKey" => "application_id",
			"objectName" => "application",
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
		$admin->findOne($specification, null, $subqueryMap);
		if($admin->issetAdminId())
		{
$appEntityLanguage = new AppEntityLanguage(new Admin(), $appConfig, $currentUser->getLanguageId());
require_once $appInclude->mainAppHeader(__DIR__);
			// Define map here
			$mapForGender = array(
				"M" => array("value" => "M", "label" => "Man", "default" => "false"),
				"W" => array("value" => "W", "label" => "Woman", "default" => "false")
			);
?>
<div class="page page-jambi page-detail">
	<div class="jambi-wrapper">
		<?php
		if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($admin->getWaitingFor()))
		{
				?>
				<div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $admin->getWaitingFor());?></div>
				<?php
		}
		?>
		
		<form name="detailform" id="detailform" action="" method="post">
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td><?php echo $appEntityLanguage->getName();?></td>
						<td><?php echo $admin->getName();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getUsername();?></td>
						<td><?php echo $admin->getUsername();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminLevel();?></td>
						<td><?php echo $admin->issetAdminLevel() ? $admin->getAdminLevel()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getGender();?></td>
						<td><?php echo isset($mapForGender) && isset($mapForGender[$admin->getGender()]) && isset($mapForGender[$admin->getGender()]["label"]) ? $mapForGender[$admin->getGender()]["label"] : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBirthDay();?></td>
						<td><?php echo $admin->getBirthDay();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getEmail();?></td>
						<td><?php echo $admin->getEmail();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPhone();?></td>
						<td><?php echo $admin->getPhone();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getApplication();?></td>
						<td><?php echo $admin->issetApplication() ? $admin->getApplication()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getWorkspace();?></td>
						<td><?php echo $admin->issetWorkspace() ? $admin->getWorkspace()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLanguageId();?></td>
						<td><?php echo $admin->getLanguageId();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLastResetPassword();?></td>
						<td><?php echo $admin->getLastResetPassword();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getBloked();?></td>
						<td><?php echo $admin->optionBloked($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeCreate();?></td>
						<td><?php echo $admin->getTimeCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $admin->getTimeEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $admin->getAdminCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $admin->getAdminEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpCreate();?></td>
						<td><?php echo $admin->getIpCreate();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getIpEdit();?></td>
						<td><?php echo $admin->getIpEdit();?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getActive();?></td>
						<td><?php echo $admin->optionActive($appLanguage->getYes(), $appLanguage->getNo());?></td>
					</tr>
				</tbody>
			</table>
			<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td></td>
						<td>
							<button type="button" class="btn btn-primary" onclick="window.location='<?php echo $currentModule->getRedirectUrl(UserAction::UPDATE, Field::of()->admin_id, $admin->getAdminId());?>';"><?php echo $appLanguage->getButtonUpdate();?></button>
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
