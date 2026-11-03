/**
 * Handzom UI Kit: Premium Glass Header Script
 */
(function ($) {
    'use strict';

    // Global document-delegated event handlers (bound once immediately)
    var bindGlobalEvents = function () {
        if (window.__hzPghEventsBound) {
            return;
        }
        window.__hzPghEventsBound = true;

        // Mobile drawer open/toggle
        $(document).on('click', '.hz-pgh-hamburger', function (e) {
            e.preventDefault();
            var $this = $(this);
            var $wrapper = $this.closest('.hz-pgh-wrapper');
            var $drawer = $wrapper.find('.hz-pgh-mobile-drawer');
            if (!$drawer.length) {
                $drawer = $('.hz-pgh-mobile-drawer').first();
            }

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

        // Mobile drawer close
        $(document).on('click', '.hz-pgh-drawer-close, .hz-pgh-drawer-overlay', function (e) {
            e.preventDefault();
            $('.hz-pgh-hamburger').removeClass('is-active');
            $('.hz-pgh-mobile-drawer').removeClass('is-active');
            $('body').css('overflow', '');
            $('html, body').removeClass('hz-pgh-drawer-open');
        });

        // Mobile nav link click closes drawer
        $(document).on('click', '.hz-pgh-mobile-ul li a', function () {
            $('.hz-pgh-hamburger').removeClass('is-active');
            $('.hz-pgh-mobile-drawer').removeClass('is-active');
            $('body').css('overflow', '');
            $('html, body').removeClass('hz-pgh-drawer-open');
        });

        // Wishlist click handler for dummy '#' links
        $(document).on('click', '.hz-pgh-wishlist-btn', function (e) {
            var href = $(this).attr('href');
            if (!href || href === '#') {
                e.preventDefault();
            }
        });

        // Search open
        $(document).on('click', '.hz-pgh-search-toggle', function (e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.hz-pgh-wrapper');
            var $search = $wrapper.find('.hz-pgh-search-overlay');
            if (!$search.length) {
                $search = $('.hz-pgh-search-overlay').first();
            }
            $search.addClass('is-active');
            setTimeout(function () {
                $search.find('.hz-pgh-search-field').focus();
            }, 100);
            $('body').css('overflow', 'hidden');
        });

        // Search close
        $(document).on('click', '.hz-pgh-search-close', function (e) {
            e.preventDefault();
            $('.hz-pgh-search-overlay').removeClass('is-active');
            $('body').css('overflow', '');
        });

        // Announcement close
        $(document).on('click', '.hz-pgh-announcement-close', function (e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.hz-pgh-wrapper');
            var $announcement = $wrapper.find('.hz-pgh-announcement');
            $announcement.slideUp(300);
        });

        // ESC key handler
        $(document).on('keydown.hzpgh', function (e) {
            if (e.key === "Escape") {
                var $activeSearch = $('.hz-pgh-search-overlay.is-active');
                if ($activeSearch.length) {
                    $activeSearch.find('.hz-pgh-search-close').first().trigger('click');
                }
                var $activeDrawer = $('.hz-pgh-mobile-drawer.is-active');
                if ($activeDrawer.length) {
                    $activeDrawer.find('.hz-pgh-drawer-close').first().trigger('click');
                }
            }
        });
    };

    // Bind event delegation immediately
    bindGlobalEvents();

    var WidgetPremiumGlassHeaderHandler = function ($scope, $) {
        var $wrapper = $scope.find('.hz-pgh-wrapper');
        if (!$wrapper.length || $wrapper.data('hz-header-init')) {
            return;
        }
        $wrapper.data('hz-header-init', true);

        var $headerInner = $wrapper.find('.hz-pgh-header-inner');
        var $announcement = $wrapper.find('.hz-pgh-announcement');

        var stickyHeight = $wrapper.data('sticky-height') || 72;
        var stickyBg = $wrapper.data('sticky-bg') || 'rgba(255, 255, 255, 0.95)';
        var isSticky = false;
        var ticking = false;

        // Optimized sticky scroll handler using requestAnimationFrame
        var checkSticky = function () {
            var announcementHeight = $announcement.length && $announcement.is(':visible') ? $announcement.outerHeight() : 0;
            var scrollTop = $(window).scrollTop();

            if (scrollTop > announcementHeight + 50) {
                if (!isSticky) {
                    $wrapper.addClass('is-sticky');
                    $headerInner.css({
                        'background': stickyBg,
                        'height': stickyHeight + 'px'
                    });
                    isSticky = true;
                }
            } else {
                if (isSticky) {
                    $wrapper.removeClass('is-sticky');
                    $headerInner.css({
                        'background': '',
                        'height': ''
                    });
                    isSticky = false;
                }
            }
            ticking = false;
        };

        var handleScroll = function () {
            if (!ticking) {
                window.requestAnimationFrame(checkSticky);
                ticking = true;
            }
        };

        $(window).on('scroll', handleScroll);

        // Only trigger scroll check on load if page was already scrolled down
        if ($(window).scrollTop() > 50) {
            checkSticky();
        }
    };

    var initWidget = function () {
        bindGlobalEvents();

        if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/handzom_premium_glass_header.default', WidgetPremiumGlassHeaderHandler);
        }

        $('.elementor-widget-handzom_premium_glass_header').each(function () {
            WidgetPremiumGlassHeaderHandler($(this), $);
        });
    };

    $(window).on('elementor/frontend/init', initWidget);

    $(document).ready(function () {
        initWidget();
    });

})(jQuery);

