<?php


if (!defined('ABSPATH')) {
	exit;
}


class Oc_Dr_Manager
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
		function doc_request_register_menu() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'oc_doc_request_list';
			$query_result = $wpdb->get_results("SELECT COUNT(*) as uncheck_number FROM $table_name WHERE is_accept = 0 AND post_status=1");
						
			if($query_result[0]->uncheck_number>0)
			{
				$bubble = sprintf(
					' <span class="update-plugins"><span class="update-count">%d</span></span>',
					$query_result[0]->uncheck_number //bubble contents
				);
			}
			else
				$bubble = '';
		
			$hook_event_list = add_menu_page(__( 'Data Request', 'oc-manager'), __( 'Data Request', 'oc-manager') . $bubble,'manage_options','doc_request','goto_doc_request_page','',26);
			add_submenu_page("admin.php?page=doc_request",__( 'Doc Request Detail', 'oc-manager' ), __( 'Doc Request Detail', 'oc-manager' ), "manage_options", "doc_request_detail", 'submenu_action_doc_request_detail');
			
			// if(isset($_GET['page']) && $_GET['page'] == 'doc_request' && oc_get_action('csv_download'))
			// 	ob_start();
		}
		
		function goto_doc_request_page(){
			require_once OC_MANAGER_PLUGIN_DIR . '/pages/doc_request.php';  
		}
		
		function submenu_action_doc_request_detail(){
			require_once OC_MANAGER_PLUGIN_DIR . '/pages/doc_request_detail.php';
		}
		
		add_action('admin_menu','doc_request_register_menu');

		function fontawesome_icon_dr_request_menu() {
			echo '<style type="text/css" media="screen">
			 icon16.icon-media:before, #toplevel_page_doc_request .toplevel_page_doc_request div.wp-menu-image:before {
			   font-family: "Font Awesome 5 Free" !important;
			   content: "\\f5da";
			   font-style:normal;
			   font-weight:900;
			 }
			</style>';
		  }
		add_action('admin_head', 'fontawesome_icon_dr_request_menu');
	}

	public function activate()
	{
		$this->oc_doc_request_db_install();
	}
	
	public function oc_doc_request_db_install() {
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'oc_doc_request_list';
		
		$sql = "CREATE TABLE `$table_name` (
			`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
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
			`content` text DEFAULT NULL,
			`is_accept` tinyint(1) NOT NULL DEFAULT 0,
			`accept_date` datetime DEFAULT NULL,
			`post_status` tinyint(1) NOT NULL DEFAULT 1,
			`create_date` datetime NOT NULL DEFAULT current_timestamp(),
			`update_date` datetime NOT NULL DEFAULT current_timestamp()
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
