<?php

namespace AppBuilder;

/**
 * Class WaitingFor
 *
 * This class defines constants representing different states of an entity
 * regarding actions that require attention or approval.
 */
class WaitingFor
{
    /**
     * Indicates that there is nothing waiting for action.
     * @var int
     */
    const NOTHING     = 0;

    /**
     * Indicates that the entity is waiting for creation approval.
     * @var int
     */
    const CREATE      = 1;

    /**
     * Indicates that the entity is waiting for update approval.
     * @var int
     */
    const UPDATE      = 2;

    /**
     * Indicates that the entity is waiting for activation approval.
     * @var int
     */
    const ACTIVATE    = 3;

    /**
     * Indicates that the entity is waiting for deactivation approval.
     * @var int
     */
    const DEACTIVATE  = 4;

    /**
     * Indicates that the entity is waiting for deletion approval.
     * @var int
     */
    const DELETE      = 5;

    /**
     * Indicates that the entity is waiting for sort order adjustment approval.
     * @var int
     */
    const SORT_ORDER  = 6;
}
