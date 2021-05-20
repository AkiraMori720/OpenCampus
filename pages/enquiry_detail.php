<?PHP 
        global $wpdb;
        $id = $_GET['id'];
        
        $table_name =  $wpdb->prefix . 'oc_enquiry_list';
        $memo_table_name = $wpdb->prefix . "oc_enquiry_memo";
        
        if(isset($_GET['accept']))
        {
            $accept = $_GET['accept'];
            $wpdb->query("Update $table_name set is_accept=$accept WHERE id = $id");
        }

        // Enquriy Data
        $enquiry_requests=$wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
        $enquiry_request = $enquiry_requests[0];
        //var_dump( $enquiry_request);
        $is_accept = $enquiry_request->is_accept;

        // Memo Action
        if(oc_get_action("memo_delete") && isset($_GET['memo_id'])){
            $memo_id = $_GET['memo_id'];
            $wpdb->query("delete from $memo_table_name where id = $memo_id");
        }

        if(oc_get_action("memo_add") && isset($_REQUEST['enquiry_memo_content'])){
            $enquiry_memo_content = $_REQUEST['enquiry_memo_content'];
            $wpdb->query("insert into $memo_table_name (enquiry_id, memo) values ($id, '$enquiry_memo_content')");
        }

        // enquiry memo
        $memo_data = $wpdb->get_results("SELECT * FROM $memo_table_name WHERE enquiry_id = $id");

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $action_url = $uri_parts[0];
?>
        
<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title"> <?php echo sprintf("「%s %sさん (%s)」" , $enquiry_request->last_name , $enquiry_request->first_name, date('Y/m/d',strtotime($enquiry_request->create_date))) ?> </span>
        <span>のお問合せ詳細</span>
    </div>
    <a href="<?PHP echo 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0] . '?page=enquiry' ; ?>">申込者一覧へ戻る</a>
</div>    
<div class="oc-event-plugin-detail-body">
    <form action="<?php echo $action_url?>" method="get">
        <input type="hidden" name="accept" id="accept" value="<?php echo $is_accept?'0':'1' ?>">
        <input type="hidden" name="page" id="page" value="<?php echo 'enquiry_detail' ?>">
        <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
        <ul>
            <li>
                <i class="far fa-user"></i><span class="detail-title-text"><?php echo sprintf("%s %s ", $enquiry_request->last_name, $enquiry_request->first_name) ?></span>
                <i class="fas fa-school"></i><span class="detail-title-text"><?php echo sprintf("%s %s",$enquiry_request->graduate_school, $enquiry_request->grade) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <a href="mailto:<?php echo $enquiry_request->email ?>"><i class="far fa-envelope"></i></a><span class="detail-title-text"><?php echo $enquiry_request->email ?></span>
                <i class="fas fa-mobile-alt"></i><span class="detail-title-text"><?php echo $enquiry_request->phone_number ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <span class="detail-title-text"><?php echo sprintf("希望学科 : %s",$enquiry_request->desire_subject) ?></span>
            </li>
            <li>
                <span class="detail-title-text">質問など</span>
            </li>
            <li>
                <span class="detail-content-text"><?php echo $enquiry_request->content ?></span>
            </li>
        </ul>

        <ul class="oc-event-plugin-detail-li-button">
            <button type="submit" class="oc-event-plugin-detail-button"><?php echo $is_accept?'未対応にする':'対応済みにする'?></button>
        </ul>
    </form>
    <form id="oc_memo_form" action="<?php echo $action_url?>" method="get">
        <input type="hidden" name="page" id="page" value="<?php echo 'enquiry_detail' ?>">
        <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
        <input type="hidden" name="action" id="action" value="memo_add">
        <ul class="oc-event-memo-ul">
            <?php
            foreach($memo_data as $memo_item)
            {
                $create_date = date("Y/m/d H:i", strtotime($memo_item->create_date));
                $delete_url = oc_get_current_url_with_page()."&id=".$id."&action=memo_delete&memo_id=".$memo_item->id;
                echo "<div class=\"oc-event-memo-data\">
                        <div>$create_date(<a href=\"$delete_url\">削除する</a>)</div>
                        <div>$memo_item->memo</div>
                    </div>";
            }

            wp_editor( '', 'enquiry_memo_content', array('textarea_name'=>'enquiry_memo_content', 'textarea_rows'=>8) ); ?>
            <div class="oc-event-memo-button">
                <button type="submit" class="oc-event-plugin-add-memo-button">メモを追加</button>
            </div>
        </ul>
    </form>
</div>
