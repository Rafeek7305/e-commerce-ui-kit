/**
 * Handzom UI Kit: Premium Glass Header Script
 */
(function ($) {
    'use strict';

    var WidgetPremiumGlassHeaderHandler = function ($scope, $) {
        var $wrapper = $scope.find('.hz-pgh-wrapper');
        if (!$wrapper.length) {
            return;
        }

        var $headerInner = $wrapper.find('.hz-pgh-header-inner');

        // Any transform on a parent breaks position: fixed. Strip them.
        $wrapper.parents().each(function() {
            var $this = $(this);
            if ($this.is('html') || $this.is('body')) return;
            var t = $this.css('transform');
            if (t && t !== 'none') {
                $this[0].style.setProperty('transform', 'none', 'important');
            }
            var wc = $this.css('will-change');
            if (wc && wc !== 'auto') {
                $this[0].style.setProperty('will-change', 'auto', 'important');
            }
            var f = $this.css('filter');
            if (f && f !== 'none') {
                $this[0].style.setProperty('filter', 'none', 'important');
            }
            var bf = $this.css('backdrop-filter') || $this.css('-webkit-backdrop-filter');
            if (bf && bf !== 'none') {
                $this[0].style.setProperty('backdrop-filter', 'none', 'important');
                $this[0].style.setProperty('-webkit-backdrop-filter', 'none', 'important');
            }
        });

        var $hamburger = $wrapper.find('.hz-pgh-hamburger');
        var $mobileDrawer = $wrapper.find('.hz-pgh-mobile-drawer');
        var $searchToggle = $wrapper.find('.hz-pgh-search-toggle');
        var $searchOverlay = $wrapper.find('.hz-pgh-search-overlay');
        var $announcementClose = $wrapper.find('.hz-pgh-announcement-close');
        var $announcement = $wrapper.find('.hz-pgh-announcement');

        // Move overlays to body to avoid overflow/z-index issues
        if ($mobileDrawer.length && !$mobileDrawer.parent().is('body')) {
            $('body').append($mobileDrawer);
        }
        if ($searchOverlay.length && !$searchOverlay.parent().is('body')) {
            $('body').append($searchOverlay);
        }

        var stickyHeight = $wrapper.data('sticky-height') || 72;
        var stickyBg = $wrapper.data('sticky-bg') || 'rgba(255, 255, 255, 0.95)';
        var isSticky = false;

        // --- STICKY LOGIC ---
        var handleScroll = function () {
            // Need to account for announcement bar height
            var announcementHeight = $announcement.length && $announcement.is(':visible') ? $announcement.outerHeight() : 0;
            var scrollTop = $(window).scrollTop();

            if (scrollTop > announcementHeight + 50) {
                if (!isSticky) {
                    $wrapper.addClass('is-sticky');
                    // Dynamic styling for sticky
                    $headerInner.css({
                        'background': stickyBg,
                        'height': stickyHeight + 'px'
                    });
                    isSticky = true;
                }
            } else {
                if (isSticky) {
                    $wrapper.removeClass('is-sticky');
                    // Reset to CSS variables
                    $headerInner.css({
                        'background': '',
                        'height': ''
                    });
                    isSticky = false;
                }
            }
        };

        $(window).on('scroll', handleScroll);
        // Trigger once on load
        handleScroll();

        // --- MOBILE DRAWER LOGIC ---
        $(document).off('click', '.hz-pgh-hamburger').on('click', '.hz-pgh-hamburger', function (e) {
            e.preventDefault();
            var $this = $(this);
            var $drawer = $('.hz-pgh-mobile-drawer').first();
            
            $this.toggleClass('is-active');
            $drawer.toggleClass('is-active');
            if ($drawer.hasClass('is-active')) {
                $('body').css('overflow', 'hidden');
                $('html, body').addClass('hz-pgh-drawer-open');
            } else {
                $('body').css('overflow', '');
                $('html, body').removeClass('hz-pgh-drawer-open');
            }
        });

        $(document).off('click', '.hz-pgh-drawer-close, .hz-pgh-drawer-overlay').on('click', '.hz-pgh-drawer-close, .hz-pgh-drawer-overlay', function (e) {
            e.preventDefault();
            $('.hz-pgh-hamburger').removeClass('is-active');
            $('.hz-pgh-mobile-drawer').removeClass('is-active');
            $('body').css('overflow', '');
            $('html, body').removeClass('hz-pgh-drawer-open');
        });

        $(document).off('click', '.hz-pgh-mobile-ul li a').on('click', '.hz-pgh-mobile-ul li a', function () {
            $('.hz-pgh-hamburger').removeClass('is-active');
            $('.hz-pgh-mobile-drawer').removeClass('is-active');
            $('body').css('overflow', '');
            $('html, body').removeClass('hz-pgh-drawer-open');
        });

        // --- SEARCH LOGIC ---
        $(document).off('click', '.hz-pgh-search-toggle').on('click', '.hz-pgh-search-toggle', function (e) {
            e.preventDefault();
            var $search = $('.hz-pgh-search-overlay').first();
            $search.addClass('is-active');
            setTimeout(function() {
                $search.find('.hz-pgh-search-field').focus();
            }, 100);
            $('body').css('overflow', 'hidden');
        });

        $(document).off('click', '.hz-pgh-search-close').on('click', '.hz-pgh-search-close', function (e) {
            e.preventDefault();
            $('.hz-pgh-search-overlay').removeClass('is-active');
            $('body').css('overflow', '');
        });

        // --- ANNOUNCEMENT CLOSE ---
        $(document).off('click', '.hz-pgh-announcement-close').on('click', '.hz-pgh-announcement-close', function(e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.hz-pgh-wrapper');
            var $announcement = $wrapper.find('.hz-pgh-announcement');
            $announcement.slideUp(300);
            setTimeout(handleScroll, 310);
        });

        // ESC Key Support
        $(document).off('keydown.hzpgh').on('keydown.hzpgh', function(e) {
            if (e.key === "Escape") {
                $('.hz-pgh-search-overlay.is-active').each(function() {
                    $(this).closest('.hz-pgh-wrapper').find('.hz-pgh-search-close').trigger('click');
                });
                $('.hz-pgh-mobile-drawer.is-active').each(function() {
                    $(this).closest('.hz-pgh-wrapper').find('.hz-pgh-drawer-close').first().trigger('click');
                });
            }
        });
    };

    var initWidget = function () {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_premium_glass_header.default', WidgetPremiumGlassHeaderHandler);

        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_premium_glass_header').each(function () {
                WidgetPremiumGlassHeaderHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);

    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        if (!elementorFrontend.isEditMode()) {
            $(document).ready(function() {
                $('.elementor-widget-handzom_premium_glass_header').each(function () {
                    WidgetPremiumGlassHeaderHandler($(this), $);
                });
            });
        }
    }

})(jQuery);
