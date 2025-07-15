<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\Field;
use MagicAppTemplate\ApplicationMenu;
use MagicAppTemplate\AppMultiLevelMenuTool;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicAppTemplate\Entity\App\AppAdminLevelImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppMessageImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicAppTemplate\Entity\App\AppNotificationImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

/**
 * Clean up unused admin roles from the database.
 *
 * This function deletes all admin roles that do not have
 * an associated admin level or module assigned.
 *
 * @param PicoDatabase $database The database connection instance.
 * @return int The total number of admin roles that were deleted.
 */
function cleanUpRole($database)
{
	$deleted = 0;
	$adminRole = new AppAdminRoleImpl(null, $database);
	try
	{
		// Find all admin roles without filter
		$pageData = $adminRole->findAll();
		foreach($pageData->getResult() as $adminRole)
		{
			if(!$adminRole->issetAdminLevel() || !$adminRole->issetModule())
			{
				// Delete the admin role if it does not have an admin level or module
				$adminRole->delete();
				
				// Increment the deleted count
				$deleted++;
			}
		}
	}
	catch(Exception $e)
	{
		// Do nothing
	}
	// Return the number of deleted admin roles
	return $deleted;
}

/**
 * Set all roles under a given admin level as superuser roles.
 *
 * This function grants full permissions (CRUD, approve, export, etc.)
 * to all admin roles under the specified admin level.
 *
 * @param string $adminLevelId The ID of the admin level.
 * @param PicoDatabase $database The database connection instance.
 * @return void
 */
function setSuperuserRole($adminLevelId, $database)
{
    $adminRole = new AppAdminRoleImpl(null, $database);
    try
    {
        // Find all admin roles without filter
        $pageData = $adminRole->findByAdminLevelId($adminLevelId);
        foreach($pageData->getResult() as $adminRole)
        {
            // Set the admin role to superuser
            $adminRole->setAllowedList(true);
            $adminRole->setAllowedCreate(true);
            $adminRole->setAllowedUpdate(true);
            $adminRole->setAllowedDelete(true);
            $adminRole->setAllowedExport(true);
            $adminRole->setAllowedDetail(true);
            $adminRole->setAllowedSortOrder(true);
            $adminRole->setAllowedApprove(true);
            $adminRole->update();
            
        }
    }
    catch(Exception $e)
    {
        // Do nothing
    }
}

/**
 * Generate admin roles for all active modules under a given admin level.
 *
 * For each active module, this function checks if a corresponding admin role exists.
 * If it does, it updates the role with full permissions. If not, it creates a new one.
 * After all roles are processed, it refreshes the application menu cache.
 *
 * @param string $adminLevelId The ID of the admin level to generate roles for.
 * @param PicoDatabase $database The database connection instance.
 * @param SecretObject $appConfig Application configuration
 * @param SecretObject $currentAction Current action information, contains user ID, IP address, and time
 * @return void
 */
function generateRole($adminLevelId, $database, $appConfig, $currentAction)
{
    // Generate admin role
	// for all active modules
	// for the selected admin level
	// and set the database connection
	// to the instance
	$adminRole = new AppAdminRoleImpl(null, $database);
	$moduleFinder = new AppModuleImpl(null, $database);
	$specification1 = PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true));
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
                    $adminRole
					->setAllowedList(true)
					->setAllowedDetail(true)
					->setAllowedCreate(true)
					->setAllowedUpdate(true)
					->setAllowedDelete(true)
					->setAllowedApprove(true)
					->setAllowedSortOrder(true)
					->setAllowedExport(true)
					->setActive(true)
					->update();
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
					->setAllowedList(true)
					->setAllowedDetail(true)
					->setAllowedCreate(true)
					->setAllowedUpdate(true)
					->setAllowedDelete(true)
					->setAllowedApprove(true)
					->setAllowedSortOrder(true)
					->setAllowedExport(true)
					->setActive(true)
					->insert();
				}
			}
            

                
            // Create parent module
            if($appConfig->issetApplication() && $appConfig->getApplication()->getMultiLevelMenu())
            {
                $appMultiLevelMenuTool = new AppMultiLevelMenuTool(null, $database);
                $appMultiLevelMenuTool->createParentModule($currentAction);
                $appMultiLevelMenuTool->updateRolesByAdminLevelId($adminLevelId, $currentAction);
            }
            
            // Update the application menu cache
            $applicationMenu = new ApplicationMenu($database, null, null, null, null, null);
            // Delete the menu cache for the specified admin level ID
            $applicationMenu->deleteMenuCache($adminLevelId);
		}
		catch(Exception $e)
		{
			// Do nothing
		}
	}
}

$inputPost = new InputPost();
$inputGet = new InputGet();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$now = date("Y-m-d H:i:s");

if($applicationId != null)
{
    $menuAppConfig = new SecretObject();
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        
        $application->findOneByApplicationId($applicationId);
        
        $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
        if(file_exists($appConfigPath))
        {
            $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
        }
        
        // Database connection for the application
        $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
        try
        {
            $database->connect();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
        
        if($inputPost->getUserAction() == "set-user-role")
        {
            // Clean up admin role from the database
            $deleted = cleanUpRole($database);
        }
        
        $adminLevelId = "superuser";
        $adminLevelName = "Super User";
        
        $now = date("Y-m-d H:i:s");
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = "superuser";
        $userName = "superuser";
        $userPassword = sha1(sha1("superuser"));
        $userFullName = "Super User";
        $userLanguageId = "en";
        $userActive = true;
        $userBlocked = false;
        $adminLevel = new AppAdminLevelImpl(null, $database);
        
        try
        {
            $adminLevel->findOneByAdminLevelId($adminLevelId);
        }
        catch(Exception $e)
        {
            // Create admin level if not exists
            $adminLevel = new AppAdminLevelImpl(null, $database);
            $adminLevel->setAdminLevelId($adminLevelId);
            $adminLevel->setName($adminLevelName);
            $adminLevel->setSpecialAccess(true);
            $adminLevel->setActive(true);
            $adminLevel->setTimeCreate($now);
            $adminLevel->setTimeUpdate($now);
            $adminLevel->setIpCreate($ip);
            $adminLevel->setIpUpdate($ip);
            $adminLevel->setAdminCreate($userId);
            $adminLevel->setAdminUpdate($userId);
            $adminLevel->insert();
        }
        $userFound = false;
        try
        {
            $userFinder = new AppAdminImpl(null, $database);
            $pageData = $userFinder->findAll();
            $userFound = $pageData->getTotalResult() > 0;

            foreach($pageData->getResult() as $admin)
            {
                // Update user with superuser level
                if($admin->getAdminLevelId() == null || $admin->getAdminLevelId() == "")
                {
                    
                    $adminLevelId = "superuser";
                }
                else
                {
                    // Set the admin level to superuser
                    $adminLevelId = $admin->getAdminLevelId();
                }
                $admin->setAdminLevelId($adminLevelId);
                $admin->update();
            }
        }
        catch(Exception $e)
        {
            // Do nothing
        }
        if(!$userFound)
        {
            // Create new user with superuser level
            $userFinder = new AppAdminImpl(null, $database);
            $userFinder->setUsername($userName);
            $userFinder->setPassword($userPassword);
            $userFinder->setSpecialAccess(true);
            $userFinder->setName($userFullName);
            $userFinder->setLanguageId($userLanguageId);
            $userFinder->setActive($userActive);
            $userFinder->setBlocked($userBlocked);
            $userFinder->setTimeCreate($now);
            $userFinder->setTimeUpdate($now);
            $userFinder->setIpCreate($ip);
            $userFinder->setIpUpdate($ip);
            $userFinder->setAdminLevelId($adminLevelId);
            $userFinder->insert();

            // Notification for user
            $notification = new AppNotificationImpl(null, $database);
            $notificationId = $notification->currentDatabase()->generateNewId();
            $notification->setNotificationId($notificationId);
            $notification->setNotificationType("general");
            $notification->setAdminGroup("admin");
            $notification->setAdminId($userFinder->getAdminId());
            $notification->setIcon("bell");
            $notification->setSubject("User Account Created");
            $notification->setContent("Your account has been created");
            $notification->setLink("notification.php?user_action=detail&notification_id=$notificationId");
            $notification->setIsRead(false);
            $notification->setTimeCreate($now);
            $notification->setTimeUpdate($now);
            $notification->setIpCreate($ip);
            $notification->setIpUpdate($ip);
            $notification->insert();

            // Notification for dummy user
            $notification2 = new AppNotificationImpl($notification, $database);
            $notificationId2 = $notification2->currentDatabase()->generateNewId();
            $notification2->setNotificationId($notificationId2);
            $notification2->setAdminId("superuser");
            $notification2->setLink("notification.php?user_action=detail&notification_id=$notificationId2");
            $notification2->insert();

            // Message for user
            $message = new AppMessageImpl(null, $database);
            $messageId = $message->currentDatabase()->generateNewId();
            $message->setMessageId($messageId);
            $message->setMessageDirection('in');
            $message->setSenderId($userFinder->getAdminId());
            $message->setReceiverId($userFinder->getAdminId());
            $message->setSubject("User Account Created");
            $message->setContent("Your account has been created");
            $message->setIsRead(false);
            $message->setTimeCreate($now);
            $message->setTimeUpdate($now);
            $message->setIpCreate($ip);
            $message->setIpUpdate($ip);
            $message->insert();

            // Message for dummy user
            $message2 = new AppMessageImpl($message, $database);
            $messageId2 = $message->currentDatabase()->generateNewId();
            $message2->setMessageId($messageId2);
            $message2->setSenderId("superuser");
            $message2->setReceiverId("superuser");
            $message2->insert();
        }

        
        if($inputPost->getAdminId() != null && $inputPost->countableAdminId())
        {
            $adminIds = $inputPost->getAdminId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
            $adminFinder = new AppAdminImpl(null, $database);
            try
            {
                $specs = PicoSpecification::getInstance()
                    ->addAnd(PicoPredicate::getInstance()->in(Field::of()->adminId, $adminIds))
                    ;
                
                $pageData = $adminFinder->findAll($specs);
                if($pageData->getTotalResult())
                {
                    // Reset password for selected users
                    foreach($pageData->getResult() as $admin)
                    {

                        if($inputPost->getUserAction() == "reset-user-password")
                        {
                            // Reset password
                            $username = $admin->trimUsername();
                            // Reset password
                            $userPassword = sha1(sha1($username));
                            $admin->setPassword($userPassword);
                            $admin->update();
                            generateRole($adminLevelId, $database, $menuAppConfig, $currentAction);
                        }
                        else if($inputPost->getUserAction() == "delete")
                        {
                            // Delete user
                            $admin->delete();
                        }
                        else if($inputPost->getUserAction() == "active")
                        {
                            // Active user
                            $admin->setActive(true);
                            $admin->update();
                        }
                        else if($inputPost->getUserAction() == "deactive")
                        {
                            // Deactive user
                            $admin->setActive(false);
                            $admin->update();
                        }
                        else if($inputPost->getUserAction() == "set-user-role")
                        {
                            $adminLevelId = $admin->getAdminLevelId();
                            generateRole($adminLevelId, $database, $menuAppConfig, $currentAction);
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                // Do nothing
            }
        }
    }
    catch(Exception $e)
    {
        // Do noting
        echo "<div class='alert alert-danger'>".$e->getMessage()."</div>";
    }
}
if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
}

if($applicationId != null)
{
    $menuAppConfig = new SecretObject();
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    if(file_exists($appConfigPath))
    {
        $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
    }
    
    // Database connection for the application
    $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
    try
    {
        $database->connect();
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
    }

    try
    {
        $adminFinder = new AppAdminImpl(null, $database);
        
        $pageData = $adminFinder->findAll();
        if($pageData->getTotalResult())
        {
            echo '<table class="table table-striped table-bordered">';
            echo "<thead>";
            echo "<tr>";
            echo '<th width="8"></th>';
            echo "<th>Name</th>";
            echo "<th>Username</th>";
            echo "<th>Admin Level</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach($pageData->getResult() as $admin)
            {
                $adminId = $admin->getAdminId();
                $adminName = $admin->getName();
                $adminUsername = $admin->getUsername();
                $adminLevelId = $admin->getAdminLevelId();
                echo "<tr>";
                echo "<td>";
                echo "<input type='checkbox' class='admin_id' value='".$adminId."' style=\"margin:0\" />";
                echo "</td>";
                echo "<td>".$adminName."</td>";
                echo "<td>".$adminUsername."</td>";
                echo "<td>".($admin->issetAdminLevel() ? $admin->getAdminLevel()->getName() : "")."</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }
           
    }
    catch(Exception $e)
    {
        // Do nothing
        echo "<div class='alert alert-danger'>".$e->getMessage()."</div>";
    }
}