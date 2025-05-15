<?php
defined('ABSPATH') || exit;

add_action('wp_ajax_nopriv_dbwCostCalcGetQuote', 'dbw_cost_calc_get_quote');
add_action('wp_ajax_dbwCostCalcGetQuote', 'dbw_cost_calc_get_quote');

function dbw_cost_calc_get_quote() {
	if (!wp_verify_nonce($_POST['nonce'], 'dbw-cost-calculator')) {
		wp_send_json_error([
			'message' => 'An error occurred while executing the request. Please reload the page and try again.',
			'error' => 'Failed to verify nonce field.'
		]);
	}

	$to = get_option('dbw-cost-calculator-recipients');
	$subject = 'Quote from dbWatch';

	$currency = sanitize_text_field($_POST['currency']);
	$supportLevel = sanitize_text_field($_POST['supportLevel']);
	$subscriptionTerm = intval($_POST['subscriptionTerm']);
	$volumeDiscount = floatval($_POST['volumeDiscount']);
	$supportPlanIncrease = floatval($_POST['supportPlanIncrease']);
	$termDiscount = floatval($_POST['termDiscount']);
	$totalPrice3Years = isset($_POST['totalPrice3Years']) ? floatval($_POST['totalPrice3Years']) : null;
	$totalPrice5Years = isset($_POST['totalPrice5Years']) ? floatval($_POST['totalPrice5Years']) : null;

	$json_data = [
		'instances' => [],
		'addons' => [],
		'currency' => $currency,
		'supportLevel' => $supportLevel,
		'subscriptionTerm' => $subscriptionTerm,
		'priceBeforeDiscount' => dbw_cost_calc_format_price_for_json($_POST['priceBeforeDiscount']),
		'volumeDiscount' => $volumeDiscount,
		'supportPlanIncrease' => $supportPlanIncrease,
		'termDiscount' => $termDiscount,
		'yearlyPricePerInstance' => dbw_cost_calc_format_price_for_json($_POST['totalPricePerInstance']),
		'totalPricePerMonth' => dbw_cost_calc_format_price_for_json($_POST['totalPricePerMonth']),
		'totalPricePerYear' => dbw_cost_calc_format_price_for_json($_POST['totalPricePerYear']),
		'totalPrice3Years' => $totalPrice3Years,
		'totalPrice5Years' => $totalPrice5Years,
		'userInformation' => [
			'email' => sanitize_text_field($_POST['email']),
			'name' => sanitize_text_field($_POST['name']),
			'companyName' => sanitize_text_field($_POST['company'])
		]
	];

	$message = '<table style="width:100%">';

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

	$message .= '<tr><th colspan="2">Price information</th></tr>';
	$message .= '<tr><td>Currency</td><td>' . esc_html($currency) . '</td></tr>';
	$message .= '<tr><td>Support Level</td><td>' . esc_html($supportLevel) . '</td></tr>';
	$message .= '<tr><td>Subscription Term</td><td>' . esc_html($subscriptionTerm) . ' year(s)</td></tr>';
	$message .= '<tr><td>Price before discount</td><td>' . esc_html($_POST['priceBeforeDiscount']) . '</td></tr>';
	$message .= '<tr><td>Volume discount</td><td>' . esc_html($volumeDiscount) . '</td></tr>';
	$message .= '<tr><td>Support plan increase</td><td>' . esc_html($supportPlanIncrease) . '</td></tr>';
	$message .= '<tr><td>Term discount</td><td>' . esc_html($termDiscount) . '</td></tr>';
	$message .= '<tr><td>Yearly price per instance</td><td>' . esc_html($_POST['totalPricePerInstance']) . '</td></tr>';
	$message .= '<tr><td>Total price per month</td><td>' . esc_html($_POST['totalPricePerMonth']) . '</td></tr>';
	$message .= '<tr><td>Total price per year</td><td>' . esc_html($_POST['totalPricePerYear']) . '</td></tr>';

	if ($totalPrice3Years !== null) {
		$message .= '<tr><td>Total Price (3 Years)</td><td>' . esc_html($totalPrice3Years) . '</td></tr>';
	}
	if ($totalPrice5Years !== null) {
		$message .= '<tr><td>Total Price (5 Years)</td><td>' . esc_html($totalPrice5Years) . '</td></tr>';
	}

	$message .= '<tr><th colspan="2">User information</th></tr>';
	$message .= '<tr><td>Email</td><td>' . esc_html($_POST['email']) . '</td></tr>';
	$message .= '<tr><td>Name</td><td>' . esc_html($_POST['name']) . '</td></tr>';
	$message .= '<tr><td>Company name</td><td>' . esc_html($_POST['company']) . '</td></tr>';
	$message .= '<tr><td>Comments</td><td>' . esc_html($_POST['comments']) . '</td></tr>';
	$message .= '</table>';

	$message = '---BEGIN JSON---' . json_encode($json_data) . '---END JSON---' . PHP_EOL . $message;

	$headers = [
		'From: dbWatch Cost Calculator <noreply@dbwatch.com>',
		'Content-type: text/html; charset=utf-8'
	];

	if (wp_mail($to, $subject, $message, $headers)) {
		wp_send_json_success();
	} else {
		wp_send_json_error([
			'message' => 'An error occurred while executing the request.',
			'error' => 'The wp_mail function returned false.'
		]);
	}
}

function dbw_cost_calc_format_price_for_json($price) {
	$price = sanitize_text_field($price);
	$price = str_replace(['$', ','], '', $price);
	return floatval($price);
}