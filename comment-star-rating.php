<?php
/**
 * Plugin Name: Comment Star Rating
 * Description: WordPress plugin to rate comments on 5 beautiful stars.
 * Author: Matometaru
 * Author URI: http://matometaru.com/
 * Version: 1.2
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
require_once plugin_dir_path( __FILE__ ) . 'classes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/config.php';

/**
 * CommentStarRating
 */
class CommentStarRating {
	/**
	 * 全評価配列.
	 * @var array $ratings
	 */
	private $ratings = array();
	/**
	 * 全評価の数.
	 * @var int $rating_count
	 */
	private $rating_count;
	/**
	 * 全評価の平均.
	 * @var int $average
	 */
	private $average;
	/**
	 * 各スコアの評価数配列.
	 * @var array $ratings_arrange 配列
	 */
	private $arranged_ratings;
	/**
	 * 現在ページの投稿ID.
	 * @var int $current_post_id
	 */
	private $current_post_id;

	public function set_ratings( $ratings ) {
		$this->ratings = $ratings;
	}

	public function set_rating_count( $rating_count ) {
		$this->rating_count = $rating_count;
	}

	public function set_average( $average ) {
		$this->average = $average;
	}

	public function set_arranged_ratings( $arranged_ratings ) {
		$this->arranged_ratings = $arranged_ratings;
	}

	/**
	 * Constructor.
	 *
	 * @param string $url プラグインURL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		// 全ファイル読み込み
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, 'init' ), 11 );
	}

	/**
	 * Init all.
	 */
	public function init() {
		// WPオブジェクト初期化後に登録しても間に合う処理.
		add_action( 'wp', array( $this, 'init_wp_after_hooks' ) );
	}

	/**
	 * Wpフック後に登録しても間に合う処理.
	 * WPオブジェクトを使った分岐をまとめて登録したい場合.
	 */
	public function init_wp_after_hooks() {
		global $post;
		$this->current_post_id = $post->ID;
		if ( CSR_Option::find()->is_enabled_post_type() ) {
			$this->setup_comment_rating( $post->ID );
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
	 * Load classes
	 * @return void
	 */
	public function _load_initialize_files() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		$includes        = array(
			'/classes/abstract',
			'/classes/controllers',
			'/classes/models',
			'/classes/services',
		);
		foreach ( $includes as $include ) {
			foreach ( glob( $plugin_dir_path . $include . '/*.php' ) as $file ) {
				require_once $file;
			}
		}
		new CSR_Comment_Controller();
		new CSR_Admin_Controller( CSR_Option::find() );
	}

	/**
	 * Shortcode.
	 */
	public function shortcode() {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		$output = wp_star_rating(
			array(
				'rating' => $this->average,
				'type'   => 'rating',
				'number' => $this->rating_count,
				'echo'   => false,
			)
		);
		if ( $this->rating_count > 0 ) {
			$output .= '<p class="star-counter-tit">';
			$output .= esc_html__( '5つ星のうち', CSR_Config::DOMAIN );
			$output .= $this->average;
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
	 * 投稿IDの集計に必要なデータをセット.
	 *
	 * @param int $post_id 投稿ID.
	 */
	public function setup_comment_rating( $post_id ) {
		CSR_Option::find();

		$comments = CSR_Post::find_all_approved_comments( $post_id );
		$ratings  = $this->generate_ratings_from_comments( $comments );
		$this->set_ratings( $ratings );

		$rating_count = count( $ratings );
		$this->set_rating_count( $rating_count );

		$average = CSR_Functions::calculate_average_rating( $ratings );
		$this->set_average( $average );

		$arranged_ratings = CSR_Functions::arrange_ratings( $ratings );
		$this->set_arranged_ratings( $arranged_ratings );
		update_post_meta( $this->current_post_id, 'csr_average_rating', $this->average );
	}

	/**
	 * コメントの配列からレーティングの配列に変換
	 *
	 * @param int $comments 投稿ID.
	 *
	 * @return array $ratings
	 */
	public function generate_ratings_from_comments( $comments ) {
		$ratings = [];
		foreach ( $comments as $comment ) {
			$star = get_comment_meta( $comment->comment_ID, CSR_Config::COMMENT_META_KEY, true );
			if ( ! empty( $star ) ) {
				array_push( $ratings, $star );
			}
		}

		return $ratings;
	}

	/**
	 * D3 init.
	 */
	public function d3_init() {
		// 一覧を出力 D3.js.
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var dataset = [
					{label: "5つ星", value: <?php echo esc_js( $this->arranged_ratings[5] ); ?>},
					{label: "4つ星", value: <?php echo esc_js( $this->arranged_ratings[4] ); ?>},
					{label: "3つ星", value: <?php echo esc_js( $this->arranged_ratings[3] ); ?>},
					{label: "2つ星", value: <?php echo esc_js( $this->arranged_ratings[2] ); ?>},
					{label: "1つ星", value: <?php echo esc_js( $this->arranged_ratings[1] ); ?>},
				]
				HorizontalBarGraph = function (el, series) {
					this.el = d3.select(el);
					this.series = series;
				};
				HorizontalBarGraph.prototype.draw = function () {
					var x = d3.scaleLinear()
						.domain([0, <?php echo esc_js( $this->rating_count ); ?>])
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
		?>
		<script type="application/ld+json">
			{
				"@context": "http://schema.org",
				"@type": "AggregateRating",
				"itemReviewed": "Article",
				"ratingValue": "<?php echo esc_js( number_format_i18n( $this->average, 1 ) ); ?>",
				"bestRating": "<?php echo esc_js( max( $this->ratings ) ); ?>",
				"worstRating": "<?php echo esc_js( min( $this->ratings ) ); ?>",
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

$url                 = plugins_url( '', __FILE__ );
$comment_star_rating = new CommentStarRating( $url );
$comment_star_rating->init();
