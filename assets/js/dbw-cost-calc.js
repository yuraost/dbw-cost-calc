jQuery(document).ready(function($){
    const $form = $('#dbw-cost-calc-form'),
        $quoteBtn = $('#get-quote-btn'),
        $thankYouCloseBtn = $('#thank-you-close'),
        $termsToggle = $('.dbw-cost-calc-terms-title');

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