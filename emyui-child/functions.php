<?php
/*
 * This is the child theme for emyui theme, generated with Generate Child Theme.
 */
define('EMUI_DIR_URI',  get_stylesheet_directory_uri());
define('EMUI_DIR',      get_stylesheet_directory());
define('EMUI_ASSETS',   EMUI_DIR_URI . '/assets');
define('EMUI_CSS',      EMUI_ASSETS . '/css');
define('EMUI_JS',       EMUI_ASSETS . '/js');
define('EMUI_IMAGES',   EMUI_ASSETS . '/images');
define('EMUI_CLASSES',  EMUI_DIR . '/classes');
define('EMUI_VIEWS',  EMUI_DIR . '/views');
add_action( 'wp_enqueue_scripts', 'emyui_child_enqueue_styles' );
function emyui_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
    wp_enqueue_script('custom.js', EMUI_JS.'/custom.js?time='.time(), false, null, true);
    wp_localize_script('custom.js', 'load_emyui', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'required' => __('This field is required.', 'emyui')
    ]);
}
require_once(EMUI_CLASSES.'/emyui-main.php');
require_once(EMUI_CLASSES.'/emyui-api.php');
require_once(EMUI_CLASSES.'/emyui-package.php');

