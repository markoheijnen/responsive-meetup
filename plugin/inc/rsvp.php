<?php

class Responsive_Meetups_RSVP {
	private static $post_statuses = array(
		'attend'      => 'Attend',
		'notattend'   => 'Not attend',
		'waitinglist' => 'Waiting list'
	);

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_post_statuses' ) );

		self::$post_statuses = array(
			'attend'      => __( 'Attend', 'responsive_meetups' ),
			'notattend'   => __( 'Not attend', 'responsive_meetups' ),
			'waitinglist' => __( 'Waiting list', 'responsive_meetups' )
		);
	}

	public function register_post_type() {
		$args = array(
			'label'           => __( 'RSVP', 'responsive_meetups' ),
			'public'          => false,
			'rewrite'         => false,
			'capability_type' => 'post',
			'has_archive'     => false, 
			'hierarchical'    => false,
			'menu_position'   => null,
			'supports'        => array( 'title' )
		);

		register_post_type( 'rsvp', $args );
	}

	public function register_post_statuses() {
		$args = array(
			'label'                     => __( 'Attend', 'responsive_meetups' ),
			'label_count'               => _n_noop( 'Attendee (%s)',  'Attendees (%s)', 'responsive_meetups' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'exclude_from_search'       => false,
		);
		register_post_status( 'attend', $args );

		$args = array(
			'label'                     => __( 'Attendee', 'responsive_meetups' ),
			'label_count'               => _n_noop( 'Attendee (%s)',  'Attendees (%s)', 'responsive_meetups' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'exclude_from_search'       => false,
		);
		register_post_status( 'attend', $args );

		$args = array(
			'label'                     => __( 'Non attendee', 'responsive_meetups' ),
			'label_count'               => _n_noop( 'Non attendee (%s)',  'Non attendees (%s)', 'responsive_meetups' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'exclude_from_search'       => false,
		);
		register_post_status( 'notattend', $args );

		$args = array(
			'label'                     => __( 'Waiting list', 'responsive_meetups' ),
			'label_count'               => __( 'Waiting list (%s)', 'responsive_meetups' ),
			'public'                    => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'exclude_from_search'       => false,
		);
		register_post_status( 'waitinglist', $args );
	}

	public static function is_rsvp( $event_id ) {
		$is_rsvp = false;

		if( is_user_logged_in() ) {
			$user = wp_get_current_user();

			$args = array(
				'post_type'   => 'rsvp',
				'post_status' => 'null',
				'post_parent' => $event_id,
				'meta_query'  => array(
					array(
						'key'   => 'email',
						'value' => $user->user_email
					)
				)
			);
			$rsvps = get_posts( $args );

			if( count( $rsvps ) > 0 )
				return $rsvps[0]->post_status;
		}

		return false;
	}


	public function rsvp_button( $event_id ) {
		$timestamp_event        = get_post_meta( $event_id, 'datetime_event', true );
		$timestamp_registration = get_post_meta( $event_id, 'datetime_registration', true );

		if( $timestamp_registration && $timestamp_registration < time() ) {
			$is_rsvp = self::is_rsvp( $event_id );

			if( $is_rsvp )
				echo $is_rsvp;
			else {
				$link = get_permalink( $event_id ) . 'rsvp/';
				echo '<a href="' . $link . '">' . __( 'RSVP', 'responsive_meetups' ) . '</a>';
			}
		}
		else if( $timestamp_event && $timestamp_event > time() ) {
			_e( 'RSVP has been closed', 'responsive_meetups' );
		}
		else {
			_e( 'RSVP not open yet', 'responsive_meetups' );
		}
	}


	public function do_rsvp( $event_id, $name, $email, $comment = '', $type = '' ) {
		$errors = new WP_Error();

		if( empty( $name ) )
			$errors->add( 'empty_name', __( 'Please enter your name.' ) );

		if( empty( $email ) )
			$errors->add( 'empty_email', __( 'Please enter your email.' ) );
		else if( ! is_email( $email ) )
			$errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.' ) );
		else {
			$args = array(
				'post_type'   => 'rsvp',
				'post_status' => 'null',
				'post_parent' => $event_id,
				'meta_query'  => array(
					array(
						'key'   => 'email',
						'value' => $email
					)
				)
			);
			$rsvps = get_posts( $args );

			if( count( $rsvps ) > 0 )
				$errors->add( 'email_exists', __( 'This email is already registered' ) );
		}


		if ( $errors->get_error_codes() )
			return $errors;

		$spots = absint( get_post_meta( $event_id, 'spots', true ) );
		$count = Responsive_Meetups_RSVP::counts( $event_id );

		if( ! isset( self::$post_statuses[ $type ] ) )
			$type = 'attend';

		if( 'attend' == $type && $count->attend >= $spots )
			$type = 'waitinglist';


		$args = array(
			'post_title'  => $name,
			'post_status' => $type,
			'post_parent' => $event_id,
			'post_type'   => 'rsvp'
		);
		$rsvp_id = wp_insert_post( $args );

		update_post_meta( $rsvp_id, 'name', $name );
		update_post_meta( $rsvp_id, 'email', $email );
		update_post_meta( $rsvp_id, 'comment', $comment );

		if( is_user_logged_in() )
			update_post_meta( $rsvp_id, 'user_id', get_current_user_id() );

		$title   = sprintf( __( 'RSVP for: %s', 'responsive_meetups' ), get_the_title( $event_id ) );
		$message = sprintf( __( 'Dear %s.', 'responsive_meetups' ), $name ) . "\r\n\r\n";

		if( 'waitinglist' == $type )
			$message .= sprintf( __( 'You are on the waitinglist for "%s".', 'responsive_meetups' ), get_the_title( $event_id ) ) . "\r\n";
		if( 'notattend' == $type )
			$message .= sprintf( __( 'You just registered to not attend the event: %s.', 'responsive_meetups' ), get_the_title( $event_id ) ) . "\r\n";
		else
			$message .= sprintf( __( 'You just registered to attend the event: %s.', 'responsive_meetups' ), get_the_title( $event_id ) ) . "\r\n";

		$title = apply_filters( 'retrieve_password_title', $title, $event_id );
		$message = apply_filters( 'retrieve_password_message', $message, $event_id );

		wp_mail( $email, $title, $message );

		wp_cache_delete( 'rsvp', 'counts_parent_' . $event_id  );

		return true;
	}


	/**
	 * This function is almost indintical to WordPress own wp_count_posts.
	 * Except this function needs post parent
	 *
	 * @since 2.5.0
	 * @link http://codex.wordpress.org/Template_Tags/wp_count_posts
	 *
	 * @param string $type Optional. Post type to retrieve count
	 * @param string $perm Optional. 'readable' or empty.
	 * @return object Number of posts for each status
	 */
	function counts( $parent_id, $perm = '' ) {
		global $wpdb;

		if( ! $parent_id )
			return false;

		$user = wp_get_current_user();

		$cache_key = 'rsvp';

		$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'rsvp' && post_parent %i";

		if ( 'readable' == $perm && is_user_logged_in() ) {
			$post_type_object = get_post_type_object( 'rsvp' );

			if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
				$cache_key .= '_' . $perm . '_' . $user->ID;
				$query .= " AND (post_status != 'private' OR ( post_author = '$user->ID' AND post_status = 'private' ))";
			}
		}

		$query .= ' GROUP BY post_status';

		$count = wp_cache_get( $cache_key, 'counts_parent_' . $parent_id );
		if ( false !== $count )
			return $count;

		$count = $wpdb->get_results( $wpdb->prepare( $query, $parent_id ), ARRAY_A );

		$stats = array();
		foreach ( self::$post_statuses as $state => $lable )
			$stats[ $state ] = 0;

		foreach ( (array) $count as $row )
			$stats[ $row['post_status'] ] = $row['num_posts'];

		$stats = (object) $stats;
		wp_cache_set( $cache_key, $stats, 'counts_parent_' . $parent_id );

		return $stats;
	}
}