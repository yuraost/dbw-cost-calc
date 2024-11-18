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
			'message' => 'An error occurred while executing the request. Please reload the page and try again.'
		]);
	}

	$to = get_option('dbw-cost-calculator-recipients');

	$subject = 'Quote from dbWatch';

	// Start building the email message with basic styling
	$message = '<style>table{width: 100%}</style>';
	$message .= '<table>';

	// Add instances information to the email if available
	if (!empty($_POST['instances'])) {
		$message .= '<tr><th colspan="2">Instances</th></tr>';
		foreach ($_POST['instances'] as $instance) {
			$message .= sprintf('<tr><td>%s</td><td>%s</td></tr>', esc_html($instance['name']), esc_html($instance['qty']));
		}
	}

	// Add addons information to the email if available
	if (!empty($_POST['addons'])) {
		$message .= '<tr><th colspan="2">Addons</th></tr>';
		foreach ($_POST['addons'] as $addons) {
			$message .= sprintf('<tr><td>%s</td><td>%s</td></tr>', esc_html($addons['name']), esc_html($addons['qty']));
		}
	}

	// Add price information to the email
	$message .= '<tr><th colspan="2">Price information</th></tr>';
	$message .= '<tr><td>Price before discount</td><td>' . esc_html($_POST['priceBeforeDiscount']) . '</td></tr>';
	$message .= '<tr><td>Discount amount</td><td>' . esc_html($_POST['discountAmount']) . '</td></tr>';
	$message .= '<tr><td>Total price per instance</td><td>' . esc_html($_POST['totalPricePerInstance']) . '</td></tr>';
	$message .= '<tr><td>Total price per month</td><td>' . esc_html($_POST['totalPricePerMonth']) . '</td></tr>';
	$message .= '<tr><td>Price after discount</td><td>' . esc_html($_POST['priceAfterDiscount']) . '</td></tr>';
	$message .= '<tr><th colspan="2">User information</th></tr>';

	// Add user information to the email
	$message .= '<tr><td>Email</td><td>' . esc_html ($_POST['email']) . '</td></tr>';
	$message .= '<tr><td>Name</td><td>' . esc_html($_POST['name']) . '</td></tr>';
	$message .= '<tr><td>Company name</td><td>' . esc_html($_POST['company']) . '</td></tr>';
	$message .= '</table>';

	// Set the email headers
	$headers = [
		'From: dbWatch Cost Calculator',
		'Content-type: text/html; charset=utf-8'
	];

	// Send the email and return the appropriate response
	if (wp_mail($to, $subject, $message, $headers)) {
		wp_send_json_success();
	} else {
		wp_send_json_error([
			'message' => 'An error occurred while sending the quota.'
		]);
	}
}