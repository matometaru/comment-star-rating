<?php

class CSR_Functions {

	/**
	 * $_POSTのCOMMENT_META_KEYを0~5の値で検証し、返す.
	 *
	 * @param void $rating ユーザー入力値.
	 *
	 * @return int レーティング.
	 */
	public static function validate_rating( $rating ) {
		$options = array(
			'options' => array(
				'default'   => 3,
				'min_range' => 0,
				'max_range' => 5,
			),
		);

		return filter_var( $rating, FILTER_VALIDATE_INT, $options );
	}

	/**
	 * 評価平均値を取得する.
	 *
	 * @param array $all_ratings 評価配列.
	 *
	 * @return int 平均値（小数点第一位）
	 */
	public static function calculate_average_rating( $all_ratings ) {
		$count = count( $all_ratings );
		if ( $count <= 0 ) {
			return;
		}
		$total          = array_sum( $all_ratings );
		$average_rating = number_format_i18n( $total / $count, 1 );

		return number_format_i18n( $average_rating, 1 );
	}

	/**
	 * レーティングを整理する.
	 *
	 * @param array $all_ratings 全評価配列.
	 *
	 * @return array $arranged_ratings 1,2,3,4,5をkeyに、評価数がvalueの配列.
	 */
	public static function arrange_ratings( $all_ratings ) {
		$arranged_ratings = array_count_values( $all_ratings );
		// 未定義、空なら0を入れる.
		for ( $i = 1; $i <= 5; $i ++ ) {
			if ( ! isset( $arranged_ratings[ $i ] ) ) {
				$arranged_ratings[ $i ] = 0;
			}
		}
		ksort( $arranged_ratings );

		return $arranged_ratings;
	}

	/**
	 * コメントの配列からレーティングの配列に変換
	 *
	 * @param int $comments 投稿ID.
	 *
	 * @return array $ratings
	 */
	public static function generate_ratings_from_comments( $comments ) {
		$ratings = [];
		foreach ( $comments as $comment ) {
			$star = get_comment_meta( $comment->comment_ID, CSR_Config::COMMENT_META_KEY, true );
			if ( ! empty( $star ) ) {
				array_push( $ratings, $star );
			}
		}

		return $ratings;
	}
}
