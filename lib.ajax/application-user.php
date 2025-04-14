<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicAppTemplate\Entity\App\AppAdminLevelImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

/**
 * Clean up admin role from the database.
 * 
 * This function deletes admin roles that do not have an admin level or module.
 *
 * @param PicoDatabase $database The database connection.
 * @throws Exception If an error occurs during the operation.
 * @return int The number of deleted admin roles.
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

function generateRole($adminLevelId, $database)
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
        
        if($inputPost->getAction() == "set-user-role")
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
        
        try
        {
            $userFinder = new AppAdminImpl(null, $database);
            $pageData = $userFinder->findAll();
            if($pageData->getTotalResult() == 0)
            {
                // Create new user with superuser level
                $userFinder = new AppAdminImpl(null, $database);
                $userFinder->setUsername($userName);
                $userFinder->setPassword($userPassword);
                $userFinder->setName($userFullName);
                $userFinder->setLanguageId($userLanguageId);
                $userFinder->setActive($userActive);
                $userFinder->setTimeCreate($now);
                $userFinder->setTimeUpdate($now);
                $userFinder->setIpCreate($ip);
                $userFinder->setIpUpdate($ip);
                $userFinder->setAdminLevelId($adminLevelId);
                $userFinder->insert();
            }
        }
        catch(Exception $e)
        {
            // Do nothing
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

                        if($inputPost->getAction() == "reset-user-password")
                        {
                            // Reset password
                            $username = $admin->trimUsername();
                            // Reset password
                            $userPassword = sha1(sha1($username));
                            $admin->setPassword($userPassword);
                            $admin->update();
                            generateRole($adminLevelId, $database);
                        }
                        else if($inputPost->getAction() == "delete")
                        {
                            // Delete user
                            $admin->delete();
                        }
                        else if($inputPost->getAction() == "active")
                        {
                            // Active user
                            $admin->setActive(true);
                            $admin->update();
                        }
                        else if($inputPost->getAction() == "deactive")
                        {
                            // Deactive user
                            $admin->setActive(false);
                            $admin->update();
                        }
                        else if($inputPost->getAction() == "set-user-role")
                        {
                            $adminLevelId = $admin->getAdminLevelId();
                            generateRole($adminLevelId, $database);
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