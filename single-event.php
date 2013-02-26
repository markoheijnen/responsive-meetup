<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Archive Template
 *
 *
 * @file           single-event.php
 * @package        Responsive 
 * @author         Marko Heijnen 
 * @copyright      2003 - 2012 Marko Heijnen
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive-meetup/single-event.php
 * @link           http://codex.wordpress.org/Theme_Development#Single_Post_.28single.php.29
 * @since          available since Release 1.0
 */

?>
<?php get_header(); ?>

<?php if (have_posts()) : ?>

		<div id="content-archive">

		<?php while (have_posts()) : the_post(); ?>

		<?php $options = get_option('responsive_theme_options'); ?>
		<?php if ( $options['breadcrumb'] == 0 ): ?>
		<?php echo responsive_breadcrumb_lists(); ?>
		<?php endif; ?>


			<div id="post-<?php the_ID(); ?>" <?php post_class('grid col-620'); ?>>
				<h1 class="post-title"><?php the_title(); ?></h1>

				<div class="post-meta">
					<?php if ( comments_open() ) : ?>
						<span class="comments-link">
						<span class="mdash">&mdash;</span>
					<?php comments_popup_link(__('No Comments &darr;', 'responsive_meetups'), __('1 Comment &darr;', 'responsive_meetups'), __('% Comments &darr;', 'responsive_meetups')); ?>
						</span>
					<?php endif; ?> 
				</div><!-- end of .post-meta -->

				<address>
					<?php echo get_post_meta( get_the_ID(), 'location_name', true ); ?><br/>
					<?php echo get_post_meta( get_the_ID(), 'address', true ); ?>
				</address>

				<div class="post-entry">
					<?php the_content(__('Read more &#8250;', 'responsive_meetups')); ?>
					<?php wp_link_pages(array('before' => '<div class="pagination">' . __( 'Pages:', 'responsive_meetups' ), 'after' => '</div>')); ?>
				</div><!-- end of .post-entry -->

				<div class="navigation">
					<div class="previous"><?php previous_post_link( '&#8249; %link' ); ?></div>
					<div class="next"><?php next_post_link( '%link &#8250;' ); ?></div>
				</div><!-- end of .navigation -->

				<div class="post-edit"><?php edit_post_link(__('Edit', 'responsive_meetups')); ?></div>             
			</div><!-- end of #post-<?php the_ID(); ?> -->

			<?php comments_template( '', true ); ?>

			<?php if (  $wp_query->max_num_pages > 1 ) : ?>
			<div class="navigation">
				<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive_meetups' ) ); ?></div>
				<div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive_meetups' ) ); ?></div>
			</div><!-- end of .navigation -->
			<?php endif; ?>


			<div id="widgets" class="grid col-300 fit">
				<div class="widget-wrapper">
					<?php
					$timestamp = get_post_meta( get_the_ID(), 'datetime_event', true );
					printf( '<p><time datetime="%1$s">%2$s<br/>%3$s</time></p>',
						esc_attr( date( 'c', $timestamp ) ),
						esc_html( date_i18n( get_option('date_format'), $timestamp ) ),
						esc_html( date_i18n( get_option('time_format'), $timestamp ) )
					);

					$count = Responsive_Meetups_RSVP::counts( get_the_ID() );

					if( $timestamp > time() ) { ?>
						<ul>
							<li>
								<?php
								Responsive_Meetups::rsvp_button();
								?>
							</li>
							<li>
								<?php printf( _n( '%s attending', '%s attending', $count->attend ), $count->attend, 'responsive_meetups' ); ?>
							</li>
							<?php if( $count->waitinglist ) { ?>
							<li>
								<?php printf( _n( '%s waiting', '%s waiting', $count->waitinglist ), $count->waitinglist, 'responsive_meetups' ); ?>
							</li>
							<?php } ?>
							<?php if( comments_open() ) { ?>
							<li>
								<a href="<?php comments_link(); ?>"><?php comments_number(); ?></a>
							</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div><!-- end of .widget-wrapper -->

				<?php
				$args = array(
					'post_type'      => 'rsvp',
					'post_status'    => 'attend',
					'post_parent'    => get_the_ID(),
					'posts_per_page' => -1,
					'order'          => 'asc',
					'fields'         => 'ids'
				);
				$rsvps = get_posts( $args );
				if( count( $rsvps ) > 0 ) {
				?>
				<div class="widget-wrapper rsvp-images">
					<h4><?php _e( 'Attendees', 'responsive_meetups' ); ?></h4>
					<ul>
						<?php foreach( $rsvps as $rsvp ) { ?>
						<li><?php echo get_avatar( get_post_meta( $rsvp, 'email', true ), 72, false, get_post_meta( $rsvp, 'name', true ) ); ?></li>

						<?php } ?>
					</ul>

					<?php
					$args = array(
						'post_type'      => 'rsvp',
						'post_status'    => 'waitinglist',
						'post_parent'    => get_the_ID(),
						'posts_per_page' => -1,
						'order'          => 'asc',
						'fields'         => 'ids'
					);
					$rsvps = get_posts( $args );

					if( count( $rsvps ) > 0 ) { ?>
					<h4><?php _e( 'Waitinglist', 'responsive_meetups' ); ?></h4>
					<ul>
						<?php foreach( $rsvps as $rsvp ) { ?>
						<li><?php echo get_avatar( get_post_meta( $rsvp, 'email', true ), 72, false, get_post_meta( $rsvp, 'name', true ) ); ?></li>

						<?php } ?>
					</ul>
					<?php }

					$args = array(
						'post_type'      => 'rsvp',
						'post_status'    => 'notattend',
						'post_parent'    => get_the_ID(),
						'posts_per_page' => -1,
						'order'          => 'asc',
						'fields'         => 'ids'
					);
					$rsvps = get_posts( $args );

					if( count( $rsvps ) > 0 ) { ?>
					<h4><?php _e( 'Not Attendees', 'responsive_meetups' ); ?></h4>
					<ul>
						<?php foreach( $rsvps as $rsvp ) { ?>
						<li><?php echo get_avatar( get_post_meta( $rsvp, 'email', true ), 72, false, get_post_meta( $rsvp, 'name', true ) ); ?></li>

						<?php } ?>
					</ul>
					<?php } ?>

				</div>
				<?php } ?>
			</div>

		<?php endwhile; ?> 

		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
		<div class="navigation">
			<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive_meetups' ) ); ?></div>
			<div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive_meetups' ) ); ?></div>
		</div><!-- end of .navigation -->
		<?php endif; ?>

		<?php else : ?>

		<h2><?php _e( 'No events are planned', 'responsive_meetups' ); ?></h2>

		<p><?php _e( 'Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive_meetups' ); ?></p>

		<?php endif; ?>

		</div><!-- end of #content-archive -->

<?php get_footer(); ?>