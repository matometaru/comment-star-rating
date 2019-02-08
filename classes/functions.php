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
}
