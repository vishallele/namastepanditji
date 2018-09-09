<?php 
    $slides_data = CFS()->get('slides');
    if( count($slides_data) > 0 ) {
        
?>

<div class="main-slider"> 
    <div class="layer-slider"><!-- end .slides -->
        <ul class="slides">
            <?php 
                foreach( $slides_data as $slider ) {
                    $title = get_the_title($slider);
                    $tag_line = CFS()->get('slides_tag_line', $slider );
                    //$content = get_the_excerpt($slider);
                    //$buttons = CFS()->get('slides_button', $slider );
                    $image_url = get_the_post_thumbnail_url($slider,'full');
            ?>
            <li class="clone">
                <div class="image-slider" title="<?php echo $title; ?>" style="background-image:url('<?php echo $image_url; ?>')"></div>
            </li>

            <?php } ?>
        </ul>
    </div> <!-- end .layer-slider -->
</div>

<?php } ?>