<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Activation: Create Tables
function mkwaf_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Create activity log table
    $table_name = $wpdb->prefix . 'mkwaf_activity_log';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        points INT(11) NOT NULL,
        description TEXT,
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create member badges table
    $table_name = $wpdb->prefix . 'mkwa_member_badges';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        member_id BIGINT(20) UNSIGNED NOT NULL,
        badge_id BIGINT(20) UNSIGNED NOT NULL,
        earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'mkwaf_create_tables');