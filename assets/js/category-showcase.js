/**
 * Handzom UI Kit: Category Showcase JS
 */
(function($) {
    'use strict';

    var HandzomCategoryShowcase = function($scope, $) {
        var $showcase = $scope.find('.hz-category-showcase');
        if (!$showcase.length) {
            return;
        }

        var $loadMoreBtn = $showcase.find('.hz-cs-load-more');
        var $grid = $showcase.find('.hz-cs-grid');
        
        var category = $showcase.data('category');
        var ppp = $showcase.data('ppp');
        var currentPage = parseInt($showcase.data('page'), 10) || 1;
        var maxPage = parseInt($showcase.data('max'), 10) || 1;
        
        var $sortSelect = $showcase.find('.hz-cs-sort-select');

        // Load More AJAX
        if ($loadMoreBtn.length) {
            $loadMoreBtn.on('click', function(e) {
                e.preventDefault();
                
                if (currentPage >= maxPage || $loadMoreBtn.hasClass('is-loading')) {
                    return;
                }
                
                $loadMoreBtn.addClass('is-loading');
                currentPage++;
                
                var sortVal = '';
                if ($sortOptions.length) {
                    sortVal = $sortOptions.filter(':checked').val();
                } else if ($sortSelect.length) {
                    sortVal = $sortSelect.val();
                }
                
                var selectedColors = [];
                $showcase.find('input[name="hz_color[]"]:checked').each(function() { selectedColors.push($(this).val()); });
                var selectedSizes = [];
                $showcase.find('input[name="hz_size[]"]:checked').each(function() { selectedSizes.push($(this).val()); });
                var selectedBrands = [];
                $showcase.find('input[name="hz_brand[]"]:checked').each(function() { selectedBrands.push($(this).val()); });
                
                var subcat = '';
                var $activeSubcat = $showcase.find('.hz-cs-subcat-btn.is-active');
                if ($activeSubcat.length) {
                    subcat = $activeSubcat.data('subcat');
                }
                
                var data = {
                    action: 'handzom_cs_load_more',
                    category: category,
                    subcat: subcat,
                    ppp: ppp,
                    page: currentPage,
                    sort: sortVal,
                    colors: selectedColors,
                    sizes: selectedSizes,
                    brands: selectedBrands,
                    image_size: $showcase.data('image-size') || 'woocommerce_single'
                };
                
                $.ajax({
                    url: handzom_cs_ajax.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $grid.append(response.data.html);
                            checkImageAspectRatios();
                            
                            if (currentPage >= response.data.max_page) {
                                $loadMoreBtn.hide();
                            }
                        }
                    },
                    complete: function() {
                        $loadMoreBtn.removeClass('is-loading');
                    }
                });
            });
        }
        
        // Sorting via standard select (if drawer is disabled)
        if ($sortSelect.length) {
            $sortSelect.on('change', function() {
                var sortVal = $(this).val();
                currentPage = 1;
                
                triggerAjaxLoad(sortVal);
            });
        }
        
        // --- Filter Drawer Logic ---
        var $drawer = $showcase.find('.hz-cs-drawer');
        var $overlay = $showcase.find('.hz-cs-drawer-overlay');
        var $toggleBtn = $showcase.find('.hz-cs-filter-toggle');
        var $closeBtn = $showcase.find('.hz-cs-drawer-close, .hz-cs-apply-btn');
        var $drawerSorts = $showcase.find('.hz-cs-drawer-sort');
        
        function openDrawer() {
            $drawer.addClass('is-open');
            $overlay.addClass('is-active');
            $('body').css('overflow', 'hidden');
        }
        
        function closeDrawer() {
            $drawer.removeClass('is-open');
            $overlay.removeClass('is-active');
            $('body').css('overflow', '');
        }
        
        if ($toggleBtn.length) {
            $toggleBtn.on('click', openDrawer);
        }
        
        if ($closeBtn.length) {
            $closeBtn.on('click', closeDrawer);
        }
        
        if ($overlay.length) {
            $overlay.on('click', closeDrawer);
        }
        
        // Accordions
        $showcase.find('.hz-cs-accordion-head').on('click', function() {
            var $parent = $(this).parent('.hz-cs-accordion');
            $parent.toggleClass('is-active');
            $parent.find('.hz-cs-accordion-body').stop().slideToggle(300);
        });
        
        // Sort Dropdown Toggle
        var $sortToggle = $showcase.find('.hz-cs-sort-toggle');
        var $sortWrapper = $showcase.find('.hz-cs-sort-wrapper');
        var $sortOptions = $showcase.find('.hz-cs-sort-option input');

        if ($sortToggle.length) {
            $sortToggle.on('click', function(e) {
                e.stopPropagation();
                $sortWrapper.toggleClass('is-active');
            });

            $(document).on('click', function() {
                $sortWrapper.removeClass('is-active');
            });
            
            $sortWrapper.on('click', function(e) {
                e.stopPropagation();
            });

            $sortOptions.on('change', function() {
                $sortWrapper.removeClass('is-active');
                currentPage = 1;
                triggerAjaxLoad();
            });
        }
        
        // Drawer Sorting & Filters Change
        $showcase.find('.hz-cs-drawer-filter').on('change', function() {
            currentPage = 1;
            triggerAjaxLoad();
        });
        
        // Reset Filters
        $showcase.find('.hz-cs-reset-btn').on('click', function(e) {
            e.preventDefault();
            
            // Uncheck all filters
            $showcase.find('.hz-cs-drawer-filter').prop('checked', false);
            
            currentPage = 1;
            triggerAjaxLoad();
        });



        // Shared AJAX Trigger for Sorts and Filters
        function triggerAjaxLoad() {
            var sortVal = '';
            if ($sortOptions.length) {
                sortVal = $sortOptions.filter(':checked').val();
            } else if ($sortSelect.length) {
                sortVal = $sortSelect.val();
            }
            
            var selectedColors = [];
            $showcase.find('input[name="hz_color[]"]:checked').each(function() {
                selectedColors.push($(this).val());
            });
            
            var selectedSizes = [];
            $showcase.find('input[name="hz_size[]"]:checked').each(function() {
                selectedSizes.push($(this).val());
            });

            var selectedBrands = [];
            $showcase.find('input[name="hz_brand[]"]:checked').each(function() {
                selectedBrands.push($(this).val());
            });
            
            var selectedSubcats = [];
            $showcase.find('input[name="hz_subcat[]"]:checked').each(function() {
                selectedSubcats.push($(this).val());
            });

            var data = {
                action: 'handzom_cs_load_more',
                category: category,
                subcats: selectedSubcats,
                ppp: ppp,
                page: 1,
                sort: sortVal,
                colors: selectedColors,
                sizes: selectedSizes,
                brands: selectedBrands,
                image_size: $showcase.data('image-size') || 'woocommerce_single',
                reset: true
            };
            
            $grid.css('opacity', '0.5');
            
            $.ajax({
                url: handzom_cs_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        if (response.data.html) {
                            $grid.html(response.data.html);
                            checkImageAspectRatios();
                        } else {
                            var emptyMsg = $showcase.data('empty-msg') || 'The selected product is not available now.';
                            $grid.html('<div class="hz-cs-no-products">' + emptyMsg + '</div>');
                        }
                        
                        maxPage = response.data.max_page;
                        $showcase.data('max', maxPage);
                        
                        if ($loadMoreBtn.length) {
                            if (1 >= maxPage) {
                                $loadMoreBtn.hide();
                            } else {
                                $loadMoreBtn.show();
                            }
                        }
                    }
                    $grid.css('opacity', '1');
                }
            });
        }
        // Image Carousel Logic
        $(document).on('mouseenter', '.hz-cs-card', function() {
            var $slider = $(this).find('.hz-cs-image-slider');
            var $slides = $slider.find('.hz-cs-slide');
            
            if ($slides.length > 1 && !$slider.data('interacted')) {
                // Fade in 2nd slide
                $slides.css({'opacity': '0', 'z-index': '1'});
                $slides.eq(1).css({'opacity': '1', 'z-index': '3'});
            }
        }).on('mouseleave', '.hz-cs-card', function() {
            var $slider = $(this).find('.hz-cs-image-slider');
            var $slides = $slider.find('.hz-cs-slide');
            
            if (!$slider.data('interacted') && $slides.length > 1) {
                // Revert to 1st slide
                $slides.css({'opacity': '0', 'z-index': '1'});
                $slides.eq(0).css({'opacity': '1', 'z-index': '3'});
            }
        });

        $(document).off('click', '.hz-cs-nav-btn').on('click', '.hz-cs-nav-btn', function(e) {
            e.preventDefault();
            e.stopPropagation(); // prevent clicking the card link
            
            var $wrap = $(this).closest('.hz-cs-card-img-wrap');
            var $slider = $wrap.find('.hz-cs-image-slider');
            var $slides = $slider.find('.hz-cs-slide');
            var total = $slides.length;
            
            if (total <= 1) return;
            
            if (!$slider.data('interacted')) {
                $slider.data('interacted', true);
                $slider.attr('data-current', 0); // we started at 0 natively
            }
            
            var current = parseInt($slider.attr('data-current'), 10) || 0;
            
            if ($(this).hasClass('hz-cs-next')) {
                current = (current + 1) % total;
            } else {
                current = (current - 1 + total) % total;
            }
            
            $slider.attr('data-current', current);
            
            // Apply crossfade
            $slides.css({'opacity': '0', 'z-index': '1'});
            $slides.eq(current).css({'opacity': '1', 'z-index': '3'});
        });
        // End Image Carousel Logic

        // Dynamic Image Aspect Ratio Helper
        function checkImageAspectRatios() {
            $showcase.find('.hz-cs-slide img').each(function() {
                var img = this;
                function applyRatio() {
                    if (img.naturalWidth && img.naturalHeight) {
                        var ratio = img.naturalHeight / img.naturalWidth;
                        if (ratio >= 1.2) {
                            $(img).addClass('hz-img-portrait');
                        } else if (ratio <= 0.85) {
                            $(img).addClass('hz-img-landscape');
                        } else {
                            $(img).addClass('hz-img-square');
                        }
                    }
                }
                if (img.complete) {
                    applyRatio();
                } else {
                    $(img).on('load', applyRatio);
                }
            });
        }
        checkImageAspectRatios();
    };

    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/handzom_category_showcase.default', HandzomCategoryShowcase);
    });

})(jQuery);
