<?php
defined('ABSPATH') || exit;

/**
 * Add a new menu item to the admin menu
 */
add_action('admin_menu', 'dbw_cost_calc_admin_menu');
function dbw_cost_calc_admin_menu()
{
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

/**
 * Display the admin page for the cost calculator
 */
function dbw_cost_calc_admin_page()
{
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

/**
 * Register settings and fields for the cost calculator
 */
add_action('admin_init',  'dbw_cost_calc_settings_fields' );
function dbw_cost_calc_settings_fields()
{
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

	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-recipients', 'dbw_cost_calc_settings_field_recipients_sanitize');
	add_settings_field(
		'dbw-cost-calculator-recipients',
		'Recipients',
		'dbw_cost_calc_settings_field_recipients',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);

	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-terms', 'sanitize_textarea_field');
	add_settings_field(
		'dbw-cost-calculator-terms',
		'Usage and Subscription Terms',
		'dbw_cost_calc_settings_field_terms',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);

	// Register and display support levels
	register_setting('dbw-cost-calculator-group', 'dbw-cost-calculator-support-levels', 'dbw_cost_calc_settings_field_support_levels_sanitize');
	add_settings_field(
		'dbw-cost-calculator-support-levels',
		'Support Levels',
		'dbw_cost_calc_settings_field_support_levels',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);
	register_setting(
		'dbw-cost-calculator-group',
		'dbw-cost-calculator-term-discounts',
		'dbw_cost_calc_settings_field_term_discounts_sanitize'
	);
	
	add_settings_field(
		'dbw-cost-calculator-term-discounts',
		'Term Discounts',
		'dbw_cost_calc_settings_field_term_discounts',
		'dbw-cost-calculator',
		'dbw-cost-calculator-section'
	);
}

/**
 * Display the Support Levels settings fields in the admin panel.
 *
 * This function outputs a table with input fields for configuring 
 * percentage price increases for each support level (Basic, Advanced, Premium).
 */
function dbw_cost_calc_settings_field_support_levels() {
    // Retrieve the settings option, ensuring it's an array
    $support_levels_raw = get_option('dbw-cost-calculator-support-levels', []);

    // If $support_levels_raw isn't an array, initialize it as an empty array
    if (!is_array($support_levels_raw)) {
        // If the data is corrupted, delete and reset to default
        delete_option('dbw-cost-calculator-support-levels');
        $support_levels_raw = [];
    }

    // Define default settings for support levels
    $defaults = [
        'basic' => [
            'title' => 'Basic Support', 
            'percent' => 0, 
            'learn_more' => '', 
            'features' => []
        ],
        'advanced' => [
            'title' => 'Advanced Support', 
            'percent' => 10, 
            'learn_more' => '', 
            'features' => []
        ],
        'premium' => [
            'title' => 'Premium Support', 
            'percent' => 25, 
            'learn_more' => '', 
            'features' => []
        ]
    ];

    // Merge saved settings with defaults
    $support_levels = wp_parse_args($support_levels_raw, $defaults);

    // Ensure each support level (basic, advanced, premium) is an array
    foreach (['basic', 'advanced', 'premium'] as $key) {
        if (!isset($support_levels[$key]) || !is_array($support_levels[$key])) {
            $support_levels[$key] = $defaults[$key]; // Fallback to default if not an array
        }
    }

    ?>

    <h4>Support Level Settings</h4>
    <table class="dbw-cost-calc-settings-table widefat">
        <thead>
            <tr>
                <th>Support Key</th>
                <th>Title</th>
                <th>Price Increase (%)</th>
                <th>Learn More URL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (['basic', 'advanced', 'premium'] as $key):
                $level = isset($support_levels[$key]) && is_array($support_levels[$key]) ? $support_levels[$key] : [];
            ?>
                <tr>
                    <td><strong><?= ucfirst($key); ?></strong></td>
                    <td><input type="text" name="dbw-cost-calculator-support-levels[<?= $key; ?>][title]" value="<?= esc_attr($level['title'] ?? ''); ?>" class="regular-text" /></td>
                    <td><input type="number" name="dbw-cost-calculator-support-levels[<?= $key; ?>][percent]" value="<?= esc_attr($level['percent'] ?? 0); ?>" style="width: 80px;" /></td>
                    <td><input type="url" name="dbw-cost-calculator-support-levels[<?= $key; ?>][learn_more]" value="<?= esc_url($level['learn_more'] ?? ''); ?>" class="regular-text" /></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4 style="margin-top:2em;">Features for Each Support Level</h4>

    <!-- Basic Support Level Features -->
    <h5>Basic Support Features</h5>
    <table class="support-feature-table widefat">
        <thead>
            <tr>
                <th>Feature</th>
                <th>Included</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="basic-feature-list">
            <?php foreach ($support_levels['basic']['features'] as $index => $feature): ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-support-levels[basic][features][<?= $index; ?>][text]" value="<?= esc_attr($feature['text']); ?>" class="regular-text" /></td>
                    <td style="text-align: center;">
                        <input type="checkbox" name="dbw-cost-calculator-support-levels[basic][features][<?= $index; ?>][included]" <?= !empty($feature['included']) ? 'checked' : ''; ?> />
                    </td>
                    <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <a href="#" class="button button-primary" id="add-basic-feature-row">Add Feature</a>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Advanced Support Level Features -->
    <h5>Advanced Support Features</h5>
    <table class="support-feature-table widefat">
        <thead>
            <tr>
                <th>Feature</th>
                <th>Included</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="advanced-feature-list">
            <?php foreach ($support_levels['advanced']['features'] as $index => $feature): ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-support-levels[advanced][features][<?= $index; ?>][text]" value="<?= esc_attr($feature['text']); ?>" class="regular-text" /></td>
                    <td style="text-align: center;">
                        <input type="checkbox" name="dbw-cost-calculator-support-levels[advanced][features][<?= $index; ?>][included]" <?= !empty($feature['included']) ? 'checked' : ''; ?> />
                    </td>
                    <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <a href="#" class="button button-primary" id="add-advanced-feature-row">Add Feature</a>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Premium Support Level Features -->
    <h5>Premium Support Features</h5>
    <table class="support-feature-table widefat">
        <thead>
            <tr>
                <th>Feature</th>
                <th>Included</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="premium-feature-list">
            <?php foreach ($support_levels['premium']['features'] as $index => $feature): ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-support-levels[premium][features][<?= $index; ?>][text]" value="<?= esc_attr($feature['text']); ?>" class="regular-text" /></td>
                    <td style="text-align: center;">
                        <input type="checkbox" name="dbw-cost-calculator-support-levels[premium][features][<?= $index; ?>][included]" <?= !empty($feature['included']) ? 'checked' : ''; ?> />
                    </td>
                    <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <a href="#" class="button button-primary" id="add-premium-feature-row">Add Feature</a>
                </td>
            </tr>
        </tfoot>
    </table>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add Feature Button for Basic Support
        document.getElementById('add-basic-feature-row').addEventListener('click', function (e) {
            e.preventDefault();
            const featureList = document.getElementById('basic-feature-list');
            const index = featureList.querySelectorAll('tr').length;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="dbw-cost-calculator-support-levels[basic][features][${index}][text]" class="regular-text" /></td>
                <td style="text-align: center;"><input type="checkbox" name="dbw-cost-calculator-support-levels[basic][features][${index}][included]" /></td>
                <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
            `;
            featureList.appendChild(row);
        });

        // Add Feature Button for Advanced Support
        document.getElementById('add-advanced-feature-row').addEventListener('click', function (e) {
            e.preventDefault();
            const featureList = document.getElementById('advanced-feature-list');
            const index = featureList.querySelectorAll('tr').length;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="dbw-cost-calculator-support-levels[advanced][features][${index}][text]" class="regular-text" /></td>
                <td style="text-align: center;"><input type="checkbox" name="dbw-cost-calculator-support-levels[advanced][features][${index}][included]" /></td>
                <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
            `;
            featureList.appendChild(row);
        });

        // Add Feature Button for Premium Support
        document.getElementById('add-premium-feature-row').addEventListener('click', function (e) {
            e.preventDefault();
            const featureList = document.getElementById('premium-feature-list');
            const index = featureList.querySelectorAll('tr').length;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="dbw-cost-calculator-support-levels[premium][features][${index}][text]" class="regular-text" /></td>
                <td style="text-align: center;"><input type="checkbox" name="dbw-cost-calculator-support-levels[premium][features][${index}][included]" /></td>
                <td><a href="#" class="button button-secondary remove-feature">Remove</a></td>
            `;
            featureList.appendChild(row);
        });

        // Remove Feature
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-feature')) {
                e.preventDefault();
                const row = e.target.closest('tr');
                const featureList = row.closest('tbody');
                if (featureList.querySelectorAll('tr').length > 1) {
                    row.remove();
                } else {
                    alert('At least one feature must remain.');
                }
            }
        });
    });
    </script>

<?php
}


/**
 * Sanitize the support levels input before saving to the database.
 *
 * Ensures all values submitted are converted to safe, valid floats.
 *
 */
function dbw_cost_calc_settings_field_support_levels_sanitize($levels) {
    $sanitized = [];

    // Loop through each support level (basic, advanced, premium)
    foreach ($levels as $key => $level) {
        // Sanitize title, percentage and learn_more URL
        $sanitized[$key] = [
            'title' => sanitize_text_field($level['title']),
            'percent' => floatval($level['percent']), // Ensure percent is a float
            'learn_more' => esc_url_raw($level['learn_more']), // Sanitize URL
            'features' => [] // Initialize features array
        ];

        // If features are provided, sanitize each feature
        if (!empty($level['features']) && is_array($level['features'])) {
            foreach ($level['features'] as $feature) {
                $sanitized[$key]['features'][] = [
                    'text' => sanitize_text_field($feature['text']), // Sanitize feature text
                    'included' => isset($feature['included']) ? (bool)$feature['included'] : false // Convert to boolean
                ];
            }
        }
    }

    return $sanitized; // Return sanitized data
}
/**
 * Display the instance types settings field
 */
function dbw_cost_calc_settings_field_instance_types()
{
    $types_raw = get_option('dbw-cost-calculator-instance-types');
	$types = [];
	if (!empty($types_raw['name']) && is_array($types_raw['name']) &&
		!empty($types_raw['price']) && is_array($types_raw['price']) &&
		count($types_raw['name']) === count($types_raw['price'])
	) {
		for ($i = 0; $i < count($types_raw['name']); $i++) {
			$types[] = [
				'name' => $types_raw['name'][$i],
				'price' => $types_raw['price'][$i],
				'link_label' => $types_raw['link_label'][$i] ?? '',
				'link_url' => $types_raw['link_url'][$i] ?? ''
			];
		}
	} else {
		$types[] = [
			'name' => '',
			'price' => '',
			'link_label' => '',
			'link_url' => ''
		];
	} ?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Link Label</th>
                <th>Link URL</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="instance-types-list">
            <?php foreach ($types as $type) { ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[name][]" value="<?= esc_attr($type['name']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[price][]" value="<?= esc_attr($type['price']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[link_label][]" value="<?= esc_attr($type['link_label']); ?>" /></td>
                    <td><input type="text" name="dbw-cost-calculator-instance-types[link_url][]" value="<?= esc_attr($type['link_url']); ?>" /></td>
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

/**
 * Sanitize the instance types settings field
 *
 * @param $types
 * @return false|mixed|void
 */
function dbw_cost_calc_settings_field_instance_types_sanitize($types)
{
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

/**
 * Display the discount rates settings field
 */
function dbw_cost_calc_settings_field_discount_rates()
{
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

/**
 * Sanitize the discount rates settings field
 *
 * @param $rates
 * @return array|mixed
 */
function dbw_cost_calc_settings_field_discount_rates_sanitize($rates)
{
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

/**
 * Display the addons settings field
 */
function dbw_cost_calc_settings_field_addons()
{
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
                'platforms' => $addons_raw['platforms'][$i],
				'link_label' => $addons_raw['link_label'][$i] ?? '',
				'link_url' => $addons_raw['link_url'][$i] ?? '',
			];
		}
	} ?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Platforms</th>
            <th>Link Label</th>
            <th>Link URL</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="addons-list">
            <?php foreach ($addons as $addon) { ?>
                <tr>
                    <td><input type="text" name="dbw-cost-calculator-addons[name][]" value="<?= esc_attr($addon['name']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[price][]" value="<?= esc_attr($addon['price']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[platforms][]" value="<?= esc_attr($addon['platforms']); ?>" required /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[link_label][]" value="<?= esc_attr($addon['link_label']); ?>" /></td>
                    <td><input type="text" name="dbw-cost-calculator-addons[link_url][]" value="<?= esc_attr($addon['link_url']); ?>" /></td>
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

/**
 * Sanitize the addons settings field
 *
 * @param $addons
 * @return array|mixed
 */
function dbw_cost_calc_settings_field_addons_sanitize($addons)
{
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

/**
 * Display the recipients settings field
 */
function dbw_cost_calc_settings_field_recipients()
{
	$recipients = get_option('dbw-cost-calculator-recipients');
    if (empty($recipients) || !is_array($recipients)) {
	    $recipients = [''];
    } ?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
        <tr>
            <th>Email</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="recipients-list">
		<?php foreach ($recipients as $recipient) { ?>
            <tr>
                <td><input type="email" name="dbw-cost-calculator-recipients[]" value="<?= esc_attr($recipient); ?>" required /></td>
                <td><a class="button button-secondary remove-recipient" href="#">Remove</a></td>
            </tr>
		<?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="2"><a id="add-recipient" class="button button-primary" href="#">Add Recipient</a></td>
        </tr>
        </tfoot>
    </table>
	<?php
}

/**
 * Sanitize the recipients settings field
 *
 * @param $recipients
 * @return false|mixed|void
 */
function dbw_cost_calc_settings_field_recipients_sanitize($recipients)
{
	if (!empty($recipients)) {
		$recipients = array_map('sanitize_email', $recipients);
	} else {
		add_settings_error(
			'dbw-cost-calculator-settings-errors',
			'empty-value',
			'Recipients can\'t be empty.',
			'error'
		);
		$recipients = get_option('dbw-cost-calculator-recipients');
	}

	return $recipients;
}

/**
 * Display the usage and subscription terms settings field
 */
function dbw_cost_calc_settings_field_terms()
{
	$terms = get_option('dbw-cost-calculator-terms');
	?>
    <table class="dbw-cost-calc-settings-table">
        <thead>
        <tr>
            <th>Content</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <textarea name="dbw-cost-calculator-terms" cols="90" rows="15"><?= esc_textarea($terms); ?></textarea>
                </td>
            </tr>
        </tbody>
    </table>
	<?php
}

/**
 * Add a notice to the admin area
 */
add_action('admin_notices', 'dbw_cost_calculator_notices');
function dbw_cost_calculator_notices()
{
	// Get any settings errors registered during the settings save
	$settings_errors = get_settings_errors( 'dbw-cost-calculator-settings-errors' );

    // If there are any errors, exit the function
	if (!empty($settings_errors)) {
		return;
	}

	// Check if we are on the cost calculator settings page and if settings were updated
	if (isset($_GET['page']) && $_GET['page'] === 'dbw-cost-calculator' &&
        isset($_GET['settings-updated']) && $_GET['settings-updated'] == true
    ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Cost Calculator settings saved.</strong></p>
        </div>
        <?php
    }
}

function dbw_cost_calc_settings_field_term_discounts() {
    $discounts = get_option('dbw-cost-calculator-term-discounts', array('1' => 0, '3' => 0, '5' => 0));
    ?>
    <label>
        1-Year Discount:
        <input type="number" step="0.01" min="0" max="1" name="dbw-cost-calculator-term-discounts[1]" value="<?php echo esc_attr($discounts['1']); ?>" />
    </label>
    <br>
    <label>
        3-Year Discount:
        <input type="number" step="0.01" min="0" max="1" name="dbw-cost-calculator-term-discounts[3]" value="<?php echo esc_attr($discounts['3']); ?>" />
    </label>
    <br>
    <label>
        5-Year Discount:
        <input type="number" step="0.01" min="0" max="1" name="dbw-cost-calculator-term-discounts[5]" value="<?php echo esc_attr($discounts['5']); ?>" />
    </label>
    <p class="description">Enter values like 0.1 for 10% discount. Max is 1 (100%).</p>
    <?php
}

function dbw_cost_calc_settings_field_term_discounts_sanitize($input) {
    $output = array();
    foreach (array('1', '3', '5') as $term) {
        $val = isset($input[$term]) ? floatval($input[$term]) : 0;
        $output[$term] = ($val >= 0 && $val <= 1) ? $val : 0;
    }
    return $output;
}
