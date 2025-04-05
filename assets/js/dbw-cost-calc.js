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

    // Function to update the summary section
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
    
        // Calculate volume discount
        let discount = 0;
        for (const rate of dbwCostCalcData.discountRates) {
            if (totalInstanceQuantity >= rate.minQty) {
                discount = rate.discount;
            }
        }
    
        const discountAmount = priceBeforeDiscount * discount;
        let priceAfterDiscount = priceBeforeDiscount - discountAmount;
    
        // Apply support level percentage
        const supportLevel = $supportRadios.filter(':checked').val();
        let supportPercent = 0;
    
        if (supportLevel === 'advanced') {
            supportPercent = 15;  // Advanced increases by 15%
        } else if (supportLevel === 'premium') {
            supportPercent = 25;  // Premium increases by 25%
        }
    
        // Calculate the support increase
        const supportIncrease = priceAfterDiscount * (supportPercent / 100);
        priceAfterDiscount += supportIncrease;
    
        const totalPricePerInstance = totalInstanceQuantity > 0 ? priceAfterDiscount / totalInstanceQuantity : 0;
        const totalPricePerMonth = priceAfterDiscount / 12;
    
        // Update the summary with calculated values
        $('#price-before-discount').text(USDollar.format(priceBeforeDiscount));
        $('#discount-amount').text(USDollar.format(discountAmount));
        $('#total-price-per-instance').text(USDollar.format(totalPricePerInstance));
        $('#total-price-per-month').text(USDollar.format(totalPricePerMonth));
        $('#price-after-discount').text(USDollar.format(priceAfterDiscount));
    }

    // Function to handle expand/collapse of all support blocks
    $supportBlocks.each(function() {
        const $this = $(this);
        const $header = $this.find('.support-header');
        const $features = $this.find('.support-features');
        const $toggleIcon = $header.find('.toggle-icon');
    
        // Initially hide the features for all blocks
        $features.hide();
        $toggleIcon.removeClass('rotate');
        $this.removeClass('open');
    
        // On click, toggle all blocks (open or close)
        $header.on('click', function() {
            const isAnyBlockOpen = $supportBlocks.filter('.open').length > 0;

            if (isAnyBlockOpen) {
                // If any block is open, close all blocks
                $supportBlocks.removeClass('open').find('.support-features').slideUp();
                $supportBlocks.find('.toggle-icon').removeClass('rotate');
            } else {
                // If no block is open, open all blocks
                $supportBlocks.addClass('open').find('.support-features').slideDown();
                $supportBlocks.find('.toggle-icon').addClass('rotate');
            }
        });
    });

    // Update summary when fields change
    $calcFieldsTypes.add($calcFieldsAddons).on('input', updateSummary);
    $supportRadios.on('change', updateSummary);

    // Quote button click event
    $quoteBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'quote-submit');
        return false;
    });

    // Form submit event
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

    // Thank you close button event
    $thankYouCloseBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'get-quote');
        return false;
    });

    // Terms toggle event
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

    // Select Block behavior
    $('.select-footer-block input[type="radio"]').on('change', function() {
        const $label = $(this).closest('label');
        const $span = $label.find('span');

        // Deselect others
        $('.select-footer-block input[type="radio"]').each(function() {
            const $otherLabel = $(this).closest('label');
            const $otherSpan = $otherLabel.find('span');
            if (!$(this).is(':checked')) {
                $otherSpan.text('Select Block');
                $otherLabel.css('border', 'none');
            }
        });

        if ($(this).is(':checked')) {
            $span.text('Selected');
            $label.css('border', '3px solid #394494');
        }
    });

    // === Preselect the first footer block ===
    const $firstRadio = $('.select-footer-block input[type="radio"]').first();
    const $firstLabel = $firstRadio.closest('label');
    const $firstSpan = $firstLabel.find('span');

    $firstRadio.prop('checked', true);
    $firstSpan.text('Selected');
    $firstLabel.css('border', '3px solid #394494');

    // === Fix default toggle state for support blocks ===
    $supportBlocks.each(function() {
        const $block = $(this);
        $block.removeClass('open');
        $block.find('.support-features').hide();
        $block.find('.toggle-icon').removeClass('rotate');
    });

    // Trigger summary update on load
    updateSummary();
});







