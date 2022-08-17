<?php
defined( 'ABSPATH' ) || exit;

get_header( 'sunshine' );

do_action( 'sunshine_before_content' );

do_action( 'sunshine_single_gallery' );

do_action( 'sunshine_after_content' );

get_footer( 'sunshine' );
