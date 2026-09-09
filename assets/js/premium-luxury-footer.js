/**
 * Handzom UI Kit: Premium Luxury Footer Script
 */
(function ($) {
    'use strict';

    var WidgetPremiumLuxuryFooterHandler = function ($scope, $) {
        var $wrapper = $scope.find('.hz-plf-wrapper');
        if (!$wrapper.length) {
            return;
        }

        // Scroll to top functionality removed by user request
        
        // Mobile Accordion Logic
        $wrapper.find('.hz-plf-menu-col .hz-plf-col-title, .hz-plf-contact-col .hz-plf-col-title').off('click.hzFooter').on('click.hzFooter', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parent();
                
                $parent.toggleClass('is-active');
                $parent.find('.hz-plf-menu, .hz-plf-contact-list').stop(true, true).slideToggle(300);
            }
        });
    };

    var initWidget = function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }
        
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_premium_luxury_footer.default', WidgetPremiumLuxuryFooterHandler);
        
        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_premium_luxury_footer').each(function () {
                WidgetPremiumLuxuryFooterHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);
    
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
