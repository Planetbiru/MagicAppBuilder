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
     * @param MagicObject $entity The master entity being approved or rejected
     * @param EntityInfo $entityInfo Information about the entity
     * @param EntityApvInfo $entityApvInfo Information about the entity's approval status
     * @param callable|null $callbackValidation Optional validation callback
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
     * @param string[] $columToBeCopied Columns to copy from the approval entity
     * @param MagicObject|null $entityApv Approval entity
     * @param MagicObject|null $entityTrash Trash entity for deletion
     * @param string $currentUser The user performing the approval
     * @param string $currentTime The current time of the action
     * @param string $currentIp The current IP address of the user
     * @param SetterGetter|null $approvalCallback Optional callback for approval
     * @return self
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
     * @return void
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
     * @return void
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
     * @param MagicObject $entityTrash Entity to store deleted data
     * @param string $currentUser The user performing the deletion
     * @param string $currentTime The current time of the action
     * @param string $currentIp The current IP address of the user
     * @param SetterGetter|null $approvalCallback Optional callback for deletion
     * @return self
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
     * @param MagicObject $entityApv Approval entity
     * @param string|null $currentUser The user performing the rejection
     * @param string|null $currentTime The current time of the action
     * @param string|null $currentIp The current IP address of the user
     * @param SetterGetter|null $approvalCallback Optional callback for rejection
     * @return self
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
     * @param MagicObject $entityApv The approval entity to validate
     * @param string $currentUser The user performing the action
     * @return boolean True if validation passes, false otherwise
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
     * @param MagicObject $entityApv Approval entity
     * @param string[] $columToBeCopied Columns to copy from the approval entity
     * @return self
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
     * @return string
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Get the current time.
     *
     * @return string
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * Get the current IP address.
     *
     * @return string
     */
    public function getCurrentIp()
    {
        return $this->currentIp;
    }
}
