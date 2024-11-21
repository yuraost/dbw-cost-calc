jQuery(document).ready(function($){
    const $form = $('#dbw-cost-calc-form');
    const $calcFieldsTypes = $form.find('.dbw-cost-calc-fields-types input[type="number"]');
    const $calcFieldsAddons = $form.find('.dbw-cost-calc-fields-extra input[type="number"]');
    const $quoteBtn = $('#get-quote-btn');
    const $messages = $('#form-messages')
    const $thankYouCloseBtn = $('#thank-you-close');
    const $termsToggle = $('.dbw-cost-calc-terms-title');

    $calcFieldsTypes.add($calcFieldsAddons).on('input', function(e) {
        let priceBeforeDiscount = 0;
        let totalInstanceQuantity = 0;

        $calcFieldsTypes.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val <= 0) return;
            priceBeforeDiscount += val * dbwCostCalcData.instances[$(this).attr('name')].price;
            totalInstanceQuantity += val;
        });

        $calcFieldsAddons.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val <= 0) return;
            priceBeforeDiscount += val * dbwCostCalcData.addons[$(this).attr('name')].price;
        });

        let discount = 0;
        for (const rate of dbwCostCalcData.discountRates) {
            if (totalInstanceQuantity >= rate.minQty) {
                discount = rate.discount;
            }
        }

        const discountAmount = priceBeforeDiscount * discount;
        const priceAfterDiscount = priceBeforeDiscount - discountAmount;
        const totalPricePerInstance = totalInstanceQuantity > 0 ? priceAfterDiscount / totalInstanceQuantity : 0;
        const totalPricePerMonth = priceAfterDiscount / 12;

        const USDollar = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });
        $('#price-before-discount').text(USDollar.format(priceBeforeDiscount));
        $('#discount-amount').text(USDollar.format(discountAmount));
        $('#total-price-per-instance').text(USDollar.format(totalPricePerInstance));
        $('#total-price-per-month').text(USDollar.format(totalPricePerMonth));
        $('#price-after-discount').text(USDollar.format(priceAfterDiscount));
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
            if (val <= 0) return;
            instances.push({
                'name': dbwCostCalcData.instances[$(this).attr('name')].name,
                'qty': val
            });
        });
        if (instances.length === 0) {
            $messages.html('<span class="error">Add at least one instance to get quota.</span>');
            $form.removeClass('processing');
            $inputs.removeAttr('disabled');
            return false;
        }

        const addons = [];
        $calcFieldsAddons.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val <= 0) return;
            addons.push({
                'name': dbwCostCalcData.addons[$(this).attr('name')].name,
                'qty': val
            });
        });

        $.post(dbwCostCalcData.ajax.url, {
            action: 'dbwCostCalcGetQuote',
            nonce: dbwCostCalcData.ajax.nonce,
            instances: instances,
            addons: addons,
            priceBeforeDiscount: $('#price-before-discount').text(),
            discountAmount: $('#discount-amount').text(),
            totalPricePerInstance: $('#total-price-per-instance').text(),
            totalPricePerMonth: $('#total-price-per-month').text(),
            priceAfterDiscount: $('#price-after-discount').text(),
            email: $form.find('input[name="email"]').val(),
            name: $form.find('input[name="name"]').val(),
            company: $form.find('input[name="company"]').val()
        }, function(response) {
            if (response.success) {
                $form.attr('data-step', 'thank-you');
            } else {
                $messages.html('<span class="error">' + response.data.message + '</span>');
                console.error(response.data.error);
            }
        })
        .fail(function(xhr) {
            $messages.html('<span class="error">An unexpected error has occurred. Please reload the page and try again.</span>');
            console.error('XHR', xhr);
        })
        .always(function() {
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
        const $_this = $(this),
            $svg = $_this.find('svg');
            $content = $_this.parent().find('.dbw-cost-calc-terms-content');

        if ($_this.is('.active')) {
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
});