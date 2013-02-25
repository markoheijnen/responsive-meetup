<?php

class Responsive_Meetups_Plugin_Mailchimp {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 11 );
	}

	public function wp_enqueue_scripts() {
		wp_dequeue_style('mailchimpSF_main_css');

		wp_enqueue_style( 'responsive-meetup-mailchimp', get_stylesheet_directory_uri() . '/css/mailchimp.css', false, MCSF_VER );
	}
}

new Responsive_Meetups_Plugin_Mailchimp;