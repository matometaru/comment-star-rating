<?php

class CSR_Post extends CSR_Model {

	/**
	 * 投稿ID
	 * @var int $id
	 */
	protected $id;

	/**
	 * 整列済みの評価配列
	 * @var array $arranged_ratings
	 */
	protected $ratings = [
		'1' => 0,
		'2' => 0,
		'3' => 0,
		'4' => 0,
		'5' => 0,
	];

	/**
	 * 評価数
	 * @var int $rating_count
	 */
	protected $rating_count;

	/**
	 * 全評価の平均（小数点第一位まで）
	 * @var float $rating_average
	 */
	protected $rating_average;

	/**
	 * @param array $properties プロパティ配列.
	 */
	public function __construct( $properties ) {
		$defaults   = array(
			'id'      => 0,
			'rating'  => 0,
			'rating_average'  => $this->ratings,
			'rating_count' => 0,
		);
		$properties = array_merge( $defaults, $properties );

		$comments = self::find_all_approved_comments( $this->id );
		if ( empty( $comments ) ) {
			return null;
		}

		$this->id             = $properties['id'];
		$this->ratings        = $properties['rating'];
		$this->rating_average = $properties['rating_average'];
		$this->rating_count   = $properties['rating_count'];
	}

	/**
	 * Return all forms
	 *
	 * @param int $post_id 投稿ID.
	 *
	 * @return CSR_Post
	 */
	public static function find( $post_id ) {
		$ratings        = get_post_meta( $post_id, CSR_Config::POST_META_RATINGS_KEY, true );
		$rating_average = get_post_meta( $post_id, CSR_Config::POST_META_AVERAGE_KEY, true );
		$rating_count   = get_post_meta( $post_id, CSR_Config::POST_META_COUNT_KEY, true );
		// $arranged_ratings = json_encode( $ratings );

		$properties['id']             = $post_id;
		$properties['ratings']        = $ratings;
		$properties['rating_average'] = $rating_average;
		$properties['rating_count']   = $rating_count;

		return new CSR_Post( $properties );
	}

	public function set_ratings( $ratings ) {
		$this->ratings = $ratings;

		return $this;
	}

	public function set_rating_average( $rating_average ) {
		$this->rating_average = $rating_average;

		return $this;
	}

	public function set_rating_count( $rating_count ) {
		$this->rating_count = $rating_count;

		return $this;
	}

	/**
	 * 全プロパティを保存
	 */
	public function save() {
		$ratings = json_encode( $this->ratings );
		update_post_meta( $this->id, CSR_Config::POST_META_RATINGS_KEY, $ratings );
		update_post_meta( $this->id, CSR_Config::POST_META_AVERAGE_KEY, $this->rating_average );
		update_post_meta( $this->id, CSR_Config::POST_META_COUNT_KEY, $this->rating_count );
	}

	/**
	 * 投稿に属するコメントをDBから取得
	 *
	 * @param int $post_id 投稿ID.
	 *
	 * @return array WP_Post
	 */
	public static function find_all_approved_comments( $post_id ) {
		return get_comments(
			array(
				'status'   => 'approve',
				'post_id'  => $post_id,
				'meta_key' => CSR_Config::COMMENT_META_KEY,
			)
		);
	}

}
