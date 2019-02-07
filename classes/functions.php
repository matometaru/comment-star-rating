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
}
