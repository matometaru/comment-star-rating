<?php
/**
 * Plugin Name: Comment Star Rating
 * Description: WordPress plugin to rate comments on 5 beautiful stars.
 * Author: Matometaru
 * Author URI: http://matometaru.com/
 * Version: 1.1
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
// wp_star_rating() を使うためのインクルード.
require_once ABSPATH . 'wp-admin/includes/template.php';

/**
 * CommentStarRating
 */
class CommentStarRating {
	/**
	 * 全評価配列.
	 *
	 * @var array $ratings
	 */
	private $ratings;
	/**
	 * 全評価の数.
	 *
	 * @var int $count
	 */
	private $count;
	/**
	 * 全評価の平均.
	 *
	 * @var int $average
	 */
	private $average;
	/**
	 * 各スコアの評価数配列.
	 *
	 * @var array $ratings_arrange 配列
	 */
	private $ratings_arrange;
	/**
	 * 設定オプション.
	 *
	 * @var int $options
	 */
	private $options;

	const NAME = 'CommentStarRating';
	const DOMAIN = 'comment-star-rating';
	const COMMENT_META_KEY = 'csr_rating';

	/**
	 * Constructor.
	 *
	 * @param string $url プラグインURL.
	 */
	public function __construct( $url ) {
		$this->ratings = array();
		$this->options = array();
		$this->url     = $url;
	}

	/**
	 * Init all.
	 */
	public function init() {
		$this->options = $this->get_options();
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		} else {
			add_action( 'wp', array( $this, 'calculate_average_rating' ) );
			add_action( 'wp_head', array( $this, 'd3_init' ) );
			add_action( 'wp_head', array( $this, 'raty_init' ) );
			add_action( 'wp_head', array( $this, 'json_ld' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_filter( 'comment_form_default_fields', array( $this, 'filter_comment_form' ) );
			add_filter( 'comment_form_fields', array( $this, 'add_star_field' ) );
			add_action( 'comment_post', array( $this, 'save_rating' ) );
			add_action( 'comment_text', array( $this, 'comment_display' ) );
			add_shortcode( 'comment_star_rating_total', array( $this, 'shortcode' ) );
		}
	}

	/**
	 * Shortcode.
	 */
	public function shortcode() {
		$output = wp_star_rating(
			array(
				'rating' => $this->average,
				'type'   => 'rating',
				'number' => $this->count,
				'echo'   => false,
			)
		);
		if ( $this->count > 0 ) {
			$output .= '<p class="star-counter-tit">';
			$output .= esc_html__( '5つ星のうち', self::DOMAIN );
			$output .= number_format_i18n( $this->average, 1 );
			$output .= '</p>';
			$output .= '<div id="star-counter"></div>';
		}

		return $output;
	}

	/**
	 * Calculate average rating.
	 */
	public function calculate_average_rating() {
		global $post;
		$comments = $this->get_approved_comment( $post->ID );
		$ratings  = $this->generate_ratings_from_comments( $comments );
		// レーティングの合計、レーティングの数を取得.
		$total = array_sum( $ratings );
		$count = count( $ratings );
		if ( $this->count <= 0 ) {
			return;
		}
		$this->average         = $total / $this->count;
		$this->count_ratings( $ratings );

		// $arranged_ratings = $this->arrange_ratings( $ratings );
	}

	/**
	 * レーティングを整理する.
	 *
	 * @param array $ratings 全評価配列.
	 *
	 * @return array $arranged_ratings 1,2,3,4,5をkeyに、評価数がvalueの配列.
	 */
	public function arrange_ratings( $ratings ) {
		$arranged_ratings = array_count_values( $ratings );
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
	 * レーティングを数える
	 *
	 * @param int $comments 投稿ID.
	 *
	 * @return array $comments
	 */
	public function count_ratings( $comments ) {
		$ratings = [];
		foreach ( $comments as $comment ) {
			$star = get_comment_meta( $comment->comment_ID, self::COMMENT_META_KEY, true );
			if ( ! empty( $star ) ) {
				array_push( $ratings, $star );
			}
		}

		return $ratings;
	}

	/**
	 * コメントの配列からレーティングの配列に変換
	 *
	 * @param int $comments 投稿ID.
	 *
	 * @return array $comments
	 */
	public function generate_ratings_from_comments( $comments ) {
		$ratings = [];
		foreach ( $comments as $comment ) {
			$star = get_comment_meta( $comment->comment_ID, self::COMMENT_META_KEY, true );
			if ( ! empty( $star ) ) {
				array_push( $ratings, $star );
			}
		}

		return $ratings;
	}

	/**
	 * Get approved comment.
	 *
	 * @param int $post_id 投稿ID.
	 *
	 * @return array $comments
	 */
	public function get_approved_comment( $post_id ) {
		$comments = get_comments(
			array(
				'status'  => 'approve',
				'post_id' => $post_id,
			)
		);

		return $comments;
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
					{label: "5つ星", value: <?php echo esc_js( $this->ratings_arrange[5] ); ?>},
					{label: "4つ星", value: <?php echo esc_js( $this->ratings_arrange[4] ); ?>},
					{label: "3つ星", value: <?php echo esc_js( $this->ratings_arrange[3] ); ?>},
					{label: "2つ星", value: <?php echo esc_js( $this->ratings_arrange[2] ); ?>},
					{label: "1つ星", value: <?php echo esc_js( $this->ratings_arrange[1] ); ?>},
				]
				HorizontalBarGraph = function (el, series) {
					this.el = d3.select(el);
					this.series = series;
				};
				HorizontalBarGraph.prototype.draw = function () {
					var x = d3.scaleLinear()
						.domain([0, <?php echo esc_js( $this->count ); ?>])
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
				"ratingCount": "<?php echo esc_js( $this->count ); ?>"
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

	/**
	 * コメントフォーム要素の削除.
	 *
	 * @param array $fields wp comment fields.
	 *
	 * @return array $fields wp comment fields.
	 */
	public function filter_comment_form( $fields ) {
		if ( $this->is_disabled_form_url() ) {
			$fields['url'] = '';
		}
		if ( $this->is_disabled_form_email() ) {
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
	public function add_star_field( $fields ) {
		if ( $this->is_enabled_post_type() ) {
			$fields['rating'] = '<div id="input-type-star"></div>';
			$fields['rating'] .= '<input type="hidden" name="csr_rating" value="" />';
		}

		return $fields;
	}

	/**
	 * コメントの保存時: レーティングを保存する
	 *
	 * @param int $comment_id コメントID.
	 *
	 * @return int $comment_id コメントID.
	 */
	public function save_rating( $comment_id ) {
		// 一般ユーザーのみレーティングを保存する.
		if ( ! is_user_logged_in() ) {
			$rating = $this->validate_rating();
			add_comment_meta( $comment_id, self::COMMENT_META_KEY, $rating );
		}

		return $comment_id;
	}

	/**
	 * $_POSTのCOMMENT_META_KEYを1~5の値で検証し、返す.
	 *
	 * @return int レーティング.
	 */
	public function validate_rating() {
		$options = array(
			'options' => array(
				'default'   => 3,
				'min_range' => 1,
				'max_range' => 5,
			),
		);

		return filter_input( INPUT_POST, self::COMMENT_META_KEY, FILTER_VALIDATE_INT, $options );
	}

	/**
	 * コメントの最後にレーティングを表示.
	 *
	 * @param array $comment コメント.
	 *
	 * @return string $comment コメント.
	 */
	public function comment_display( $comment ) {
		// スターがなければ通常コメントを返す.
		$star = get_comment_meta( get_comment_ID(), self::COMMENT_META_KEY, true );
		if ( empty( $star ) ) {
			return $comment;
		}
		$star = is_numeric( $star ) ? $star : 3;

		// 投稿タイプが無効の場合も通常コメントを返す.
		if ( ! $this->is_enabled_post_type() ) {
			return $comment;
		}

		// ログインユーザーの場合も通常コメントを返す.
		$general_user = $this->is_general_user( get_comment_ID() );
		if ( ! $general_user ) {
			return $comment;
		}

		// それ以外はレーティングを付与.
		$output = wp_star_rating(
			array(
				'rating' => $star,
				'type'   => 'rating',
				'number' => 0,
				'echo'   => false,
			)
		);

		return $comment . $output;
	}

	/**
	 * 一般ユーザーのコメントか？
	 *
	 * @param int $comment_id コメントID.
	 *
	 * @return boolean 一般ユーザーのコメントか.
	 */
	public function is_general_user( $comment_id ) {
		// コメントしたユーザーの取得.
		$comment_object = get_comment( $comment_id );
		$comment_user   = $comment_object->user_id;

		// 一般ユーザーはIDが0.
		return '0' === $comment_user;
	}

	/**
	 * 現在の投稿タイプで有効か？
	 *
	 * @param string $post_type 投稿タイプ.
	 *
	 * @return boolean
	 */
	public function is_enabled_post_type( $post_type = null ) {
		if ( null === $post_type ) {
			$post_type = get_post_type();
		}

		return isset( $this->options[ $post_type ] ) && '1' === $this->options[ $post_type ];
	}

	/**
	 * コメントフォームにURLの表示が無効か？
	 *
	 * @return boolean
	 */
	public function is_disabled_form_url() {
		return isset( $this->options['url'] ) && '1' === $this->options['url'];
	}

	/**
	 * コメントフォームにメールアドレスの表示が無効か？
	 *
	 * @return boolean
	 */
	public function is_disabled_form_email() {
		return isset( $this->options['email'] ) && '1' === $this->options['email'];
	}

	/**
	 * 管理画面を追加.
	 */
	public function admin_menu() {
		add_options_page(
			self::NAME, // page_title.
			self::NAME, // menu_title.
			'manage_options', // capabiliity.
			self::DOMAIN, // menu_slug.
			array( $this, 'admin_save_options' ) // callback.
		);
	}

	/**
	 * 管理画面:オプション保存レイアウト
	 */
	public function admin_setting_form() {
		$post_types = wp_list_filter( get_post_types( array( 'public' => true ) ), array( 'attachment' ), 'NOT' );
		?>
		<div class="wrap">
			<h2><?php echo esc_attr( self::NAME ); ?> &raquo; <?php _e( 'Settings' ); ?></h2>
			<form id="<?php echo esc_attr( self::DOMAIN ); ?>" method="post" action="">
				<?php wp_nonce_field( 'csr-nonce-key', 'csr-key' ); ?>
				<h3><?php _e( '有効にする投稿タイプを選択してください' ); ?></h3>
				<?php
				foreach ( $post_types as $post_type ) {
					?>
					<p>
						<strong><?php echo esc_attr( $post_type ); ?>ページ上で有効にします</strong>
						<input type="checkbox"
							   name="<?php echo esc_attr( self::DOMAIN ); ?>[<?php echo esc_attr( $post_type ); ?>]"
							   value="1"
							<?php
							if ( $this->is_enabled_post_type( $post_type ) ) {
								echo 'checked';
							}
							?>
						/>
					</p>
					<?php
				}
				?>
				<h3>コメントの入力から外したい要素を選択</h3>
				<p>
					<strong>URLを外す</strong>
					<input type="checkbox" name="<?php echo esc_attr( self::DOMAIN ); ?>[url]" value="1"
						<?php
						if ( $this->is_disabled_form_url() ) {
							echo 'checked';
						}
						?>
					/>
				</p>
				<p>
					<strong>メールアドレスを外す</strong>
					<input type="checkbox" name="<?php echo esc_attr( self::DOMAIN ); ?>[email]" value="1"
						<?php
						if ( $this->is_disabled_form_email() ) {
							echo 'checked';
						}
						?>
					/>
				</p>
				<p class="submit">
					<input class="button-primary" type="submit" name='save' value='<?php _e( 'Save Changes' ); ?>'/>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * 保存
	 */
	public function admin_save_options() {
		$post_types = wp_list_filter( get_post_types( array( 'public' => true ) ), array( 'attachment' ), 'NOT' );
		$key_array  = array();
		foreach ( $post_types as $post_type ) {
			array_push( $key_array, $post_type );
		}
		array_push( $key_array, 'url', 'email' );
		if ( isset( $_POST['save'] ) ) {
			if ( check_admin_referer( 'csr-nonce-key', 'csr-key' ) ) {
				$options = array();
				if ( ! empty( $_POST[ self::DOMAIN ] ) ) {
					foreach ( $_POST[ self::DOMAIN ] as $key => $value ) {
						if ( in_array( $key, $key_array ) ) {
							$key = $key;
						} else {
							break;
						}
						$value   = '1';
						$options += array( $key => $value );
					}
				}
				$this->update_options( $options );
			}
		}
		$this->admin_setting_form();
	}

	/**
	 * オプションセット、保存.
	 *
	 * @param array $options オプション.
	 */
	public function update_options( $options ) {
		$this->options = array_merge( $this->options, $options );
		update_option( self::DOMAIN, $this->options );
	}

	/**
	 * オプションゲッター.
	 */
	public function get_options() {
		return get_option( self::DOMAIN );
	}
}

$url                 = plugins_url( '', __FILE__ );
$comment_star_rating = new CommentStarRating( $url );
$comment_star_rating->init();
