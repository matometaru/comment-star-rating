<?php

class CSR_Post extends CSR_Model {

	/**
	 * 投稿ID
	 * @var int $id
	 */
	protected $id = 0;

	/**
	 * 整列済みの評価配列
	 * @var array $arranged_ratings
	 */
	protected $arranged_ratings = [
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
	protected $rating_count = 0;

	/**
	 * 全評価の平均（小数点第一位まで）
	 * @var float $rating_average
	 */
	protected $rating_average = 0;

	/**
	 * @param int $post_id 投稿ID.
	 */
	public function __construct( $post_id ) {
		$this->id = $post_id;

		// commentの取得.
		$comments = self::find_all_approved_comments( $this->id );
		// 評価コメントがなければ何もしない.
		if ( empty( $comments ) ) {
			return;
		}
		$this->find_and_set_meta();
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
	 * 全プロパティを保存
	 */
	public function save() {
		$ratings = json_encode( $this->arranged_ratings );
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

	/**
	 * 投稿メタ要素をDBから取得
	 */
	private function find_and_set_meta() {
		$ratings                = get_post_meta( $this->id, CSR_Config::POST_META_RATINGS_KEY, true );
		$this->rating_average   = get_post_meta( $this->id, CSR_Config::POST_META_AVERAGE_KEY, $this->rating_average, true );
		$this->rating_count     = get_post_meta( $this->id, CSR_Config::POST_META_COUNT_KEY, $this->rating_count, true );
		$this->arranged_ratings = json_encode( $ratings );
	}

}
