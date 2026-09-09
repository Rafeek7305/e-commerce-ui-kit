<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HZ Variation Extra Images Admin Component
 * Adds "Variation Extra Images" section inside WooCommerce variation edit panels.
 */
class HZ_Variation_Extra_Images
{
    private static $instance = null;
    const META_KEY = '_hzsp_variation_extra_image_ids';

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        if (!is_admin()) {
            return;
        }

        // Admin scripts & styles for product edit screens
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // WooCommerce variation hooks
        add_action('woocommerce_product_after_variable_attributes', [$this, 'render_variation_extra_images_field'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'save_variation_extra_images'], 10, 2);
    }

    /**
     * Enqueue WP Media, jQuery UI sortable, and custom admin assets on Product Edit pages
     */
    public function enqueue_admin_assets($hook)
    {
        global $post_type;

        if (in_array($hook, ['post.php', 'post-new.php'], true) && $post_type === 'product') {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');

            $base_dir = str_replace('\\', '/', __DIR__);
            $wp_content_dir = str_replace('\\', '/', WP_CONTENT_DIR);

            if (strpos($base_dir, $wp_content_dir) !== false) {
                $rel_path = str_replace($wp_content_dir, '', $base_dir);
                $css_url = content_url($rel_path . '/assets/variation-extra-images.css');
                $js_url  = content_url($rel_path . '/assets/variation-extra-images.js');
            } else {
                $css_url = plugins_url('assets/variation-extra-images.css', __FILE__);
                $js_url  = plugins_url('assets/variation-extra-images.js', __FILE__);
            }

            wp_enqueue_style('hzsp-variation-extra-images', $css_url, [], '1.0.0');
            wp_enqueue_script('hzsp-variation-extra-images', $js_url, ['jquery', 'jquery-ui-sortable', 'media-upload'], '1.0.0', true);
        }
    }

    /**
     * Render Variation Extra Images field inside WooCommerce variation panel
     */
    public function render_variation_extra_images_field($loop, $variation_data, $variation)
    {
        $variation_id = is_object($variation) ? $variation->ID : (int)$variation;
        if (!$variation_id) {
            return;
        }

        $raw_ids = get_post_meta($variation_id, self::META_KEY, true);
        $image_ids = [];

        if (!empty($raw_ids)) {
            if (is_array($raw_ids)) {
                $image_ids = array_filter(array_map('absint', $raw_ids));
            } else {
                $image_ids = array_filter(array_map('absint', explode(',', (string)$raw_ids)));
            }
        }

        $ids_string = implode(',', $image_ids);
        ?>
        <div class="hzsp-variation-extra-images-wrap form-row form-row-full">
            <div class="hzsp-vei-header">
                <h4 class="hzsp-vei-title"><?php esc_html_e('Variation Extra Images', 'handzom-ui-kit'); ?></h4>
                <button type="button" class="button button-secondary hzsp-vei-add-btn">
                    <?php esc_html_e('Add Images', 'handzom-ui-kit'); ?>
                </button>
            </div>

            <p class="hzsp-vei-empty-msg" style="<?php echo !empty($image_ids) ? 'display:none;' : ''; ?>">
                <?php esc_html_e('No extra images selected.', 'handzom-ui-kit'); ?>
            </p>

            <div class="hzsp-vei-thumbnails">
                <?php foreach ($image_ids as $img_id) :
                    $thumb_src = wp_get_attachment_image_url($img_id, 'thumbnail');
                    if (!$thumb_src) {
                        continue;
                    }
                ?>
                    <div class="hzsp-vei-thumb" data-id="<?php echo esc_attr($img_id); ?>">
                        <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php esc_attr_e('Extra Image', 'handzom-ui-kit'); ?>">
                        <button type="button" class="hzsp-vei-remove-btn" title="<?php esc_attr_e('Remove image', 'handzom-ui-kit'); ?>">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden"
                   name="hzsp_variation_extra_images[<?php echo esc_attr($variation_id); ?>]"
                   class="hzsp-vei-input"
                   value="<?php echo esc_attr($ids_string); ?>">
        </div>
        <?php
    }

    /**
     * Save variation extra images when variation data is saved
     */
    public function save_variation_extra_images($variation_id, $i)
    {
        if (!current_user_can('edit_products')) {
            return;
        }

        // Verify nonce if WooCommerce meta nonce is present
        if (isset($_POST['woocommerce_meta_nonce']) && !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }

        if (isset($_POST['hzsp_variation_extra_images'][$variation_id])) {
            $raw_val = $_POST['hzsp_variation_extra_images'][$variation_id];
            $clean_ids = [];

            if (is_array($raw_val)) {
                $clean_ids = array_filter(array_map('absint', $raw_val));
            } else {
                $clean_ids = array_filter(array_map('absint', explode(',', (string)$raw_val)));
            }

            if (!empty($clean_ids)) {
                update_post_meta($variation_id, self::META_KEY, implode(',', $clean_ids));
            } else {
                delete_post_meta($variation_id, self::META_KEY);
            }
        }
    }
}

// Initialize admin component
HZ_Variation_Extra_Images::instance();
