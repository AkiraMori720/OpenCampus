<?PHP 
        global $wpdb;
        $id = $_GET['id'];
        
        $table_name =  $wpdb->prefix . 'oc_request_list';
        
        if(isset($_GET['accept']))
        {
            $accept = $_GET['accept'];
            $wpdb->query("Update $table_name set is_accept=$accept WHERE id = $id");
        }

        $event_requests=$wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
        $event_request = $event_requests[0];
        
        //var_dump( $event_request);
        
        $event_post = get_post($event_request->post_id);
        $is_accept = $event_request->is_accept;

        $post_field_bus_route_item = wp_get_post_terms( $event_request->post_id, TAXONOMY_OC_EVENT_ROUTE, array('fields'=>'names'));
        $event_post_course_title = "利用する[" . implode(', ', $post_field_bus_route_item) . "]";
     

        // $post_field_course_data = json_decode(get_post_meta( $event_request->post_id, 'course_data', true ));
        // $post_field_sub_course = array();
        // foreach($post_field_course_data as $course_item){
        //     $sub_courses = json_decode($course_item->sub_courses);
        //     array_push($post_field_sub_course, 
        //         [
        //             'course_name'=>$course_item->course_name, 
        //             'sub_courses'=>$sub_courses?implode(',',$sub_courses):''
        //         ]
        //     );
        // }
        //var_dump($event_post);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $action_url = $uri_parts[0];

        // use bus constants
        const NO_USE = 0;
        const USE_ALL = 1;
        const USE_ONLY_COMMING = 2;
        const USE_ONLY_GOING = 3;

        const bus_constant_str = [
            NO_USE=>'利用しない',
            USE_ALL=>'往復とも利用する',
            USE_ONLY_COMMING=>'行きのみ利用する',
            USE_ONLY_GOING=>'帰りのみ利用する',
        ];

        $unaccept_list_url = get_admin_url() . 'edit.php?post_type='. POST_TYPE_OC_EVENT . '&page=unaccept_request_list';
        $request_list_url = get_admin_url() . 'admin.php?page=event_detail&post_id=' . $event_request->post_id;
        $upload_date = get_post_meta( $event_request->post_id, 'upload_date', true );
?>
        
<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title"> <?php echo sprintf("「%s」" , $event_post->post_title) ?> </span>
        <span>の申込者詳細</span>
    </div>
    <a href="<?PHP echo $unaccept_list_url ?>">未対応者一覧へ戻る</a>
    <a href="<?PHP echo $request_list_url ?>">申込者一覧へ戻る</a>
</div>    
<form action="<?php echo $action_url?>" method="get">
    <input type="hidden" name="accept" id="accept" value="<?php echo $is_accept?'0':'1' ?>">
    <input type="hidden" name="page" id="page" value="<?php echo 'request_detail' ?>">
    <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
    <div class="oc-event-plugin-detail-body">
        <ul>
            <li>
                <i class="far fa-caret-square-right"></i><span class="detail-title-text"><?php echo $event_post->post_title ?></span>
            </li>
            <li>
                <span class="detail-title-text event-upload-date">イベント日時 <?php echo date('Y年 m月 d日', $upload_date) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <i class="far fa-user"></i><span class="detail-title-text"><?php echo sprintf("%s %s(%s %s)  %s",$event_request->last_name, $event_request->first_name, $event_request->last_name_furigana, $event_request->first_name_furigana,  $event_request->sex) ?></span>
                <i class="fas fa-birthday-cake"></i><span class="detail-title-text"><?php echo date('Y年 m月 d日', strtotime($event_request->birthday)) ?></span>
			</li>
			<li>
				<i class="fas fa-school"></i><span class="detail-title-text"><?php echo sprintf("%s %s",$event_request->graduate_school, $event_request->grade) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <a href="mailto:<?php echo $event_request->email ?>"><i class="far fa-envelope"></i></a><span class="detail-title-text"><?php echo $event_request->email ?></span>
                <i class="fas fa-mobile-alt"></i><span class="detail-title-text"><?php echo $event_request->phone_number ?></span>
            </li>
            <li>
                <i class="fas fa-home"></i><span class="detail-title-text"><?php echo sprintf("%s-%s %s %s %s",$event_request->postal_code_1, $event_request->postal_code_2, $event_request->address_prefecture, $event_request->address_municipality, $event_request->address_detail) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <span class="detail-item-title">希望学科 : </span><span class="detail-content-text"><?php echo $event_request->desire_subject ?></span>
            </li>
            <li>
                <span class="detail-item-title">宿泊施設利用 : </span><span class="detail-content-text"><?php echo $event_request->is_use_accommodation?'利用する':'利用しない' ?></span>
            </li>
            <li>
                <span class="detail-item-title">保護者説明会参加 : </span><span class="detail-content-text"><?php echo $event_request->is_attend_in_parent_priefing?'参加する':'参加しない' ?></span>
            </li>
            <li>
                <span class="detail-item-title">バス利用 : </span><span class="detail-content-text"><?php echo bus_constant_str[$event_request->is_use_bus] ?></span>
                <?php if($event_request->is_use_bus != NO_USE){ ?>
			</li>
            <li><span class="detail-item-title">乗車場所 : </span><span class="detail-content-text"><?php echo $event_request->meeting_place ?></span>
                <?php } ?>
            </li>
        </ul>
        <ul>
            <li>
                <span class="detail-item-title">コース名:</span>
                <span class="detail-content-text"><?php echo $event_request->course_name ?></span>
            </li>
            <?php if($event_request->sub_course_name){ ?>
            <li>
                <span class="detail-item-title">サブコース名:</span>
                <span class="detail-content-text"><?php echo $event_request->sub_course_name ?></span>
            </li>
            <?php } ?>
        </ul>
        <ul>
            <li>
                <span class="detail-title-text">質問など</span>
            </li>
            <li>
                <span class="detail-content-text"><?php echo $event_request->content ?></span>
            </li>
        </ul>

        <ul class="oc-event-plugin-detail-li-button">
            <button type="submit" class="oc-event-plugin-detail-button"><?php echo $is_accept?'未対応にする':'対応済みにする'?></button>
        </ul>
    </div>
</form>
