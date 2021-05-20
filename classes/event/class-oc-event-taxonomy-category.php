<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;


class Oc_Event_Taxonomy_Category {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.26.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

        add_action( 'init', [ $this, 'register_taxonomy_category' ], 0 );
    }
    
	public function register_taxonomy_category() {
        $labels = array(
            'name'              => __( 'Oc Event Categories', 'oc-manager' ),
            'singular_name'     => __( 'Oc Event Category', 'oc-manager' ),
            'search_items'      => __( 'Search Oc Event Categories', 'oc-manager' ),
            'all_items'         => __( 'All Oc Event Categories', 'oc-manager' ),
            'edit_item'         => __( 'Edit Oc Event Category', 'oc-manager' ),
            'update_item'       => __( 'Update Oc Event Category', 'oc-manager' ),
            'add_new_item'      => __( 'Add New Oc Event Category', 'oc-manager' ),
            'new_item_name'     => __( 'New Oc Event Category Name', 'oc-manager' ),
            'menu_name'         => __( 'Oc Event Category', 'oc-manager' ),
        );
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
			'show_ui'           => true,
			'show_in_menu'      => true,
            'show_admin_column' => true,
			'query_var'         => true,
			'meta_box_cb'       => false,
            'rewrite'           => array( 'slug' => TAXONOMY_OC_EVENT_CATEGORY ),
        );
        register_taxonomy( TAXONOMY_OC_EVENT_CATEGORY, array( POST_TYPE_OC_EVENT ), $args );
    }

	public function unregister_taxonomy_category(){
		unregister_taxonomy(TAXONOMY_OC_EVENT_CATEGORY);
	}
}
