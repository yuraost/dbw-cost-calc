<?php
defined('ABSPATH') || exit;

add_action('admin_menu', 'dbw_cost_calc_admin_menu');
function dbw_cost_calc_admin_menu() {
	add_menu_page(
		'dbWatch Control Center License Cost Calculator Settings',
		'Cost Calculator',
		'manage_options',
		'dbw-cost-calculator',
		'dbw_cost_calc_admin_page',
		'dashicons-calculator',
		80
	);
}

function dbw_cost_calc_admin_page() {
	// Enqueue admin styles and scripts
	wp_enqueue_style('dbw-cost-calc-admin');
	wp_enqueue_script('dbw-cost-calc-admin');
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>
        <form method="post" action="options.php">
			<?php
			settings_errors('dbw-cost-calculator-settings-errors');
			settings_fields('dbw-cost-calculator-group');
			do_settings_sections('dbw-cost-calculator');
			submit_button();
			?>
        </form>
	</div>
	<?php
}

add_action('admin_init',  'dbw_cost_calc_settings_fields' );
function dbw_cost_calc_settings_fields() {
	add_settings_section(
		'dbw-cost-calculator-section',
		'',
		'',
		'dbw-cost-calculator'
	);

	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-instance-types', 'dbw_cost_calc_settings_field_instance_types_sanitize');
	add_settings_field(
		'dbw-cost-calculator-instance-types',
		'Instance Types',
		'dbw_cost_calc_settings_field_instance_types',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);

	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-discount-rates', 'dbw_cost_calc_settings_field_discount_rates_sanitize');
	add_settings_field(
		'dbw-cost-calculator-discount-rates',
		'Discount Rates',
		'dbw_cost_calc_settings_field_discount_rates',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);

	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-addons', 'dbw_cost_calc_settings_field_addons_sanitize');
	add_settings_field(
		'dbw-cost-calculator-addons',
		'Addons',
		'dbw_cost_calc_settings_field_addons',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);
}

function dbw_cost_calc_settings_field_instance_types() {
    $types_raw = get_option('dbw-cost-calculator-instance-types');
	$types = [];
	if (!empty($types_raw['name']) && is_array($types_raw['name']) &&
		!empty($types_raw['price']) && is_array($types_raw['price']) &&
		count($types_raw['name']) === count($types_raw['price'])
	) {
		for ($i = 0; $i < count($types_raw['name']); $i++) {
			$types[] = [
				'name' => $types_raw['name'][$i],
				'price' => $types_raw['price'][$i]
			];
		}
	} else {
		$types[] = [
			'name' => '',
			'price' => ''
		];
	} ?>

    <table class="dbw-cost-calc-settings-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="instance-types-list">
            <?php foreach ($types as $type) { ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[name][]" value="<?= esc_attr($type['name']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[price][]" value="<?= esc_attr($type['price']); ?>" required /></td>
                    <td><a class="button button-secondary remove-instance-type" href="#">Remove</a></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><a id="add-instance-type" class="button button-primary" href="#">Add Instance Type</a></td>
            </tr>
        </tfoot>
    </table>
    <?php
}

function dbw_cost_calc_settings_field_instance_types_sanitize($types) {
	if (!empty($types['name']) && is_array($types['name']) &&
		!empty($types['price']) && is_array($types['price']) &&
        count($types['name']) === count($types['price'])
    ) {
	    $types['name'] = array_map('sanitize_text_field', $types['name']);
	    $types['price'] = array_map('floatval', $types['price']);
    } else {
        add_settings_error(
			'dbw-cost-calculator-settings-errors',
			'empty-value',
			'Instance Types can\'t be empty.',
			'error'
		);
		$types = get_option('dbw-cost-calculator-instance-types');
    }

	return $types;
}

function dbw_cost_calc_settings_field_discount_rates() {
	$rates_raw = get_option('dbw-cost-calculator-discount-rates');
	$rates = [];
    if (!empty($rates_raw['min_qty']) && is_array($rates_raw['min_qty']) &&
	    !empty($rates_raw['discount']) && is_array($rates_raw['discount']) &&
	    count($rates_raw['min_qty']) === count($rates_raw['discount'])
    ) {
	    for ($i = 0; $i < count($rates_raw['min_qty']); $i++) {
		    $rates[] = [
			    'min_qty' => $rates_raw['min_qty'][$i],
			    'discount' => $rates_raw['discount'][$i]
		    ];
	    }
    } ?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
        <tr>
            <th>Min Quantity</th>
            <th>Discount</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="discount-rates-list">
            <?php foreach ($rates as $rate) { ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-discount-rates[min_qty][]" value="<?= esc_attr($rate['min_qty']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-discount-rates[discount][]" value="<?= esc_attr($rate['discount']); ?>" required /></td>
                    <td><a class="button button-secondary remove-discount-rate" href="#">Remove</a></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3"><a id="add-discount-rate" class="button button-primary" href="#">Add Discount Rate</a></td>
        </tr>
        </tfoot>
    </table>
	<?php
}

function dbw_cost_calc_settings_field_discount_rates_sanitize($rates) {
	if (!empty($rates['min_qty']) && is_array($rates['min_qty']) &&
		!empty($rates['discount']) && is_array($rates['discount']) &&
		count($rates['min_qty']) === count($rates['discount'])
    ) {
		$rates['min_qty'] = array_map('floatval', $rates['min_qty']);
		$rates['discount'] = array_map('floatval', $rates['discount']);
	} else {
		$rates = [];
	}

	return $rates;
}

function dbw_cost_calc_settings_field_addons() {
	$addons_raw = get_option('dbw-cost-calculator-addons');
	$addons = [];
	if (!empty($addons_raw['name']) && is_array($addons_raw['name']) &&
		!empty($addons_raw['price']) && is_array($addons_raw['price']) &&
		!empty($addons_raw['platforms']) && is_array($addons_raw['platforms']) &&
		count($addons_raw['name']) === count($addons_raw['price']) &&
        count($addons_raw['name']) === count($addons_raw['platforms'])
	) {
		for ($i = 0; $i < count($addons_raw['name']); $i++) {
			$addons[] = [
				'name' => $addons_raw['name'][$i],
				'price' => $addons_raw['price'][$i],
                'platforms' => $addons_raw['platforms'][$i]
			];
		}
	} ?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Platforms</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="addons-list">
            <?php foreach ($addons as $addon) { ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-addons[name][]" value="<?= esc_attr($addon['name']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[price][]" value="<?= esc_attr($addon['price']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[platforms][]" value="<?= esc_attr($addon['platforms']); ?>" required /></td>
                    <td><a class="button button-secondary remove-addon" href="#">Remove</a></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4"><a id="add-addon" class="button button-primary" href="#">Add Addon</a></td>
        </tr>
        </tfoot>
    </table>
	<?php
}

function dbw_cost_calc_settings_field_addons_sanitize($addons) {
	if (!empty($addons['name']) && is_array($addons['name']) &&
		!empty($addons['price']) && is_array($addons['price']) &&
		!empty($addons['platforms']) && is_array($addons['platforms']) &&
		count($addons['name']) === count($addons['price']) &&
		count($addons['name']) === count($addons['platforms'])
	) {
		$addons['name'] = array_map('sanitize_text_field', $addons['name']);
		$addons['price'] = array_map('floatval', $addons['price']);
		$addons['platforms'] = array_map('sanitize_text_field', $addons['platforms']);
	} else {
		$addons = [];
	}

	return $addons;
}

add_action('admin_notices', 'dbw_cost_calculator_notices');
function dbw_cost_calculator_notices() {
	$settings_errors = get_settings_errors( 'dbw-cost-calculator-settings-errors' );

    // if we have any errors, exit
	if (!empty($settings_errors)) {
		return;
	}

	if (isset($_GET['page']) && $_GET['page'] === 'dbw-cost-calculator' &&
        isset($_GET['settings-updated']) && $_GET['settings-updated'] == true)
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Cost Calculator settings saved.</strong></p>
        </div>
        <?php
    }
}
