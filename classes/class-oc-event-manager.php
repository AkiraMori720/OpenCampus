<?php


if (!defined('ABSPATH')) {
	exit;
}

global $wpdb;


class Oc_Event_Manager
{
	
	private static $instance = null;

	private $post_type;

	private $category;

	private $post_type_name;

	public static function instance($post_type_name)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($post_type_name);
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct($post_type_name)
	{
		// Include Class Files
		include_once OC_MANAGER_PLUGIN_DIR . '/classes/event/class-oc-event-post-type.php';
		
		// Inite 
        $this->post_type = Oc_Event_Post_Type::instance($post_type_name);
		
		$this->post_type_name = $post_type_name;
		
		// Taxonomy
		include_once OC_MANAGER_PLUGIN_DIR . '/classes/event/class-oc-event-taxonomy-category.php';

		$this->category = Oc_Event_Taxonomy_Category::instance($post_type_name);

		include_once OC_MANAGER_PLUGIN_DIR . '/classes/event/class-oc-event-taxonomy-route.php';

		$this->route = Oc_Event_Taxonomy_Route::instance($post_type_name);

		// Include Css
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));

		function fontawesome_icon_oc_event() {
			echo '<style type="text/css" media="screen">
			 icon16.icon-media:before, #menu-posts-event .menu-icon-event div.wp-menu-image:before {
			   font-family: "Font Awesome 5 Free" !important;
			   content: "\\f4fd";
			   font-style:normal;
			   font-weight:900;
			 }
			</style>';
		  }
		add_action('admin_head', 'fontawesome_icon_oc_event');
	}

	/**
	 * Performs plugin activation steps.
	 */
	public function activate()
	{
		$this->oc_event_db_install();
		flush_rewrite_rules();
	}

	public function oc_event_db_install() {
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'oc_request_list';
		
		$sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`last_name` varchar(64) NOT NULL,
			`first_name` varchar(64) NOT NULL,
			`last_name_furigana` varchar(64) NOT NULL,
			`first_name_furigana` varchar(64) NOT NULL,
			`sex` varchar(32) DEFAULT NULL,
			`email` varchar(64) DEFAULT NULL,
			`phone_number` varchar(64) DEFAULT NULL,
			`birthday` datetime DEFAULT NULL,
			`graduate_school` varchar(256) DEFAULT NULL,
			`grade` varchar(64) DEFAULT NULL,
			`postal_code_1` varchar(64) DEFAULT NULL,
			`postal_code_2` varchar(64) DEFAULT NULL,
			`address_prefecture` varchar(64) DEFAULT NULL,
			`address_municipality` varchar(64) DEFAULT NULL,
			`address_detail` varchar(256) DEFAULT NULL,
			`desire_subject` varchar(128) DEFAULT NULL,
			`is_use_accommodation` tinyint(1) NOT NULL DEFAULT 1,
			`is_attend_in_parent_priefing` tinyint(1) NOT NULL DEFAULT 1,
			`is_use_bus` int(11) NOT NULL DEFAULT 0,
			`meeting_place` varchar(256) DEFAULT NULL,
			`course_name` varchar(512) DEFAULT NULL,
			`sub_course_name` varchar(512) DEFAULT NULL,
			`content` text DEFAULT NULL,
			`is_accept` tinyint(1) NOT NULL DEFAULT 0,
			`accept_date` datetime DEFAULT NULL,
			`post_status` tinyint(1) NOT NULL DEFAULT 1,
			`create_date` datetime NOT NULL DEFAULT current_timestamp(),
			`update_date` datetime NOT NULL DEFAULT current_timestamp()
		  )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function enqueue($hook){
		// set global
		global $post_type, $taxonomy;
		// end set global
		if( $this->post_type_name != $post_type)
			return;
		wp_enqueue_style( 'oc_event_style', plugin_dir_url(__DIR__) . 'assets/oc_event_post_style.css');

		if($hook == 'post-new.php' || $hook == 'post.php'){
			wp_enqueue_media();
			wp_enqueue_editor();
			wp_enqueue_script('oc_evet_post_script', plugin_dir_url(__DIR__) . 'assets/oc_event_post_script.js', array('jquery', 'jquery-ui-datepicker') );
			wp_enqueue_style( 'oc_datepicker_style', plugin_dir_url(__DIR__) . 'assets/oc_datepicker.min.css');
		}
	}
}

