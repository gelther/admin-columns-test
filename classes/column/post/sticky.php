<?php

/**
 * CPAC_Column_Post_Sticky
 *
 * @since 2.0.0
 */
class CPAC_Column_Post_Sticky extends CPAC_Column {

	function __construct( $storage_model ) {		
		
		// define properties		
		$this->properties['type']	 	= 'column-sticky';
		$this->properties['label']	 	= __( 'Sticky', CPAC_TEXTDOMAIN );
			
		parent::__construct( $storage_model );
	}
	
	/**
	 * @see CPAC_Column::get_value()
	 * @since 2.0.0
	 */
	function get_value( $post_id ) {
		if ( ! has_post_thumbnail( $post_id ) )
			return false;
			
		return $this->get_thumbnails( get_post_thumbnail_id( $post_id ), $this->options );		
	}
}