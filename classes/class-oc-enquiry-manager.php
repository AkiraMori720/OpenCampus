<?php

if (!defined('ABSPATH')) {
	exit;
}

class Oc_Enquiry_Manager
{

	private static $instance = null;


	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
    public function __construct()
	{
        function enqury_register_menu() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'oc_enquiry_list';
			$query_result = $wpdb->get_results("SELECT COUNT(*) as uncheck_number FROM $table_name WHERE is_accept = 0 AND post_status=1");
			
			if($query_result[0]->uncheck_number > 0)
				$bubble = sprintf(
					' <span class="update-plugins"><span class="update-count">%d</span></span>',
					$query_result[0]->uncheck_number //bubble contents
				);
			else
				$bubble = '';

			$hook_event_list = add_menu_page(__( 'Enquiry', 'oc-manager'), __( 'Enquiry', 'oc-manager'). $bubble ,'manage_options','enquiry','goto_enquiry_page','',26);
			add_submenu_page("admin.php?page=enquiry", __( 'Enquiry Detail', 'oc-manager' ), __( 'Enquiry Detail', 'oc-manager' ), "manage_options", "enquiry_detail", 'submenu_action_doc_enquiry_detail');
			
		}
        
        function goto_enquiry_page(){
            require_once OC_MANAGER_PLUGIN_DIR . '/pages/enquiry.php';  
		}
		
		function submenu_action_doc_enquiry_detail(){
			require_once OC_MANAGER_PLUGIN_DIR . '/pages/enquiry_detail.php';
		}
        
		add_action('admin_menu','enqury_register_menu');
		
		function fontawesome_icon_enquiry_menu() {
			echo '<style type="text/css" media="screen">
			 icon16.icon-media:before, #toplevel_page_enquiry .toplevel_page_enquiry div.wp-menu-image:before {
			   font-family: "Font Awesome 5 Free" !important;
			   content: "\\f075";
			   font-style:normal;
			   font-weight:900;
			 }
			</style>';
		  }
		add_action('admin_head', 'fontawesome_icon_enquiry_menu');
        
	}

	/**
	 * Performs plugin activation steps.
	 */
	public function activate()
	{
		$this->oc_enquiry_db_install();
	}

	public function oc_enquiry_db_install() {
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'oc_enquiry_list';
		$memo_table_name = $wpdb->prefix . "oc_enquiry_memo";

		$sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`last_name` varchar(64) NOT NULL,
			`first_name` varchar(64) NOT NULL,
			`email` varchar(64) DEFAULT NULL,
			`phone_number` varchar(64) DEFAULT NULL,
			`graduate_school` varchar(256) DEFAULT NULL,
			`grade` varchar(64) DEFAULT NULL,
			`desire_subject` varchar(128) DEFAULT NULL,
			`content` text DEFAULT NULL,
			`is_accept` tinyint(1) NOT NULL DEFAULT 0,
			`accept_date` datetime DEFAULT NULL,
			`post_status` tinyint(1) NOT NULL DEFAULT 1,
			`create_date` datetime NOT NULL DEFAULT current_timestamp(),
			`update_date` datetime NOT NULL DEFAULT current_timestamp()
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$sql = "CREATE TABLE `$memo_table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`enquiry_id` int(11) NOT NULL,
			`memo` text NOT NULL DEFAULT '',
			`create_date` datetime NOT NULL DEFAULT current_timestamp()
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
