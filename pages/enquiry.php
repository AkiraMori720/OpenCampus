<?php 

$current_url = oc_get_current_url_with_page();

if(oc_get_action('csv_download')){
    
    global $wpdb;

    $table_name = $wpdb->prefix."oc_enquiry_list";
    $accept = $_REQUEST['accept']?1:0;

    $accepted_enquiry_data=$wpdb->get_results("SELECT desire_subject, CONCAT(last_name, ' ', first_name), email, phone_number, graduate_school, grade,  content FROM $table_name WHERE post_status=1 AND is_accept=$accept");

    $download_data[0] = [
        __("希望学科", "oc-manager"),
        __("氏名", "oc-manager"),
        __("メイル", "oc-manager"),  
        __("電話番号", "oc-manager"), 
        __("学校名", "oc-manager"), 
        __("学年", "oc-manager"),  
        __("質問など", "oc-manager"),
    ]; 


    foreach($accepted_enquiry_data as $row)
        array_push($download_data, get_object_vars($row));

    //var_dump($download_data);
    $csv_file_name = ($accept?'問合せの対応者_':'問合せの未対応者_') .  date('Y-m-d H:i:s', current_time( 'timestamp' )).'.csv';

    oc_array_to_csv_download($download_data, $csv_file_name);

    exit;	
}

            
?>


<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title">「問合せ」</span>
        <span>の受付状況</span>
    </div>
    <a href="<?php echo $current_url ?>&action=csv_download&accept=1">対応済みデータのダウンロード</a>
    <a href="<?php echo $current_url ?>&action=csv_download&accept=0">未対応データのダウンロード</a>
</div>    


<form id="enquiry_no_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php

    include_once OC_MANAGER_PLUGIN_DIR.'/classes/base/Event_List_Table.php';


    $no_accept_enquiry_list_table = 
        new Event_List_Table(
            'oc_enquiry_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'content' => __( 'Enquiry Content', 'oc-manager' ), 
            ],
            false, 
            "enquiry_detail"
        ); 
    
    $no_accept_row_count = $no_accept_enquiry_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>". __( 'No Accept Enquiry List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$no_accept_row_count</span> 人</h2>"; 

    $no_accept_enquiry_list_table->display(); 

    echo '</div>'; 

    ?>
</form>



<form id="enquiry_accept" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

    <?php

    $accept_enquiry_list_table = 
        new Event_List_Table(
            'oc_enquiry_list', 
            [
                'id' => __( 'ID', 'oc-manager' ), 
                'name' => __( 'Name', 'oc-manager' ), 
                'create_date' => __( 'Create Date', 'oc-manager' ), 
                'content' => __( 'Enquiry Content', 'oc-manager' ), 
            ],
            true,
            "enquiry_detail"
        ); 

    $accept_row_count = $accept_enquiry_list_table->prepare_items(); 

    echo "<div class=\"wrap\"><h2>". __( 'Accept Enquiry List', 'oc-manager' ) . "<span style=\"color:red; padding-left:40px\">$accept_row_count</span> 人</h2>"; 

    $accept_enquiry_list_table->display(); 

    echo '</div>'; 

    ?>
</form>