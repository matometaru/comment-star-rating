<?php

class CSR_Shortcode_Controller extends CSR_Controller {

	private $csr_post;

	public function __construct() {
		add_shortcode( 'comment_star_rating_total', array( $this, '_shortcode' ) );
		add_shortcode( 'comment_star_rating_ranking', array( $this, '_shortcode_post_ranking' ) );
	}

	/**
	 * Shortcode.
	 */
	public function _shortcode( $attributes ) {
		$unverified_post_id = ! empty( $attributes['post_id'] ) ? $attributes['post_id'] : 0;
		$post_id            = CSR_Functions::validate_post_id( $unverified_post_id );
		$this->csr_post     = CSR_Post::find( $post_id );
		$star_rating        = wp_star_rating(
			array(
				'rating' => $this->csr_post->get( 'rating_average' ),
				'type'   => 'rating',
				'number' => $this->csr_post->get( 'rating_count' ),
				'echo'   => false,
			)
		);
		if ( $this->csr_post->get( 'rating_count' ) > 0 ) {
			return $this->_generator(
				'shortcode/settings',
				array(
					'star_rating' => $star_rating,
					'title'       => esc_html__( '5つ星のうち', 'csr-main' ),
					'average'     => $this->csr_post->get( 'rating_average' ),
				)
			);
		}
	}

	/**
	 * Shortcode.
	 *
	 * @param array $atts 投稿タイプ文字列.
	 *
	 * @return string $outpu HTMLコード.
	 */
	public function _shortcode_post_ranking( $atts ) {
		if ( isset( $atts['post_type'] ) ) {
			$output = $this->get_average_ranking_html( $atts['post_type'] );
		} else {
			$output = $this->get_average_ranking_html();
		}

		return $output;
	}

	/**
	 * Shortcode.
	 *
	 * @param string $post_type 投稿タイプ文字列.
	 *
	 * @return string $html HTMLコード.
	 */
	public function get_average_ranking_html( $post_type = 'post' ) {
		$args  = array(
			'post_type'      => $post_type,
			'posts_per_page' => 3,
			'order'          => 'DESC',
			'meta_key'       => 'csr_average_rating',
			'orderby'        => 'meta_value',
		);
		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$output = '<ul id="csr-ranking">';
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$output .= '<li>';
				$output .= '<a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>';
				$output .= '<span class="csr-ranking-score">' . get_post_meta( $post->ID, 'csr_average_rating', true ) . '</span>';
				$output .= '</li>';
			}
			$output .= '</ul>';
		} else {
			$output = '<p>評価された記事がありません。</p>';
		}

		return $output;
	}
}
