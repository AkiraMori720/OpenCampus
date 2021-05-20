<?php
class Oc_Event_Post_Admin{

	private $post_type;

	public function __construct($post_type_name)
	{
		$this->post_type = $post_type_name;
	}

	public function init(){
		add_filter('manage_edit-'.$this->post_type.'_columns' , array($this,'columns_add'));
		add_filter('manage_'.$this->post_type.'_posts_custom_column' , array($this,'columns_output'),10, 2);
		add_filter('manage_edit-'.$this->post_type.'_sortable_columns', array($this,'sortable_columns') );

		add_filter('pre_get_posts', array($this, 'oc_event_custom_orderby'));
		add_filter( 'posts_clauses', array($this, 'filter_by_uncheck_number'), 10, 2 ); // we need the 2 because we want to get all the arguments
		add_action('add_meta_boxes', array($this, 'oc_event_metabox'));
		add_action('save_post', array($this, 'oc_save_event_info'), 10, 2);
	}

	public function oc_event_custom_orderby($query){
		if(!is_admin()|| !$query->is_main_query()){
			return;
		}

		$post_type = $query->get('post_type');
		if($post_type == $this->post_type)
		{
			$orderby = $query->get( 'orderby');
			
			if ( !$orderby || 'upload_date' == $orderby ) {
				$query->set( 'meta_key', 'upload_date' );
				$query->set( 'orderby', 'meta_value' );
			}
			// else if ( 'uncheck_number' == $orderby ) {
			// 	var_dump($query);
			// 	
			// 	$query['join'] .= ` INNER JOIN $table_name AS pml WHERE pml.is_accept = 0 AND pml.post_status=1 AND pml.post_id = t.ID`;
			// 	$query->set( 'meta_key', 'uncheck_number' );
			// 	$query->set( 'orderby', 'uncheck_number' );
			// }
		}
	}

	public function filter_by_uncheck_number( $clauses, $wp_query ){
		if(!is_admin())
			return $clauses;
		
		global $wpdb;
		
		if($wp_query->query['post_type'] == $this->post_type && $wp_query->query['orderby'] == 'uncheck_number')
		{
			//var_dump($clauses);
			$oc_request_table = $wpdb->prefix . 'oc_request_list';
			$post_table = $wpdb->prefix . 'posts';
			$clauses['join']     .= 'LEFT OUTER JOIN '.$oc_request_table.' AS pm ON pm.post_id = '.$post_table.'.ID AND pm.post_status=1 AND pm.is_accept = 0 ';
			$clauses['fields'] .= ', COUNT(pm.id) AS uncheck_number';
			$clauses['groupby'] = $post_table.'.ID';
			$clauses['orderby']   = 'uncheck_number '.$wp_query->query['order'];
		}
		return $clauses;
	}

	public function oc_event_metabox(){
		add_meta_box(
			'oc-event-metabox_id',
			'イベント情報',
			array($this, 'oc_metabox_callback'),
			$this->post_type,
		);
	}

	public function sort_by_order_no($origin_array){
		foreach($origin_array as $item){
			$order_no = get_term_meta( $item->term_id, 'order_no', true );
			$item->order_no = $order_no;
		}

		function data_compare($element1, $element2) { 
			$value1 = $element1->order_no; 
			$value2 = $element2->order_no; 
			return $value1 - $value2; 
		}  
		  
		// Sort the array  
		usort($origin_array, 'data_compare'); 
		return $origin_array;
	}

	// View Meta box field in New / Edit Form
	public function oc_metabox_callback( $post ) {

		// upload_date
		$upload_date = date('Y/m/d');
		$upload_date_timestamp = get_post_meta( $post->ID, 'upload_date', true );
		if($upload_date_timestamp != null && !empty($upload_date_timestamp))
			$upload_date = date("Y/m/d", $upload_date_timestamp);
		$event_category = wp_get_post_terms( $post->ID, TAXONOMY_OC_EVENT_CATEGORY, array('fields'=>'ids'));
		$start_time = get_post_meta( $post->ID, 'start_time', true );
		if(!$start_time){
			$start_time = '13:00~16:00 (12:15受付開始)';
		}
		$venue_text = get_post_meta( $post->ID, 'venue_text', true );
		if(!$venue_text){
			$venue_text = '本校 1・2・3号館';
		}
		$venue_url = get_post_meta( $post->ID, 'venue_url', true );
		if(!$venue_url){
			$venue_url = 'https://omula.com/about/access.html';
		}
		$event_option_accommodation_pull_down = get_post_meta( $post->ID, 'event_option_accommodation_pull_down', true );
		$event_option_parent_brief = get_post_meta( $post->ID, 'event_option_parent_brief', true );
		$event_option_bus_route = get_post_meta( $post->ID, 'event_option_bus_route', true );
		$post_field_bus_route_item = wp_get_post_terms( $post->ID, TAXONOMY_OC_EVENT_ROUTE, array('fields'=>'ids'));
		$access_data = get_post_meta( $post->ID, 'access_data', true );
		$course_data = get_post_meta( $post->ID, 'course_data', true );

		// var_dump($event_category);
		// var_dump($post_field_bus_route_item);
		?>
		<ul class="field_row">
			<div class="post_field" style="width: 100%">
				<div class="post_field_label">開催日<span class="post_field_required">*</span></div>
				<input class='input_content' type="text" id="upload_date" name="upload_date" required maxlength="10" value="<?php echo $upload_date?>" />
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field" style="width: 100%">
				<div class="post_field_label">イベントカテゴリ<span class="post_field_required">*</span></div>
				<?php
					$categoryes = get_terms(array(
						'taxonomy' => TAXONOMY_OC_EVENT_CATEGORY,
						'hide_empty' => false
					));
					$output = '<select class="input_content" id="event_category" name="event_category">';
					if(!empty($categoryes)){
						foreach( $categoryes as $category ) {
							if( $category->parent == 0 ) {
								$output.= '<option value="'. esc_attr( $category->name ) .'" ' .(in_array($category->term_id, $event_category)?'selected':''). '>'. esc_attr( $category->name ).'</option>';
							}
						}
					}
					$output.='</select>';
					if(empty($categoryes))
						$output.='<div class="post_field_label" style="color:red; font-size:14px">' . __('No Categories. Please Insert Category!', 'oc-manager'). '</div>';
					echo $output;
				?>
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field" style="width: 100%">
				<div class="post_field_label">開催時間<span class="post_field_required">*</span></div>
				<input class='input_content' type="text" id="start_time" name="start_time" required placeholder="例)13:00~16:00 (12:30受付開始)" value="<?php echo $start_time ?>" />
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field" style="width:50%">
				<div class="post_field_label">開催地テキスト<span class="post_field_required">*</span></div>
				<input class='input_content' type="text" id="venue_text" name="venue_text" required value="<?php echo $venue_text ?>" />
			</div>
			<div class="post_field" style="width:50%">
				<div class="post_field_label">開催地URL</div>
				<input class='input_content' type="text" id="venue_url" name="venue_url" value="<?php echo $venue_url ?>" />
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field" style="width:100%">
				<div class="post_field_label">備考</div>
				<textarea class='input_content' id='post_content' name='post_content'rows=4><?php echo $post->post_content ?></textarea>
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field_check" style="width:30%">
				<div class="post_field_label">宿泊プルダウン<span class="post_field_required">*</span></div>
				<div class="check_box_field">
					<div>
						<input type="radio" id="event_option_accommodation_pull_down" name="event_option_accommodation_pull_down" <?php echo $event_option_accommodation_pull_down=="on"?'checked="checked"':'' ?> value="on"><span class="event_option_check_label">表示する</span>
					</div>
					<div>
						<input type="radio" id="event_option_accommodation_pull_down" name="event_option_accommodation_pull_down" <?php echo (!$event_option_accommodation_pull_down || $event_option_accommodation_pull_down=="off")?'checked="checked"':'' ?>  value="off"><span class="event_option_check_label">表示しない</span>
					</div>
				</div>
			</div>
			<div class="post_field_check" style="width:30%">
				<div class="post_field_label">保護者説明会プルダウン<span class="post_field_required">*</span></div>
				<div class="check_box_field">
					<div>
						<input type="radio" id="event_option_parent_brief" name="event_option_parent_brief" <?php echo (!$event_option_parent_brief || $event_option_parent_brief=="on")?'checked="checked"':'' ?> value="on"><span class="event_option_check_label">表示する</span>
					</div>
					<div>
						<input type="radio" id="event_option_parent_brief" name="event_option_parent_brief" <?php echo $event_option_parent_brief=="off"?'checked="checked"':'' ?> value="off"><span class="event_option_check_label">表示しない</span>
					</div>
				</div>
			</div>
			<div class="post_field_check" style="width:30%">
				<div class="post_field_label">バスルート<span class="post_field_required">*</span></div>
				<div class="check_box_field">
					<div>
						<input type="radio" id="event_option_bus_route" name="event_option_bus_route" <?php echo (!$event_option_bus_route || $event_option_bus_route=="off")?'checked="checked"':'' ?> value="off"><span class="event_option_check_label">運行が無い</span>
					</div>
					<div>
						<input type="radio" id="event_option_bus_route" name="event_option_bus_route" <?php echo $event_option_bus_route=="on"?'checked="checked"':'' ?> value="on"><span class="event_option_check_label">運行がある</span>
					</div>
				</div>
			</div>
		</ul>
		<hr/>
		<div id="post_field_bus_routes_list" <?php echo ($event_option_bus_route==="on")?'':'style="display: none;"' ?>>
			<input type="hidden" id="post_field_bus_route" name="post_field_bus_route" value="">
			<ul class="field_row">
				<div class="post_field" style="width:100%">
					<div class="post_field_label">バスルート詳細</div>
					<div class="post_field_description">今回使用するバスルートにチェックを入れて下さい</div>
					<div class='post_field_content'>
					<?php
						$routes = get_terms(array(
							'taxonomy' => TAXONOMY_OC_EVENT_ROUTE,
							'hide_empty' => false
						));
						
						$routes = $this->sort_by_order_no($routes);

						$output = '<ul class="post_field_bus_routes_list">';
						if(!empty($routes)){
							foreach( $routes as $route ) {

								$is_checked = false;

								if(!empty($post_field_bus_route_item))
									$is_checked = in_array($route->term_id, $post_field_bus_route_item);

								if( $route->parent == 0 ) {
									$output .= '<li><label><input type="checkbox" '. ($is_checked?'checked':'') .' name="post_field_bus_route_item[]" value="'. esc_attr( $route->name ) .'">'. esc_attr( $route->name ) .'</label></li>';
								}
							}
						}
						$output.='</ul>';
						if(empty($routes))
							$output.='<div class="post_field_label" style="color:red; font-size:14px">' . __('No Routes. Please Insert Routes!', 'oc-manager'). '</div>';
						echo $output;
					?>
					</div>
				</div>
			</ul>
			<hr/>
		</div>
		<ul class="field_row">
			<div class="post_field" style="width:100%">
				<div class="post_field_label">アクセス</div>
				<div class="post_field_description">
					[コピペ用テキスト]<br>
					バス乗車口PDF https://omula.com/assets/pdf/bustime_fc.pdf<br> 
					交通費補助PDF https://omula.com/assets/pdf/op_support.pdf<br>
					アクセス　https://omula.com/about/access.html
				</div>
				<div class='post_field_content'>
					<div id="access_table" class="post_field_access">
						<table>
							<tr class="table_header">
								<th class="table_id_field" style="width: 5%"></th>
								<th style="width: 30%">テキスト</th>
								<th style="width: 55%">URL</th>
								<th style="width: 10%"></th>
							</tr>
							<tr class="hide_row table_row">
								<td class="table_id_field table_index">1</td>
								<td><input class='input_content access_label' type="text" placeholder="例)マップを見る" /></td>
								<td><input class='input_content access_url' type="text" placeholder="例)https://omula.com/about/access.html" /></td>
								<td class="table_btn_field table_operation">
									<i class="fas fa-plus table-add glyphicon"></i>
									<i class="fas fa-minus table-remove glyphicon"></i>
								</td>
							</tr>
						</table>
					</div>

					<button id="access-add-btn" class="button button-primary add-btn">行を追加</button>
					<input type="hidden" id="access_data" name="access_data" value='<?php echo $access_data; ?>'>
				</div>
			</div>
		</ul>
		<hr/>
		<ul class="field_row">
			<div class="post_field" style="width:100%">
				<div class="post_field_label">各コース詳細<span class="post_field_required">*</span></div>
				<div class="post_field_description">同一日にはに1~6コースの登録が可能です。</div>
				<div class='post_field_content'>
					<div id="course_table" class="post_field_course">
						<table id="course_table_id">
							<tr class="hide_row course_table_row table_index_tr">
								<td class="table_id_field table_index" rowspan="6" width="5%">1</td>
								<td class="table_id_field course_table_header" width="20%">
									<div class="post_field_label">メイン画像<span class="post_field_required">*</span></div>
									<div class="post_field_description">コース数によって画像のトリミング位置が変わります。 推奨サイズ : 横1600px×縦900px</div>
								</td>
								<td width="70%">
									<div class="post_field_description course_table_main_img_upload">
										<span class="main_img_slug" style="vertical-align: middle; padding-right: 16px;">画像が選択されていません</span>
										<button class="button add-image">画像を追加する</button>
									</div>
									<img style="padding:8px;width:90%" class="img_data main_image">
								</td>
								<td class="table_btn_field table_operation"  rowspan="6"  width="5%">
									<i class="fas fa-plus table-add course-add glyphicon" style="width: 100%;margin: 16px 0;"></i>
									<i class="fas fa-minus table-remove course-remove glyphicon" style="width: 100%;margin: 16px 0;"></i>
								</td>
							</tr>
							<tr class="hide_row course_table_row">
								<td class="table_id_field course_table_header">
									<div class="post_field_label">コース名<span class="post_field_required">*</span></div>
									<div class="post_field_description">上限15文字</div>
								</td>
								<td style="padding: 0 16px;">
									<input class='input_content course_name' type="text" placeholder="例)HAIR" maxlength="15">
								</td>
							</tr>
							<tr class="hide_row course_table_row">
								<td class="table_id_field course_table_header">
									<div class="post_field_label">コース詳細</div>
								</td>
								<td>
									<div id="course_data_table" class="course_data_table">
										<table class="course_data_table_id">
											<tr class="hide_row couse_data_table_row data-table-index-tr">
												<td class="table_id_field table_index" rowspan="2" width="5%">1</td>
												<td width="85%">
													<div class="post_field_check" style="text-align: left">
														<div class="post_field_label">ブロックを追加<span class="post_field_required">*</span></div>
														<div class="check_box_field">
															<div>
																<input class="block_media" type="radio" checked='checked' value="media"><span class="event_option_check_label">画像グループ</sapn>
															</div>
															<div>
																<input class="block_text" type="radio" value="text"><span class="event_option_check_label">記事</sapn>
															</div>
														</div>
													</div>
												</td>
												<td class="table_btn_field table_operation" rowspan="2" width="10%">
													<i class="fas fa-plus table-add course-data-add glyphicon"  style="width: 100%;margin: 16px 0;"></i>
													<i class="fas fa-minus table-remove course-data-remove glyphicon"  style="width: 100%;margin: 16px 0;"></i>
												</td>
											</tr>
											<tr class="hide_row couse_data_table_row course_select_data">
												<td>
													<div class="block_data_media">
														<div class="post_field_label">画像<span class="post_field_required">*</span></div>
														<!-- <div class="post_field_description"><span style="vertical-align:middle; margin:0 8px;">上限22文字</span> --><button class="button add-image">画像を追加する</button></div>
														<img style="padding:8px;width:calc(100% - 16px)" class="img_data">
													</div>
													<div class="block_data_text" style="display: none">
														<div class="post_field_label">テキスト<span class="post_field_required">*</span></div>
														<textarea class="text_data" id="block_data_detail" style="width:100%; height:420px" rows="8"></textarea>
													</div>
												</td>
											</tr>
										</table>

										<button class="course-data-add-btn button button-primary add-btn">行を追加</button>
										<input type="hidden" class="course-detail-data">
									</div>
								</td>
							</tr>
							<tr class="hide_row course_table_row">
								<td class="table_id_field course_table_header">
									<div class="post_field_label">サブコース</div>
								</td>
								<td style="padding: 16px;">
									<div class="sub_course_table">
										<div class="sub-notice-warning hidden"><p>最大行数に達しました（3 行）</p><a class="sub-notice-dismiss">x</a></div>
										<table>
											<tr class="hide_row sub_course_table_row">
												<td class="table_id_field table_index">0</td>
												<td><div class="post_field_label">サブコース名</div></td>
												<td><input class='input_content sub_course_name' type="text"/></td>
												<td class="table_btn_field table_operation">
													<i class="fas fa-plus table-add sub-course-add glyphicon"></i>
													<i class="fas fa-minus table-remove sub-course-remove glyphicon"></i>
												</td>
											</tr>
										</table>
										<button class="button button-primary add-btn sub-course-add-btn">行を追加</button>
										<input type="hidden" class="sub_course_data">
									</div>
								</td>
							</tr>
							<tr class="hide_row course_table_row">
								<td class="table_id_field course_table_header">
									<div class="post_field_label">募集終了</div>
								</td>
								<td style="padding: 16px; text-align: left;">
									<input type='checkbox' class='input_content is_ended' style="width: 1rem">
									<label class="is_ended_label">募集終了する</label>
								</td>
							</tr>
							<tr class="hide_row course_table_row">
								<td class="table_id_field course_table_header">
									<div class="post_field_label">交通費補助</div>
								</td>
								<td style="padding: 16px; text-align: left;">
									<input type='checkbox' class='input_content has_traffic_help' style="width: 1rem">
									<label class="has_traffic_help_label">交通費補助無し</label>
								</td>
							</tr>
						</table>
					</div>

					<button id="course-add-btn" class="button button-primary add-btn">行を追加</button>
					<input type="hidden" id="course_data" name="course_data" value='<?php echo empty($course_data)?'':$course_data; ?>'>
				</div>
			</div>
		</ul>
		<?PHP
	}

	public function input_dateformat(){
		return 'Y/m/d';
	}

	// Save Post Data
	public function oc_save_event_info( $post_id ,  $post){
		// check user permission
		if ( ( get_post_type() != $this->post_type ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// upload_data
		if ( isset( $_POST['upload_date'] ) ) {
			update_post_meta( $post_id, 'upload_date', sanitize_text_field(strtotime( $_POST['upload_date'] ) ) );
		}

		// event_category
		if ( isset( $_POST['event_category'] ) ) {
			$event_category = array($_POST['event_category']);
			wp_remove_object_terms($post_id, wp_get_post_terms( $post->ID, TAXONOMY_OC_EVENT_CATEGORY, array('fields'=>'names')),TAXONOMY_OC_EVENT_CATEGORY);
			wp_set_post_terms($post_id,$event_category,TAXONOMY_OC_EVENT_CATEGORY,true);
		}


		// start_time
		if ( isset( $_POST['start_time'] ) ) {
			update_post_meta( $post_id, 'start_time', sanitize_text_field($_POST['start_time']) );
		}

		// venue_text
		if ( isset( $_POST['venue_text'] ) ) {
			update_post_meta( $post_id, 'venue_text', sanitize_text_field($_POST['venue_text']) );
		}

		// venue_url
		if ( isset( $_POST['venue_url'] ) ) {
			update_post_meta( $post_id, 'venue_url', sanitize_text_field($_POST['venue_url']) );
		}

		// event_option_accommodation_pull_down
		if ( isset( $_POST['event_option_accommodation_pull_down'] ) ) {
			update_post_meta( $post_id, 'event_option_accommodation_pull_down', sanitize_text_field($_POST['event_option_accommodation_pull_down']) );
		}

		// event_option_parent_brief
		if ( isset( $_POST['event_option_parent_brief'] ) ) {
			update_post_meta( $post_id, 'event_option_parent_brief', sanitize_text_field($_POST['event_option_parent_brief']) );
		}

		// event_option_bus_route
		if ( isset( $_POST['event_option_bus_route'] ) ) {
			update_post_meta( $post_id, 'event_option_bus_route', sanitize_text_field($_POST['event_option_bus_route']) );
		}

		// post_field_bus_route_item
		if ( isset( $_POST['post_field_bus_route_item'] ) ) {
			$route_ids = $_POST['post_field_bus_route_item'];

			wp_remove_object_terms($post_id, wp_get_post_terms( $post->ID, TAXONOMY_OC_EVENT_ROUTE, array('fields'=>'names')),TAXONOMY_OC_EVENT_ROUTE);
			wp_set_post_terms($post_id,$route_ids,TAXONOMY_OC_EVENT_ROUTE,true);
		}

		// access_data
		if ( isset( $_POST['access_data'] ) ) {
			update_post_meta( $post_id, 'access_data', sanitize_text_field($_POST['access_data']) );
		}

		// course_data
		if ( isset( $_POST['course_data'] ) ) {
			update_post_meta( $post_id, 'course_data', $_POST['course_data'] );
		}
	}
	/**
	 *
	 * Not yet in use
	 */
	public function parse_query(){
		global $wp_query;
		if( !empty($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == $this->post_type && (empty($wp_query->query_vars['post_status']) || !in_array($wp_query->query_vars['post_status'],array('trash','pending','draft'))) ) {
		    //Set up Scope for Oc_Event_Post
			$scope = $wp_query->query_vars['scope'] = (!empty($_REQUEST['scope'])) ? $_REQUEST['scope']:'future';
		}
	}


	function oc_get_scopes(){
		$scopes = array(
			'all' => __('All events','oc-manager'),
			'future' => __('Future events','oc-manager'),
			'past' => __('Past events','oc-manager'),
			'today' => __('Today\'s events','oc-manager'),
			'tomorrow' => __('Tomorrow\'s events','oc-manager'),
			'month' => __('Events this month','oc-manager'),
		);
		return apply_filters('em_get_scopes',$scopes);
	}

	public function views($views){
		if( !current_user_can('edit_others_events') ){
			//alter the views to reflect correct numbering

		}
		return $views;
	}

	public function columns_add($columns) {
		if( array_key_exists('cb', $columns) ){
			$cb = $columns['cb'];
	    	unset($columns['cb']);
	    	$id_array = array('cb'=>$cb);
		}else{
	    	$id_array = array();
		}
	    unset($columns['comments']);
	    unset($columns['date']);
		unset($columns['author']);
		unset($columns[TAXONOMY_OC_EVENT_CATEGORY]);
		unset($columns[TAXONOMY_OC_EVENT_ROUTE]);
		//var_dump($columns);
	    $columns = array_merge($id_array, array(
			'title' => __('Title', 'oc-manager'),
	    	'upload_date' => __('UploadDate','oc-manager'),
	    	'check_number' => __('CheckNumber','oc-manager'),
			'uncheck_number' => __('UnCheckNumber','oc-manager'),
	    	'action' => '',
			'date' => __('Date','oc-manager')
	    ));
	    if( !get_option('dbem_locations_enabled') ){
	    	unset($columns['location']);
	    }
	    return $columns;
	}


	// Table Content View

	public function columns_output( $column_name, $post_id) {
		// upload_date
		global $wpdb, $wp_query;
		$table_name = $wpdb->prefix . 'oc_request_list';
		switch($column_name)
		{
			case 'upload_date':
				$upload_date = get_post_meta( $post_id, 'upload_date', true );
				//echo $upload_date;

				if(!empty( $upload_date ) ) {
					echo date( $this->input_dateformat(), $upload_date);
				} else {
					echo '<span aria-hidden="true">&mdash;</span>';
				}
				break;
			case 'check_number':
				$check_record = $wpdb->get_results("SELECT COUNT(*) as check_number FROM $table_name WHERE is_accept = 1 AND post_status=1 AND post_id = ". $post_id);
				echo sprintf("%s人", $check_record[0]->check_number);
				break;
			case 'uncheck_number':
				$uncheck_record = $wpdb->get_results("SELECT COUNT(*) as uncheck_number FROM $table_name WHERE is_accept = 0 AND post_status=1 AND post_id = ". $post_id);
				$uncheck_number = $uncheck_record[0]->uncheck_number;
				if(!$uncheck_number)
					echo '<span class="uncheck_number_zero" >0人<span>';
				else
					echo sprintf('<span class="uncheck_number" >%s人<span>', $uncheck_number);
				break;
			case 'action':
				$action_url = admin_url().'admin.php';

				echo '<a href="' . $action_url. '?page=event_detail&post_id=' . $post_id . '" target=\"_blank\">' . __( 'view detail', 'oc-manager' ) .'</a>';
				break;
		}
	}


	public function sortable_columns( $columns ){
		if(!isset($_GET['page'])){
			$columns['date-time'] = 'date-time';
			$columns['upload_date'] = 'upload_date';
			$columns['uncheck_number'] = 'uncheck_number';
			return $columns;
		}
		return $columns;
	}

}
