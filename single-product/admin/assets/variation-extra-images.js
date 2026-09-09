(function ($) {
    'use strict';

    /**
     * Helper to update the hidden input field for a variation wrap
     */
    function updateVariationInput($wrap) {
        var ids = [];
        $wrap.find('.hzsp-vei-thumb').each(function () {
            var id = $(this).attr('data-id');
            if (id) {
                ids.push(id);
            }
        });

        var idString = ids.join(',');
        $wrap.find('.hzsp-vei-input').val(idString).trigger('change');

        // Toggle empty message
        if (ids.length > 0) {
            $wrap.find('.hzsp-vei-empty-msg').hide();
        } else {
            $wrap.find('.hzsp-vei-empty-msg').show();
        }

        // Notify WooCommerce variations manager that data has changed
        $('#woocommerce-product-data').trigger('woocommerce_variations_input_changed');
    }

    /**
     * Initialize drag and drop sortable on thumbnail lists
     */
    function initSortable($wrap) {
        var $thumbnails = $wrap.find('.hzsp-vei-thumbnails');
        if ($thumbnails.length && $.fn.sortable) {
            if ($thumbnails.hasClass('ui-sortable')) {
                $thumbnails.sortable('refresh');
            } else {
                $thumbnails.sortable({
                    items: '.hzsp-vei-thumb',
                    placeholder: 'hzsp-vei-thumb-placeholder',
                    cursor: 'grabbing',
                    scroll: false,
                    update: function () {
                        updateVariationInput($wrap);
                    }
                });
            }
        }
    }

    /**
     * Document ready and event delegation
     */
    $(function () {
        // Initialize sortable on any pre-rendered variation blocks
        $('.hzsp-variation-extra-images-wrap').each(function () {
            initSortable($(this));
        });

        // Initialize sortable when WooCommerce expands variations dynamically
        $(document).on('woocommerce_variations_loaded woocommerce_variations_added', function () {
            $('.hzsp-variation-extra-images-wrap').each(function () {
                initSortable($(this));
            });
        });

        // Open WP Media Uploader
        $(document).on('click', '.hzsp-vei-add-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var $wrap = $btn.closest('.hzsp-variation-extra-images-wrap');
            var $thumbnails = $wrap.find('.hzsp-vei-thumbnails');

            // Create media frame instance per click
            var frame = wp.media({
                title: 'Select Variation Extra Images',
                button: {
                    text: 'Add Selected Images'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');
                selection.each(function (attachment) {
                    var attr = attachment.toJSON();
                    var id = attr.id;
                    var thumbUrl = (attr.sizes && attr.sizes.thumbnail) ? attr.sizes.thumbnail.url : attr.url;

                    // Prevent duplicate insertion in same variation
                    if ($thumbnails.find('.hzsp-vei-thumb[data-id="' + id + '"]').length > 0) {
                        return;
                    }

                    var $thumbHtml = $(
                        '<div class="hzsp-vei-thumb" data-id="' + id + '">' +
                        '<img src="' + thumbUrl + '" alt="Extra Image">' +
                        '<button type="button" class="hzsp-vei-remove-btn" title="Remove image">&times;</button>' +
                        '</div>'
                    );

                    $thumbnails.append($thumbHtml);
                });

                initSortable($wrap);
                updateVariationInput($wrap);
            });

            frame.open();
        });

        // Remove image thumbnail
        $(document).on('click', '.hzsp-vei-remove-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $removeBtn = $(this);
            var $thumb = $removeBtn.closest('.hzsp-vei-thumb');
            var $wrap = $removeBtn.closest('.hzsp-variation-extra-images-wrap');

            $thumb.fadeOut(180, function () {
                $(this).remove();
                updateVariationInput($wrap);
            });
        });
    });

})(jQuery);
