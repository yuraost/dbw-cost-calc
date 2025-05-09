<?php
defined('ABSPATH') || exit;
?>
<form id="dbw-cost-calc-form" class="dbw-cost-calc-form" action="/" method="post" data-step="get-quote">
    <?php
    $currencies = get_option('dbw-cost-calculator-currencies', []);
    $defaults = ['USD' => 1, 'EUR' => 0.9, 'NOK' => 10];
    $currencies = wp_parse_args($currencies, $defaults);
    ?>
    <div id="dbw-exchange-rates" data-rates='<?php echo json_encode($currencies); ?>'></div>
    <?php
    // Get country from GeoIP
    $geo = geoip_detect2_get_info_from_current_ip();
    $countryCode = $geo->country->isoCode ?? 'US'; // Fallback to US if detection fails

    // Define Eurozone countries
    $eurCountries = ['AT','BE','CY','EE','FI','FR','DE','GR','IE','IT','LV','LT','LU','MT','NL','PT','SK','SI','ES'];

    if ($countryCode === 'NO') {
        $defaultCurrency = 'NOK';
    } elseif (in_array($countryCode, $eurCountries)) {
        $defaultCurrency = 'EUR';
    } else {
        $defaultCurrency = 'USD';
    }
    ?>
    <div id="geo-default-currency" data-default-currency="<?= esc_attr($defaultCurrency); ?>"></div>
    <div class="currency-selector">
        <label for="currency">Select Currency:</label>
        <select id="currency" name="currency">
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
            <option value="NOK">NOK</option>
        </select>
    </div>
    <?php
    $term_discounts = get_option('dbw-cost-calculator-term-discounts', [
        '1' => 0,
        '3' => 0.10,
        '5' => 0.15
    ]);
    ?>
    <div id="dbw-term-discounts" data-discounts='<?php echo json_encode($term_discounts); ?>'></div>
	<div class="dbw-cost-calc-fields">
		<div class="dbw-cost-calc-fields-col dbw-cost-calc-shadow">
            <h2 class="dbw-cost-calc-fields-col-title">Instance Types</h2>
            <div class="dbw-cost-calc-fields-types">
                <?php
                    $types = get_option('dbw-cost-calculator-instance-types');
                    if (is_array($types) && isset($types['name']) && is_array($types['name'])) {
                        $count = count($types['name']);
                        for ($i = 0; $i < $count; $i++) {
                            $name = $types['name'][$i] ?? '';
                            $usd_price = $types['usd_price'][$i] ?? '';
                            $eur_price = $types['eur_price'][$i] ?? '';
                            $nok_price = $types['nok_price'][$i] ?? '';
                            $link_label = $types['link_label'][$i] ?? '';
                            $link_url = $types['link_url'][$i] ?? '';
                            ?>
                            <div class="dbw-cost-calc-field">
                                <label>
                                    <div class="dbw-cost-call-name-wrap">
                                        <div class="label-title"><?= esc_html($name); ?></div>
                                        <div class="label-desc"
                                            data-usd-price="<?= esc_attr($usd_price); ?>"
                                            data-eur-price="<?= esc_attr($eur_price); ?>"
                                            data-nok-price="<?= esc_attr($nok_price); ?>">
                                        </div>
                                    </div>
                                    <div>
                                        <input type="number" name="<?= sanitize_key($name); ?>" value="0" min="0" max="10000" />
                                    </div>
                                </label>
                            </div>
                        <?php }
                    }
                ?>
            </div>
		</div>
		<div class="dbw-cost-calc-fields-col dbw-cost-calc-shadow">
            <h2 class="dbw-cost-calc-fields-col-title">Premium packages and add-ons</h2>
            <div class="dbw-cost-calc-fields-extra">
                <?php
                    $addons = get_option('dbw-cost-calculator-addons');
                    if (is_array($addons) && isset($addons['name']) && is_array($addons['name'])) {
                        $count = count($addons['name']);
                        for ($i = 0; $i < $count; $i++) {
                            $name = $addons['name'][$i] ?? '';
                            $usd_price = $addons['usd_price'][$i] ?? '';
                            $eur_price = $addons['eur_price'][$i] ?? '';
                            $nok_price = $addons['nok_price'][$i] ?? '';
                            $platforms = $addons['platforms'][$i] ?? '';
                            $link_label = $addons['link_label'][$i] ?? '';
                            $link_url = $addons['link_url'][$i] ?? '';
                            ?>
                            <div class="dbw-cost-calc-field">
                                <label>
                                    <div class="dbw-cost-call-name-wrap">
                                        <div class="label-title"><?= esc_html($name); ?></div>
                                        <div class="label-desc"
                                            data-usd-price="<?= esc_attr($usd_price); ?>"
                                            data-eur-price="<?= esc_attr($eur_price); ?>"
                                            data-nok-price="<?= esc_attr($nok_price); ?>"
                                            data-platform="<?= esc_attr($platforms); ?>">
                                            ($<?= esc_html($usd_price); ?>)
                                            <?php if (!empty($platforms)): ?>
                                                <span class="platform-inline">for <?= esc_html($platforms); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                            if (!empty($link_label) && !empty($link_url)) {
                                                $class = 'dbw-cost-call-res-link';
                                                $attr = '';
                                                if (!str_starts_with($link_url, '/') && !str_starts_with($link_url, site_url())) {
                                                    $class .= ' external-link';
                                                    $attr .= ' target="_blank" rel="noopener noreferrer"';
                                                }
                                                printf(
                                                    '<a class="%s" href="%s"%s>%s</a>',
                                                    esc_attr($class),
                                                    esc_url($link_url),
                                                    $attr,
                                                    esc_html($link_label)
                                                );
                                            }
                                        ?>
                                    </div>
                                    <div>
                                        <input type="number" name="<?= sanitize_key($name); ?>" value="0" min="0" max="10000" />
                                    </div>
                                </label>                                
                            </div>
                        <?php }
                    }
                ?>
            </div>
		</div>
	</div>

    <?php
    // Fetch support levels from the backend
    $support_levels = get_option('dbw-cost-calculator-support-levels', [
        'basic' => ['title' => 'Basic Support', 'percent' => 0, 'learn_more' => '', 'features' => []],
        'advanced' => ['title' => 'Advanced Support', 'percent' => 10, 'learn_more' => '', 'features' => []],
        'premium' => ['title' => 'Premium Support', 'percent' => 25, 'learn_more' => '', 'features' => []],
    ]);

    // Render feature line
    function render_feature($feature, $default = '') {
        // Check if the feature is an array with 'included' and 'text' as keys
        if (is_array($feature) && isset($feature['included'], $feature['text'])) {
            // Render icon based on the 'included' value
            if ($feature['included']) {
                return '<span class="icon-check">✔</span> ' . esc_html($feature['text']);
            } else {
                return '<span class="icon-x">✘</span> ' . esc_html($feature['text']);
            }
        }
    
        // Default behavior if the feature is not an array or missing required keys
        return '<span class="icon-check">✔</span> ' . esc_html($default);
    }
    ?>

    <div class="dbw-support-levels">
        <h2 class="dbw-cost-calc-fields-col-title">Support Level</h2>
        <div id="support-levels-container">
            <?php foreach ($support_levels as $key => $level): ?>
                <label class="support-level-block dbw-cost-calc-shadow <?= $key === 'basic' ? 'open' : ''; ?>">
                    <input type="radio" name="support_level" value="<?= esc_attr($key); ?>" <?= $key === 'basic' ? 'checked' : ''; ?> hidden />
                    <div class="support-header">
                        <?= esc_html($level['title']); ?>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="support-body" style="<?= $key === 'basic' ? 'display:block;' : 'display:none;'; ?>">
                        <ul class="support-features">
                            <?php foreach ($level['features'] as $feature): ?>
                                <li><?= render_feature($feature); ?></li>  <!-- Display feature text with icons -->
                            <?php endforeach; ?>
                        </ul>
                        <div class="support-footer">
                            <div class="support-price" data-support-key="<?= esc_attr($key); ?>" data-percent="<?= esc_attr($level['percent']); ?>">
                                <?= $key === 'basic' ? 'No additional cost' : '+ ' . esc_html($level['percent']) . '%'; ?>
                            </div>
                            <?php if (!empty($level['learn_more'])): ?>
                                <div class="support-learn">
                                    <a href="<?= esc_url($level['learn_more']); ?>" class="learn-more">Learn more</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="dbw-cost-calc-footer">
                        <label class="select-footer-block">
                            <input type="radio" name="footer-select" value="1" id="footer-select-1" />
                            <span>Select Block</span>
                        </label>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="dbw-cost-calc-subscription-term">
        <div class="subscription-inner">
            <label for="subscription-term">Subscription term</label>
            <select id="subscription-term" name="subscription_term" class="subscription_term_sel">
                <option value="1" selected>1 Year</option>
                <option value="3">3 Years</option>
                <option value="5">5 Years</option>
            </select>
        </div>
    </div>

    <div class="dbw-cost-calc-summary">
        <h3 class="dbw-cost-calc-title">Summary</h3>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Price before discount</span>
            <span class="summary-item-val" id="price-before-discount"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Volume discount</span>
            <span class="summary-item-val" id="discount-amount"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Term discount</span>
            <span class="summary-item-val" id="term-discount"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Support plan increase</span>
            <span class="summary-item-val" id="support-plan-increase"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Yearly price per instance</span>
            <span class="summary-item-val" id="total-price-per-instance"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Total price per month</span>
            <span class="summary-item-val" id="total-price-per-month"></span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow item-total">
            <span id="total-price-label">Total price per year</span>
            <span class="summary-item-val" id="price-after-discount"></span>
        </div>
    </div>

    <div class="dbw-cost-calc-get-quote">
        <div class="dbw-cost-calc-get-quote-btn">
            <button id="get-quote-btn" class="dbw-cost-calc-btn">Get a Quote</button>
        </div>
        <div class="dbw-cost-calc-get-quote-fields">
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">Email<sup>*</sup></div>
                    <div class="quote-input"><input type="email" name="email" placeholder="Email" required /></div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">Name</div>
                    <div class="quote-input"><input type="text" name="name" placeholder="Name" /></div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">Company Name</div>
                    <div class="quote-input"><input type="text" name="company" placeholder="Company" /></div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">Comments</div>
                    <div class="quote-input"><textarea name="comments"></textarea></div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-actions">
                <button class="dbw-cost-calc-btn">Request a quote</button>
            </div>
        </div>
        <div class="dbw-cost-calc-thank-you">
            <a id="thank-you-close" href="#" class="dbw-cost-calc-thank-you-close">
                <svg width="14" height="14"><path d="M1.4 14L0 12.6L5.6 7L0 1.4L1.4 0L7 5.6L12.6 0L14 1.4L8.4 7L14 12.6L12.6 14L7 8.4L1.4 14Z" fill="#555"/></svg>
            </a>
            <svg width="15" height="13"><path d="M1 9L4.233 11.425C4.43936 11.5797 4.69752 11.6487 4.95356 11.6176C5.2096 11.5865 5.44372 11.4577 5.607 11.258L14 1" stroke="#394494" stroke-width="2" stroke-linecap="round"/></svg>
            <h4 class="thank-you-title">Thank you!</h4>
            <p class="thank-you-content">We will send you a quote shortly</p>
        </div>
        <div class="dbw-cost-calc-messages" id="form-messages">&nbsp;</div>
    </div>
</form>