<?php

class CSR_Option extends CSR_Model {

	/**
	 * @var array $options
	 */
	protected $options = [];

	/**
	 * @param array $properties プロパティ.
	 */
	public function __construct( $options ) {
		$defaults = array(
			'post'  => '1',
			'page'  => '0',
			'url'   => '0',
			'email' => '0',
		);
		$this->options = array_merge( $defaults, $options );
	}

	/**
	 * Set options
	 *
	 * @param array $values
	 * @return void
	 */
	public function set_options( array $options ) {
		$this->options = $options;

		return $this;
	}

	/**
	 * Update with retained data
	 * @return boolean
	 */
	public function save() {
		return update_option( CSR_Config::DOMAIN, $this->options );
	}

	/**
	 * Return all forms
	 * @return array $comment
	 */
	public static function find() {
		$options = get_option( CSR_Config::DOMAIN, [] );

		return new CSR_Option( $options );
	}

	/**
	 * コメントフォームにURLの表示が無効か？
	 * @return boolean
	 */
	public function is_disabled_form_url() {
		return isset( $this->options['url'] ) && '1' === $this->options['url'];
	}

	/**
	 * コメントフォームにメールアドレスの表示が無効か？
	 * @return boolean
	 */
	public function is_disabled_form_email() {
		return isset( $this->options['email'] ) && '1' === $this->options['email'];
	}

	/**
	 * 現在の投稿タイプで有効か？
	 *
	 * @param string $post_type 投稿タイプ.
	 *
	 * @return boolean
	 */
	public function is_enabled_post_type( $post_type = null ) {
		if ( null === $post_type ) {
			$post_type = get_post_type();
		}

		return isset( $this->options[ $post_type ] ) && '1' === $this->options[ $post_type ];
	}
}
