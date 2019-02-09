<div class="star-rating"><?php _e( $star_rating ); ?></div>
<p class="star-counter-tit"><?php esc_attr_e( $title . $average ); ?></p>
<div class="star-counter"
	 data-ratings="<?php esc_attr_e( $rating_json ); ?>"
	 data-count="<?php esc_attr_e( $rating_count ); ?>">
</div>