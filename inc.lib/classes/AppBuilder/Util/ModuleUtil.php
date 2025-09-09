<?php

namespace AppBuilder\Util;

use Exception;
use MagicAdmin\Entity\Data\Module;
use MagicObject\Request\InputPost;

/**
 * Utility class for handling Module entity operations.
 *
 * This class provides helper methods related to the creation and
 * persistence of Module entities within a specific application.
 *
 * @package AppBuilder\Util
 */
class ModuleUtil
{
    /**
     * Save a module entity with metadata.
     *
     * This method prepares and persists a Module object with the provided
     * application ID, request data, and metadata such as creation time,
     * admin, and IP address. If the operation fails, it will return false.
     *
     * The module ID is generated as an MD5 hash of application ID,
     * module file, and target directory.
     *
     * @param string    $applicationId  The ID of the application this module belongs to.
     * @param Module    $module         The Module entity instance to populate and save.
     * @param InputPost $request        The request object containing module input data.
     * @param string    $timeCreate     The creation timestamp (Y-m-d H:i:s).
     * @param string    $adminCreate    The admin ID who creates this module.
     * @param string    $ipCreate       The IP address from which the module is created.
     *
     * @return bool Returns true if the module is successfully saved, false otherwise.
     */
    public static function saveModule($applicationId, $module, $request, $timeCreate, $adminCreate, $ipCreate)
    {
        $moduleFile = $request->getModuleFile();
        $target = $request->getTarget();
        $moduleName = $request->getModuleName();
        $moduleCode = $request->getModuleCode();

        // Generate unique module ID
        $moduleId = md5(
            $applicationId
            ."-".
            $moduleFile
            ."-".
            $target
        );

        // Populate module properties
        $module->setModuleId($moduleId);
        $module->setApplicationId($applicationId);
        $module->setName($moduleName);
        $module->setModuleCode($moduleCode);
        $module->setFileName($moduleFile);
        $module->setDirectory($target);

        // Metadata
        $module->setTimeCreate($timeCreate);
        $module->setTimeEdit($timeCreate);
        $module->setAdminCreate($adminCreate);
        $module->setAdminEdit($adminCreate);
        $module->setIpCreate($ipCreate);
        $module->setIpEdit($ipCreate);

        try {
            $module->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
