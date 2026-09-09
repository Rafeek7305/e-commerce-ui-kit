/**
 * Handzom UI Kit: Trending Products Script
 */
(function ($) {
    'use strict';

    var WidgetTrendingProductsHandler = function ($scope, $) {
        var $wrapper = $scope.find('.hz-trending-products-wrapper');
        if (!$wrapper.length) {
            return;
        }

        var $tabs = $wrapper.find('.hz-tp-tab-title');
        var $panes = $wrapper.find('.hz-tp-tab-pane');

        $tabs.on('click', function () {
            var $this = $(this);
            var targetId = $this.data('target');

            // Remove active classes
            $tabs.removeClass('hz-tp-active');
            $panes.removeClass('hz-tp-active');

            // Add active class to clicked tab and corresponding pane
            $this.addClass('hz-tp-active');
            $('#' + targetId).addClass('hz-tp-active');
        });
    };

    var initWidget = function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }
        
        // Hook for Elementor Editor
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_trending_products.default', WidgetTrendingProductsHandler);
        
        // Manual initialization for Live Frontend
        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_trending_products').each(function () {
                WidgetTrendingProductsHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);
    
    // If Elementor is already initialized before this script runs, call it immediately.
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
