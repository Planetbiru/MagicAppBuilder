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
     * This method provides a human-readable description of the given waiting status.
     * Each constant in the class represents a specific status, and this method
     * returns the corresponding description based on the value passed.
     *
     * @param int $status The waiting status constant (e.g., `WaitingFor::CREATE`).
     * 
     * @return string The description of the waiting status.
     *               Returns a message indicating no action pending if the status is unknown.
     */
    public static function getDescription($status)
    {
        $description = ''; // Initialize an empty variable for the description.

        switch ($status) {
            case self::CREATE:
                $description = 'Waiting for creation approval.';
                break;
            case self::UPDATE:
                $description = 'Waiting for update approval.';
                break;
            case self::ACTIVATE:
                $description = 'Waiting for activation approval.';
                break;
            case self::DEACTIVATE:
                $description = 'Waiting for deactivation approval.';
                break;
            case self::DELETE:
                $description = 'Waiting for deletion approval.';
                break;
            case self::SORT_ORDER:
                $description = 'Waiting for sort order approval.';
                break;
            default:
                $description = 'No actions pending.';
                break;
        }

        return $description; // Return the final description.
    }

}
