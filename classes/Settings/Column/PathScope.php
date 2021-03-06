<?php

class AC_Settings_Column_PathScope extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	/**
	 * @var string
	 */
	private $path_scope;

	protected function define_options() {
		return array(
			'path_scope' => 'full',
		);
	}

	public function create_view() {
		$select = $this->create_element( 'select', 'path_scope' )
		               ->set_options( array(
			               'full'             => __( 'Full path', 'codepress-admin-columns' ),
			               'relative-domain'  => __( 'Relative to domain', 'codepress-admin-columns' ),
			               'relative-uploads' => __( 'Relative to main uploads folder ', 'codepress-admin-columns' ),
		               ) );

		$view = new AC_View( array(
			'label'   => __( 'Path scope', 'codepress-admin-columns' ),
			'tooltip' => __( 'Part of the file path to display', 'codepress-admin-columns' ),
			'setting' => $select,
		) );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_path_scope() {
		return $this->path_scope;
	}

	/**
	 * @param string $path_scope
	 *
	 * @return bool
	 */
	public function set_path_scope( $path_scope ) {
		$this->path_scope = $path_scope;

		return true;
	}

	public function format( $value, $original_value ) {
		$file = $value;
		$value = '';

		if ( $file ) {
			$file = str_replace( 'https://', 'http://', $file );

			switch ( $this->get_path_scope() ) {
				case 'relative-domain' :
					$url = str_replace( 'https://', 'http://', home_url( '/' ) );

					if ( strpos( $file, $url ) === 0 ) {
						$file = '/' . substr( $file, strlen( $url ) );
					}

					break;
				case 'relative-uploads' :
					$upload_dir = wp_upload_dir();
					$url = str_replace( 'https://', 'http://', $upload_dir['baseurl'] );

					if ( strpos( $file, $url ) === 0 ) {
						$file = substr( $file, strlen( $url ) );
					}

					break;
			}

			$value = $file;
		}

		return $value;
	}

}