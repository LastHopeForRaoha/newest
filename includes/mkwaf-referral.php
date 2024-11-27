<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Generate Referral Code
function mkwaf_generate_referral_code($user_id) {
    $referral_code = md5($user_id . uniqid());
    update_user_meta($user_id, 'mkwaf_referral_code', $referral_code);
    return $referral_code;
}

// Track Referrals
function mkwaf_track_referral($referral_code) {
    $user_id = get_user_by_meta_key_and_value('mkwaf_referral_code', $referral_code);

    if ($user_id) {
        $current_referrals = (int) get_user_meta($user_id, 'mkwaf_referrals', true);
        update_user_meta($user_id, 'mkwaf_referrals', $current_referrals + 1);

        // Award points for a successful referral
        mkwaf_add_points($user_id, 50, 'referral', 'Successful referral');
    }
}

// Referral Shortcode
function mkwaf_referral_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/login">log in</a> to view your referral stats.</p>';
    }

    $user_id = get_current_user_id();
    $referral_code = get_user_meta($user_id, 'mkwaf_referral_code', true);

    if (!$referral_code) {
        $referral_code = mkwaf_generate_referral_code($user_id);
    }

    $referrals = (int) get_user_meta($user_id, 'mkwaf_referrals', true);

    ob_start();
    ?>
    <div class="mkwaf-referrals">
        <h2>Your Referrals</h2>
        <p><strong>Referral Code:</strong> <?php echo esc_html($referral_code); ?></p>
        <p><strong>Successful Referrals:</strong> <?php echo intval($referrals); ?></p>
        <p>Share your referral code with friends to earn rewards!</p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwaf_referral', 'mkwaf_referral_shortcode');
