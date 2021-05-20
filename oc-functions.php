<?php

function oc_array_to_csv_download($data_array, $filename = "export.csv", $delimiter=",") {

    ob_clean();

    header('Content-type: text/csv; charset=UTF-16LE');
    header('Content-Disposition: attachment; filename="' . $filename. '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $file = fopen('php://output', 'w');
 
    fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

    foreach ($data_array as $line) {
        fputcsv($file, $line, $delimiter);
    }
    fclose( $file );
    
    ob_end_flush();
    
    die();
}   


function oc_get_current_url(){
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    return "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0];
}

function oc_get_current_url_with_page(){
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    return "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=". $_GET['page'];
}

function oc_get_current_url_with_page_and_post_type(){
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    return "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=". $_GET['page'] . "&post_type=". $_GET['post_type'];
}

function oc_get_current_url_with_page_and_post_id(){
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    return "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=". $_GET['page'] . "&post_id=" .  $_GET['post_id'] ;
}

function oc_get_action($action_name){
    if(is_admin() && isset($_GET['action']) && $_GET['action'] == $action_name){
        return true;
    }
    return false;
}