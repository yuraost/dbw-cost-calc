<?php
defined('ABSPATH') || exit;
$terms = get_option('dbw-cost-calculator-terms');
if (empty($terms)) return;
?>
<div class="dbw-cost-calc-terms">
	<div class="dbw-cost-calc-terms-title dbw-cost-calc-shadow">
        <span>Learn More About Usage and Subscription Terms</span>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.39893 6.39502L9.53872 12.6849L16.6222 6.33173" stroke="#394494" stroke-width="2"/>
        </svg>
    </div>
	<div class="dbw-cost-calc-terms-content">
        <p><?= implode('</p><p>', explode(PHP_EOL, $terms)) ?></p>
	</div>
</div>