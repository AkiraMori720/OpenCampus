<?php 

$current_url = oc_get_current_url_with_page();

if(oc_get_action('csv_download')){
    
    global $wpdb;

    $table_name = $wpdb->prefix."oc_doc_request_list";
    $accept = $_REQUEST['accept']?1:0;

    $accepted_data=$wpdb->get_results("SELECT desire_subject, CONCAT(last_name, ' ', first_name) AS name, CONCAT(last_name_furigana,' ', first_name_furigana) AS name_furigana, sex, DATE_FORMAT( birthday, '%Y-%m-%e' ) AS birthday, CONCAT(postal_code_1, postal_code_2) AS postal, address_prefecture, CONCAT(address_municipality, ' ', address_detail) as address , phone_number, graduate_school, grade, content FROM $table_name WHERE post_status=1 AND is_accept=$accept");

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
        __("質問など", "oc-manager"),
    ];

    foreach($accepted_data as $row)
        array_push($download_data, get_object_vars($row));

    //var_dump($download_data);
    $csv_file_name = ($accept?'資料請求の対応者_':'資料請求の未対応者_') .  date('Y-m-d H:i:s', current_time( 'timestamp' )).'.csv';

    oc_array_to_csv_download($download_data, $csv_file_name);

    exit;	
}

            
?>

<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title">「資料請求」</span>
        <span>の受付状況</span>
    </div>
    <a href="<?php echo $current_url ?>&action=csv_download&accept=1">対応済みデータのダウンロード</a>
    <a href="<?php echo $current_url ?>&action=csv_download&accept=0">未対応データのダウンロード</a>
</div>    

<form id="doc_request_no_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

    <?php

    include_once OC_MANAGER_PLUGIN_DIR.'/classes/base/Event_List_Table.php';


    $no_accept_dr_request_list_table = 
        new Event_List_Table(
            'oc_doc_request_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'content' => __( 'Data Request Content', 'oc-manager' ), 
            ],
            false, 
            "doc_request_detail"
        ); 
    
    $no_accept_row_count = $no_accept_dr_request_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>". __( 'No Accept Data Request List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$no_accept_row_count</span> 人</h2>"; 

    $no_accept_dr_request_list_table->display(); 

    echo '</div>'; 

    ?>
</form>



<form id="doc_request_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

    <?php

    $accept_dr_request_list_table = 
        new Event_List_Table( 
            'oc_doc_request_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'content' => __( 'Data Request Content', 'oc-manager' ), 
            ],
            true, 
            "doc_request_detail"
        ); 

    $accept_row_count = $accept_dr_request_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>". __( 'Accept Data Request List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$accept_row_count</span> 人</h2>"; 

    $accept_dr_request_list_table->display(); 

    echo '</div>'; 

    ?>
</form>