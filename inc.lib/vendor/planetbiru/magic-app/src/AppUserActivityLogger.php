<?php

namespace MagicApp;

use Exception;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

/**
 * Class responsible for logging user activity in the application.
 */
class AppUserActivityLogger
{
    /**
     * Application configuration settings.
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * Entity used for storing user activity logs.
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * Initializes the user activity logger with the necessary configurations.
     *
     * @param SecretObject $appConfig Application configuration.
     * @param MagicObject $entity Entity to store user activity logs.
     */
    public function __construct($appConfig, $entity)
    {
        $this->appConfig = $appConfig;
        $this->entity = $entity;
    }

    /**
     * Logs user activity if logging is enabled in the application configuration.
     * The user action is retrieved from either POST or GET request data.
     *
     * @param AppUser $currentUser The current user performing the action.
     * @param SetterGetter $currentAction The details of the current action, including user ID, timestamp, and IP address.
     * @return self Returns the logger instance.
     */
    public function logActivity($currentUser, $currentAction)
    {
        $inputGet = new InputGet();
        $inputPost = new InputPost();
        $userAction = $inputPost->getUserAction();

        if (!isset($userAction) || empty($userAction)) {
            $userAction = $inputGet->getUserAction();
        }

        if ($this->appConfig->getLogUserActivity()) {
            try {
                $this->entity->setUserAction($userAction);
                $this->entity->setUserId($currentAction->getUserId());
                $this->entity->setUsername($currentUser->getUsername());
                $this->entity->setTimeCreate($currentAction->getTime());
                $this->entity->setIpCreate($currentAction->getIp());
                $this->entity->setGetData($inputGet);
                $this->entity->setPostData($inputPost);
                $this->entity->insert();
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        }

        return $this;
    }

}
