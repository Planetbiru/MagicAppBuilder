<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\AppEntityLanguage;
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\Admin;
use MagicAdmin\Entity\Data\AdminWorkspace;
use MagicAdmin\Entity\Data\GitProfileMin;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;

require_once __DIR__ . "/inc.app/auth-profile.php";

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
		$adminWorkspace->findOneByAdminIdAndWorkspaceId($adminId, $workspaceId);
	}
	catch(Exception $e)
	{
		$now = date("Y-m-d H:i:s");
		$adminWorkspace = new AdminWorkspace(null, $database);
		$adminWorkspace->setAdminId($adminId);
		$adminWorkspace->setWorkspaceId($workspaceId);
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

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "profile", $appLanguage->getAdministratorProfile());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

$dataFilter = PicoSpecification::getInstance()
	->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $entityAdmin->getAdminId()));

if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminId, $entityAdmin->getAdminId());
	$admin = new Admin(null, $database);
	$updater = $admin->where($specification)
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setGender($inputPost->getGender(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setBirthDay($inputPost->getBirthDay(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setEmail($inputPost->getEmail(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setPhone($inputPost->getPhone(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setGitProfileId($inputPost->getGitProfileId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setLanguageId($inputPost->getLanguageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
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

if($inputGet->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstance();
	$specification->addAnd($dataFilter);
	$admin = new Admin(null, $database);
	try{
		$admin->findOne($specification);
		if($admin->issetAdminId())
		{
$appEntityLanguage = new AppEntityLanguage(new Admin(), $appConfig, $currentUser->getLanguageId());
require_once __DIR__ ."/inc.app/simple-header.php";
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
							<input class="form-control" type="text" name="username" id="username" value="<?php echo $admin->getUsername();?>" autocomplete="off" readonly/>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getPassword();?></td>
						<td>
							<input class="form-control" type="password" name="password" id="password" value="" autocomplete="off"/>
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
						<td><?php echo $appEntityLanguage->getGitProfile();?></td>
						<td>
							<select class="form-control" name="git_profile_id" id="git_profile_id">
								<option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
								<?php echo AppFormBuilder::getInstance()->createSelectOption(new GitProfileMin(null, $database), 
								PicoSpecification::getInstance()
									->addAnd(new PicoPredicate(Field::of()->active, true))
									->addAnd(new PicoPredicate(Field::of()->draft, false))
									->addAnd(new PicoPredicate(Field::of()->adminId, $admin->getAdminId())), 
								PicoSortable::getInstance()
									->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
									->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
								Field::of()->gitProfileId, Field::of()->name, $admin->getGitProfileId())
								; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getLanguageId();?></td>
						<td>
							<select class="form-control" name="language_id" id="language_id" data-value="<?php echo $admin->getLanguage();?>">
							<?php
                            $languages = $appConfig->getLanguages();
                            foreach($languages as $language)
                            {
                                if($language->getCode() != null && $language->getName() != null)
                                {
                                    ?>
									<option value="<?php echo $language->getCode();?>"<?php echo $language->getCode() == $admin->getLanguageId() ? ' selected' : '';?>><?php echo $language->getName();?></option>
                                    <?php
                                }
                            }
                            ?>
							</select>
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
require_once __DIR__ ."/inc.app/simple-footer.php";
	}
	catch(Exception $e)
	{
require_once __DIR__ ."/inc.app/simple-header.php";
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once __DIR__ ."/inc.app/simple-footer.php";
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
		$admin->findOne($specification, null, $subqueryMap);
		if($admin->issetAdminId())
		{
$appEntityLanguage = new AppEntityLanguage(new Admin(), $appConfig, $currentUser->getLanguageId());
require_once __DIR__ ."/inc.app/simple-header.php";
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
						<td><?php echo $appEntityLanguage->getGitProfile();?></td>
						<td><?php echo $admin->issetGitProfile() ? $admin->getGitProfile()->getName() : "";?></td>
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
						<td><?php echo $admin->dateFormatTimeCreate($appConfig->getDateFormatDetail());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getTimeEdit();?></td>
						<td><?php echo $admin->dateFormatTimeEdit($appConfig->getDateFormatDetail());?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminCreate();?></td>
						<td><?php echo $admin->issetCreator() ? $admin->getCreator()->getName() : "";?></td>
					</tr>
					<tr>
						<td><?php echo $appEntityLanguage->getAdminEdit();?></td>
						<td><?php echo $admin->issetEditor() ? $admin->getEditor()->getName() : "";?></td>
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
                            <button type="button" class="btn btn-primary" onclick="window.location='../';"><?php echo $appLanguage->getButtonHome();?></button>
                            <button type="button" class="btn btn-warning" onclick="window.location='logout.php';"><?php echo $appLanguage->getButtonLogout();?></button>

						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php 
require_once __DIR__ ."/inc.app/simple-footer.php";
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
require_once __DIR__ ."/inc.app/simple-header.php";
		// Do somtething here when exception
		?>
		<div class="alert alert-danger"><?php echo $e->getMessage();?></div>
		<?php 
require_once __DIR__ ."/inc.app/simple-footer.php";
	}
}
