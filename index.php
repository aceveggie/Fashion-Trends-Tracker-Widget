<?php
if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed');
/*------------------------------------------------------------------------------
Plugin Name: Fashion Trends Tracker
Plugin URI: http://www.rahulkavi.com/
Description: Widget to track fashion trends in your blog
Author: Rahul Kavi
Version: 0.1
Author URI: http://www.rahulkavi.com/
------------------------------------------------------------------------------*/
// include() or require() any necessary files here...
include_once('includes/FashionTrendTracker.php');
include_once('includes/FashionTrendTrackerWidget.php');


// Tie into WordPress Hooks and any functions that should run on load.
add_action('widgets_init', 'FashionTrendTrackerWidget::register_this_widget');

//add_action('admin_menu', 'FashionTrendTracker::add_menu_item');
// add_action( 'wp_enqueue_scripts', 'FashionTrendTracker::include_custom_ajax_call_scripts' );
// add_action( 'admin_init', 'FashionTrendTracker::add_js_to_admin_page' );

// load a post page load jquery ui css
add_action('widgets_init', 'FashionTrendTracker::add_js_css_to_post_page');
// add_action('widgets_init', 'FashionTrendTracker::include_latest_accordion_css_js');

/* EOF */
