<?php
/**
 * Displays the header content
 *
 * @package Theme Freesia
 * @subpackage Event
 * @since Event 1.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php
$event_settings = event_get_theme_options(); ?>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="page" class="site">
<!-- Masthead ============================================= -->
<header id="masthead" class="site-header" role="banner">
<?php 
if($event_settings['event_header_image_display'] =='top'){
	do_action('event_header_image');
}?>
		<div class="top-header">
			<div class="container clearfix">
				<?php
				if( is_active_sidebar( 'event_header_info' )) {
					dynamic_sidebar( 'event_header_info' );
				}
				if($event_settings['event_top_social_icons'] == 0):
					echo '<div class="header-social-block">';
						do_action('event_social_links');
					echo '</div>'.'<!-- end .header-social-block -->';
				endif; ?>
			</div> <!-- end .container -->
		</div> <!-- end .top-header -->
		<?php 

		if($event_settings['event_header_image_display'] =='bottom'){
			do_action('event_header_image');
		}?>
		<!-- Main Header============================================= -->
				<div id="sticky-header" class="clearfix">
					<div class="container clearfix">
					<?php
						do_action('event_site_branding'); ?>	
						<!-- Main Nav ============================================= -->
						<?php
						if (has_nav_menu('primary')) { ?>
						<?php $args = array(
							'theme_location' => 'primary',
							'container'      => '',
							'items_wrap'     => '<ul id="primary-menu" class="menu nav-menu">%3$s</ul>',
							); ?>
						<nav id="site-navigation" class="main-navigation clearfix">
							<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
								<span class="line-one"></span>
								<span class="line-two"></span>
								<span class="line-three"></span>
							</button><!-- end .menu-toggle -->
							<?php wp_nav_menu($args);//extract the content from apperance-> nav menu ?>
						</nav> <!-- end #site-navigation -->
						<?php } else {// extract the content from page menu only ?>
						<nav id="site-navigation" class="main-navigation clearfix">
							<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
								<span class="line-one"></span>
								<span class="line-two"></span>
								<span class="line-three"></span>
							</button><!-- end .menu-toggle -->
							<?php	wp_page_menu(array('menu_class' => 'menu', 'items_wrap'     => '<ul id="primary-menu" class="menu nav-menu">%3$s</ul>')); ?>
						</nav> <!-- end #site-navigation -->
						<?php } ?>
					</div> <!-- end .container -->
				</div> <!-- end #sticky-header -->

		<!--Home Page Slider---->		
			<?php
				if( is_front_page()) {
					get_template_part('inc/page-components/slider','component');
				}
			?>
		<!--End Home Page Slider---->

</header> <!-- end #masthead -->
<!-- Main Page Start ============================================= -->
<div id="content">
<?php if(!is_page_template('page-templates/event-corporate.php') ){ ?>
	<div class="container clearfix">
	<?php 
}
if(!(is_front_page() || is_page_template('page-templates/event-corporate.php') ) ){
	$custom_page_title = apply_filters( 'event_filter_title', '' ); ?>
		<div class="page-header">
		<?php if(is_home() ){ ?>
			<h2 class="page-title"><?php  echo event_title(); ?></h2>
		<?php }else{ ?>
			<h1 class="page-title"><?php  echo event_title(); ?></h1>
		<?php } ?>
			<!-- .page-title -->
			<?php event_breadcrumb(); ?>
			<!-- .breadcrumb -->
		</div>
		<!-- .page-header -->
<?php } ?>