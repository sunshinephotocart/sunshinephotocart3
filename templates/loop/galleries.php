<?php
if ( empty( $galleries ) ) {
    $galleries = sunshine_get_galleries();
}

if ( !empty( $galleries ) ) {

    foreach ( $galleries as $gallery ) {
        sunshine_get_template( 'loop/single-gallery', array( 'gallery' => $gallery ) );
    }

} else {

    echo '<p>' . apply_filters( 'sunshine_no_galleries', __( 'Sorry, no galleries have been setup yet', 'sunshine-photo-cart' ) ) . '</p>';

}
