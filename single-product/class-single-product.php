<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HZ Single Product Module
 * Isolated WooCommerce single product page override.
 * Prefix: hzsp_ / .hzsp-
 */
class HZ_Single_Product
{
    private static $instance = null;
    private $opts = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Bail gracefully if WooCommerce not active
        if (!class_exists('WooCommerce')) {
            return;
        }

        $this->opts = $this->get_defaults();

        // Frontend hooks
        add_action('wp_enqueue_scripts',      [$this, 'enqueue_assets']);
        add_filter('template_include',         [$this, 'override_product_template'], 99);

        // Admin hooks
        if (is_admin()) {
            require_once __DIR__ . '/admin/class-variation-extra-images.php';
        }
        add_action('admin_menu',               [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts',    [$this, 'admin_assets']);
        add_action('admin_post_hzsp_save',     [$this, 'save_settings']);

        // AJAX: add to cart
        add_action('wp_ajax_hzsp_add_to_cart',        [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_hzsp_add_to_cart', [$this, 'ajax_add_to_cart']);

        // AJAX: get variation data
        add_action('wp_ajax_hzsp_get_variation',        [$this, 'ajax_get_variation']);
        add_action('wp_ajax_nopriv_hzsp_get_variation', [$this, 'ajax_get_variation']);

        // Custom cart & order item metadata hooks (Simple and Variable products unified display)
        add_action('wp_head',                                     [$this, 'enqueue_cart_attribute_styles']);
        add_filter('woocommerce_cart_item_name',                  [$this, 'clean_cart_item_name'], 10, 3);
        add_filter('woocommerce_add_cart_item_data',              [$this, 'add_custom_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data',                   [$this, 'display_cart_item_custom_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_custom_data_to_order_items'], 10, 4);

        // Force single actual price display for variable products across catalog and single product pages
        add_filter('woocommerce_variable_price_html',             [$this, 'custom_variable_price_html'], 10, 2);
    }

    /* ---------------------------------------------------------------
     * DEFAULTS / OPTIONS
     * ------------------------------------------------------------- */
    private function get_defaults()
    {
        $saved = get_option('hzsp_settings', []);
        $defaults = [
            'enabled'              => 1,
            'layout_width'         => '1280px',
            'bg_color'             => '#ffffff',
            'title_font_size'      => '28px',
            'title_font_weight'    => '600',
            'price_font_size'      => '22px',
            'breadcrumb_font_size' => '13px',
            // Header clearance
            'header_offset_auto'   => 1,      // 1 = auto-detect via JS, 0 = use manual
            'header_offset_manual' => '0px',  // fallback if auto disabled or as extra padding
            'desktop_top_spacing'  => '48px', // additional breathing room below header
            // Buttons
            'btn_atc_text'         => 'ADD TO CART',
            'btn_atc_bg'           => '#111111',
            'btn_atc_color'        => '#ffffff',
            'btn_bn_text'          => 'BUY NOW',
            'btn_bn_bg'            => '#ffffff',
            'btn_bn_color'         => '#111111',
            'btn_radius'           => '0px',
            // Benefits
            'benefit_1_icon'       => '↩',
            'benefit_1_text'       => 'Easy Returns',
            'benefit_2_icon'       => '📦',
            'benefit_2_text'       => 'Pan India Delivery',
            'benefit_3_icon'       => '🔒',
            'benefit_3_text'       => 'Secure Payments',
            // Accordion
            'accordion_details'    => 'Natural Fabrics • Thoughtful Construction • Made for Everyday Wear',
            'accordion_shipping'   => 'Standard delivery in 5–7 business days. Easy 7-day returns on all orders.',
            'accordion_contact'    => 'Email: hello@example.com | Phone: +91 98765 43210',
            'custom_css'           => '',
        ];
        return wp_parse_args($saved, $defaults);
    }

    /* ---------------------------------------------------------------
     * TEMPLATE OVERRIDE
     * ------------------------------------------------------------- */
    public function override_product_template($template)
    {
        if (!is_product()) {
            return $template;
        }
        $opt = get_option('hzsp_settings', []);
        $enabled = isset($opt['enabled']) ? (int)$opt['enabled'] : 1;
        if (!$enabled) {
            return $template;
        }
        return HANDZOM_UI_KIT_PATH . 'single-product/template-single-product.php';
    }

    /* ---------------------------------------------------------------
     * ASSETS
     * ------------------------------------------------------------- */
    public function enqueue_assets()
    {
        if (!is_product()) {
            return;
        }
        $opt = get_option('hzsp_settings', []);
        $enabled = isset($opt['enabled']) ? (int)$opt['enabled'] : 1;
        if (!$enabled) {
            return;
        }

        wp_enqueue_style(
            'hzsp-style',
            HANDZOM_UI_KIT_URL . 'single-product/assets/single-product.css',
            [],
            HANDZOM_UI_KIT_VERSION
        );

        wp_enqueue_script(
            'hzsp-script',
            HANDZOM_UI_KIT_URL . 'single-product/assets/single-product.js',
            ['jquery'],
            HANDZOM_UI_KIT_VERSION,
            true
        );

        // Pass data to JS
        global $product;
        if (!$product || !is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        $variation_data = [];
        if ($product && $product->is_type('variable')) {
            foreach ($product->get_available_variations() as $v) {
                $v_image = isset($v['image']) ? $v['image'] : [];
                
                // Variation specific gallery images
                $v_gallery = [];
                if (!empty($v_image['full_src'])) {
                    $v_gallery[] = [
                        'full_src'  => $v_image['full_src'],
                        'thumb_src' => !empty($v_image['thumb_src']) ? $v_image['thumb_src'] : $v_image['full_src'],
                        'alt'       => !empty($v_image['alt']) ? $v_image['alt'] : '',
                    ];
                }

                // Check WooCommerce variation gallery meta
                $v_gallery_ids_raw = get_post_meta($v['variation_id'], '_variation_image_gallery', true);
                if (empty($v_gallery_ids_raw)) {
                    $v_gallery_ids_raw = get_post_meta($v['variation_id'], 'variation_image_gallery', true);
                }

                if (!empty($v_gallery_ids_raw)) {
                    $v_ids_array = array_filter(is_array($v_gallery_ids_raw) ? $v_gallery_ids_raw : explode(',', (string)$v_gallery_ids_raw));
                    foreach ($v_ids_array as $g_id) {
                        $full_url  = wp_get_attachment_image_url($g_id, 'full');
                        $thumb_url = wp_get_attachment_image_url($g_id, 'woocommerce_thumbnail');
                        $alt_text  = get_post_meta($g_id, '_wp_attachment_image_alt', true);
                        if ($full_url) {
                            $exists = false;
                            foreach ($v_gallery as $existing_img) {
                                if ($existing_img['full_src'] === $full_url) {
                                    $exists = true;
                                    break;
                                }
                            }
                            if (!$exists) {
                                $v_gallery[] = [
                                    'full_src'  => $full_url,
                                    'thumb_src' => $thumb_url ? $thumb_url : $full_url,
                                    'alt'       => $alt_text ? $alt_text : '',
                                ];
                            }
                        }
                    }
                }

                // Check custom Variation Extra Images meta (_hzsp_variation_extra_image_ids)
                $v_extra_ids_raw = get_post_meta($v['variation_id'], '_hzsp_variation_extra_image_ids', true);
                if (!empty($v_extra_ids_raw)) {
                    $v_extra_ids_array = array_filter(is_array($v_extra_ids_raw) ? $v_extra_ids_raw : explode(',', (string)$v_extra_ids_raw));
                    foreach ($v_extra_ids_array as $e_id) {
                        $e_id = absint($e_id);
                        if (!$e_id) continue;
                        $full_url  = wp_get_attachment_image_url($e_id, 'full');
                        $thumb_url = wp_get_attachment_image_url($e_id, 'woocommerce_thumbnail');
                        if (!$thumb_url) {
                            $thumb_url = wp_get_attachment_image_url($e_id, 'large');
                        }
                        $alt_text  = get_post_meta($e_id, '_wp_attachment_image_alt', true);
                        if ($full_url) {
                            $exists = false;
                            foreach ($v_gallery as $existing_img) {
                                if ($existing_img['full_src'] === $full_url) {
                                    $exists = true;
                                    break;
                                }
                            }
                            if (!$exists) {
                                $v_gallery[] = [
                                    'full_src'  => $full_url,
                                    'thumb_src' => $thumb_url ? $thumb_url : $full_url,
                                    'alt'       => $alt_text ? $alt_text : '',
                                ];
                            }
                        }
                    }
                }

                $variation_data[] = [
                    'variation_id'         => $v['variation_id'],
                    'attributes'           => $v['attributes'],
                    'price_html'           => $v['price_html'],
                    'display_price'        => $v['display_price'],
                    'display_regular_price'=> $v['display_regular_price'],
                    'is_in_stock'          => $v['is_in_stock'],
                    'max_qty'              => $v['max_qty'],
                    'image'                => $v_image,
                    'gallery'              => $v_gallery,
                ];
            }
        }

        wp_localize_script('hzsp-script', 'hzsp_data', [
            'ajax_url'          => admin_url('admin-ajax.php'),
            'nonce'             => wp_create_nonce('hzsp_nonce'),
            'product_id'        => get_the_ID(),
            'product_type'      => $product ? $product->get_type() : 'simple',
            'variations'        => $variation_data,
            'default_attributes'=> ($product && $product->is_type('variable')) ? $product->get_default_attributes() : [],
            'checkout_url'      => wc_get_checkout_url(),
            'cart_url'          => wc_get_cart_url(),
            'btn_atc_text'      => esc_html($this->opts['btn_atc_text']),
            'btn_bn_text'       => esc_html($this->opts['btn_bn_text']),
            'header_offset_auto'=> (int) $this->opts['header_offset_auto'],
        ]);

        // Inline CSS from admin settings
        $this->output_dynamic_css();
    }

    private function output_dynamic_css()
    {
        $o = $this->opts;
        // If auto-detect is on, JS will override --hzsp-header-h at runtime.
        // If manual, we set it directly here as a fallback.
        $manual_offset  = sanitize_text_field($o['header_offset_manual']);
        $desktop_spacing = sanitize_text_field($o['desktop_top_spacing']);

        $css = "
        :root {
            --hzsp-max-width:        {$o['layout_width']};
            --hzsp-bg:               {$o['bg_color']};
            --hzsp-title-size:       {$o['title_font_size']};
            --hzsp-title-weight:     {$o['title_font_weight']};
            --hzsp-price-size:       {$o['price_font_size']};
            --hzsp-bc-size:          {$o['breadcrumb_font_size']};
            --hzsp-atc-bg:           {$o['btn_atc_bg']};
            --hzsp-atc-color:        {$o['btn_atc_color']};
            --hzsp-bn-bg:            {$o['btn_bn_bg']};
            --hzsp-bn-color:         {$o['btn_bn_color']};
            --hzsp-btn-radius:       {$o['btn_radius']};
            --hzsp-header-h:         {$manual_offset};
            --hzsp-desktop-spacing:  {$desktop_spacing};
        }";
        if (!empty($o['custom_css'])) {
            $css .= "\n" . wp_strip_all_tags($o['custom_css']);
        }
        wp_add_inline_style('hzsp-style', $css);
    }

    /* ---------------------------------------------------------------
     * AJAX: ADD TO CART
     * ------------------------------------------------------------- */
    public function ajax_add_to_cart()
    {
        check_ajax_referer('hzsp_nonce', 'nonce');

        $product_id   = absint($_POST['product_id'] ?? 0);
        $variation_id = absint($_POST['variation_id'] ?? 0);
        $quantity     = absint($_POST['quantity'] ?? 1);
        $attributes   = isset($_POST['attributes']) ? (array)$_POST['attributes'] : [];

        if (!$product_id) {
            wp_send_json_error(['message' => __('Invalid product.', 'handzom-ui-kit')]);
        }

        $sanitized_attrs = [];
        $custom_options  = [];

        foreach ($attributes as $key => $val_data) {
            $val_str = is_array($val_data) ? ($val_data['label'] ?? $val_data['val'] ?? '') : $val_data;
            if ($val_str !== '') {
                $clean_key = sanitize_text_field($key);
                $clean_val = sanitize_text_field($val_str);
                $sanitized_attrs[$clean_key] = $clean_val;

                $label = wc_attribute_label($clean_key);
                if (empty($label)) {
                    $label = ucwords(str_replace(['pa_', '-', '_'], ['', ' ', ' '], $clean_key));
                }
                $custom_options[$clean_key] = [
                    'name'  => $label,
                    'value' => ucwords(str_replace(['-', '_'], [' ', ' '], $clean_val)),
                ];
            }
        }

        if ($variation_id > 0) {
            $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $sanitized_attrs);
        } else {
            $cart_item_data = [];
            if (!empty($custom_options)) {
                $cart_item_data['hzsp_custom_options'] = $custom_options;
            }
            $added = WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);
        }

        if ($added) {
            WC()->cart->calculate_totals();

            // Render WooCommerce mini-cart fragment
            ob_start();
            woocommerce_mini_cart();
            $mini_cart_html = ob_get_clean();

            $fragments = apply_filters('woocommerce_add_to_cart_fragments', [
                'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart_html . '</div>',
            ]);

            $cart_hash = WC()->cart->get_cart_for_session() ? md5(json_encode(WC()->cart->get_cart_for_session())) : '';

            wp_send_json_success([
                'message'    => __('Product added to cart.', 'handzom-ui-kit'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_url'   => wc_get_cart_url(),
                'fragments'  => $fragments,
                'cart_hash'  => $cart_hash,
            ]);
        } else {
            $notices = wc_get_notices('error');
            $msg     = !empty($notices) ? wp_strip_all_tags($notices[0]['notice']) : __('Could not add to cart.', 'handzom-ui-kit');
            wc_clear_notices();
            wp_send_json_error(['message' => $msg]);
        }
    }

    /* ---------------------------------------------------------------
     * CUSTOM CART & ORDER ITEM DATA HANDLERS FOR SIMPLE PRODUCTS
     * ------------------------------------------------------------- */
    public function add_custom_cart_item_data($cart_item_data, $product_id, $variation_id)
    {
        if (!$variation_id && isset($_POST['attributes']) && is_array($_POST['attributes'])) {
            $custom_opts = [];
            foreach ($_POST['attributes'] as $key => $val_data) {
                $val_str = is_array($val_data) ? ($val_data['label'] ?? $val_data['val'] ?? '') : $val_data;
                if ($val_str !== '') {
                    $clean_key = sanitize_text_field($key);
                    $clean_val = sanitize_text_field($val_str);
                    $label     = wc_attribute_label($clean_key);
                    if (empty($label)) {
                        $label = ucwords(str_replace(['pa_', '-', '_'], ['', ' ', ' '], $clean_key));
                    }
                    $custom_opts[$clean_key] = [
                        'name'  => $label,
                        'value' => ucwords(str_replace(['-', '_'], [' ', ' '], $clean_val)),
                    ];
                }
            }
            if (!empty($custom_opts)) {
                $cart_item_data['hzsp_custom_options'] = $custom_opts;
            }
        }
        return $cart_item_data;
    }

    /**
     * Clean variable product titles in cart so they match simple products ("Product Name" without " – Color, Size" appended to title)
     */
    public function clean_cart_item_name($name, $cart_item, $cart_item_key)
    {
        if (!empty($cart_item['variation_id'])) {
            $product_id = $cart_item['product_id'];
            $parent_product = wc_get_product($product_id);
            if ($parent_product) {
                $clean_title = $parent_product->get_name();
                $permalink   = (isset($cart_item['data']) && is_object($cart_item['data']) && $cart_item['data']->is_visible()) ? $cart_item['data']->get_permalink($cart_item) : '';
                if ($permalink) {
                    return sprintf('<a href="%s">%s</a>', esc_url($permalink), esc_html($clean_title));
                } else {
                    return esc_html($clean_title);
                }
            }
        }
        return $name;
    }

    public function enqueue_cart_attribute_styles()
    {
        ?>
        <style id="hzsp-cart-attribute-styles">
        /* Force WooCommerce cart variation item metadata onto separate vertical lines with clean gap */
        .woocommerce-cart-form dl.variation,
        .widget_shopping_cart dl.variation,
        .cart-collaterals dl.variation,
        .cart_item dl.variation,
        .wc-item-meta,
        dl.variation {
          display: grid !important;
          grid-template-columns: auto 1fr !important;
          align-items: baseline !important;
          column-gap: 8px !important;
          row-gap: 5px !important;
          margin: 6px 0 8px 0 !important;
          padding: 0 !important;
          width: fit-content !important;
          max-width: 100% !important;
        }
        .woocommerce-cart-form dl.variation dt,
        .widget_shopping_cart dl.variation dt,
        .cart-collaterals dl.variation dt,
        .cart_item dl.variation dt,
        dl.variation dt {
          grid-column: 1 !important;
          float: none !important;
          clear: none !important;
          margin: 0 !important;
          padding: 0 !important;
          font-weight: 500 !important;
          color: #666666 !important;
          font-size: 13px !important;
          line-height: 1.4 !important;
          white-space: nowrap !important;
        }
        .woocommerce-cart-form dl.variation dd,
        .widget_shopping_cart dl.variation dd,
        .cart-collaterals dl.variation dd,
        .cart_item dl.variation dd,
        dl.variation dd {
          grid-column: 2 !important;
          float: none !important;
          clear: none !important;
          margin: 0 !important;
          padding: 0 !important;
          font-size: 13px !important;
          color: #111111 !important;
          line-height: 1.4 !important;
        }
        .woocommerce-cart-form dl.variation dd p,
        .widget_shopping_cart dl.variation dd p,
        .cart-collaterals dl.variation dd p,
        .cart_item dl.variation dd p,
        dl.variation dd p {
          display: inline !important;
          margin: 0 !important;
          padding: 0 !important;
        }
        </style>
        <?php
    }

    public function display_cart_item_custom_data($item_data, $cart_item)
    {
        $raw_list = [];

        // 1. Simple products with custom options
        if (!empty($cart_item['hzsp_custom_options']) && is_array($cart_item['hzsp_custom_options'])) {
            foreach ($cart_item['hzsp_custom_options'] as $opt) {
                $raw_list[] = [
                    'name'  => $opt['name'],
                    'value' => $opt['value'],
                ];
            }
        }
        // 2. Variable products: extract variation attributes
        elseif (!empty($cart_item['variation_id']) && !empty($cart_item['variation']) && is_array($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $attr_key => $attr_val) {
                if ($attr_val === '') continue;
                $clean_key = str_replace('attribute_', '', $attr_key);
                $label     = wc_attribute_label($clean_key);
                if (empty($label)) {
                    $label = ucwords(str_replace(['pa_', '-', '_'], ['', ' ', ' '], $clean_key));
                }

                $display_val = $attr_val;
                if (taxonomy_exists($clean_key)) {
                    $term = get_term_by('slug', $attr_val, $clean_key);
                    if ($term && !is_wp_error($term)) {
                        $display_val = $term->name;
                    }
                } else {
                    $display_val = ucwords(str_replace(['-', '_'], [' ', ' '], $attr_val));
                }

                $raw_list[] = [
                    'name'  => $label,
                    'value' => $display_val,
                ];
            }
        }

        if (empty($raw_list)) {
            return $item_data;
        }

        // Strict ordering: Color MUST be Line 1, Size MUST be Line 2
        $color_items = [];
        $size_items  = [];
        $other_items = [];

        foreach ($raw_list as $entry) {
            $name_lower = strtolower(trim($entry['name']));
            if (strpos($name_lower, 'color') !== false || strpos($name_lower, 'colour') !== false) {
                $color_items[] = $entry;
            } elseif (strpos($name_lower, 'size') !== false) {
                $size_items[] = $entry;
            } else {
                $other_items[] = $entry;
            }
        }

        $sorted = array_merge($color_items, $size_items, $other_items);

        $item_data = [];
        foreach ($sorted as $s) {
            $item_data[] = [
                'name'  => esc_html($s['name']),
                'value' => esc_html($s['value']),
            ];
        }

        return $item_data;
    }

    public function add_custom_data_to_order_items($item, $cart_item_key, $values, $order)
    {
        if (!empty($values['hzsp_custom_options']) && is_array($values['hzsp_custom_options'])) {
            $color_opts = [];
            $size_opts  = [];
            $other_opts = [];

            foreach ($values['hzsp_custom_options'] as $opt) {
                $name_lower = strtolower(trim($opt['name']));
                if (strpos($name_lower, 'color') !== false || strpos($name_lower, 'colour') !== false) {
                    $color_opts[] = $opt;
                } elseif (strpos($name_lower, 'size') !== false) {
                    $size_opts[] = $opt;
                } else {
                    $other_opts[] = $opt;
                }
            }

            $sorted = array_merge($color_opts, $size_opts, $other_opts);
            foreach ($sorted as $s) {
                $item->add_meta_data($s['name'], $s['value'], true);
            }
        }
    }

    /* ---------------------------------------------------------------
     * AJAX: GET VARIATION
     * ------------------------------------------------------------- */
    public function ajax_get_variation()
    {
        check_ajax_referer('hzsp_nonce', 'nonce');

        $product_id = absint($_POST['product_id'] ?? 0);
        $attributes = isset($_POST['attributes']) ? (array)$_POST['attributes'] : [];

        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            wp_send_json_error(['message' => 'Not a variable product.']);
        }

        $sanitized = [];
        foreach ($attributes as $k => $v) {
            $sanitized[sanitize_key($k)] = sanitize_text_field($v);
        }

        $data_store = \WC_Data_Store::load('product');
        $variation_id = $data_store->find_matching_product_variation($product, $sanitized);

        if (!$variation_id) {
            wp_send_json_error(['message' => 'Variation not found.']);
        }

        $variation = wc_get_product($variation_id);
        wp_send_json_success([
            'variation_id'  => $variation_id,
            'price_html'    => $variation->get_price_html(),
            'is_in_stock'   => $variation->is_in_stock(),
            'stock_qty'     => $variation->get_stock_quantity(),
            'sku'           => $variation->get_sku(),
        ]);
    }

    /* ---------------------------------------------------------------
     * ADMIN MENU
     * ------------------------------------------------------------- */
    public function admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            __('HZ Product Page', 'handzom-ui-kit'),
            __('HZ Product Page', 'handzom-ui-kit'),
            'manage_options',
            'hzsp-settings',
            [$this, 'admin_page']
        );
    }

    public function admin_assets($hook)
    {
        if ($hook !== 'woocommerce_page_hzsp-settings') {
            return;
        }
        wp_enqueue_style(
            'hzsp-admin',
            HANDZOM_UI_KIT_URL . 'single-product/assets/single-product-admin.css',
            [],
            HANDZOM_UI_KIT_VERSION
        );
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
    }

    /* ---------------------------------------------------------------
     * ADMIN PAGE RENDER
     * ------------------------------------------------------------- */
    public function admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $o = $this->opts;
        $saved_msg = isset($_GET['hzsp_saved']) && $_GET['hzsp_saved'] === '1';
        ?>
        <div class="wrap hzsp-admin-wrap">
            <h1><?php esc_html_e('HZ Product Page Settings', 'handzom-ui-kit'); ?></h1>
            <?php if ($saved_msg): ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.', 'handzom-ui-kit'); ?></p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('hzsp_save_nonce', 'hzsp_nonce_field'); ?>
                <input type="hidden" name="action" value="hzsp_save">

                <div class="hzsp-admin-sections">

                    <?php $this->admin_section('General', [
                        $this->admin_checkbox('enabled', 'Enable Custom Product Page', $o['enabled']),
                        $this->admin_text('layout_width', 'Max Layout Width', $o['layout_width']),
                        $this->admin_color('bg_color', 'Page Background Color', $o['bg_color']),
                    ]); ?>

                    <?php $this->admin_section('Header Clearance', [
                        $this->admin_checkbox('header_offset_auto', 'Auto-detect header height (recommended)', $o['header_offset_auto']),
                        $this->admin_text('header_offset_manual', 'Manual Header Offset (e.g. 80px — used as fallback or when auto is off)', $o['header_offset_manual']),
                        $this->admin_text('desktop_top_spacing', 'Extra spacing below header (e.g. 48px)', $o['desktop_top_spacing']),
                    ]); ?>

                    <?php $this->admin_section('Typography', [
                        $this->admin_text('title_font_size', 'Product Title Font Size', $o['title_font_size']),
                        $this->admin_text('title_font_weight', 'Product Title Font Weight', $o['title_font_weight']),
                        $this->admin_text('price_font_size', 'Price Font Size', $o['price_font_size']),
                        $this->admin_text('breadcrumb_font_size', 'Breadcrumb Font Size', $o['breadcrumb_font_size']),
                    ]); ?>

                    <?php $this->admin_section('Buttons', [
                        $this->admin_text('btn_atc_text', 'Add to Cart Label', $o['btn_atc_text']),
                        $this->admin_color('btn_atc_bg', 'Add to Cart BG', $o['btn_atc_bg']),
                        $this->admin_color('btn_atc_color', 'Add to Cart Text Color', $o['btn_atc_color']),
                        $this->admin_text('btn_bn_text', 'Buy Now Label', $o['btn_bn_text']),
                        $this->admin_color('btn_bn_bg', 'Buy Now BG', $o['btn_bn_bg']),
                        $this->admin_color('btn_bn_color', 'Buy Now Text Color', $o['btn_bn_color']),
                        $this->admin_text('btn_radius', 'Button Border Radius', $o['btn_radius']),
                    ]); ?>

                    <?php $this->admin_section('Benefits / Service Row', [
                        $this->admin_text('benefit_1_icon', 'Benefit 1 Icon/Emoji', $o['benefit_1_icon']),
                        $this->admin_text('benefit_1_text', 'Benefit 1 Text', $o['benefit_1_text']),
                        $this->admin_text('benefit_2_icon', 'Benefit 2 Icon/Emoji', $o['benefit_2_icon']),
                        $this->admin_text('benefit_2_text', 'Benefit 2 Text', $o['benefit_2_text']),
                        $this->admin_text('benefit_3_icon', 'Benefit 3 Icon/Emoji', $o['benefit_3_icon']),
                        $this->admin_text('benefit_3_text', 'Benefit 3 Text', $o['benefit_3_text']),
                    ]); ?>

                    <?php $this->admin_section('Accordion Content', [
                        $this->admin_textarea('accordion_details',  'Details Content', $o['accordion_details']),
                        $this->admin_textarea('accordion_shipping', 'Shipping & Returns Content', $o['accordion_shipping']),
                        $this->admin_textarea('accordion_contact',  'Contact Us Content', $o['accordion_contact']),
                    ]); ?>

                    <?php $this->admin_section('Custom CSS', [
                        $this->admin_textarea('custom_css', 'Custom CSS (scoped to product page)', $o['custom_css'], 8),
                    ]); ?>

                </div><!-- .hzsp-admin-sections -->

                <?php submit_button(__('Save Settings', 'handzom-ui-kit')); ?>
            </form>
        </div>
        <script>
        jQuery(function($){
            $('.hzsp-color-field').wpColorPicker();
        });
        </script>
        <?php
    }

    /* ---- Admin field helpers ---- */
    private function admin_section($title, $fields)
    {
        echo '<div class="hzsp-admin-card"><h2>' . esc_html($title) . '</h2><table class="form-table">';
        foreach ($fields as $f) {
            echo $f;
        }
        echo '</table></div>';
    }

    private function admin_text($name, $label, $value)
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" id="%1$s" name="hzsp[%1$s]" value="%3$s" class="regular-text"></td></tr>',
            esc_attr($name),
            esc_html($label),
            esc_attr($value)
        );
    }

    private function admin_color($name, $label, $value)
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" id="%1$s" name="hzsp[%1$s]" value="%3$s" class="hzsp-color-field"></td></tr>',
            esc_attr($name),
            esc_html($label),
            esc_attr($value)
        );
    }

    private function admin_checkbox($name, $label, $value)
    {
        $checked = checked(1, (int)$value, false);
        return sprintf(
            '<tr><th scope="row">%2$s</th><td><label><input type="checkbox" name="hzsp[%1$s]" value="1" %3$s> %2$s</label></td></tr>',
            esc_attr($name),
            esc_html($label),
            $checked
        );
    }

    private function admin_textarea($name, $label, $value, $rows = 4)
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="hzsp[%1$s]" rows="%4$d" class="large-text">%3$s</textarea></td></tr>',
            esc_attr($name),
            esc_html($label),
            esc_textarea($value),
            $rows
        );
    }

    /* ---------------------------------------------------------------
     * SAVE SETTINGS
     * ------------------------------------------------------------- */
    public function save_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('hzsp_save_nonce', 'hzsp_nonce_field');

        $raw = isset($_POST['hzsp']) ? (array)$_POST['hzsp'] : [];

        $allowed_colors = ['bg_color','btn_atc_bg','btn_atc_color','btn_bn_bg','btn_bn_color'];
        $allowed_texts  = ['layout_width','title_font_size','title_font_weight','price_font_size',
                           'breadcrumb_font_size','btn_atc_text','btn_bn_text','btn_radius',
                           'header_offset_manual','desktop_top_spacing',
                           'benefit_1_icon','benefit_1_text','benefit_2_icon','benefit_2_text',
                           'benefit_3_icon','benefit_3_text'];
        $allowed_areas  = ['accordion_details','accordion_shipping','accordion_contact','custom_css'];

        $clean = [];
        $clean['enabled']             = isset($raw['enabled']) ? 1 : 0;
        $clean['header_offset_auto']  = isset($raw['header_offset_auto']) ? 1 : 0;
        foreach ($allowed_texts as $k) {
            $clean[$k] = isset($raw[$k]) ? sanitize_text_field($raw[$k]) : '';
        }
        foreach ($allowed_colors as $k) {
            $clean[$k] = isset($raw[$k]) ? sanitize_hex_color($raw[$k]) : '#000000';
        }
        foreach ($allowed_areas as $k) {
            $clean[$k] = isset($raw[$k]) ? sanitize_textarea_field($raw[$k]) : '';
        }

        update_option('hzsp_settings', $clean);
        wp_redirect(admin_url('admin.php?page=hzsp-settings&hzsp_saved=1'));
        exit;
    }

    /* ---------------------------------------------------------------
     * VARIABLE PRODUCT SINGLE PRICE DISPLAY FILTER
     * ------------------------------------------------------------- */
    public function custom_variable_price_html($price, $product)
    {
        if (!$product || !is_a($product, 'WC_Product_Variable')) {
            return $price;
        }

        $available_variations = $product->get_available_variations();
        $default_attrs        = $product->get_default_attributes();
        $found_default_price  = '';

        if (!empty($default_attrs) && !empty($available_variations)) {
            foreach ($available_variations as $v) {
                $match = true;
                foreach ($default_attrs as $d_key => $d_val) {
                    $v_val = isset($v['attributes']['attribute_' . $d_key]) ? $v['attributes']['attribute_' . $d_key] : (isset($v['attributes'][$d_key]) ? $v['attributes'][$d_key] : '');
                    if ($v_val !== '' && $v_val !== $d_val) {
                        $match = false;
                        break;
                    }
                }
                if ($match && !empty($v['price_html'])) {
                    $found_default_price = $v['price_html'];
                    break;
                }
            }
        }

        if (!empty($found_default_price)) {
            return $found_default_price;
        }

        if (!empty($available_variations) && !empty($available_variations[0]['price_html'])) {
            return $available_variations[0]['price_html'];
        }

        $min_price      = $product->get_variation_price('min', true);
        $min_reg_price  = $product->get_variation_regular_price('min', true);
        $min_sale_price = $product->get_variation_sale_price('min', true);

        if ($min_sale_price && $min_reg_price && $min_sale_price < $min_reg_price) {
            return wc_format_sale_price(wc_price($min_reg_price), wc_price($min_sale_price));
        }

        if ($min_price) {
            return wc_price($min_price);
        }

        return $price;
    }

    /* ---------------------------------------------------------------
     * PUBLIC HELPERS — used by the template
     * ------------------------------------------------------------- */
    public function get_opt($key)
    {
        return $this->opts[$key] ?? '';
    }
}
