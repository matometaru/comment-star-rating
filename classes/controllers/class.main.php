<?php

class CSR_Main_Controller extends CSR_Controller {

	private $csr_option;
	private $csr_post;
	private $csr_post_service;
	private $url;

	public function __construct( $post_id, $csr_option ) {
		$this->url              = plugins_url( CSR_Config::DOMAIN );
		$this->csr_option       = $csr_option;
		$this->csr_post_service = new CSR_Post_Service( $post_id );
		$this->csr_post         = $this->csr_post_service->post_init();

		if ( $this->csr_option->is_enabled_post_type() ) {
			add_action( 'wp_head', array( $this, 'd3_init' ) );
			add_action( 'wp_head', array( $this, 'raty_init' ) );
			add_action( 'wp_head', array( $this, 'json_ld' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		}
	}

	/**
	 * D3 init.
	 */
	public function d3_init() {
		// 一覧を出力 D3.js.
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				HorizontalBarGraph = function (el, series) {
					this.el = d3.select(el);
					this.series = series;
				};
				HorizontalBarGraph.prototype.draw = function (count) {
					console.log(count);
					var x = d3.scaleLinear()
						.domain([0, count])
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

				var elements = document.getElementsByClassName('star-counter');
				 for (var i = 0; i < elements.length; i++) {
				 	var ratings = JSON.parse(elements[i].dataset.ratings);
				 	var count = elements[i].dataset.count;
					var dataset = [
						{label: "5つ星", value: ratings[5] },
						{label: "4つ星", value: ratings[4] },
						{label: "3つ星", value: ratings[3] },
						{label: "2つ星", value: ratings[2] },
						{label: "1つ星", value: ratings[1] },
					];
					var graph = new HorizontalBarGraph(elements[i], dataset);
					graph.draw(count);
				}
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
		$average = $this->csr_post->get( 'rating_average' );
		$count   = $this->csr_post->get( 'rating_count' );
		$ratings = $this->csr_post->get( 'ratings' );
		?>
		<script type="application/ld+json">
			{
				"@context": "http://schema.org",
				"@type": "AggregateRating",
				"itemReviewed": "Article",
				"ratingValue": "<?php echo esc_js( $average ); ?>",
				"bestRating": "<?php echo esc_js( max( $ratings ) ); ?>",
				"worstRating": "<?php echo esc_js( min( $ratings ) ); ?>",
				"ratingCount": "<?php echo esc_js( $count ); ?>"
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
