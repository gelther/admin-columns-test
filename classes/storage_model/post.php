<?php

class CPAC_Storage_Model_Post extends CPAC_Storage_Model {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	function __construct( $post_type ) {		
		
		$this->key 		= $post_type;		
		$this->label 	= $this->get_label();
		$this->type 	= 'post';
		
		// headings
		add_filter( "manage_edit-{$post_type}_columns",  array( $this, 'add_headings' ) );
		
		// values
		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'manage_value' ), 10, 2 );
		
		parent::__construct();
	}
	
	/**
	 * Get Label
	 *
	 * @since 1.5.0
	 *
	 * @return string Singular posttype name
	 */
	private function get_label() {
		$posttype_obj = get_post_type_object( $this->key );

		return $posttype_obj->labels->singular_name;
	}	
	
	/**
	 * Get WP default supported admin columns per post type.
	 *
	 * @see CPAC_Type::get_default_columns()
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_default_columns() {
		
		// You can use this filter to add thirdparty columns by hooking into this.
		// See classes/third_party.php for an example.
		do_action( "cpac_before_default_columns_posts" );
		do_action( "cpac_before_default_columns_{$this->key}" );

		// some plugins directly hook into get_column_headers, such as: WooCommerce.
		// @todo: create conflicts, where the stored admin columns columns get included as default columns.
		// $columns = get_column_headers( 'edit-' . $this->key );
		
		// used for getting columns
		if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) )
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' ) )
			require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );

		// As of WP Release 3.5 we can use the following.
		$table 		= new WP_Posts_List_Table( array( 'screen' => $this->key ) );
		$columns 	= $table->get_columns();
	
		if ( empty( $columns ) )
			return false;
		
		return $columns;
	}
	
	/**
	 * Maybe add hidden meta
	 *
	 * @since 1.5
	 *
	 * @param array $fields Custom fields.
	 * @return array Custom fields.
	 */
	private function maybe_add_hidden_meta( $fields ) {
		if ( ! $fields )
			return false;

		$combined_fields = array();

		$use_hidden_meta = apply_filters( 'cpac_use_hidden_custom_fields', false );

		// filter out hidden meta fields
		foreach ( $fields as $field ) {

			// give hidden fields a prefix for identifaction
			if ( $use_hidden_meta && substr( $field[0], 0, 1 ) == "_") {
				$combined_fields[] = 'cpachidden'.$field[0];
			}

			// non hidden fields are saved as is
			elseif ( substr( $field[0], 0, 1 ) != "_" ) {
				$combined_fields[] = $field[0];
			}
		}

		if ( empty( $combined_fields ) )
			return false;

		return $combined_fields;
	}
	
	/**
     * Get Meta Keys
     *
	 * @since 1.5.0
	 *
	 * @return array
     */
    public function get_meta_keys() {
        global $wpdb;

		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.post_type = %s ORDER BY 1", $this->key ), ARRAY_N );

		if ( is_wp_error( $fields ) )
			$fields = false;

		return apply_filters( "cpac_get_meta_keys_{$this->key}", $this->maybe_add_hidden_meta( $fields ), $this->key );
    }
	
	/**
	 * Manage value
	 *
	 * @since 2.0.0
	 *
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function manage_value( $column_name, $post_id ) {		
		
		//$cols = $this->get_columns();
		
		//print_R( $cols );
		//exit;
		
		
		$value = '';
		
		//echo $column_name . "<br/>";
		//echo $post_id . "<br/>";
		
		// get column instance
		if ( $column = $this->get_column_by_name( $column_name ) ) {
			//print_R( $column );
			
			$value = $column->get_value( $post_id );
		}
		
		
		
		$value = apply_filters( "cpac_value_posts", $value, $column );
		$value = apply_filters( "cpac_value_{$this->key}", $value, $column );
		
		echo $value;
	}
}