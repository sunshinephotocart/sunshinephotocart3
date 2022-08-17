<div class="<?php $gallery->classes(); ?>" id="sunshine--gallery-<?php echo $gallery->get_id(); ?>">

    <?php do_action( 'sunshine_before_loop_gallery_item' ); ?>

    <a href="<?php echo $gallery->get_permalink(); ?>"><?php $gallery->featured_image(); ?></a>
    <h2><a href="<?php echo $gallery->get_permalink(); ?>"><?php echo $gallery->get_name(); ?></a></h2>

    <?php do_action( 'sunshine_after_loop_gallery_item' ); ?>

</div>
