<?php
/**
 * List Tables View
 *
 * @package TablePress
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * List Tables View class
 * @package TablePress
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class TablePress_List_View extends TablePress_View {

	/**
	 * Number of screen columns for the List View
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $screen_columns = 2;

	/**
	 * Object for the All Tables List Table
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected $wp_list_table;

	/**
	 * Set up the view with data and do things that are specific for this view
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for this view
	 * @param array $data Data for this view
	 */
	public function setup( $action, $data ) {
		parent::setup( $action, $data );

		add_thickbox();
		$this->admin_page->enqueue_script( 'list', array( 'jquery' ), array(
			'list' => array(
				'shortcode_popup' => __( 'To embed this table into a post or page, use this Shortcode:', 'tablepress' )
			)
		) );

		if ( $data['messages']['first_visit'] )
			$this->add_header_message(
				'<strong><em>Welcome!</em></strong><br />Thank you for using TablePress for the first time!<br/>'
				. $this->ajax_link( array( 'action' => 'hide_message', 'item' => 'first_visit', 'return' => 'list' ) , __( 'Hide', 'tablepress' ) )
			);

		if ( $data['messages']['plugin_update'] )
			$this->add_header_message(
				'<strong><em>Thank you for updating to TablePress' . TablePress::version . ' (revision ' . TablePress::db_version . ')</em></strong><br />'
				. $this->ajax_link( array( 'action' => 'hide_message', 'item' => 'plugin_update', 'return' => 'list' ) , __( 'Hide', 'tablepress' ) )
			);

		$this->action_messages = array(
			'success_delete' => _n( 'The table was deleted successfully.', 'The tables were deleted successfully.', 1, 'tablepress' ),
			'success_delete_plural' => _n( 'The table was deleted successfully.', 'The tables were deleted successfully.', 2, 'tablepress' ),
			'error_delete' => __( 'Error: The table could not be deleted.', 'tablepress' ),
			'success_copy' => _n( 'The table was copied successfully.', 'The tables were copied successfully.', 1, 'tablepress' ),
			'success_copy_plural' => _n( 'The table was copied successfully.', 'The tables were copied successfully.', 2, 'tablepress' ),
			'error_copy' => __( 'Error: The table could not be copied.', 'tablepress' ),
			'error_no_table' => __( 'Error: You did not specify a valid table ID.', 'tablepress' ),
			'error_load_table' => __( 'Error: This table could not be loaded!', 'tablepress' ),
			'error_bulk_action_invalid' => __( 'Error: This bulk action is invalid!', 'tablepress' ),
			'error_no_selection' => __( 'Error: You did not select any tables!', 'tablepress' ),
			'error_delete_not_all_tables' => __( 'Notice: Not all selected tables could be deleted!', 'tablepress' ),
			'error_copy_not_all_tables' => __( 'Notice: Not all selected tables could be copied!', 'tablepress' )
		);
		if ( $data['message'] && isset( $this->action_messages[ $data['message'] ] ) ) {
			$class = ( 'error' == substr( $data['message'], 0, 5 ) ) ? 'error' : 'updated';
			$this->add_header_message( "<strong>{$this->action_messages[ $data['message'] ]}</strong>", $class );
		}

		$this->add_meta_box( 'support', __( 'Support', 'tablepress' ), array( &$this, 'postbox_support' ), 'side' );
		$this->add_text_box( 'head1', array( &$this, 'textbox_head1' ), 'normal' );
		$this->add_text_box( 'head2', array( &$this, 'textbox_head2' ), 'normal' );
		$this->add_text_box( 'tables-list', array( &$this, 'textbox_tables_list' ), 'normal' );

		add_screen_option( 'per_page', array( 'label' => __( 'Tables', 'tablepress' ), 'default' => 20 ) ); // Admin_Controller contains function to allow changes to this in the Screen Options to be saved
		$this->wp_list_table = new TablePress_All_Tables_List_Table();
		$this->wp_list_table->set_items( $this->data['tables'] );
		$this->wp_list_table->prepare_items();
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function postbox_support( $data, $box ) {
		_e( 'These people are proud supporters of TablePress:', 'tablepress' );
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function textbox_head1( $data, $box ) {
		?>
		<p><?php _e( 'This is a list of all available tables.', 'tablepress' ); ?> <?php _e( 'You may add, edit, copy, delete or preview tables here.', 'tablepress' ); ?></p>
		<?php
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function textbox_head2( $data, $box ) {
		?>
		<p><?php printf( __( 'To insert the table into a page, post or text-widget, copy the shortcode %s and paste it into the corresponding place in the editor.', 'tablepress' ), '<input type="text" class="table-shortcode table-shortcode-inline" value="[' . TablePress::$shortcode . ' id=&lt;ID&gt; /]" readonly="readonly" />' ); ?> <?php _e( 'Each table has a unique ID that needs to be adjusted in that shortcode.', 'tablepress' ); ?> <?php printf( __( 'You can also click the button &quot;%s&quot; in the editor toolbar to select and insert a table.', 'tablepress' ), __( 'Table', 'tablepress' ) ); ?></p>
		<?php
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function textbox_tables_list( $data, $box ) {
		$this->wp_list_table->search_box( __( 'Search Tables', 'tablepress' ), 'tables_search' );
		$this->wp_list_table->display();
	}

	/**
	 * Return the content for the help tab for this screen
	 *
	 * @since 1.0.0
	 */
	protected function help_tab_content() {
		return 'Help for the List Tables screen';
	}

} // class TablePress_List_View

/**
 * TablePress All Tables List Table Class
 * @package TablePress
 * @subpackage Views
 * @author Tobias Bäthge
 * @see http://codex.wordpress.org/Class_Reference/WP_List_Table
 * @since 1.0.0
 * @uses WP_List_Table
 */
class TablePress_All_Tables_List_Table extends WP_List_Table {

	/**
	 * Initialize the List Table
	 *
	 * @since 1.0.0
	 */
	public function __construct(){
		parent::__construct( array(
			'singular'	=> 'tablepress-table',		// singular name of the listed records
			'plural'	=> 'tablepress-all-tables',	// plural name of the listed records
			'ajax'		=> false					// does this list table support AJAX?
		) );
	}

	/**
	 * Set the data (here: Tables) that are to be displayed by the List Tables
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Tables to be displayed in the List Table
	 */
	public function set_items( $items ) {
		$this->items = $items;
	}

	/**
	 * Check whether the user has permissions for certain AJAX actions
	 * (not used, but must be implemented in this child class)
	 *
	 * @since 1.0.0
	 *
	 * @return bool true (Default value)
	 */
	public function ajax_user_can() {
		return true;
	}

	/**
	 * Get a list of columns in this List Table.
	 * Format: 'internal-name' => 'Column Title'
	 *
	 * @since 1.0.0
	 *
	 * @return array List of columns in this List Table
	 */
	public function get_columns(){
		$columns = array(
			'cb' => $this->has_items() ? '<input type="checkbox" class="hide-if-no-js" />' : '', // checkbox for "Select all", but only if there are items in the table
			'table_id' => __( 'ID', 'tablepress' ),
			'table_name' => __( 'Table Name', 'tablepress' ), // just "name" is special in WP, which is why we prefix every entry here, to be safe!
			'table_description' => __( 'Description', 'tablepress' ),
			'table_author' => __( 'Author', 'tablepress' ),
			'table_last_modified' => __( 'Last Modified', 'tablepress' )
		);
		return $columns;
	}

	/**
	 * Get a list of columns that are sortable
	 * Format: 'internal-name' => array( $field for $item[$field], true for already sorted )
	 *
	 * @since 1.0.0
	 *
	 * @return array List of sortable columns in this List Table
	 */
	public function get_sortable_columns() {
		// no sorting on the Empty List placeholder
		if ( ! $this->has_items() )
			return array();

		$sortable_columns = array(
			'table_id' => array( 'id', true ), //true means its already sorted
			'table_name' => array( 'name', false ),
			'table_description' => array( 'description', false ),
			'table_author' => array( 'author', false ),
			'table_last_modified' => array( 'last_modified', false )
		);
		return $sortable_columns;
	}

	/**
	 * Render a cell in the "cb" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_cb( $item ) {
		return '<input type="checkbox" name="table[]" value="' . esc_attr( $item['id'] ) . '" />';
	}

	/**
	 * Render a cell in the "table_id" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_table_id( $item ) {
		return esc_html( $item['id'] );
	}

	/**
	 * Render a cell in the "table_name" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_table_name( $item ) {
		$edit_url = TablePress::url( array( 'action' => 'edit', 'table_id' => $item['id'] ) );
		$copy_url = TablePress::url( array( 'action' => 'copy_table', 'item' => $item['id'], 'return' => 'list', 'return_item' => $item['id'] ), true, 'admin-post.php' );
		$delete_url = TablePress::url( array( 'action' => 'delete_table', 'item' => $item['id'], 'return' => 'list', 'return_item' => $item['id'] ), true, 'admin-post.php' );
		$preview_url = TablePress::url( array( 'action' => 'preview_table', 'item' => $item['id'], 'return' => 'list', 'return_item' => $item['id'] ), true, 'admin-post.php' );
		if ( '' == trim( $item['name'] ) )
			$item['name'] = __( '(no name)', 'tablepress' );

		$row_text = '<strong><a title="' . sprintf ( __( 'Edit &#8220;%s&#8221;', 'tablepress' ), esc_attr( $item['name'] ) ) . '" class="row-title" href="' . $edit_url . '">' . esc_html( $item['name'] ) . '</a></strong>';
		$row_actions = array(
			'edit' => sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $edit_url, sprintf ( __( 'Edit &#8220;%s&#8221;', 'tablepress' ), esc_attr( $item['name'] ) ), __( 'Edit', 'tablepress' ) ),
			'shortcode hide-if-no-js' => sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', '#', '[' . TablePress::$shortcode . ' id=' . esc_attr( $item['id'] ) . ' /]', __( 'Shortcode', 'tablepress' ) ),
			'copy' => sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $copy_url, sprintf ( __( 'Copy &#8220;%s&#8221;', 'tablepress' ), esc_attr( $item['name'] ) ), __( 'Copy', 'tablepress' ) ),
			'delete' => sprintf( '<a href="%1$s" title="%2$s" class="delete-link">%3$s</a>', $delete_url, sprintf ( __( 'Delete &#8220;%s&#8221;', 'tablepress' ), esc_attr( $item['name'] ) ), __( 'Delete', 'tablepress' ) ),
			'table-preview' => sprintf( '<a href="%1$s" title="%2$s" target="_blank">%3$s</a>', $preview_url, sprintf ( __( 'Show a preview of &#8220;%s&#8221;', 'tablepress' ), esc_attr( $item['name'] ) ), __( 'Preview', 'tablepress' ) )
		);

		return $row_text . $this->row_actions( $row_actions );
	}

	/**
	 * Render a cell in the "table_description" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_table_description( $item ){
		if ( '' == trim( $item['description'] ) )
			$item['description'] = __( '(no description)', 'tablepress' );
		return esc_html( $item[ 'description' ] );
	}

	/**
	 * Render a cell in the "table_author" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_table_author( $item ){
		return TablePress::get_last_editor( $item['author'] );
	}

	/**
	 * Render a cell in the "table_last_modified" column
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Data item for the current row
	 * @return string HTML content of the cell
	 */
	protected function column_table_last_modified( $item ){
		return TablePress::format_datetime( $item['last_modified'], 'mysql', '<br/>' );
	}

	/**
	 * Get a list (name => title) bulk actions that are available
	 *
	 * @since 1.0.0
	 *
	 * @return array Bulk actions for this table
	 */
	public function get_bulk_actions() {
		$bulk_actions = array(
			'copy' => __( 'Copy', 'tablepress' ),
			'delete' => __( 'Delete', 'tablepress' )
		);
		return $bulk_actions;
	}

	/**
	 * Render the bulk actions dropdown
	 * In comparsion with parent class, this has modified HTML (especially no field named "action" as that's being used already)!
	 *
	 * @since 1.0.0
	 */
	public function bulk_actions() {
		$screen = get_current_screen();

		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			// This filter can currently only be used to remove actions.
			$this->_actions = apply_filters( 'bulk_actions-' . $screen->id, $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
			$name_id = 'bulk-action-top';
		} else {
			$two = '2';
			$name_id = 'bulk-action-bottom';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='$name_id' id='$name_id'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";
		foreach ( $this->_actions as $name => $title ) {
			echo "\t<option value='$name'$>$title</option>\n";
		}
		echo "</select>\n";
		echo '<input type="submit" name="" id="doaction' . $two . '" class="button-secondary action" value="' . __( 'Apply', 'tablepress' ) . '" />' . "\n";
	}

	/**
	 * Holds the message to be displayed when there are no items in the table
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		$add_url = TablePress::url( array( 'action' => 'add' ) );
		$import_url = TablePress::url( array( 'action' => 'import' ) );
		echo __( 'No tables found.', 'tablepress' ) . ' ' . sprintf( __( 'You should <a href="%s">add</a> or <a href="%s">import</a> a table to get started!', 'tablepress' ), $add_url, $import_url );
	}

	/**
	 * Generate the elements above or below the table (like bulk actions and pagination)
	 * In comparsion with parent class, this has modified HTML (no nonce field), and a check whether there are items.
	 *
	 * @since 1.0.0
	 *
	 * @param string $which Location ("top" or "bottom")
	 */
	public function display_tablenav( $which ) {
		if ( ! $this->has_items() )
			return;
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
		<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
		?>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Callback to determine whether the given $item contains the search term
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Item that shall be searched
	 * @return bool Whether the search term was found or not
	 */
	protected function _search_callback( $item ) {
		static $term;
		if ( is_null( $term ) )
			$term = stripslashes( $_GET['s'] );

		// search from easy to hard, so that "expensive" code maybe doesn't have to run
		if ( false !== stripos( $item['id'], $term )
		|| false !== stripos( $item['name'], $term )
		|| false !== stripos( $item['description'], $term )
		|| false !== stripos( TablePress::get_last_editor( $item['author'] ), $term )
		|| false !== stripos( TablePress::format_datetime( $item['created'], 'mysql', ' ' ), $term )
		|| false !== stripos( json_encode( $item['data'] ), $term ) )
			return true;

		return false;
	}

	/**
	 * Callback to for the array sort function
	 *
	 * @since 1.0.0
	 *
	 * @param array $item_a First item that shall be compared to...
	 * @param array $item_b the second item
	 * @return int (-1, 0, 1) depending on which item sorts "higher"
	 */
	protected function _order_callback( $item_a, $item_b ) {
		global $orderby, $order;

		if ( $item_a[$orderby] == $item_b[$orderby] )
			return 0;

		// certain fields require some extra work before being sortable
		switch( $orderby ) {
			case 'last_modified':
				// Compare UNIX timestamps for "last modified", which actually is a mySQL datetime string
				$result = ( strtotime( $item_a['last_modified'] ) > strtotime( $item_b['last_modified'] ) ) ? 1 : -1;
				break;
			case 'author':
				// Get the actual author name, plain value is just the user ID
				$result = strnatcasecmp( TablePress::get_last_editor( $item_a['author'] ), TablePress::get_last_editor( $item_b['author'] ) );					break;
			default:
				// other fields (ID, name, description) are sorted as strings
				$result = strnatcasecmp( $item_a[$orderby], $item_b[$orderby] );
		}

		return ( 'asc' == $order ) ? $result : - $result;
	}	
	
	/**
	 * Prepares the list of items for displaying, by maybe searching and sorting, and by doing pagination
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		global $orderby, $order, $s;
		wp_reset_vars( array( 'orderby', 'order', 's' ) );

		// Maybe search in the items
		if ( $s )
			$this->items = array_filter( $this->items, array( &$this, '_search_callback' ) );

		// Maybe sort the items		
		$_sortable_columns = $this->get_sortable_columns();
		if ( $orderby && ! empty( $this->items ) && isset( $_sortable_columns["table_{$orderby}"] ) )
			usort( $this->items, array( &$this, '_order_callback' ) );

		// number of records to show per page
		$per_page = $this->get_items_per_page( 'tablepress_list_per_page', 20 ); // hard-coded, as in filter in Admin_Controller
		// page number the user is currently viewing
		$current_page = $this->get_pagenum();
		// number of records in the array
		$total_items = count( $this->items );

		// Slice items array to hold only items for the current page
		$this->items = array_slice( $this->items, ( ( $current_page-1) * $per_page ), $per_page );
		
		// Register pagination options and calculation results
		$this->set_pagination_args( array(
			'total_items' => $total_items,					// total number of records/items
			'per_page'	  => $per_page,						// number of items per page
			'total_pages' => ceil( $total_items/$per_page )	// total number of pages
		) );
	}

} // class TablePress_All_Tables_List_Table