/**
 * Handzom UI Kit: Luxury Story Scroll Script
 */
(function ($) {
    'use strict';

    var WidgetLuxuryStoryScrollHandler = function ($scope, $) {
        var $wrapper = $scope.find('.hz-lss-wrapper');
        if (!$wrapper.length) {
            return;
        }

        // We now enable sticky scroll for all devices, including mobile.
        var $bgItems = $wrapper.find('.hz-lss-bg-item');
        var $textItems = $wrapper.find('.hz-lss-text-item');
        var $dots = $wrapper.find('.hz-lss-dot');
        var storiesCount = $wrapper.data('stories') || 1;
        var scrollSensitivity = parseFloat($wrapper.data('scroll-sensitivity')) || 0.2;
        var wrapperTop = 0;
        var windowHeight = window.innerHeight;
        var scrollThreshold = windowHeight;
        var totalWrapperHeight = windowHeight;
        var currentIndex = 0;
        var ticking = false;

        var updateMetrics = function () {
            // Elementor often adds overflow:hidden on mobile which breaks position:sticky. 
            // We must force ALL parents (except html/body) to be visible for sticky to work.
            $wrapper.parents().each(function () {
                var $this = $(this);
                if ($this.is('html') || $this.is('body')) return;

                var ox = $this.css('overflow-x');
                var oy = $this.css('overflow-y');
                var o = $this.css('overflow');
                if (ox === 'hidden' || oy === 'hidden' || o === 'hidden') {
                    $this[0].style.setProperty('overflow', 'visible', 'important');
                    $this[0].style.setProperty('overflow-x', 'visible', 'important');
                    $this[0].style.setProperty('overflow-y', 'visible', 'important');
                    $this[0].style.setProperty('overflow-clip-margin', 'unset', 'important');
                }

                // Any transform, filter, or perspective on a parent breaks position: sticky!
                var t = $this.css('transform');
                if (t && t !== 'none') {
                    $this[0].style.setProperty('transform', 'none', 'important');
                }
            });

            // Calculate the height of the sticky container (which can be customized in Elementor)
            var stickyHeight = $wrapper.find('.hz-lss-sticky').outerHeight() || window.innerHeight;
            scrollThreshold = stickyHeight * scrollSensitivity;
            totalWrapperHeight = stickyHeight + (scrollThreshold * (storiesCount - 1));

            // Set the parent wrapper height so it has room to scroll all stories
            $wrapper.css('height', totalWrapperHeight + 'px');

            wrapperTop = $wrapper.offset().top;
            windowHeight = stickyHeight;
        };

        var updateActiveState = function (index) {
            if (index === currentIndex) return;

            // Remove active classes
            $bgItems.removeClass('active');
            $textItems.removeClass('active exiting');
            $dots.removeClass('active');

            // Add exiting to old text to slide it up
            $textItems.eq(currentIndex).addClass('exiting');

            // Activate new items
            $bgItems.eq(index).addClass('active');
            $textItems.eq(index).addClass('active');
            $dots.eq(index).addClass('active');

            currentIndex = index;
        };

        var onScroll = function () {
            var rect = $wrapper[0].getBoundingClientRect();

            // rect.top is the distance from the viewport top to the wrapper top.
            // If it's > windowHeight, it's below the fold. If it's < -(total height), it's above.
            if (rect.top > windowHeight || rect.top < -totalWrapperHeight) {
                return;
            }

            // How far we have scrolled *into* the wrapper (0 when wrapper hits the top of viewport)
            var scrollDistance = -rect.top;
            if (scrollDistance < 0) scrollDistance = 0;

            // Use the scrollThreshold (which is a fraction of the screen based on sensitivity)
            var index = Math.floor(scrollDistance / scrollThreshold);

            // Clamp
            if (index < 0) index = 0;
            if (index >= storiesCount) index = storiesCount - 1;

            updateActiveState(index);
        };

        var requestTick = function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    onScroll();
                    ticking = false;
                });
                ticking = true;
            }
        };

        // Initialize
        updateMetrics();
        $(window).on('resize', updateMetrics);
        $(window).on('scroll', requestTick);

        // Dot click support
        $dots.on('click', function () {
            var idx = $(this).data('index');
            var dynamicWrapperTop = $wrapper.offset().top;
            var targetScroll = dynamicWrapperTop + (idx * scrollThreshold) + 5;

            window.scrollTo({
                top: targetScroll,
                behavior: 'smooth'
            });
        });
    };

    var initWidget = function () {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_luxury_story_scroll.default', WidgetLuxuryStoryScrollHandler);

        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_luxury_story_scroll').each(function () {
                WidgetLuxuryStoryScrollHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);

    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
