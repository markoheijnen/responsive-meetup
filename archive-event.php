<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

/**
 * Archive Template
 *
 *
 * @file           archive-event.php
 * @package        Responsive 
 * @author         Marko Heijnen 
 * @copyright      2003 - 2012 Marko Heijnen
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive-meetup/archive.php
 * @link           http://codex.wordpress.org/Theme_Development#Archive_.28archive.php.29
 * @since          available since Release 1.0
 */

?>
<?php get_header(); ?>

		<div id="content-archive">

		<?php $options = get_option('responsive_theme_options'); ?>
		<?php if ( $options['breadcrumb'] == 0 ): ?>
		<?php echo responsive_breadcrumb_lists(); ?>
		<?php endif; ?>

			<h1><?php _e( 'Events', 'responsive_meetups' ); ?></h1>

			<nav>
				<?php Responsive_Meetups::event_menu(); ?>
			</nav>

		<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div id="post-<?php the_ID(); ?>" <?php post_class('grid col-620'); ?>>
				<h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__( 'Permanent Link to %s', 'responsive' ), the_title_attribute( 'echo=0' )); ?>"><?php the_title(); ?></a></h2>

				<address>
					<?php echo get_post_meta( get_the_ID(), 'location_name', true ); ?><br/>
					<?php echo get_post_meta( get_the_ID(), 'address', true ); ?>
				</address>

				<div class="post-entry">
					<?php if ( has_post_thumbnail()) : ?>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
					<?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
						</a>
					<?php endif; ?>
					<?php the_excerpt(); ?>
					<?php wp_link_pages(array('before' => '<div class="pagination">' . __( 'Pages:', 'responsive' ), 'after' => '</div>')); ?>
				</div><!-- end of .post-entry -->

				<div class="post-data">
					<?php get_the_term_list( null, 'event_type', __('Type:', 'responsive_meetups'), ', ', '' ); ?>
				</div><!-- end of .post-data -->             

			<div class="post-edit"><?php edit_post_link(__('Edit', 'responsive')); ?></div>             
			</div><!-- end of #post-<?php the_ID(); ?> -->

			<?php comments_template( '', true ); ?>

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
			</div>

		<?php endwhile; ?> 

		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
		<div class="navigation">
			<div class="previous"><?php next_posts_link( __( '&#8249; Older posts', 'responsive' ) ); ?></div>
			<div class="next"><?php previous_posts_link( __( 'Newer posts &#8250;', 'responsive' ) ); ?></div>
		</div><!-- end of .navigation -->
		<?php endif; ?>

		<?php else : ?>

		<h2><?php _e( 'No events are planned', 'responsive_meetups' ); ?></h2>

		<p><?php _e( 'Don&#39;t panic, we&#39;ll get through this together. Let&#39;s explore our options here.', 'responsive' ); ?></p>

		<?php endif; ?>

		</div><!-- end of #content-archive -->

<?php get_footer(); ?>