<?php
defined('ABSPATH') || exit;
?>
<form id="dbw-cost-calc-form" class="dbw-cost-calc-form" action="/" method="post" data-step="get-quote">
	<div class="dbw-cost-calc-fields">
		<div class="dbw-cost-calc-fields-col dbw-cost-calc-shadow">
            <h2 class="dbw-cost-calc-fields-col-title">Instance Types</h2>
            <div class="dbw-cost-calc-fields-types">
                <?php
                    $types = get_option('dbw-cost-calculator-instance-types');
                    if (is_array($types)) {
                        for ($i = 0; $i < count($types['name']); $i++) { ?>
                            <div class="dbw-cost-calc-field">
                                <label>
                                    <div class="dbw-cost-call-name-wrap">
                                        <?= $types['name'][$i]; ?>
                                        <?php
                                            if (!empty($types['link_label'][$i]) && !empty($types['link_url'][$i])) {
                                                $class = 'dbw-cost-call-res-link';
                                                $attr = '';
                                                if (!str_starts_with($types['link_url'][$i], '/') && !str_starts_with($types['link_url'][$i], site_url())) {
                                                    $class .= ' external-link';
                                                    $attr .= ' target="_blank" rel="noopener noreferrer"';
                                                }
                                                printf(
                                                    '<a class="%s" href="%s"%s>%s</a>',
                                                    $class,
                                                    esc_url($types['link_url'][$i]),
                                                    $attr,
                                                    esc_html($types['link_label'][$i])
                                                );
                                            }
                                        ?>
                                    </div>
                                    <div>
                                        <input type="number" name="<?= sanitize_key($types['name'][$i]); ?>" value="0" min="0" max="10000" />
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
                    if (is_array($addons)) {
                        for ($i = 0; $i < count($addons['name']); $i++) { ?>
                            <div class="dbw-cost-calc-field">
                                <label>
                                    <div class="dbw-cost-call-name-wrap">
                                        <div class="label-title"><?= $addons['name'][$i]; ?></div>
                                        <div class="label-desc">($<?= $addons['price'][$i]; ?>) for <?= $addons['platforms'][$i]; ?></div>
	                                    <?php
                                            if (!empty($addons['link_label'][$i]) && !empty($addons['link_url'][$i])) {
	                                            $class = 'dbw-cost-call-res-link';
                                                $attr = '';
	                                            if (!str_starts_with($addons['link_url'][$i], '/') && !str_starts_with($addons['link_url'][$i], site_url())) {
		                                            $class .= ' external-link';
                                                    $attr .= ' target="_blank" rel="noopener noreferrer"';
	                                            }
                                                printf(
                                                    '<a class="%s" href="%s"%s>%s</a>',
	                                                $class,
                                                    esc_url($addons['link_url'][$i]),
                                                    $attr,
                                                    esc_html($addons['link_label'][$i])
                                                );
                                            }
	                                    ?>
                                    </div>
                                    <div>
                                        <input type="number" name="<?= sanitize_key($addons['name'][$i]); ?>" value="0" min="0" max="10000" />
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
                            <div class="support-price">
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
            <span class="summary-item-val" id="price-before-discount">$0.00</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Volume discount</span>
            <span class="summary-item-val" id="discount-amount">$0.00</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Yearly price per instance</span>
            <span class="summary-item-val" id="total-price-per-instance">$0.00</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Total price per month</span>
            <span class="summary-item-val" id="total-price-per-month">$0.00</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow item-total">
            <span id="total-price-label">Total price per year</span>
            <span class="summary-item-val" id="price-after-discount">$0.00</span>
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