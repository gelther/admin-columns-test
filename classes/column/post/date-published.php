<?php
defined( 'ABSPATH' ) or die();

/**
 * @since 2.4
 */
class CPAC_Column_Post_Date_Published extends CPAC_Column {

	public function init() {
		parent::init();

		$this->properties['type'] = 'column-date_published';
		$this->properties['label'] = __( 'Date Published' );
	}

	public function get_value( $post_id ) {
		$raw_value = $this->get_raw_value( $post_id );
		if ( ! $this->get_option( 'date_format' ) ) {
			return $this->get_date( $raw_value ) . ' ' . $this->get_time( $raw_value );
		}

		return $this->get_date( $raw_value, $this->get_option( 'date_format' ) );
	}

	public function get_raw_value( $post_id ) {
		$post = get_post( $post_id );

		return $post->post_date;
	}

	public function display_settings() {
		$this->display_field_date_format();
	}
}