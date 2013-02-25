<?php

include 'lib/custom-meta-boxes/custom-meta-boxes.php';
include 'inc/custom-meta-boxes-addon.php';

include 'inc/template.php';

include 'inc/meetups.php';
include 'inc/rsvp.php';

class Meetups {
	public function __construct() {
		new Responsive_Meetups_Events();
		new Responsive_Meetups_RSVP();

		if( defined('MCSF_VER') )
			include 'inc/plugin-mailchimp.php';
	}
}
new Meetups();