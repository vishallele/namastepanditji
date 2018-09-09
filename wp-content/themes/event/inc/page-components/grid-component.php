<?php 
    $grids_data = CFS()->get('grids');

    //echo "<pre>"; print_r($grids_data); exit;

    if( count($grids_data) > 0 ) {
        
?>
<div class="our-feature-box rm-top-brdr">
    <div class="container">
        <div class="column">
        <?php 
            foreach( $grids_data as $grid ) {

                $grid_title = $grid['grid_title'];
                $grid_img = $grid['grid_image'];
                $grid_img = wp_get_attachment_image_src($grid['grid_image'],'medium');
                $grid_content = $grid['grid_content'];
                $grid_read_more_link = $grid['grid_read_more_link'];
        ?>
                <div class="three-column">
                    <div class="feature-content">
                        <a class="feature-img" href="#"><img src=<?php echo $grid_img[0] ?> /></a>
                        <br/>
                        <article>
                            <h3 class="feature-title"><a href="#" title="" rel="bookmark"><?php echo $grid_title; ?></a></h3>
                            <p><?php echo $grid_content ?></p>
                        </article>
                        <a title="<?php echo $grid_read_more_link['text']; ?>" href="<?php echo $grid_read_more_link['url']; ?>" target="<?php echo $grid_read_more_link['target']; ?>" class="more-link"><?php echo $grid_read_more_link['text']; ?></a>
                    </div> <!-- end .feature-content -->
                </div><!-- end .three-column -->

        <?php } ?>

        </div><!-- .end column-->
        
    </div><!-- .container -->
</div>


<?php } ?>