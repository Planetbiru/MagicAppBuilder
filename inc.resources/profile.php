<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicApp\UserAction;
use MagicApp\AppUserPermission;
use MagicAppTemplate\AppAccountSecurity;
use MagicAppTemplate\AppEntityLanguageImpl;
use MagicAppTemplate\AppIncludeImpl;
use MagicAppTemplate\AppValidatorMessage;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicAppTemplate\Entity\App\AppUserPasswordHistoryImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Exceptions\InvalidValueException;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "profile", $appLanguage->getAdministratorProfile());
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

/**
 * Checks if a given hashed password has been used previously by the admin.
 *
 * This function attempts to find a password history record for the specified admin ID 
 * and hashed password. It returns true if the password exists in the history, 
 * indicating it has been used before; otherwise, it returns false.
 *
 * @param PicoDatabase $database The database connection instance.
 * @param string $adminId The ID of the admin user.
 * @param string $hashPassword The hashed password to check against the history.
 * 
 * @return bool Returns true if the password was found in history, false otherwise.
 */
function passwordExists($database, $adminId, $hashPassword)
{
	try
	{
		$passwordHistory = new AppUserPasswordHistoryImpl(null, $database);
		$passwordHistory->findOneByAdminIdAndPassword($adminId, $hashPassword);
		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

/**
 * Creates a new password history record for the given admin user.
 *
 * This function saves the provided hashed password along with the current timestamp 
 * into the password history for the specified admin ID. 
 * It returns true on success or false if an exception occurs.
 *
 * @param PicoDatabase $database The database connection instance.
 * @param string $adminId The ID of the admin user.
 * @param string $hashPassword The hashed password to be saved.
 * 
 * @return bool Returns true if the password history record was successfully created, false otherwise.
 */
function createPasswordHistory($database, $adminId, $hashPassword)
{
	try
	{
		$passwordHistory = new AppUserPasswordHistoryImpl(null, $database);
		$passwordHistory->setAdminId($adminId);
		$passwordHistory->setPassword($hashPassword);
		$passwordHistory->setTimeCreate(date('Y-m-d H:i:s'));
		$passwordHistory->setIpCreate($_SERVER['REMOTE_ADDR']);
		$passwordHistory->insert();
		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

$dataFilter = PicoSpecification::getInstance()
	->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminId, $currentUser->getAdminId()));

if($inputPost->getUserAction() == UserAction::UPDATE)
{
	$specification = PicoSpecification::getInstanceOf(Field::of()->adminId, $currentUser->getAdminId());
	$admin = new AppAdminImpl(null, $database);
	$updater = $admin->where($specification);
	$updater->with()
		->setName($inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setGender($inputPost->getGender(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setBirthDay($inputPost->getBirthDay(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setEmail($inputPost->getEmail(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
		->setPhone($inputPost->getPhone(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true))
	;
	$updater->setAdminEdit($currentAction->getUserId());
	$updater->setTimeEdit($currentAction->getTime());
	$updater->setIpEdit($currentAction->getIp());
	$passwordUsed = false;
	try
	{
		$updater->validate(null, AppValidatorMessage::loadTemplate($currentUser->getLanguageId()));
		$updater->update();
		$adminId = $currentUser->getAdminId();

        $plainPassword = trim($inputPost->getPassword(PicoFilterConstant::FILTER_DEFAULT, false, false, true), " \t\r\n ");
        if(!empty($plainPassword))
        {
            $hashPassword = AppAccountSecurity::generateHash($appConfig, $plainPassword, 1);
			$hashPassword2 = AppAccountSecurity::generateHash($appConfig, $hashPassword, 1);

			if(passwordExists($database, $adminId, $hashPassword))
			{
				$passwordUsed = true;
			}
			else
			{
				$updater = $admin->where($specification);
				$updater
					->setPassword($hashPassword2)
					->setPasswordVersion(sha1(time().mt_rand(1000000, 9999999)))
					->update();
				$sessions->userPassword = $hashPassword;
				createPasswordHistory($database, $adminId, $hashPassword);
			}
        }

        $username = trim($inputPost->getUsername(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true), " \t\r\n ");
        if(!empty($username))
        {
            try
            {
                $adminTest = new AppAdminImpl(null, $database);
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
		if($passwordUsed)
		{
			$currentModule->redirectTo(UserAction::UPDATE, Field::of()->error, "password-has-been-used");
		}
		else
		{
			$currentModule->redirectTo(UserAction::DETAIL);
		}
	}
	catch(InvalidValueException $e)
	{
		$currentModule->setErrorMessage($e->getMessage());
		$currentModule->setErrorField($e->getPropertyName());
		$currentModule->setCurrentAction(UserAction::UPDATE);
		$currentModule->setFormData($inputPost->formData());
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
	$admin = new AppAdminImpl(null, $database);
	try{
		$admin->findOne($specification);
		if($admin->issetAdminId())
		{
$appEntityLanguage = new AppEntityLanguageImpl(new AppAdminImpl(), $appConfig, $currentUser->getLanguageId());
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
		<?php
		if($inputGet->getError() == 'password-has-been-used')
		{
			?>
			<div class="alert alert-danger"><?php echo $appLanguage->getMessagePasswordHasBeenUsed();?></div>
			<?php
		}
		?>
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
							<input class="form-control" type="text" name="username" id="username" value="<?php echo $admin->getUsername();?>" autocomplete="off" readonly />
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
	$admin = new AppAdminImpl(null, $database);
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
$appEntityLanguage = new AppEntityLanguageImpl(new AppAdminImpl(), $appConfig, $currentUser->getLanguageId());
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