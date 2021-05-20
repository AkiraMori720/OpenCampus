<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


class Event_List_Table extends WP_List_Table
{
    private $table_name;

    private $view_columns;

    private $table_colums = ['cb' => '<input type="checkbox" />'];

    private $condition;

    private $orderby_column;

    private $is_name_split;

    private $is_accept_page;

    private $view_page ='';

    function __construct($table_name, $view_columns = [], $is_accept_page, $view_page, $is_name_split = true,  $condition = ' 1=1 ', $orderby_column = 'create_date')
    {
        parent::__construct(array(
            'singular' => 'singular_name',
            'plural' => 'plural_name',
            'ajax' => false
        ));

        global $wpdb;
        
        $this->is_accept_page = $is_accept_page;
        $this->view_page= $view_page;
        $this->table_name =  $wpdb->prefix . $table_name;

        // Set Table Column
        $this->view_columns = $view_columns;
        $this->table_colums = array_merge($this->table_colums, $this->view_columns);
        $this->table_colums = array_merge($this->table_colums, ['action' => ''], $this->table_colums);

        // Name
        if($is_name_split && isset($this->view_columns['name'])){
            $this->is_name_split = $is_name_split;
            unset($this->view_columns['name']);
            $this->view_columns['first_name'] = 'First Name';
            $this->view_columns['last_name'] = 'Last Name';
        }


        $this->condition = $condition . ($is_accept_page?' AND is_accept = 1 ':' AND is_accept = 0 ');
        $this->orderby_column = $orderby_column;
    }

    public function column_default( $item, $column_name )
    {
        // if($column_name == 0){
        //     return print_r( $item, true ) ;
        // }
        switch( $column_name ) {
            case 'name':
                if($this->is_name_split)
                {
                    return $this->first_column_name($item, "<strong>".$item['last_name'] .'  '. $item['first_name'] . "</strong>"); 
                }
                break;
            case 'action':
                $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
                $view_url = "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=$this->view_page&id=" . $item['id'];

                return "<a href=\"$view_url\" target=\"_blank\" style=\"text-decoration: underline !important;\">" . __("view","oc-manager") . "</a>";
            default:
                return "<strong>".$item[$column_name]."</strong>";
        }
    }

    // Displaying checkboxes!
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s" id="%2$s" value="checked" />',
            //$this->_args['singular'],
            $item['id'] . '_status',
            $item['id'] . '_status'
        );
    }

    public function get_sortable_columns() {
 
        $sortable_columns = array(
            'name' => array('name', true),
            'create_date'     => array('create_date',true), 
            'upload_date'     => array('upload_date',true),
            'desire_subject'     => array('desire_subject',true),
        ); 
    
        return $sortable_columns;
    }
    
    public function get_columns(){
        return $this->table_colums;
    }
    
    public function get_hidden_columns()
    
       {
           // Setup Hidden columns and return them
           return ['id'];
       }
       
    public function first_column_name($item, $field) {
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $cur_page = $_GET['page'];
        
        if(isset($_GET['post_id']))
        {
            $post_id = $_GET['post_id'];
            $action_url = "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=$cur_page&post_id=$post_id&id=" . $item['id'];
        }
        else
            $action_url = "http://" . $_SERVER['HTTP_HOST'] . $uri_parts[0] . "?page=$cur_page&id=" . $item['id'];

        $actions = array(
    
            'accept'      => sprintf('<a href="%s&action=%s">%s</a>',$action_url, $this->is_accept_page?'unaccept':'accept', $this->is_accept_page? __("UnAccept","oc-manager"):__("Accept","oc-manager")),
 
            'trash'    => sprintf('<a href="%s&action=trash">' . __("Trash","oc-manager") . '</a>',$action_url),
 
        );
        return sprintf('%1$s %2$s', $field, $this->row_actions($actions) );
   }

   function get_bulk_actions()
   {
       if($this->is_accept_page)
            $actions = array(
        
                'trash'    => __("Move To Trash","oc-manager"),
                'unaccept' => __("UnAccept Event","oc-manager"),
            );
       else
            $actions = array(
        
                    'trash' => __("Move To Trash","oc-manager"),
                    'accept' => __("Accept Event","oc-manager")
            );
 
        return $actions;
    }
    
    function process_bulk_action()
    {  
        global $wpdb;

        if (isset($_GET['id'])) {
                if (!empty($_GET['id'])) { 
                    
                    $id=$_GET['id'];
                    switch($this->current_action())
                    {
                        case 'trash':
                            wp_trash_post($id);  

                            $wpdb->query("update $this->table_name set post_status=0 WHERE id =$id");  
                        break;
                        case 'accept':
                            $wpdb->query("Update $this->table_name set is_accept=1, accept_date='". date("Y-m-d H:i:s") ."' WHERE id = $id");
                        break;
                        case 'unaccept':
                            $wpdb->query("Update $this->table_name set is_accept=0 WHERE id = $id");
                        break;
                    }
                }
            }
        else{
            $ids = [];
            foreach($_REQUEST as $key => $value){
                $lastPos = 0;
                if($value == 'checked' && ($lastPos = strpos($key, '_status', $lastPos))!== false)
                    array_push($ids, substr($key, 0, $lastPos));
            }
            $ids_str=implode(',', $ids);
            switch($this->current_action())
            {
                case 'trash':
                    wp_trash_post($id);
                    $wpdb->query("update $this->table_name set post_status=0 WHERE id IN($ids_str)");  
                break;
                case 'accept':
                    $wpdb->query("Update $this->table_name set is_accept=1, accept_date='". date("Y-m-d H:i:s") ."' WHERE id IN($ids_str)");
                break;
                case 'unaccept':
                    $wpdb->query("Update $this->table_name set is_accept=0 WHERE id IN($ids_str)");
                break;
            }
        }
            
    }
    
    
   private function table_data()
   {      
        global $wpdb;
 
        $view_columns= implode(',', array_keys($this->view_columns));

        $wk_posts=$wpdb->get_results("SELECT $view_columns FROM $this->table_name WHERE post_status=1 AND $this->condition");
 
        $data = array();
 
        $i=0;
 
        foreach ($wk_posts as $wk_post) {
            foreach($wk_post as $key => $value){
                $data[$i][$key] = $value;
            }
            $data[$i]['action'] = 'view';
            $i++;
        }

        //var_dump($data);

        return $data;
 
    }
    
    public function prepare_items(){
 
        global $wpdb;  

            $columns = $this->get_columns();
 
            $sortable = $this->get_sortable_columns();
 
            $hidden=$this->get_hidden_columns();
 
            $this->process_bulk_action();
 
            $data = $this->table_data();

            $totalitems = count($data);
 
            $user = get_current_user_id();
 
            $screen = get_current_screen();
 
            //$option = $screen->get_option('per_page', 'option'); 
 
            $perpage = 10;
            
            $this->_column_headers = array($columns,$hidden,$sortable); 
 

            usort($data, array($this, 'usort_reorder'));
 

            $totalpages = ceil($totalitems/$perpage); 

            $currentPage = $this->get_pagenum();
                
            $data = array_slice($data,(($currentPage-1)*$perpage),$perpage);

            $this->set_pagination_args( 
                array(

                "total_items" => $totalitems,

                "total_pages" => $totalpages,

                "per_page" => $perpage,
                ) 
            );
                 
        $this->items =$data;
        return $totalitems;
    }

    function usort_reorder($a,$b){
 
        if(isset($_GET['orderby']))
        {
            if($_GET['orderby'] == 'name')
                $this->orderby_column = 'last_name';
            else
                $this->orderby_column = $_GET['orderby'];
        }
        $orderby = $this->orderby_column; //If no sort, default to title

        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

        $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order

        return ($order==='asc') ? $result : -$result; //Send final sort direction to usort

    }

}

