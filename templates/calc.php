<?php
defined('ABSPATH') || exit;
?>
<form id="dbw-cost-calc-form" action="/" method="post" data-step="get-quote">
	<div class="dbw-cost-calc-fields">
		<div class="dbw-cost-calc-fields-col dbw-cost-calc-shadow">
            <h2 class="dbw-cost-calc-fields-col-title">Instance Types</h2>
            <div class="dbw-cost-calc-fields-types">
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>Oracle</span>
                        <div>
                            <input type="number" name="oracle" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>MS SQL Server</span>
                        <div>
                            <input type="number" name="msSQL" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>MySQL</span>
                        <div>
                            <input type="number" name="mysql" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>PostgreSQL</span>
                        <div>
                            <input type="number" name="postgres" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>MariaDB</span>
                        <div>
                            <input type="number" name="mariaDB" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <span>Sybase</span>
                        <div>
                            <input type="number" name="sybase" value="0" min="0" />
                        </div>
                    </label>
                </div>
            </div>
		</div>
		<div class="dbw-cost-calc-fields-col dbw-cost-calc-shadow">
            <h2 class="dbw-cost-calc-fields-col-title">Extra Cost Add-ons</h2>
            <div class="dbw-cost-calc-fields-extra">
                <div class="dbw-cost-calc-field">
                    <label>
                        <div>
                            <div class="label-title">SQL Performance package</div>
                            <div class="label-desc">($120) for Oracle, MSSQL, MySQL, PostgreSQL, MariaDB</div>
                        </div>
                        <div>
                            <input type="number" name="addon0" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <div>
                            <div class="label-title">Maintenance package</div>
                            <div class="label-desc">($120) for MSSQL</div>
                        </div>
                        <div>
                            <input type="number" name="addon1" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <div>
                            <div class="label-title">Security and Compliance package </div>
                            <div class="label-desc">($120) for MSSQL</div>
                        </div>
                        <div>
                            <input type="number" name="addon2" value="0" min="0" />
                        </div>
                    </label>
                </div>
                <div class="dbw-cost-calc-field">
                    <label>
                        <div>
                            <div class="label-title">Cloud Router</div>
                            <div class="label-desc">($5000) for ControlCenter</div>
                        </div>
                        <div>
                            <input type="number" name="addon3" value="0" min="0" />
                        </div>
                    </label>
                </div>
            </div>
		</div>
	</div>
	<div class="dbw-cost-calc-summary">
        <h3 class="dbw-cost-calc-title">Summary</h3>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Price before discount</span>
            <span class="summary-item-val" id="price-before-discount">$0</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Discount</span>
            <span class="summary-item-val" id="discount">$0</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Yearly price per instance</span>
            <span class="summary-item-val" id="yearly-price-per-instance">$0</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow">
            <span>Total price per month</span>
            <span class="summary-item-val" id="total-price-per-month">$0</span>
        </div>
        <div class="dbw-cost-calc-summary-item dbw-cost-calc-shadow item-total">
            <span>Total price per year</span>
            <span class="summary-item-val" id="total-price-per-year">$0</span>
        </div>
	</div>
    <div class="dbw-cost-calc-get-quote">
        <div class="dbw-cost-calc-get-quote-btn">
            <button id="get-quote-btn" class="dbw-cost-calc-btn">Get a Quote</button>
        </div>
        <div class="dbw-cost-calc-get-quote-fields">
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">
                        Email<sup>*</sup>
                    </div>
                    <div class="quote-input">
                        <input type="email" name="email" placeholder="Email" required />
                    </div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">
                        Name
                    </div>
                    <div class="quote-input">
                        <input type="text" name="name" placeholder="Name" />
                    </div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-field">
                <label>
                    <div class="quote-label">
                        Company Name
                    </div>
                    <div class="quote-input">
                        <input type="text" name="company" placeholder="Company" />
                    </div>
                </label>
            </div>
            <div class="dbw-cost-calc-get-quote-actions">
                <button class="dbw-cost-calc-btn">Submit</button>
            </div>
        </div>
        <div class="dbw-cost-calc-thank-you">
            <a id="thank-you-close" href="#" class="dbw-cost-calc-thank-you-close">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.4 14L0 12.6L5.6 7L0 1.4L1.4 0L7 5.6L12.6 0L14 1.4L8.4 7L14 12.6L12.6 14L7 8.4L1.4 14Z" fill="#555555"/>
                </svg>
            </a>
            <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 9L4.233 11.425C4.43936 11.5797 4.69752 11.6487 4.95356 11.6176C5.2096 11.5865 5.44372 11.4577 5.607 11.258L14 1" stroke="#394494" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <h4 class="thank-you-title">Thank you!</h4>
            <p class="thank-you-content">The form successfully submitted</p>
        </div>
    </div>
</form>