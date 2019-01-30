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
	private $cat1;
	private $post1;

	const ENABLE_POST  = [ 'post' => '1' ];
	const DISABLE_POST = [ 'post' => '0' ];
	const ENABLE_EMAIL = [ 'email' => '1' ];
	const ENABLE_URL   = [ 'url' => '1' ];

	/**
	 * construct.
	 */
	public function __construct( $name = null, array $data = array(), $data_name = '' ) {
		parent::__construct( $name, $data, $data_name );

		$url = plugins_url( '', __FILE__ );
		$this->comment_star_rating = new CommentStarRating( $url );
		$this->setOptions( self::ENABLE_POST );
	}

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		// 投稿を作成.
		$this->post1 = $this->factory->post->create();
	}

	/**
	 * Tear down.
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
		$actual = $this->comment_star_rating->is_enabled_post_type( $post_type );
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
}
