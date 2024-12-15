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
     * Approve the entity based on its current status and the provided parameters.
     *
     * This method handles the approval process for the entity, taking actions based on its
     * current status and the type of approval required (e.g., create, update, delete, etc.). 
     * It also allows for optional callback actions before and after the approval process.
     * The method updates the entity's fields, such as approval status, draft state, and 
     * approval ID, according to the approval type.
     *
     * The method can approve various actions:
     * - **CREATE**: Sets the entity to a finalized state by removing the draft status and resetting approval ID.
     * - **ACTIVATE**: Approves the activation of the entity.
     * - **DEACTIVATE**: Approves the deactivation of the entity.
     * - **UPDATE**: Approves the update of the entity, copying specified columns from the approval entity.
     * - **DELETE**: Approves the deletion of the entity, optionally moving the data to a trash entity.
     *
     * Additionally, if the `approvalCallback` is provided, it can trigger actions before or after the approval process.
     *
     * @param string[] $columToBeCopied Columns to copy from the approval entity.
     * @param MagicObject|null $entityApv Approval entity, used for approval actions like updating or copying.
     * @param MagicObject|null $entityTrash Trash entity for storing deleted data.
     * @param string $currentUser The user performing the approval.
     * @param string $currentTime The current time of the action.
     * @param string $currentIp The current IP address of the user performing the action.
     * @param SetterGetter|null $approvalCallback Optional callback for approval, which can trigger actions before and after the approval process.
     * 
     * @return self The current instance, allowing method chaining The current instance of the class, allowing for method chaining.
     */
    public function approve($columToBeCopied, $entityApv, $entityTrash, $currentUser, $currentTime, $currentIp, $approvalCallback = null)
    {
        $this->validateApproval($entityApv, $currentUser);
        $waitingFor = $this->entity->get($this->entityInfo->getWaitingFor());

        if ($waitingFor == WaitingFor::CREATE) {
            $this->entity
                ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
                ->set($this->entityInfo->getDraft(), false)
                ->set($this->entityInfo->getApprovalId(), null)
                ->update();
        } elseif ($waitingFor == WaitingFor::ACTIVATE) {
            $this->approveActivate();
        } elseif ($waitingFor == WaitingFor::DEACTIVATE) {
            $this->approveDeactivate();
        } elseif ($waitingFor == WaitingFor::UPDATE) {
            $this->approveUpdate($entityApv, $columToBeCopied);
        } elseif ($waitingFor == WaitingFor::DELETE) {
            $this->approveDelete($entityTrash, $currentUser, $currentTime, $currentIp, $approvalCallback);
        }

        if ($approvalCallback != null && $approvalCallback->getAfterApprove() != null && is_callable($approvalCallback->getAfterApprove())) {
            call_user_func($approvalCallback->getAfterApprove(), $this->entity, null, null);
        }

        return $this;
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
     * @return self The current instance, allowing method chaining
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
            $entityTrash->loadData($this->entity)->insert();
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
     * @return self The current instance, allowing method chaining
     *         Returns the current instance to allow for method chaining.
     */
    public function reject($entityApv, $currentUser = null, $currentTime = null, $currentIp = null, $approvalCallback = null)
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
     * Approve the update of the entity.
     *
     * This method handles the approval process for updating an entity, copying specified 
     * columns from an approval entity, and updating the original entity with those values. 
     * It also resets the approval status and clears the approval ID once the update is complete.
     *
     * @param MagicObject $entityApv Approval entity
     *        The entity that contains the approval information, including the status and fields to be copied.
     *
     * @param string[] $columToBeCopied Columns to copy from the approval entity
     *        An array of column names that should be copied from the approval entity to the main entity 
     *        during the approval process.
     *
     * @return self The current instance, allowing method chaining
     *        Returns the current instance of the class for method chaining.
     */
    private function approveUpdate($entityApv, $columToBeCopied)
    {
        $tableInfo = $this->entity->tableInfo();
        $primaryKeys = array_keys($tableInfo->getPrimaryKeys());
        $approvalId = $this->entity->get($this->entityInfo->getApprovalId());
        $primaryKeyName = $primaryKeys[0];
        $oldPrimaryKeyValue = $this->entity->get($primaryKeyName);

        if ($approvalId != null) {
            // copy database connection from entity to entityApv
            $entityApv->currentDatabase($this->entity->currentDatabase());
            try {
                $entityApv->find($approvalId);
                $values = $entityApv->valueArray();
                $updated = 0;
                $specs = PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals($primaryKeyName, $oldPrimaryKeyValue));
                $updater = $this->entity->where($specs);

                foreach ($values as $field => $value) {
                    if (in_array($field, $columToBeCopied)) {
                        $updater->set($field, $value);
                        $updated++;
                    }
                }

                if ($updated > 0) {
                    $updater->update();
                }
                $entityApv->set($this->entityApvInfo->getApprovalStatus(), self::APPROVAL_REJECT)->update();
                $this->entity
                    ->set($this->entityInfo->getWaitingFor(), WaitingFor::NOTHING)
                    ->set($this->entityInfo->getApprovalId(), null)
                    ->update();
            } catch (Exception $e) {
                // Handle exception if necessary
            }
        }
        return $this;
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
