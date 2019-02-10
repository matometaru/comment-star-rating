<?php
/**
 * CSR_Comment test case.
 */
class CSR_CommentTest extends WP_UnitTestCase {

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

		// コメントを作成.
		$this->comment1 = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $this->comment1, CSR_Config::COMMENT_META_KEY, 5 );

		$this->comment2 = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $this->comment2, CSR_Config::COMMENT_META_KEY, 3 );
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
	public function test_コメントを取得するfindが機能しているか() {
		$actual = CSR_Comment::find( $this->comment1 )->get( 'id' );
		$this->assertEquals( $this->comment1, $actual );
	}
}
