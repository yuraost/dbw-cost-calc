jQuery(document).ready(function($) {
    const $form = $('#dbw-cost-calc-form');
    const $calcFieldsTypes = $form.find('.dbw-cost-calc-fields-types input[type="number"]');
    const $calcFieldsAddons = $form.find('.dbw-cost-calc-fields-extra input[type="number"]');
    const $messages = $('#form-messages');
    const $thankYouCloseBtn = $('#thank-you-close');
    const $termsToggle = $('.dbw-cost-calc-terms-title');
    const $supportRadios = $form.find('input[name="support_level"]');
    const $supportBlocks = $('#support-levels-container label');
    const $currencySelector = $('#currency');
    const geoDefaultCurrency = $('#geo-default-currency').data('default-currency');

    if (geoDefaultCurrency && $currencySelector.length) {
        $currencySelector.val(geoDefaultCurrency).trigger('change');
    }

    const supportPercentages = {};
    $('.support-price').each(function () {
        const key = $(this).data('support-key');
        const percent = parseFloat($(this).data('percent'));
        if (key && !isNaN(percent)) {
            supportPercentages[key] = percent;
        }
    });

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

        $calcFieldsTypes.each(function () {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                const $labelDesc = $(this).closest('.dbw-cost-calc-field').find('.label-desc');
                const pricePerUnit = parseFloat($labelDesc.data(selectedCurrency.toLowerCase() + '-price'));
                if (!isNaN(pricePerUnit)) {
                    priceBeforeDiscount += val * pricePerUnit;
                    totalInstanceQuantity += val;
                }
            }
        });

        $calcFieldsAddons.each(function () {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                const $labelDesc = $(this).closest('.dbw-cost-calc-field').find('.label-desc');
                const pricePerUnit = parseFloat($labelDesc.data(selectedCurrency.toLowerCase() + '-price'));
                if (!isNaN(pricePerUnit)) {
                    priceBeforeDiscount += val * pricePerUnit;
                }
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

        $('#price-before-discount').text(`${selectedCurrency} ${priceBeforeDiscount.toFixed(2)}`);
        $('#discount-amount').text(`${selectedCurrency} ${discountAmount.toFixed(2)}`);
        $('#support-plan-increase').text(`${selectedCurrency} ${supportIncrease.toFixed(2)}`);
        $('#term-discount').text(`${selectedCurrency} ${termDiscountAmount.toFixed(2)}`);
        $('#total-price-per-instance').text(`${selectedCurrency} ${totalPricePerInstance.toFixed(2)}`);
        $('#total-price-per-month').text(`${selectedCurrency} ${totalPricePerMonth.toFixed(2)}`);

        $('#total-price-label').text(subscriptionTerm > 1 ? `Total price for ${subscriptionTerm} years` : 'Total price per year');
        $('#price-after-discount').text(`${selectedCurrency} ${totalPriceForTerm.toFixed(2)}`);

        $('.label-desc').each(function () {
            const $desc = $(this);
            const price = parseFloat($desc.data(selectedCurrency.toLowerCase() + '-price'));
            if (!isNaN(price)) {
                const platformText = $desc.data('platform');
                const formattedPrice = price % 1 === 0 ? price.toString() : price.toFixed(2);
                const currencySymbol = getCurrencySymbol(selectedCurrency);
                const priceDisplay = `(${currencySymbol}${formattedPrice})`;
                $desc.html(platformText ? `${priceDisplay} <span class="platform-inline">for ${platformText}</span>` : priceDisplay);
            }
        });
    }

    $supportBlocks.each(function() {
        const $this = $(this);
        const $header = $this.find('.support-header');
        const $features = $this.find('.support-features');
        const $toggleIcon = $header.find('.toggle-icon');

        $features.show();
        $toggleIcon.addClass('rotate');
        $this.addClass('open');

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

    $supportRadios.on('change', function() {
        const selectedValue = $(this).val();
        $supportBlocks.each(function() {
            const $block = $(this);
            const $radio = $block.find('input[type="radio"]');
            const $labelSpan = $block.find('.select-footer-block span');

            if ($radio.val() === selectedValue) {
                $block.addClass('selected');
                $labelSpan.text('Selected support');
            } else {
                $block.removeClass('selected');
                $labelSpan.text('Choose');
            }
        });
        updateSummary();
    });

    $supportRadios.filter(':checked').trigger('change');

    $supportBlocks.on('click', function(e) {
        if (!$(e.target).is('input[type="radio"]')) {
            const $radio = $(this).find('input[type="radio"]');
            $radio.prop('checked', true).trigger('change');
        }
    });

    const $checkedSupport = $supportRadios.filter(':checked');
    if ($checkedSupport.length) {
        $checkedSupport.closest('label').addClass('selected');
    }

    $calcFieldsTypes.add($calcFieldsAddons).on('input', updateSummary);
    $('#subscription-term').on('change', updateSummary);
    $currencySelector.on('change', updateSummary);

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

        const currency = $currencySelector.val();
        const supportLevel = $supportRadios.filter(':checked').val();
        const subscriptionTerm = parseInt($('#subscription-term').val(), 10);
        const totalPricePerYear = parseFloat($('#price-after-discount').text().replace(/[^\d.]/g, '')) || 0;

        $.post(dbwCostCalcData.ajax.url, {
            action: 'dbwCostCalcGetQuote',
            nonce: dbwCostCalcData.ajax.nonce,
            instances: instances,
            addons: addons,
            currency: currency,
            supportLevel: supportLevel,
            subscriptionTerm: subscriptionTerm,
            priceBeforeDiscount: parseFloat($('#price-before-discount').text().replace(/[^\d.]/g, '')) || 0,
            volumeDiscount: parseFloat($('#discount-amount').text().replace(/[^\d.]/g, '')) || 0,
            supportPlanIncrease: parseFloat($('#support-plan-increase').text().replace(/[^\d.]/g, '')) || 0,
            termDiscount: parseFloat($('#term-discount').text().replace(/[^\d.]/g, '')) || 0,
            totalPricePerInstance: parseFloat($('#total-price-per-instance').text().replace(/[^\d.]/g, '')) || 0,
            totalPricePerMonth: parseFloat($('#total-price-per-month').text().replace(/[^\d.]/g, '')) || 0,
            totalPricePerYear: totalPricePerYear,
            totalPrice3Years: subscriptionTerm >= 3 ? (totalPricePerYear * 3).toFixed(2) : null,
            totalPrice5Years: subscriptionTerm >= 5 ? (totalPricePerYear * 5).toFixed(2) : null,
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

    updateSummary();
});
