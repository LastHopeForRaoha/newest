<?php
/**
 * Plugin Name: MKWA Fitness
 * Plugin URI: https://yoursite.com/mkwa-fitness
 * Description: A comprehensive fitness tracking and gamification system
 * Version: 1.0.0
 * Author: LastHopeForRaoha
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mkwa-fitness
 * Domain Path: /languages
 *
 * @package MkwaFitness
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MKWA_VERSION', '1.0.0');
define('MKWA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MKWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKWA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MKWA_CURRENT_TIME', current_time('mysql'));

// Define error logging function
if (!function_exists('mkwa_log')) {
    function mkwa_log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

// Include Required Files
try {
    require_once MKWA_PLUGIN_DIR . 'includes/constants.php'; // Constants
    require_once MKWA_PLUGIN_DIR . 'includes/functions.php'; // Utility functions
    require_once MKWA_PLUGIN_DIR . 'includes/points-functions.php'; // Points System
    require_once MKWA_PLUGIN_DIR . 'includes/badge-system.php'; // Badge System
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-frontend.php'; // Frontend logic
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-ajax.php'; // AJAX handlers
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-leaderboard.php'; // Leaderboard logic
    require_once MKWA_PLUGIN_DIR . 'includes/mkwaf-dashboard.php'; // Dashboard
    require_once MKWA_PLUGIN_DIR . 'includes/mkwaf-leaderboard.php'; // Leaderboard display
    require_once MKWA_PLUGIN_DIR . 'includes/mkwaf-referral.php'; // Referral System
    require_once MKWA_PLUGIN_DIR . 'includes/mkwaf-points-system.php'; // Missing file
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-activator.php';

} catch (Exception $e) {
    mkwa_log('Error loading required files: ' . $e->getMessage());
}

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    try {
        $prefix = 'MKWA_';
        $base_dir = MKWA_PLUGIN_DIR . 'includes/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . 'class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';

        if (file_exists($file)) {
            require $file;
            mkwa_log("Successfully loaded class file: $file");
        } else {
            mkwa_log("Class file not found: $file");
        }
    } catch (Exception $e) {
        mkwa_log('Error in autoloader: ' . $e->getMessage());
    }
});

// Plugin Activation Hook
// register_activation_hook(__FILE__, ['MKWA_Activator', 'activate']);


// Enqueue Plugin Assets
function mkwaf_enqueue_assets() {
    // CSS Files
    wp_enqueue_style('mkwaf-dashboard-css', MKWA_PLUGIN_URL . 'assets/css/mkwaf-dashboard.css', [], MKWA_VERSION);
    wp_enqueue_style('mkwaf-leaderboard-css', MKWA_PLUGIN_URL . 'assets/css/mkwaf-leaderboard.css', [], MKWA_VERSION);
    wp_enqueue_style('mkwa-fitness-css', MKWA_PLUGIN_URL . 'assets/css/mkwa-fitness.css', [], MKWA_VERSION);
    wp_enqueue_style('mkwa-frontend-css', MKWA_PLUGIN_URL . 'assets/css/frontend.css', [], MKWA_VERSION);

    // JavaScript Files
    wp_enqueue_script('mkwa-notifications-js', MKWA_PLUGIN_URL . 'assets/js/notifications.js', ['jquery'], MKWA_VERSION, true);
    wp_enqueue_script('mkwa-fitness-js', MKWA_PLUGIN_URL . 'assets/js/mkwa-fitness.js', ['jquery'], MKWA_VERSION, true);
    wp_enqueue_script('mkwa-frontend-js', MKWA_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], MKWA_VERSION, true);
}

// Main Plugin Class
final class MKWA_Fitness {
    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function init_plugin() {
        load_plugin_textdomain('mkwa-fitness', false, dirname(MKWA_PLUGIN_BASENAME) . '/languages');
    }

    public function add_admin_menu() {
        add_menu_page(
            __('MKWA Fitness', 'mkwa-fitness'),
            __('MKWA Fitness', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness',
            [$this, 'render_dashboard_page'],
            'dashicons-universal-access',
            30
        );

        add_submenu_page(
            'mkwa-fitness',
            __('Leaderboard', 'mkwa-fitness'),
            __('Leaderboard', 'mkwa-fitness'),
            'manage_options',
            'mkwa-leaderboard',
            [$this, 'render_leaderboard_page']
        );
    }

    public function render_dashboard_page() {
        echo '<div class="wrap"><h1>MKWA Fitness Dashboard</h1>';
        echo do_shortcode('[mkwaf_dashboard]'); // Dashboard shortcode
        echo '</div>';
    }

    public function render_leaderboard_page() {
        echo '<div class="wrap"><h1>MKWA Fitness Leaderboard</h1>';
        echo do_shortcode('[mkwaf_leaderboard]'); // Leaderboard shortcode
        echo '</div>';
    }
}

// Initialize the Plugin
function mkwa_fitness() {
    return MKWA_Fitness::instance();
}
mkwa_fitness();
