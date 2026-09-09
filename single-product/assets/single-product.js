/**
 * HZ Single Product – Frontend JS
 * Namespace: window.HZSP
 * No globals polluted. Prefix: hzsp-
 */
(function ($) {
    'use strict';

    window.HZSP = window.HZSP || {};

    var data        = (typeof hzsp_data !== 'undefined') ? hzsp_data : {};
    var productId   = parseInt(data.product_id || 0, 10);
    var productType = data.product_type || 'simple';
    var variations  = data.variations || [];
    var checkoutUrl = data.checkout_url || '/checkout/';
    var autoOffset  = parseInt(data.header_offset_auto || 1, 10) === 1;

    /* ---- State ---- */
    var state = {
        selectedAttrs:  {},
        variationId:    0,
        quantity:       1,
        currentImgFull: '',
    };

    var initialDefaults = {
        priceHtml:   '',
        mainImgSrc:  '',
        mainImgFull: ''
    };

    /* =============================================================
       HEADER CLEARANCE AUTO-DETECT
       Finds whatever sticky/fixed header exists and measures its
       rendered height, then writes --hzsp-header-h to :root so the
       product page padding-top stays exactly below it.
    ============================================================= */
    function initHeaderClearance() {
        if (!autoOffset) return;

        function measure() {
            // Candidate selectors — tries common header patterns
            var selectors = [
                'header.site-header',
                'header#masthead',
                '#masthead',
                '.site-header',
                '#header',
                '.header',
                'header[role="banner"]',
                '.elementor-location-header header',
                '.elementor-section-wrap > section:first-child',
                'nav.navbar',
                '#wpadminbar' // admin bar only as last resort
            ];

            var maxH = 0;

            for (var i = 0; i < selectors.length; i++) {
                var el = document.querySelector(selectors[i]);
                if (!el) continue;
                var style = window.getComputedStyle(el);
                var pos   = style.position;
                // Only count fixed or sticky elements
                if (pos === 'fixed' || pos === 'sticky') {
                    var h = el.getBoundingClientRect().height;
                    if (h > maxH) maxH = h;
                    break; // use first match only
                }
            }

            // WordPress admin bar
            var adminBar = document.getElementById('wpadminbar');
            var adminBarH = (adminBar && window.getComputedStyle(adminBar).position === 'fixed')
                ? adminBar.getBoundingClientRect().height
                : 0;

            var total = maxH; // admin bar offset handled by WP itself via html margin-top

            document.documentElement.style.setProperty('--hzsp-header-h', total + 'px');
        }

        // Run immediately and on resize (debounced)
        measure();
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(measure, 120);
        });
    }

    /* =============================================================
       GALLERY – Desktop & Preloading
    ============================================================= */
    function preloadGalleryImages() {
        $('#hzsp-thumbs .hzsp-thumb').each(function () {
            var full = $(this).data('full');
            if (full) {
                var img = new Image();
                img.src = full;
            }
        });
    }

    function initGallery() {
        var $thumbs  = $('#hzsp-thumbs').find('.hzsp-thumb');
        var $mainImg = $('#hzsp-main-image');
        var $zoomBtn = $('#hzsp-zoom-btn');

        if (!$thumbs.length || !$mainImg.length) return;

        // Preload all thumbnail large images in the background so clicks are instantaneous
        preloadGalleryImages();

        // Bind gallery click/keyboard events
        initGalleryEvents();

        // Set initial state
        var $firstActive = $thumbs.filter('.hzsp-thumb--active').first();
        state.currentImgFull = $firstActive.length
            ? $firstActive.data('full')
            : ($zoomBtn.attr('data-full') || $mainImg.attr('src'));
    }

    /* =============================================================
       PREMIUM LIGHTBOX
       - Full-screen dark backdrop with blur
       - Scale-in animation
       - Prev/Next navigation through all gallery images
       - Keyboard: Esc closes, ← → navigate
       - Shows image counter
    ============================================================= */
    function initLightbox() {
        var $lb      = $('#hzsp-lightbox');
        var $lbImg   = $('#hzsp-lightbox-img');
        var $close   = $('#hzsp-lightbox-close');
        var $backdrop= $('#hzsp-lightbox-overlay');
        var $prev    = $('#hzsp-lb-prev');
        var $next    = $('#hzsp-lb-next');
        var $counter = $('#hzsp-lb-counter');

        // Build image list from thumbnails
        function getImages() {
            var imgs = [];
            $('#hzsp-thumbs .hzsp-thumb').each(function () {
                var full = $(this).data('full');
                if (full) imgs.push(full);
            });
            // Fallback: at least main image
            if (!imgs.length) {
                var src = $('#hzsp-main-image').attr('src');
                if (src) imgs.push(src);
            }
            return imgs;
        }

        var lbImages = [];
        var lbIndex  = 0;

        function showImage(idx) {
            if (!lbImages.length) return;
            idx = ((idx % lbImages.length) + lbImages.length) % lbImages.length; // wrap
            lbIndex = idx;

            // Instant swap
            $lbImg.attr('src', lbImages[idx]);
            $lbImg.css('opacity', 1);

            // Counter
            $counter.text((idx + 1) + ' / ' + lbImages.length);

            // Show/hide nav
            if (lbImages.length <= 1) {
                $prev.addClass('hzsp-lb-hidden');
                $next.addClass('hzsp-lb-hidden');
                $counter.hide();
            } else {
                $prev.removeClass('hzsp-lb-hidden');
                $next.removeClass('hzsp-lb-hidden');
                $counter.show();
            }
        }

        function openLightbox(startSrc) {
            lbImages = getImages();
            // Find starting index by src
            lbIndex = 0;
            for (var i = 0; i < lbImages.length; i++) {
                if (lbImages[i] === startSrc) { lbIndex = i; break; }
            }
            $lb.attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
            showImage(lbIndex);
        }

        function closeLightbox() {
            $lb.attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }

        // Open from zoom button
        $(document).on('click', '#hzsp-zoom-btn', function () {
            var src = $(this).attr('data-full') || state.currentImgFull || $('#hzsp-main-image').attr('src');
            openLightbox(src);
        });

        // Close
        $close.on('click', closeLightbox);
        $backdrop.on('click', closeLightbox);

        // Navigation
        $prev.on('click', function () { showImage(lbIndex - 1); });
        $next.on('click', function () { showImage(lbIndex + 1); });

        // Keyboard
        $(document).on('keydown.hzsp_lb', function (e) {
            if ($lb.attr('aria-hidden') === 'true') return;
            if (e.key === 'Escape' || e.key === 'Esc') closeLightbox();
            if (e.key === 'ArrowLeft')  showImage(lbIndex - 1);
            if (e.key === 'ArrowRight') showImage(lbIndex + 1);
        });
    }

    /* =============================================================
       MOBILE GALLERY – swipe dots
    ============================================================= */
    function initMobileGallery() {
        var $slides = $('#hzsp-mob-slides');
        if (!$slides.length) return;

        function updateActiveState(idx) {
            var $dots = $('#hzsp-mob-dots .hzsp-mob-dot');
            $dots.removeClass('hzsp-mob-dot--active');
            $dots.eq(idx).addClass('hzsp-mob-dot--active');

            var total = $dots.length || $('#hzsp-mob-slides .hzsp-mob-slide').length || 1;
            if (total > 1) {
                $('#hzsp-mob-counter').text((idx + 1) + ' / ' + total).show();
                $('#hzsp-mob-dots').show();
                $('#hzsp-mob-gallery').removeClass('hzsp-mob-no-carousel');
            } else {
                $('#hzsp-mob-counter').hide();
                $('#hzsp-mob-dots').hide();
                $('#hzsp-mob-gallery').addClass('hzsp-mob-no-carousel');
            }
        }

        // Event delegation for mobile dot click
        $(document).off('click.hzspMobDot').on('click.hzspMobDot', '#hzsp-mob-dots .hzsp-mob-dot', function () {
            var idx = parseInt($(this).data('slide'), 10);
            var slideW = $slides[0].offsetWidth || 1;
            $slides[0].scrollTo({ left: slideW * idx, behavior: 'smooth' });
            updateActiveState(idx);
        });

        // Scroll → update dots & counter dynamically
        var scrollTimer;
        $slides.off('scroll.hzspMob').on('scroll.hzspMob', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function () {
                var slideW = $slides[0].offsetWidth || 1;
                var idx = Math.round($slides[0].scrollLeft / slideW);
                updateActiveState(idx);
            }, 40);
        });
    }

    /* =============================================================
       VARIATIONS & ATTRIBUTES
    ============================================================= */
    /* =============================================================
       VARIATION & ATTRIBUTE ENGINE (WOOCOMMERCE COMPATIBLE)
    ============================================================= */
    function normalizeKey(key) {
        if (!key) return '';
        return key.toString()
                  .toLowerCase()
                  .trim()
                  .replace(/^attribute_/, '')
                  .replace(/^pa_/, '');
    }

    function normalizeVal(val) {
        if (val === undefined || val === null) return '';
        return val.toString().toLowerCase().trim();
    }

    function slugify(str) {
        if (!str) return '';
        return str.toString().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    /**
     * Checks whether user selected value or button label matches variation attribute value.
     * Robust against slug vs label vs taxonomy term variations.
     */
    function isAttrMatch(userVal, btnLabel, vAttrVal) {
        if (vAttrVal === undefined || vAttrVal === null || vAttrVal === '') {
            return true; // Wildcard / "Any..." in WooCommerce
        }
        var nV = normalizeVal(vAttrVal);
        var nU = normalizeVal(userVal);
        var nL = normalizeVal(btnLabel);

        if (nU !== '' && nU === nV) return true;
        if (nL !== '' && nL === nV) return true;

        var sV = slugify(vAttrVal);
        var sU = slugify(userVal);
        var sL = slugify(btnLabel);

        return (sU !== '' && sU === sV) || (sL !== '' && sL === sV);
    }

    /**
     * Find all variations matching the given selected attributes.
     */
    function getMatchingVariations(selectedAttrs, varsList) {
        if (!varsList || !varsList.length) return [];

        return varsList.filter(function (v) {
            var vAttrs = v.attributes || {};

            for (var sKey in selectedAttrs) {
                if (!selectedAttrs.hasOwnProperty(sKey)) continue;

                var sel = selectedAttrs[sKey];
                var userVal   = (typeof sel === 'object' ? sel.val : sel) || '';
                var userLabel = (typeof sel === 'object' ? sel.label : sel) || '';
                if (!userVal && !userLabel) continue;

                var cleanSKey = normalizeKey(sKey);

                var vVal = undefined;
                var foundAttr = false;

                for (var vKey in vAttrs) {
                    if (normalizeKey(vKey) === cleanSKey) {
                        vVal = vAttrs[vKey];
                        foundAttr = true;
                        break;
                    }
                }

                if (foundAttr && vVal !== '' && vVal !== null && vVal !== undefined) {
                    if (!isAttrMatch(userVal, userLabel, vVal)) {
                        return false;
                    }
                }
            }
            return true;
        });
    }

    /**
     * Dynamically updates option button availability (e.g. Size buttons for selected Color)
     * using real WooCommerce variation stock/availability data, and resets invalid selections.
     */
    function updateOptionStates() {
        if (productType !== 'variable' || !variations || !variations.length) return;

        var $attrRows = $('.hzsp-attr-row, .hzsp-mob-attr-row');
        if (!$attrRows.length) return;

        var processedKeys = {};

        $attrRows.each(function () {
            var rowAttr     = $(this).data('attribute');
            var cleanRowKey = normalizeKey(rowAttr);

            if (processedKeys[cleanRowKey]) return;
            processedKeys[cleanRowKey] = true;

            var otherSelectedAttrs = {};
            for (var k in state.selectedAttrs) {
                if (state.selectedAttrs.hasOwnProperty(k) && normalizeKey(k) !== cleanRowKey) {
                    otherSelectedAttrs[k] = state.selectedAttrs[k];
                }
            }

            var compatibleVars = getMatchingVariations(otherSelectedAttrs, variations);

            // Select ALL matching option buttons on desktop & mobile by clean attribute key
            var $buttons = $('.hzsp-option-btn, .hzsp-mob-option-btn').filter(function () {
                return normalizeKey($(this).data('attribute')) === cleanRowKey;
            });

            $buttons.each(function () {
                var $btn     = $(this);
                var btnVal   = $btn.data('value');
                var btnLabel = $btn.data('label') || btnVal;

                var isAvailable = false;

                for (var i = 0; i < compatibleVars.length; i++) {
                    var v = compatibleVars[i];
                    var vAttrs = v.attributes || {};

                    // Find attribute key in variation matching this row
                    var matchedAttrKey = null;
                    for (var vk in vAttrs) {
                        if (normalizeKey(vk) === cleanRowKey) {
                            matchedAttrKey = vk;
                            break;
                        }
                    }

                    var vAttrVal = matchedAttrKey ? vAttrs[matchedAttrKey] : '';

                    if (isAttrMatch(btnVal, btnLabel, vAttrVal)) {
                        // Check if in stock according to WooCommerce
                        if (v.is_in_stock !== false) {
                            isAvailable = true;
                            break;
                        }
                    }
                }

                if (isAvailable) {
                    $btn.removeClass('hzsp-disabled')
                        .prop('disabled', false)
                        .attr('style', function(i, s) { return (s || '').replace(/display\s*:\s*none\s*!important;?/gi, ''); })
                        .show();
                } else {
                    $btn.addClass('hzsp-disabled')
                        .prop('disabled', true)
                        .attr('style', 'display: none !important;')
                        .hide();

                    // Auto-reset invalid selection if currently selected
                    if ($btn.hasClass('hzsp-selected')) {
                        $btn.removeClass('hzsp-selected');
                        var slug = slugify(rowAttr);
                        $('#hzsp-val-' + slug).text('');
                        $('#hzsp-mob-val-' + slug).text('');
                        delete state.selectedAttrs[rowAttr];
                    }
                }
            });
        });
    }

    function hasSelectedColor() {
        for (var k in state.selectedAttrs) {
            if (state.selectedAttrs.hasOwnProperty(k)) {
                var cleanK = normalizeKey(k);
                if (cleanK === 'color' || cleanK === 'colour') {
                    return true;
                }
            }
        }
        return false;
    }

    function hasSelectedSize() {
        for (var k in state.selectedAttrs) {
            if (state.selectedAttrs.hasOwnProperty(k)) {
                var cleanK = normalizeKey(k);
                if (cleanK === 'size') {
                    return true;
                }
            }
        }
        return false;
    }

    function getSelectedAttrVal(attrName) {
        var cleanSearchKey = normalizeKey(attrName);
        for (var k in state.selectedAttrs) {
            if (state.selectedAttrs.hasOwnProperty(k)) {
                if (normalizeKey(k) === cleanSearchKey) {
                    var sel = state.selectedAttrs[k];
                    return (typeof sel === 'object' ? sel.val : sel) || '';
                }
            }
        }
        return null;
    }

    function selectOption(attr, val, label) {
        var cleanKey = normalizeKey(attr);

        // Check if user clicked an already-selected button (toggle off / deselect)
        var currentSelVal = getSelectedAttrVal(cleanKey);
        if (currentSelVal && isAttrMatch(val, label, currentSelVal)) {
            delete state.selectedAttrs[attr];
            for (var k in state.selectedAttrs) {
                if (state.selectedAttrs.hasOwnProperty(k) && normalizeKey(k) === cleanKey) {
                    delete state.selectedAttrs[k];
                }
            }

            $('.hzsp-option-btn, .hzsp-mob-option-btn').each(function () {
                if (normalizeKey($(this).data('attribute')) === cleanKey) {
                    $(this).removeClass('hzsp-selected');
                }
            });

            var slug = slugify(attr);
            $('#hzsp-val-' + slug).text('');
            $('#hzsp-mob-val-' + slug).text('');

            if (productType === 'variable') {
                onAttributeChange();
            }
            return;
        }

        // Cross-attribute compatibility test when switching attributes
        var isColorClick = (cleanKey === 'color' || cleanKey === 'colour');
        var isSizeClick  = (cleanKey === 'size');

        var newSelectedAttrs = {};
        for (var existingKey in state.selectedAttrs) {
            if (state.selectedAttrs.hasOwnProperty(existingKey)) {
                if (normalizeKey(existingKey) !== cleanKey) {
                    newSelectedAttrs[existingKey] = state.selectedAttrs[existingKey];
                }
            }
        }
        newSelectedAttrs[attr] = { val: val, label: label || val };

        var testVars = getMatchingVariations(newSelectedAttrs, variations);
        if (!testVars.length) {
            // If combination doesn't exist (e.g. Beige + M doesn't exist when switching to Beige), clear the incompatible size/color
            if (isColorClick) {
                for (var sK in state.selectedAttrs) {
                    if (state.selectedAttrs.hasOwnProperty(sK) && normalizeKey(sK) === 'size') {
                        delete state.selectedAttrs[sK];
                        var sSlug = slugify(sK);
                        $('#hzsp-val-' + sSlug).text('');
                        $('#hzsp-mob-val-' + sSlug).text('');
                    }
                }
                $('.hzsp-option-btn, .hzsp-mob-option-btn').each(function () {
                    if (normalizeKey($(this).data('attribute')) === 'size') {
                        $(this).removeClass('hzsp-selected');
                    }
                });
            } else if (isSizeClick) {
                for (var cK in state.selectedAttrs) {
                    if (state.selectedAttrs.hasOwnProperty(cK)) {
                        var cNorm = normalizeKey(cK);
                        if (cNorm === 'color' || cNorm === 'colour') {
                            delete state.selectedAttrs[cK];
                            var cSlug = slugify(cK);
                            $('#hzsp-val-' + cSlug).text('');
                            $('#hzsp-mob-val-' + cSlug).text('');
                        }
                    }
                }
                $('.hzsp-option-btn, .hzsp-mob-option-btn').each(function () {
                    var cNorm = normalizeKey($(this).data('attribute'));
                    if (cNorm === 'color' || cNorm === 'colour') {
                        $(this).removeClass('hzsp-selected');
                    }
                });
            }
        }

        // Highlight matching buttons on desktop & mobile by clean attribute key and value
        $('.hzsp-option-btn, .hzsp-mob-option-btn').each(function () {
            var $b = $(this);
            var bAttr = normalizeKey($b.data('attribute'));
            var bVal  = $b.data('value');
            var bLbl  = $b.data('label') || bVal;

            if (bAttr === cleanKey) {
                if (isAttrMatch(val, label, bVal)) {
                    $b.addClass('hzsp-selected');
                } else {
                    $b.removeClass('hzsp-selected');
                }
            }
        });

        // Update display text labels on desktop & mobile
        var slug = slugify(attr);
        var dispVal = (label || val).toString().toUpperCase();
        $('#hzsp-val-' + slug).text(dispVal);
        $('#hzsp-mob-val-' + slug).text(dispVal);

        state.selectedAttrs[attr] = { val: val, label: label || val };
        if (productType === 'variable') {
            onAttributeChange();
        }
    }

    function initVariations() {
        // Desktop & Mobile option buttons share identical state & logic
        $(document).off('click.hzspOpt').on('click.hzspOpt', '.hzsp-option-btn, .hzsp-mob-option-btn', function (e) {
            var $btn = $(this);
            if ($btn.hasClass('hzsp-disabled')) return;

            var attr  = $btn.data('attribute');
            var val   = $btn.data('value');
            var label = $btn.data('label') || val;

            selectOption(attr, val, label);
        });

        // WooCommerce default attributes handling
        initDefaultAttributes();
    }

    function initDefaultAttributes() {
        if (productType !== 'variable' || !data.default_attributes) return;

        var defaults = data.default_attributes;
        var hasDefaults = false;

        for (var attrKey in defaults) {
            if (!defaults.hasOwnProperty(attrKey)) continue;
            var defVal = defaults[attrKey];
            if (!defVal) continue;

            var cleanKey = normalizeKey(attrKey);

            var $btn = $('.hzsp-option-btn, .hzsp-mob-option-btn').filter(function () {
                var bAttr = normalizeKey($(this).data('attribute'));
                var bVal  = $(this).data('value');
                var bLbl  = $(this).data('label');
                return bAttr === cleanKey && isAttrMatch(bVal, bLbl, defVal);
            }).first();

            if ($btn.length) {
                $btn.trigger('click');
                hasDefaults = true;
            }
        }

        if (!hasDefaults) {
            state.selectedAttrs = {};
            state.variationId = 0;
            updateOptionStates();
        }
    }

    /* =============================================================
       SIZE GUIDE MODAL
    ============================================================= */
    function initSizeGuideModal() {
        $(document).on('click', '.hzsp-size-guide-trigger', function (e) {
            e.preventDefault();
            $('#hzsp-size-modal').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#hzsp-size-modal-close-btn, #hzsp-size-modal-close-overlay', function (e) {
            e.preventDefault();
            $('#hzsp-size-modal').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        });

        $(document).on('keydown.hzsp_sg', function (e) {
            if ($('#hzsp-size-modal').attr('aria-hidden') === 'false' && e.key === 'Escape') {
                $('#hzsp-size-modal').attr('aria-hidden', 'true');
                $('body').css('overflow', '');
            }
        });
    }

    /**
     * CENTRALIZED DYNAMIC PRICE UPDATER (DESKTOP & MOBILE)
     * Ensures exact single variation prices are displayed without min-max price ranges.
     */
    function updateVariationPrice(rawPriceHtml) {
        if (!rawPriceHtml) return;

        var singlePriceHtml = rawPriceHtml;

        // If rawPriceHtml contains a WooCommerce range (e.g. min &ndash; max), extract the first price
        if (singlePriceHtml.indexOf('&ndash;') !== -1 || singlePriceHtml.indexOf(' - ') !== -1 || singlePriceHtml.indexOf('–') !== -1) {
            var parts = singlePriceHtml.split(/&ndash;|-|–/);
            if (parts.length > 0 && parts[0].trim()) {
                singlePriceHtml = parts[0].trim();
            }
        }

        var desktopPriceHtml = singlePriceHtml;
        if (desktopPriceHtml.indexOf('hzsp-tax-note') === -1) {
            desktopPriceHtml += '<span class="hzsp-tax-note">(Inclusive of all taxes)</span>';
        }

        $('#hzsp-price-wrap').html(desktopPriceHtml);
        $('.hzsp-mob-price').html(singlePriceHtml);
    }

    function onAttributeChange() {
        if (productType !== 'variable') return;

        // 1. Filter option button states in BOTH directions (Color -> Sizes AND Size -> Colors)
        updateOptionStates();

        // 2. Get variations matching current selected attributes
        var matchingVars  = getMatchingVariations(state.selectedAttrs, variations);
        var selectedCount = Object.keys(state.selectedAttrs).length;

        var hasColor = hasSelectedColor();
        var hasSize  = hasSelectedSize();

        // CASE 0: ZERO SELECTION (Clear Selection / Initial Default Product State)
        if (selectedCount === 0) {
            state.variationId = 0;

            if (initialDefaults.priceHtml) {
                updateVariationPrice(initialDefaults.priceHtml);
            }
            if (initialDefaults.thumbsHtml) {
                $('#hzsp-thumbs').html(initialDefaults.thumbsHtml).show();
                $('.hzsp-desktop-layout > .hzsp-inner').removeClass('hzsp-no-thumbs');
                initGalleryEvents();
            }
            if (initialDefaults.mainImgSrc) {
                updateMainImageDisplay(initialDefaults.mainImgSrc);
            }
            if (initialDefaults.mobSlidesHtml) {
                $('#hzsp-mob-slides').html(initialDefaults.mobSlidesHtml);
                $('#hzsp-mob-gallery').removeClass('hzsp-mob-no-carousel');
                var totalInitialImgs = $(initialDefaults.mobSlidesHtml).filter('.hzsp-mob-slide').length || $(initialDefaults.mobSlidesHtml).find('.hzsp-mob-slide').length || 1;
                if (totalInitialImgs > 1) {
                    $('#hzsp-mob-counter').html('1 / ' + totalInitialImgs).show();
                    $('#hzsp-mob-dots').show();
                } else {
                    $('#hzsp-mob-counter, #hzsp-mob-dots').hide();
                }
            }
            $('#hzsp-stock-notice, #hzsp-mob-stock-notice').empty();
            clearMsg();
            disableButtons(false);
            $('#hzsp-reset-wrap, #hzsp-mob-reset-wrap').fadeOut(160);
            return;
        }

        // Toggle Reset Selection button visibility
        $('#hzsp-reset-wrap, #hzsp-mob-reset-wrap').fadeIn(160);

        // CASE 1: ONLY SIZE IS SELECTED (Color = null, Size = L)
        // -> Filter colors, but KEEP DEFAULT PRODUCT IMAGE, GALLERY & PRICE
        if (!hasColor && hasSize) {
            state.variationId = 0;

            if (initialDefaults.thumbsHtml) {
                $('#hzsp-thumbs').html(initialDefaults.thumbsHtml).show();
                $('.hzsp-desktop-layout > .hzsp-inner').removeClass('hzsp-no-thumbs');
                initGalleryEvents();
            }
            if (initialDefaults.mainImgSrc) {
                updateMainImageDisplay(initialDefaults.mainImgSrc);
            }
            if (initialDefaults.mobSlidesHtml) {
                $('#hzsp-mob-slides').html(initialDefaults.mobSlidesHtml);
                $('#hzsp-mob-gallery').removeClass('hzsp-mob-no-carousel');
            }
            if (initialDefaults.priceHtml) {
                updateVariationPrice(initialDefaults.priceHtml);
            }

            $('#hzsp-stock-notice, #hzsp-mob-stock-notice').empty();
            clearMsg();
            disableButtons(false);
            return;
        }

        // CASE 2 & 3: COLOR IS SELECTED (either Color alone OR Color + Size)
        if (hasColor) {
            // Update Gallery for Selected Color
            renderColorGallery(matchingVars);

            if (matchingVars.length > 0) {
                if (matchingVars.length === 1 && hasSize) {
                    // EXACT MATCH (Color + Size selected)
                    var matched = matchingVars[0];
                    state.variationId = matched.variation_id;

                    if (matched.price_html) {
                        updateVariationPrice(matched.price_html);
                    }

                    // Quantity Max
                    if (matched.max_qty && parseInt(matched.max_qty, 10) > 0) {
                        var maxVal = parseInt(matched.max_qty, 10);
                        $('.hzsp-qty-input').attr('max', maxVal);
                        var curVal = parseInt($('.hzsp-qty-input').first().val() || 1, 10);
                        if (curVal > maxVal && window.hzspChangeQty) {
                            window.hzspChangeQty(maxVal, true);
                        }
                    } else {
                        $('.hzsp-qty-input').attr('max', 99);
                    }

                    // Stock Notice
                    var $stockNotice = $('#hzsp-stock-notice, #hzsp-mob-stock-notice');
                    if (!matched.is_in_stock) {
                        $stockNotice.html('<span class="hzsp-out-of-stock">Out of Stock</span>');
                        showMsg('This variation is currently out of stock.', 'error');
                        disableButtons(true);
                    } else {
                        $stockNotice.empty();
                        clearMsg();
                        disableButtons(false);
                    }
                } else {
                    // ONLY COLOR SELECTED (or multiple sizes matching)
                    state.variationId = 0;

                    if (matchingVars[0].price_html) {
                        updateVariationPrice(matchingVars[0].price_html);
                    }

                    $('#hzsp-stock-notice, #hzsp-mob-stock-notice').empty();
                    clearMsg();
                    disableButtons(false);
                }
            } else {
                state.variationId = 0;
                $('#hzsp-stock-notice, #hzsp-mob-stock-notice').empty();
                clearMsg();
                disableButtons(false);
            }
        }
    }

    /**
     * DYNAMIC COLOR-SPECIFIC GALLERY SYSTEM
     * Filter thumbnail gallery and mobile carousel to show ONLY images for selected color/variation.
     */
    function renderColorGallery(matchingVars) {
        var $thumbsContainer = $('#hzsp-thumbs');
        var $mobSlides       = $('#hzsp-mob-slides');

        if (!matchingVars || !matchingVars.length) {
            if (initialDefaults.thumbsHtml) {
                $thumbsContainer.html(initialDefaults.thumbsHtml).show();
                initGalleryEvents();
            }
            if (initialDefaults.mainImgSrc) {
                updateMainImageDisplay(initialDefaults.mainImgSrc);
            }
            return;
        }

        // Collect all unique gallery images across matching variations
        var imagesList = [];
        var seenUrls   = {};

        matchingVars.forEach(function (v) {
            if (v.gallery && v.gallery.length) {
                v.gallery.forEach(function (gItem) {
                    var url = gItem.full_src;
                    if (url && !seenUrls[url]) {
                        seenUrls[url] = true;
                        imagesList.push({
                            full_src:  url,
                            thumb_src: gItem.thumb_src || url,
                            alt:       gItem.alt || ''
                        });
                    }
                });
            } else if (v.image && (v.image.full_src || v.image.src || v.image.url)) {
                var url = v.image.full_src || v.image.src || v.image.url;
                if (url && !seenUrls[url]) {
                    seenUrls[url] = true;
                    imagesList.push({
                        full_src:  url,
                        thumb_src: v.image.thumb_src || v.image.src || url,
                        alt:       v.image.alt || ''
                    });
                }
            }
        });

        // CASE A: Multiple valid gallery images for this color -> show thumbnails & mobile carousel
        if (imagesList.length > 1) {
            $('.hzsp-desktop-layout > .hzsp-inner').removeClass('hzsp-no-thumbs');
            var html = '';
            imagesList.forEach(function (img, idx) {
                var activeCls = (idx === 0) ? 'hzsp-thumb--active' : '';
                html += '<button type="button" class="hzsp-thumb ' + activeCls + '" data-full="' + img.full_src + '" aria-label="Gallery thumbnail ' + (idx + 1) + '">';
                html += '<img src="' + img.thumb_src + '" alt="' + (img.alt || '') + '" loading="lazy" />';
                html += '</button>';
            });

            $thumbsContainer.html(html).show();
            initGalleryEvents();

            // Render Mobile Slides & Dots
            if ($mobSlides.length) {
                var mobHtml = '';
                var mobDotsHtml = '';
                imagesList.forEach(function (img, idx) {
                    mobHtml += '<div class="hzsp-mob-slide ' + (idx === 0 ? 'hzsp-mob-slide--active' : '') + '" data-index="' + idx + '">';
                    mobHtml += '<img src="' + img.full_src + '" alt="' + (img.alt || '') + '" loading="' + (idx === 0 ? 'eager' : 'lazy') + '" />';
                    mobHtml += '</div>';

                    mobDotsHtml += '<button type="button" class="hzsp-mob-dot ' + (idx === 0 ? 'hzsp-mob-dot--active' : '') + '" data-slide="' + idx + '" aria-label="Go to image ' + (idx + 1) + '"></button>';
                });
                $mobSlides.html(mobHtml);
                if ($mobSlides[0]) $mobSlides[0].scrollTo({ left: 0, behavior: 'instant' });

                $('#hzsp-mob-counter').html('1 / ' + imagesList.length).show();
                $('#hzsp-mob-dots').html(mobDotsHtml).show();
                $('#hzsp-mob-gallery').removeClass('hzsp-mob-no-carousel');
            }

            updateMainImageDisplay(imagesList[0].full_src);

        } else if (imagesList.length === 1) {
            // CASE B: Only 1 image -> show main image, HIDE desktop thumbs AND mobile carousel controls
            $('.hzsp-desktop-layout > .hzsp-inner').addClass('hzsp-no-thumbs');
            $thumbsContainer.hide().empty();
            updateMainImageDisplay(imagesList[0].full_src);

            if ($mobSlides.length) {
                var mobHtml = '<div class="hzsp-mob-slide hzsp-mob-slide--active" data-index="0">';
                mobHtml += '<img src="' + imagesList[0].full_src + '" alt="' + (imagesList[0].alt || '') + '" loading="eager" />';
                mobHtml += '</div>';
                $mobSlides.html(mobHtml);
                if ($mobSlides[0]) $mobSlides[0].scrollTo({ left: 0, behavior: 'instant' });

                $('#hzsp-mob-counter').hide().empty();
                $('#hzsp-mob-dots').hide().empty();
                $('#hzsp-mob-gallery').addClass('hzsp-mob-no-carousel');
            }

        } else {
            // CASE C: 0 images for variation -> fallback to parent product main image
            $('.hzsp-desktop-layout > .hzsp-inner').addClass('hzsp-no-thumbs');
            $thumbsContainer.hide().empty();
            if (initialDefaults.mainImgSrc) {
                updateMainImageDisplay(initialDefaults.mainImgSrc);
            }
            if ($mobSlides.length && initialDefaults.mainImgSrc) {
                var mobHtml = '<div class="hzsp-mob-slide hzsp-mob-slide--active" data-index="0">';
                mobHtml += '<img src="' + initialDefaults.mainImgSrc + '" alt="" loading="eager" />';
                mobHtml += '</div>';
                $mobSlides.html(mobHtml);
                $('#hzsp-mob-counter').hide().empty();
                $('#hzsp-mob-dots').hide().empty();
                $('#hzsp-mob-gallery').addClass('hzsp-mob-no-carousel');
            }
        }
    }

    function initGalleryEvents() {
        preloadGalleryImages();

        // Use delegated click & keydown on #hzsp-thumbs .hzsp-thumb for instant response
        $(document).off('click.hzspG keydown.hzspG', '#hzsp-thumbs .hzsp-thumb');
        $(document).on('click.hzspG keydown.hzspG', '#hzsp-thumbs .hzsp-thumb', function (e) {
            if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) return;
            e.preventDefault();
            var $t = $(this);
            var full = $t.data('full');
            if (!full || $t.hasClass('hzsp-thumb--active')) return;

            // Instant active state switch
            $('#hzsp-thumbs .hzsp-thumb').removeClass('hzsp-thumb--active');
            $t.addClass('hzsp-thumb--active');

            // Instantly update main image with zero delay
            updateMainImageDisplay(full);
        });

        // Preload image on hover or focus for zero network wait
        $(document).off('mouseenter.hzspG focus.hzspG', '#hzsp-thumbs .hzsp-thumb');
        $(document).on('mouseenter.hzspG focus.hzspG', '#hzsp-thumbs .hzsp-thumb', function () {
            var full = $(this).data('full');
            if (full) {
                var img = new Image();
                img.src = full;
            }
        });
    }

    function updateMainImageDisplay(newFull) {
        if (!newFull) return;

        // Desktop Gallery Update
        var $mainImg = $('#hzsp-main-image');
        var $zoomBtn = $('#hzsp-zoom-btn');
        var $thumbs  = $('#hzsp-thumbs .hzsp-thumb');

        if ($mainImg.length) {
            var $matchedThumb = $thumbs.filter(function () {
                var tf = $(this).data('full');
                return tf && (tf === newFull || tf.split('?')[0] === newFull.split('?')[0]);
            });

            if ($matchedThumb.length) {
                $thumbs.removeClass('hzsp-thumb--active');
                $matchedThumb.addClass('hzsp-thumb--active');
            } else {
                $thumbs.removeClass('hzsp-thumb--active');
            }

            // INSTANT UPDATE: Immediately change main image src with zero timeout or opacity delay
            $mainImg.removeAttr('srcset');
            $mainImg.attr('src', newFull);
            $mainImg.css('opacity', '1');

            state.currentImgFull = newFull;
            $zoomBtn.attr('data-full', newFull).data('full', newFull);
        }

        // Mobile Gallery Update
        var $slides = $('#hzsp-mob-slides');
        if ($slides.length) {
            var $slideImgs = $slides.find('img');
            var foundSlideIdx = -1;
            $slideImgs.each(function (idx) {
                var sSrc = $(this).attr('src');
                if (sSrc && (sSrc === newFull || sSrc.split('?')[0] === newFull.split('?')[0])) {
                    foundSlideIdx = idx;
                    return false;
                }
            });

            if (foundSlideIdx >= 0) {
                var slideW = $slides[0].offsetWidth || 1;
                $slides[0].scrollTo({ left: slideW * foundSlideIdx, behavior: 'smooth' });
            } else {
                var $firstImg = $slideImgs.first();
                if ($firstImg.length) {
                    $firstImg.attr('src', newFull);
                    $slides[0].scrollTo({ left: 0, behavior: 'smooth' });
                }
            }
        }
    }

    /* =============================================================
       RESET SELECTION
    ============================================================= */
    function initResetButton() {
        $(document).on('click', '#hzsp-reset-btn, #hzsp-mob-reset-btn', function (e) {
            e.preventDefault();

            // 1. Clear selected attributes state
            state.selectedAttrs = {};
            state.variationId = 0;

            // 2. Remove selection class & clear labels & re-enable and show option buttons
            $('.hzsp-option-btn, .hzsp-mob-option-btn')
                .removeClass('hzsp-selected hzsp-disabled')
                .prop('disabled', false)
                .attr('style', function(i, s) { return (s || '').replace(/display\s*:\s*none\s*!important;?/gi, ''); })
                .show();

            $('.hzsp-attr-selected-val, .hzsp-mob-attr-val').text('');

            // 3. Reset price to original default single product price (never price range)
            if (initialDefaults.priceHtml) {
                updateVariationPrice(initialDefaults.priceHtml);
            }

            // 4. Restore original parent product thumbnail gallery & mobile slides
            if (initialDefaults.thumbsHtml && initialDefaults.thumbsHtml.trim() !== '') {
                $('.hzsp-desktop-layout > .hzsp-inner').removeClass('hzsp-no-thumbs');
                $('#hzsp-thumbs').html(initialDefaults.thumbsHtml).show();
                initGalleryEvents();
            } else {
                $('.hzsp-desktop-layout > .hzsp-inner').addClass('hzsp-no-thumbs');
                $('#hzsp-thumbs').hide();
            }
            if (initialDefaults.mobSlidesHtml) {
                $('#hzsp-mob-slides').html(initialDefaults.mobSlidesHtml);
                if ($('#hzsp-mob-slides')[0]) $('#hzsp-mob-slides')[0].scrollTo({ left: 0, behavior: 'instant' });
                var defaultCount = $('#hzsp-mob-slides .hzsp-mob-slide').length;
                if (defaultCount > 1) {
                    var defaultDotsHtml = '';
                    for (var d = 0; d < defaultCount; d++) {
                        defaultDotsHtml += '<button type="button" class="hzsp-mob-dot ' + (d === 0 ? 'hzsp-mob-dot--active' : '') + '" data-slide="' + d + '" aria-label="Go to image ' + (d + 1) + '"></button>';
                    }
                    $('#hzsp-mob-counter').html('1 / ' + defaultCount).show();
                    $('#hzsp-mob-dots').html(defaultDotsHtml).show();
                    $('#hzsp-mob-gallery').removeClass('hzsp-mob-no-carousel');
                } else {
                    $('#hzsp-mob-counter').hide().empty();
                    $('#hzsp-mob-dots').hide().empty();
                    $('#hzsp-mob-gallery').addClass('hzsp-mob-no-carousel');
                }
            }

            // 5. Reset main image to original featured product image
            if (initialDefaults.mainImgSrc) {
                updateMainImageDisplay(initialDefaults.mainImgSrc);
            }

            // 6. Clear stock notice & re-enable buttons & update option states
            $('#hzsp-stock-notice, #hzsp-mob-stock-notice').empty();
            clearMsg();
            disableButtons(false);
            updateOptionStates();

            // 7. Hide reset button wrapper
            $('#hzsp-reset-wrap, #hzsp-mob-reset-wrap').fadeOut(160);
        });
    }

    /* =============================================================
       QUANTITY
    ============================================================= */
    function initQty() {
        var isUpdating = false;

        window.hzspChangeQty = function (delta, isDirect) {
            if (isUpdating) return;
            isUpdating = true;

            var $inputs = $('.hzsp-qty-input');
            var firstInput = $inputs.first();
            var currentVal = parseInt(firstInput.val(), 10);
            if (isNaN(currentVal) || currentVal < 1) currentVal = 1;

            var minVal = parseInt(firstInput.attr('min') || 1, 10);
            if (isNaN(minVal) || minVal < 1) minVal = 1;

            var maxRaw = firstInput.attr('max');
            var maxVal = (maxRaw && parseInt(maxRaw, 10) > 0) ? parseInt(maxRaw, 10) : 99;

            var newVal;
            if (isDirect) {
                newVal = parseInt(delta, 10);
            } else {
                newVal = currentVal + delta;
            }

            if (isNaN(newVal) || newVal < minVal) newVal = minVal;
            if (newVal > maxVal) newVal = maxVal;

            $inputs.val(newVal).prop('value', newVal).attr('value', newVal);
            state.quantity = newVal;

            // Trigger WooCommerce native change event safely
            $inputs.trigger('change');

            isUpdating = false;
        };

        // Ensure no duplicate click bindings
        $(document).off('click.hzspQty', '.hzsp-qty-minus, .hzsp-qty-plus');

        $(document).on('click.hzspQty', '.hzsp-qty-minus', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.hzspChangeQty(-1, false);
        });

        $(document).on('click.hzspQty', '.hzsp-qty-plus', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.hzspChangeQty(1, false);
        });

        $(document).off('input.hzspQty change.hzspQty keyup.hzspQty blur.hzspQty', '.hzsp-qty-input');
        $(document).on('input.hzspQty change.hzspQty keyup.hzspQty blur.hzspQty', '.hzsp-qty-input', function (e) {
            if (isUpdating) return;

            var val = parseInt($(this).val(), 10);
            var minVal = parseInt($(this).attr('min') || 1, 10);
            if (isNaN(minVal) || minVal < 1) minVal = 1;

            var maxRaw = $(this).attr('max');
            var maxVal = (maxRaw && parseInt(maxRaw, 10) > 0) ? parseInt(maxRaw, 10) : 99;

            if (isNaN(val) || val < minVal) val = minVal;
            if (val > maxVal) val = maxVal;

            state.quantity = val;
            $('.hzsp-qty-input').not(this).val(val).prop('value', val).attr('value', val);
        });
    }

    /* =============================================================
       ADD TO CART
    ============================================================= */
    function initCartButtons() {
        // Desktop ATC
        $(document).on('click', '#hzsp-atc-btn', function (e) {
            e.preventDefault();
            doAddToCart(false);
        });

        // Mobile ATC
        $(document).on('click', '.hzsp-mob-btn-group .hzsp-btn--atc', function (e) {
            e.preventDefault();
            doAddToCart(false);
        });

        // Desktop Buy Now
        $(document).on('click', '#hzsp-bn-btn', function (e) {
            e.preventDefault();
            doAddToCart(true);
        });

        // Mobile Buy Now
        $(document).on('click', '.hzsp-mob-btn-group .hzsp-btn--bn', function (e) {
            e.preventDefault();
            doAddToCart(true);
        });
    }

    function hasAttrRow(attrType) {
        var exists = false;
        $('.hzsp-attr-row, .hzsp-mob-attr-row').each(function () {
            var aName = $(this).data('attribute');
            if (aName) {
                var clean = normalizeKey(aName);
                if (attrType === 'color' && (clean === 'color' || clean === 'colour')) {
                    exists = true;
                    return false;
                }
                if (attrType === 'size' && clean === 'size') {
                    exists = true;
                    return false;
                }
            }
        });
        return exists;
    }

    function doAddToCart(buyNow) {
        var attributesToSend = state.selectedAttrs;

        var hasColorAttr = hasAttrRow('color');
        var hasSizeAttr  = hasAttrRow('size');

        var colorSelected = hasSelectedColor();
        var sizeSelected  = hasSelectedSize();

        if (productType === 'variable') {
            if (hasColorAttr && hasSizeAttr) {
                if (!colorSelected && !sizeSelected) {
                    showMsg('Please select a color and size.', 'error');
                    return;
                }
                if (!colorSelected) {
                    showMsg('Please select a color.', 'error');
                    return;
                }
                if (!sizeSelected) {
                    showMsg('Please select a size.', 'error');
                    return;
                }
            } else if (hasColorAttr && !hasSizeAttr) {
                if (!colorSelected) {
                    showMsg('Please select a color.', 'error');
                    return;
                }
            } else if (hasSizeAttr && !hasColorAttr) {
                if (!sizeSelected) {
                    showMsg('Please select a size.', 'error');
                    return;
                }
            } else {
                var totalAttrRows = $('.hzsp-attr-row').length || $('.hzsp-mob-attr-row').length;
                var selectedCount = Object.keys(state.selectedAttrs).length;
                if (selectedCount < totalAttrRows) {
                    showMsg('Please select all product options before adding to cart.', 'error');
                    return;
                }
            }

            if (!state.variationId || state.variationId === 0) {
                showMsg('The selected color and size combination is unavailable.', 'error');
                return;
            }

        } else {
            // Simple product validation for custom option selectors
            var finalAttrs = $.extend({}, state.selectedAttrs);

            var attrGroups = {};
            $('.hzsp-attr-row, .hzsp-mob-attr-row').each(function () {
                var $row = $(this);
                var attrName = $row.data('attribute');
                if (!attrName) return;
                var cleanK = normalizeKey(attrName);
                if (!attrGroups[cleanK]) {
                    attrGroups[cleanK] = {
                        attrName: attrName,
                        buttons: []
                    };
                }
                $row.find('.hzsp-option-btn, .hzsp-mob-option-btn').each(function () {
                    var $btn = $(this);
                    var bVal = $btn.data('value');
                    var bLbl = $btn.data('label') || bVal;
                    var exists = false;
                    for (var i = 0; i < attrGroups[cleanK].buttons.length; i++) {
                        if (attrGroups[cleanK].buttons[i].val === bVal) {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists) {
                        attrGroups[cleanK].buttons.push({ val: bVal, label: bLbl });
                    }
                });
            });

            for (var k in attrGroups) {
                if (attrGroups.hasOwnProperty(k)) {
                    var grp = attrGroups[k];
                    var isSelected = false;
                    for (var sK in finalAttrs) {
                        if (finalAttrs.hasOwnProperty(sK) && normalizeKey(sK) === k) {
                            isSelected = true;
                            break;
                        }
                    }

                    if (!isSelected) {
                        if (grp.buttons.length === 1) {
                            // Single-choice row (e.g. Color: Beige) -> Auto include
                            finalAttrs[grp.attrName] = { val: grp.buttons[0].val, label: grp.buttons[0].label };
                            if (k === 'color' || k === 'colour') colorSelected = true;
                            if (k === 'size') sizeSelected = true;
                        }
                    }
                }
            }

            if (hasColorAttr && hasSizeAttr) {
                if (!colorSelected && !sizeSelected) {
                    showMsg('Please select a color and size.', 'error');
                    return;
                }
                if (!colorSelected) {
                    showMsg('Please select a color.', 'error');
                    return;
                }
                if (!sizeSelected) {
                    showMsg('Please select a size.', 'error');
                    return;
                }
            } else if (hasSizeAttr && !hasColorAttr) {
                if (!sizeSelected) {
                    showMsg('Please select a size.', 'error');
                    return;
                }
            } else if (hasColorAttr && !hasSizeAttr) {
                if (!colorSelected) {
                    showMsg('Please select a color.', 'error');
                    return;
                }
            }

            attributesToSend = finalAttrs;
        }

        var qtyInput = $('.hzsp-qty-input').first();
        var qty = qtyInput.length ? parseInt(qtyInput.val(), 10) : state.quantity;
        if (isNaN(qty) || qty < 1) qty = 1;

        setBtnLoading(true);

        $.ajax({
            url:  data.ajax_url,
            type: 'POST',
            data: {
                action:       'hzsp_add_to_cart',
                nonce:        data.nonce,
                product_id:   productId,
                variation_id: (productType === 'variable' ? state.variationId : 0),
                quantity:     qty,
                attributes:   attributesToSend,
            },
            success: function (res) {
                setBtnLoading(false);
                if (res.success) {
                    if (buyNow) {
                        window.location.href = checkoutUrl;
                    } else {
                        showMsg('✓ ' + res.data.message, 'success');

                        // Instantly update header cart badges across common WooCommerce theme selectors
                        if (typeof res.data.cart_count !== 'undefined') {
                            var cartSelectors = [
                                '.cart-contents-count',
                                '.cart-count',
                                '.header-cart-count',
                                '.shopping-cart-count',
                                '.mini-cart-count',
                                '.count',
                                '.wc-cart-count',
                                '.cart-quantity',
                                '.cart-total-badge',
                                '.ast-cart-contents-count',
                                '.elementor-button-icon-qty',
                                '.cart-items-count',
                                '.handzom-cart-count',
                                '[data-cart-count]'
                            ].join(',');

                            $(cartSelectors).text(res.data.cart_count).html(res.data.cart_count);
                        }

                        // Replace cart fragments in DOM if available
                        if (res.data.fragments) {
                            $.each(res.data.fragments, function (key, value) {
                                $(key).replaceWith(value);
                            });
                        }

                        // Trigger WooCommerce cart events
                        $(document.body).trigger('added_to_cart', [res.data.fragments, res.data.cart_hash, $('#hzsp-atc-btn')]);
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    showMsg(res.data.message || 'Error adding to cart.', 'error');
                }
            },
            error: function () {
                setBtnLoading(false);
                showMsg('Server error. Please try again.', 'error');
            }
        });
    }

    function setBtnLoading(loading) {
        if (loading) {
            $('.hzsp-btn--atc .hzsp-btn-text').hide();
            $('.hzsp-btn--atc .hzsp-btn-spinner').css('display', 'inline-flex');
            $('.hzsp-btn, #hzsp-atc-btn, #hzsp-bn-btn, .hzsp-mob-btn-group .hzsp-btn').addClass('hzsp-loading').prop('disabled', true);
        } else {
            $('.hzsp-btn--atc .hzsp-btn-text').show();
            $('.hzsp-btn--atc .hzsp-btn-spinner').hide();
            $('.hzsp-btn, #hzsp-atc-btn, #hzsp-bn-btn, .hzsp-mob-btn-group .hzsp-btn').removeClass('hzsp-loading').prop('disabled', false);
        }
    }

    function disableButtons(state_) {
        $('#hzsp-atc-btn, #hzsp-bn-btn, .hzsp-mob-btn-group .hzsp-btn').prop('disabled', state_);
    }

    /* =============================================================
       PREMIUM FLOATING TOAST NOTIFICATION
    ============================================================= */
    var toastTimer = null;

    function showMsg(msg, type) {
        var isSuccess = type === 'success';
        var cleanMsg  = msg.replace(/^✓\s*/, '');
        var cartUrl   = (data && data.cart_url) ? data.cart_url : '/cart/';

        // Ensure toast container exists
        var $container = $('#hzsp-toast-container');
        if (!$container.length) {
            $container = $('<div id="hzsp-toast-container" class="hzsp-toast-container"></div>').appendTo('body');
        }

        var iconSvg = isSuccess
            ? '<svg class="hzsp-toast-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
            : '<svg class="hzsp-toast-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

        var cartBtnHtml = isSuccess ? '<a href="' + cartUrl + '" class="hzsp-toast-cart-link">View Cart &rarr;</a>' : '';

        var $existingToast = $container.find('.hzsp-toast-card');

        if ($existingToast.length) {
            // Reuse and update active toast without stacking multiple elements
            if (toastTimer) clearTimeout(toastTimer);
            $existingToast.removeClass('hzsp-toast-card--success hzsp-toast-card--error hzsp-toast-hiding')
                           .addClass('hzsp-toast-card--' + (isSuccess ? 'success' : 'error'));

            $existingToast.find('.hzsp-toast-badge').html(iconSvg);
            $existingToast.find('.hzsp-toast-text').text(cleanMsg);

            var $link = $existingToast.find('.hzsp-toast-cart-link');
            if (isSuccess) {
                if ($link.length) {
                    $link.attr('href', cartUrl);
                } else {
                    $existingToast.find('.hzsp-toast-body').append(cartBtnHtml);
                }
            } else {
                $link.remove();
            }

            // Re-trigger icon animation
            var $icon = $existingToast.find('.hzsp-toast-svg');
            $icon.css('animation', 'none');
            setTimeout(function() { $icon.css('animation', ''); }, 10);
        } else {
            // Create new toast card
            var html = '<div class="hzsp-toast-card hzsp-toast-card--' + (isSuccess ? 'success' : 'error') + '">' +
                           '<div class="hzsp-toast-body">' +
                               '<span class="hzsp-toast-badge">' + iconSvg + '</span>' +
                               '<span class="hzsp-toast-text">' + cleanMsg + '</span>' +
                               cartBtnHtml +
                           '</div>' +
                           '<button type="button" class="hzsp-toast-close" aria-label="Close notification">&times;</button>' +
                       '</div>';

            $container.html(html);
        }

        // Set auto-hide timer (4 seconds)
        toastTimer = setTimeout(function () {
            dismissToast();
        }, 4000);
    }

    function dismissToast() {
        if (toastTimer) clearTimeout(toastTimer);
        var $toast = $('#hzsp-toast-container .hzsp-toast-card');
        if ($toast.length && !$toast.hasClass('hzsp-toast-hiding')) {
            $toast.addClass('hzsp-toast-hiding');
            setTimeout(function () {
                $('#hzsp-toast-container').empty();
            }, 280);
        }
    }

    function clearMsg() {
        dismissToast();
    }

    // Dismiss toast on close button click
    $(document).on('click', '.hzsp-toast-close', function (e) {
        e.preventDefault();
        dismissToast();
    });

    /* =============================================================
       DESKTOP ACCORDIONS
    ============================================================= */
    function initAccordions() {
        $(document).on('click', '.hzsp-acc-trigger', function () {
            var $btn   = $(this);
            var $panel = $('#' + $btn.attr('aria-controls'));
            var open   = $btn.attr('aria-expanded') === 'true';

            // Close all
            $('.hzsp-acc-trigger').attr('aria-expanded', 'false');
            $('.hzsp-acc-panel').attr('hidden', '');

            if (!open) {
                $btn.attr('aria-expanded', 'true');
                $panel.removeAttr('hidden');
            }
        });
    }

    /* =============================================================
       MOBILE DRAWERS
    ============================================================= */
    function initMobileDrawers() {
        // Open drawer
        $(document).on('click', '.hzsp-mob-acc-trigger', function () {
            var key = $(this).data('drawer');
            openDrawer(key);
        });

        // Close via overlay or X
        $(document).on('click', '.hzsp-mob-drawer-overlay, .hzsp-mob-drawer-close', function () {
            var key = $(this).data('close');
            closeDrawer(key);
        });

        // ESC key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('.hzsp-mob-drawer').attr('aria-hidden', 'true');
                $('body').css('overflow', '');
            }
        });
    }

    function openDrawer(key) {
        $('#hzsp-drawer-' + key).attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
    }

    function closeDrawer(key) {
        $('#hzsp-drawer-' + key).attr('aria-hidden', 'true');
        $('body').css('overflow', '');
    }

    /* =============================================================
       INIT
    ============================================================= */
    $(document).ready(function () {
        initialDefaults.priceHtml     = $('#hzsp-price-wrap').html();
        initialDefaults.mainImgSrc    = $('#hzsp-main-image').attr('src');
        initialDefaults.mainImgFull   = $('#hzsp-zoom-btn').attr('data-full') || initialDefaults.mainImgSrc;
        initialDefaults.thumbsHtml    = $('#hzsp-thumbs').html();
        initialDefaults.mobSlidesHtml = $('#hzsp-mob-slides').html();

        initHeaderClearance(); // run first so layout shift is minimal
        initGallery();
        initLightbox();
        initMobileGallery();
        initVariations();
        initResetButton();
        initSizeGuideModal();
        initQty();
        initCartButtons();
        initAccordions();
        initMobileDrawers();
    });

})(jQuery);
