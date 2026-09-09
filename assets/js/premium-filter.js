(function ($) {
    'use strict';

    var WidgetPremiumFilterHandler = function ($scope, $) {
        var $drawer = $scope.find('.hz_pf_drawer');
        if (!$drawer.length) return;

        var $form = $scope.find('.hz_pf_form');
        var $priceSlider = $scope.find('.hz_pf_price_slider');
        var targetSelector = $drawer.data('target') || 'ul.products';

        var isMobile = function () {
            return window.innerWidth <= 768;
        };
        var fetchTimeout;

        // --- Accordion Logic ---
        $scope.find('.hz_pf_accordion_header').on('click', function () {
            var $accordion = $(this).parent();
            $accordion.toggleClass('hz_pf_open');
        });

        // --- Mobile Drawer Logic ---
        $scope.find('.hz_pf_btn_toggle').on('click', function () {
            $drawer.addClass('hz_pf_drawer_open');
            $('body').css('overflow', 'hidden'); // Prevent background scrolling
        });

        $scope.find('.hz_pf_btn_close, .hz_pf_drawer_overlay').on('click', function () {
            $drawer.removeClass('hz_pf_drawer_open');
            $('body').css('overflow', '');
        });

        // --- Price Slider Initialization (requires jquery-ui-slider) ---
        if ($priceSlider.length && typeof $.fn.slider !== 'undefined') {
            var $wrapper = $priceSlider.closest('.hz_pf_price_wrapper');
            var minPrice = parseFloat($wrapper.data('min')) || 0;
            var maxPrice = parseFloat($wrapper.data('max')) || 1000;
            var $minInput = $wrapper.find('.hz_pf_price_min');
            var $maxInput = $wrapper.find('.hz_pf_price_max');

            var currentMin = parseFloat($minInput.val()) || minPrice;
            var currentMax = parseFloat($maxInput.val()) || maxPrice;

            $priceSlider.slider({
                range: true,
                min: minPrice,
                max: maxPrice,
                values: [currentMin, currentMax],
                slide: function (event, ui) {
                    $minInput.val(ui.values[0]);
                    $maxInput.val(ui.values[1]);
                },
                change: function (event, ui) {
                    if (!isMobile()) {
                        triggerAjax();
                    }
                }
            });

            // Update slider when inputs change
            $minInput.add($maxInput).on('change', function () {
                var min = parseFloat($minInput.val());
                var max = parseFloat($maxInput.val());

                if (min < minPrice) min = minPrice;
                if (max > maxPrice) max = maxPrice;
                if (min > max) min = max;

                $minInput.val(min);
                $maxInput.val(max);

                $priceSlider.slider('values', [min, max]);
            });
        }

        // --- AJAX Filtering Logic ---
        function triggerAjax() {
            var currentUrl = window.location.href.split('?')[0];
            var formData = $form.serialize();

            // Clean empty params
            var params = new URLSearchParams(formData);
            var cleanParams = new URLSearchParams();
            for (var [key, value] of params.entries()) {
                if (value !== '' && value !== null) {
                    cleanParams.append(key, value);
                }
            }

            var newUrl = currentUrl + '?' + cleanParams.toString();

            // Add loading state
            $scope.addClass('hz_pf_loading');
            $(targetSelector).css('opacity', '0.5');

            $.ajax({
                url: newUrl,
                type: 'GET',
                success: function (response) {
                    var $html = $(response);

                    // Replace products grid
                    var $newProducts = $html.find(targetSelector);
                    if ($newProducts.length) {
                        $(targetSelector).html($newProducts.html());
                    } else {
                        // Fallback if no products found
                        $(targetSelector).html('<li class="no-products-found">No products found matching your selection.</li>');
                    }

                    // Update pagination if exists
                    if ($('.woocommerce-pagination').length && $html.find('.woocommerce-pagination').length) {
                        $('.woocommerce-pagination').html($html.find('.woocommerce-pagination').html());
                    } else if ($html.find('.woocommerce-pagination').length) {
                        $(targetSelector).after($html.find('.woocommerce-pagination'));
                    } else {
                        $('.woocommerce-pagination').remove();
                    }

                    // Update result count
                    if ($('.woocommerce-result-count').length && $html.find('.woocommerce-result-count').length) {
                        $('.woocommerce-result-count').html($html.find('.woocommerce-result-count').html());
                    }

                    // Update URL without reload
                    window.history.pushState({ path: newUrl }, '', newUrl);
                },
                complete: function () {
                    $scope.removeClass('hz_pf_loading');
                    $(targetSelector).css('opacity', '1');

                    // Trigger custom event for third-party scripts (e.g. lazyload)
                    $(document).trigger('hz_pf_ajax_complete');
                }
            });
        }

        // --- Event Listeners ---
        // Inputs change
        $form.on('change', 'input[type="checkbox"]', function () {
            if (!isMobile()) {
                triggerAjax();
            }
        });

        // Search input debounce
        $form.on('input', '.hz_pf_search_input', function () {
            if (!isMobile()) {
                clearTimeout(fetchTimeout);
                fetchTimeout = setTimeout(triggerAjax, 600);
            }
        });

        // Form Submit (for mobile or pressing enter)
        $form.on('submit', function (e) {
            e.preventDefault();
            triggerAjax();

            if (isMobile()) {
                $drawer.removeClass('hz_pf_drawer_open');
                $('body').css('overflow', '');
            }
        });

        // Reset Button
        $scope.find('.hz_pf_btn_reset').on('click', function (e) {
            e.preventDefault();
            $form[0].reset();

            // Reset Slider
            if ($priceSlider.length && typeof $.fn.slider !== 'undefined') {
                var $wrapper = $priceSlider.closest('.hz_pf_price_wrapper');
                var minPrice = parseFloat($wrapper.data('min')) || 0;
                var maxPrice = parseFloat($wrapper.data('max')) || 1000;
                $priceSlider.slider('values', [minPrice, maxPrice]);
                $wrapper.find('.hz_pf_price_min').val(minPrice);
                $wrapper.find('.hz_pf_price_max').val(maxPrice);
            }

            if (!isMobile()) {
                triggerAjax();
            }
        });
    };

    var initWidget = function () {
        if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') {
            return;
        }
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_premium_filter.default', WidgetPremiumFilterHandler);

        if (!elementorFrontend.isEditMode()) {
            $('.elementor-widget-handzom_premium_filter').each(function () {
                WidgetPremiumFilterHandler($(this), $);
            });
        }
    };

    $(window).on('elementor/frontend/init', initWidget);
    if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.hooks !== 'undefined') {
        initWidget();
    }

})(jQuery);
