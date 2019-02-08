<?php

/**
 * CSR_Comment test case.
 */
class CSR_OptionTest extends WP_UnitTestCase {

	const ENABLE_POST  = [ 'post' => '1' ];
	const ENABLE_EMAIL = [ 'email' => '1' ];
	const ENABLE_URL   = [ 'url' => '1' ];

	const DISABLE_POST  = [ 'post' => '0' ];
	const DISABLE_EMAIL = [ 'email' => '0' ];
	const DISABLE_URL   = [ 'url' => '0' ];

	/**
	 * construct.
	 */
	public function __construct( $name = null, array $data = array(), $data_name = '' ) {
		parent::__construct( $name, $data, $data_name );
	}

	/**
	 * 各テストメソッド実行前に呼ばれる処理.
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * 各テストメソッド実行後に呼ばれる処理.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Find all approved comments
	 */
	public function test_デフォルト値が期待通りか() {
		$defaults = array(
			'post'  => 1,
			'page'  => 0,
			'url'   => 0,
			'email' => 0,
		);
		$actual   = CSR_Option::find()->get( 'options' );
		$this->assertEquals( $defaults, $actual );
	}

	/**
	 * Find all approved comments
	 */
	public function test_メールフラグが有効か() {
		update_option( CSR_Config::DOMAIN, self::ENABLE_EMAIL );
		$actual = CSR_Option::find()->get( 'options' );
		$this->assertEquals( 1, $actual['email'] );
	}

	/**
	 * Find all approved comments
	 */
	public function test_URLフラグが保存できているか() {
		CSR_Option::find()->set_options( self::ENABLE_URL )->save();

		$actual = get_option( CSR_Config::DOMAIN );
		$this->assertEquals( 1, $actual['url'] );
	}
}
