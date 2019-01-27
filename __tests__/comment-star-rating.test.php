<?php
require_once dirname(__FILE__) . "../comment-star-rating.php";

Class CommentStarRatingTest extends PHPUnit_Framework_TestCase
{
	private $ctr;

	public function __construct()
	{
		$this->ctr = new CommentStarRating();
	}

	/**
	 * @test
	 */
	public function _test_comment_form()
	{
		$this->ctr->comment_form();
	}

}