/**
 * Handzom UI Kit: Featured Cards Script
 */
(function ($) {
    'use strict';

    var WidgetFeaturedCardsHandler = function ($scope, $) {
        var $slider = $scope.find('.handzom-swiper-container');
        if (!$slider.length) {
            return;
        }

        var settings = $slider.data('settings');

        var swiperOptions = {
            slidesPerView: settings.mobile_cols || 1,
            spaceBetween: settings.gap_mobile || 10,
            loop: settings.infinite === 'yes',
            speed: settings.speed || 500,
            watchSlidesProgress: true,
            breakpoints: {
                768: {
                    slidesPerView: settings.tablet_cols || 2,
                    spaceBetween: settings.gap_tablet || 20
                },
                1025: {
                    slidesPerView: settings.desktop_cols || 4,
                    spaceBetween: settings.gap || 20
                }
            }
        };

        // Fix Swiper breaking if loop is true but not enough slides exist
        var slideCount = $slider.find('.swiper-slide:not(.swiper-slide-duplicate)').length;
        if (swiperOptions.loop && slideCount <= (settings.desktop_cols || 4)) {
            swiperOptions.loop = false;
        }

        if (settings.autoplay === 'yes') {
            swiperOptions.autoplay = {
                delay: settings.autoplay_speed || 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: settings.pause_on_hover === 'yes'
            };
        }

        if (settings.arrows === 'yes') {
            swiperOptions.navigation = {
                nextEl: $scope.find('.handzom-swiper-next')[0],
                prevEl: $scope.find('.handzom-swiper-prev')[0]
            };
        }

        if (settings.dots === 'yes') {
            swiperOptions.pagination = {
                el: $scope.find('.handzom-swiper-pagination')[0],
                clickable: true
            };
        }

        if ('undefined' === typeof Swiper) {
            if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.utils !== 'undefined' && typeof elementorFrontend.utils.swiper !== 'undefined') {
                const asyncSwiper = elementorFrontend.utils.swiper;
                new asyncSwiper($slider, swiperOptions).then((newSwiperInstance) => {
                    // Instance initialized.
                }).catch(function (err) {
                    console.error("Handzom: Swiper init failed", err);
                });
            } else {
                console.error("Handzom: Elementor Swiper utility is missing on this page.");
            }
        } else {
            new Swiper($slider[0], swiperOptions);
        }
    };

    var initWidget = function () {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }

        // Hook for Elementor Editor (when widgets are dragged/dropped)
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_featured_cards.default', WidgetFeaturedCardsHandler);

        // Manual initialization for Live Frontend (in case the hook fired before this script loaded)
        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_featured_cards').each(function () {
                WidgetFeaturedCardsHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);

    // If Elementor is already initialized before this script runs, call it immediately.
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
