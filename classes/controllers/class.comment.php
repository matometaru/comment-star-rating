<?php

class CSR_Comment_Controller extends CSR_Controller {

	public function __construct() {
		add_action( 'comment_text', array( $this, '_comment_display' ) );
		add_action( 'comment_post', array( $this, '_save_rating' ) );
		add_action( 'comment_form_default_fields', array( $this, '_filter_comment_form' ) );
		add_action( 'comment_form_fields', array( $this, '_add_star_field' ) );
	}

	/**
	 * Comment display
	 *
	 * @param array $wp_comment
	 *
	 * @return void
	 */
	public function _comment_display( $wp_comment ) {
		$comment = CSR_Comment::find( get_comment_ID() );
		$rating  = $comment->get( 'rating' );
		if ( ! $rating ) {
			return $wp_comment;
		}

		// コメントしたユーザーがログインユーザーの場合も通常コメントを返す.
		if ( ! $comment->is_general_user() ) {
			return $wp_comment;
		}

		$rating = CSR_Functions::validate_rating( $rating );
		$output = wp_star_rating(
			array(
				'rating' => $rating,
				'type'   => 'rating',
				'number' => 0,
				'echo'   => false,
			)
		);

		return $wp_comment . $output;
	}

	/**
	 * コメントの保存時: レーティングを保存する
	 *
	 * @param int $comment_id コメントID.
	 *
	 * @return int $comment_id コメントID.
	 */
	public function _save_rating( $comment_id ) {
		// 一般ユーザーのみレーティングを保存する.
		if ( ! is_user_logged_in() ) {
			$rating = CSR_Functions::validate_rating( $_POST[ CSR_Config::COMMENT_META_KEY ] );
			CSR_Comment::find( $comment_id )->set_rating( $rating )->save();
		}

		return $comment_id;
	}

	/**
	 * コメントフォーム要素の削除.
	 *
	 * @param array $fields wp comment fields.
	 *
	 * @return array $fields wp comment fields.
	 */
	public function _filter_comment_form( $fields ) {
		$options = CSR_Option::find();
		if ( $options->is_disabled_form_url() ) {
			$fields['url'] = '';
		}
		if ( $options->is_disabled_form_email() ) {
			$fields['email'] = '';
		}

		return $fields;
	}

	/**
	 * コメントに星入力フォームの追加.
	 *
	 * @param array $fields wp comment fields.
	 *
	 * @return array $fields wp comment fields.
	 */
	public function _add_star_field( $fields ) {
		$fields['rating'] = '<div id="input-type-star"></div><input type="hidden" name="csr_rating" value="" />';

		return $fields;
	}
}
