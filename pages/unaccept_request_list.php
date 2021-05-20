<?php

include_once OC_MANAGER_PLUGIN_DIR.'/classes/base/Event_Request_List_Table.php';

$post_type = $_GET['post_type'];
$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

$current_url = oc_get_current_url_with_page_and_post_type();


if(oc_get_action('csv_download')){
    
    global $wpdb;

    $table_name = $wpdb->prefix."oc_request_list";
    $post_table = $wpdb->prefix."posts";
    $post_meta_table = $wpdb->prefix."postmeta";
    
    $sql = "SELECT wpp.ID AS post_id, desire_subject, CONCAT(last_name, ' ', first_name) AS name, CONCAT(last_name_furigana,' ', first_name_furigana) AS name_furigana, sex, DATE_FORMAT( birthday, '%Y-%m-%e' ) AS birthday, CONCAT(postal_code_1, postal_code_2) AS postal, address_prefecture, CONCAT(address_municipality, ' ', address_detail) as address , phone_number, graduate_school, grade, wpp.post_title AS event_title, wpm.meta_value AS upload_date, course_name, sub_course_name, CASE is_use_accommodation WHEN true THEN '利用する' ELSE '利用しない' END, CASE is_attend_in_parent_priefing WHEN true THEN '参加する' ELSE '参加しない' END, content, CASE is_use_bus WHEN 0 THEN '利用しない' WHEN 1 THEN '往復とも利用する' WHEN 2 THEN '行きのみ利用する' WHEN 3 THEN '帰りのみ利用する' END, meeting_place FROM $table_name as wpor INNER JOIN $post_table as wpp ON wpor.post_id = wpp.ID LEFT JOIN $post_meta_table AS wpm ON wpp.ID=wpm.post_id AND wpm.meta_key='upload_date' WHERE wpor.post_status=1 AND wpor.is_accept=0 AND wpp.post_type='$post_type' ";
    
    $orderby_column = 'create_date';
    if(isset($_GET['orderby'])){
        $orderby_column = $_GET['orderby'];
    }
    
    $sql .= 'ORDER BY ' . $orderby_column . ' ' . (isset($_GET['order'])? $_GET['order']:'DESC');


    //var_dump($sql);
    $accepted_data=$wpdb->get_results($sql);

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
        $e_routes = wp_get_post_terms( $row->post_id, TAXONOMY_OC_EVENT_ROUTE);
        unset($row->post_id);

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

        $row->event_title = date('Y年 m月 d日', $row->upload_date) . ' ' . $row->event_title;
        unset($row->upload_date);
        array_push($download_data, get_object_vars($row));
    }

    //var_dump($download_data);
    $csv_file_name = '全てのイベントの未対応者_' . date('Y-m-d H:i:s', current_time( 'timestamp' )) . '.csv';

    oc_array_to_csv_download($download_data, $csv_file_name);

    exit;	
}

$csv_download_url = $current_url . '&action=csv_download';
if(isset($_GET['orderby'])){
    $csv_download_url .= '&orderby=' . $_GET['orderby'] . '&order='. (isset($_GET['order'])? $_GET['order']:'DESC');
}
?>

<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title"> <?php echo "「全てのイベント」" ?> </span>
        <span>の未対応者</span>
    </div>
    <a href="<?php echo $csv_download_url ?>">未対応データのダウンロード</a>
</div>    


<form id="data_request_no_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <input type="hidden" name="post_type" value="<?php echo $_REQUEST['post_type'] ?>" />
    <?php


    $no_accept_request_list_table = 
        new Event_Request_List_Table(
            'oc_request_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'desire_subject' => __( '希望学科', 'oc-manager' ), 
                'create_date' => __( '受付日', 'oc-manager' ), 
            ], 
            false,
            'request_detail',
            false);
    
    $no_accept_row_count = $no_accept_request_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>" . __( 'No Accepted Event List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$no_accept_row_count</span> 人</h2>"; 

    $no_accept_request_list_table->display(); 

    echo '</div>'; 

    ?>
</form>