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
     * @param PicoDatabase $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Retrieves the parent module of a given module by its ID.
     * Attempts to get it either directly from the module object
     * or by loading it using the parent module ID.
     *
     * @param string $moduleId The module ID to look up.
     * @return AppModuleMultiLevelImpl|null The parent module, or null if not found.
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
     * If not found, returns an empty instance.
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
            // Silently ignore if module is not found
        }
        return $appModule;
    }

    /**
     * Retrieves the role for a given module and admin level.
     *
     * @param string $moduleId The module ID.
     * @param string $adminLevelId The admin level ID.
     * @return AppAdminRoleMinImpl The role instance (empty if not found).
     */
    public function getRole($moduleId, $adminLevelId)
    {
        $appRole = new AppAdminRoleMinImpl(null, $this->database);
        try {
            $appRole->findOneByModuleIdAndAdminLevelId($moduleId, $adminLevelId);
        } catch (Exception $e) {
            // Silently ignore if role is not found
        }
        return $appRole;
    }

    /**
     * Retrieves the parent role of the given child role using its parent module.
     *
     * @param AppAdminRoleMinImpl $childRole The child role instance.
     * @return AppAdminRoleMinImpl|null The parent role instance or null if not found.
     */
    public function getParentRole($childRole)
    {
        $appAdminRole = new AppAdminRoleMinImpl(null, $this->database);
        try {
            if ($childRole->issetModuleId() && $childRole->issetAdminLevelId()) {
                $parentModule = $this->getParentModule($childRole->getModuleId());
                if ($parentModule != null) {
                    $appAdminRole->findOneByModuleIdAndAdminLevelId($parentModule()->getModuleId(), $childRole->getAdminLevelId());
                    return $appAdminRole;
                }
            }
        } catch (Exception $e) {
            // Silently ignore if parent module is not found
        }
        return null;
    }

    /**
     * Copies all permission flags from a child role to a parent role.
     * 
     * If the parent role is null, a new role instance will be created, initialized,
     * and inserted into the database using metadata from the child role (such as admin info, IP, and timestamps).
     * 
     * If the parent role already exists, its permission values will be updated to include any
     * `true` values from the child role. Only permissions set to `true` in the child role are copied.
     * 
     * After copying, the role will be either inserted (if new) or updated (if existing).
     *
     * @param AppAdminRoleMinImpl $childRole The role to copy permissions from.
     * @param AppAdminRoleMinImpl|null $parentRole The role to update, or null to create a new one.
     * @return AppAdminRoleMinImpl The resulting parent role, either updated or newly created.
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
                // Only copy if value is true
                $parentRole->$setter($value);
            }
        }

        if($createNew)
        {
            // Create new role
            $parentRole->setModuleId($childRole->getModuleId()); 
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
        }
        else
        {
            $parentRole->update();
        }

        return $parentRole;
    }

    /**
     * Creates parent modules for modules that don't have one.
     * This includes creating a new parent module based on either the
     * parent module ID or module group if no parent is assigned.
     *
     * @return int The number of parent modules created.
     */
    public function createParentModule()
    {
        $created = 0;
        $parentModule = new AppModuleMultiLevelImpl(null, $this->database);

        $sortable = PicoSortable::getInstance()
            ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC));

        $sortOrder = 1;
        try {
            $appModuleFinder = new AppModuleMultiLevelImpl(null, $this->database);
            $pageData = $appModuleFinder->findAll(null, null, $sortable);

            foreach ($pageData->getResult() as $appModule) {
                // If module already has a parent, reuse it
                if ($appModule->issetParentModule()) {
                    $parentModule = $appModule->getParentModule();
                } else if ($appModule->issetParentModuleId()) {
                    // Create parent module using specified ID
                    $parentModule = new AppModuleImpl(null, $this->database);
                    $parentModule->setModuleId($appModule->getParentModuleId());
                    $parentModule->setName('Parent of ' . $appModule->getName());
                    $parentModule->setModuleCode('g-' . $appModule->getModuleCode());
                    $parentModule->setMenu(true);
                    $parentModule->setSortOrder($sortOrder);
                    $sortOrder++;
                    try {
                        $parentModule->insert();
                        $created++;
                    } catch (Exception $e) {
                        // Silently ignore if insert fails
                    }
                } else {
                    $parentModule = new AppModuleImpl(null, $this->database);

                    // Attempt to create parent module based on module group
                    $moduleGroup = new AppModuleGroupImpl(null, $this->database);
                    try {
                        $moduleGroup->find($appModule->getModuleGroupId());

                        $parentModule->setModuleId($moduleGroup->getModuleGroupId());
                        $parentModule->setName('Parent of ' . $moduleGroup->getName());
                        $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $moduleGroup->getName())));
                        $parentModule->setMenu(true);
                        $parentModule->setSortOrder($sortOrder);
                        $parentModule->insert();
                        $created++;
                        $sortOrder++;

                        // Link the module to the new parent
                        $appModule->setParentModuleId($parentModule->getModuleId());
                        $appModule->update();
                    } catch (Exception $e) {
                        // Fallback: use module info if module group lookup fails
                        $parentModule->setModuleId($appModule->getModuleGroupId());
                        $parentModule->setName($appModule->getName());
                        $parentModule->setModuleCode('g-' . strtolower(str_replace(' ', '-', $appModule->getName())));
                        $parentModule->setMenu(true);
                        $parentModule->setSortOrder($sortOrder);
                        $sortOrder++;

                        // Link the module to the fallback parent
                        $appModule->setParentModuleId($parentModule->getModuleId());
                        $appModule->update();
                    }
                }
            }
        } catch (Exception $e) {
            // Silently ignore if fetch fails
        }

        return $created;
    }
}
