/**
 * Handzom UI Kit: Premium Luxury Footer Script
 */
(function ($) {
    'use strict';

    var bindGlobalFooterEvents = function () {
        if (window.__hzPlfEventsBound) {
            return;
        }
        window.__hzPlfEventsBound = true;

        // Mobile Accordion Logic
        $(document).on('click', '.hz-plf-menu-col .hz-plf-col-title, .hz-plf-contact-col .hz-plf-col-title', function (e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parent();

                $parent.toggleClass('is-active');
                $parent.find('.hz-plf-menu, .hz-plf-contact-list').stop(true, true).slideToggle(300);
            }
        });
    };

    bindGlobalFooterEvents();

    var WidgetPremiumLuxuryFooterHandler = function ($scope, $) {
        bindGlobalFooterEvents();
    };

    var initWidget = function () {
        bindGlobalFooterEvents();

        if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/handzom_premium_luxury_footer.default', WidgetPremiumLuxuryFooterHandler);
        }

        $('.elementor-widget-handzom_premium_luxury_footer').each(function () {
            WidgetPremiumLuxuryFooterHandler($(this), $);
        });
    };

    $(window).on('elementor/frontend/init', initWidget);

    $(document).ready(function () {
        initWidget();
    });

})(jQuery);

