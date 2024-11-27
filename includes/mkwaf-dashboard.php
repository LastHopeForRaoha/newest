<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Dashboard Shortcode
function mkwaf_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/login">log in</a> to view your dashboard.</p>';
    }

    $user_id = get_current_user_id();
    $points = (int) get_user_meta($user_id, 'mkwaf_points', true);
    $badges = array_filter(get_user_meta($user_id), function ($key) {
        return strpos($key, 'mkwaf_badge_') === 0;
    }, ARRAY_FILTER_USE_KEY);

    ob_start();
    ?>
    <div class="mkwaf-dashboard">
        <h2>Your Dashboard</h2>
        <p><strong>Points:</strong> <?php echo $points; ?></p>
        <p><strong>Badges:</strong></p>
        <ul>
            <?php foreach ($badges as $badge_key => $value): ?>
                <li><?php echo ucfirst(str_replace('_', ' ', str_replace('mkwaf_badge_', '', $badge_key))); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwaf_dashboard', 'mkwaf_dashboard_shortcode');
