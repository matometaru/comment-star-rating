<?php
/**
 * Plugin Name: Comment Star Rating
 * Description: Wordpress plugin to rate comments on 5 beautiful stars.
 * Author: Matometaru
 * Author URI: http://matometaru.com/
 * Version: 1.1
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
// wp_star_rating() を使うためのインクルード
require_once(ABSPATH . 'wp-admin/includes/template.php');
// settings
define( "COMMENT_STAR_RATING_DOMAIN", "comment-star-rating" );
class CommentStarRating
{
	public $ratings;
	public $total;
	public $count;
	public $average;
	public $ratings_arrange;
	function __construct() {
        $path				= __FILE__;
        $dir				= dirname( $path );
		$this->ratings 		= array();
		$this->name 		= 'CommentStarRating';
		$this->prefix 		= 'csr_';
		$this->options		= array();
        $this->url			= plugins_url( '', $path );
	}
	function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		} else {
			$this->options = get_option(COMMENT_STAR_RATING_DOMAIN);
			add_action( 'wp',  array( $this, 'get_average_rating') );
			add_action( 'wp_head', array( $this, 'd3_init' ) );
			add_action( 'wp_head', array( $this, 'raty_init' ) );
			add_action( 'wp_head', array( $this, 'json_ld' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			// comment form
			add_filter( 'comment_form_default_fields', array( $this, 'comment_form' ) );
			// comment post
			add_action( 'comment_post', array( $this, 'comment_post' ) );
			// comment display
			add_action( 'comment_text', array( $this, 'comment_display' ) );
			// shortcode
			add_shortcode( 'comment_star_rating_total', array( $this, 'shortcode' ) );
		}
	}
	function shortcode( $atts ){
		$output = wp_star_rating( array(
			'rating'    => $this->average,
			'type'      => 'rating',
			'number'    => $this->count,
			'echo'      => false
		));
		if( $this->count > 0 ) {
			$output .= '<p class="star-counter-tit">' . esc_html__('5つ星のうち', COMMENT_STAR_RATING_DOMAIN ) . number_format_i18n( $this->average, 1 ) . '</p>' . '<div id="star-counter"></div>';
		}
		return $output;
	}
	function get_average_rating() {
		global $post;
		$comments = get_comments(array(
			'status' => 'approve',
			'number' => 700,
			'post_id'=> $post->ID,
		));
		// 合計、数、平均を取得
		foreach($comments as $comment) {
			$star = get_comment_meta( $comment->comment_ID, 'csr_rating', true);
			if( !empty( $star ) ) {
				array_push( $this->ratings, $star );
			}
		}
		$this->total = array_sum($this->ratings);
		$this->count = count($this->ratings);
		if( $this->count > 0 ) {
			$this->average = $this->total / $this->count;
			$this->ratings_arrange = array_count_values($this->ratings);
		}
		// 未定義、空なら0を入れる
		for ( $i = 1; $i <= 5; $i++) {
			if( !isset($this->ratings_arrange[$i]) ) {
				$this->ratings_arrange[$i] = 0;
			}
		}
	}
	function d3_init() {
		// 一覧を出力 D3.js
		?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					var dataset = [
						{label: "5つ星" , value: <?php echo esc_js($this->ratings_arrange[5]); ?>},
						{label: "4つ星" , value: <?php echo esc_js($this->ratings_arrange[4]); ?>},
						{label: "3つ星" , value: <?php echo esc_js($this->ratings_arrange[3]); ?>},
						{label: "2つ星" , value: <?php echo esc_js($this->ratings_arrange[2]); ?>},
						{label: "1つ星" , value: <?php echo esc_js($this->ratings_arrange[1]); ?>},
					]
					HorizontalBarGraph = function(el, series) {
						this.el = d3.select(el);
						this.series = series;
					};
					HorizontalBarGraph.prototype.draw = function() {
						var x = d3.scaleLinear()
						.domain([0, <?php echo esc_js($this->count); ?>])
						.range([0, 100]);

						var segment = this.el
							.selectAll(".horizontal-bar-graph-segment")
							.data(this.series)
							.enter()
							.append("div").classed("horizontal-bar-graph-segment", true);

						segment
							.append("div").classed("horizontal-bar-graph-label", true)
							.text(function(d) { return d.label });

						segment
							.append("div").classed("horizontal-bar-graph-value", true)
							.append("div").classed("horizontal-bar-graph-value-bg", true)
							.append("div").classed("horizontal-bar-graph-value-bar", true)
							.style("background-color", function(d) { return d.color })
							.transition()
							.duration(1000)
							.style("min-width", function(d) { return x(d.value) + "%" });

						segment
							.append("div").classed("horizontal-bar-graph-num", true)
							.text(function(d) { return d.value });
					};

					var graph = new HorizontalBarGraph('#star-counter', dataset);
					graph.draw();
				});
			</script>
		<?php
	}
	function raty_init() {
		?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					jQuery('#input-type-star').raty({
						starType: 'i',	// Web Fonts
						number: 5,		// number
						score : 3,		// Defaults Star
						//half : true,
						//halfShow checke_admin_refere: true, 
						hints: ['全く気に入らない', '気に入らない', '普通', '気に入った', 'とても気に入った'],
					});
					jQuery('#commentform').submit(function(){
						var score = jQuery('#input-type-star').raty('score');
						jQuery('input[name=csr_rating]').val(score);
					});
				});
			</script>
		<?php
	}
	function json_ld() {
		// @type Articleを指定する際使うかも
		// global $post;
		// $post_id = $post->ID;
		// $post_author = $post->post_author;
		// $post_published_date = get_the_date("Y-m-d",$post_id);
		// $post_modified_date = get_the_date("Y-m-d",null,$post_id);
		?>
			<script type="application/ld+json">
			{
				"@context" : "http://schema.org",
				"@type" : "AggregateRating",
				"itemReviewed" : "Article", 
				"ratingValue" : "<?php echo esc_js(number_format_i18n( $this->average, 1 )); ?>",
				"bestRating" : "<?php echo esc_js(max($this->ratings)); ?>",
				"worstRating" : "<?php echo esc_js(min($this->ratings)); ?>",
				"ratingCount" : "<?php echo esc_js($this->count); ?>"
			}
			</script>
		<?php
	}
	function wp_enqueue_styles() {
		//wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
		wp_enqueue_style( 'csr-rating',  $this->url . '/css/rating.css' );
		wp_enqueue_style( 'csr-raty',  $this->url . '/css/jquery.raty.css' );
	}
	function wp_enqueue_scripts() {
		wp_enqueue_script( 'd3', $this->url . '/js/d3.min.js', array('jquery') );
		wp_enqueue_script( 'raty', $this->url . '/js/jquery.raty.js', array('jquery') );
	}
	// コメントに星入力フォームの追加・削除
	function comment_form( $fields ) {
		global $post;
		$post_type = get_post_type();
		$fields['rating'] = null;
		if ( isset( $this->options[$post_type] ) && $this->options[$post_type] == 1  ) {
			$fields['rating'] .= '<div id="input-type-star"></div>';
			$fields['rating'] .= '<input type="hidden" name="csr_rating" value="" />';
		}
		// オプション：不要フォームの削除
		if ( isset( $this->options['url'] ) && $this->options['url'] == 1 ) {
			$fields['url'] = '';
		}
		if ( isset( $this->options['email'] ) && $this->options['email'] == 1 ) {
			$fields['email'] = '';
		}
		return $fields;
	}
	// コメントの保存処理
	function comment_post( $comment_id ) {
		$_comment = get_comment( $comment_id );
		$_post    = get_post( $_comment->comment_post_ID );
		if( !is_user_logged_in() ) {
			// data check
			$rating = isset( $_POST['csr_rating'] ) && is_numeric( $_POST['csr_rating'] ) ? $_POST['csr_rating'] : 3;
			$rating = intval( $rating );
			if ( ! $rating ) {
				$rating = '';
			}
			if ( strlen( $rating ) > 1 ) {
				$rating = substr( $rating, 0, 1 );
			}
			// add
			add_comment_meta( $comment_id, 'csr_rating', $rating );
		}
		return $comment_id;
	}
	// コメントの最後にレーティングを表示
	function comment_display( $comment ) {
		$post_type = get_post_type();
		$comment_id = get_comment_ID();
		$star = get_comment_meta( get_comment_ID(), 'csr_rating', true);
		$star = isset( $star ) && is_numeric( $star ) ? $star : 3;

		if ( isset( $this->options[$post_type] ) && $this->options[$post_type] == 1  ) {
			$output = wp_star_rating( array(
				'rating'    => $star,
				'type'      => 'rating',
				'number'    => 0,
				'echo'      => false,
			));
			return $comment.$output;
		}
	}
	// 管理画面を追加
	function admin_menu() {
		add_options_page (
			$this->name, //page_title
			$this->name, //menu_title
			'manage_options', // capabiliity
			COMMENT_STAR_RATING_DOMAIN, //menu_slug
			array( $this, 'admin_save_options' ) //callback
		);
	}
	function admin_setting_form() {
		$post_types = wp_list_filter( get_post_types(array('public'=>true)),array('attachment'), 'NOT' );
		?>
		<div class="wrap">
			<h2><?php echo esc_attr($this->name); ?> &raquo; <?php _e('Settings'); ?></h2>
			<form id="<?php echo esc_attr(COMMENT_STAR_RATING_DOMAIN); ?>" method="post" action="">
				<?php wp_nonce_field( 'csr-nonce-key', 'csr-key' ); ?>
				<h3><?php _e('有効にする投稿タイプを選択してください'); ?></h3>
				<?php
					foreach ( $post_types as $post_type ) {
				?>
				<p>
					<strong><?php echo esc_attr($post_type); ?>ページ上で有効にします</strong>
					<input type="checkbox" name="<?php echo esc_attr(COMMENT_STAR_RATING_DOMAIN); ?>[<?php echo esc_attr($post_type); ?>]"  value="1" <?php if( isset( $this->options[$post_type] ) && $this->options[$post_type] == '1' ) echo 'checked'; ?> />
				</p>
				<?php 
					} 
				?>
				<h3>コメントの入力から外したい要素を選択</h3>
				<p>
					<strong>URLを外す</strong>
					<input type="checkbox" name="<?php echo esc_attr(COMMENT_STAR_RATING_DOMAIN); ?>[url]"  value="1" <?php if( isset( $this->options['url'] ) && $this->options['url'] == '1' ) echo 'checked'; ?> />
				</p>
				<p>
					<strong>メールアドレスを外す</strong>
					<input type="checkbox" name="<?php echo esc_attr(COMMENT_STAR_RATING_DOMAIN); ?>[email]"  value="1" <?php if( isset( $this->options['email'] ) && $this->options['email'] == '1' ) echo 'checked'; ?> /
				</p>
				<p class="submit">
					<input class="button-primary" type="submit" name='save' value='<?php _e('Save Changes') ?>' />
				</p>
			</form>
		</div>
		<?php
	}
	// save
	function admin_save_options() {
		$post_types = wp_list_filter( get_post_types(array('public'=>true)),array('attachment'), 'NOT' );
		$key_array = array();
		foreach ( $post_types as $post_type ) {
			array_push( $key_array, $post_type );
		}
		array_push( $key_array, 'url', 'email' );
		if (isset($_POST['save'])) {
			if( check_admin_referer( 'csr-nonce-key', 'csr-key' ) ) {
				if ( !empty($_POST[COMMENT_STAR_RATING_DOMAIN]) ) {
					$options = array();
					foreach ( $_POST[COMMENT_STAR_RATING_DOMAIN] as $key => $value ) {
						if( in_array( $key, $key_array) ){
							$key = $key;
						}else{
							break;
						}
						$value = '1';
						$options += array( $key => $value );
					}
					update_option(COMMENT_STAR_RATING_DOMAIN, $options );
				}
				//wp_safe_redirect( menu_page_url( COMMENT_STAR_RATING_DOMAIN, false ) );
			}
		}
		$this->options = get_option(COMMENT_STAR_RATING_DOMAIN);
		$this->admin_setting_form();
	}
}
$comment_star_rating = new CommentStarRating();
$comment_star_rating->init();
