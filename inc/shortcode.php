<?php
defined('ABSPATH') || exit;

/**
 * Register the shortcode [dbw-cost-calc] and its callback function
 */
add_shortcode('dbw-cost-calc', 'dbw_cost_calc_shortcode');
function dbw_cost_calc_shortcode()
{
	// Enqueue the plugin's styles and scripts
	wp_enqueue_style('dbw-cost-calc');
	wp_enqueue_script('dbw-cost-calc');

	// Start output buffering
	ob_start();

	echo '<div class="dbw-cost-calc-wrapper">';

	// Include the necessary template files
	include DBW_COST_CALC_PATH . 'templates/head.php';
	include DBW_COST_CALC_PATH . 'templates/calc.php';
	include DBW_COST_CALC_PATH . 'templates/terms.php';

	echo '</div>';

	// Return the buffered content
	return ob_get_clean();
}