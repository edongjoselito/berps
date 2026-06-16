<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TimezoneHook
 *
 * Centralized hook that forces Asia/Manila timezone across the entire
 * application stack so any class/controller/library/model that handles
 * date or time automatically follows Manila time without manual setup.
 *
 *  - setPhpTimezone(): Runs at pre_system; ensures PHP date functions
 *    (date(), time(), DateTime, etc.) default to Asia/Manila.
 *  - setDbTimezone():  Runs after the controller is constructed (DB
 *    connection is up); sets the MySQL session time_zone to +08:00 so
 *    NOW(), CURRENT_TIMESTAMP, UNIX_TIMESTAMP, etc. also follow Manila.
 */
class TimezoneHook
{
    const TZ_NAME   = 'Asia/Manila';
    const TZ_OFFSET = '+08:00';

    public function setPhpTimezone()
    {
        @date_default_timezone_set(self::TZ_NAME);

        // Best-effort: align ini setting too (some libs read this directly)
        if (function_exists('ini_set')) {
            @ini_set('date.timezone', self::TZ_NAME);
        }
    }

    public function setDbTimezone()
    {
        // PHP timezone may not have been set if pre_system hook was skipped
        if (date_default_timezone_get() !== self::TZ_NAME) {
            @date_default_timezone_set(self::TZ_NAME);
        }

        $CI =& get_instance();
        if (!isset($CI->db) || !is_object($CI->db)) {
            return;
        }

        try {
            $CI->db->query("SET time_zone = '" . self::TZ_OFFSET . "'");
        } catch (Exception $e) {
            // Silently ignore if the DB driver doesn't support SET time_zone
            log_message('error', 'TimezoneHook: failed to set MySQL time_zone - ' . $e->getMessage());
        }
    }
}
