<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Content_Section
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_box_data']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_shortcode('handzom_content_section', [$this, 'render_shortcode']);
    }

    public function register_cpt()
    {
        $labels = [
            'name'               => 'Content Sections',
            'singular_name'      => 'Content Section',
            'menu_name'          => 'Content Sections',
            'add_new'            => 'Add New Section',
            'add_new_item'       => 'Add New Content Section',
            'edit_item'          => 'Edit Content Section',
            'new_item'           => 'New Content Section',
            'view_item'          => 'View Content Section',
            'search_items'       => 'Search Content Sections',
            'not_found'          => 'No sections found',
            'not_found_in_trash' => 'No sections found in Trash',
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-text-page',
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => ['title'],
        ];

        register_post_type('handzom_content', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'handzom_content_data',
            'Content Editor',
            [$this, 'render_meta_box'],
            'handzom_content',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('handzom_content_save', 'handzom_content_nonce');
        $data = get_post_meta($post->ID, '_handzom_content_data', true);
        if (empty($data)) {
            $data = '{"page_title":"","sections":[]}';
        }
        ?>
        <div id="handzom-content-editor-wrapper">
            <input type="hidden" id="handzom_content_json" name="handzom_content_json" value="<?php echo esc_attr($data); ?>">
            <div id="handzom-content-ui">
                <!-- React/Vanilla JS will mount here -->
            </div>
        </div>
        <?php
    }

    public function save_meta_box_data($post_id)
    {
        if (!isset($_POST['handzom_content_nonce']) || !wp_verify_nonce($_POST['handzom_content_nonce'], 'handzom_content_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['handzom_content_json'])) {
            $data = wp_unslash($_POST['handzom_content_json']);
            $decoded = json_decode($data);
            if ($decoded) {
                update_post_meta($post_id, '_handzom_content_data', wp_json_encode($decoded));
            }
        }
    }

    public function admin_scripts($hook)
    {
        global $post;
        if ($hook === 'post-new.php' || $hook === 'post.php') {
            if ($post && 'handzom_content' === $post->post_type) {
                wp_enqueue_style('handzom-admin-content', HANDZOM_UI_KIT_URL . 'content-section/assets/css/admin.css', [], HANDZOM_UI_KIT_VERSION);
                wp_enqueue_script('handzom-admin-content', HANDZOM_UI_KIT_URL . 'content-section/assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], HANDZOM_UI_KIT_VERSION, true);
            }
        }
    }

    public function frontend_scripts()
    {
        wp_register_style('handzom-frontend-content', HANDZOM_UI_KIT_URL . 'content-section/assets/css/frontend.css', [], HANDZOM_UI_KIT_VERSION);
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(['id' => 0], $atts, 'handzom_content_section');
        $post_id = intval($atts['id']);

        if (!$post_id || get_post_type($post_id) !== 'handzom_content') {
            return '';
        }

        $data_json = get_post_meta($post_id, '_handzom_content_data', true);
        if (!$data_json) {
            return '';
        }

        $data = json_decode($data_json, true);
        if (!$data) {
            return '';
        }

        wp_enqueue_style('handzom-frontend-content');

        ob_start();
        ?>
        <div class="handzom-content-section">
            <?php if (!empty($data['page_title'])): ?>
                <h1 class="content-page-title"><?php echo esc_html($data['page_title']); ?></h1>
                <hr class="content-divider">
            <?php endif; ?>

            <?php if (!empty($data['sections']) && is_array($data['sections'])): ?>
                <?php foreach ($data['sections'] as $section): ?>
                    <div class="content-section">
                        <?php if (!empty($section['title'])): ?>
                            <h2 class="content-section-title"><?php echo esc_html($section['title']); ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($section['subtitles']) && is_array($section['subtitles'])): ?>
                            <?php foreach ($section['subtitles'] as $subtitle): ?>
                                <div class="content-subtitle-block">
                                    <?php if (!empty($subtitle['title'])): ?>
                                        <h3 class="content-subtitle"><?php echo esc_html($subtitle['title']); ?></h3>
                                    <?php endif; ?>

                                    <?php if (isset($subtitle['type'])): ?>
                                        <?php if ($subtitle['type'] === 'none' && trim($subtitle['description']) !== ''): ?>
                                            <div class="content-description">
                                                <?php echo wp_kses_post(nl2br($subtitle['description'])); ?>
                                            </div>
                                        <?php elseif ($subtitle['type'] === 'bullet' && !empty($subtitle['points']) && count(array_filter(array_map('trim', $subtitle['points']))) > 0): ?>
                                            <ul class="content-bullet-points">
                                                <?php foreach ($subtitle['points'] as $point): ?>
                                                    <?php if(trim($point) !== ''): ?>
                                                        <li><?php echo esc_html($point); ?></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php elseif ($subtitle['type'] === 'number' && !empty($subtitle['points']) && count(array_filter(array_map('trim', $subtitle['points']))) > 0): ?>
                                            <ol class="content-numbered-points">
                                                <?php foreach ($subtitle['points'] as $point): ?>
                                                    <?php if(trim($point) !== ''): ?>
                                                        <li><?php echo esc_html($point); ?></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ol>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
