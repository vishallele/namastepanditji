<?php

include 'np_table.php';

/*
 * Project - Namaste Panditji 
 * Version - 1.0
 * Date Created - 11-08-2018
 * Created By - Vishal Lele
 * File Name - np_appointment_list_table.php
 * Description - table to show list of Leads in HTML table
*/

class NP_Leads_List_Table extends Table 
{
    /**
    * user object
    * 
    * @var object
    */ 
    protected $expense;
        
    protected $category;


    public function __construct() {
		
    }
    
    public function prepare_items() {
		
		global $wpdb;

		$startIndex = ( $this->get_pagenum() * $this->get_items_per_page() ) - $this->get_items_per_page();
        $endIndex = $this->get_items_per_page();
        $order = ( isset( $_GET['order'] ) && $_GET['order'] !== '' ) ? $_GET['order'] : 'asc';
        $orderby = ( isset( $_GET['orderby'] ) && $_GET['orderby'] !== '' ) ? $_GET['orderby'] : 'id';

		$total_items = $wpdb->get_var(
			'
			SELECT COUNT(*) FROM '.$wpdb->prefix.'np_appointments_request
			'
		);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $endIndex
        ));

		$this->items = $wpdb->get_results(
			'
			SELECT id, name, email, subject, message, created_at, status
            FROM  '.$wpdb->prefix.'np_appointments_request
            ORDER BY '.$orderby.' '.$order. '
			LIMIT '.$startIndex.', '.$endIndex.'
			'
		);

		//echo "<pre>"; print_r($this->items); exit;

    }
    
    public function get_columns() {
        
        $c = array(
            'name'  => 'Name',
            'email' => 'Email Address',
            'subject' => 'Subject',
            'created_at' => 'Date',
            'message' => 'Messge'			
        );
        
        return $c;
    }
    
    public function get_sortable_columns() {
        
        $c = array(
            'name'  => 'name',
            'email' => 'email',
			'subject' => 'subject',
			'created_at' => 'created_at'
        );
        
       return $c;
        
    }

    public function search_box($input_id = NULL) {
        ?>
	        <p class="search-box">
				<label class="screen-reader-text" for="post-search-input">Search Leads:</label>
				<input type="search" id="post-search-input" name="s" value="">
				<input type="submit" id="search-submit" class="button" value="Search Leads"></p>
			</p>
        <?php
    }

    protected function months_dropdown() {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results("
			SELECT DISTINCT YEAR( created_at ) AS year, MONTH( created_at ) AS month
			FROM ".$wpdb->prefix."np_appointments_request
			ORDER BY created_at DESC
        ");
        
        $month_count = count( $months );
        
		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
            return;

        $m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;

?>
		<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date' ); ?></label>
		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $m, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
		</select>
<?php
	}
    
    public function display_rows() {
        
        foreach( $this->items as $item ) { 
            echo $this->single_row( $item );
        }
        
    }
    
    public function single_row( $item ) {
        
        list( $columns ) = $this->get_column_info();
        
        $r = "<tr id='subadmin_$item->id'>";
        
        foreach( $columns as $column_name => $column_display_name ) {
            
            switch( $column_name ) {
             
                case 'name' :
                      $r .= "<td>$item->name</td>";  
                      break;
                  
                case 'email':
                      $r .= "<td>$item->email</td>";
					  break;
					  
				case 'subject':
                      $r .= "<td>$item->subject</td>";
                    break;
                    
                case 'message':
                    $r .= "<td>$item->message</td>";
                    break;
    
				case 'created_at':
							$date = ( $item->created_at != '' ) ? date( 'Y-m-d', strtotime($item->created_at )) : '--';
                            $r .= "<td>".$date." </td>";
                      break; 
                  
                case 'action':
                      $r .= "<td>";
                    
                      $r .= "<span class='badge bg-gray'><a href='#' title='Edit Sub Admin'>Edit</a></span>";
                      
                      $r .= "<span class='badge bg-gray'><a href='#' title='Delete Sub Admin'>Delete</a></span>";
                      
                      $r .= "</td>";
                      break;       
            }
       }
        
        $r .= "</tr>";
        
        return $r;
        
    }
}

?>
