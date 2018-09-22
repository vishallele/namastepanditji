<?php
/**
 * The template for displaying the footer.
 *
 * @package Theme Freesia
 * @subpackage Event
 * @since Event 1.0
 */
$event_settings = event_get_theme_options();
?>
	</div> <!-- end .container -->
</div> <!-- end #content -->
<!-- Footer Start ============================================= -->
<footer id="colophon" class="site-footer clearfix">
<?php $footer_column = $event_settings['event_footer_column_section']; ?>
<div class="site-info" <?php if($event_settings['event-img-upload-footer-image'] !=''){?>style="background-image:url('<?php echo esc_url($event_settings['event-img-upload-footer-image']); ?>');" <?php } ?>>
	<div class="container">

<div class="social-links clearfix">
	<ul><li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5"><a href="http://facebook.com" target="_blank"><span class="screen-reader-text">Facebook</span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-6"><a href="http://twitter.com" target="_blank"><span class="screen-reader-text">Twitter</span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-328"><a href="http://www.instagram.com" target="_blank"><span class="screen-reader-text">Instagram</span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-373"><a href="http://youtube.com" target="_blank"><span class="screen-reader-text">Youtube</span></a></li>
</ul>	</div>

	<?php
		/////=======================================================================	
		if ( is_active_sidebar( 'event_footer_options' ) ) :
		dynamic_sidebar( 'event_footer_options' );
		else:
			echo '<div class="copyright">' .'&copy; ' . date('Y') .' '; ?>
			<a title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" target="_blank" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo get_bloginfo( 'name', 'display' ); ?></a>
					</div>
		<?php endif;?>
			<div style="clear:both;"></div>
		</div> <!-- end .container -->
	</div> <!-- end .site-info -->
	<?php
		$disable_scroll = $event_settings['event_scroll'];
		if($disable_scroll == 0):?>
	<a class="go-to-top">
		<span class="icon-bg"></span>
		<span class="back-to-top-text"><?php esc_html_e('Top','event');?></span>
		<i class="fa fa-angle-up back-to-top-icon"></i>
	</a>
	<?php endif; ?>
</footer> <!-- end #colophon -->
</div><!-- end #page -->
<?php wp_footer(); ?>
</body>
</html>