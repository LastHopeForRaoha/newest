<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MKWA_Activator {

    /**
     * Handles plugin activation.
     */
    public static function activate() {
        global $wpdb;

        // Set charset and collation for database tables
        $charset_collate = $wpdb->get_charset_collate();

        // Log activation process
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Activating MKWA Fitness plugin...');
        }

        // Create members table
        $members_table = $wpdb->prefix . 'mkwa_members';
        $members_sql = "CREATE TABLE IF NOT EXISTS $members_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT(11) NOT NULL DEFAULT 0,
            level INT(11) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        // Create member badges table
        $member_badges_table = $wpdb->prefix . 'mkwa_member_badges';
        $member_badges_sql = "CREATE TABLE IF NOT EXISTS $member_badges_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            badge_id BIGINT(20) UNSIGNED NOT NULL,
            earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Create badges table
        $badges_table = $wpdb->prefix . 'mkwa_badges';
        $badges_sql = "CREATE TABLE IF NOT EXISTS $badges_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            image_url VARCHAR(255),
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Include the WordPress database upgrade functions
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute the SQL to create the tables
        dbDelta($members_sql);
        dbDelta($member_badges_sql);
        dbDelta($badges_sql);

        // Log table creation success
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("MKWA Fitness plugin activated: Tables created successfully.");
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log activation completion
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('MKWA Fitness plugin activation completed successfully.');
        }

        // Set default plugin options
        self::set_default_options();
    }

    /**
     * Sets default plugin options.
     */
    private static function set_default_options() {
        $default_options = [
            'mkwa_points_per_checkin' => 10, // Example: Points per check-in
            'mkwa_points_per_referral' => 50, // Example: Points per referral
        ];

        foreach ($default_options as $key => $value) {
            if (!get_option($key)) {
                add_option($key, $value);
            }
        }

        // Log default options setup
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Default options set for MKWA Fitness.');
        }
    }
}