<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;


class Oc_Event_Post_Type {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $instance = null;

    private $post_type_name = "";

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.26.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance($post_type_name) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self($post_type_name);
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct($post_type_name) {
       
        $this->post_type_name = $post_type_name;
        
        require_once OC_MANAGER_PLUGIN_DIR . '/classes/base/oc-event-post-admin.php';

        // Register Post Type
        add_action( 'init', [ $this, 'register_event_post_type' ], 0 );
        
        
        $post_editor = new Oc_Event_Post_Admin($this->post_type_name);
        add_action('admin_init', array($post_editor,'init'));

    }
    

	/**
	 * Register the post type.
	 */
	public function register_event_post_type() {
        $event_labels = array(
            'name' => __( 'Oc Event', 'oc-manager' ),
            'singular_name' => __( 'Oc Event', 'oc-manager' ),
            'all_items' => __( 'All Oc Events', 'oc-manager' ),
            'add_new_item' => __( 'Add New Oc Event', 'oc-manager' ),
            'add_new' => __( 'New Oc Event', 'oc-manager' ),
            'new_item' => __( 'New Oc Event', 'oc-manager' ),
            'edit_item' => __( 'Edit Oc Event', 'oc-manager' ),
            'view_item' => __( 'View Oc Event', 'oc-manager' ),
            'search_items' => __( 'Search Oc Events', 'oc-manager' ),
            'not_found' => __( 'No events found', 'oc-manager' ),
            'not_found_in_trash' => __( 'No events found in Trash', 'oc-manager' )
        );
        $event_args = array(
            'labels' => $event_labels,
            'menu_icon' => '',
            'public' => true,
            'can_export' => true,
            'show_in_nav_menus' => true,
            'has_archive' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'taxonomies' => array( 'event_cat' ),
            'rewrite' => array( 'slug' => $this->post_type_name ),
            'supports' => ['title'],
            'menu_position' => 25
        );
        register_post_type( $this->post_type_name, $event_args );
        
        // Customize OC_Event Admin Menu Title
        add_action('admin_menu','change_event_type_admin_menu');
        function change_event_type_admin_menu() {
            global $menu;
            global $wpdb;
            $table_name = $wpdb->prefix . 'oc_request_list';
            $query_result = $wpdb->get_results("SELECT * FROM wp_posts as wp JOIN $table_name as wo ON wp.ID = wo.post_id WHERE wp.post_status <> 'trash' and wo.post_status=1 and wo.is_accept = 0 GROUP BY wp.ID");
                        
            if($query_result && count($query_result)>0)
            {
                $bubble = sprintf(
                    ' <span class="update-plugins"><span class="update-count">%d</span></span>',
                    count($query_result) //bubble contents
                );
            }
            else
                $bubble = '';
            foreach ( $menu as $index => $item ) {
                if ( $item[0] == __( 'Oc Event', 'oc-manager' ))
                    $menu[$index][0] = __( 'Oc Event', 'oc-manager' ).$bubble;
            }
        }

        // Add Sub Menu in OC_Event Admin Menu
        add_action( 'admin_menu', array($this, 'add_submenu'));

        // Remove Metabox in OC_EVENT Post Edit Page
        add_action( 'add_meta_boxes', 'remove_metaboxes', 11 );
        function remove_metaboxes(){
            remove_meta_box('slugdiv', 'event', 'normal'); // Slug
            //remove_meta_box('submitdiv', 'event', 'side'); // Publish box
        }
    }
    
    public function add_submenu() {
        add_submenu_page("edit.php?post_type=".$this->post_type_name, "全ての未対応者", "全ての未対応者", "manage_options", "unaccept_request_list", array($this, 'submenu_action_unaccept_request_list'), 1);
        add_submenu_page("edit.php?post_type=".$this->post_type_name, "Event Detail", "", "manage_options", "event_detail", array($this, 'submenu_action_event_detail'));
        add_submenu_page("edit.php?post_type=".$this->post_type_name, "Request Detail", "", "manage_options", "request_detail", array($this, 'submenu_action_request_detail'));
    }
    
    public function submenu_action_unaccept_request_list(){
        require_once OC_MANAGER_PLUGIN_DIR . '/pages/unaccept_request_list.php';
    }

    public function submenu_action_event_detail(){
        require_once OC_MANAGER_PLUGIN_DIR . '/pages/request_list.php';
    }

    public function submenu_action_request_detail(){
        require_once OC_MANAGER_PLUGIN_DIR . '/pages/request_detail.php';
    }

	public function unregister_event_post_type(){
		unregister_post_type($this->post_type_name);
	}
}
