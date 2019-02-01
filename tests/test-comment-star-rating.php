<?php
/**
 * Class CommentStarRatingTest
 *
 * @package Commen-star-rating
 */

/**
 * CommentStarRatingTest test case.
 */
class CommentStarRatingTest extends WP_UnitTestCase {

	private $comment_star_rating;
	private $post1;
	private $comment1;

	const ENABLE_POST = [ 'post' => '1' ];
	const DISABLE_POST = [ 'post' => '0' ];
	const ENABLE_EMAIL = [ 'email' => '1' ];
	const ENABLE_URL = [ 'url' => '1' ];

	/**
	 * construct.
	 */
	public function __construct( $name = null, array $data = array(), $data_name = '' ) {
		parent::__construct( $name, $data, $data_name );

		$url                       = plugins_url( '', __FILE__ );
		$this->comment_star_rating = new CommentStarRating( $url );
		$this->setOptions( self::ENABLE_POST );
	}

	/**
	 * 各テストメソッド実行前に呼ばれる処理.
	 */
	public function setUp() {
		parent::setUp();

		// 投稿を作成.
		$this->post1 = $this->factory->post->create();

		// コメントを作成.
		$this->comment1 = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $this->comment1, CommentStarRating::COMMENT_META_KEY, 5 );
	}

	/**
	 * 各テストメソッド実行後に呼ばれる処理.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * 設定: オプションを設定する.
	 *
	 * @param array $options オプション配列.
	 */
	public function setOptions( $options ) {
		$this->comment_star_rating->update_options( $options );
	}

	/**
	 * 管理画面側の設定
	 */
	public function test_投稿タイプpostが無効になっているか() {
		$this->setOptions( self::DISABLE_POST );
		$actual = $this->comment_star_rating->is_enabled_post_type( 'post' );
		$this->assertEquals( false, $actual );
	}

	/**
	 * 現在のページで投稿タイプの有効フラグが立っているか。
	 */
	public function test_現在のページで投稿タイプの有効フラグが立っているか() {
		$this->go_to( '/?p=' . $this->post1 );
		$post_type = get_post_type();
		$actual    = $this->comment_star_rating->is_enabled_post_type( $post_type );
		$this->assertEquals( true, $actual );
	}

	/**
	 * Filter_comment_form.
	 */
	public function test_filter_comment_formによりサイトの表示が消えてるか() {
		$this->setOptions( self::ENABLE_EMAIL );
		$this->setOptions( self::ENABLE_URL );
		$fields = array(
			'email' => 1,
			'url'   => 2,
		);
		$actual = $this->comment_star_rating->filter_comment_form( $fields );
		$this->assertEquals( null, $actual['email'] );
		$this->assertEquals( null, $actual['url'] );
	}

	/**
	 * Save rating.
	 */
	public function test_save_ratingでコメントメタにレーティングがデフォルト値の3が保存されてるか() {
		$comment_id = $this->comment_star_rating->save_rating( $this->comment1 );
		$actual     = get_comment_meta( $comment_id, CommentStarRating::COMMENT_META_KEY, true );
		$this->assertEquals( 5, $actual );
	}

	/**
	 * Generate ratings from comments.
	 */
	public function test_コメント配列がレーティング配列に生成されたか() {
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $this->post1 ) );
		add_comment_meta( $comment_id, CommentStarRating::COMMENT_META_KEY, 3 );

		$comments = $this->comment_star_rating->get_approved_comment( $this->post1 );
		$actual   = $this->comment_star_rating->generate_ratings_from_comments( $comments );
		$this->assertEquals( [ 5, 3 ], $actual );
	}

}
