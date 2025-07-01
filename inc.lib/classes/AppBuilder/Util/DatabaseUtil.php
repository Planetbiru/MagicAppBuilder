<?php

namespace AppBuilder\Util;

class DatabaseUtil
{
    /**
     * List of system-defined tables used by the application.
     * These are considered core tables and should not be modified by users.
     * @var string[]
     */
    const SYSTEM_TABLES = [
            'admin',
            'admin_level',
            'admin_profile',
            'admin_role',
            'menu_cache',
            'menu_group_translation',
            'menu_translation',
            'message',
            'message_folder',
            'module',
            'module_group',
            'notification',
            'user_activity',
            'user_password_history',
    ];
}