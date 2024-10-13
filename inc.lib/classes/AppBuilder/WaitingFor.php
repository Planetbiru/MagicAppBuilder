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
     */
    const NOTHING     = 0;

    /**
     * Indicates that the entity is waiting for creation approval.
     */
    const CREATE      = 1;

    /**
     * Indicates that the entity is waiting for update approval.
     */
    const UPDATE      = 2;

    /**
     * Indicates that the entity is waiting for activation approval.
     */
    const ACTIVATE    = 3;

    /**
     * Indicates that the entity is waiting for deactivation approval.
     */
    const DEACTIVATE  = 4;

    /**
     * Indicates that the entity is waiting for deletion approval.
     */
    const DELETE      = 5;

    /**
     * Indicates that the entity is waiting for sort order adjustment approval.
     */
    const SORT_ORDER  = 6;
}
