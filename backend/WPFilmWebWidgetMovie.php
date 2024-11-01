<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	class WPFilmWebWidgetMovie {
		private $url;
		private $year;
		private $title;
		private $rating;
		private $imageUrl;
		private $ratingDate;
		
		public function __construct( $title, $year, $url, $imageUrl, $rating, $ratingDate ) {
			$this->url = 'http://www.filmweb.pl'. $url;
			$this->year = $year;
			$this->title = $title;
			$this->rating = $rating;
			$this->imageUrl = $imageUrl;
			if( strpos( $this->imageUrl, '.svg' ) !== FALSE ) {
				$this->imageUrl = '';
			}
			
			$this->ratingDate = $ratingDate;
		}
		
		public function getUrl( ) {
			return $this->url;
		}
		
		public function getYear( ) {
			return $this->year;
		}
		
		public function getTitle( ) {
			return $this->title;
		}
		
		public function getRating( ) {
			return $this->rating;
		}
		
		public function getImageUrl( ) {
			return $this->imageUrl;
		}
		
		public function getRatingDate( ) {
			return $this->ratingDate;
		}
		
		public static function compareMoviesByDateAsc( $movie1, $movie2 ) {
			return $movie1->getRatingDate( ) > $movie2->getRatingDate( );
		}
		
		public static function compareMoviesByDateDesc( $movie1, $movie2 ) {
			return $movie1->getRatingDate( ) < $movie2->getRatingDate( );
		}
	}
?>