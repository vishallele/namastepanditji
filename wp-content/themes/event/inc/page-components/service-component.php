<?php 
    $grids_data = CFS()->get('services');

    if( count($grids_data) > 0 ) {
        
?>
<div class="our-feature-box rm-top-brdr">
    <h2 class="widget-title"><span>Services</span></h2>
    <div class="container clearfix">
        <div class="column clearfix">
        <?php 
            foreach( $grids_data as $grid ) {

                $grid_title = $grid['service_title'];
                $grid_content = $grid['service_content'];
                $service_button_url = $grid['service_button_url'];
        ?>
                <div class="two-column">
                    <div class="feature-content">
                        <article>
                            <h3 class="feature-title"><a href="#" title="" rel="bookmark"><?php echo $grid_title; ?></a></h3>
                            <p><?php echo $grid_content ?></p>
                        </article>
                        <?php if( $service_button_url !== '' ) { ?>
                            <a title="<?php echo $service_button_url['text']; ?>" href="<?php echo $service_button_url['url']; ?>" target="<?php echo $service_button_url['target']; ?>" class="more-link"><?php echo $service_button_url['text']; ?></a>
                        <?php } ?>        
                    </div> <!-- end .feature-content -->
                </div><!-- end .three-column -->

        <?php } ?>

        </div><!-- .end column-->
        
    </div><!-- .container -->
</div>


<?php } ?>