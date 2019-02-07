<?php

class CSR_Option {

	/**
	 * 投稿タイプ
	 * @var string $post_type
	 */
	protected $post_type = 'post';

	/**
	 * URL
	 * @var string $url
	 */
	protected $url = '0';

	/**
	 * メールアドレス
	 * @var string $email
	 */
	protected $email = '0';

	/**
	 * @param array $properties プロパティ.
	 */
	public function __construct( $properties ) {
		$this->id      = $properties['id'];
		$this->rating  = $properties['rating'];
		$this->user_id = $properties['user_id'];
	}

	/**
	 * Set a attribute
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function set( $key, $value ) {
		if ( isset( $this->$key ) ) {
			$this->$key = $value;
		}
	}

	/**
	 * Set attributes
	 *
	 * @param array $values
	 */
	public function sets( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * Update with retained data
	 * @return void
	 */
	public static function save( $options ) {
		update_option( CSR_Config::DOMAIN, $options );
	}
}
