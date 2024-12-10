<?php
defined('ABSPATH') || exit;

/**
 * Ajax handler for Get a quote functionality
 *
 * @hook wp_ajax_nopriv_updateAccountInformation
 * @hook wp_ajax_updateAccountInformation
 */
add_action('wp_ajax_nopriv_dbwCostCalcGetQuote', 'dbw_cost_calc_get_quote');
add_action('wp_ajax_dbwCostCalcGetQuote', 'dbw_cost_calc_get_quote');
function dbw_cost_calc_get_quote() {
	// Verify the nonce to ensure the request is valid and secure
	if (!wp_verify_nonce($_POST['nonce'], 'dbw-cost-calculator')) {
		wp_send_json_error([
			'message' => 'An error occurred while executing the request. Please reload the page and try again.',
			'error' => 'Failed to verify nonce field.'
		]);
	}

	$to = get_option('dbw-cost-calculator-recipients');

	$subject = 'Quote from dbWatch';

	// Start building the email message
	$json_data = [
		'instances' => [],
		'addons' => [],
		'priceBeforeDiscount' => dbw_cost_calc_format_price_for_json($_POST['priceBeforeDiscount']),
		'discount' => dbw_cost_calc_format_price_for_json($_POST['discountAmount']),
		'yearlyPricePerInstance' => dbw_cost_calc_format_price_for_json($_POST['totalPricePerInstance']),
		'totalPricePerMonth' => dbw_cost_calc_format_price_for_json($_POST['totalPricePerMonth']),
		'totalPricePerYear' => dbw_cost_calc_format_price_for_json($_POST['priceAfterDiscount']),
		'userInformation' => [
			'email' => sanitize_text_field($_POST['email']),
			'name' => sanitize_text_field($_POST['name']),
			'companyName' => sanitize_text_field($_POST['company'])
		]
	];
	$message = '<table style="width:100%">';

	// Add instances information to the email if available
	if (!empty($_POST['instances'])) {
		$message .= '<tr><th colspan="2">Instances</th></tr>';
		foreach ($_POST['instances'] as $instance) {
			$json_data['instances'][] = [
				'name' => sanitize_text_field($instance['name']),
				'quantity' => intval($instance['qty'])
			];
			$message .= sprintf('<tr><td>%s</td><td>%s</td></tr>', esc_html($instance['name']), intval($instance['qty']));
		}
	}

	// Add addons information to the email if available
	if (!empty($_POST['addons'])) {
		$message .= '<tr><th colspan="2">Addons</th></tr>';
		foreach ($_POST['addons'] as $addon) {
			$json_data['addons'][] = [
				'name' => sanitize_text_field($addon['name']),
				'quantity' => intval($addon['qty'])
			];
			$message .= sprintf('<tr><td>%s</td><td>%s</td></tr>', esc_html($addon['name']), intval($addon['qty']));
		}
	}

	// Add price information to the email
	$message .= '<tr><th colspan="2">Price information</th></tr>';
	$message .= '<tr><td>Price before discount</td><td>' . esc_html($_POST['priceBeforeDiscount']) . '</td></tr>';
	$message .= '<tr><td>Discount</td><td>' . esc_html($_POST['discountAmount']) . '</td></tr>';
	$message .= '<tr><td>Yearly price per instance</td><td>' . esc_html($_POST['totalPricePerInstance']) . '</td></tr>';
	$message .= '<tr><td>Total price per month</td><td>' . esc_html($_POST['totalPricePerMonth']) . '</td></tr>';
	$message .= '<tr><td>Total price per year</td><td>' . esc_html($_POST['priceAfterDiscount']) . '</td></tr>';
	$message .= '<tr><th colspan="2">User information</th></tr>';

	// Add user information to the email
	$message .= '<tr><td>Email</td><td>' . esc_html($_POST['email']) . '</td></tr>';
	$message .= '<tr><td>Name</td><td>' . esc_html($_POST['name']) . '</td></tr>';
	$message .= '<tr><td>Company name</td><td>' . esc_html($_POST['company']) . '</td></tr>';
	$message .= '<tr><td>Comments</td><td>' . esc_html($_POST['comments']) . '</td></tr>';
	$message .= '</table>';

	$message = '---BEGIN JSON---' . json_encode($json_data) . '---END JSON---' . PHP_EOL . $message;

	// Set the email headers
	$headers = [
		'From: dbWatch Cost Calculator <noreply@dbwatch.com>',
		'Content-type: text/html; charset=utf-8'
	];

	// Send the email and return the appropriate response
	if (wp_mail($to, $subject, $message, $headers)) {
		wp_send_json_success();
	} else {
		wp_send_json_error([
			'message' => 'An error occurred while executing the request.',
			'error' => 'The wp_mail function returned false.'
		]);
	}
}

/**
 * Formats a price for JSON output.
 *
 * This function sanitizes the input price, removes any dollar signs
 * and commas, and converts the sanitized string to a floating-point number.
 *
 * @param string $price The price to be formatted.
 * @return float The formatted price as a float.
 */
function dbw_cost_calc_format_price_for_json($price) {
	$price = sanitize_text_field($price);
	$price = str_replace(['$', ','], '', $price);
	return floatval($price);
}