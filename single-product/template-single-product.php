<?php
/**
 * HZ Single Product – Frontend Template
 * Loaded via template_include filter only when is_product() is true
 * and the module is enabled. Everything is prefixed hzsp-.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Bail if WooCommerce not active
if (!function_exists('wc_get_product')) {
    include(get_template_directory() . '/single-product.php');
    return;
}

global $post;
$product_id = get_the_ID();
$product    = wc_get_product($product_id);

if (!$product) {
    include(get_template_directory() . '/single-product.php');
    return;
}

// ---- Gather product data ----
$title          = $product->get_name();
$short_desc     = $product->get_short_description();
$description    = $product->get_description();
$sku            = $product->get_sku();
$stock_status   = $product->get_stock_status(); // 'instock' | 'outofstock'
$is_variable    = $product->is_type('variable');
$price_html     = $product->get_price_html();

// For variable products, ensure initial price is a single price instead of a min-max price range
if ($is_variable && is_a($product, 'WC_Product_Variable')) {
    $available_variations = $product->get_available_variations();
    $default_attrs = $product->get_default_attributes();
    $found_default_price = '';

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
        $price_html = $found_default_price;
    } elseif (!empty($available_variations) && !empty($available_variations[0]['price_html'])) {
        $price_html = $available_variations[0]['price_html'];
    } else {
        $min_price = $product->get_variation_price('min', true);
        if ($min_price) {
            $price_html = wc_price($min_price);
        }
    }
}

// Categories & Hierarchical Breadcrumbs
$cats = get_the_terms($product_id, 'product_cat');
$cat_name   = '';
$cat_crumbs = [];

if ($cats && !is_wp_error($cats)) {
    $primary_cat = $cats[0];
    $cat_name    = esc_html($primary_cat->name);

    // Get ancestor category tree (from top parent down to child)
    $ancestor_ids = get_ancestors($primary_cat->term_id, 'product_cat', 'taxonomy');
    if (!empty($ancestor_ids) && !is_wp_error($ancestor_ids)) {
        $ancestor_ids = array_reverse($ancestor_ids); // top parent first
        foreach ($ancestor_ids as $anc_id) {
            $anc_term = get_term($anc_id, 'product_cat');
            if ($anc_term && !is_wp_error($anc_term)) {
                $cat_crumbs[] = [
                    'name' => $anc_term->name,
                    'link' => get_term_link($anc_term),
                ];
            }
        }
    }

    // Add current category
    $cat_crumbs[] = [
        'name' => $primary_cat->name,
        'link' => get_term_link($primary_cat),
    ];
}


// Gallery images
$main_image_id    = $product->get_image_id();
$gallery_ids      = $product->get_gallery_image_ids();
$all_image_ids    = array_filter(array_merge([$main_image_id], $gallery_ids));

// Helper function to resolve color hex values from term meta or predefined color map
if (!function_exists('hzsp_get_color_hex')) {
    function hzsp_get_color_hex($val, $term = null) {
        // 1. Term Meta Check (Custom HEX from WP term meta if provided)
        if ($term && is_object($term)) {
            $meta_color = get_term_meta($term->term_id, 'product_attributes_color', true);
            if (!$meta_color) $meta_color = get_term_meta($term->term_id, 'pa_color', true);
            if (!$meta_color) $meta_color = get_term_meta($term->term_id, 'color', true);
            if (!$meta_color) $meta_color = get_term_meta($term->term_id, '_color', true);
            if (!$meta_color) $meta_color = get_term_meta($term->term_id, 'swatch_color', true);
            if (!empty($meta_color) && is_string($meta_color) && trim($meta_color) !== '') {
                return trim($meta_color);
            }
        }

        // 2. Direct HEX String Check
        if (is_string($val) && preg_match('/^#([a-f0-9]{3}){1,2}$/i', trim($val))) {
            return trim($val);
        }
        if ($term && is_object($term) && isset($term->name) && preg_match('/^#([a-f0-9]{3}){1,2}$/i', trim($term->name))) {
            return trim($term->name);
        }

        // 3. Normalization Helper
        $normalize = function($raw) {
            if (!$raw || !is_string($raw)) return '';
            $clean = strtolower(trim($raw));
            // Replace hyphens, underscores, dots, and non-alphanumeric chars with spaces
            $clean = str_replace(['_', '-'], ' ', $clean);
            $clean = preg_replace('/[^\w\s]/u', ' ', $clean);
            // Replace multiple spaces with a single space
            $clean = preg_replace('/\s+/', ' ', $clean);
            return trim($clean);
        };

        $candidates = [];
        if ($term && is_object($term) && !empty($term->name)) {
            $candidates[] = $normalize($term->name);
        }
        if (!empty($val)) {
            $candidates[] = $normalize($val);
        }

        // 4. 100+ Fashion Color Palette Map
        $palette = [
            // NEUTRALS / WHITE / CREAM
            'white'             => '#ffffff',
            'off white'         => '#faf9f6',
            'pure white'        => '#ffffff',
            'ivory'             => '#fffff0',
            'cream'             => '#fffdd0',
            'vanilla'           => '#f3e5ab',
            'ecru'              => '#c2b280',
            'pearl'             => '#eaedec',
            'snow'              => '#fffafa',
            'alabaster'         => '#f0f0e8',
            'porcelain'         => '#f2f0eb',
            'milk white'        => '#f8f6f0',
            'oyster'            => '#e3dac9',
            'linen'             => '#faf0e6',
            'bone'              => '#e3dac9',
            'champagne'         => '#f7e7ce',
            'sand'              => '#c2b280',
            'natural'           => '#e8e3d9',
            'beige'             => '#f5f5dc',
            'light beige'       => '#f7f5e6',
            'warm beige'        => '#f5e6d3',
            'cool beige'        => '#e8e4d9',
            'taupe'             => '#483c32',
            'mushroom'          => '#ba9f8d',
            'stone'             => '#877f7d',
            'greige'            => '#b0a99f',

            // BLACK / GREY
            'black'             => '#000000',
            'jet black'         => '#0a0a0a',
            'soft black'        => '#1c1c1c',
            'charcoal'          => '#36454f',
            'dark charcoal'     => '#2b2b2b',
            'graphite'          => '#383838',
            'slate'             => '#708090',
            'dark grey'         => '#555555',
            'grey'              => '#808080',
            'medium grey'       => '#999999',
            'light grey'        => '#d3d3d3',
            'ash grey'          => '#b2beb5',
            'silver'            => '#c0c0c0',
            'steel grey'        => '#71797e',
            'smoke grey'        => '#848884',
            'cloud grey'        => '#d6d6d6',
            'cement'            => '#a5a5a5',
            'stone grey'        => '#928e85',
            'pewter'            => '#899499',

            // BLUE
            'blue'              => '#2563eb',
            'light blue'        => '#add8e6',
            'sky blue'          => '#87ceeb',
            'baby blue'         => '#89cff0',
            'powder blue'       => '#b0e0e6',
            'ice blue'          => '#d4f1f9', // Pale icy blue
            'pale blue'         => '#afeeee',
            'pastel blue'       => '#aec6cf',
            'dusty blue'        => '#7c9ed9',
            'muted blue'        => '#6b889e',
            'denim blue'        => '#1560bd',
            'washed blue'       => '#6c829b',
            'steel blue'        => '#4682b4',
            'slate blue'        => '#6a5acd',
            'ocean blue'        => '#006994',
            'aqua blue'         => '#00ffff',
            'cerulean'          => '#007ba7',
            'cobalt blue'       => '#0047ab',
            'royal blue'        => '#4169e1',
            'sapphire blue'     => '#0f52ba',
            'navy'              => '#000080',
            'dark navy'         => '#000054',
            'midnight navy'     => '#101728',
            'ink blue'          => '#002fa7',
            'deep blue'         => '#00008b',
            'petrol blue'       => '#005f73',
            'teal blue'         => '#367588',
            'teal'              => '#008080',

            // GREEN
            'green'             => '#16a34a',
            'light green'       => '#90ee90',
            'pale green'        => '#98fb98',
            'mint'              => '#98ff98',
            'mint green'        => '#98ff98',
            'pastel green'      => '#77dd77',
            'sage'              => '#9dc183',
            'sage green'        => '#9dc183',
            'dusty green'       => '#879b83',
            'muted green'       => '#6e8b6e',
            'olive'             => '#808000',
            'olive green'       => '#556b2f',
            'army green'        => '#4b5320',
            'military green'    => '#454b1b',
            'moss green'        => '#8a9a86',
            'forest green'      => '#228b22',
            'deep green'        => '#05472a',
            'bottle green'      => '#006a4e',
            'emerald'           => '#50c878',
            'jade'              => '#00a86b',
            'sea green'         => '#2e8b57',
            'pistachio'         => '#93c572',
            'khaki green'       => '#727c59',
            'khaki'             => '#c3b091',

            // RED
            'red'               => '#dc2626',
            'light red'         => '#ffcccb',
            'dark red'          => '#8b0000',
            'true red'          => '#e60000',
            'classic red'       => '#d1001c',
            'brick red'         => '#cb4154',
            'rust red'          => '#b7410e',
            'burgundy'          => '#800020',
            'wine'              => '#722f37',
            'maroon'            => '#800000',
            'deep maroon'       => '#4a0000',
            'oxblood'           => '#4a0e0e',
            'crimson'           => '#dc143c',
            'scarlet'           => '#ff2400',
            'cherry red'        => '#d2042d',
            'berry red'         => '#990f3d',
            'ruby'              => '#e0115f',
            'cranberry'         => '#9e003a',

            // PINK
            'pink'              => '#db2777',
            'light pink'        => '#ffb6c1',
            'baby pink'         => '#f4c2c2',
            'pastel pink'       => '#ffd1dc',
            'blush'             => '#de5d83',
            'blush pink'        => '#feaea5',
            'dusty pink'        => '#dcae96',
            'dusty rose'        => '#dcae96',
            'rose'              => '#ff007f',
            'rose pink'         => '#ff66cc',
            'soft rose'         => '#e6b8b8',
            'old rose'          => '#c08081',
            'muted pink'        => '#d19fe8',
            'mauve pink'        => '#e0b0ff',
            'fuchsia'           => '#ff00ff',
            'magenta'           => '#ff00ff',
            'hot pink'          => '#ff69b4',
            'coral pink'        => '#f88379',
            'salmon pink'       => '#ff91a4',
            'peach pink'        => '#ffdab9',

            // PURPLE
            'purple'            => '#9333ea',
            'light purple'      => '#d8bfd8',
            'dark purple'       => '#301934',
            'lavender'          => '#e6e6fa',
            'pastel purple'     => '#c3aed6',
            'lilac'             => '#c8a2c8',
            'dusty lilac'       => '#b39eb5',
            'mauve'             => '#e0b0ff',
            'plum'              => '#8e4585',
            'deep plum'         => '#4a0e4e',
            'eggplant'          => '#614051',
            'aubergine'         => '#3d0c02',
            'violet'            => '#8f00ff',
            'deep violet'       => '#330066',
            'amethyst'          => '#9966cc',

            // BROWN
            'brown'             => '#78350f',
            'light brown'       => '#b5651d',
            'dark brown'        => '#654321',
            'chocolate'         => '#7b3f00',
            'dark chocolate'    => '#3d2314',
            'milk chocolate'    => '#84563c',
            'coffee'            => '#6f4e37',
            'espresso'          => '#4a2c11',
            'mocha'             => '#967969',
            'cocoa'             => '#875f42',
            'café'              => '#6f4e37',
            'cafe'              => '#6f4e37',
            'caramel'           => '#c68a4c',
            'camel'             => '#c19a6b',
            'tan'               => '#d2b48c',
            'light tan'         => '#edd6b1',
            'dark tan'          => '#915c83',
            'chestnut'          => '#954535',
            'walnut'            => '#773f1a',
            'mahogany'          => '#400001',
            'cinnamon'          => '#d2691e',
            'rust'              => '#b7410e',
            'terracotta'        => '#e2725b',
            'burnt brown'       => '#5c2c16',
            'tobacco'           => '#715037',

            // YELLOW
            'yellow'            => '#ca8a04',
            'light yellow'      => '#ffffe0',
            'pastel yellow'     => '#fdfd96',
            'pale yellow'       => '#ffffbf',
            'butter yellow'     => '#fffd74',
            'cream yellow'      => '#fffdd0',
            'lemon'             => '#fff700',
            'lemon yellow'      => '#fff700',
            'mustard'           => '#ffdb58',
            'dark mustard'      => '#c7a317',
            'golden yellow'     => '#ffdf00',
            'ochre'             => '#cc7722',
            'marigold'          => '#eaa221',
            'honey'             => '#eb9605',
            'saffron'           => '#f4c430',

            // ORANGE
            'orange'            => '#ea580c',
            'light orange'      => '#fed8b1',
            'pastel orange'     => '#ffb347',
            'peach'             => '#ffe5b4',
            'soft peach'        => '#ffe5b4',
            'apricot'           => '#fbceb1',
            'coral'             => '#ff7f50',
            'coral orange'      => '#ff6f59',
            'burnt orange'      => '#cc5500',
            'rust orange'       => '#c45624',
            'terracotta orange' => '#d96b43',
            'tangerine'         => '#f28500',
            'pumpkin'           => '#ff7518',
            'copper'            => '#b87333',
        ];

        // 5. Common Aliases Map
        $aliases = [
            'navy blue'         => 'navy',
            'dark navy blue'    => 'dark navy',
            'offwhite'          => 'off white',
            'gray'              => 'grey',
            'dark gray'         => 'dark grey',
            'light gray'        => 'light grey',
            'medium gray'       => 'medium grey',
            'ash gray'          => 'ash grey',
            'slate gray'        => 'slate grey',
            'stone gray'        => 'stone grey',
            'smoke gray'        => 'smoke grey',
            'steel gray'        => 'steel grey',
            'wine red'          => 'wine',
            'burgundy red'      => 'burgundy',
            'chocolate brown'   => 'chocolate',
            'cream beige'       => 'cream',
            'skyblue'           => 'sky blue',
            'babyblue'          => 'baby blue',
            'iceblue'           => 'ice blue',
            'dustyrose'         => 'dusty rose',
            'sagegreen'         => 'sage green',
            'mintgreen'         => 'mint green',
            'olivegreen'        => 'olive green',
            'dustypink'         => 'dusty pink',
            'blushpink'         => 'blush pink',
            'rosepink'          => 'rose pink',
            'lightred'          => 'light red',
            'darkred'           => 'dark red',
            'lightblue'         => 'light blue',
            'darkblue'          => 'deep blue',
            'lightgreen'        => 'light green',
            'darkgreen'         => 'deep green',
            'lightbrown'        => 'light brown',
            'darkbrown'         => 'dark brown',
            'lightpurple'       => 'light purple',
            'darkpurple'        => 'dark purple',
            'lightyellow'       => 'light yellow',
            'darkyellow'        => 'dark mustard',
            'lightorange'       => 'light orange',
            'darkorange'        => 'burnt orange',
        ];

        // Check 1: Direct Palette Match or Alias Match on candidate strings
        foreach ($candidates as $str) {
            if (!$str) continue;

            if (isset($palette[$str])) {
                return $palette[$str];
            }

            if (isset($aliases[$str]) && isset($palette[$aliases[$str]])) {
                return $palette[$aliases[$str]];
            }
        }

        // Check 2: Safe Keyword / Family Fallback
        foreach ($candidates as $str) {
            if (!$str) continue;

            if (strpos($str, 'ice blue') !== false || strpos($str, 'iceblue') !== false) return $palette['ice blue'];
            if (strpos($str, 'navy') !== false) return $palette['navy'];
            if (strpos($str, 'ocean') !== false) return $palette['ocean blue'];
            if (strpos($str, 'sky') !== false) return $palette['sky blue'];
            if (strpos($str, 'baby blue') !== false) return $palette['baby blue'];
            if (strpos($str, 'powder blue') !== false) return $palette['powder blue'];
            if (strpos($str, 'blue') !== false) return $palette['blue'];

            if (strpos($str, 'sage') !== false) return $palette['sage'];
            if (strpos($str, 'olive') !== false) return $palette['olive'];
            if (strpos($str, 'mint') !== false) return $palette['mint'];
            if (strpos($str, 'forest') !== false) return $palette['forest green'];
            if (strpos($str, 'emerald') !== false) return $palette['emerald'];
            if (strpos($str, 'green') !== false) return $palette['green'];

            if (strpos($str, 'dusty rose') !== false || strpos($str, 'dustyrose') !== false) return $palette['dusty rose'];
            if (strpos($str, 'blush') !== false) return $palette['blush'];
            if (strpos($str, 'rose') !== false) return $palette['rose'];
            if (strpos($str, 'pink') !== false) return $palette['pink'];

            if (strpos($str, 'burgundy') !== false) return $palette['burgundy'];
            if (strpos($str, 'wine') !== false) return $palette['wine'];
            if (strpos($str, 'maroon') !== false) return $palette['maroon'];
            if (strpos($str, 'cherry') !== false) return $palette['cherry red'];
            if (strpos($str, 'red') !== false) return $palette['red'];

            if (strpos($str, 'charcoal') !== false) return $palette['charcoal'];
            if (strpos($str, 'black') !== false) return $palette['black'];
            if (strpos($str, 'grey') !== false || strpos($str, 'gray') !== false) return $palette['grey'];

            if (strpos($str, 'chocolate') !== false) return $palette['chocolate'];
            if (strpos($str, 'espresso') !== false || strpos($str, 'coffee') !== false) return $palette['espresso'];
            if (strpos($str, 'mocha') !== false || strpos($str, 'cocoa') !== false) return $palette['mocha'];
            if (strpos($str, 'camel') !== false) return $palette['camel'];
            if (strpos($str, 'tan') !== false) return $palette['tan'];
            if (strpos($str, 'brown') !== false) return $palette['brown'];

            if (strpos($str, 'mustard') !== false) return $palette['mustard'];
            if (strpos($str, 'yellow') !== false) return $palette['yellow'];

            if (strpos($str, 'peach') !== false || strpos($str, 'apricot') !== false) return $palette['peach'];
            if (strpos($str, 'coral') !== false) return $palette['coral'];
            if (strpos($str, 'orange') !== false) return $palette['orange'];

            if (strpos($str, 'lavender') !== false || strpos($str, 'lilac') !== false) return $palette['lavender'];
            if (strpos($str, 'purple') !== false || strpos($str, 'plum') !== false || strpos($str, 'violet') !== false) return $palette['purple'];

            if (strpos($str, 'cream') !== false || strpos($str, 'ivory') !== false || strpos($str, 'vanilla') !== false) return $palette['cream'];
            if (strpos($str, 'white') !== false) return $palette['white'];
            if (strpos($str, 'beige') !== false || strpos($str, 'sand') !== false || strpos($str, 'ecru') !== false) return $palette['beige'];
        }

        // Final Neutral Fallback Safeguard
        return '#e0e0e0';
    }
}

// Attributes for variable OR simple product
$attributes = [];
if ($is_variable) {
    foreach ($product->get_variation_attributes() as $attr_name => $attr_values) {
        $label = wc_attribute_label($attr_name);
        if (empty($label)) {
            $label = $attr_name;
        }
        
        $parsed_values = [];
        foreach ($attr_values as $val) {
            $term = taxonomy_exists($attr_name) ? get_term_by('slug', $val, $attr_name) : null;
            $display = $term ? $term->name : ucwords(str_replace(['-', '_'], ' ', $val));
            $parsed_values[] = [
                'slug'    => $val,
                'display' => $display,
                'term'    => $term,
            ];
        }

        $attributes[$attr_name] = [
            'label'  => $label,
            'values' => $parsed_values,
        ];
    }
} else {
    // Simple product or non-variable product with visible attributes
    $raw_attributes = $product->get_attributes();
    if (!empty($raw_attributes)) {
        foreach ($raw_attributes as $attr_key => $attr_obj) {
            if (is_object($attr_obj) && method_exists($attr_obj, 'get_visible')) {
                if (!$attr_obj->get_visible()) {
                    continue;
                }
                $attr_name = $attr_obj->get_name();
                $label     = wc_attribute_label($attr_name);
                if (empty($label)) {
                    $label = $attr_name;
                }
                
                $parsed_values = [];
                if ($attr_obj->is_taxonomy()) {
                    $terms = $attr_obj->get_terms();
                    if (!empty($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $parsed_values[] = [
                                'slug'    => $term->slug,
                                'display' => $term->name,
                                'term'    => $term,
                            ];
                        }
                    }
                } else {
                    $raw_opts = $attr_obj->get_options();
                    if (is_array($raw_opts)) {
                        foreach ($raw_opts as $opt) {
                            $clean_opt = trim($opt);
                            if ($clean_opt !== '') {
                                $parsed_values[] = [
                                    'slug'    => $clean_opt,
                                    'display' => $clean_opt,
                                    'term'    => null,
                                ];
                            }
                        }
                    }
                }
                
                if (!empty($parsed_values)) {
                    $attributes[$attr_name] = [
                        'label'  => $label,
                        'values' => $parsed_values,
                    ];
                }
            } elseif (is_array($attr_obj)) {
                if (!empty($attr_obj['is_visible'])) {
                    $attr_name = isset($attr_obj['name']) ? $attr_obj['name'] : $attr_key;
                    $label = wc_attribute_label($attr_name);
                    if (empty($label)) {
                        $label = $attr_name;
                    }
                    $raw_vals = array_map('trim', explode('|', $attr_obj['value']));
                    $parsed_values = [];
                    foreach ($raw_vals as $val) {
                        if ($val !== '') {
                            $parsed_values[] = [
                                'slug'    => $val,
                                'display' => $val,
                                'term'    => null,
                            ];
                        }
                    }
                    if (!empty($parsed_values)) {
                        $attributes[$attr_name] = [
                            'label'  => $label,
                            'values' => $parsed_values,
                        ];
                    }
                }
            }
        }
    }
}

// Module options (via helper)
$mod = \HandzomUIKit\HZ_Single_Product::instance();

// Header/footer — use theme's
get_header();
?>
<style id="hzsp-escape-container">
/*
 * Force the product page to escape any narrow theme content container.
 * Uses the classic 50vw trick: position relative + negative side margins
 * equal to 50vw so the element stretches edge-to-edge regardless of parent width.
 */
#hzsp-page {
    position: relative !important;
    width: 100vw !important;
    max-width: 100vw !important;
    left: 50% !important;
    margin-left: -50vw !important;
    margin-right: -50vw !important;
    margin-top: 40px !important;
    box-sizing: border-box !important;
    right: auto !important;
}
</style>

<div id="hzsp-page" class="hzsp-page" data-product-id="<?php echo esc_attr($product_id); ?>">

    <!-- ============================================================
         DESKTOP LAYOUT
    ============================================================ -->
    <div class="hzsp-desktop-layout">
        <div class="hzsp-inner">

            <!-- COL 1: Vertical thumbnails -->
            <div class="hzsp-thumbs" id="hzsp-thumbs">
                <?php foreach ($all_image_ids as $idx => $img_id) :
                    $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                    $full  = wp_get_attachment_image_url($img_id, 'large');
                    if (!$thumb) continue;
                ?>
                <link rel="prefetch" as="image" href="<?php echo esc_url($full); ?>">
                <button type="button" class="hzsp-thumb <?php echo $idx === 0 ? 'hzsp-thumb--active' : ''; ?>"
                        data-full="<?php echo esc_url($full); ?>"
                        data-thumb="<?php echo esc_url($thumb); ?>"
                        aria-label="<?php printf(esc_attr__('View image %d', 'handzom-ui-kit'), $idx + 1); ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title . ' ' . ($idx + 1)); ?>">
                </button>
                <?php endforeach; ?>
            </div>

            <!-- COL 2: Main image -->
            <div class="hzsp-main-image-wrap" id="hzsp-main-image-wrap">
                <?php
                $main_url  = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'large') : wc_placeholder_img_src('large');
                $main_full = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'full') : $main_url;
                ?>
                <img id="hzsp-main-image"
                     src="<?php echo esc_url($main_url); ?>"
                     alt="<?php echo esc_attr($title); ?>"
                     class="hzsp-main-image">
                <!-- Zoom button -->
                <button class="hzsp-zoom-btn" id="hzsp-zoom-btn"
                        aria-label="<?php esc_attr_e('Zoom image', 'handzom-ui-kit'); ?>"
                        data-full="<?php echo esc_url($main_full); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="11" y1="8" x2="11" y2="14"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </button>
            </div>

            <!-- RIGHT: Info -->
            <div class="hzsp-info-col">

                <!-- Hierarchical Category Breadcrumb -->
                <nav class="hzsp-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'handzom-ui-kit'); ?>">
                    <?php
                    $shop_page_id = wc_get_page_id('shop');
                    $shop_url     = $shop_page_id > 0 ? get_permalink($shop_page_id) : home_url('/');
                    ?>
                    <?php if (empty($cat_crumbs)) : ?>
                        <a href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('SHOP', 'handzom-ui-kit'); ?></a>
                    <?php else : ?>
                        <?php if (count($cat_crumbs) === 1) : ?>
                            <a href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('SHOP', 'handzom-ui-kit'); ?></a>
                            <span class="hzsp-bc-sep">/</span>
                        <?php endif; ?>

                        <?php foreach ($cat_crumbs as $idx => $crumb) : ?>
                            <?php if ($idx > 0) : ?>
                                <span class="hzsp-bc-sep">/</span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(is_string($crumb['link']) ? $crumb['link'] : '#'); ?>">
                                <?php echo esc_html(strtoupper($crumb['name'])); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <span class="hzsp-bc-sep">/</span>
                    <span><?php echo esc_html(strtoupper($title)); ?></span>
                </nav>


                <!-- Category label -->
                <?php if ($cat_name) : ?>
                <p class="hzsp-category-label"><?php echo esc_html(strtoupper($cat_name)); ?></p>
                <?php endif; ?>

                <!-- Title -->
                <h1 class="hzsp-product-title"><?php echo esc_html(strtoupper($title)); ?></h1>

                <!-- Price -->
                <div class="hzsp-price-wrap" id="hzsp-price-wrap">
                    <?php echo wp_kses_post($price_html); ?>
                    <span class="hzsp-tax-note"><?php esc_html_e('(Inclusive of all taxes)', 'handzom-ui-kit'); ?></span>
                </div>

                <div class="hzsp-divider"></div>

                <?php if (!empty($attributes)) : ?>
                <!-- Variations & Attributes -->
                <div class="hzsp-variations" id="hzsp-variations">
                    <?php foreach ($attributes as $attr_name => $attr_data) :
                        $label    = strtoupper($attr_data['label']);
                        $values   = $attr_data['values'];
                        $slug     = sanitize_title($attr_name);
                        $is_color = (stripos($attr_name, 'color') !== false || stripos($attr_name, 'colour') !== false);
                        $is_size  = (stripos($attr_name, 'size') !== false);
                    ?>
                    <div class="hzsp-attr-row" data-attribute="<?php echo esc_attr($attr_name); ?>">
                        <div class="hzsp-attr-label-row">
                            <div class="hzsp-attr-label-left">
                                <span class="hzsp-attr-label"><?php echo esc_html($label); ?>:</span>
                                <span class="hzsp-attr-selected-val" id="hzsp-val-<?php echo esc_attr($slug); ?>"></span>
                            </div>
                            <?php if ($is_size) : ?>
                                <button type="button" class="hzsp-size-guide-btn hzsp-size-guide-trigger">
                                    <?php esc_html_e("What's my size?", 'handzom-ui-kit'); ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($values)) : ?>
                            <p class="hzsp-not-available"><?php esc_html_e('Not available', 'handzom-ui-kit'); ?></p>
                        <?php else : ?>
                        <div class="hzsp-attr-options hzsp-attr-options--<?php echo $is_color ? 'color' : 'size'; ?>">
                            <?php foreach ($values as $val_data) :
                                $val      = $val_data['slug'];
                                $display  = $val_data['display'];
                                $term     = $val_data['term'];

                                $swatch_color = '';
                                if ($is_color) {
                                    $swatch_color = hzsp_get_color_hex($val, $term);
                                }
                            ?>
                            <button type="button"
                                    class="hzsp-option-btn <?php echo $is_color ? 'hzsp-color-btn' : 'hzsp-size-btn'; ?>"
                                    data-attribute="<?php echo esc_attr($attr_name); ?>"
                                    data-value="<?php echo esc_attr($val); ?>"
                                    data-label="<?php echo esc_attr($display); ?>"
                                    title="<?php echo esc_attr($display); ?>"
                                    aria-label="<?php echo esc_attr($label . ': ' . $display); ?>">
                                <?php if ($is_color) : ?>
                                    <span class="hzsp-color-swatch"
                                          style="background:<?php echo esc_attr($swatch_color); ?>;"
                                          aria-hidden="true"></span>
                                    <span class="hzsp-color-name-tooltip"><?php echo esc_html($display); ?></span>
                                <?php else : ?>
                                    <?php echo esc_html($display); ?>
                                <?php endif; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <!-- Desktop Reset / Clear selection button -->
                    <div class="hzsp-reset-wrap" id="hzsp-reset-wrap" style="display: none;">
                        <button type="button" class="hzsp-reset-btn" id="hzsp-reset-btn">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                            <span><?php esc_html_e('CLEAR SELECTION', 'handzom-ui-kit'); ?></span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>


                <!-- Quantity -->
                <div class="hzsp-qty-row">
                    <span class="hzsp-qty-label"><?php esc_html_e('QTY:', 'handzom-ui-kit'); ?></span>
                    <div class="hzsp-qty-ctrl">
                        <button type="button" class="hzsp-qty-btn hzsp-qty-minus" id="hzsp-qty-minus" aria-label="<?php esc_attr_e('Decrease quantity', 'handzom-ui-kit'); ?>">−</button>
                        <input type="number" id="hzsp-qty-input" class="hzsp-qty-input" name="quantity" value="1" min="1" max="<?php echo esc_attr($product->get_max_purchase_quantity() ?: 99); ?>" inputmode="numeric">
                        <button type="button" class="hzsp-qty-btn hzsp-qty-plus" id="hzsp-qty-plus" aria-label="<?php esc_attr_e('Increase quantity', 'handzom-ui-kit'); ?>">+</button>
                    </div>
                </div>

                <!-- Stock notice -->
                <div class="hzsp-stock-notice" id="hzsp-stock-notice">
                    <?php if ($stock_status === 'outofstock') : ?>
                    <span class="hzsp-out-of-stock"><?php esc_html_e('Out of Stock', 'handzom-ui-kit'); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Buttons -->
                <div class="hzsp-btn-group">
                    <button type="button" id="hzsp-atc-btn" class="hzsp-btn hzsp-btn--atc"
                            <?php echo $stock_status === 'outofstock' ? 'disabled' : ''; ?>>
                        <span class="hzsp-btn-text"><?php echo esc_html($mod->get_opt('btn_atc_text')); ?></span>
                        <span class="hzsp-btn-spinner" style="display:none;" aria-hidden="true">
                            <svg class="hzsp-spinner-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                            </svg>
                        </span>
                    </button>
                    <button type="button" id="hzsp-bn-btn" class="hzsp-btn hzsp-btn--bn"
                            <?php echo $stock_status === 'outofstock' ? 'disabled' : ''; ?>>
                        <?php echo esc_html($mod->get_opt('btn_bn_text')); ?>
                    </button>
                </div>

                <!-- Ajax message -->
                <div id="hzsp-msg" class="hzsp-msg" role="alert" aria-live="polite"></div>

                <!-- Benefits row (Image 2 design match) -->
                <div class="hzsp-benefits-row">
                    <div class="hzsp-benefit">
                        <div class="hzsp-benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.5 2v6h-6"/>
                                <path d="M2.5 22v-6h6"/>
                                <path d="M2 11.5a10 10 0 0 1 18.8-4.3L21.5 8"/>
                                <path d="M22 12.5a10 10 0 0 1-18.8 4.2L2.5 16"/>
                            </svg>
                        </div>
                        <div class="hzsp-benefit-info">
                            <span class="hzsp-benefit-line1"><?php esc_html_e('7 DAYS', 'handzom-ui-kit'); ?></span>
                            <span class="hzsp-benefit-line2"><?php esc_html_e('EASY RETURNS', 'handzom-ui-kit'); ?></span>
                        </div>
                    </div>
                    <div class="hzsp-benefit">
                        <div class="hzsp-benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 3h15v13H1z"/>
                                <path d="M16 8h4l3 3v5h-7V8z"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <div class="hzsp-benefit-info">
                            <span class="hzsp-benefit-line1"><?php esc_html_e('PAN INDIA', 'handzom-ui-kit'); ?></span>
                            <span class="hzsp-benefit-line2"><?php esc_html_e('DELIVERY', 'handzom-ui-kit'); ?></span>
                        </div>
                    </div>
                    <div class="hzsp-benefit">
                        <div class="hzsp-benefit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="hzsp-benefit-info">
                            <span class="hzsp-benefit-line1"><?php esc_html_e('SECURE', 'handzom-ui-kit'); ?></span>
                            <span class="hzsp-benefit-line2"><?php esc_html_e('PAYMENTS', 'handzom-ui-kit'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Brand Tagline Bar (Image 1 & 2 design match) -->
                <div class="hzsp-tagline-bar">
                    <span class="hzsp-tagline-item"><?php esc_html_e('NATURAL FABRICS', 'handzom-ui-kit'); ?></span>
                    <span class="hzsp-tagline-dot">&bull;</span>
                    <span class="hzsp-tagline-item"><?php esc_html_e('THOUGHTFUL CONSTRUCTION', 'handzom-ui-kit'); ?></span>
                    <span class="hzsp-tagline-dot">&bull;</span>
                    <span class="hzsp-tagline-item"><?php esc_html_e('MADE FOR EVERYDAY WEAR', 'handzom-ui-kit'); ?></span>
                </div>

                <!-- Short description tagline -->
                <?php if ($short_desc) : ?>
                <div class="hzsp-tagline">
                    <?php echo wp_kses_post($short_desc); ?>
                </div>
                <?php endif; ?>

            </div><!-- .hzsp-info-col -->

        </div><!-- .hzsp-inner -->

        <!-- Accordions -->
        <div class="hzsp-accordions-wrap">
            <div class="hzsp-inner">
                <?php
                // Dynamic product description for Details tab
                $details_content = is_a($product, 'WC_Product') ? trim($product->get_description()) : '';
                if (empty($details_content)) {
                    $details_content = trim(get_the_content());
                }
                if (empty(trim(strip_tags($details_content)))) {
                    $details_content = __('No details available for this product.', 'handzom-ui-kit');
                }

                $shipping_content = trim($mod->get_opt('accordion_shipping'));
                if (empty($shipping_content)) {
                    $shipping_content = __('Standard delivery in 5–7 business days. Easy 7-day returns on all orders.', 'handzom-ui-kit');
                }

                $contact_raw = trim($mod->get_opt('accordion_contact'));
                if (empty($contact_raw) || strpos($contact_raw, 'example.com') !== false) {
                    $contact_raw = "63633 99799\nsupport@handzom.in";
                }

                if (strpos($contact_raw, '<svg') !== false && strpos($contact_raw, 'hzsp-contact-item') !== false) {
                    $contact_content = $contact_raw;
                } else {
                    $lines = array_filter(array_map('trim', explode("\n", strip_tags($contact_raw))));
                    $items_html = '';
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        if (filter_var($line, FILTER_VALIDATE_EMAIL) || strpos($line, '@') !== false) {
                            $items_html .= '
                            <div class="hzsp-contact-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                <a href="mailto:' . esc_attr($line) . '">' . esc_html($line) . '</a>
                            </div>';
                        } elseif (preg_match('/[0-9]{5,}/', $line)) {
                            $clean_phone = preg_replace('/[^0-9+]/', '', $line);
                            $items_html .= '
                            <div class="hzsp-contact-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                <a href="tel:' . esc_attr($clean_phone) . '">' . esc_html($line) . '</a>
                            </div>';
                        } else {
                            $items_html .= '<div class="hzsp-contact-item"><span>' . esc_html($line) . '</span></div>';
                        }
                    }
                    $contact_content = '<div class="hzsp-contact-list">' . $items_html . '</div>';
                }

                $accordions = [
                    'details'  => [__('DETAILS', 'handzom-ui-kit'),  $details_content],
                    'shipping' => [__('SHIPPING & RETURNS', 'handzom-ui-kit'), $shipping_content],
                    'contact'  => [__('CONTACT US', 'handzom-ui-kit'), $contact_content],
                ];
                foreach ($accordions as $akey => [$alabel, $acontent]) :
                ?>
                <div class="hzsp-accordion" id="hzsp-acc-<?php echo esc_attr($akey); ?>">
                    <button type="button" class="hzsp-acc-trigger" aria-expanded="false"
                            aria-controls="hzsp-acc-panel-<?php echo esc_attr($akey); ?>">
                        <span><?php echo esc_html($alabel); ?></span>
                        <svg class="hzsp-acc-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <div class="hzsp-acc-panel" id="hzsp-acc-panel-<?php echo esc_attr($akey); ?>" hidden>
                        <div class="hzsp-acc-content">
                            <?php echo ($akey === 'contact') ? wp_kses_post($acontent) : wp_kses_post(wpautop($acontent)); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- .hzsp-desktop-layout -->

    <!-- ============================================================
         MOBILE LAYOUT
    ============================================================ -->
    <div class="hzsp-mobile-layout">

        <!-- Mobile image slider -->
        <div class="hzsp-mob-gallery" id="hzsp-mob-gallery">
            <div class="hzsp-mob-slides" id="hzsp-mob-slides">
                <?php foreach ($all_image_ids as $idx => $img_id) :
                    $src = wp_get_attachment_image_url($img_id, 'large');
                    if (!$src) continue;
                ?>
                <div class="hzsp-mob-slide <?php echo $idx === 0 ? 'hzsp-mob-slide--active' : ''; ?>">
                    <img src="<?php echo esc_url($src); ?>"
                         alt="<?php echo esc_attr($title . ' ' . ($idx + 1)); ?>"
                         loading="<?php echo $idx === 0 ? 'eager' : 'lazy'; ?>">
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($all_image_ids) > 1) : ?>
            <!-- Dynamic Image Counter -->
            <div class="hzsp-mob-counter" id="hzsp-mob-counter">1 / <?php echo count($all_image_ids); ?></div>

            <!-- Dots Indicator -->
            <div class="hzsp-mob-dots" id="hzsp-mob-dots">
                <?php foreach ($all_image_ids as $idx => $img_id) :
                    if (!wp_get_attachment_image_url($img_id, 'thumbnail')) continue;
                ?>
                <button class="hzsp-mob-dot <?php echo $idx === 0 ? 'hzsp-mob-dot--active' : ''; ?>"
                        data-slide="<?php echo esc_attr($idx); ?>"
                        aria-label="<?php printf(esc_attr__('Go to image %d', 'handzom-ui-kit'), $idx + 1); ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mobile info -->
        <div class="hzsp-mob-info">
            <?php if ($cat_name) : ?>
            <p class="hzsp-mob-category"><?php echo esc_html(strtoupper($cat_name)); ?></p>
            <?php endif; ?>
            <h1 class="hzsp-mob-title"><?php echo esc_html(strtoupper($title)); ?></h1>
            <div class="hzsp-mob-price">
                <?php echo wp_kses_post($price_html); ?>
                <span class="hzsp-tax-note"><?php esc_html_e('(Inclusive of all taxes)', 'handzom-ui-kit'); ?></span>
            </div>

            <div class="hzsp-divider" style="margin: 16px 0;"></div>

            <?php if (!empty($attributes)) :
                foreach ($attributes as $attr_name => $attr_data) :
                    $slug     = sanitize_title($attr_name);
                    $label    = strtoupper($attr_data['label']);
                    $values   = $attr_data['values'];
                    $is_color = (stripos($attr_name, 'color') !== false || stripos($attr_name, 'colour') !== false);
                    $is_size  = (stripos($attr_name, 'size') !== false);
            ?>
            <div class="hzsp-mob-attr-row" data-attribute="<?php echo esc_attr($attr_name); ?>">
                <div class="hzsp-mob-attr-label-row">
                    <div class="hzsp-attr-label-left">
                        <span class="hzsp-mob-attr-label"><?php echo esc_html($label); ?>:</span>
                        <span class="hzsp-mob-attr-val" id="hzsp-mob-val-<?php echo esc_attr($slug); ?>"></span>
                    </div>
                    <?php if ($is_size) : ?>
                        <button type="button" class="hzsp-size-guide-btn hzsp-size-guide-trigger">
                            <?php esc_html_e("What's my size?", 'handzom-ui-kit'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="hzsp-mob-attr-options hzsp-mob-attr-options--<?php echo $is_color ? 'color' : 'size'; ?>">
                    <?php foreach ($values as $val_data) :
                        $val     = $val_data['slug'];
                        $display = $val_data['display'];
                        $term    = $val_data['term'];

                        $swatch_color = '';
                        if ($is_color) {
                            $swatch_color = hzsp_get_color_hex($val, $term);
                        }
                    ?>
                    <button type="button"
                            class="hzsp-mob-option-btn <?php echo $is_color ? 'hzsp-mob-color-btn' : 'hzsp-mob-size-btn'; ?>"
                            data-attribute="<?php echo esc_attr($attr_name); ?>"
                            data-value="<?php echo esc_attr($val); ?>"
                            data-label="<?php echo esc_attr($display); ?>"
                            title="<?php echo esc_attr($display); ?>">
                        <?php if ($is_color) : ?>
                            <span class="hzsp-mob-color-swatch" style="background:<?php echo esc_attr($swatch_color); ?>;"></span>
                        <?php else : ?>
                            <?php echo esc_html($display); ?>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Mobile Reset / Clear selection button -->
            <div class="hzsp-reset-wrap" id="hzsp-mob-reset-wrap" style="display: none;">
                <button type="button" class="hzsp-reset-btn" id="hzsp-mob-reset-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                    </svg>
                    <span><?php esc_html_e('CLEAR SELECTION', 'handzom-ui-kit'); ?></span>
                </button>
            </div>
            <?php endif; ?>

            <!-- Mobile Qty -->
            <div class="hzsp-mob-qty-row">
                <span class="hzsp-mob-qty-label"><?php esc_html_e('QTY:', 'handzom-ui-kit'); ?></span>
                <div class="hzsp-mob-qty-ctrl">
                    <button type="button" class="hzsp-qty-btn hzsp-qty-minus" aria-label="<?php esc_attr_e('Decrease', 'handzom-ui-kit'); ?>">−</button>
                    <input type="number" class="hzsp-qty-input" name="quantity" value="1" min="1" max="<?php echo esc_attr($product->get_max_purchase_quantity() ?: 99); ?>" inputmode="numeric">
                    <button type="button" class="hzsp-qty-btn hzsp-qty-plus" aria-label="<?php esc_attr_e('Increase', 'handzom-ui-kit'); ?>">+</button>
                </div>
            </div>

            <!-- Mobile stock -->
            <div class="hzsp-stock-notice" id="hzsp-mob-stock-notice">
                <?php if ($stock_status === 'outofstock') : ?>
                <span class="hzsp-out-of-stock"><?php esc_html_e('Out of Stock', 'handzom-ui-kit'); ?></span>
                <?php endif; ?>
            </div>

            <!-- Mobile buttons -->
            <div class="hzsp-mob-btn-group">
                <button type="button" class="hzsp-btn hzsp-btn--atc" <?php echo $stock_status === 'outofstock' ? 'disabled' : ''; ?>>
                    <span class="hzsp-btn-text"><?php echo esc_html($mod->get_opt('btn_atc_text')); ?></span>
                    <span class="hzsp-btn-spinner" style="display:none;" aria-hidden="true">
                        <svg class="hzsp-spinner-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                    </span>
                </button>
                <button type="button" class="hzsp-btn hzsp-btn--bn" <?php echo $stock_status === 'outofstock' ? 'disabled' : ''; ?>>
                    <?php echo esc_html($mod->get_opt('btn_bn_text')); ?>
                </button>
            </div>

            <div class="hzsp-mob-msg" role="alert" aria-live="polite"></div>

            <!-- Mobile Benefits Row (Image 2 design match) -->
            <div class="hzsp-benefits-row">
                <div class="hzsp-benefit">
                    <div class="hzsp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.5 2v6h-6"/>
                            <path d="M2.5 22v-6h6"/>
                            <path d="M2 11.5a10 10 0 0 1 18.8-4.3L21.5 8"/>
                            <path d="M22 12.5a10 10 0 0 1-18.8 4.2L2.5 16"/>
                        </svg>
                    </div>
                    <div class="hzsp-benefit-info">
                        <span class="hzsp-benefit-line1"><?php esc_html_e('7 DAYS', 'handzom-ui-kit'); ?></span>
                        <span class="hzsp-benefit-line2"><?php esc_html_e('EASY RETURNS', 'handzom-ui-kit'); ?></span>
                    </div>
                </div>
                <div class="hzsp-benefit">
                    <div class="hzsp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 3h15v13H1z"/>
                            <path d="M16 8h4l3 3v5h-7V8z"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="hzsp-benefit-info">
                        <span class="hzsp-benefit-line1"><?php esc_html_e('PAN INDIA', 'handzom-ui-kit'); ?></span>
                        <span class="hzsp-benefit-line2"><?php esc_html_e('DELIVERY', 'handzom-ui-kit'); ?></span>
                    </div>
                </div>
                <div class="hzsp-benefit">
                    <div class="hzsp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="hzsp-benefit-info">
                        <span class="hzsp-benefit-line1"><?php esc_html_e('SECURE', 'handzom-ui-kit'); ?></span>
                        <span class="hzsp-benefit-line2"><?php esc_html_e('PAYMENTS', 'handzom-ui-kit'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Mobile Brand Tagline Bar (Image 1 & 2 design match) -->
            <div class="hzsp-tagline-bar">
                <span class="hzsp-tagline-item"><?php esc_html_e('NATURAL FABRICS', 'handzom-ui-kit'); ?></span>
                <span class="hzsp-tagline-dot">&bull;</span>
                <span class="hzsp-tagline-item"><?php esc_html_e('THOUGHTFUL CONSTRUCTION', 'handzom-ui-kit'); ?></span>
                <span class="hzsp-tagline-dot">&bull;</span>
                <span class="hzsp-tagline-item"><?php esc_html_e('MADE FOR EVERYDAY WEAR', 'handzom-ui-kit'); ?></span>
            </div>

            <!-- Mobile accordions (slide-over drawers) -->
            <div class="hzsp-mob-accordions">
                <?php foreach ($accordions as $akey => [$alabel, $acontent]) : ?>
                <div class="hzsp-mob-acc">
                    <button type="button" class="hzsp-mob-acc-trigger"
                            data-drawer="<?php echo esc_attr($akey); ?>">
                        <span><?php echo esc_html($alabel); ?></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

        </div><!-- .hzsp-mob-info -->
    </div><!-- .hzsp-mobile-layout -->

    <!-- Mobile drawers -->
    <?php foreach ($accordions as $akey => [$alabel, $acontent]) : ?>
    <div class="hzsp-mob-drawer" id="hzsp-drawer-<?php echo esc_attr($akey); ?>" aria-hidden="true">
        <div class="hzsp-mob-drawer-overlay" data-close="<?php echo esc_attr($akey); ?>"></div>
        <div class="hzsp-mob-drawer-panel">
            <div class="hzsp-mob-drawer-header">
                <span><?php echo esc_html($alabel); ?></span>
                <button type="button" class="hzsp-mob-drawer-close" data-close="<?php echo esc_attr($akey); ?>" aria-label="<?php esc_attr_e('Close', 'handzom-ui-kit'); ?>">✕</button>
            </div>
            <div class="hzsp-mob-drawer-body">
                <?php echo wp_kses_post(wpautop($acontent)); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <!-- ============================================================
         YOU MAY ALSO LIKE (RELATED PRODUCTS)
    ============================================================ -->
    <?php
    $related_ids = wc_get_related_products($product_id, 4);

    if (empty($related_ids) && !empty($cats) && !is_wp_error($cats)) {
        $rel_args = [
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'post__not_in'   => [$product_id],
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $cats[0]->term_id,
                ],
            ],
        ];
        $rel_query = new WP_Query($rel_args);
        if ($rel_query->have_posts()) {
            $related_ids = wp_list_pluck($rel_query->posts, 'ID');
        }
    }

    if (!empty($related_ids)) :
    ?>
    <section class="hzsp-related-section">
        <div class="hzsp-inner">
            <h2 class="hzsp-related-heading"><?php esc_html_e('YOU MAY ALSO LIKE', 'handzom-ui-kit'); ?></h2>
            <div class="hzsp-related-grid">
                <?php foreach ($related_ids as $rel_id) :
                    $rel_product = wc_get_product($rel_id);
                    if (!$rel_product || !$rel_product->is_visible()) continue;
                    
                    $rel_title = $rel_product->get_name();
                    $rel_link  = get_permalink($rel_id);
                    $rel_img   = wp_get_attachment_image_url($rel_product->get_image_id(), 'medium_large') ?: wc_placeholder_img_src('medium_large');
                    $rel_price = $rel_product->get_price_html();
                    $rel_terms = get_the_terms($rel_id, 'product_cat');
                    $rel_cat   = (!empty($rel_terms) && !is_wp_error($rel_terms)) ? $rel_terms[0]->name : '';
                ?>
                <div class="hzsp-related-card">
                    <a href="<?php echo esc_url($rel_link); ?>" class="hzsp-related-img-link" tabindex="-1">
                        <div class="hzsp-related-img-wrap">
                            <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel_title); ?>" loading="lazy">
                        </div>
                    </a>
                    <div class="hzsp-related-info">
                        <?php if ($rel_cat) : ?>
                            <span class="hzsp-related-cat"><?php echo esc_html(strtoupper($rel_cat)); ?></span>
                        <?php endif; ?>
                        <h3 class="hzsp-related-title">
                            <a href="<?php echo esc_url($rel_link); ?>"><?php echo esc_html(strtoupper($rel_title)); ?></a>
                        </h3>
                        <div class="hzsp-related-price"><?php echo wp_kses_post($rel_price); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div><!-- #hzsp-page -->

<!-- Premium Lightbox / Image Zoom -->
<div id="hzsp-lightbox" class="hzsp-lightbox" aria-modal="true" role="dialog" aria-label="<?php esc_attr_e('Product image zoom', 'handzom-ui-kit'); ?>" aria-hidden="true">
    <div class="hzsp-lb-backdrop" id="hzsp-lightbox-overlay"></div>
    <div class="hzsp-lb-stage">
        <!-- Close -->
        <button class="hzsp-lb-close" id="hzsp-lightbox-close" aria-label="<?php esc_attr_e('Close', 'handzom-ui-kit'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <!-- Prev / Next (only shown when multiple images) -->
        <button class="hzsp-lb-nav hzsp-lb-prev" id="hzsp-lb-prev" aria-label="<?php esc_attr_e('Previous image', 'handzom-ui-kit'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <!-- Image -->
        <div class="hzsp-lb-img-wrap">
            <img id="hzsp-lightbox-img" src="" alt="<?php echo esc_attr($title); ?>" class="hzsp-lb-img">
        </div>
        <button class="hzsp-lb-nav hzsp-lb-next" id="hzsp-lb-next" aria-label="<?php esc_attr_e('Next image', 'handzom-ui-kit'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <!-- Counter -->
        <div class="hzsp-lb-counter" id="hzsp-lb-counter"></div>
    </div>
</div>

<!-- Premium Size Guide Modal -->
<div id="hzsp-size-modal" class="hzsp-size-modal" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e('Size Guide', 'handzom-ui-kit'); ?>">
    <div class="hzsp-size-modal-overlay" id="hzsp-size-modal-close-overlay"></div>
    <div class="hzsp-size-modal-content">
        <div class="hzsp-size-modal-header">
            <h3 class="hzsp-size-modal-title"><?php esc_html_e('SIZE GUIDE (INCHES)', 'handzom-ui-kit'); ?></h3>
            <button type="button" class="hzsp-size-modal-close" id="hzsp-size-modal-close-btn" aria-label="<?php esc_attr_e('Close size guide', 'handzom-ui-kit'); ?>">&times;</button>
        </div>
        <div class="hzsp-size-modal-body">
            <p class="hzsp-size-modal-desc"><?php esc_html_e('Measurements shown are garment dimensions in inches. For a relaxed fit, order your normal size.', 'handzom-ui-kit'); ?></p>
            <div class="hzsp-size-table-wrap">
                <table class="hzsp-size-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('SIZE', 'handzom-ui-kit'); ?></th>
                            <th><?php esc_html_e('CHEST', 'handzom-ui-kit'); ?></th>
                            <th><?php esc_html_e('SHOULDER', 'handzom-ui-kit'); ?></th>
                            <th><?php esc_html_e('LENGTH', 'handzom-ui-kit'); ?></th>
                            <th><?php esc_html_e('SLEEVE', 'handzom-ui-kit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>S</strong></td><td>38&nbsp;&ndash;&nbsp;40"</td><td>17.5"</td><td>28.5"</td><td>25.0"</td></tr>
                        <tr><td><strong>M</strong></td><td>40&nbsp;&ndash;&nbsp;42"</td><td>18.0"</td><td>29.0"</td><td>25.5"</td></tr>
                        <tr><td><strong>L</strong></td><td>42&nbsp;&ndash;&nbsp;44"</td><td>18.5"</td><td>29.5"</td><td>26.0"</td></tr>
                        <tr><td><strong>XL</strong></td><td>44&nbsp;&ndash;&nbsp;46"</td><td>19.0"</td><td>30.0"</td><td>26.5"</td></tr>
                        <tr><td><strong>XXL</strong></td><td>46&nbsp;&ndash;&nbsp;48"</td><td>19.5"</td><td>30.5"</td><td>27.0"</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
