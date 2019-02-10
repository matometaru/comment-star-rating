<?php

class CSR_Admin_Controller extends CSR_Controller {

	public function __construct( $options ) {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		$this->csr_options = $options;
		$this->post_types  = wp_list_filter( get_post_types( array( 'public' => true ) ), array( 'attachment' ), 'NOT' );
	}

	/**
	 * 管理画面を追加.
	 */
	public function admin_menu() {
		add_options_page(
			CSR_Config::NAME, // page_title.
			CSR_Config::NAME, // menu_title.
			'manage_options', // capability.
			CSR_Config::DOMAIN, // menu_slug.
			array( $this, 'admin_save_options' ) // callback.
		);
	}

	/**
	 * 管理画面:オプション保存レイアウト
	 */
	public function admin_settings() {
		$this->_render( 'admin/settings', array(
			'test' => 'TEST TEST',
			'post_types' => $this->post_types,
			'csr_options' => $this->csr_options,
		) );
	}

	/**
	 * 保存
	 */
	public function admin_save_options() {
		$key_array  = array();
		foreach ( $this->post_types as $post_type ) {
			array_push( $key_array, $post_type );
		}
		array_push( $key_array, 'url', 'email' );
		if ( isset( $_POST['save'] ) ) {
			if ( check_admin_referer( 'csr-nonce-key', 'csr-key' ) ) {
				$options = array();
				if ( ! empty( $_POST[ CSR_Config::DOMAIN ] ) ) {
					foreach ( $_POST[ CSR_Config::DOMAIN ] as $key => $value ) {
						if ( in_array( $key, $key_array ) ) {
							$key = $key;
						} else {
							break;
						}
						$value   = '1';
						$options += array( $key => $value );
					}
				}
				$this->csr_options->set_options( $options )->save();
			}
		}
		$this->admin_settings();
	}
}