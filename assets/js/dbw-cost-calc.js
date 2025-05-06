jQuery(document).ready(function($) {
    const $form = $('#dbw-cost-calc-form');
    const $calcFieldsTypes = $form.find('.dbw-cost-calc-fields-types input[type="number"]');
    const $calcFieldsAddons = $form.find('.dbw-cost-calc-fields-extra input[type="number"]');
    const $quoteBtn = $('#get-quote-btn');
    const $messages = $('#form-messages');
    const $thankYouCloseBtn = $('#thank-you-close');
    const $termsToggle = $('.dbw-cost-calc-terms-title');
    const $supportRadios = $form.find('input[name="support_level"]');
    const $supportBlocks = $('#support-levels-container label');
    const $currencySelector = $('#currency'); // Currency selector element
    const geoDefaultCurrency = $('#geo-default-currency').data('default-currency');
    if (geoDefaultCurrency && $currencySelector.length) {
        $currencySelector.val(geoDefaultCurrency).trigger('change');
    }

    let conversionRates = {};
    const supportPercentages = {};

    $('.support-price').each(function () {
        const key = $(this).data('support-key');
        const percent = parseFloat($(this).data('percent'));
        if (key && !isNaN(percent)) {
            supportPercentages[key] = percent;
        }
    });
    const $exchangeRates = $('#dbw-exchange-rates');

    try {
        const rawRates = JSON.parse($exchangeRates.attr('data-rates'));
        for (const [currency, rate] of Object.entries(rawRates)) {
            conversionRates[currency] = parseFloat(rate);
        }
    } catch (e) {
        console.error('Invalid exchange rate data:', e);
        conversionRates = { USD: 1 };
    }
    const termDiscounts = {};
    const $termDiscounts = $('#dbw-term-discounts');

    try {
        const rawTermDiscounts = JSON.parse($termDiscounts.attr('data-discounts'));
        for (const [term, discount] of Object.entries(rawTermDiscounts)) {
            termDiscounts[term] = parseFloat(discount);
        }
    } catch (e) {
        console.error('Invalid term discount data:', e);
    }

    function updateSummary() {
        let priceBeforeDiscount = 0;
        let totalInstanceQuantity = 0;
        const selectedCurrency = $currencySelector.val();
    
        const currencySymbols = { USD: '$', EUR: '€', NOK: 'kr' };
        const getCurrencySymbol = (code) => currencySymbols[code] || code;
        const convertToCurrency = (amount) => (amount * conversionRates[selectedCurrency]).toFixed(2);
    
        $calcFieldsTypes.each(function () {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                const name = $(this).attr('name');
                priceBeforeDiscount += val * dbwCostCalcData.instances[name].price;
                totalInstanceQuantity += val;
            }
        });
    
        $calcFieldsAddons.each(function () {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                const name = $(this).attr('name');
                priceBeforeDiscount += val * dbwCostCalcData.addons[name].price;
            }
        });
    
        let discount = 0;
        for (const rate of dbwCostCalcData.discountRates) {
            if (totalInstanceQuantity >= rate.minQty) {
                discount = rate.discount;
            }
        }
    
        const discountAmount = priceBeforeDiscount * discount;
        const priceAfterDiscount = priceBeforeDiscount - discountAmount;
    
        const supportLevel = $supportRadios.filter(':checked').val();
        const supportPercent = supportPercentages[supportLevel] || 0;
    
        const supportIncrease = priceAfterDiscount * (supportPercent / 100);
        const finalPriceAfterSupport = priceAfterDiscount + supportIncrease;
    
        const subscriptionTerm = parseInt($('#subscription-term').val(), 10);
        const termDiscount = termDiscounts[subscriptionTerm] || 0;
        const termDiscountAmount = finalPriceAfterSupport * termDiscount;
        const finalPriceAfterTermDiscount = finalPriceAfterSupport - termDiscountAmount;
    
        const totalPriceForTerm = finalPriceAfterTermDiscount * subscriptionTerm;
        const totalPricePerInstance = totalInstanceQuantity > 0 ? finalPriceAfterTermDiscount / totalInstanceQuantity : 0;
        const totalPricePerMonth = finalPriceAfterTermDiscount / 12;
    
        // 🟡 Use CURRENCY CODE in the summary block
        $('#price-before-discount').text(`${selectedCurrency} ${convertToCurrency(priceBeforeDiscount)}`);
        $('#discount-amount').text(`${selectedCurrency} ${convertToCurrency(discountAmount)}`);
        $('#support-plan-increase').text(`${selectedCurrency} ${convertToCurrency(supportIncrease)}`);
        $('#term-discount').text(`${selectedCurrency} ${convertToCurrency(termDiscountAmount)}`);
        $('#total-price-per-instance').text(`${selectedCurrency} ${convertToCurrency(totalPricePerInstance)}`);
        $('#total-price-per-month').text(`${selectedCurrency} ${convertToCurrency(totalPricePerMonth)}`);
    
        if (subscriptionTerm > 1) {
            $('#total-price-label').text(`Total price for ${subscriptionTerm} years`);
        } else {
            $('#total-price-label').text('Total price per year');
        }
    
        $('#price-after-discount').text(`${selectedCurrency} ${convertToCurrency(totalPriceForTerm)}`);
    
        // Use CURRENCY SYMBOLS for package/add-on label display
        $('.label-desc').each(function () {
            const $desc = $(this);
            const usdPrice = parseFloat($desc.data('usd-price'));
            if (isNaN(usdPrice)) return;
    
            const rate = conversionRates[selectedCurrency] ?? 1;
            const converted = (usdPrice * rate).toFixed(2);
    
            const currentText = $desc.text();
            const platformMatch = currentText.match(/for\s(.+)$/i);
            const platformText = platformMatch ? platformMatch[1] : '';
    
            $desc.text(`(${getCurrencySymbol(selectedCurrency)}${converted}) for ${platformText}`);
        });
    }

    // Expand/collapse toggle
    $supportBlocks.each(function() {
        const $this = $(this);
        const $header = $this.find('.support-header');
        const $features = $this.find('.support-features');
        const $toggleIcon = $header.find('.toggle-icon');

        $features.hide();
        $toggleIcon.removeClass('rotate');
        $this.removeClass('open');

        $header.on('click', function() {
            const isAnyOpen = $supportBlocks.filter('.open').length > 0;

            if (isAnyOpen) {
                $supportBlocks.removeClass('open').find('.support-features').slideUp();
                $supportBlocks.find('.toggle-icon').removeClass('rotate');
            } else {
                $supportBlocks.addClass('open').find('.support-features').slideDown();
                $supportBlocks.find('.toggle-icon').addClass('rotate');
            }
        });
    });

    // Highlight only main support blocks (not footer blocks)
    $supportRadios.on('change', function() {
        const selectedValue = $(this).val();

        $supportBlocks.each(function() {
            const $block = $(this);
            const $radio = $block.find('input[type="radio"]');

            if ($radio.val() === selectedValue) {
                $block.addClass('selected');
            } else {
                $block.removeClass('selected');
            }
        });

        updateSummary();
    });

    // Make entire support block clickable/selectable
    $supportBlocks.on('click', function(e) {
        if (!$(e.target).is('input[type="radio"]')) {
            const $radio = $(this).find('input[type="radio"]');
            $radio.prop('checked', true).trigger('change');
        }
    });
    
    // Initial support highlight
    const $checkedSupport = $supportRadios.filter(':checked');
    if ($checkedSupport.length) {
        $checkedSupport.closest('label').addClass('selected');
    }

    // Update prices when user interacts with any calculation fields or subscription term
    $calcFieldsTypes.add($calcFieldsAddons).on('input', updateSummary);
    $('#subscription-term').on('change', updateSummary);

    // Handle currency selection change
    $currencySelector.on('change', function() {
        updateSummary();
    });

    $quoteBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'quote-submit');
        return false;
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        if ($form.hasClass('processing')) return false;
        $form.addClass('processing');

        $messages.html('&nbsp;');
        const $inputs = $form.find('input, button');
        $inputs.attr('disabled', 'disabled');

        const instances = [];
        $calcFieldsTypes.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                instances.push({
                    name: dbwCostCalcData.instances[$(this).attr('name')].name,
                    qty: val
                });
            }
        });

        if (instances.length === 0) {
            $messages.html('<span class="error">Add at least one instance to get a quote.</span>');
            $form.removeClass('processing');
            $inputs.removeAttr('disabled');
            return false;
        }

        const addons = [];
        $calcFieldsAddons.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                addons.push({
                    name: dbwCostCalcData.addons[$(this).attr('name')].name,
                    qty: val
                });
            }
        });

        $.post(dbwCostCalcData.ajax.url, {
            action: 'dbwCostCalcGetQuote',
            nonce: dbwCostCalcData.ajax.nonce,
            instances: instances,
            addons: addons,
            supportLevel: $supportRadios.filter(':checked').val(),
            priceBeforeDiscount: $('#price-before-discount').text(),
            discountAmount: $('#discount-amount').text(),
            totalPricePerInstance: $('#total-price-per-instance').text(),
            totalPricePerMonth: $('#total-price-per-month').text(),
            priceAfterDiscount: $('#price-after-discount').text(),
            email: $form.find('input[name="email"]').val(),
            name: $form.find('input[name="name"]').val(),
            company: $form.find('input[name="company"]').val(),
            comments: $form.find('textarea[name="comments"]').val()
        }, function(response) {
            if (response.success) {
                $form.attr('data-step', 'thank-you');
            } else {
                $messages.html('<span class="error">' + response.data.message + '</span>');
                console.error(response.data.error);
            }
        }).fail(function(xhr) {
            $messages.html('<span class="error">An unexpected error occurred. Please reload the page and try again.</span>');
            console.error('XHR', xhr);
        }).always(function() {
            $form.removeClass('processing');
            $inputs.removeAttr('disabled');
        });

        return false;
    });

    $thankYouCloseBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'get-quote');
        return false;
    });

    $termsToggle.on('click', function() {
        const $_this = $(this);
        const $svg = $_this.find('svg');
        const $content = $_this.parent().find('.dbw-cost-calc-terms-content');

        if ($_this.hasClass('active')) {
            $svg.removeClass('active');
            $content.slideUp(function() {
                $_this.removeClass('active');
            });
        } else {
            $svg.addClass('active');
            $_this.addClass('active');
            $content.slideDown();
        }
    });

    // Footer radio block
    $('.select-footer-block input[type="radio"]').on('change', function() {
        const $label = $(this).closest('label');
        const $span = $label.find('span');

        $('.select-footer-block input[type="radio"]').each(function() {
            const $otherLabel = $(this).closest('label');
            const $otherSpan = $otherLabel.find('span');
            if (!$(this).is(':checked')) {
                $otherSpan.text('Choose');
            }
        });

        if ($(this).is(':checked')) {
            $span.text('Selected Support')
        }
    });

    // Preselect first footer block 
    const $firstRadio = $('.select-footer-block input[type="radio"]').first();
    const $firstLabel = $firstRadio.closest('label');
    const $firstSpan = $firstLabel.find('span');
    $firstRadio.prop('checked', true);
    $firstSpan.text('Selected');

    // Reset toggle state
    $supportBlocks.each(function() {
        const $block = $(this);
        $block.removeClass('open');
        $block.find('.support-features').hide();
        $block.find('.toggle-icon').removeClass('rotate');
    });

    updateSummary();
}); 








