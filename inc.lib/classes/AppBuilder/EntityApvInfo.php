<?php

namespace AppBuilder;

use MagicObject\SecretObject;

/**
 * Class EntityApvInfo
 *
 * Represents information related to approval status in an application.
 * This class extends SecretObject to encapsulate sensitive data and 
 * provides methods to manage the approval status.
 */
class EntityApvInfo extends SecretObject
{
    /**
     * Approval status
     *
     * @var string
     */
    protected $approvalStatus;
}