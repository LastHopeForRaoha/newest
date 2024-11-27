<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Points System Functions
function mkwaf_add_points($user_id, $points, $activity_type, $description = '') {
    $current_points = (int) get_user_meta($user_id, 'mkwaf_points', true);
    $new_points = $current_points + $points;

    // Update points
    update_user_meta($user_id, 'mkwaf_points', $new_points);

    // Log activity
    mkwaf_log_activity($user_id, $activity_type, $points, $description);
}

// Deduct Points Function
function mkwaf_deduct_points($user_id, $points) {
    $current_points = (int) get_user_meta($user_id, 'mkwaf_points', true);

    if ($current_points >= $points) {
        update_user_meta($user_id, 'mkwaf_points', $current_points - $points);
        mkwaf_log_activity($user_id, 'redeem', -$points, 'Redeemed points for rewards');
        return true;
    }
    return false;
}

// Activity Log Function
function mkwaf_log_activity($user_id, $activity_type, $points, $description) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'mkwaf_activity_log',
        [
            'user_id' => $user_id,
            'activity_type' => $activity_type,
            'points' => $points,
            'description' => $description,
            'date_created' => current_time('mysql'),
        ]
    );
}

// Badge System
function mkwaf_check_badges($user_id) {
    $badges = [
        'first_login' => [
            'name' => 'First Timer',
            'description' => 'Awarded on first login',
            'criteria' => function ($user_id) {
                global $wpdb;
                $logins = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}mkwaf_activity_log WHERE user_id = %d AND activity_type = 'login'",
                    $user_id
                ));
                return $logins === 1;
            },
        ],
        '100_points' => [
            'name' => 'Centurion',
            'description' => 'Earned 100 points',
            'criteria' => function ($user_id) {
                $points = (int) get_user_meta($user_id, 'mkwaf_points', true);
                return $points >= 100;
            },
        ],
    ];

    foreach ($badges as $key => $badge) {
        if (!get_user_meta($user_id, 'mkwaf_badge_' . $key, true) && $badge['criteria']($user_id)) {
            update_user_meta($user_id, 'mkwaf_badge_' . $key, true);
            mkwaf_log_activity($user_id, 'badge_award', 0, 'Earned badge: ' . $badge['name']);
        }
    }
}

// Hook Badge Check After Login
add_action('wp_login', function ($user_login) {
    $user = get_user_by('login', $user_login);
    mkwaf_check_badges($user->ID);
});
