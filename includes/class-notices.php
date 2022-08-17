<?php
// TODO: Admin notices: Ability to close out a notice and not show any more
class SPC_Notices {

    private $notices;
    private $admin_notices;

    public function __construct() {
        add_action( 'init', array( $this, 'set' ) );
        add_action( 'sunshine_before_content', array( $this, 'show' ), 3 );

        add_action( 'admin_init', array( $this, 'set_admin' ) );
        add_action( 'admin_notices', array( $this, 'show_admin' ) );
    }

    public function set() {
		$this->notices = SPC()->session->get( 'notices' );
	}

	public function add( $text, $type = 'success', $permanent = false ) {
		$this->notices[] = array(
            'text' => $text,
			'type' => $type,
			'permanent' => $permanent
		);
		SPC()->session->set( 'notices', $this->notices );
	}

	public function get_notices_html() {
		if ( !empty( $this->notices ) ) {
			$html = '<div id="sunshine-notices">';
			foreach ( $this->notices as $notice ) {
				$html .= '<div class="sunshine-notice ' . $notice['type'] . '">' . $notice['text'] . '</div>';
			}
			$html .= '</div>';
			return $html;
		}
		return false;
	}

	public function delete( $key ) {
		if ( !empty( $this->notices ) && array_key_exists( $key, $this->notices ) ) {
			unset( $this->notices[ $key ] );
			SPC()->session->set( 'notices', $this->notices );
		}
	}

	public function show() {
		if ( !empty( $this->notices ) ) {
			$notices = $this->get_notices_html();
            echo wp_kses_post( $notices );
            $this->clear();
		}
	}

    public function clear() {
		foreach ( $this->notices as $key => $notice ) {
			if ( !$notice['permanent'] ) {
				$this->delete( $key );
			}
		}
	}

    public function set_admin() {
        $this->admin_notices = SPC()->session->get( 'admin_notices' );
    }

    function add_admin( $key, $text, $type = 'success', $permanent = false ) {
        $this->admin_notices[ $key ] = array(
            'text' => $text,
            'type' => $type,
            'permanent' => $permanent
        );
        SPC()->session->set( 'admin_notices', $this->admin_notices );
    }

    public function get_admin_notices_html() {
        if ( !empty( $this->admin_notices ) ) {
            $html = '<div id="sunshine-notices">';
            foreach ( $this->admin_notices as $notice ) {
                $html .= '<div class="notice is-dismissible sunshine-notice notice-' . $notice['type'] . '"><p>' . $notice['text'] . '</p></div>';
            }
            $html .= '</div>';
            return $html;
        }
        return false;
    }

    public function delete_admin( $key ) {
        if ( !empty( $this->admin_notices ) && array_key_exists( $key, $this->admin_notices ) ) {
            unset( $this->admin_notices[ $key ] );
            SPC()->session->set( 'admin_notices', $this->admin_notices );
        }
    }

    public function show_admin() {
        if ( !empty( $this->admin_notices ) ) {
            $notices = $this->get_admin_notices_html();
            echo $notices;
            $this->clear_admin();
        }
    }

	public function clear_admin() {
		foreach ( $this->admin_notices as $key => $notice ) {
			if ( !$notice['permanent'] ) {
				$this->delete_admin( $key );
			}
		}
	}

}
