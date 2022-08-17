<?php

class SPC_Email {

    private $template;
    private $to;
    private $to_name;
    private $cc = array(); // Array of email addresses to CC
    private $from;
    private $from_name;
    private $subject;
    private $content;
    private $args;
    private $search_replace;

    public function __construct( $template, $to, $subject, $args = array() ) {

        $this->set_template( $template );
        $this->set_to( $to );
        $this->set_subject( $subject );
        $this->set_args( $args );

    }

    public function set_template( $template ) {
        $template_path = SUNSHINE_PHOTO_CART_PATH . 'templates/email/' . $template . '.php';
        if ( file_exists( $template_path ) ) {
            $this->template = $template;
        }
    }

    public function set_to( $to ) {
        if ( !empty( $to ) && is_email( $to ) ) {
            $this->to = $to;
        }
    }
    public function set_to_name( $to_name ) {
        if ( !empty( $to_name ) ) {
            $this->to_name = sanitize_text_field( $to_name );
        }
    }

    public function set_from( $from ) {
        if ( !empty( $from ) && is_email( $from ) ) {
            $this->from = $from;
        }
    }
    public function set_from_name( $from_name ) {
        if ( !empty( $from_name ) ) {
            $this->from_name = sanitize_text_field( $from_name );
        }
    }

    public function set_subject( $subject ) {
        if ( !empty( $subject ) ) {
            $this->subject = sanitize_text_field( $subject );
        }
    }

    public function set_content( $content ) {
        if ( !empty( $content ) ) {
            $this->content = wp_kses_post( $content );
        }
    }

    // Can pass array or single email address
    public function set_cc( $cc ) {
        if ( is_array( $cc ) ) {
            foreach ( $cc as $email ) {
                if ( is_email( $email ) ) {
                    $this->cc[] = $email;
                }
            }
        } elseif ( !empty( $cc ) && is_email( $cc ) ) {
            $this->cc[] = $cc;
        }
    }

    public function set_args( $args ) {
        $this->args = $args;
    }

    public function set_search_replace( $search_replace ) {
        $this->search_replace = $search_replace;
    }

    public function send() {

        if ( empty( $this->template ) ||empty( $this->to ) || empty( $this->subject ) ) {
            return false;
        }

        if ( empty( $this->from ) ) { // Set up default from email
            $this->from = SPC()->get_option( 'from_email' );
            if ( empty( $this->from ) ) { // Default to admin email if no from is set
                $this->from = get_option( 'admin_email' );
            }
        }

        if ( empty( $this->from_name ) ) { // Set up default from name
            $this->from_name = SPC()->get_option( 'from_name' );
            if ( empty( $this->from_name ) ) { // Default to blog name if no from name is set
                $this->from_name = get_option( 'blogname' );
            }
        }

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from . '>'
        );

        if ( !empty( $this->cc ) ) {
            foreach ( $this->cc as $cc_email ) {
                $headers[] = 'Cc: ' . $cc_email;
            }
        }

        // Get main content
        $this->content = $this->get_template_content( $this->template );

        // Get header/footer
        $header = $this->get_template_content( 'header', $this->args );
        $footer = $this->get_template_content( 'footer', $this->args );
        $this->content = $header . $this->content . $footer;

        // Search/replace
        $search = array( '[sitename]', '[siteurl]' );
        $replace = array( get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
        if ( !empty( $this->search_replace ) ) {
            foreach ( $this->search_replace as $key => $value ) {
                $search[] = '[' . $key . ']';
                $replace[] = $value;
            }
        }
        $this->subject = str_replace( $search, $replace, $this->subject );
        $this->content = str_replace( $search, $replace, $this->content );

        // Run through emogrifier
        if ( !class_exists( 'Sunshine_Emogrifier' ) ) {
            include_once( SUNSHINE_PHOTO_CART_PATH . 'includes/class-emogrifier.php' );
        }
        $css = $this->get_template_content( 'style' );
        $emogrifier = new Sunshine_Emogrifier( $this->content, $css );
        $this->content = $emogrifier->emogrify();

        // Send
        //sunshine_log( $this );
        return wp_mail( $this->to, $this->subject, $this->content, $headers );

    }

    public function get_template_content( $template ) {

        if ( file_exists( TEMPLATEPATH . '/sunshine/templates/email/' . $template . '.php' ) ) {
            $template_path = TEMPLATEPATH . '/sunshine/templates/email/' . $template . '.php';
        } else {
            $template_path = SUNSHINE_PHOTO_CART_PATH . 'templates/email/' . $template . '.php';
        }
        $args['template'] = $this->template; // Throw this in so any hooks in the template can know which template this is for
        ob_start();
            extract( $this->args );
            include( $template_path );
            $template_content = ob_get_contents();
        ob_end_clean();
        return $template_content;

    }

}
