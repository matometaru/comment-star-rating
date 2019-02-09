<?php

class CSR_Post_Service {

	public function __construct( $post_id ) {
		$this->id       = $post_id;
		$this->csr_post = CSR_Post::find( $post_id );
	}

	public function post_init() {
		$comments         = CSR_Post::find_all_approved_comments( $this->id );
		$all_ratings      = CSR_Functions::generate_ratings_from_comments( $comments );
		$average          = CSR_Functions::calculate_average_rating( $all_ratings );
		$arranged_ratings = CSR_Functions::arrange_ratings( $all_ratings );

		$this->csr_post
			->set_rating_count( count( $all_ratings ) )
			->set_rating_average( $average )
			->set_ratings( $arranged_ratings )
			->save();

		return $this->csr_post;
	}
}
