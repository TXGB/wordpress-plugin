<?php

if (! class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Service_List_Table extends WP_List_Table {

	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	public function __construct() {
		$args = array(
			'singular' => 'txgb_service',
			'plural'   => 'txgb_services',
			'ajax'     => false,
		);

		parent::__construct( $args );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			'checkbox',
		);
	}

	public function no_items() {
		_e( 'No services found.', 'txgb' );
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			// echo "I'm before the table";
		}
		if ( $which == "bottom" ) {
			// echo "I'm after the table";
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-import' => 'Import'
		];

		return $actions;
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __('Name'),
			'categories'  => __('Categories'),
			'city'        => __('City'),
			'description' => __('Description'),
		);
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return array();
	}

	public function prepare_items() {
		global $_wp_column_headers;

		$this->process_bulk_action();
		$preparedItems = array();

		foreach ( $this->items as $item ) {

			$preparedItems[] = array(
				'id'          => $item->id,
				'name'        => $item->name,
				'categories'  => $item->categories,
				'city'        => $item->address['city'],
				'description' => $item->summary,
			);

		}

		$columns = $this->get_columns();
		$_wp_column_headers[$this->screen->id] = $columns;

		$this->items = $preparedItems;
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['id']
		);
	}

	public function column_default ( $item, $column_name ) {
		return $item[ $column_name ];
	}

	public function column_date ( $item ) {
		return 'Last updated at:<br>'. $item['date'];
	}

	public function column_checkbox ( $item ) {
		return '<input type="checkbox" name="provider_ids[]" value="'. $item['id'] .'" />';
	}

	public function column_categories ( $item ) {
		$labels = [];

		foreach ( $item['categories'] as $category ) {
			$labels[] = $category;
		}

		return implode(', ', $labels);
	}

	public function process_bulk_action() {
		if ( $this->current_action() ) {
			$action = $this->current_action();
		} else {
			$action = array_key_exists( 'action', $_POST ) ? $_POST['action'] : '';
		}

		if ( 'import' === $action ) {
			do_action( 'admin_post_txgb_import_providers', [$_POST['txgb_service']] );
		} elseif ( 'bulk-import' === $action ) {
			do_action( 'admin_post_txgb_import_providers', $_POST['txgb_service'] );
		}
	}
}
