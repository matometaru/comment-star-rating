<?php
class CSR_Controller {

	/**
	 * Rendering template
	 *
	 * @param string $template {directory name}/{file name (no need extension)}
	 * @param array Array of data you want to assign
	 * @return void
	 */
	protected function _render( $template, array $args = array() ) {
		extract( $args );
		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		include $template_path;
	}

	protected function _generator( $template, array $args = array() ) {
		extract( $args );
		$template_dir  = plugin_dir_path( __FILE__ ) . '../../templates/';
		$template_path = $template_dir . $template . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		ob_start();
		include $template_path;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
