<?php

namespace MagicAppTemplate;

use Exception;
use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminRoleMinImpl;
use MagicAppTemplate\Entity\App\AppModuleGroupImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicAppTemplate\Entity\App\AppModuleMultiLevelImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;

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
 */
class AppMultiLevelMenuTool
{
    /**
     * Database instance used for module and role operations.
     *
     * @var PicoDatabase
     */
    private $database;

    /**
     * Constructor to initialize the tool with a PicoDatabase instance.
     *
     * @param PicoDatabase $database The database instance to be used for operations.
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Retrieves the parent module of a given module by its ID.
     * Attempts to get it either directly from the module object (if already loaded)
     * or by loading it from the database using the parent module ID.
     *
     * @param string $moduleId The ID of the module for which to find the parent.
     * @return AppModuleMultiLevelImpl|null The parent module instance, or null if not found.
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
     * Retrieves a module by its ID.
     * If the module is not found in the database, an empty instance of
     * `AppModuleMultiLevelImpl` is returned, preventing errors.
     *
     * @param string $moduleId The ID of the module to retrieve.
     * @return AppModuleMultiLevelImpl The module instance (empty if not found).
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
     * Retrieves an administrative role for a given module and admin level.
     * If the role is not found, an empty `AppAdminRoleMinImpl` instance is returned.
     *
     * @param string $moduleId The ID of the module associated with the role.
     * @param string $adminLevelId The ID of the admin level associated with the role.
     * @return AppAdminRoleMinImpl The role instance (empty if not found).
     */
    public function getRole($moduleId, $adminLevelId)
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
     * Retrieves the parent role of the given child role by identifying its parent module.
     * This method is essential for navigating the role hierarchy based on the module hierarchy.
     *
     * @param AppAdminRoleMinImpl $childRole The child role instance whose parent is to be found.
     * @return AppAdminRoleMinImpl|null The parent role instance, or null if no parent module or role is found.
     */
    public function getParentRole($childRole)
    {
        $appAdminRole = new AppAdminRoleMinImpl(null, $this->database);
        try {
            if ($childRole->issetModuleId() && $childRole->issetAdminLevelId()) {
                $parentModule = $this->getParentModule($childRole->getModuleId());
                if ($parentModule != null) {
                    // Note: The original code had `$parentModule()` which seems like a typo.
                    // Assuming it should be `$parentModule->getModuleId()`.
                    $appAdminRole->findOneByModuleIdAndAdminLevelId($parentModule->getModuleId(), $childRole->getAdminLevelId());
                    return $appAdminRole;
                }
            }
        } catch (Exception $e) {
            // Silently ignore if parent module or role is not found during the process.
        }
        return null;
    }

    /**
     * Copies all permission flags from a child role to a parent role.
     *
     * If the `$parentRole` provided is null, a new `AppAdminRoleMinImpl` instance
     * will be created, initialized with metadata (such as admin info, IP, and
     * timestamps) from the child role, and then inserted into the database.
     *
     * If the `$parentRole` already exists, its permission values will be updated
     * to include any `true` values from the child role. Only permissions explicitly
     * set to `true` in the child role are propagated upwards to the parent.
     *
     * After copying permissions, the role will be either inserted (if new) or
     * updated (if existing) in the database.
     *
     * @param AppAdminRoleMinImpl $childRole The role to copy permissions from.
     * @param AppAdminRoleMinImpl|null $parentRole The role to update, or null to create a new one.
     * @return AppAdminRoleMinImpl The resulting parent role, either updated or newly created, with merged permissions.
     */
    public function copyPermissionsFromChild($childRole, $parentRole = null)
    {
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

                $now = date('Y-m-d H:i:s');
                $parentRole->setTimeCreate($now);
                $parentRole->setTimeEdit($now);
                $parentRole->setAdminCreate($childRole->getAdminCreate());
                $parentRole->setAdminEdit($childRole->getAdminEdit());
                $parentRole->setIpCreate($childRole->getIpCreate());
                $parentRole->setIpEdit($childRole->getIpEdit());
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
     * Creates parent modules for modules that currently do not have one assigned.
     * This method iterates through all modules and, for those without an existing
     * parent, attempts to create a new parent module based on either a specified
     * parent module ID or the module's group. It also links the child module
     * to its newly created parent.
     *
     * @return int The number of new parent modules successfully created.
     */
    public function createParentModule()
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
                if ($appModule->issetParentModule() || $appModule->issetParentModuleId()) {
                    continue; // Skip if a parent is already linked or specified
                }

                $parentModule = new AppModuleImpl(null, $this->database);
                $moduleGroupId = $appModule->getModuleGroupId();

                try {
                    // Attempt to create parent module based on module group if available
                    $moduleGroup = new AppModuleGroupImpl(null, $this->database);
                    $moduleGroup->find($moduleGroupId);

                    $parentModule->setModuleId($moduleGroup->getModuleGroupId());
                    $parentModule->setName('Parent of ' . $moduleGroup->getName());
                    $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $moduleGroup->getName())));
                    $parentModule->setMenu(true);
                    $parentModule->setSortOrder($sortOrder);
                    $parentModule->insert();
                    $created++;
                    $sortOrder++;

                    // Link the child module to the newly created parent
                    $appModule->setParentModuleId($parentModule->getModuleId());
                    $appModule->update();
                } catch (Exception $e) {
                    // If module group lookup or insert fails, fall back to using module info
                    // This creates a generic parent based on the child module's own name/code.
                    $parentModule->setModuleId($appModule->getModuleId() . '-parent'); // Use a distinct ID
                    $parentModule->setName('Parent of ' . $appModule->getName());
                    $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $appModule->getName())));
                    $parentModule->setMenu(true);
                    $parentModule->setSortOrder($sortOrder);

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
        } catch (Exception $e) {
            // Silently ignore if fetching all modules fails.
            // Log this error for debugging if necessary.
        }

        return $created;
    }
}
