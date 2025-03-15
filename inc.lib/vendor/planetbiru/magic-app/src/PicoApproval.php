<?php

namespace MagicApp;

use Exception;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\SetterGetter;

class PicoApproval
{
    const APPROVAL_APPROVE = 1;
    const APPROVAL_REJECT = 2;

    /**
     * Master entity
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * Entity information
     *
     * @var EntityInfo
     */
    private $entityInfo;

    /**
     * Entity approval information
     *
     * @var EntityApvInfo
     */
    private $entityApvInfo;

    /**
     * Callback for validation
     *
     * @var callable|null
     */
    private $callbackValidation;

    /**
     * Callback after approval
     *
     * @var callable|null
     */
    private $callbackAfterApprove;

    /**
     * Callback after rejection
     *
     * @var callable|null
     */
    private $callbackAfterReject;

    /**
     * Current user performing the action
     *
     * @var string
     */
    private $currentUser;

    /**
     * Current time of the action
     *
     * @var string
     */
    private $currentTime;

    /**
     * Current IP address of the user
     *
     * @var string
     */
    private $currentIp;

    /**
     * Constructor
     *
     * Initializes the approval handler with the provided entity, entity information, 
     * approval status information, and an optional validation callback.
     *
     * This constructor is used to set up the necessary components to manage the approval 
     * and rejection process for the given entity. The validation callback, if provided, 
     * will be used during the approval process to perform custom validation checks before 
     * proceeding with the approval or rejection of the entity.
     *
     * @param MagicObject $entity The master entity being approved or rejected.
     * @param EntityInfo $entityInfo Information about the entity, including metadata like 
     *        approval status, creation time, and edit history.
     * @param EntityApvInfo $entityApvInfo Information about the entity's approval status 
     *        and related attributes.
     * @param callable|null $callbackValidation Optional validation callback to perform 
     *        custom validation before proceeding with the approval or rejection.
     */
    public function __construct($entity, $entityInfo, $entityApvInfo, $callbackValidation = null)
    {
        $this->entity = $entity;
        $this->entityInfo = $entityInfo;
        $this->entityApvInfo = $entityApvInfo;
        $this->callbackValidation = $callbackValidation;
    }

    /**
     * Approves the entity based on its current status and the provided parameters.
     *
     * This method processes the approval of an entity, updating its state based on the 
     * required approval action (e.g., create, activate, deactivate, update, delete). 
     * It also supports optional pre- and post-approval callback actions.
     *
     * The approval actions include:
     * - **CREATE**: Finalizes the entity by removing the draft status and resetting the approval ID.
     * - **ACTIVATE**: Approves the activation of the entity.
     * - **DEACTIVATE**: Approves the deactivation of the entity.
     * - **UPDATE**: Updates the entity by copying specified columns from the approval entity.
     * - **DELETE**: Deletes the entity, with an option to move data to a trash entity.
     *
     * If an `approvalCallback` is provided, it executes specific actions before and after the approval process.
     *
     * @param string[]        $columnToBeCopied Columns to copy from the approval entity (for updates).
     * @param MagicObject|null $entityApv       The approval entity, used for update and copy operations.
     * @param MagicObject|null $entityTrash     The trash entity for storing deleted data.
     * @param string          $currentUser      The user performing the approval.
     * @param string          $currentTime      The timestamp of the action.
     * @param string          $currentIp        The IP address of the user performing the action.
     * @param SetterGetter|null $approvalCallback Optional approval callback for executing pre/post-approval actions.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function approve($columnToBeCopied, $entityApv, $entityTrash, $currentUser, $currentTime, $currentIp, $approvalCallback = null)
    {
        $this->validateApproval($entityApv, $currentUser);
        $waitingFor = $this->entity->get($this->entityInfo->getWaitingFor());

        if ($waitingFor == WaitingFor::CREATE) {
            $this->entity
                ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
                ->set($this->entityInfo->getDraft(), false)
                ->set($this->entityInfo->getApprovalId(), null)
                ->update();
            $this->executeCallback($approvalCallback, 'afterInsert', $entityApv, $entityTrash);
        } elseif ($waitingFor == WaitingFor::ACTIVATE) {
            $this->approveActivate();
            $this->executeCallback($approvalCallback, 'afterActivate', $entityApv, $entityTrash);
        } elseif ($waitingFor == WaitingFor::DEACTIVATE) {
            $this->approveDeactivate();
            $this->executeCallback($approvalCallback, 'afterDeactivate', $entityApv, $entityTrash);
        } elseif ($waitingFor == WaitingFor::UPDATE) {
            $this->executeCallback($approvalCallback, 'beforeUpdate', $entityApv, $entityTrash);
            $this->approveUpdate($entityApv, $columnToBeCopied);
            $this->executeCallback($approvalCallback, 'afterUpdate', $entityApv, $entityTrash);
        } elseif ($waitingFor == WaitingFor::DELETE) {
            $this->executeCallback($approvalCallback, 'beforeDelete', $entityApv, $entityTrash);
            $this->approveDelete($entityTrash, $currentUser, $currentTime, $currentIp, $approvalCallback);
            $this->executeCallback($approvalCallback, 'afterDelete', $entityApv, $entityTrash);
        }

        if ($approvalCallback != null && $approvalCallback->getAfterApprove() != null && is_callable($approvalCallback->getAfterApprove())) {
            call_user_func($approvalCallback->getAfterApprove(), $this->entity, null, null);
        }

        return $this;
    }

    /**
     * Executes a specified callback function if it is defined and callable.
     *
     * This method checks if the provided `approvalCallback` contains a valid callback 
     * for the given `callbackName`. If the callback exists and is callable, it is executed 
     * with the entity, approval entity, and trash entity as parameters.
     *
     * @param SetterGetter|null $approvalCallback The callback handler containing callable functions.
     * @param string            $callbackName     The name of the callback function to execute.
     * @param MagicObject|null  $entityApv        The approval entity (used for updates or deletions).
     * @param MagicObject|null  $entityTrash      The trash entity (used for deletions).
     */
    private function executeCallback($approvalCallback, $callbackName, $entityApv = null, $entityTrash = null)
    {
        if ($approvalCallback != null && $approvalCallback->get($callbackName) != null && is_callable($approvalCallback->get($callbackName))) {
            call_user_func($approvalCallback->get($callbackName), $this->entity, $entityApv, $entityTrash);
        }
    }

    /**
     * Approve activation of the entity.
     *
     * This method approves the activation of the entity by updating its status to active. 
     * It clears any pending actions and preserves the relevant information regarding the 
     * last edit request, including admin details, timestamp, and IP address. The entity’s 
     * status is updated to reflect that it is now active and ready for further actions.
     * 
     * - The entity's `active` field is set to `true`, indicating that the entity is now active.
     * - The `waitingFor` field is set to `NOTHING`, meaning no further actions are pending.
     * - The fields such as `adminAskEdit`, `timeAskEdit`, and `ipAskEdit` retain the values 
     *   from the previous edit request, ensuring continuity of the last action performed.
     *
     * @return void
     *         This method does not return any value, as it performs an in-place update of the entity's status.
     */
    private function approveActivate()
    {
        $adminEdit = $this->entity->get($this->entityInfo->getAdminAskEdit());
        $timeEdit = $this->entity->get($this->entityInfo->getTimeAskEdit());
        $ipEdit = $this->entity->get($this->entityInfo->getIpAskEdit());

        $this->entity
            ->set($this->entityInfo->getActive(), true)
            ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
            ->set($this->entityInfo->getAdminAskEdit(), $adminEdit)
            ->set($this->entityInfo->getTimeAskEdit(), $timeEdit)
            ->set($this->entityInfo->getIpAskEdit(), $ipEdit)
            ->update();
    }

    /**
     * Approve deactivation of the entity.
     *
     * This method approves the deactivation of the entity by updating its status to inactive. 
     * It clears any pending edits and resets the relevant fields such as admin request, 
     * timestamp, and IP address of the last request. The entity’s status is updated to 
     * indicate that it is no longer active and that no further actions are pending.
     * 
     * - The entity's `active` field is set to `false`, indicating deactivation.
     * - The `waitingFor` field is set to `NOTHING`, meaning no further actions are required.
     * - Fields such as `adminAskEdit`, `timeAskEdit`, and `ipAskEdit` are preserved from 
     *   the previous request, ensuring that these values are not lost during the deactivation process.
     *
     * @return void
     *         This method does not return any value, as it performs an in-place update of the entity's status.
     */
    private function approveDeactivate()
    {
        $adminEdit = $this->entity->get($this->entityInfo->getAdminAskEdit());
        $timeEdit = $this->entity->get($this->entityInfo->getTimeAskEdit());
        $ipEdit = $this->entity->get($this->entityInfo->getIpAskEdit());

        $this->entity
            ->set($this->entityInfo->getActive(), false)
            ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
            ->set($this->entityInfo->getAdminAskEdit(), $adminEdit)
            ->set($this->entityInfo->getTimeAskEdit(), $timeEdit)
            ->set($this->entityInfo->getIpAskEdit(), $ipEdit)
            ->update();
    }

    /**
     * Approve deletion of the entity.
     *
     * This method handles the approval and execution of the entity's deletion process. 
     * Before performing the deletion, the method can execute custom logic through 
     * optional callbacks. The method supports copying the entity's data to a trash 
     * or backup table before deletion.
     *
     * - If an `entityTrash` is provided, the entity's data will be copied to the trash 
     *   table before deletion.
     * - The method allows for callback functions to be executed before and after the 
     *   deletion process, allowing custom logic to be integrated.
     *
     * @param MagicObject $entityTrash Entity to store deleted data
     *        The entity that will hold the data of the deleted entity. This is typically 
     *        used to back up the entity's data before it is permanently deleted.
     *
     * @param string $currentUser The user performing the deletion
     *        The identifier or name of the user who is performing the deletion action.
     *
     * @param string $currentTime The current time of the action
     *        The timestamp when the deletion action occurs. This can be useful for auditing purposes.
     *
     * @param string $currentIp The current IP address of the user
     *        The IP address of the user performing the deletion. This can be useful for security auditing.
     *
     * @param SetterGetter|null $approvalCallback Optional callback for deletion
     *        An optional callback object that provides custom logic to be executed 
     *        before and after the deletion process. The `getBeforeDelete` and 
     *        `getAfterDelete` methods on the callback can be used to define actions 
     *        that should be taken before and after the deletion respectively.
     *
     * @return self Returns the current instance for method chaining.
     *         Returns the current instance to allow for method chaining.
     */
    public function approveDelete($entityTrash, $currentUser, $currentTime, $currentIp, $approvalCallback = null)
    {
        if ($approvalCallback != null && $approvalCallback->getBeforeDelete() != null && is_callable($approvalCallback->getBeforeDelete())) {
            call_user_func($approvalCallback->getBeforeDelete(), $this->entity, null, null);
        }

        if ($entityTrash != null) {
            // copy database connection from entity to entityTrash
            $entityTrash->currentDatabase($this->entity->currentDatabase());
            // copy data from entity to entityTrash
            $entityTrash->loadData($this->entity);

            $entityTrash->set($this->entityInfo->getAdminDelete(), $currentUser);
            $entityTrash->set($this->entityInfo->getTimeDelete(), $currentTime);
            $entityTrash->set($this->entityInfo->getIpDelete(), $currentIp);

            $entityTrash->insert();
        }

        // delete data
        $this->entity->delete();

        if ($approvalCallback != null && $approvalCallback->getAfterDelete() != null && is_callable($approvalCallback->getAfterDelete())) {
            call_user_func($approvalCallback->getAfterDelete(), $this->entity, null, null);
        }

        return $this;
    }

    /**
     * Reject the entity approval.
     *
     * This method handles the rejection of an entity's approval process. It updates the 
     * approval status to "rejected" and performs corresponding actions based on the 
     * current status of the entity. The method also supports optional callback functions 
     * to be executed before and after the rejection, allowing for custom logic.
     *
     * - If the entity is waiting for creation approval, it will be deleted upon rejection.
     * - If the entity is waiting for update, activation, deactivation, or deletion approval, 
     *   it will reset the waiting status and approval ID after rejection.
     *
     * @param MagicObject $entityApv The approval entity to reject
     *        The entity that holds the approval details to be rejected.
     *
     * @param string|null $currentUser The user performing the rejection (optional)
     *        The identifier or name of the current user rejecting the approval.
     *        If not provided, the current user is assumed to be unavailable.
     *
     * @param string|null $currentTime The current time of the action (optional)
     *        The timestamp when the rejection action occurs. If not provided, 
     *        the current time will be used by default.
     *
     * @param string|null $currentIp The current IP address of the user (optional)
     *        The IP address of the user performing the rejection. If not provided, 
     *        the IP address will be assumed to be unavailable.
     *
     * @param SetterGetter|null $approvalCallback Optional callback for rejection logic
     *        An optional callback object that provides custom logic to be executed 
     *        before and after the rejection process. The `getBeforeReject` and 
     *        `getAfterReject` methods on the callback can be used to define actions 
     *        that should be taken before and after the rejection respectively.
     *
     * @return self Returns the current instance for method chaining.
     *         Returns the current instance to allow for method chaining.
     */
    public function reject($entityApv, $currentUser = null, $currentTime = null, $currentIp = null, $approvalCallback = null) // NOSONAR
    {
        if ($approvalCallback != null && $approvalCallback->getBeforeReject() != null && is_callable($approvalCallback->getBeforeReject())) {
            call_user_func($approvalCallback->getBeforeReject(), $this->entity, null, null);
        }

        $waitingFor = $this->entity->get($this->entityInfo->getWaitingFor());
        $entityApv->currentDatabase($this->entity->currentDatabase());
        $this->validateApproval($entityApv, $currentUser);

        if ($waitingFor == WaitingFor::CREATE) {
            $entityApv->set($this->entityApvInfo->getApprovalStatus(), self::APPROVAL_REJECT)->update();
            $this->entity->delete();
        } elseif (in_array($waitingFor, [WaitingFor::UPDATE, WaitingFor::ACTIVATE, WaitingFor::DEACTIVATE, WaitingFor::DELETE])) {
            $entityApv->set($this->entityApvInfo->getApprovalStatus(), self::APPROVAL_REJECT)->update();
            $this->entity
                ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
                ->set($this->entityInfo->getApprovalId(), null)
                ->update();
        }

        if ($approvalCallback != null && $approvalCallback->getAfterReject() != null && is_callable($approvalCallback->getAfterReject())) {
            call_user_func($approvalCallback->getAfterReject(), $this->entity, null, null);
        }

        return $this;
    }

    /**
     * Validate the approval process.
     *
     * This method checks if the approval process is valid by using a callback function, 
     * if provided. The callback function allows for custom validation logic to be applied 
     * to the entity, the approval entity, and the current user performing the action. 
     * If no callback is provided, it simply returns `true`, indicating that the approval 
     * process is valid by default.
     *
     * @param MagicObject $entityApv The approval entity to validate
     *        The entity containing the approval details to be validated in the process.
     *
     * @param string $currentUser The user performing the action
     *        The identifier or name of the current user who is requesting the approval validation.
     *
     * @return boolean true if validation passes, false otherwise
     *         Returns `true` if the validation passes (either by the callback or default validation), 
     *         or `false` if it fails based on the validation logic.
     */
    private function validateApproval($entityApv, $currentUser)
    {
        if ($this->callbackValidation != null && is_callable($this->callbackValidation)) {
            return call_user_func($this->callbackValidation, $this->entity, $entityApv, $currentUser);
        }
        return true;
    }

    /**
     * Approves the update of the entity by copying specified columns from an approval entity.
     *
     * This method retrieves the approved values from the approval entity and updates 
     * the main entity accordingly. Only the specified columns are copied, and once 
     * the update is complete, the approval status is updated, and the approval ID is cleared.
     *
     * @param MagicObject $entityApv The approval entity containing approved values.
     *        This entity holds the values that will be copied to the main entity upon approval.
     *
     * @param string[] $columnToBeCopied The list of columns to be copied from the approval entity.
     *        Only these specified columns will be updated in the main entity.
     *
     * @return self Returns the current instance for method chaining.
     */
    private function approveUpdate($entityApv, $columnToBeCopied)
    {
        $tableInfo = $this->entity->tableInfo();
        $primaryKeys = array_keys($tableInfo->getPrimaryKeys());
        $approvalId = $this->entity->get($this->entityInfo->getApprovalId());
        $primaryKeyName = $primaryKeys[0];
        $oldPrimaryKeyValue = $this->entity->get($primaryKeyName);

        if ($approvalId !== null) {
            // Copy the database connection from the main entity to the approval entity
            $entityApv->currentDatabase($this->entity->currentDatabase());

            try {
                // Retrieve the approval entity data using the approval ID
                $entityApv->findOneWithPrimaryKeyValue($approvalId);
                $values = $entityApv->valueArray();
                $updated = 0;

                // Define criteria to find and update the correct entity record
                $specs = PicoSpecification::getInstance()
                    ->addAnd(PicoPredicate::getInstance()->equals($primaryKeyName, $oldPrimaryKeyValue));

                $updater = $this->entity->where($specs);

                // Copy specified columns from the approval entity to the main entity
                foreach ($values as $field => $value) {
                    if (in_array($field, $columnToBeCopied)) {
                        $updater->set($field, $value);
                        $updated++;
                    }
                }

                // Perform the update only if at least one column was modified
                if ($updated > 0) {
                    $updater->update();
                }

                // Mark the approval entity as approved after processing
                $entityApv->set($this->entityApvInfo->getApprovalStatus(), self::APPROVAL_APPROVE)->update();

                // Reset approval-related fields in the main entity
                $this->entity
                    ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
                    ->set($this->entityInfo->getApprovalId(), null)
                    ->update();

                $this->updateEntity($updated, $values, $columnToBeCopied);
            } catch (Exception $e) {
                // Log or handle exception as needed
            }
        }
        return $this;
    }

    /**
     * Updates the main entity instance in memory with the approved values.
     *
     * This method ensures that the entity reflects the latest approved values 
     * without immediately saving them to the database. Only the specified columns 
     * from the approval entity are updated.
     *
     * @param int $updated The number of columns that were modified.
     *        This value determines whether any updates were made to the entity.
     *
     * @param array $values The key-value pairs of approved data from the approval entity.
     *        This contains all the column values available for update.
     *
     * @param string[] $columnToBeCopied The list of columns allowed to be updated.
     *        Only these specified columns will be modified in the main entity.
     *
     * @return void
     */
    private function updateEntity($updated, $values, $columnToBeCopied)
    {
        // Ensure that at least one column was updated before modifying the entity
        if ($updated > 0) {
            foreach ($values as $field => $value) {
                if (in_array($field, $columnToBeCopied)) {
                    // Apply the approved values to the entity without persisting to the database
                    $this->entity->set($field, $value);
                }
            }
        }
    }

    /**
     * Get the current user.
     *
     * This method returns the username or identifier of the current user.
     *
     * @return string The current user's identifier or name.
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Get the current time.
     *
     * This method returns the current timestamp or time when the action was performed.
     *
     * @return string The current time in a formatted string (e.g., 'Y-m-d H:i:s').
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * Get the current IP address.
     *
     * This method returns the IP address of the user making the request or action.
     *
     * @return string The current IP address of the user.
     */
    public function getCurrentIp()
    {
        return $this->currentIp;
    }

    /**
     * Get callback after approval
     *
     * @return callable|null
     */ 
    public function getCallbackAfterApprove()
    {
        return $this->callbackAfterApprove;
    }

    /**
     * Get callback after rejection
     *
     * @return callable|null
     */ 
    public function getCallbackAfterReject()
    {
        return $this->callbackAfterReject;
    }
}
