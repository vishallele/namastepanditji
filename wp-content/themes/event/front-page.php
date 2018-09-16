<?php
/**
 * The template for displaying all pages.
 *
 * @package Theme Freesia
 * @subpackage Event
 * @since Event 1.0
 */

get_header(); ?>

    <div id="main" class="clearfix">
        <?php

            get_template_part('inc/page-components/grid','component');

            get_template_part('inc/page-components/service','component');

        ?>
	</div> <!-- #main -->



<?php
    get_footer();
