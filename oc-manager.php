<?php

/**
 * Plugin Name: OC Manager
 * Description: OC Event listings from the WordPress admin panel, and allow users to post Accept Request directly to your site.
 * Version: 1.0.0
 * Author: Kzar
 * Author URI: mailto:kzar1102@outlook.com?subject=OCManager
 * Requires at least: 4.9
 * Tested up to: 5.3
 * Requires PHP: 5.6
 * Text Domain: my-event-manager
 * License: GPL2+
 *
 */

if (!defined('ABSPATH')) {
    die;
}

// load plugin text domain
function oc_manager_load_plugin_textdomain() {

    $result = load_plugin_textdomain(
        'oc-manager',
        false, 
        dirname( plugin_basename( __FILE__ ) ) . '/translation' 
    );
}

add_action('plugins_loaded', 'oc_manager_load_plugin_textdomain');


// Include Functions
include("oc-functions.php");


// Define constants.
define('OC_MANAGER_VERSION', '1.0.0');
define('OC_MANAGER_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('OC_MANAGER_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('OC_MANAGER_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Event Post Type
define('POST_TYPE_OC_EVENT', 'event');
define('TAXONOMY_OC_EVENT_CATEGORY', 'event_category');
define('TAXONOMY_OC_EVENT_ROUTE', 'event_route');


// Global Managers
require_once dirname(__FILE__) . '/classes/class-oc-event-manager.php';
require_once dirname(__FILE__) . '/classes/class-oc-dr-manager.php';
require_once dirname(__FILE__) . '/classes/class-oc-enquiry-manager.php';

if(oc_get_action('csv_download'))
    ob_start();

$GLOBALS['oc_event_manager'] = new Oc_Event_Manager(POST_TYPE_OC_EVENT);
$GLOBALS['oc_dr_manager'] = new Oc_Dr_Manager();
$GLOBALS['oc_enquiry_manager'] = new Oc_Enquiry_Manager();


// Plugin Activation
register_activation_hook(__FILE__, 'oc_manager_activate');


function oc_manager_activate(){

    // Global Managers Activate
    global $oc_event_manager;
    global $oc_dr_manager;
    global $oc_enquiry_manager;

    $oc_event_manager->activate();
    $oc_dr_manager->activate();
    $oc_enquiry_manager->activate();
}


function enqueue($hook){
    wp_enqueue_style('fontawesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css', '', '5.11.2', 'all');
    wp_enqueue_style('oc_manager_style', plugins_url('/assets/oc_manager_style.css', __FILE__));

    if($hook == "event_page_request_detail" 
    || $hook == "toplevel_page_doc_request"
    || $hook == "toplevel_page_enquiry"
    || $hook == "event_page_event_detail" 
    || $hook == "event_page_unaccept_request_list" 
    || $hook == "admin_page_doc_request_detail" 
    || $hook == "admin_page_enquiry_detail")
    {
        wp_enqueue_style('oc_event_detail', plugins_url('assets/oc_event_detail.css', __FILE__));
    }

    if($hook == "post-new.php" || $hook == "post.php"){
        wp_enqueue_style('oc_event_cre_style', plugins_url('assets/oc_event_post_cre_style.css', __FILE__));
    }

}
add_action('admin_enqueue_scripts',  'enqueue');


// Plugin Deactivation
register_deactivation_hook(__FILE__, 'oc_manager_deactivate');


function oc_manager_deactivate(){
    flush_rewrite_rules();
}