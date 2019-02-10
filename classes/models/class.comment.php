<?php

class CSR_Comment extends CSR_Model {

	/**
	 * コメントID
	 * @var int $id
	 */
	protected $id = 0;

	/**
	 * レーティング
	 * @var int $rating
	 */
	protected $rating;

	/**
	 * ユーザーID
	 * @var int $rating
	 */
	protected $user_id;

	/**
	 * @param array $properties プロパティ.
	 */
	public function __construct( $properties ) {
		$defaults   = array(
			'id'      => 0,
			'rating'  => 0,
			'user_id' => 0,
		);
		$properties = array_merge( $defaults, $properties );

		$this->id      = $properties['id'];
		$this->rating  = CSR_Functions::validate_rating( $properties['rating'] );
		$this->user_id = $properties['user_id'];
	}

	/**
	 * Return all forms
	 *
	 * @param int $comment_id コメントID.
	 *
	 * @return array $comment
	 */
	public static function find( $comment_id ) {
		$properties = [];
		$comment    = get_comment( $comment_id );
		$rating     = get_comment_meta( $comment->comment_ID, CSR_Config::COMMENT_META_KEY, true );

		$properties['id']      = $comment->comment_ID;
		$properties['user_id'] = $comment->user_id;
		$properties['rating']  = $rating;

		return new CSR_Comment( $properties );
	}

	/**
	 * 全プロパティを保存
	 */
	public function save() {
		if ( 0 !== $this->rating ) {
			update_comment_meta( $this->id, CSR_Config::COMMENT_META_KEY, $this->rating );
		}
	}

	public function set_rating( $rating ) {
		$this->rating = CSR_Functions::validate_rating( $rating );

		return $this;
	}

	/**
	 * 一般ユーザーのコメントか？
	 * @return boolean 一般ユーザーのコメントか.
	 */
	public function is_general_user() {
		// 一般ユーザーはIDが0.
		return '0' === $this->user_id;
	}
}
