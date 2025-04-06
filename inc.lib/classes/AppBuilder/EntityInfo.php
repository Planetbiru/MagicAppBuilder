<?php

namespace AppBuilder;

use MagicObject\SecretObject;

/**
 * Class EntityInfo
 *
 * Represents metadata about an entity, including its status, administrative actions, and timestamps.
 * This class extends SecretObject to inherit additional functionality related to secret management.
 */
class EntityInfo extends SecretObject
{
    /**
     * Sort order for the entity.
     *
     * @var int
     */
    protected $sortOrder;

    /**
     * Key indicating if the entity is active.
     *
     * @var string
     */
    protected $active;

    /**
     * Key indicating if the entity is in draft status.
     *
     * @var string
     */
    protected $draft;
    
    /**
     * Key indicating if the entity has been restored from trash to master table.
     *
     * @var string
     */
    protected $restored;

    /**
     * Admin user who created the entity.
     *
     * @var string
     */
    protected $adminCreate;

    /**
     * Admin user who last edited the entity.
     *
     * @var string
     */
    protected $adminEdit;

    /**
     * Admin user who requested the edit of the entity.
     *
     * @var string
     */
    protected $adminAskEdit;

    /**
     * Admin user who delete the entity.
     *
     * @var string
     */
    protected $adminDelete;
    
    /**
     * Admin user who restore the entity.
     *
     * @var string
     */
    protected $adminRestore;

    /**
     * IP address of the user who created the entity.
     *
     * @var string
     */
    protected $ipCreate;

    /**
     * IP address of the user who last edited the entity.
     *
     * @var string
     */
    protected $ipEdit;

    /**
     * IP address of the user who requested the edit of the entity.
     *
     * @var string
     */
    protected $ipAskEdit;

    /**
     * IP address of the user who delete the entity.
     *
     * @var string
     */
    protected $ipDelete;
    
    /**
     * IP address of the user who restore the entity.
     *
     * @var string
     */
    protected $ipRestore;

    /**
     * Timestamp of when the entity was created.
     *
     * @var string
     */
    protected $timeCreate;

    /**
     * Timestamp of when the entity was last edited.
     *
     * @var string
     */
    protected $timeEdit;

    /**
     * Timestamp of when the edit was requested.
     *
     * @var string
     */
    protected $timeAskEdit;

    /**
     * Timestamp of when the entity was deleted.
     *
     * @var string
     */
    protected $timeDelete;
    
    /**
     * Timestamp of when the entity was restored.
     *
     * @var string
     */
    protected $timeRestore;

    /**
     * Key indicating who the entity is waiting for approval from.
     *
     * @var string
     */
    protected $waitingFor;

    /**
     * Approval ID for the entity.
     *
     * @var string
     */
    protected $approvalId;
}
