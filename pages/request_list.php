<?php

include_once OC_MANAGER_PLUGIN_DIR.'/classes/base/Event_List_Table.php';

$post_id = $_GET['post_id'];
$event_post = get_post($post_id);
$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$post_edit_url = admin_url() . "post.php?post=$post_id&action=edit";

$current_url = oc_get_current_url_with_page_and_post_id();
$upload_date = get_post_meta( $post_id, 'upload_date', true );

if(oc_get_action('csv_download')){
    
    global $wpdb;

    $table_name = $wpdb->prefix."oc_request_list";
    $post_table = $wpdb->prefix."posts";
    $post_meta_table = $wpdb->prefix."postmeta";

    $accept = $_REQUEST['accept']?1:0;

    $accepted_data=$wpdb->get_results("SELECT desire_subject, CONCAT(last_name, ' ', first_name) AS name, CONCAT(last_name_furigana,' ', first_name_furigana) AS name_furigana, sex, DATE_FORMAT( birthday, '%Y-%m-%e' ) AS birthday, CONCAT(postal_code_1, postal_code_2) AS postal, address_prefecture, CONCAT(address_municipality, ' ', address_detail) as address , phone_number, graduate_school, grade, wpp.post_title AS event_title, course_name, sub_course_name, CASE is_use_accommodation WHEN true THEN '利用する' ELSE '利用しない' END, CASE is_attend_in_parent_priefing WHEN true THEN '参加する' ELSE '参加しない' END, content, CASE is_use_bus WHEN 0 THEN '利用しない' WHEN 1 THEN '往復とも利用する' WHEN 2 THEN '行きのみ利用する' WHEN 3 THEN '帰りのみ利用する' END, meeting_place FROM $table_name as wpor LEFT JOIN $post_table as wpp ON wpor.post_id = wpp.ID WHERE wpor.post_status=1 AND wpor.is_accept=$accept AND wpor.post_id=$post_id");

    $e_routes = wp_get_post_terms( $post_id, TAXONOMY_OC_EVENT_ROUTE);

    $download_data[0] = [
        __("希望学科", "oc-manager"), 
        __("氏名", "oc-manager"), 
        __("氏名（ふりがな）", "oc-manager"),  
        __("性別", "oc-manager"),  
        __("生年月日", "oc-manager"),  
        __("郵便番号", "oc-manager"),  
        __("都道府県", "oc-manager"), 
        __("住所 (市区町村/番地/マンション名)", "oc-manager"),  
        __("電話番号", "oc-manager"), 
        __("学校名", "oc-manager"), 
        __("学年", "oc-manager"),  
        __("参加希望イベント", "oc-manager"), 
        __("参加希望コース", "oc-manager"),  
        __("サブコース", "oc-manager"),
        __("宿泊", "oc-manager"),
        __("保護者", "oc-manager"),
        __("質問など", "oc-manager"),
        __("シャトルバス", "oc-manager"),
        __("バスルート", "oc-manager")
    ]; 

    foreach($accepted_data as $row)
    {
        $bus_route_name = $row->meeting_place;
        $row->meeting_place = '';
        if(count($e_routes) != 0)
        {
            foreach($e_routes as $e_route):
                if($e_route->name==$bus_route_name){
                    $row->meeting_place = urldecode($e_route->slug);
                    break;
                }
            endforeach;
        }

        $row->event_title = date('Y年 m月 d日', $upload_date) . ' ' . $row->event_title;
        array_push($download_data, get_object_vars($row));
    }

    //var_dump($download_data);
    $csv_file_name = $event_post->post_title . ($accept?'の対応者_':'の未対応者_') .  date('Y-m-d H:i:s', current_time( 'timestamp' )).'.csv';

    oc_array_to_csv_download($download_data, $csv_file_name);

    exit;	
}

?>

<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title"> <?php echo sprintf("「%s」" , $event_post->post_title) ?> </span>
        <span>[<?php echo date('Y年 m月 d日', $upload_date) ?>]</span>
    </div>
    <a class="evet-req-download" href="<?php echo $current_url ?>&action=csv_download&accept=1">対応済みデータのダウンロード</a>
    <a class="evet-req-download" href="<?php echo $current_url ?>&action=csv_download&accept=0">未対応データのダウンロード</a>
    <a href="<?PHP echo $post_edit_url ?>">イベント情報の確認・編集</a>
</div>    


<form id="data_request_no_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <input type="hidden" name="post_id" value="<?php echo $_REQUEST['post_id'] ?>" />

    <?php


    $no_accept_request_list_table = 
        new Event_List_Table(
            'oc_request_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'desire_subject' => __( 'Desire Subject', 'oc-manager' ), 
            ], 
            false,
            'request_detail',
            true, 
            ' post_id = ' . $post_id ); 
    
    $no_accept_row_count = $no_accept_request_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>" . __( 'No Accepted Event List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$no_accept_row_count</span> 人</h2>"; 

    $no_accept_request_list_table->display(); 

    echo '</div>'; 

    ?>
</form>



<form id="data_request_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <input type="hidden" name="post_id" value="<?php echo $_REQUEST['post_id'] ?>" />
    <?php

    $accept_request_list_table = 
        new Event_List_Table(
            'oc_request_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'desire_subject' => __( 'Desire Subject', 'oc-manager' ), 
            ], 
            true, 
            'request_detail',
            true,
            ' post_id = ' . $post_id
            ); 

    $accept_row_count = $accept_request_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>" . __( 'Accepted Event List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$accept_row_count</span> 人</h2>"; 

    $accept_request_list_table->display(); 

    echo '</div>'; 

    ?>
</form>