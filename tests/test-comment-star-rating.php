<?php
/**
 * Class CommentStarRatingTest
 * @package Commen-star-rating
 */

/**
 * CommentStarRatingTest test case.
 */
class CommentStarRatingTest extends WP_UnitTestCase {

	private $comment_star_rating;
	private $csr_options;
	private $csr_comment_controller;
	private $post1;
	private $post2;
	private $comment1;
	private $comment2;

	const ENABLE_POST = [ 'post' => '1' ];
	const DISABLE_POST = [ 'post' => '0' ];
	const ENABLE_EMAIL = [ 'email' => '1' ];
	const ENABLE_URL = [ 'url' => '1' ];

	/**
	 * construct.
	 */
	public function __construct( $name = null, array $data = array(), $data_name = '' ) {
		parent::__construct( $name, $data, $data_name );

		$url                          = plugins_url( '', __FILE__ );
		$this->comment_star_rating    = new CommentStarRating( $url );
		$this->csr_options            = CSR_Option::find();
		$this->csr_comment_controller = new CSR_Comment_Controller( $this->csr_options );
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
	 * Filter_comment_form.
	 */
	public function test_filter_comment_formによりサイトの表示が消えてるか() {
		$options = array_merge( self::ENABLE_EMAIL, self::ENABLE_URL );
		$this->csr_options->set_options( $options )->save();
		$fields = array(
			'email' => 1,
			'url'   => 2,
		);
		$actual = $this->csr_comment_controller->_filter_comment_form( $fields );
		$this->assertEquals( null, $actual['email'] );
		$this->assertEquals( null, $actual['url'] );
	}

	/**
	 * Save rating.
	 */
	public function test_save_ratingでコメントメタに不正な値が送られた場合デフォルト値の3が保存されてるか() {
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $this->post2 ) );

		$_POST['csr_rating'] = 'abc';
		$comment_id          = $this->csr_comment_controller->_save_rating( $comment_id );
		$actual              = get_comment_meta( $comment_id, CSR_Config::COMMENT_META_KEY, true );
		$this->assertEquals( 3, $actual );
	}

	/**
	 * Save rating.
	 */
	public function test_save_ratingでコメントメタに5が保存されてるか() {
		$_POST['csr_rating'] = 5;
		$comment_id          = $this->csr_comment_controller->_save_rating( $this->comment1 );
		$actual              = get_comment_meta( $comment_id, CSR_Config::COMMENT_META_KEY, true );
		$this->assertEquals( 5, $actual );
	}

	/**
	 * Generate ratings from comments.
	 */
	public function test_コメント配列がレーティング配列に生成されたか() {
		$comments = CSR_Post::find_all_approved_comments( $this->post1 );
		$actual   = CSR_Functions::generate_ratings_from_comments( $comments );
		$this->assertEquals( [ 5, 3 ], $actual );
	}

	/**
	 * Calculate average rating.
	 */
	public function test_post1のコメントの平均値が4か() {
		$comments = CSR_Post::find_all_approved_comments( $this->post1 );
		$ratings  = CSR_Functions::generate_ratings_from_comments( $comments );
		$actual   = CSR_Functions::calculate_average_rating( $ratings );
		$this->assertEquals( 4, $actual );
	}

	/**
	 * Setup comment rating.
	 */
	// public function test_初回設定値に期待通りの値が入っているか() {
	// 	$this->comment_star_rating->setup_comment_rating( $this->post1 );
	// 	$expected = [
	// 		'1' => 0,
	// 		'2' => 0,
	// 		'3' => 1,
	// 		'4' => 0,
	// 		'5' => 1,
	// 	];
	//
	// 	$this->assertEquals( [ 5, 3 ], $this->comment_star_rating->get_ratings() );
	// 	$this->assertEquals( 2, $this->comment_star_rating->get_rating_count() );
	// 	$this->assertEquals( 4, $this->comment_star_rating->get_average() );
	// 	$this->assertEquals( $expected, $this->comment_star_rating->get_arranged_ratings() );
	// }

}
