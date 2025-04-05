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

    const USDollar = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    });

    function updateSummary() {
        let priceBeforeDiscount = 0;
        let totalInstanceQuantity = 0;
    
        $calcFieldsTypes.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val > 0) {
                const name = $(this).attr('name');
                priceBeforeDiscount += val * dbwCostCalcData.instances[name].price;
                totalInstanceQuantity += val;
            }
        });
    
        $calcFieldsAddons.each(function() {
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
        let priceAfterDiscount = priceBeforeDiscount - discountAmount;
    
        const supportLevel = $supportRadios.filter(':checked').val();
        let supportPercent = 0;
    
        if (supportLevel === 'advanced') {
            supportPercent = 15;
        } else if (supportLevel === 'premium') {
            supportPercent = 25;
        }
    
        const supportIncrease = priceAfterDiscount * (supportPercent / 100);
        priceAfterDiscount += supportIncrease;
    
        // Get selected subscription term (1, 3, or 5)
        const subscriptionTerm = parseInt($('#subscription-term').val(), 10);
    
        // Total cost across selected years
        const totalPriceForTerm = priceAfterDiscount * subscriptionTerm;
    
        const totalPricePerInstance = totalInstanceQuantity > 0 ? priceAfterDiscount / totalInstanceQuantity : 0;
        const totalPricePerMonth = priceAfterDiscount / 12;
    
        // Update labels
        $('#price-before-discount').text(USDollar.format(priceBeforeDiscount));
        $('#discount-amount').text(USDollar.format(discountAmount));
        $('#total-price-per-instance').text(USDollar.format(totalPricePerInstance));
        $('#total-price-per-month').text(USDollar.format(totalPricePerMonth));
    
        // Update label and value based on term
        if (subscriptionTerm > 1) {
            $('#total-price-label').text(`Total price for ${subscriptionTerm} years`);
            $('#price-after-discount').text(USDollar.format(totalPriceForTerm));
        } else {
            $('#total-price-label').text('Total price per year');
            $('#price-after-discount').text(USDollar.format(priceAfterDiscount));
        }
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

    $calcFieldsTypes.add($calcFieldsAddons).on('input', updateSummary);
    $('#subscription-term').on('change', updateSummary);

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

    // Footer radio block (just label text toggle)
    $('.select-footer-block input[type="radio"]').on('change', function() {
        const $label = $(this).closest('label');
        const $span = $label.find('span');

        $('.select-footer-block input[type="radio"]').each(function() {
            const $otherLabel = $(this).closest('label');
            const $otherSpan = $otherLabel.find('span');
            if (!$(this).is(':checked')) {
                $otherSpan.text('Select Block');
            }
        });

        if ($(this).is(':checked')) {
            $span.text('Selected');
        }
    });

    // Preselect first footer block (without border)
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








