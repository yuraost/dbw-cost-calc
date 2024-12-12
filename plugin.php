<?php
/**
 * Plugin Name: dbWatch Cost Calculator
 * Description: The dbWatch Cost Calculator is a powerful and user-friendly plugin designed to help you easily calculate the cost of licenses for dbWatch Control Center. This plugin provides a simple interface where users can input their requirements and instantly get an accurate estimate of the licensing costs.
 * Version: 1.2
 * Author: Yurii Ostapchuk
 */

// Prevent direct access to the file
defined('ABSPATH') || exit;

// Define constants for the plugin version, path, and URL
define('DBW_COST_CALC_VERSION', '1.1');
define('DBW_COST_CALC_PATH', realpath(plugin_dir_path(__FILE__)) . '/');
define('DBW_COST_CALC_URL', plugin_dir_url(__FILE__));

// Register plugin styles and scripts
add_action('wp_enqueue_scripts', 'dbw_cost_calc_register_assets');
function dbw_cost_calc_register_assets()
{
	wp_register_style('dbw-cost-calc', DBW_COST_CALC_URL . 'assets/css/dbw-cost-calc.css', [], DBW_COST_CALC_VERSION);
	wp_register_script('dbw-cost-calc', DBW_COST_CALC_URL . 'assets/js/dbw-cost-calc.js', ['jquery'], DBW_COST_CALC_VERSION, true);
}

// Register plugin admin styles and scripts
add_action('admin_enqueue_scripts', 'dbw_cost_calc_register_admin_assets');
function dbw_cost_calc_register_admin_assets()
{
	wp_register_style('dbw-cost-calc-admin', DBW_COST_CALC_URL . 'assets/css/dbw-cost-calc-admin.css', [], DBW_COST_CALC_VERSION);
	wp_register_script('dbw-cost-calc-admin', DBW_COST_CALC_URL . 'assets/js/dbw-cost-calc-admin.js', ['jquery'], DBW_COST_CALC_VERSION, true);
}

// Include the wp-admin functionality
require_once DBW_COST_CALC_PATH . 'inc/admin.php';

// Include the shortcode file
require_once DBW_COST_CALC_PATH . 'inc/shortcode.php';

// Include the AJAX handlers
require_once DBW_COST_CALC_PATH . 'inc/ajax.php';