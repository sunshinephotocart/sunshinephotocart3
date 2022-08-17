<?php
// TODO: Remove, using main notices class for front and admin
class Sunshine_Admin_Notices {

    private $notices;

    public function __construct() {
        add_action( 'init', array( $this, 'set' ) );
        add_action( 'admin_notices', array( $this, 'show' ) );
    }

    public function set() {
		$this->notices = SPC()->session->get( 'admin_notices' );
	}

	public function add( $text, $type = 'success', $permanent = false ) {
		$this->notices[] = array(
			'text' => $text,
			'type' => $type,
			'permanent' => $permanent
		);
		SPC()->session->set( 'admin_notices', $this->notices );
	}

	public function get_notices_html() {
		if ( !empty( $this->notices ) ) {
			$html = '<div id="sunshine-notices">';
			foreach ( $this->notices as $notice ) {
				$html .= '<div class="notice sunshine-notice ' . $notice['type'] . '">' . $notice['text'] . '</div>';
			}
			$html .= '</div>';
			return $html;
		}
		return false;
	}

	public function delete( $key ) {
		if ( array_key_exists( $key, $this->notices ) ) {
			unset( $this->notices[ $key ] );
			SPC()->session->set( 'admin_notices', $this->notices );
		}
	}

	public function show() {
		if ( !empty( $this->notices ) ) {
			$notices = $this->get_notices_html();
            echo $notices;
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

}
