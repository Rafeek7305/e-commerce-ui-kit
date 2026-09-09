/**
 * Handzom UI Kit: Testimonials Carousel Script
 */
(function ($) {
    'use strict';

    var WidgetTestimonialsCarouselHandler = function ($scope, $) {
        var $slider = $scope.find('.hz-tc-swiper-container');
        if (!$slider.length) {
            return;
        }

        var settings = $slider.data('settings');
        
        var swiperOptions = {
            slidesPerView: settings.mobile_cols || 1,
            spaceBetween: settings.gap_mobile || 10,
            loop: settings.loop === 'yes',
            speed: settings.speed || 500,
            centeredSlides: settings.center_mode === 'yes',
            allowTouchMove: settings.touch_swipe !== 'no',
            grabCursor: settings.drag_mouse !== 'no',
            watchSlidesProgress: true,
            breakpoints: {
                768: {
                    slidesPerView: settings.tablet_cols || 2,
                    spaceBetween: settings.gap_tablet || 20
                },
                1025: {
                    slidesPerView: settings.desktop_cols || 5,
                    spaceBetween: settings.gap || 20
                }
            }
        };

        // If 'continuous move' style is requested (marquee)
        if (settings.continuous_scroll === 'yes') {
            swiperOptions.autoplay = {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: settings.pause_on_hover === 'yes'
            };
            swiperOptions.speed = settings.speed || 3000;
            swiperOptions.freeMode = true;
            swiperOptions.loop = true;
            swiperOptions.on = {
                touchEnd: function() {
                    var swiper = this;
                    setTimeout(function() {
                        if (swiper && swiper.autoplay && !swiper.autoplay.running) {
                            swiper.autoplay.start();
                        }
                    }, 500);
                },
                sliderMove: function() {
                    var swiper = this;
                    if (swiper && swiper.autoplay) {
                        swiper.autoplay.stop();
                    }
                }
            };
        } else if (settings.autoplay === 'yes') {
            swiperOptions.autoplay = {
                delay: settings.autoplay_speed || 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: settings.pause_on_hover === 'yes'
            };
        }

        if (settings.keyboard_nav === 'yes') {
            swiperOptions.keyboard = {
                enabled: true,
                onlyInViewport: true
            };
        }

        if (settings.navigation === 'yes') {
            swiperOptions.navigation = {
                nextEl: $scope.find('.hz-tc-next')[0],
                prevEl: $scope.find('.hz-tc-prev')[0]
            };
        }

        if (settings.pagination !== 'none') {
            var pagType = settings.pagination === 'bullets' ? 'bullets' : (settings.pagination === 'fraction' ? 'fraction' : 'progressbar');
            swiperOptions.pagination = {
                el: $scope.find('.hz-tc-pagination')[0],
                type: pagType,
                clickable: true
            };
        }

        // Safety fix: disable loop if slide count is less than or equal to slidesPerView
        var slideCount = $slider.find('.swiper-slide:not(.swiper-slide-duplicate)').length;
        var cols = settings.desktop_cols || 3;
        if ( swiperOptions.loop && slideCount <= cols ) {
            swiperOptions.loop = false;
        }

        var initSwiper = function() {
            if ( 'undefined' === typeof Swiper ) {
                if ( typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.utils !== 'undefined' && typeof elementorFrontend.utils.swiper !== 'undefined' ) {
                    const asyncSwiper = elementorFrontend.utils.swiper;
                    new asyncSwiper($slider, swiperOptions).then((newSwiperInstance) => {
                        // Instance initialized.
                    }).catch(function (err) {
                        console.error("Handzom: Testimonials Swiper init failed", err);
                    });
                }
            } else {
                new Swiper($slider[0], swiperOptions);
            }
        };

        initSwiper();

        // Extra aggressive fallback to resume marquee if it gets stuck
        if (settings.continuous_scroll === 'yes') {
            $slider.on('mouseleave mouseup touchend', function() {
                setTimeout(function() {
                    var swiperInstance = $slider[0].swiper || $slider.data('swiper');
                    if (swiperInstance && swiperInstance.autoplay && !swiperInstance.autoplay.running) {
                        swiperInstance.autoplay.start();
                    }
                }, 500);
            });
        }
    };

    var initWidget = function() {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }
        
        // Hook for Elementor Editor
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_testimonials_carousel.default', WidgetTestimonialsCarouselHandler);
        
        // Manual initialization for Live Frontend
        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_testimonials_carousel').each(function () {
                WidgetTestimonialsCarouselHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);
    
    // Fallback if Elementor is already initialized
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
