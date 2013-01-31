<?php
class CPAC_Utility {

	/**
	 * Get column types
	 *
	 * @since 1.1.0
	 *
	 * @return array CPAC types objects
	 */
	public static function get_types( $storage_key = '' )
	{
		$types = array();
		
		// Post
		foreach ( CPAC_Utility::get_post_types() as $post_type ) {
			$type = new CPAC_Type_Post( $post_type );
			
			$types[$type->storage_key] = $type;
		}
		
		// @todo: register type function
		
		if ( isset( $types[$storage_key] ) ) {
			return $types[$storage_key];
		}
		
		return $types;
	} 	
	
	/**
	 * Get post types
	 *
	 * @since 1.0.0
	 *
	 * @return array Posttypes
	 */
	public static function get_post_types() {
		$post_types = get_post_types( array(
			'_builtin' => false
		));
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';

		return apply_filters( 'cpac_get_post_types', $post_types );
	}

	/**
	 * Checks if column name is a csutom field
	 *
	 * Check for custom fields, such as column-meta-[customfieldname]
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name
	 * @return bool
	 */
	public static function is_column_customfield( $column_name = '' ) {
		if ( strpos( $column_name, 'column-meta-' ) !== false )
			return true;

		return false;
	}

	/**
	 * Checks if column name is a taxonomy
	 *
	 * Check for taxonomies, such as column-taxonomy-[taxname]
	 *
	 * @since 2.0.0
	 *
	 * @param string $column_name Column name
	 * @return bool
	 */
	public static function is_column_taxonomy( $column_name = '' ) {
		if ( 0 === strpos( $column_name, 'column-taxonomy-' ) || 0 === strpos( $column_name, 'taxonomy-' ) )
			return true;

		return false;
	}

	/**
	 * Get column name type
	 *
	 * @since 2.0.0
	 *
	 * @param string $column_name Column name
	 * @return string Column Name Type
	 */
	public static function get_column_name_type( $column_name ) {

		if ( CPAC_Utility::is_column_taxonomy( $column_name ) ) {
			$column_name = 'column-taxonomy';
		}

		if ( CPAC_Utility::is_column_customfield( $column_name ) ) {
			$column_name = 'column-meta';
		}

		return $column_name;
	}

	/**
	 * Get the posttype from columnname
	 *
	 * Check for post count: column-user_postcount-[posttype]
	 *
	 * @since 1.3.1
	 *
	 * @param string $id
	 * @return string Posttype
	 */
	public static function get_posttype_by_postcount_column( $column_name = '' ) {
		if ( strpos( $column_name, 'column-user_postcount-' ) !== false ) {
			return str_replace( 'column-user_postcount-', '', $column_name );
		}

		return false;
	}

	/**
	 * Get the taxonomy from columnname
	 *
	 * Return the taxonomy: column-taxonomy-[taxonomy]
	 *
	 * @since 2.0.0
	 *
	 * @param string $id
	 * @return string Posttype
	 */
	public static function get_taxonomy_by_column_name( $column_name = '' ) {
		if ( ! CPAC_Utility::is_column_taxonomy( $column_name ) )
			return false;

		return str_replace( array( 'column-taxonomy-', 'taxonomy-' ), '', $column_name );
	}

	/**
	 * Sanitize label
	 *
	 * Uses intern wordpress function esc_url so it matches the label sorting url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 * @return string Sanitized string
	 */
	public static function sanitize_string( $string ) {
		$string = esc_url( $string );
		$string = str_replace( 'http://','', $string );
		$string = str_replace( 'https://','', $string );

		return $string;
	}

	/**
	 * Get column options from DB
	 *
	 * @since 1.0.0
	 *
	 * @paran string $storage_key
	 * @return array Column options
	 */
	public static function get_stored_columns( $storage_key ) {
		// get plugin options
		$options = get_option('cpac_options');

		// get saved columns
		if ( empty( $options['columns'][$storage_key] ) )
			return false;

		return $options['columns'][$storage_key];
	}

	/**
	 * Get post count
	 *
	 * @since 1.3.1
	 *
	 * @param string $post_type
	 * @param int $user_id
	 * @return int Postcount
	 */
	public static function get_post_count( $post_type, $user_id ) {
		global $wpdb;

		if ( ! post_type_exists( $post_type ) || empty( $user_id ) )
			return false;

		$sql = "
			SELECT COUNT(ID)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_author = %d
			AND post_type = %s
		";

		return $wpdb->get_var( $wpdb->prepare( $sql, $user_id, $post_type ) );
	}

	/**
	 * Strip tags and trim
	 *
	 * @since     1.3
	 */
	public static function strip_trim($string)
	{
		return trim(strip_tags($string));
	}

	/**
	 * Get column value of post attachments
	 *
	 * @since 1.2.1
	 *
	 * @param int $post_id
	 * @return array Attachment ID's
	 */
	public static function get_attachment_ids( $post_id ) {
		return get_posts( array(
			'post_type' 	=> 'attachment',
			'numberposts' 	=> -1,
			'post_status' 	=> null,
			'post_parent' 	=> $post_id,
			'fields' 		=> 'ids'
		));
	}
	
	/**
	 * Get licenses
	 *
	 * @since 2.0.0
	 *
	 * @return array Licenses.
	 */
	function get_licenses() {		
		
		return array(
			'sortable' 		=> new CPAC_Licence( 'sortable' ),
			'customfields' 	=> new CPAC_Licence( 'customfields' )
		);
	}	
	
	/**
	 * Get author field by nametype
	 *
	 * Used by posts and sortable
	 *
	 * @since 1.4.6.1
	 *
	 * @param string $nametype
	 * @param int $user_id
	 * @return string Author
	 */
	public static function get_author_field_by_nametype( $nametype, $user_id ) {
		$userdata = get_userdata( $user_id );

		switch ( $nametype ) :

			case "display_name" :
				$name = $userdata->display_name;
				break;

			case "first_name" :
				$name = $userdata->first_name;
				break;

			case "last_name" :
				$name = $userdata->last_name;
				break;

			case "first_last_name" :
				$first = !empty($userdata->first_name) ? $userdata->first_name : '';
				$last = !empty($userdata->last_name) ? " {$userdata->last_name}" : '';
				$name = $first.$last;
				break;

			case "nickname" :
				$name = $userdata->nickname;
				break;

			case "username" :
				$name = $userdata->user_login;
				break;

			case "email" :
				$name = $userdata->user_email;
				break;

			case "userid" :
				$name = $userdata->ID;
				break;

			default :
				$name = $userdata->display_name;

		endswitch;

		return $name;
	}

	/**
	 * Admin message
	 *
	 * @since 1.5.0
	 *
	 * @param string $message Message.
	 * @param string $type Update Type.
	 */
	public static function admin_message( $message = '', $type = 'updated' ) {
		$GLOBALS['cpac_message']	  = $message;
		$GLOBALS['cpac_message_type'] = $type;

		add_action('admin_notices', array( 'CPAC_Utility', 'admin_notice' ) );
	}

	/**
	 * Admin Notice
	 *
	 * @since 1.5.0
	 *
	 * @return string Message.
	 */
	public static function admin_notice() {
	    echo '<div class="' . $GLOBALS['cpac_message_type'] . '" id="message">' . $GLOBALS['cpac_message'] . '</div>';
	}
}