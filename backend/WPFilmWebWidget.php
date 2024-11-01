<?php
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	
	require_once( 'WPFilmWebWidgetMovie.php' );

	class WPFilmWebWidget {
		private $html;
		private $htmlDOM;
		private $htmlXPath;
		
		private $username;
		private $topCount;
		private $lastCount;
		private $largePhoto;
		private $smallPhoto;
		private $avatarSize;
		private $showTopLabel;
		private $showLastLabel;
		private $lastSeenMovies;
		private $topRatedMovies;
		private $usernamePosition;
		
		public function __construct( $username, $usernamePosition, $avatarSize, $topCount, $showTopLabel, $lastCount, $showLastLabel ) {
			$this->username = $username;
			$this->topCount = $topCount;
			$this->lastCount = $lastCount;
			$this->avatarSize = $avatarSize;
			$this->showTopLabel = $showTopLabel;
			$this->showLastLabel = $showLastLabel;
			$this->usernamePosition = $usernamePosition;
			
			$this->largeAvatar = '';
			$this->smallAvatar = '';
			$this->lastSeenMovies = array( );
			$this->topRatedMovies = array( );
		}
		
		private function showMovie( $movie, $class ) {
			$html.= '<li class="wp-filmweb-widget-movie '. $class .'">';
			$html.= '<span class="wp-filmweb-widget-movie-poster">';
			$html.= '<a href="'. $movie->getUrl( ) .'" target="_blank">';
			
			if( $movie->getImageUrl( ) != '' ) {
				$html.= '<img src="'. $movie->getImageUrl( ) .'" />';
			}
			
			$html.= '</a>';
			$html.= '</span>';
			$html.= '<div class="wp-filmweb-widget-movie-info">';
			$html.= '<a href="'. $movie->getUrl( ) .'" target="_blank">'. $movie->getTitle( ) .'</a>';
			$html.= '<div class="wp-filmweb-widget-movie-movieYear">'. $movie->getYear( ) .'</div>';
			$html.= '<div class="wp-filmweb-widget-movie-ratingDate">'. $movie->getRatingDate( )->format( 'Y-m-d' ) .'</div>';
			$html.= '<div class="wp-filmweb-widget-movie-rating">'. $movie->getRating( ) .'</div>';
			$html.= '<div class="wp-filmweb-widget-movie-rating-stars wp-filmweb-widget-movie-rating-stars-'. $movie->getRating( ) .'"></div>';
			$html.= '</div>';
			$html.= '</li>';

			return $html;
		}
		
		private function getCacheTimePath( ) {
			return $this->getCacheDir( ) . $this->username .'_cache_time.txt';
		}
		
		private function getCacheHtmlPath( ) {
			return $this->getCacheDir( ) . $this->username .'_cache_html.txt';
		}
		
		private function convertJSJSON2PHPJSON( $JSJSON ) {
			$PHPJSON = $JSJSON;
			$PHPJSON = preg_replace( '/(,|\{)[ \t\n]*(\w+)[ ]*:[ ]*/', '$1"$2":', $PHPJSON );
			$PHPJSON = preg_replace( '/":\'?([^\[\]\{\}]*?)\'?[ \n\t]*(,"|\}$|\]$|\}\]|\]\}|\}|\])/', '":"$1"$2', $PHPJSON );
			return $PHPJSON;
		}
		
		private function parseAvatar( ) {
			if( $this->avatarSize == 'hidden' ) {
				return;
			}
			
			$avatarList = $this->htmlXPath->query( '//img[@class="userAvatarImg"]' );
			if( $avatarList->length == 0 ) {
				return;
			}
			
			// there should only be one avatar
			$avatar = $avatarList->item( 0 );

			// user has no avatar set
			if( strpos( $avatar->getAttribute( 'src' ), '.1.jpg' ) === FALSE ) {
				return;
			}
			
			$this->largeAvatar = $avatar->getAttribute( 'src' );
			$this->smallAvatar = str_replace( '.1.jpg', '.2.jpg', $this->largeAvatar );			
		}
		
		private function parseMovies( $moviesDiv ) {
			$movies = array( );
			
			$movieList = $this->htmlXPath->query( './/li', $moviesDiv );
			foreach( $movieList as $movie ) {
				if( $movie->getAttribute( 'rel') == '' ) {
					continue;
				}
				
				$movieJSON = $this->htmlXPath->query( './/div[@class="hide"]', $movie )->item( 0 );
				$PHPJSON = $this->convertJSJSON2PHPJSON( $movieJSON->nodeValue );
				$movieJSON = json_decode( $PHPJSON, true );
				if( $movieJSON == NULL ) {
					continue;
				}
				
				$movieLink = $this->htmlXPath->query( './/a', $movie )->item( 0 );
				$movieInfo = $this->htmlXPath->query( './/div', $movie )->item( 0 );
				$movieImage = $this->htmlXPath->query( './/img', $movieLink )->item( 0 );
				
				$movieYear = $this->htmlXPath->query( './/li', $movieInfo )->item( 0 );
				if( !$movieYear ) {
					$movieYear = $this->htmlXPath->query( './/div', $movieInfo )->item( 0 );
				}
				$movieTitle = $this->htmlXPath->query( './/a', $movieInfo )->item( 0 );
				
				$movie = new WPFilmWebWidgetMovie( 
							$movieTitle->nodeValue,
							$movieYear->nodeValue,
							$movieLink->getAttribute( 'href' ),
							$movieImage->getAttribute( 'src' ),
							$movieJSON[ 'r' ],
							new DateTime( $movieJSON[ 'd' ][ 'y' ] .'-'. $movieJSON[ 'd' ][ 'm' ] .'-'. $movieJSON[ 'd' ][ 'd' ]  ) );
				
				$movies[ ] = $movie;
			}
			
			return $movies;
		}
		
		private function parseLastSeenMovies( ) {
			if( $this->lastCount == 0 ) {
				return;
			}
			
			$divList = $this->htmlXPath->query( '//div[contains(@class, "lastSeenFilms")]' );
			if( $divList->length == 0 ) {
				return;
			}
			
			$div = $divList->item( 0 );
			$this->lastSeenMovies = $this->parseMovies( $div );
			
			// descending movies by rating date
			usort( $this->lastSeenMovies, array( 'WPFilmWebWidgetMovie', 'compareMoviesByDateDesc' ) );
		}
		
		private function parseTopRatedMovies( ) {
			if( $this->topCount == 0 ) {
				return;
			}
			
			$divList = $this->htmlXPath->query( '//div[contains(@class, "bestFilms")]' );
			if( $divList->length == 0 ) {
				return;
			}
			
			$div = $divList->item( 0 );
			$this->topRatedMovies = $this->parseMovies( $div );
		}
		
		public function getUrl( ) {
			return "http://www.filmweb.pl/user/". $this->username;
		}
		
		public function load( ) {
			$this->html = FALSE;
			$saveToFile = FALSE;
			$loadFromFile = FALSE;
			
			// check if it should be loaded from file
			$f = @fopen( $this->getCacheTimePath( ), 'r' );
			if( $f ) {
				$fileDate = fgets( $f );
				fclose( $f );
				
				$fileDateTime = new DateTime( $fileDate );
				$currentDateTime = new DateTime( );
				
				$fileTime = strtotime( $fileDateTime->format( 'Y-m-d H:i:s' ) );
				$currentTime = strtotime( $currentDateTime->format( 'Y-m-d H:i:s' ) );
				$hours = abs( ( $currentTime - $fileTime ) / 3600 );
				
				// load from file if file data younger than 6 hours
				$loadFromFile = $hours <= 6;
			}
			
			// load from file
			if( $loadFromFile ) {
				$this->html = file_get_contents( $this->getCacheHtmlPath( ) );
			}
			
			// load from www
			if( $this->html === FALSE ) {
				$saveToFile = TRUE;
				$this->html = file_get_contents( $this->getUrl( ) );
				if( $this->html === FALSE ) {
					return FALSE;
				}
			}
			
			// save to file
			if( $saveToFile ) {
				$currentDateTime = new DateTime( );
				
				$f = @fopen( $this->getCacheTimePath( ), 'w' );
				if( $f ) {
					fwrite( $f, $currentDateTime->format( 'Y-m-d H:i:s' ) );
					fclose( $f );
				}
				
				$f = fopen( $this->getCacheHtmlPath( ), 'w' );
				if( $f ) {
					fwrite( $f, $this->html );
					fclose( $f );
				}
				
				// clear cache for the page
				// if WP Fastest Cache is installed
				if( isset( $GLOBALS[ "wp_fastest_cache" ] ) ) {
					$GLOBALS[ "wp_fastest_cache" ]->deleteCache( );
				}
			}
			
			return TRUE;
		}

		public function parse( ) {
			if( $this->html === FALSE || $this->html == '' ) {
				return;
			}
			
			$this->htmlDOM = new DOMDocument( );
			@$this->htmlDOM->loadHTML( $this->html );
			
			$this->htmlXPath = new DOMXPath( $this->htmlDOM );
			
			$this->parseAvatar( );
			$this->parseLastSeenMovies( );
			$this->parseTopRatedMovies( );
		}
		
		public function show( ) {
			$html = '<!-- WP Filmweb Widget Start //-->';
			$html.= '<div class="wp-filmweb-widget-container">';
			
			$html.= '<a href="'. $this->getUrl( ) .'" target="_blank">';
			$html.= '<div class="wp-filmweb-widget-avatar">';
			
			$avatarShown = FALSE;
			if( $this->avatarSize == 'large' && $this->largeAvatar != '' ) {
				$avatarShown = TRUE;
				$html.= '<img class="wp-filmweb-widget-large" src="'. $this->largeAvatar .'" />';
			} elseif( $this->avatarSize == 'small' && $this->smallAvatar != '' ) {
				$avatarShown = TRUE;
				$html.= '<img class="wp-filmweb-widget-small" src="'. $this->smallAvatar .'" />';
			}
			
			if( $this->usernamePosition != 'hidden' ) {
				if( $avatarShown ) {
					$html.= '<span class="wp-filmweb-widget-name';
					
					if( $this->usernamePosition == 'top' ) {
						$html.= ' wp-filmweb-widget-name-top';
					} else {
						$html.= ' wp-filmweb-widget-name-bottom';
					}
					
					$html.= '">'. $this->username .'</span>';
				} else {
					$html.= $this->username;
				}
			}
			
			$html.= '</div>';
			$html.= '</a>';
			
			if( $this->lastCount > 0 ) {
				$html.= '<ul class="wp-filmweb-widget-movies wp-filmweb-widget-movies-lastSeen">';
				
				if( $this->showLastLabel != 'false' ) {
					$html.= '<span class="wp-filmweb-widget-movies-title">'. __( 'Last Seen Movies', 'wp_filmweb_widget' ) .'</span>';
				}
				
				$count = 0;
				foreach( $this->lastSeenMovies as $movie ) {
					$html.= $this->showMovie( $movie, "wp-filmweb-widget-lastSeen" );
					
					$count++;
					if( $count >= $this->lastCount ) {
						break;
					}
				}
				$html.= '</ul>';
			}
			
			if( $this->topCount > 0 ) {
				$html.= '<ul class="wp-filmweb-widget-movies wp-filmweb-widget-movies-topRated">';
				
				if( $this->showTopLabel != 'false' ) {
					$html.= '<span class="wp-filmweb-widget-movies-title">'. __( 'Top Rated Movies', 'wp_filmweb_widget' ) .'</span>';
				}
				
				$count = 0;
				foreach( $this->topRatedMovies as $movie ) {
					$html.= $this->showMovie( $movie, "wp-filmweb-widget-topRated" );
					
					$count++;
					if( $count >= $this->topCount ) {
						break;
					}
				}
				$html.= '</ul>';
			}
			
			$html.= '</div>';
			$html.= '<!-- WP Filmweb Widget End //-->';
			
			return $html;
		}
		
		public static function clearCache( ) {
			$files = glob( WPFilmWebWidget::getCacheDir( ) .'*' ); // get all file names
			foreach( $files as $file ) {
				if( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}
		
		public static function getCacheDir( ) {
			return dirname( plugin_dir_path( __FILE__ ) ) .'/cache/';
		}
	}
?>