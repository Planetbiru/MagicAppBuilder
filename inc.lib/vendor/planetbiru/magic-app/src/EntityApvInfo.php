<?php

namespace MagicApp;

use MagicObject\SecretObject;

/**
 * Class EntityApvInfo
 *
 * Represents the approval information entity.
 * 
 * This class encapsulates the approval status information of an entity.
 * It extends from `SecretObject` to inherit functionality related to security
 * or handling sensitive data, depending on the implementation of the parent class.
 */
class EntityApvInfo extends SecretObject
{
    /**
     * Approval status
     *
     * This property holds the approval status of the entity.
     * It can represent various states like "pending", "approved", "rejected", etc.
     *
     * @var string
     */
    protected $approvalStatus;
}
