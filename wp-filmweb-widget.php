<?php
/*
Plugin Name: WP Filmweb Widget
Description: Shows basic user data from Filmweb.pl portal.
Version: 0.5
Author: Mateusz Adamus
Author URI: http://mateuszadamus.pl
License: GPLv2
*/
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	
	require_once( "backend/WPFilmWebWidget.php" );
	
	class WP_FilmWeb_Widget extends WP_Widget {
		function __construct() {
			parent::__construct( 'wp_filmweb_widget', 'WP Filmweb Widget', array( 'description' => __( 'Shows basic user data from Filmweb.pl portal', 'wp_filmweb_widget' ) ) );
		}
 
		// Creating widget front-end
		// This is where the action happens
		public function widget( $args, $instance ) {
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );
			
			// before and after widget arguments are defined by themes
			echo $args[ 'before_widget' ];
			if( !empty( $title ) ) {
				echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
			}
			
			if( !empty( $instance[ 'username' ] ) ) {
				$usernames = explode( ',', $instance[ 'username' ] );
				foreach( $usernames as $username ) {
					$wpFilmWebWidget = new WPFilmWebWidget( 
											trim( $username ), 
											$instance[ 'username_position' ], 
											$instance[ 'avatar_size' ], 
											$instance[ 'top_count' ], 
											$instance[ 'top_label' ], 
											$instance[ 'last_count' ],
											$instance[ 'last_label' ] );
											
					if( $wpFilmWebWidget->load( ) === TRUE ) {
						$wpFilmWebWidget->parse( );
						echo $wpFilmWebWidget->show( );
					} else {
						echo __( 'Error loading Filmweb data', 'wp_filmweb_widget' );
					}
				}
			} else {
				echo __( 'Please enter Filmweb username in the widget\'s settings', 'wp_filmweb_widget' );
			}
 
			echo $args[ 'after_widget' ];
		}
         
		// Widget Backend
		public function form( $instance ) {
			/* Set up some default widget settings. */
			$defaults = array( 'title' => '', 'username' => '', 'username_position' => 'bottom', 'avatar_size' => 'large', 'top_count' => 4, 'last_count' => 4 );
			$instance = wp_parse_args( ( array ) $instance, $defaults );
			
			// Widget admin form
			// Title
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'title' ) .'">'. _e( 'Title:' ) .'</label>';
			echo '<input class="widefat" id="'. $this->get_field_id( 'title' ) .'" name="'. $this->get_field_name( 'title' ) .'" type="text" value="'. esc_attr( $instance[ 'title' ] ) .'" />';
			echo '</p>';
			
			// Username
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'username' ) .'">'. __( 'Filmweb Username:', 'wp_filmweb_widget' ) .'</label>';
			echo '<input class="widefat" id="'. $this->get_field_id( 'username' ) .'" name="'. $this->get_field_name( 'username' ) .'" type="text" value="'. esc_attr( $instance[ 'username' ] ) .'" />';
			echo '</p>';
			
			// Username Position
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'username_position' ) .'">'. __( 'Username Position:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'username_position' ) .'" name="'. $this->get_field_name( 'username_position' ) .'" class="widefat" style="width:100%;">';
			
			echo '<option value="hidden" ';
			if( $instance[ 'username_position' ] == 'hidden' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'hidden', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="bottom" ';
			if( $instance[ 'username_position' ] == 'bottom' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'bottom', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="top" ';
			if( $instance[ 'username_position' ] == 'top' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'top', 'wp_filmweb_widget' ) .'</option>';
			
			echo '</select>';
			echo '</p>';
			
			// Avatar
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'avatar_size' ) .'">'. __( 'Avatar Size:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'avatar_size' ) .'" name="'. $this->get_field_name( 'avatar_size' ) .'" class="widefat" style="width:100%;">';
			
			echo '<option value="hidden" ';
			if( $instance[ 'avatar_size' ] == 'hidden' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'hidden', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="small" ';
			if( $instance[ 'avatar_size' ] == 'small' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'small', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="large" ';
			if( $instance[ 'avatar_size' ] == 'large' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'large', 'wp_filmweb_widget' ) .'</option>';
			
			echo '</select>';
			echo '</p>';

			// Top Rated Movies Count
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'top_count' ) .'">'. __( 'Top Rated Movies Count:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'top_count' ) .'" name="'. $this->get_field_name( 'top_count' ) .'" class="widefat" style="width:100%;">';
			
			for( $i = 0; $i <= 12; $i++ ) {
				echo '<option ';
				if( $instance[ 'top_count' ] == $i ) {
					echo 'selected="selected"';
				}
				echo '>'. $i .'</option>';
			}
			echo '</select>';
			echo '</p>';
			
			// Show Top Rated Movies Label
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'top_label' ) .'">'. __( 'Show Top Rated Movies Label:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'top_label' ) .'" name="'. $this->get_field_name( 'top_label' ) .'" class="widefat" style="width:100%;">';
			
			echo '<option value="true" ';
			if( $instance[ 'top_label' ] == 'true' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'yes', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="false" ';
			if( $instance[ 'top_label' ] == 'false' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'no', 'wp_filmweb_widget' ) .'</option>';
			
			echo '</select>';
			echo '</p>';

			// Last Seen Movies Count
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'last_count' ) .'">'. __( 'Last Seen Movies Count:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'last_count' ) .'" name="'. $this->get_field_name( 'last_count' ) .'" class="widefat" style="width:100%;">';
			
			for( $i = 0; $i <= 8; $i++ ) {
				echo '<option ';
				if( $instance[ 'last_count' ] == $i ) {
					echo 'selected="selected"';
				}
				echo '>'. $i .'</option>';
			}
			echo '</select>';
			echo '</p>';
			
			// Show Last Seen Movies Label
			echo '<p>';
			echo '<label for="'. $this->get_field_id( 'last_label' ) .'">'. __( 'Show Last Seen Movies Label:', 'wp_filmweb_widget' ) .'</label>';
			echo '<select id="'. $this->get_field_id( 'last_label' ) .'" name="'. $this->get_field_name( 'last_label' ) .'" class="widefat" style="width:100%;">';
			
			echo '<option value="true" ';
			if( $instance[ 'last_label' ] == 'true' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'yes', 'wp_filmweb_widget' ) .'</option>';

			echo '<option value="false" ';
			if( $instance[ 'last_label' ] == 'false' ) {
				echo 'selected="selected"';
			}
			echo '>'. __( 'no', 'wp_filmweb_widget' ) .'</option>';
			
			echo '</select>';
			echo '</p>';
		}
     
		// Updating widget replacing old instances with new
		public function update( $new_instance, $old_instance ) {
			$instance = array( );
			
			$instance[ 'title' ] = ( !empty( $new_instance[ 'title' ] ) ) ? strip_tags( $new_instance[ 'title' ] ) : '';
			$instance[ 'username' ] = ( !empty( $new_instance[ 'username' ] ) ) ? strip_tags( $new_instance[ 'username' ] ) : '';
			$instance[ 'top_count' ] = ( !empty( $new_instance[ 'top_count' ] ) ) ? strip_tags( $new_instance[ 'top_count' ] ) : '';
			$instance[ 'top_label' ] = ( !empty( $new_instance[ 'top_label' ] ) ) ? strip_tags( $new_instance[ 'top_label' ] ) : '';
			$instance[ 'last_count' ] = ( !empty( $new_instance[ 'last_count' ] ) ) ? strip_tags( $new_instance[ 'last_count' ] ) : '';
			$instance[ 'last_label' ] = ( !empty( $new_instance[ 'last_label' ] ) ) ? strip_tags( $new_instance[ 'last_label' ] ) : '';
			$instance[ 'avatar_size' ] = ( !empty( $new_instance[ 'avatar_size' ] ) ) ? strip_tags( $new_instance[ 'avatar_size' ] ) : '';
			$instance[ 'username_position' ] = ( !empty( $new_instance[ 'username_position' ] ) ) ? strip_tags( $new_instance[ 'username_position' ] ) : '';
			
			WPFilmWebWidget::clearCache( );
			
			return $instance;
		}
	}
 
	// Register and load the widget
	function wp_filmweb_widget_load() {
		register_widget( 'WP_FilmWeb_Widget' );
	}
	add_action( 'widgets_init', 'wp_filmweb_widget_load' );
	
	// Enqueue plugin style-file
	function wp_filmweb_widget_stylesheet( ) {
		// Respects SSL, Style.css is relative to the current file
		wp_register_style( 'wp-filmweb-widget-style', plugins_url( 'style.css', __FILE__ ) );
		wp_enqueue_style( 'wp-filmweb-widget-style' );
	}
	add_action( 'wp_enqueue_scripts', 'wp_filmweb_widget_stylesheet' );
	
	// Register translation domain
	function wp_filmweb_widget_textdomain( ) {
		$domain = 'wp_filmweb_widget';
		
		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters( 'plugin_locale', get_locale( ), $domain );

		load_textdomain( $domain, WP_LANG_DIR .'/wp-filmweb-widget/'. $domain .'-'. $locale .'.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) .'/languages/' );
	}
	add_action( 'plugins_loaded', 'wp_filmweb_widget_textdomain' );
?>