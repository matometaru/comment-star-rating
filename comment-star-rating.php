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

/**
 * CommentStarRating
 */
class CommentStarRating {

	private $csr_option;

	/**
	 * Constructor.
	 *
	 * @param string $url プラグインURL.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		add_action( 'after_setup_theme', array( $this, '_after_setup_theme' ), 11 );
		add_action( 'wp', array( $this, '_main' ) );
	}

	/**
	 * プラグインがロード時に実行する処理.
	 *
	 * @return void
	 */
	public function _load_initialize_files() {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/functions.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/config.php';

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

	/**
	 * テーマセットアップ後に実行する処理.
	 * 管理画面などはwpフックでは遅い.
	 *
	 * @return void
	 */
	public function _after_setup_theme() {
		$this->csr_option = CSR_Option::find();
		if ( is_admin() ) {
			new CSR_Admin_Controller( $csr_option );
		}
		// 管理画面でもスター表示利用するのでここで定義.
		new CSR_Comment_Controller();
	}

	/**
	 * メイン処理.
	 * WPオブジェクトの利用はwpフック以後.
	 */
	public function _main() {
		global $post;
		new CSR_Main_Controller( $post->ID, $this->csr_option );
		new CSR_Shortcode_Controller();
	}
}

$comment_star_rating = new CommentStarRating();
