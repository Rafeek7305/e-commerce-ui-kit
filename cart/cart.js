jQuery(document).ready(function($) {
    'use strict';

    var $body = $('body');

    // Handle Quantity Plus/Minus
    $body.on('click', '.hz-qty-btn', function() {
        var $this = $(this);
        var $input = $this.siblings('.hz-qty-input');
        var currentVal = parseInt($input.val(), 10);
        var isMinus = $this.hasClass('hz-qty-minus');
        var itemKey = $this.closest('.hz-cart-item').data('cart_item_key');

        if (isNaN(currentVal)) {
            currentVal = 1;
        }

        var newVal = isMinus ? currentVal - 1 : currentVal + 1;

        if (newVal < 1) {
            newVal = 1;
        }

        if (newVal !== currentVal) {
            $input.val(newVal);
            updateCartItem(itemKey, newVal);
        }
    });

    // Handle Remove Item
    $body.on('click', '.hz-cart-item-remove', function(e) {
        e.preventDefault();
        var itemKey = $(this).data('cart_item_key');
        if (itemKey) {
            removeItem(itemKey);
        }
    });

    // Handle Apply Coupon
    $body.on('click', '.hz-coupon-btn', function(e) {
        e.preventDefault();
        var couponCode = $('#hz-coupon-code').val().trim();
        if (couponCode) {
            applyCoupon(couponCode);
        } else {
            $('.hz-cart-coupon-message').removeClass('success').addClass('error').text('Please enter a coupon code.');
        }
    });

    function showLoading() {
        $('.handzom-cart-wrapper').addClass('is-loading');
    }

    function updateCartItem(itemKey, quantity) {
        showLoading();
        $.ajax({
            url: handzom_cart_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handzom_cart_update',
                nonce: handzom_cart_ajax.nonce,
                cart_item_key: itemKey,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    $('.handzom-cart-wrapper').html(response.data.html);
                    
                    // Trigger WooCommerce event so other elements (like mini cart) can update
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_cart_totals');
                }
            },
            complete: function() {
                $('.handzom-cart-wrapper').removeClass('is-loading');
            }
        });
    }

    // Handle Remove from Cart via Bookmark Icons (Toggle) - Using Capture Phase
    document.addEventListener('click', function(e) {
        var target = e.target.closest('.hz-cs-wishlist-icon.added, .hz-cs-wishlist-icon.hz-is-added, .hz-tp-wishlist-icon.added, .hz-tp-wishlist-icon.hz-is-added');
        if (target) {
            e.preventDefault();
            e.stopPropagation(); // Stop event immediately during capture phase so WooCommerce never sees it
            
            var $this = $(target);
            var productId = $this.data('product_id');

            if (productId) {
                $this.css('opacity', '0.5');
                $this.removeClass('added hz-is-added');

                var ajaxUrl = typeof handzom_cart_ajax !== 'undefined' ? handzom_cart_ajax.ajax_url : '/wp-admin/admin-ajax.php';

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'handzom_remove_from_cart',
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            $(document.body).trigger('wc_fragment_refresh');
                        }
                    },
                    complete: function() {
                        $this.css('opacity', '1');
                    }
                });
            }
        }
    }, true); // true = Use Capturing Phase

    function removeItem(itemKey) {
        showLoading();
        $.ajax({
            url: handzom_cart_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handzom_cart_remove',
                nonce: handzom_cart_ajax.nonce,
                cart_item_key: itemKey
            },
            success: function(response) {
                if (response.success) {
                    $('.handzom-cart-wrapper').html(response.data.html);
                    
                    // Trigger WooCommerce event so other elements (like mini cart) can update
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_cart_totals');
                }
            },
            complete: function() {
                $('.handzom-cart-wrapper').removeClass('is-loading');
            }
        });
    }

    function applyCoupon(couponCode) {
        showLoading();
        $('.hz-cart-coupon-message').removeClass('success error').text('');
        
        $.ajax({
            url: handzom_cart_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'handzom_cart_apply_coupon',
                nonce: handzom_cart_ajax.nonce,
                coupon_code: couponCode
            },
            success: function(response) {
                if (response.success) {
                    $('.handzom-cart-wrapper').html(response.data.html);
                    $('.hz-cart-coupon-message').removeClass('error').addClass('success').text(response.data.message);
                    
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_cart_totals');
                } else {
                    $('.hz-cart-coupon-message').removeClass('success').addClass('error').text(response.data.message);
                }
            },
            complete: function() {
                $('.handzom-cart-wrapper').removeClass('is-loading');
            }
        });
    }

    // Sync Cart State Across UI Elements globally
    function syncCartUI() {
        var $syncData = $('#hz-cart-sync-data');
        if ($syncData.length) {
            try {
                var cartItems = JSON.parse($syncData.text());
                // For all wishlist icons on the page
                $('.hz-cs-wishlist-icon, .hz-tp-wishlist-icon').each(function() {
                    var $icon = $(this);
                    var pid = $icon.data('product_id');
                    if (pid && cartItems.indexOf(parseInt(pid, 10)) !== -1) {
                        $icon.addClass('hz-is-added added');
                    } else {
                        $icon.removeClass('hz-is-added added');
                    }
                });

                // Update "ADD TO BAG" main buttons as well
                $('.add_to_cart_button').each(function() {
                    var $btn = $(this);
                    var pid = $btn.data('product_id');
                    if (pid && cartItems.indexOf(parseInt(pid, 10)) !== -1) {
                        $btn.addClass('added');
                    } else {
                        $btn.removeClass('added');
                    }
                });
            } catch (e) {
                // Ignore parsing errors
            }
        }
    }

    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', syncCartUI);

    // Initial sync on page load just in case fragments were cached
    syncCartUI();

});
