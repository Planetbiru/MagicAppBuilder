<?php

namespace MagicApp;

class WaitingFor
{
    // Constants representing different waiting statuses
    const NOTHING     = 0; // No action pending
    const CREATE      = 1; // Action pending: Create
    const UPDATE      = 2; // Action pending: Update
    const ACTIVATE    = 3; // Action pending: Activate
    const DEACTIVATE  = 4; // Action pending: Deactivate
    const DELETE      = 5; // Action pending: Delete
    const SORT_ORDER  = 6; // Action pending: Sort Order

    /**
     * Get the description of the waiting status.
     *
     * @param int $status The waiting status.
     * @return string The description of the waiting status.
     */
    public static function getDescription($status)
    {
        switch ($status) {
            case self::CREATE:
                return 'Waiting for creation approval.';
            case self::UPDATE:
                return 'Waiting for update approval.';
            case self::ACTIVATE:
                return 'Waiting for activation approval.';
            case self::DEACTIVATE:
                return 'Waiting for deactivation approval.';
            case self::DELETE:
                return 'Waiting for deletion approval.';
            case self::SORT_ORDER:
                return 'Waiting for sort order approval.';
            default:
                return 'No actions pending.';
        }
    }
}
