<?php

function rsvp_registration_form() {
	$result = $user_id = false;
	$name = $email = $comment = $protected = '';

	if( is_user_logged_in() ) {
		$user    = wp_get_current_user();

		$user_id = $user->ID;
		$name    = $user->display_name;
		$email   = $user->user_email;

		$protected = ' readonly="readonly"';
	}

	if( isset( $_POST['rsvp_name'], $_POST['rsvp_email'] ) ) {
		if( ! $user_id ) {
			$name    = strip_tags( $_POST['rsvp_name'] );
			$email   = strip_tags( $_POST['rsvp_email'] );
		}

		$comment = strip_tags( $_POST['rsvp_comment'] );
		$result = Responsive_Meetups_RSVP::do_rsvp( $event_id, $name, $email, $comment );
	}


	$html = '<h1>' . __( 'Registration' ) . '</h1>';

	if( is_wp_error( $result ) ) {
		echo '<ul class="error">';

		foreach ( $result->get_error_messages() as $err )
			echo '<li>' . $err . '</li>';

		echo '</ul>';
	}

	$html .= '<form action="' . get_permalink() . 'rsvp/" method="post">';
	$html .= '<div>';
	$html .= '<label for="rsvp_name">' . __( 'Name', 'responsive_meetups' ) . '<span class="required">*</span></label>';
	$html .= '<div><input name="rsvp_name" id="rsvp_name" type="text" value="' . esc_attr( $name ) . '" class="medium"' . $protected . ' /></div>';
	$html .= '</div>';

	$html .= '<div>';
	$html .= '<label for="rsvp_email">' . __( 'E-mail', 'responsive_meetups' ) . '<span class="required">*</span></label>';
	$html .= '<div><input name="rsvp_email" id="rsvp_email" type="email" value="' . esc_attr( $email ) . '" class="medium"' . $protected . ' /></div>';
	$html .= '</div>';

	$html .= '<div>';
	$html .= '<label for="rsvp_comment">' . __( 'Comment', 'responsive_meetups' ) . '</label>';
	$html .= '<div><textarea name="rsvp_comment" id="rsvp_comment" class="medium">' . esc_attr( $comment ) . '</textarea></div>';
	$html .= '</div>';

	$html .= '<input type="submit" name="submit" value="' . __( 'Submit' ) . '" />';

	$html .= '</form>';

	echo $html;
}