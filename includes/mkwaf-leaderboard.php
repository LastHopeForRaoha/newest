<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Fetch Leaderboard Data
function mkwaf_get_leaderboard_data($limit = 10, $timeframe = 'all_time') {
    global $wpdb;

    // Adjust query based on timeframe
    $date_condition = '';
    if ($timeframe === 'monthly') {
        $date_condition = "WHERE DATE_FORMAT(date_created, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
    }

    $results = $wpdb->get_results("
        SELECT user_id, SUM(points) AS total_points 
        FROM {$wpdb->prefix}mkwaf_activity_log
        $date_condition
        GROUP BY user_id
        ORDER BY total_points DESC
        LIMIT $limit
    ");

    return $results;
}

// Leaderboard Shortcode
function mkwaf_leaderboard_shortcode($atts) {
    $atts = shortcode_atts(['limit' => 10, 'timeframe' => 'all_time'], $atts);
    $data = mkwaf_get_leaderboard_data($atts['limit'], $atts['timeframe']);

    ob_start();
    ?>
    <div class="mkwaf-leaderboard">
        <h2>Leaderboard</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Member</th>
                    <th>Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo esc_html(get_userdata($row->user_id)->display_name); ?></td>
                        <td><?php echo intval($row->total_points); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwaf_leaderboard', 'mkwaf_leaderboard_shortcode');
