<?php
if (!class_exists('Mantra_Audience_CPT')) {
    class Mantra_Audience_CPT
    {

        function __construct()
        {
            add_action('init', array($this, 'register_post_type'));

        }

        public function register_post_type()
        {
            $labels = array(
                'name' => _x('Popups', 'post type general name', 'mantra-audience'),
                'singular_name' => _x('Popup', 'post type singular name', 'mantra-audience'),
                'menu_name' => _x('Mantra Audiences', 'admin menu', 'mantra-audience'),
                'name_admin_bar' => _x('Popup', 'add new on admin bar', 'mantra-audience'),
                'add_new' => _x('Add New', 'book', 'mantra-audience'),
                'add_new_item' => __('Add New Popup', 'mantra-audience'),
                'new_item' => __('New Popup', 'mantra-audience'),
                'edit_item' => __('Edit Popup', 'mantra-audience'),
                'all_items' => __('Manage Popups', 'mantra-audience'),
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'mantra_audience'),
                'capability_type' => 'post',
                'menu_position' => 80, /* below Settings */
                'has_archive' => false,
                'supports' => array('title', 'editor'),
            );

            register_post_type('mantra_audience', $args);
        }
    }

}
return new Mantra_Audience_CPT();