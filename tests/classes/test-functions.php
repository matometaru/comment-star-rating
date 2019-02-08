<?php
/**
 * CSR_Post test case.
 */
class CSR_FunctionsTest extends WP_UnitTestCase {

	private $post1;
	private $post2;
	private $comment1;
	private $comment2;

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
	 * Validate rating.
	 */
	public function test_バリデーションに10を入力すると3が返る() {
		$actual = CSR_Functions::validate_rating( 10 );
		$this->assertEquals( 3, $actual );
	}

	/**
	 * Arrange ratings.
	 */
	public function test_全評価配列が1_2_3_4_5をkeyに、評価数がvalueの配列() {
		$actual = CSR_Functions::arrange_ratings( [ 1, 1, 3, 5 ] );
		$expected = [
			'1' => 2,
			'2' => 0,
			'3' => 1,
			'4' => 0,
			'5' => 1,
		];
		$this->assertEquals( $expected, $actual );
	}
}
