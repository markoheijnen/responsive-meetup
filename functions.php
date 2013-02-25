<?php

if ( ! defined('ABSPATH') ) exit;

class Responsive_Meetups {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		if( ! class_exists( 'Meetups' ) )
			include 'plugin/meetup.php';
	}

	public function wp_enqueue_scripts() {
		wp_deregister_style( 'responsive-style' );
		wp_register_style( 'responsive-style', get_template_directory_uri() . '/style.css', false, get_responsive_template_version() );
		wp_enqueue_style( 'responsive-style' );

		wp_register_style( 'responsive-meetup', get_stylesheet_uri(), false, get_responsive_theme_version() );
		wp_enqueue_style( 'responsive-meetup' );

		wp_deregister_script('jquery');
		wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js', false, '1.8.3', true );
	}

	public static function event_menu() {
		global $wp_query;

		$menu = array(
			'' => __( 'Upcoming', 'responsive_meetups' ),
			'past' => __( 'Past', 'responsive_meetups' )
		);

		$selected = esc_attr( $wp_query->get('event_var') );
		if( ! isset( $menu[ $selected ] ) )
			$selected = '';

		$html = '<ul class="menu-horizontal">';
		foreach( $menu as $slug => $name ) {
			$url = get_post_type_archive_link('event') . $slug;

			if( $slug == $selected )
				$html .= '<li class="selected"><a href="' . $url . '">' . $name . '</a></li>';
			else
				$html .= '<li><a href="' . $url . '">' . $name . '</a></li>';
		}
		$html .= '</ul>';

		echo $html;
	}

	public static function rsvp_button( $event_id = false ) {
		if( ! $event_id )
			$event_id = get_the_ID();

		Responsive_Meetups_RSVP::rsvp_button( $event_id );
	}
}

new Responsive_Meetups;