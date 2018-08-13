<?php

/*
 * Project - Namaste Panditji 
 * Version - 1.0
 * Date Created - 11/08/2018
 * Created By - Vishal Lele
 * File Name - np_table.php
 * Description - Abstract class for table to show list of item in HTML table
*/

abstract class Table 
{
    /**
     * current list of items
     * 
     * @var array
     */
    public $items;
    
    /**
     * array of pagination arguments
     * 
     * @var array
     */
    protected $_pagination_args;
    
    /**
     * column header list
     * 
     * @var array
     */
    public $_column_headers;
    
    
    /**
     * prepare list of item for displaying in table
    */
    abstract function prepare_items();
    
    /**
     * Get all column list
     * 
     * format : 'internal name' => 'Title'
     * 
     * @return array
     */
    abstract function get_columns();
    
    
    /**
     * Wether table has item to display list
     * 
     * @return bool
     */
    public function has_items(){
        return ( count($this->items)  === 0 ) ? false : true;
    }
    
    /**
     * Display message when there are not items to display
     * 
     * @return string
     */
    public function no_items(){
        return "No items found.";
    }
    
    /**
     * Return list of all sortable columns
     * 
     * @return array
     */
    protected function get_sortable_columns(){
        return array();
    }
   
    /**
     * display search box
     * 
     * @param string $input_id input box id attribute
     */
    abstract function search_box($input_id);
    
    /**
     * Generate table body element for list items
     * 
     * @return void
     */
     public function display_rows_or_placeholder() {
         if( $this->has_items() ) {
             $this->display_rows();
         } else {
             echo "<tr><td colspan=".$this->get_column_count().">".$this->no_items()."</td></tr>";
         }
         
     }
     
     /**
      * Generate table rows
      * 
      * @return void
      */
     abstract function display_rows();
     
     /**
      * Generate single row
      * 
      * @return void 
      */
     abstract function single_row( $item );
    
    
    /**
     * Get list all sortable, non-sortable columns list
     * 
     * @return array
     */
    protected function get_column_info(){
        
        $columns   = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $sortable);
        
        return $this->_column_headers;
        
    }
    
    /**
     * Return number of columns in table
     * 
     * @return integer
     */
    protected function get_column_count(){
        list( $columns ) = $this->get_column_info();
        return count($columns);
	}
	
	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array|string $args Array or string of arguments with information about the pagination.
	 */
	protected function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// Redirect if page number is invalid and headers are not already sent.
		if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}
    
    public function print_column_headers($with_id=true) {

        list($columns,$sortable) = $this->get_column_info();
        
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );
        
		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}
        
        foreach( $columns as $column_key => $column_display_name ){

			$class = array( 'manage-column', "column-$column_key" );
            
            if( isset( $sortable[$column_key] ) ) {
                
                $orderby = $sortable[$column_key];
                
                if( $current_orderby ==  $orderby ) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
                } else {
                    $order = 'asc';
					$class[] = 'sortable';
					$class[] = 'asc' == $current_order ? 'desc' : 'asc';
                }    
                
                /*$params = array( 'orderby' => $orderby, 'order' => $order );
                $currentQuery = Input::query();
                $q = array_merge( $currentQuery, $params );
                $queryString = http_build_query($q);*/
                
                $column_display_name = '<a href="'.esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ).'"><span>'.$column_display_name.'</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			//echo $column_key.'=>'.$column_display_name; 
			
            if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

				//echo "<pre>"; print_r($class); exit;

			echo "<$tag $scope $id $class>$column_display_name</$tag>";

			//echo "<pre>"; print_r($class); exit;
        }
        
        
	}

	/**
	 * Get the current page number
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max(1, $pagenum);
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $default = 20 ) {
		return $default;
	}
	

	/**
	 * Display the pagination.
	 *
	 * @param string $which
	*/
	protected function pagination( $which='bottom' ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

 		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev = true;
 		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
 		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
 		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
    
    public function display(){
    
    ?>

		<h1 class="wp-heading-inline">Appointment Leads</h1>
		
		<form id="post-filter" method="get">
			<?php $this->search_box(); ?>	

			<div class="tablenav top">
			<div class="alignleft actions">
			<?php $this->months_dropdown(); ?>
				<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">		</div>
			<div class='tablenav-pages'>
				<?php $this->pagination(); ?>
			</div>
			</div>

			<table class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>    
				</thead>
				<tbody>
					<?php $this->display_rows_or_placeholder(); ?>
				</tbody>
				<tfoot>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>    
				</tfoot>
			</table>
		</form>      
   <?php     
    }
    
    
}

?>
