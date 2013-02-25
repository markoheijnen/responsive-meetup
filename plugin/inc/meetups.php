<?php

class Responsive_Meetups_Events {
	private $posttype_slug = 'events';
	private $taxonomy_slug = 'events/type';

	public function __construct() {
		add_action( 'init', array( $this, 'set_vars' ), 9 );

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		add_action( 'init', array( $this, 'register_taxonomy' ) );

		add_action( 'cmb_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'cmb_field_types', array( $this, 'cmb_field_types' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'template_include', array( $this, 'template_include' ) );
	}

	public function set_vars() {
		$this->posttype_slug = apply_filters( 'responive_meetups_event_slug', $this->posttype_slug );
		$this->taxonomy_slug = apply_filters( 'responive_meetups_event_type_slug', $this->taxonomy_slug );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Events', 'responsive_meetups' ),
			'singular_name'      => __( 'event', 'responsive_meetups' ),
			'add_new'            => __( 'Add new', 'responsive_meetups' ),
			'add_new_item'       => __( 'Add new event', 'responsive_meetups' ),
			'edit_item'          => __( 'Edit event', 'responsive_meetups' ),
			'new_item'           => __( 'New event', 'responsive_meetups' ),
			'all_items'          => __( 'All events', 'responsive_meetups' ),
			'view_item'          => __( 'View event', 'responsive_meetups' ),
			'search_items'       => __( 'Search events', 'responsive_meetups' ),
			'not_found'          => __( 'No events found', 'responsive_meetups' ),
			'not_found_in_trash' => __( 'No events found in Trash', 'responsive_meetups' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Events', 'responsive_meetups' )
		);

		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'rewrite'         => array( 'slug' => $this->posttype_slug ),
			'capability_type' => 'post',
			'has_archive'     => true, 
			'hierarchical'    => false,
			'menu_position'   => null,
			'supports'        => array( 'title', 'editor', 'excerpt', 'comments' )
		); 

		register_post_type( 'event', $args );
	}

	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['event'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Event updated. <a href="%s">View event</a>', 'responsive_meetups' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'responsive_meetups' ),
			3 => __( 'Custom field deleted.', 'responsive_meetups' ),
			4 => __( 'Event updated.', 'responsive_meetups' ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'responsive_meetups' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Event published. <a href="%s">View event</a>', 'responsive_meetups' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Event saved.', 'responsive_meetups'),
			8 => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>', 'responsive_meetups' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'responsive_meetups' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'responsive_meetups'), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}


	public function register_taxonomy() {
		$labels = array(
			'name'                       => __( 'Types', 'responsive_meetups' ),
			'singular_name'              => __( 'Type', 'responsive_meetups' ),
			'search_items'               => __( 'Search types', 'responsive_meetups' ),
			'popular_items'              => __( 'Popular types', 'responsive_meetups' ),
			'all_items'                  => __( 'All types', 'responsive_meetups' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit type', 'responsive_meetups' ), 
			'update_item'                => __( 'Update type', 'responsive_meetups' ),
			'add_new_item'               => __( 'Add new type', 'responsive_meetups' ),
			'new_item_name'              => __( 'New type name', 'responsive_meetups' ),
			'separate_items_with_commas' => __( 'Separate typess with commas' ),
			'add_or_remove_items'        => __( 'Add or remove types' ),
			'choose_from_most_used'      => __( 'Choose from the most used types' ),
			'menu_name'                  => __( 'Type', 'responsive_meetups' )
		);

		register_taxonomy( 'event_type', 'event', array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => $this->taxonomy_slug ),
		) );
	}

	public function add_meta_boxes( $meta_boxes ) {
		$meta_boxes[] = array(
			'id'         => 'event_info',
			'title'      => __( 'Event information', 'responsive_meetups' ),
			'pages'      => 'event',
			'context'    => 'advanced',
			'priority'   => 'high',
			'show_names' => true, // Show field names on the left
			'fields'     => array(
				array( 'id' => 'location_name', 'name' => __( 'Location name', 'responsive_meetups'), 'type' => 'text', 'cols' => 12 ),
				array( 'id' => 'address', 'name' =>  __( 'Location address', 'responsive_meetups'), 'type' => 'text', 'cols' => 12 ),
				array( 'id' => 'spots', 'name' =>  __( 'Amount of spots', 'responsive_meetups'), 'type' => 'text_integer', 'cols' => 12 ),

				array( 'id' => 'datetime_event', 'name' =>  __( 'Event date/time', 'responsive_meetups'), 'type' => 'datetime_unix', 'cols' => 6 ),
				array( 'id' => 'datetime_registration', 'name' =>  __( 'Registration date/time', 'responsive_meetups'), 'type' => 'datetime_unix', 'cols' => 6 )
			)
		);

		return $meta_boxes;
	}

	public function cmb_field_types( $field_types ) {
		$field_types['text_integer']  = 'CMB_Text_Small_Field_Integer';

		return $field_types;
	}

	public function admin_enqueue_scripts() {
		wp_register_style( 'responsive-admin', get_stylesheet_directory_uri() . '/plugin/css/main.css', false, get_responsive_theme_version() );
		wp_enqueue_style( 'responsive-admin' );
	}



	public function add_rewrite_rule( $rules ) {
		$newrules = array();
		$newrules[ $this->posttype_slug . '/past/?$' ] = 'index.php?post_type=event&event_var=past';
		$newrules[ $this->posttype_slug . '/([^/]+)/rsvp/?$' ] = 'index.php?event=$matches[1]&event_var=rsvp';

		return $newrules + $rules;
	}

	public function add_query_vars( $vars ) {
		array_push( $vars, 'event_var' );

		return $vars;
	}

	public function pre_get_posts( $query ) {
		if( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'event' ) ) {
			$meta_query = $query->get( 'meta_query' );

			$meta_query[] = array(
				'key'     => 'datetime_event',
				'value'   => time(),
				'compare' => ( 'past' == $query->get( 'event_var' ) ) ? "<=": ">"
			);

			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	public function template_include( $template ) {
		if( 'rsvp' == get_query_var( 'event_var' ) ) {
			$template = get_query_template( 'event-rsvp' );

			if( ! $template )
				$template = dirname( dirname( __FILE__ ) ) . '/templates/rsvp.php';
		}

		return $template;
	}
}
