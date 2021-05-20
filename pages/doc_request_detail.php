<?PHP 
        global $wpdb;
        $id = $_GET['id'];
        
        $table_name =  $wpdb->prefix . 'oc_doc_request_list';
        
        if(isset($_GET['accept']))
        {
            $accept = $_GET['accept'];
            $wpdb->query("Update $table_name set is_accept=$accept WHERE id = $id");
        }

        $doc_requests=$wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
        $doc_request = $doc_requests[0];
        //var_dump( $doc_request);
        $is_accept = $doc_request->is_accept;

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $action_url = $uri_parts[0];

?>
        
<div class= "oc-event-plugin-detail-header">
    <div>
        <span class="oc-event-request-detail-title"> <?php echo sprintf("「%s %sさん (%s)」" ,$doc_request->last_name,  $doc_request->first_name,  date('Y/m/d',strtotime($doc_request->create_date))) ?> </span>
        <span>の資料請求詳細</span>
    </div>
    <a href="<?PHP echo 'http://' . $_SERVER['HTTP_HOST'] . $uri_parts[0] . '?page=doc_request' ; ?>">申込者一覧へ戻る</a>
</div>    
<form action="<?php echo $action_url?>" method="get">
    <input type="hidden" name="accept" id="accept" value="<?php echo $is_accept?'0':'1' ?>">
    <input type="hidden" name="page" id="page" value="<?php echo 'doc_request_detail' ?>">
    <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
    <div class="oc-event-plugin-detail-body">
        <ul>
            <li>
                <i class="far fa-user"></i><span class="detail-title-text"><?php echo sprintf("%s %s(%s %s)  %s", $doc_request->last_name, $doc_request->first_name,  $doc_request->last_name_furigana, $doc_request->first_name_furigana, $doc_request->sex) ?></span>
                <i class="fas fa-school"></i><span class="detail-title-text"><?php echo sprintf("%s %s",$doc_request->graduate_school, $doc_request->grade) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <a href="mailto:<?php echo $doc_request->email ?>"><i class="far fa-envelope"></i></a><span class="detail-title-text"><?php echo $doc_request->email ?></span>
                <i class="fas fa-mobile-alt"></i><span class="detail-title-text"><?php echo $doc_request->phone_number ?></span>
            </li>
            <li>
                <i class="fas fa-home"></i><span class="detail-title-text"><?php echo sprintf("%s-%s %s %s %s",$doc_request->postal_code_1, $doc_request->postal_code_2, $doc_request->address_prefecture, $doc_request->address_municipality, $doc_request->address_detail) ?></span>
            </li>
        </ul>

        <ul>
            <li>
                <span class="detail-title-text"><?php echo sprintf("希望学科 : %s",$doc_request->desire_subject) ?></span>
            </li>
            <li>
                <span class="detail-title-text">質問など</span>
            </li>
            <li>
                <span class="detail-content-text"><?php echo $doc_request->content ?></span>
            </li>
        </ul>

        <ul class="oc-event-plugin-detail-li-button">
            <button type="submit" class="oc-event-plugin-detail-button"><?php echo $is_accept?'未対応にする':'対応済みにする'?></button>
        </ul>
    </div>
</form>
