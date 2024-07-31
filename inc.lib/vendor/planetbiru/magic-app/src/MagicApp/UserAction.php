<?php

namespace MagicApp;

class UserAction
{
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

    public static function isRequireApproval($waitingFor)
    {
        return isset($waitingFor) && $waitingFor != WaitingFor::NOTHING;
    }

    public static function isRequireNextAction($inputGet)
    {
        return isset($inputGet) && $inputGet->getNextAction() != null;
    }

    public static function getWaitingForMessage($appLanguage, $waitingFor)
    {
        $approvalMessage = "";
        if($waitingFor == WaitingFor::CREATE)
        {
            $approvalMessage = $appLanguage->getMessageWaitingForCreate();
        }
        else if($waitingFor == WaitingFor::UPDATE)
        {
            $approvalMessage = $appLanguage->getMessageWaitingForUpdate();
        }
        else if($waitingFor == WaitingFor::ACTIVATE)
        {
            $approvalMessage = $appLanguage->getMessageWaitingForActivate();
        }
        else if($waitingFor == WaitingFor::DEACTIVATE)
        {
            $approvalMessage = $appLanguage->getMessageWaitingForDeactivate();
        }
        else if($waitingFor == WaitingFor::DELETE)
        {
            $approvalMessage = $appLanguage->getMessageWaitingForDelete();
        }
        return $approvalMessage;
    }

    public static function getWaitingForText($appLanguage, $waitingFor)
    {
        $approvalMessage = "";
        if($waitingFor == WaitingFor::CREATE)
        {
            $approvalMessage = $appLanguage->getShortWaitingForCreate();
        }
        else if($waitingFor == WaitingFor::UPDATE)
        {
            $approvalMessage = $appLanguage->getShortWaitingForUpdate();
        }
        else if($waitingFor == WaitingFor::ACTIVATE)
        {
            $approvalMessage = $appLanguage->getShortWaitingForActivate();
        }
        else if($waitingFor == WaitingFor::DEACTIVATE)
        {
            $approvalMessage = $appLanguage->getShortWaitingForDeactivate();
        }
        else if($waitingFor == WaitingFor::DELETE)
        {
            $approvalMessage = $appLanguage->getShortWaitingForDelete();
        }
        return $approvalMessage;
    }
}