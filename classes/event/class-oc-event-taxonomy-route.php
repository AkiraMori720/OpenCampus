<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;


class Oc_Event_Taxonomy_Route {
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

		// Include Css
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));

		// Init
		add_action( 'init', [ $this, 'register_taxonomy_route' ], 0 );

		// Add the fields to the "order_no" taxonomy, using our callback function  
		add_action( TAXONOMY_OC_EVENT_ROUTE.'_add_form_fields', [$this, 'event_route_add_order_no'], 10, 2 );  

		// Edit the fields to the "order_no" taxonomy, using our callback function  
		add_action( TAXONOMY_OC_EVENT_ROUTE.'_edit_form_fields', [$this, 'event_route_edit_order_no'], 10, 2 );  

		// Save the changes made on the "order_no" taxonomy, using our callback function  
		add_action( 'create_'.TAXONOMY_OC_EVENT_ROUTE, [$this, 'save_taxonomy_custom_fields'], 10, 2 );  
		add_action( 'edited_'.TAXONOMY_OC_EVENT_ROUTE, [$this, 'save_taxonomy_custom_fields'], 10, 2 );  

		// Operation in event_type Taxonomy Table
		function register_display_columns( $columns ) {
			$new_columms = [];
			foreach($columns as $key => $value){
				$new_columms[$key] = $value;
				if($key == 'name')
					$new_columms['order_no'] = __('表示順', 'oc-manager');
			}
			return $new_columms;
		}
		add_filter( 'manage_edit-'.TAXONOMY_OC_EVENT_ROUTE.'_columns', 'register_display_columns' );

		if(!function_exists('mbe_change_sortable_columns')){
			function mbe_change_sortable_columns($columns){
				$columns['order_no'] = 'order_no';
				return $columns;
			}
			add_filter('manage_edit-'.TAXONOMY_OC_EVENT_ROUTE.'_sortable_columns', 'mbe_change_sortable_columns');
		}

		function registe_column_display( $string = '', $column_name, $term_id ) {
			return esc_html( get_term_meta( $term_id, $column_name, true ) ); // XSS ok.
		}
		add_filter( 'manage_'.TAXONOMY_OC_EVENT_ROUTE.'_custom_column', 'registe_column_display', 10, 3 );

		function filter_terms_clauses( $pieces, $taxonomies, $args ) {

			global $pagenow, $wpdb;
			
			if(!is_admin()) {
				return $pieces;
			}
			if(
				is_admin() 
				&& $pagenow == 'edit-tags.php' 
				&& $taxonomies[0] == TAXONOMY_OC_EVENT_ROUTE
				&& ( isset($_GET['orderby']) && $_GET['orderby'] == 'order_no' )
			) {
				$pieces['join']     .= ' INNER JOIN ' . $wpdb->termmeta . ' AS tm ON t.term_id = tm.term_id ';
				$pieces['where']    .= ' AND tm.meta_key = "order_no"'; 
				$pieces['orderby']   = ' ORDER BY CAST(tm.meta_value AS SIGNED)';
				$pieces['order']     = isset($_GET['order']) ? $_GET['order'] : "DESC";
			}
			return $pieces;
		}
		
		add_filter( 'terms_clauses', 'filter_terms_clauses', 10, 3 );

		// Quick Edit 
		function quick_edit_category_field( $column_name, $screen ) {
			if ( $screen != TAXONOMY_OC_EVENT_ROUTE && $column_name != 'order_no' ) {
				return false;
			}
			?>
			<fieldset>
				<div id="order_no" class="inline-edit-col">
					<label>
						<span class="title"><?php echo __('表示順', 'oc-manager'); ?></span>
						<span class="input-text-wrap"><input type="number" name="<?php echo esc_attr( $column_name ); ?>" class="ptitle" value="" aria-required="true"></span>
					</label>
				</div>
			</fieldset>
			<?php
		}
		add_action( 'quick_edit_custom_box', 'quick_edit_category_field', 10, 2 );

		function quick_edit_save_category_field( $term_id ) {
			$term_order_no = $_POST['order_no']?$_POST['order_no']:0;  
			update_term_meta( $term_id, 'order_no', $term_order_no);
		}
		add_action( 'edited_'.TAXONOMY_OC_EVENT_ROUTE, 'quick_edit_save_category_field' );

		function quickedit_category_javascript() {
			$current_screen = get_current_screen();
		
			if ( $current_screen->id != 'edit-'.TAXONOMY_OC_EVENT_ROUTE || $current_screen->taxonomy != TAXONOMY_OC_EVENT_ROUTE ) {
				return;
			}
		
			// Ensure jQuery library is loaded
			wp_enqueue_script( 'jquery' );
			?>
			<script type="text/javascript">
				/*global jQuery*/
				jQuery(function($) {
					$('#the-list').on( 'click', 'button.editinline', function( e ) {
						e.preventDefault();
						var $tr = $(this).closest('tr');
						var val = $tr.find('td.order_no').text();
						// Update field
						$('tr.inline-edit-row :input[name="order_no"]').val(val ? val : '');
					});
				});
			</script>
			<?php
		}
		add_action( 'admin_print_footer_scripts-edit-tags.php', 'quickedit_category_javascript'); 
    }
	
	
	public function enqueue($hook){
		// set global
		global $post_type, $taxonomy;
		// end set global
		if( POST_TYPE_OC_EVENT != $post_type || TAXONOMY_OC_EVENT_ROUTE != $taxonomy)
			return;
		wp_enqueue_style( 'oc_event_route_style', plugin_dir_url(__DIR__).'../assets/oc_tax_event_route_style.css');
	}

	public function register_taxonomy_route() {
        $labels = array(
            'name'              => __( 'Oc Event Routes', 'oc-manager' ),
            'singular_name'     => __( 'Oc Event Route', 'oc-manager' ),
            'search_items'      => __( 'Search Oc Event Routes', 'oc-manager' ),
            'all_items'         => __( 'All Oc Event Routes', 'oc-manager' ),
            'edit_item'         => __( 'Edit Oc Event Route', 'oc-manager' ),
            'update_item'       => __( 'Update Oc Event Route', 'oc-manager' ),
            'add_new_item'      => __( 'Add New Oc Event Route', 'oc-manager' ),
            'new_item_name'     => __( 'New Oc Event Route Name', 'oc-manager' ),
            'menu_name'         => __( 'Oc Event Route', 'oc-manager' ),
        );
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
			'show_ui'           => true,
			'show_in_menu'      => true,
            'show_admin_column' => true,
			'query_var'         => true,
			'meta_box_cb'       => false,
            'rewrite'           => array( 'slug' => TAXONOMY_OC_EVENT_ROUTE ),
        );
		register_taxonomy( TAXONOMY_OC_EVENT_ROUTE, array( POST_TYPE_OC_EVENT ), $args );
	}
	
			
	public function event_route_add_order_no($term) {  
	 ?>  
	   
	 <div class="form-field term-order-warp">  
		<label for="order_no"><?php echo __('表示順', 'oc-manager'); ?></label>  
		<input type="number" name="order_no" id="order_no" size="40" aria-required="true"><br />  
		<p class="description"><?php echo __('バスルートは「表示順」でソートされます。', 'oc-manager'); ?></p>  
	 </div>  
	   
	 <?php  
	 }  

	 public function event_route_edit_order_no($term) {  
		$t_id = $term->term_id;

		$order_no = get_term_meta( $t_id, 'order_no', true );
		?>  
		  
		<tr class="form-field term-order-warp">  
			<th>
		   		<label for="order_no"><?php echo __('表示順', 'oc-manager'); ?></label>  
			</th>
			<td>
		   		<input type="number" name="order_no" id="order_no" size="40" value="<?php echo esc_attr($order_no)?esc_attr($order_no):'' ?>" aria-required="true"><br />  
		   		<p class="description"><?php echo __('バスルートは「表示順」でソートされます。', 'oc-manager'); ?></p>  
			</td>
		</tr>  
		  
		<?php  
	}  
	
	public function save_taxonomy_custom_fields( $term_id ) {  
		$t_id = $term_id;  
		$term_order_no = $_POST['order_no']?$_POST['order_no']:0;  
		update_term_meta( $term_id, 'order_no', $term_order_no );  
		
	}  

	public function unregister_taxonomy_route(){
		unregister_taxonomy(TAXONOMY_OC_EVENT_ROUTE);
	}
}
