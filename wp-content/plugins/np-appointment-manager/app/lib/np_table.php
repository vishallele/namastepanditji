<?php

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @version 1.0.0
 * @access private
 */
abstract class NP_List_Table {

	/**
	 * The current list of items.
	 *
	 * @var array
	 */
	public $items;

	/**
	 * Various information needed for displaying the pagination.
	 *
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * Cached bulk actions.
	 *
	 * @var array
	 */
	private $_actions;

	/**
	 * Cached pagination output.
	 *
	 * @var string
	 */
	private $_pagination;
	
	/**
	 * Stores the value returned by ->get_column_info().
	 *
	 * @var array
	 */
	protected $_column_headers;

	/**
	 * Prepares the list of items for displaying.
	 * @abstract
	 */
	abstract function prepare_items();

	/**
	 * An internal method that sets all the necessary pagination arguments
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

	/**
	 * Access the pagination args.
	 *
	 * @param string $key Pagination argument to retrieve. Common values include 'total_items',
	 *                    'total_pages', 'per_page', or 'infinite_scroll'.
	 * @return int Number of items that correspond to the given pagination argument.
	 */
	public function get_pagination_arg( $key ) {
		if ( 'page' === $key ) {
			return $this->get_pagenum();
		}

		if ( isset( $this->_pagination_args[$key] ) ) {
			return $this->_pagination_args[$key];
		}
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @return bool
	 */
	public function has_items() {
		return !empty( $this->items );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 */
	public function no_items() {
		_e( 'No items found.' );
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
<?php
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array();
	}

	/**
	 * Display the bulk actions dropdown.
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 * This is designated as optional for backward compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();
			/**
			 * Filters the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-post", $this->_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

			echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 * @return string|false The action name or False if no action was selected
	 */
	public function current_action() {
		if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
			return false;

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}

	/**
	 * Generate row actions div
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}

	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @global wpdb      $wpdb
	 * @global WP_Locale $wp_locale
	 *
	 * @param string $post_type
	 */
	protected function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;

		/**
		 * Filters whether to remove the 'Months' drop-down from the post list table.
		 *
		 * @param bool   $disable   Whether to disable the drop-down. Default false.
		 * @param string $post_type The post type.
		 */
		if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
			return;
		}

		$extra_checks = "AND post_status != 'auto-draft'";
		if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
			$extra_checks .= " AND post_status != 'trash'";
		} elseif ( isset( $_GET['post_status'] ) ) {
			$extra_checks = $wpdb->prepare( ' AND post_status = %s', $_GET['post_status'] );
		}

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			$extra_checks
			ORDER BY post_date DESC
		", $post_type ) );

		/**
		 * Filters the 'Months' drop-down results.
		 *
		 * @since 3.7.0
		 *
		 * @param object $months    The months drop-down query results.
		 * @param string $post_type The post type.
		 */
		$months = apply_filters( 'months_dropdown_results', $months, $post_type );

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


	/**
	 * Get the current page number
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		/**
		 * Filters the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @since 2.9.0
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( "{$option}", $per_page );
	}

	/**
	 * Display the pagination.
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
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

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 * @abstract
	 *
	 * @return array
	 */
	abstract function get_columns();

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column, in this case, an empty string.
	 */
	protected function get_default_primary_column_name() {
		$columns = $this->get_columns();
		$column = '';

		if ( empty( $columns ) ) {
			return $column;
		}

		// We need a primary defined so responsive views show something,
		// so let's fall back to the first non-checkbox column.
		foreach ( $columns as $col => $column_name ) {
			if ( 'cb' === $col ) {
				continue;
			}

			$column = $col;
			break;
		}

		return $column;
	}

	/**
	 * Public wrapper for WP_List_Table::get_default_primary_column_name().
	 *
	 * @return string Name of the default primary column.
	 */
	public function get_primary_column() {
		return $this->get_primary_column_name();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		$columns = $this->get_columns();
		$default = $this->get_default_primary_column_name();

		// If the primary column doesn't exist fall back to the
		// first non-checkbox column.
		if ( ! isset( $columns[ $default ] ) ) {
			$default = NP_List_Table::get_default_primary_column_name();
		}

		/**
		 * Filters the name of the primary column for the current list table.
		 *
		 * @param string $default Column name default for the specific list table, e.g. 'name'.
		 * @param string $context Screen ID for specific list table, e.g. 'plugins'.
		 */
		$column  = $default;

		if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
			$column = $default;
		}

		return $column;
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @return array
	 */
	protected function get_column_info() {
		// $_column_headers is already set / cached
		if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
			// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
			// In 4.3, we added a fourth argument for primary column.
			$column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
			foreach ( $this->_column_headers as $key => $value ) {
				$column_headers[ $key ] = $value;
			}

			return $column_headers;
		}

		$columns = $this->get_columns();
		$sortable_columns = $this->get_sortable_columns();
		$hidden = array();
		/**
		 * Filters the list table sortable columns for a specific screen.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @param array $sortable_columns An array of sortable columns.
		 */
		$_sortable = $sortable_columns;

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$primary = $this->get_primary_column_name();
		$this->_column_headers = array( $columns, $hidden, $sortable, $primary );

		return $this->_column_headers;
	}

	/**
	 * Return number of visible columns
	 *
	 * @return int
	 */
	public function get_column_count() {
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
		return count( $columns ) - count( $hidden );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @staticvar int $cb_counter
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

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

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}

	/**
	 * Display the table
	 */
	public function display() {
		
		$this->display_tablenav( 'top' );
?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody id="the-list">
		<?php $this->display_rows_or_placeholder(); ?>
	</tbody>

	<tfoot>
	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>
	</tfoot>

</table>
<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Generate the table navigation above or below the table
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<?php if ( $this->has_items() ): ?>
		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
		<?php endif;
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {}

	/**
	 * Generate the tbody element for the list table.
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 */
	public function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		echo '<tr>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	protected function column_default( $item, $column_name ) {}

	/**
	 *
	 * @param object $item
	 */
	protected function column_cb( $item ) {}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data,
					$primary
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo $this->handle_row_actions( $item, $column_name, $primary );
				echo "</td>";
			}
		}
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @param object $item        The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string The row actions HTML, or an empty string if the current column is the primary column.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		return $column_name === $primary ? '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>' : '';
 	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 */
	public function ajax_response() {
		$this->prepare_items();

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();

		$response = array( 'rows' => $rows );

		if ( isset( $this->_pagination_args['total_items'] ) ) {
			$response['total_items_i18n'] = sprintf(
				_n( '%s item', '%s items', $this->_pagination_args['total_items'] ),
				number_format_i18n( $this->_pagination_args['total_items'] )
			);
		}
		if ( isset( $this->_pagination_args['total_pages'] ) ) {
			$response['total_pages'] = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
		}

		die( wp_json_encode( $response ) );
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 */
	public function _js_vars() {
		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->screen->id,
				'base' => $this->screen->base,
			)
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
	}
}
