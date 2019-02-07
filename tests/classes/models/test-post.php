<?php
/**
 * CSR_Post test case.
 */
class CSR_PostTest extends WP_UnitTestCase {

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

		// 投稿を作成.
		$this->post1 = $this->factory->post->create();
		$this->post2 = $this->factory->post->create();

		// コメントを作成.
		$this->comment1 = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $this->comment1, CommentStarRating::COMMENT_META_KEY, 5 );

		$this->comment2 = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $this->comment2, CommentStarRating::COMMENT_META_KEY, 3 );
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
	public function test_投稿の承認コメントが全て取得できているか() {
		$actual = CSR_Post::find_all_approved_comments( $this->post1 );
		$this->assertEquals( 2, count( $actual ) );
	}

	public function test_保存したコメントメタが取得できているか() {
		$post = new CSR_Post( $this->post1 );
		$post->set( 'rating_count', 8 );
		$post->set( 'rating_average', 3.2 );
		$post->save();
		$actual = $post->gets();
		$this->assertEquals( 8, $actual['rating_count'] );
		$this->assertEquals( 3.2, $actual['rating_average'] );

	}

	public function test_レーティングがDBにJSONで保存されているか() {
		$post = new CSR_Post( $this->post1 );
		$post->set( 'arranged_ratings', [ '1' => 1, '2' => 0, '3' => 5, '4' => 0, '5' => 2 ] );
		$post->save();

		$post_id = $post->get( 'id' );
		$actual  = get_post_meta( $post_id, CSR_Config::POST_META_RATINGS_KEY, true );
		$this->assertEquals( '{"1":1,"2":0,"3":5,"4":0,"5":2}', $actual );
	}
}
