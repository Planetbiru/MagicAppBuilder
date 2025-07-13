<?php

namespace MagicApp;

/**
 * Class UserAction
 *
 * This class handles various actions a user can perform within the system. 
 * It provides methods to determine if an action requires approval, handles the approval waiting message, 
 * and checks if a next action is specified.
 */
class UserAction
{
    // Action constants
    const SHOW_ALL          = "list";
    const INSERT            = "insert";
    const CREATE            = "create";
    const UPDATE            = "update";
    const DELETE            = "delete";
    const ACTIVATE          = "activate";
    const DEACTIVATE        = "deactivate";
    const DETAIL            = "detail";
    const APPROVE           = "approve";
    const APPROVAL          = "approval";
    const REJECT            = "reject";
    const SORT_ORDER        = "sort_order";  
    const USER_ACTION       = "user_action";
    const NEXT_ACTION       = "next_action";
    const SPECIAL_ACTION    = "special_action";
    const EXPORT            = "export";
    const RESTORE           = "restore";

    /**
     * Determine if an action requires approval.
     *
     * @param mixed $waitingFor The status indicating what is waiting for approval.
     * @return boolean True if approval is required, false otherwise.
     */
    public static function isRequireApproval($waitingFor)
    {
        return isset($waitingFor) && $waitingFor != WaitingFor::NOTHING;
    }

    /**
     * Determine if a next action is specified in the input.
     *
     * @param mixed $inputGet The input data containing the next action.
     * @return boolean True if there is a next action, false otherwise.
     */
    public static function isRequireNextAction($inputGet)
    {
        return isset($inputGet) && $inputGet->getNextAction() != null;
    }

    /**
     * Get the approval waiting message based on the specified action.
     *
     * @param object $appLanguage The language object for fetching messages.
     * @param mixed $waitingFor The action waiting for approval.
     * @return string The approval message.
     */
    public static function getWaitingForMessage($appLanguage, $waitingFor)
    {
        $approvalMessage = "";
        if ($waitingFor == WaitingFor::CREATE) {
            $approvalMessage = $appLanguage->getMessageWaitingForCreate();
        } elseif ($waitingFor == WaitingFor::UPDATE) {
            $approvalMessage = $appLanguage->getMessageWaitingForUpdate();
        } elseif ($waitingFor == WaitingFor::ACTIVATE) {
            $approvalMessage = $appLanguage->getMessageWaitingForActivate();
        } elseif ($waitingFor == WaitingFor::DEACTIVATE) {
            $approvalMessage = $appLanguage->getMessageWaitingForDeactivate();
        } elseif ($waitingFor == WaitingFor::DELETE) {
            $approvalMessage = $appLanguage->getMessageWaitingForDelete();
        } elseif ($waitingFor == WaitingFor::SORT_ORDER) {
            $approvalMessage = $appLanguage->getMessageWaitingForSortOrder();
        }
        return $approvalMessage;
    }

    /**
     * Get the short waiting text based on the specified action.
     *
     * @param object $appLanguage The language object for fetching messages.
     * @param mixed $waitingFor The action waiting for approval.
     * @return string The short approval message.
     */
    public static function getWaitingForText($appLanguage, $waitingFor)
    {
        $approvalMessage = "";
        if ($waitingFor == WaitingFor::CREATE) {
            $approvalMessage = $appLanguage->getShortWaitingForCreate();
        } elseif ($waitingFor == WaitingFor::UPDATE) {
            $approvalMessage = $appLanguage->getShortWaitingForUpdate();
        } elseif ($waitingFor == WaitingFor::ACTIVATE) {
            $approvalMessage = $appLanguage->getShortWaitingForActivate();
        } elseif ($waitingFor == WaitingFor::DEACTIVATE) {
            $approvalMessage = $appLanguage->getShortWaitingForDeactivate();
        } elseif ($waitingFor == WaitingFor::DELETE) {
            $approvalMessage = $appLanguage->getShortWaitingForDelete();
        }
        return $approvalMessage;
    }
}
