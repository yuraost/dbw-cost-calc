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
	$types = get_option('dbw-cost-calculator-instance-types');
	$types = array_combine(
		array_map(
			'sanitize_key',
			$types['name']
		),
		$types['price']
	);

	// Get addons from the options table
	$addons = get_option('dbw-cost-calculator-addons');
	$addons = array_combine(
		array_map(
			'sanitize_key',
			$addons['name']
		),
		$addons['price']
	);

	// Get discount rates from the options table
	$rates_raw = get_option('dbw-cost-calculator-discount-rates');
	$rates = [];
	for ($i = 0; $i < count($rates_raw['min_qty']); $i++) {
		$rates[] = [
			'minQty' => $rates_raw['min_qty'][$i],
			'discount' => $rates_raw['discount'][$i]
		];
	}

	// Return the collected data as an array
	return [
		'instancePricing' => $types,
		'addonsPricing' => $addons,
		'discountRates' => $rates
	];
}