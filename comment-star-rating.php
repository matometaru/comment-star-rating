<?php
/**
 * Plugin Name: Comment Star Rating
 * Description: WordPress plugin to rate comments on 5 beautiful stars.
 * Author: Matometaru
 * Author URI: http://matometaru.com/
 * Version: 1.2
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
require_once plugin_dir_path( __FILE__ ) . 'classes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/config.php';

/**
 * CommentStarRating
 */
class CommentStarRating {

	/**
	 * Constructor.
	 *
	 * @param string $url プラグインURL.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, '_init' ), 11 );
		add_action( 'wp', array( $this, 'init' ), 11 );
	}

	/**
	 * Load classes
	 * @return void
	 */
	public function _load_initialize_files() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		$includes        = array(
			'/classes/abstract',
			'/classes/controllers',
			'/classes/models',
			'/classes/services',
		);
		foreach ( $includes as $include ) {
			foreach ( glob( $plugin_dir_path . $include . '/*.php' ) as $file ) {
				require_once $file;
			}
		}
	}

	public function _init() {
		$csr_option = CSR_Option::find();
		new CSR_Admin_Controller( $csr_option );
		new CSR_Comment_Controller();
	}

	/**
	 * Init all.
	 */
	public function init() {
		$csr_option = CSR_Option::find();

		new CSR_Main_Controller( $csr_option );
	}
}

$comment_star_rating = new CommentStarRating( $url );
