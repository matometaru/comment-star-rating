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
// wp_star_rating() を使うためのインクルード.
require_once ABSPATH . 'wp-admin/includes/template.php';
include_once( plugin_dir_path( __FILE__ ) . 'classes/functions.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );


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
	 * 設定オプション.
	 * @var int $options
	 */
	private $options;
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
		$this->options = $this->get_options();
		$this->url     = $url;
	}

	/**
	 * Init all.
	 */
	public function init() {
		// 全ファイル読み込み
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		// WPオブジェクト初期化前に実行される処理.
		add_action( 'comment_post', array( $this, 'save_rating' ) );
		add_action( 'comment_text', array( $this, 'comment_display' ) );
		add_filter( 'comment_form_default_fields', array( $this, 'filter_comment_form' ) );
		add_filter( 'comment_form_fields', array( $this, 'add_star_field' ) );
		// WPオブジェクト初期化後に登録しても間に合う処理.
		add_action( 'wp', array( $this, 'init_wp_after_hooks' ) );
		// 管理画面.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Wpフック後に登録しても間に合う処理.
	 * WPオブジェクトを使った分岐をまとめて登録したい場合.
	 */
	public function init_wp_after_hooks() {
		global $post;
		$this->current_post_id = $post->ID;
		if ( $this->is_enabled_post_type() ) {
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
				require_once( $file );
			}
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
				'number' => $this->rating_count,
				'echo'   => false,
			)
		);
		if ( $this->rating_count > 0 ) {
			$output .= '<p class="star-counter-tit">';
			$output .= esc_html__( '5つ星のうち', CSR_Config::DOMAIN );
			$output .= number_format_i18n( $this->average, 1 );
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
		$comments = CSR_Post::find_all_approved_comments( $post_id );
		$ratings  = $this->generate_ratings_from_comments( $comments );
		$this->set_ratings( $ratings );

		$rating_count = count( $ratings );
		$this->set_rating_count( $rating_count );

		$average = $this->calculate_average_rating( $ratings );
		$this->set_average( $average );

		$arranged_ratings = $this->arrange_ratings( $ratings );
		$this->set_arranged_ratings( $arranged_ratings );
		update_post_meta( $this->current_post_id, 'csr_average_rating', $this->average );
	}

	/**
	 * 評価平均値を取得する.
	 *
	 * @param array $ratings 評価配列.
	 *
	 * @return int 平均値
	 */
	public function calculate_average_rating( $ratings ) {
		$count = count( $ratings );
		if ( $count <= 0 ) {
			return;
		}
		$total          = array_sum( $ratings );
		$average_rating = number_format_i18n( $total / $count, 1 );

		return $average_rating;
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
		$fields['rating'] = '<div id="input-type-star"></div>';
		$fields['rating'] .= '<input type="hidden" name="csr_rating" value="" />';

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
			$rating = CSR_Functions::validate_rating( $_POST[ CSR_Config::COMMENT_META_KEY ] );
			CSR_Config::find( $comment_id )->set_rating( $rating )->save();
		}

		return $comment_id;
	}

	/**
	 * コメントの最後にレーティングを表示.
	 *
	 * @param array $wp_comment コメント.
	 *
	 * @return string $wp_comment コメント.
	 */
	public function comment_display( $wp_comment ) {
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
	 * @return boolean
	 */
	public function is_disabled_form_url() {
		return isset( $this->options['url'] ) && '1' === $this->options['url'];
	}

	/**
	 * コメントフォームにメールアドレスの表示が無効か？
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
			CSR_Config::NAME, // page_title.
			CSR_Config::NAME, // menu_title.
			'manage_options', // capabiliity.
			CSR_Config::DOMAIN, // menu_slug.
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
			<h2><?php echo esc_attr( CSR_Config::NAME ); ?> &raquo; <?php _e( 'Settings' ); ?></h2>
			<form id="<?php echo esc_attr( CSR_Config::DOMAIN ); ?>" method="post" action="">
				<?php wp_nonce_field( 'csr-nonce-key', 'csr-key' ); ?>
				<h3><?php _e( '有効にする投稿タイプを選択してください' ); ?></h3>
				<?php
				foreach ( $post_types as $post_type ) {
					?>
					<p>
						<strong><?php echo esc_attr( $post_type ); ?>ページ上で有効にします</strong>
						<input type="checkbox"
							   name="<?php echo esc_attr( CSR_Config::DOMAIN ); ?>[<?php echo esc_attr( $post_type ); ?>]"
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
					<input type="checkbox" name="<?php echo esc_attr( CSR_Config::DOMAIN ); ?>[url]" value="1"
						<?php
						if ( $this->is_disabled_form_url() ) {
							echo 'checked';
						}
						?>
					/>
				</p>
				<p>
					<strong>メールアドレスを外す</strong>
					<input type="checkbox" name="<?php echo esc_attr( CSR_Config::DOMAIN ); ?>[email]" value="1"
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
				if ( ! empty( $_POST[ CSR_Config::DOMAIN ] ) ) {
					foreach ( $_POST[ CSR_Config::DOMAIN ] as $key => $value ) {
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
		$this->options = $options;
		update_option( CSR_Config::DOMAIN, $options );
	}

	/**
	 * オプションゲッター.
	 */
	public function get_options() {
		return get_option( CSR_Config::DOMAIN, [] );
	}
}

$url                 = plugins_url( '', __FILE__ );
$comment_star_rating = new CommentStarRating( $url );
$comment_star_rating->init();
