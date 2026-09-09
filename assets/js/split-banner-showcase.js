/**
 * Handzom UI Kit: Split Banner Showcase Script
 */
(function ($) {
    'use strict';

    var WidgetSplitBannerShowcaseHandler = function ($scope, $) {
        // Most interactions are CSS driven, but we can handle fallback JS here
        // if needed in the future (like complex parallax calculations).
    };

    var initWidget = function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }
        
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_split_banner_showcase.default', WidgetSplitBannerShowcaseHandler);
        
        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_split_banner_showcase').each(function () {
                WidgetSplitBannerShowcaseHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);
    
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
