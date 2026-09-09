(function ($) {
    'use strict';

    $(document).ready(function () {

        // Coupon toggle handler
        $(document).on('click', '.hz-toggle-coupon-btn', function (e) {
            e.preventDefault();
            $('.hz-coupon-form-box').slideToggle(200);
        });

        // Apply Coupon AJAX
        $(document).on('click', '.hz-apply-coupon-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var couponCode = $('#hz_checkout_coupon_code').val().trim();
            var $msgBox = $('.hz-coupon-message');

            if (!couponCode) {
                $msgBox.removeClass('success').addClass('error').html('Please enter a coupon code.');
                return;
            }

            $btn.prop('disabled', true).text('...');
            $msgBox.removeClass('success error').html('');

            $.ajax({
                url: handzom_checkout_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'handzom_checkout_apply_coupon',
                    coupon_code: couponCode,
                    nonce: handzom_checkout_ajax.nonce
                },
                success: function (res) {
                    $btn.prop('disabled', false).text('APPLY');
                    if (res.success) {
                        $msgBox.addClass('success').html(res.data.message);
                        $('#hz_checkout_coupon_code').val('');
                        $('body').trigger('update_checkout');
                    } else {
                        $msgBox.addClass('error').html(res.data.message);
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).text('APPLY');
                    $msgBox.addClass('error').html('Error applying coupon. Please try again.');
                }
            });
        });

        // Remove Coupon Link
        $(document).on('click', '.hz-remove-coupon-link', function (e) {
            e.preventDefault();
            var couponCode = $(this).data('coupon');
            if (!couponCode) return;

            $.ajax({
                url: handzom_checkout_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'handzom_checkout_remove_coupon',
                    coupon_code: couponCode,
                    nonce: handzom_checkout_ajax.nonce
                },
                success: function (res) {
                    if (res.success) {
                        $('body').trigger('update_checkout');
                    }
                }
            });
        });

        // Toggle Ship to Different Address Visibility
        function updateShippingToggle() {
            var $checkbox = $('#ship-to-different-address-checkbox');
            if ($checkbox.length) {
                if ($checkbox.is(':checked')) {
                    $('.shipping_address').slideDown(200);
                } else {
                    $('.shipping_address').slideUp(200);
                }
            }
        }

        $(document).on('change', '#ship-to-different-address-checkbox', function () {
            updateShippingToggle();
        });

        // Initial check
        updateShippingToggle();

        // Scroll to top notices if checkout error occurs
        $(document).on('checkout_error', function () {
            var $notices = $('.hz-checkout-notices');
            if ($notices.length) {
                $('html, body').animate({
                    scrollTop: $notices.offset().top - 100
                }, 400);
            }
        });

        // =========================================================================
        // Force Select2 / selectWoo Dropdown to ALWAYS Open Below
        // =========================================================================
        function forceDropdownBelow(dropdownInstance) {
            if (!dropdownInstance || !dropdownInstance.$container || !dropdownInstance.$dropdown) {
                return;
            }

            var $container = dropdownInstance.$container;
            var $dropdown = dropdownInstance.$dropdown;
            var $openContainer = $dropdown.closest('.select2-container--open');
            if (!$openContainer.length) {
                $openContainer = $dropdown.parent();
            }

            // 1. Force classes to 'below'
            $dropdown.removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
            $container.removeClass('select2-container--above').addClass('select2-container--below');
            $openContainer.removeClass('select2-container--above').addClass('select2-container--below');

            // 2. Position below the container
            var offset = $container.offset();
            if (!offset) return;

            var containerHeight = $container.outerHeight(false);
            var containerWidth = $container.outerWidth(false);
            var targetTop = offset.top + containerHeight;

            $openContainer.css({
                top: targetTop + 'px',
                left: offset.left + 'px',
                width: containerWidth + 'px',
                position: 'absolute'
            });

            // 3. Smoothly scroll if dropdown is clipped at bottom of viewport
            var dropdownHeight = 240;
            var windowScrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();
            var windowBottom = windowScrollTop + windowHeight;
            var dropdownBottom = targetTop + dropdownHeight;

            if (dropdownBottom > windowBottom) {
                var neededScroll = dropdownBottom - windowBottom + 30;
                $('html, body').stop().animate({
                    scrollTop: windowScrollTop + neededScroll
                }, 200);
            }
        }

        function patchSelect2Prototype(s2Instance) {
            if (!s2Instance || !s2Instance.dropdown) return;
            var dropdownProto = s2Instance.dropdown.constructor ? s2Instance.dropdown.constructor.prototype : Object.getPrototypeOf(s2Instance.dropdown);
            if (dropdownProto && !dropdownProto._hzPatched) {
                dropdownProto._hzPatched = true;
                dropdownProto._positionDropdown = function () {
                    forceDropdownBelow(this);
                };
                if (typeof dropdownProto.position === 'function') {
                    dropdownProto.position = function () {
                        forceDropdownBelow(this);
                    };
                }
            }
            // Also override directly on instance in case prototype is masked
            s2Instance.dropdown._positionDropdown = function () {
                forceDropdownBelow(this);
            };
            if (typeof s2Instance.dropdown.position === 'function') {
                s2Instance.dropdown.position = function () {
                    forceDropdownBelow(this);
                };
            }
        }

        // Hook on document for select2/selectWoo open
        $(document).on('select2:open selectWoo:open', function (e) {
            var $select = $(e.target);
            var s2 = $select.data('select2') || $select.data('selectWoo');
            if (s2) {
                patchSelect2Prototype(s2);
                if (s2.dropdown) {
                    forceDropdownBelow(s2.dropdown);
                }
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 10);
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 50);
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 150);
            }
        });

        // Patch all existing and dynamically loaded country/state selects
        function patchAllSelects() {
            $('select.country_select, select.state_select, #billing_country, #shipping_country, #billing_state, #shipping_state').each(function () {
                var s2 = $(this).data('select2') || $(this).data('selectWoo');
                if (s2) {
                    patchSelect2Prototype(s2);
                }
            });
        }
        patchAllSelects();
        setTimeout(patchAllSelects, 300);
        setTimeout(patchAllSelects, 800);
        setTimeout(patchAllSelects, 2000);

        $(document.body).on('updated_checkout country_to_state_changed', function () {
            setTimeout(patchAllSelects, 100);
        });
    });

})(jQuery);
