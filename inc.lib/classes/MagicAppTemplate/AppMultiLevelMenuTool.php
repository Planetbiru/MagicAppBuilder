<?php

namespace MagicAppTemplate;

use Exception;
use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleMinImpl;
use MagicAppTemplate\Entity\App\AppModuleGroupImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicAppTemplate\Entity\App\AppModuleMultiLevelImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\SetterGetter;

/**
 * AppMultiLevelMenuTool
 *
 * This class is a utility designed to manage and maintain the hierarchical structure
 * of modules and their associated roles within an application, specifically focusing
 * on multi-level menu functionalities. It provides methods for retrieving, creating,
 * and updating modules and their administrative roles, ensuring proper relationships
 * and permissions are maintained across a multi-level menu system.
 *
 * It relies on a `PicoDatabase` instance for all database operations, allowing it
 * to interact with the application's module and role data. Key functionalities
 * include:
 * - Retrieving parent modules and roles.
 * - Automatically creating parent modules for orphaned modules based on their
 * parent ID or module group.
 * - Copying and propagating permission flags from child roles to parent roles,
 * ensuring consistent access control across the menu hierarchy.
 *
 * This tool is particularly useful in applications that require dynamic and flexible
 * multi-level navigation menus where modules can be nested, and administrative
 * access roles need to be managed consistently across these levels.
 * 
 * @package MagicAppTemplate
 */
class AppMultiLevelMenuTool
{
    /**
     * Database instance used for all module and role operations.
     * This dependency is injected via the constructor.
     *
     * @var PicoDatabase
     */
    private $database;

    /**
     * Constructor to initialize the AppMultiLevelMenuTool with a database instance.
     *
     * @param PicoDatabase $database The database instance to be used for all operations
     * within this tool.
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Retrieves the parent module of a given module by its ID.
     *
     * This method first attempts to get the parent module object if it's already
     * loaded within the `$appModule` instance. If not, it tries to load the parent
     * module from the database using the `parent_module_id` field of the current module.
     *
     * @param string $moduleId The ID of the child module for which to find the parent.
     * @return AppModuleMultiLevelImpl|null The parent module instance if found, or `null` if no parent
     * is linked or loaded.
     */
    public function getParentModule($moduleId)
    {
        $parentModule = null;
        $appModule = $this->getModule($moduleId);
        if ($appModule->issetParentModule()) {
            $parentModule = $appModule->getParentModule();
        } else if ($appModule->issetParentModuleId()) {
            $parentModule = $this->getModule($appModule->getParentModuleId());
        }
        return $parentModule;
    }

    /**
     * Retrieves a module by its unique ID.
     *
     * If the module with the given ID is not found in the database, an empty
     * instance of `AppModuleMultiLevelImpl` is returned. This prevents errors
     * from attempting to access properties of a null object.
     *
     * @param string $moduleId The ID of the module to retrieve.
     * @return AppModuleMultiLevelImpl An instance of the module. It will be an empty
     * instance if the module is not found in the database.
     */
    public function getModule($moduleId)
    {
        $appModule = new AppModuleMultiLevelImpl(null, $this->database);
        try {
            $appModule->find($moduleId);
        } catch (Exception $e) {
            // Silently ignore if module is not found. An empty instance is returned.
        }
        return $appModule;
    }

    /**
     * Retrieves an administrative role based on its associated module ID and admin level ID.
     *
     * This method queries the database to find a specific admin role. If no matching
     * role is found, an empty `AppAdminRoleMinImpl` instance is returned, preventing
     * errors when accessing its properties.
     *
     * @param string $moduleId   The ID of the module linked to the role.
     * @param string $adminLevelId The ID of the administrative level for the role.
     * @return AppAdminRoleMinImpl An instance of the administrative role. It will be an empty
     * instance if the role is not found.
     */
    public function getRoleByModuleAndAdminLevel($moduleId, $adminLevelId)
    {
        $appRole = new AppAdminRoleMinImpl(null, $this->database);
        try {
            $appRole->findOneByModuleIdAndAdminLevelId($moduleId, $adminLevelId);
        } catch (Exception $e) {
            // Silently ignore if role is not found. An empty instance is returned.
        }
        return $appRole;
    }

    /**
     * Retrieves the parent administrative role for a given child role.
     *
     * This method first identifies the parent module of the `$childRole` using `getParentModule()`.
     * If a parent module is found, it then attempts to retrieve the corresponding
     * parent role based on the parent module's ID and the child role's admin level ID.
     *
     * @param AppAdminRoleMinImpl $childRole The child role instance for which to find the parent.
     * @return AppAdminRoleMinImpl|null The parent role instance, or `null` if no parent module exists
     * or if no corresponding parent role is found.
     */
    public function getParentRole($childRole)
    {
        $appAdminRole = new AppAdminRoleMinImpl(null, $this->database);
        try {
            if ($childRole->issetModuleId() && $childRole->issetAdminLevelId()) {
                $parentModule = $this->getParentModule($childRole->getModuleId());
                if ($parentModule != null) {
                    // Assuming $parentModule->getModuleId() is the correct method to get the ID.
                    $appAdminRole->findOneByModuleIdAndAdminLevelId($parentModule->getModuleId(), $childRole->getAdminLevelId());
                    return $appAdminRole;
                }
            }
        } catch (Exception $e) {
            // Silently ignore if parent module or role is not found during the process.
            // Returning null explicitly handles the not-found case.
        }
        return null;
    }

    /**
     * Updates administrative roles for a specific admin level by propagating permissions.
     *
     * This method fetches all roles associated with the given `$adminLevelId`.
     * For each role, it attempts to find its parent role. If a parent role exists,
     * permissions are copied from the child role to the parent role using
     * `copyPermissionsFromChild()`. This ensures that parent roles inherit
     * necessary permissions from their children in the menu hierarchy.
     *
     * @param string $adminLevelId The ID of the admin level whose roles need to be updated.
     * @param SetterGetter $currentAction Current action information, contains user ID, IP address, and time
     * @return array An empty array if an exception occurs during the process, otherwise implicitly void.
     * (Note: The method currently returns an empty array on exception; consider returning void or bool for clear success/failure).
     */
    public function updateRolesByAdminLevelId($adminLevelId, $currentAction = null)
    {
        if(!isset($currentAction))
        {
            $currentAction = new SetterGetter();
        }
        try {
            $roles = $this->getRoles($adminLevelId);
            foreach ($roles as $role) {
                $parentRole = $this->getParentRole($role);
                $this->copyPermissionsFromChild($role, $parentRole, $currentAction);
            }
        } catch(Exception $e) {
            return array();
        }
    }

    /**
     * Updates a single administrative role by propagating its permissions to its parent.
     *
     * This method retrieves a specific role by its ID. If the role exists and has a parent
     * role, permissions are copied from the current role to its parent using
     * `copyPermissionsFromChild()`. This is useful for ensuring permission consistency
     * when an individual role is modified.
     *
     * @param string $adminRoleId The ID of the administrative role to update.
     * @return array An empty array if an exception occurs during the process, otherwise implicitly void.
     * (Note: The method currently returns an empty array on exception; consider returning void or bool for clear success/failure).
     */
    public function updateParentRole($adminRoleId)
    {
        try {
            $role = new AppAdminRoleImpl(null, $this->database);
            $role->find($adminRoleId);

            $parentRole = $this->getParentRole($role);
            if ($parentRole !== null) {
                $this->copyPermissionsFromChild($role, $parentRole);
            }

        } catch(Exception $e) {
            return array();
        }
    }

    /**
     * Retrieves all administrative roles for a given admin level.
     *
     * This method queries the database to fetch all roles associated with the
     * specified administrative level ID. It returns a collection of `AppAdminRoleMinImpl`
     * instances.
     *
     * @param string $adminLevelId The ID of the admin level for which to retrieve roles.
     * @return AppAdminRoleMinImpl[] An array of `AppAdminRoleMinImpl` objects representing
     * the roles for the specified admin level, or an empty
     * array if no roles are found or an error occurs.
     */
    public function getRoles($adminLevelId)
    {
        try {
            $appRole = new AppAdminRoleMinImpl(null, $this->database);
            $pageData = $appRole->findByAdminLevelId($adminLevelId);
            return $pageData->getResult();
        } catch(Exception $e) {
            return array();
        }
    }

    /**
     * Copies all relevant permission flags from a child role to a parent role.
     *
     * This method ensures permission propagation up the module hierarchy.
     * If `$parentRole` is provided as `null`, a new `AppAdminRoleMinImpl` instance
     * is created, populated with essential metadata (like module ID, admin level ID,
     * timestamps, and user info) derived from the child role, and then inserted
     * into the database.
     *
     * If `$parentRole` already exists (not `null`), its permission values are
     * updated. Only permissions explicitly set to `true` in the `$childRole`
     * are propagated (set to `true`) in the `$parentRole`. Permissions that are
     * `false` in the child role do not unset `true` permissions in the parent.
     *
     * After permission propagation, the parent role is either inserted (if new)
     * or updated (if existing) in the database.
     *
     * @param AppAdminRoleMinImpl $childRole  The source role from which permissions are copied.
     * @param AppAdminRoleMinImpl|null $parentRole The target role to which permissions are copied.
     * If `null`, a new parent role is created.
     * @param SetterGetter $currentAction Current action information, contains user ID, IP address, and time
     * @return AppAdminRoleMinImpl The resulting parent role instance, which is either the
     * updated existing parent role or the newly created one.
     * @throws Exception If database operations fail during insert or update of the parent role.
     */
    public function copyPermissionsFromChild($childRole, $parentRole = null, $currentAction = null)
    {
        if(!isset($currentAction))
        {
            $currentAction = new SetterGetter();
        }
        $createNew = false;
        if ($parentRole === null) {
            $parentRole = new AppAdminRoleMinImpl(null, $this->database);
            $createNew = true;
        }

        $permissions = [
            'allowedList',
            'allowedDetail',
            'allowedCreate',
            'allowedUpdate',
            'allowedDelete',
            'allowedApprove',
            'allowedSortOrder',
            'allowedExport',
        ];

        foreach ($permissions as $perm) {
            $setter = "set" . ucfirst($perm);
            $getter = "is" . ucfirst($perm);

            // Get permission from child role
            $value = $childRole->$getter();
            if ($value) {
                // Only copy if the permission value is true in the child role
                $parentRole->$setter($value);
            }
        }

        if ($createNew) {
            // Initialize and create new parent role
            $parentModule = $this->getParentModule($childRole->getModuleId());
            if ($parentModule !== null) {
                $parentRole->setModuleId($parentModule->getModuleId());
                $parentRole->setAdminLevelId($childRole->getAdminLevelId());

                $now = $this->getCurrentTime();
                $parentRole->setTimeCreate($now);
                $parentRole->setTimeEdit($now);
                $parentRole->setAdminCreate($currentAction->getUserId());
                $parentRole->setAdminEdit($currentAction->getUserId());
                $parentRole->setIpCreate($currentAction->getIp());
                $parentRole->setIpEdit($currentAction->getIp());
                $parentRole->setActive(true);

                $parentRole->insert();
            } else {
                // If no parent module is found, it might mean the child module itself
                // is at the top level or has an invalid parent module ID.
                // In a multi-level menu system, a child role should ideally have a parent module.
                // Depending on application logic, you might want to throw an exception here
                // or handle this edge case differently. For now, it will not insert.
            }
        } else {
            // Update existing parent role
            $parentRole->update();
        }

        return $parentRole;
    }
    
    /**
     * Determines whether a parent module needs to be created for the given module.
     *
     * This function returns true if the current module does not already have a parent module
     * and either a parent module ID or a module group ID is provided.
     *
     * @param object $appModule The module object to check.
     * @return bool True if a parent module needs to be created; false otherwise.
     */
    public function needToCreateParent($appModule)
    {
        return !$appModule->issetParentModule() &&
            !(
                ($appModule->getParentModuleId() === null || $appModule->getParentModuleId() === '') &&
                ($appModule->getModuleGroupId() === null || $appModule->getModuleGroupId() === '')
            );
    }

    /**
     * Creates parent modules for child modules that are currently unassigned or orphaned.
     *
     * This method iterates through all existing modules. For any module found
     * without a linked parent module (either by object or parent ID), it attempts
     * to create a new parent module. The creation logic prioritizes using an
     * existing module group ID; if that fails, it falls back to creating a generic
     * parent based on the child module's own name/code.
     *
     * Upon successful creation of a parent module, the child module's `parentModuleId`
     * is updated to link it to the newly created parent. This helps maintain a
     * complete hierarchical menu structure.
     *
     * @param SetterGetter $currentAction An object containing user/action context,
     * for example, an instance with `getUserId()` and `getIp()` methods
     * to record creation/edit metadata for new parent modules.
     * @return int The total number of new parent modules successfully created during this operation.
     */
    public function createParentModule($currentAction)
    {
        $created = 0;
        $sortOrder = 1;

        try {
            $appModuleFinder = new AppModuleMultiLevelImpl(null, $this->database);
            $sortable = PicoSortable::getInstance()
                ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC));
            $pageData = $appModuleFinder->findAll(null, null, $sortable);

            foreach ($pageData->getResult() as $appModule) {
                    

                // If module already has a parent (object loaded or ID set), skip creation.
                // The `issetParentModule()` checks if the object is already populated,
                // and `issetParentModuleId()` checks if a parent ID is assigned (even if not loaded).
                if (!$this->needToCreateParent($appModule)) {
                    continue; // Skip if a parent is already linked OR both parent_module_id and group_module_id are empty
                }           

                $parentModule = new AppModuleImpl(null, $this->database);
                $moduleGroupId = $appModule->getModuleGroupId();

                try {
                    $parentModule->find($moduleGroupId);
                    $appModule->setParentModuleId($moduleGroupId);
                    $appModule->update();
                } catch (Exception $e) {
                    try {
                        // Attempt to create parent module based on module group if available
                        $moduleGroup = new AppModuleGroupImpl(null, $this->database);
                        $moduleGroup->find($moduleGroupId);

                        $parentModule->setModuleId($moduleGroup->getModuleGroupId());
                        $parentModule->setName($moduleGroup->getName());
                        $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $moduleGroup->getName())));
                        $parentModule->setMenu(true);
                        $parentModule->setSortOrder($sortOrder);

                        $parentModule->setIcon('fa fa-folder');
                        $parentModule->setSpecialAccess(false);
                        $parentModule->setUrl('#'.$parentModule->getModuleCode());
                        $parentModule->setTarget('_self');

                        $now = $this->getCurrentTime();
                        $parentModule->setTimeCreate($now);
                        $parentModule->setTimeEdit($now);
                        $parentModule->setAdminCreate($currentAction->getUserId());
                        $parentModule->setAdminEdit($currentAction->getUserId());
                        $parentModule->setIpCreate($currentAction->getIp());
                        $parentModule->setIpEdit($currentAction->getIp());
                        $parentModule->setActive(true);

                        $parentModule->insert();
                        $created++;
                        $sortOrder++;

                        // Link the child module to the newly created parent
                        $appModule->setParentModuleId($moduleGroupId);
                        $appModule->update();
                    } catch (Exception $e) {
                        // If module group lookup or insert fails, fall back to using module info
                        // This creates a generic parent based on the child module's own name/code.
                        $parentModule->setModuleId($appModule->getModuleGroupId()); // Use a distinct ID
                        $parentModule->setName('Parent of ' . $appModule->getName());
                        $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $appModule->getName())));
                        $parentModule->setMenu(true);
                        $parentModule->setSortOrder($sortOrder);

                        $parentModule->setIcon('fa fa-folder');
                        $parentModule->setSpecialAccess(false);
                        $parentModule->setUrl('#'.$parentModule->getModuleCode());
                        $parentModule->setTarget('_self');

                        $now = $this->getCurrentTime();
                        $parentModule->setTimeCreate($now);
                        $parentModule->setTimeEdit($now);
                        $parentModule->setAdminCreate($currentAction->getUserId());
                        $parentModule->setAdminEdit($currentAction->getUserId());
                        $parentModule->setIpCreate($currentAction->getIp());
                        $parentModule->setIpEdit($currentAction->getIp());
                        $parentModule->setActive(true);

                        try {
                            $parentModule->insert();
                            $created++;
                            $sortOrder++;

                            // Link the child module to the fallback parent
                            $appModule->setParentModuleId($parentModule->getModuleId());
                            $appModule->update();
                        } catch (Exception $e) {
                            // Silently ignore if fallback parent creation also fails.
                            // Log this error if more robust error handling is needed.
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Silently ignore if fetching all modules fails.
            // Log this error for debugging if necessary.
        }

        return $created;
    }
    
    /**
     * Returns the current date and time in 'Y-m-d H:i:s' format.
     *
     * @return string The current timestamp in the format 'YYYY-MM-DD HH:MM:SS'.
     */
    public function getCurrentTime()
    {
        return date('Y-m-d H:i:s');
    }

}
