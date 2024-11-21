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
	wp_localize_script('dbw-cost-calc', 'dbwCostCalcData', dbw_cost_calc_data());

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

/**
 * Get Cost Calculator data for use in JS
 *
 * @return array
 */
function dbw_cost_calc_data()
{
	// Get instance types from the options table
	$types_raw = get_option('dbw-cost-calculator-instance-types');
	$instances = [];
	if (is_array($types_raw)) {
		for ($i = 0; $i < count($types_raw['name']); $i++) {
			$instances[sanitize_key($types_raw['name'][$i])] = [
				'name' => $types_raw['name'][$i],
				'price' => $types_raw['price'][$i]
			];
		}
	}

	// Get addons from the options table
	$addons_raw = get_option('dbw-cost-calculator-addons');
	$addons = [];
	if (is_array($addons_raw)) {
		for ($i = 0; $i < count($addons_raw['name']); $i++) {
			$addons[sanitize_key($addons_raw['name'][$i])] = [
				'name' => $addons_raw['name'][$i],
				'price' => $addons_raw['price'][$i]
			];
		}
	}

	// Get discount rates from the options table
	$rates_raw = get_option('dbw-cost-calculator-discount-rates');
	$rates = [];
	if (is_array($rates_raw)) {
		for ($i = 0; $i < count($rates_raw['min_qty']); $i++) {
			$rates[] = [
				'minQty' => $rates_raw['min_qty'][$i],
				'discount' => $rates_raw['discount'][$i]
			];
		}
	}

	// Return the collected data as an array
	return [
		'instances' => $instances,
		'addons' => $addons,
		'discountRates' => $rates,
		'ajax' => [
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('dbw-cost-calculator')
		]
	];
}