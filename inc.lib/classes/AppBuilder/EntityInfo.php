<?php

namespace AppBuilder;

use MagicObject\SecretObject;

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
