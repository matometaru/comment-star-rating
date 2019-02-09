<?php

class CSR_Main_Controller extends CSR_Controller {

	private $csr_option;
	private $csr_post;
	private $csr_post_service;

	public function __construct( $csr_option ) {
		global $post;
		$this->url              = 'http://localhost:9000/wp-content/plugins/comment-star-rating/';
		$this->csr_option       = $csr_option;
		$this->csr_post_service = new CSR_Post_Service( $post->ID );
		$this->csr_post         = $this->csr_post_service->post_init();

		if ( $this->csr_option->is_enabled_post_type() ) {
			add_action( 'wp_head', array( $this, 'd3_init' ) );
			add_action( 'wp_head', array( $this, 'raty_init' ) );
			add_action( 'wp_head', array( $this, 'json_ld' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			// ショートコード.
			add_shortcode( 'comment_star_rating_total', array( $this, 'shortcode' ) );
			add_shortcode( 'comment_star_rating_ranking', array( $this, 'shortcode_post_ranking' ) );
		}
	}

	/**
	 * Shortcode.
	 */
	public function shortcode() {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		$output = wp_star_rating(
			array(
				'rating' => $this->csr_post->get( 'rating_average' ),
				'type'   => 'rating',
				'number' => $this->csr_post->get( 'rating_count' ),
				'echo'   => false,
			)
		);
		if ( $this->csr_post->get( 'rating_count' ) > 0 ) {
			$output .= '<p class="star-counter-tit">';
			$output .= esc_html__( '5つ星のうち', 'csr-main' );
			$output .= $this->csr_post->get( 'rating_average' );
			$output .= '</p>';
			$output .= '<div id="star-counter"></div>';
		}

		return $output;
	}

	/**
	 * Shortcode.
	 *
	 * @param array $atts 投稿タイプ文字列.
	 *
	 * @return string $outpu HTMLコード.
	 */
	public function shortcode_post_ranking( $atts ) {
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

	/**
	 * D3 init.
	 */
	public function d3_init() {
		$ratings = $this->csr_post->get( 'ratings' );
		$count = $this->csr_post->get( 'rating_count' );
		// 一覧を出力 D3.js.
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var dataset = [
					{label: "5つ星", value: <?php echo esc_js( $ratings[5] ); ?>},
					{label: "4つ星", value: <?php echo esc_js( $ratings[4] ); ?>},
					{label: "3つ星", value: <?php echo esc_js( $ratings[3] ); ?>},
					{label: "2つ星", value: <?php echo esc_js( $ratings[2] ); ?>},
					{label: "1つ星", value: <?php echo esc_js( $ratings[1] ); ?>},
				]
				HorizontalBarGraph = function (el, series) {
					this.el = d3.select(el);
					this.series = series;
				};
				HorizontalBarGraph.prototype.draw = function () {
					var x = d3.scaleLinear()
						.domain([0, <?php echo esc_js( $count ); ?>])
						.range([0, 100]);

					var segment = this.el
						.selectAll(".horizontal-bar-graph-segment")
						.data(this.series)
						.enter()
						.append("div").classed("horizontal-bar-graph-segment", true);

					segment
						.append("div").classed("horizontal-bar-graph-label", true)
						.text(function (d) {
							return d.label
						});

					segment
						.append("div").classed("horizontal-bar-graph-value", true)
						.append("div").classed("horizontal-bar-graph-value-bg", true)
						.append("div").classed("horizontal-bar-graph-value-bar", true)
						.style("background-color", function (d) {
							return d.color
						})
						.transition()
						.duration(1000)
						.style("min-width", function (d) {
							return x(d.value) + "%"
						});

					segment
						.append("div").classed("horizontal-bar-graph-num", true)
						.text(function (d) {
							return d.value
						});
				};

				var graph = new HorizontalBarGraph('#star-counter', dataset);
				graph.draw();
			});
		</script>
		<?php
	}

	/**
	 * Raty init.
	 */
	public function raty_init() {
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				jQuery('#input-type-star').raty({
					starType: 'i',	// Web Fonts
					number: 5,		// number
					score: 3,		// Defaults Star
					//half : true,
					//halfShow checke_admin_refere: true,
					hints: ['全く気に入らない', '気に入らない', '普通', '気に入った', 'とても気に入った'],
				});
				jQuery('#commentform').submit(function () {
					var score = jQuery('#input-type-star').raty('score');
					console.log(score);
					jQuery('input[name=csr_rating]').val(score);
				});
			});
		</script>
		<?php
	}

	/**
	 * JSON-LD.
	 */
	public function json_ld() {
		$ratings = $this->csr_post->get( 'arranged_rating' );
		?>
		<script type="application/ld+json">
			{
				"@context": "http://schema.org",
				"@type": "AggregateRating",
				"itemReviewed": "Article",
				"ratingValue": "<?php echo esc_js( $this->average ); ?>",
				"bestRating": "<?php echo esc_js( max( $ratings ) ); ?>",
				"worstRating": "<?php echo esc_js( min( $ratings ) ); ?>",
				"ratingCount": "<?php echo esc_js( $this->rating_count ); ?>"
			}
		</script>
		<?php
	}

	/**
	 * Setting styles.
	 */
	public function wp_enqueue_styles() {
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
		wp_enqueue_style( 'csr-rating', $this->url . '/css/rating.css' );
		wp_enqueue_style( 'csr-raty', $this->url . '/css/jquery.raty.css' );
	}

	/**
	 * Setting scripts.
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_script( 'd3', $this->url . '/js/d3.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'raty', $this->url . '/js/jquery.raty.js', array( 'jquery' ) );
	}
}
