jQuery(document).ready(function($){
    const $form = $('#dbw-cost-calc-form');
    const $calcFieldsTypes = $form.find('.dbw-cost-calc-fields-types input[type="number"]');
    const $calcFieldsAddons = $form.find('.dbw-cost-calc-fields-extra input[type="number"]');
    const $quoteBtn = $('#get-quote-btn');
    const $thankYouCloseBtn = $('#thank-you-close');
    const $termsToggle = $('.dbw-cost-calc-terms-title');

    $calcFieldsTypes.add($calcFieldsAddons).on('input', function(e) {
        let priceBeforeDiscount = 0;
        let totalInstanceQuantity = 0;

        $calcFieldsTypes.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val <= 0) return;
            priceBeforeDiscount += val * dbwCostCalcData.instancePricing[$(this).attr('name')];
            totalInstanceQuantity += val;
        });

        $calcFieldsAddons.each(function() {
            const val = parseInt($(this).val(), 10);
            if (val <= 0) return;
            priceBeforeDiscount += val * dbwCostCalcData.addonsPricing[$(this).attr('name')];
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

        $('#price-before-discount').text(priceBeforeDiscount.toFixed(2));
        $('#discount').text(discountAmount.toFixed(2));
        $('#yearly-price-per-instance').text(totalPricePerInstance.toFixed(2));
        $('#total-price-per-month').text(totalPricePerMonth.toFixed(2));
        $('#total-price-per-year').text(priceAfterDiscount.toFixed(2));
    });

    $quoteBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'quote-submit');
        return false;
    });

    $form.on('submit', function(e) {
        e.preventDefault();

        $form.attr('data-step', 'thank-you');
        return false;
    });

    $thankYouCloseBtn.on('click', function(e) {
        e.preventDefault();
        // todo reset calc
        // todo empty fields
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