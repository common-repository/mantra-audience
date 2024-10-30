<?php
if (!class_exists('Mantra_Audience_Main')) {
    class Mantra_Audience_Main
    {

        private static $instance = null;

        /**
         * Creates or returns an instance of this class.
         *
         * @return   A single instance of this class.
         */
        public static function get_instance()
        {
            return null == self::$instance ? self::$instance = new self : self::$instance;
        }

        private function __construct()
        {
            add_action('init', array($this, 'i18n'), 5);
             add_filter('mantra_audience_do_metaboxes', array($this, 'meta_box'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
            add_filter('wp_nav_menu_objects', array($this, 'wp_nav_menu_objects'));
            add_filter('mce_external_plugins', array($this, 'mce_external_plugins'));
            add_filter('mce_buttons', array($this, 'mce_buttons'));
            add_action('wp_enqueue_scripts', array($this, 'tinymce_localize'));
            add_action('admin_enqueue_scripts', array($this, 'tinymce_localize'));
            add_filter('template_include', array($this, 'template_include'), 100);
            add_action('template_redirect', array($this, 'hooks'));
            add_action('wp_footer', array($this, 'footer_script'), 2);


        }

        public function hooks()
        {
            if (!is_singular('mantra_audience')) {
                add_action('wp_footer', array($this, 'render'), 1);
                add_action('wp_enqueue_scripts', array($this, 'enqueue'), 13);
                add_action('wp_footer', array($this, 'loader'), 2);
                add_shortcode('ma_popup', array($this, 'shortcode'));

                $this->update_page_view();
            } else {
                if (!current_user_can('manage_options')) {
                    wp_redirect(home_url());
                    exit;
                }
            }
        }

        public function i18n()
        {
            load_plugin_textdomain('mantra-audience', false, MANTRA_AUDIENCE_DIR . 'languages/');
        }



        function meta_box($panels)
        {
            $options = include($this->get_view_path('config.php'));
            $panels[] = array(
                "name" => __('Popup Settings', 'mantra-audience'),
                'id' => 'mantra-audience',
                "options" => $options,
                "pages" => "mantra_audience"
            );
            $panels[] = array(
                'name' => __('Custom CSS', 'mantra-audience'),
                'id' => 'mantra-audience-css',
                "options" => array(
                    array(
                        'name' => 'custom_css',
                        'title' => __('Custom CSS', 'mantra-audience'),
                        'type' => 'textarea',
                        'size' => 55,
                        'rows' => 25,
                        'description' => __('You can use <code>%POPUP%</code> to reference this popup.', 'mantra-audience'),
                    ),
                ),
                "pages" => "mantra_audience"
            );

            return $panels;
        }

        function is_admin_screen()
        {
            global $hook_suffix, $post;
            if (('post.php' == $hook_suffix || 'post-new.php' == $hook_suffix) && $post->post_type == 'mantra_audience') {
                return true;
            }
            return false;
        }

        public function admin_enqueue()
        {
            global $post, $hook_suffix;

            if (!$this->is_admin_screen())
                return;

            wp_enqueue_script('mantra-audience', MANTRA_AUDIENCE_URI . 'assets/js/admin.js', array('jquery'), MANTRA_AUDIENCE_VERSION, true);
        }

        function get_popups()
        {
            $datenow = date_i18n('Y-m-d H:i:s');
            $args = array(
                'post_type' => 'mantra_audience',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'popup_start_at',
                        'value' => $datenow,
                        'compare' => '<=',
                        'type' => 'datetime'
                    ),
                    array(
                        'key' => 'popup_end_at',
                        'value' => $datenow,
                        'type' => 'datetime',
                        'compare' => '>='
                    )
                )
            );
            if (class_exists('SitePress')) {
                /*
                * For some unknown reason WPML 4.0.2 will not render posts for other languages if suppress_filters or posts_per_page value is not a string type.
                */
                $args['suppress_filters'] = '0';
            }
            $the_query = new WP_Query();
            $args = apply_filters("mantra_audience_query_args", $args);
            $posts = $the_query->query($args);

            return $posts;
        }

        public function get_view_path($name)
        {
            if (locate_template('mantra-audience/' . $name)) {
                return locate_template('mantra-audience/' . $name);
            } elseif (file_exists(MANTRA_AUDIENCE_DIR . 'includes/views/' . $name)) {
                return MANTRA_AUDIENCE_DIR . 'includes/views/' . $name;
            }

            return false;
        }

        public function load_view($name, $data = array())
        {
            extract($data);
            if ($view = $this->get_view_path($name)) {
                ob_start();
                include($view);
                return ob_get_clean();
            }

            return '';
        }

        function render()
        {
            do_action('mantra_audience_before_render');

            echo $this->load_view('render.php', array(
                'popups' => $this->get_popups()
            ));

            do_action('mantra_audience_after_render');
        }

        public function enqueue()
        {
            wp_enqueue_script('jquery');

        }

        function loader()
        {
            include($this->get_view_path('loader.php'));
        }

        public function get_element_attributes($props)
        {
            $out = '';
            foreach ($props as $atts => $val) {
                if (!in_array($atts, array('id', 'class', 'style')) && substr($atts, 0, 5) != 'data-') {
                    $atts = 'data-' . $atts;
                }
                $out .= ' ' . $atts . '="' . esc_attr($val) . '"';
            }
            return $out;
        }

        /**
         * Fix URLs in menu items pointing to an inline popup
         */
        function wp_nav_menu_objects($items)
        {
            foreach ($items as $item) {
                if ($item->type == 'post_type' && $item->object == 'mantra_audience') {
                    $item->url = '#mantra-audience-' . $item->object_id;
                    $item->classes[] = 'ma-popup';
                }
            }

            return $items;
        }

        function update_page_view()
        {
            $days = 1;
            $count = isset($_COOKIE['mantra_audience_page_view']) ? (int)$_COOKIE['mantra_audience_page_view'] + 1 : 1;
            setcookie('mantra_audience_page_view', $count, time() + 3600 * 24 * $days, COOKIEPATH, COOKIE_DOMAIN, false);
        }

        /**
         * Add plugin JS file to list of external plugins.
         *
         * @param array $mce_external_plugins
         * @return mixed
         */
        function mce_external_plugins($mce_external_plugins)
        {
            global $wp_version;
            $mce_external_plugins['mcemantraAudiencePopup'] = MANTRA_AUDIENCE_URI . 'assets/js/tinymce.js';

            return $mce_external_plugins;
        }

        function mce_buttons($mce_buttons)
        {
            array_push($mce_buttons, 'separator', 'mcemantraAudiencePopup');
            return $mce_buttons;
        }

        function tinymce_localize()
        {
            $fields = include($this->get_view_path('shortcode-fields.php'));
            wp_localize_script('editor', 'mcemantraAudiencePopup', array(
                'fields' => $fields,
                'labels' => array(
                    'menuName' => __('Mantra Audience', 'mantra-audience'),
                )
            ));


        }

        function get_manual_popup_list()
        {
            $list = array();
            $args = array(
                'post_type' => 'mantra_audience',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'nopaging' => true,
                'meta_query' => array(
                    array(
                        'key' => 'popup_trigger',
                        'value' => 'manual',
                        'compare' => '=',
                    ),
                )
            );
            if (class_exists('SitePress')) {
                /*
                * This will insure that only wpml current language related popups are shown in shortcode popup list.
                */
                $args['suppress_filters'] = '0';
            }
            $query = get_posts($args);
            if (!empty($query)) {
                foreach ($query as $popup) {
                    $list[] = array('text' => $popup->post_title, 'value' => $popup->ID);
                }
            }

            return $list;
        }

        function shortcode($atts, $content = null)
        {
            extract(shortcode_atts(array(
                'color' => '',
                'size' => '',
                'style' => '',
                'link' => 0,
                'target' => '',
                'text' => ''
            ), $atts, 'ma_popup'));

            if (!$post = get_post($link)) {
                return;
            }

            if ($color) {
                $color = "background-color: $color;";
            }
            if ($text) {
                $text = "color: $text;";
            }
            $html = '<a href="#mantra-audience-' . $link . '" class="ma_popup ' . esc_attr($style . ' ' . $size) . '"';
            if ($color || $text) {
                $html .= ' style="' . esc_attr($color . $text) . '"';
            }
            if ($target) {
                $html .= ' target="' . esc_attr($target) . '"';
            }
            $html .= '>' . do_shortcode($content) . '</a>';

            return $html;
        }

        /**
         * Use custom template file on popup single pages
         *
         * @since 1.0.0
         */
        function template_include($template)
        {
            if (is_singular('mantra_audience')) {
                $template = $this->get_view_path('single-popup.php');
            }

            return $template;
        }

        /**
         * Checks whether a popup should be displayed or not
         *
         * @since 1.0.0
         * @return bool
         */
        function is_popup_visible($id)
        {
            $visible = true;

            // popup is disabled for mobile
            if (mantra_audience_check('popup_mobile_disable') && wp_is_mobile()) {
                $visible = false;
            }

            // has user seen this popup before?
            /**
             * Migration routine: previsouly used "show_once" checkbox is converted to "limit_count" (number).
             */
            if (mantra_audience_check('popup_show_once')) {
                delete_post_meta($id, 'popup_show_once');
                add_post_meta($id, 'popup_limit_count', 1);
            }
            if (mantra_audience_check('popup_limit_count') && isset($_COOKIE["mantra-audience-{$id}"]) && $_COOKIE["mantra-audience-{$id}"] >= mantra_audience_get('popup_limit_count')) {
                $visible = false;
            }

            // check if popup has a page view limit
            if ($view_count = mantra_audience_get('popup_page_view', 0)) {
                if (!(isset($_COOKIE['mantra_audience_page_view']) && $_COOKIE['mantra_audience_page_view'] >= $view_count)) {
                    $visible = false;
                }
            }

            if (mantra_audience_get('popup_show_on_toggle', 'all-pages') == 'specific-pages' && mantra_audience_check('popup_show')) {
                if (!mantra_audience_verify_assignments(mantra_audience_get('popup_show'))) {
                    $visible = false;
                }
            }

            if ((mantra_audience_get('popup_show_to') == 'guest' && is_user_logged_in())
                || (mantra_audience_get('popup_show_to') == 'user' && !is_user_logged_in())
            ) {
                $visible = false;
            }

            return $visible;
        }

        function footer_script()
        {
            global $mantra_audience_custom_css;

            wp_enqueue_script(
                'mantra-lightbox',
                MANTRA_AUDIENCE_URI . 'assets/js/lightbox.min.js'
            );

            wp_enqueue_script(
                'mantra-custom-script',
                MANTRA_AUDIENCE_URI . 'assets/js/scripts.js'
            );

            wp_enqueue_style(
                'mantra-audience-builder-animate',
                MANTRA_AUDIENCE_URI . 'assets/css/animate.min.css'
            );

             wp_enqueue_style(
                'magnific',
                MANTRA_AUDIENCE_URI . 'assets/css/lightbox.css'
            );

             wp_enqueue_style(
                'mantra-audience-style',
                MANTRA_AUDIENCE_URI . 'assets/css/styles.css'
            );

            wp_add_inline_style('mantra-audience-style', $mantra_audience_custom_css);


        }

    }

    Mantra_Audience_Main::get_instance();
}

/**
 * Check if option is set for the current popup in the loop
 *
 * @since 1.0.0
 */
function mantra_audience_check($var)
{
    global $post;

    if (is_object($post) && get_post_meta($post->ID, $var, true) != '' && get_post_meta($post->ID, $var, true)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get an option for the current popup in the loop
 *
 * @since 1.0.0
 */
function mantra_audience_get($var, $default = null)
{
    global $post;

    if (is_object($post) && get_post_meta($post->ID, $var, true) !== '') {
        return get_post_meta($post->ID, $var, true);
    } else {
        return $default;
    }
}

/**
 * Return the custom CSS codes for current popup (in the loop)
 *
 * @return string
 */
function mantra_audience_get_custom_css()
{
    $css = mantra_audience_get('custom_css');
    $css = str_replace('%POPUP%', '#mantra-audience-' . get_the_id(), $css);

    return $css;
}
