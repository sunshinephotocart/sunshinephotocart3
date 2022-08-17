<?php
class SPC_Order_Item extends SPC_Cart_Item {

    public function get_image_name() {
        $image_name = '';
        if ( is_a( $this->object, 'SPC_Image' ) && $this->object->exists() ) {
            $url = ( is_admin() ) ? admin_url( 'post.php?action=edit&post=' . $this->object->gallery->get_id() ): $this->object->gallery->get_permalink();
            $image_name = $this->object->get_name() . ' &mdash; <a href="' . $url . '">' . $this->object->gallery->get_name() . '</a>';
        } elseif ( !empty( $this->item['image_name'] ) ) {
            $image_name = $this->item['image_name'];
            if ( !empty( $this->item['gallery_name'] ) ) {
                $image_name .= ' &mdash; ' . $this->item['gallery_name'];
            }
        }
        //$image_name = apply_filters( 'sunshine_order_item_image_name', $image_name, $this );
        return $image_name;
    }

    function get_gallery_id() {
        if ( $this->item['gallery_id'] ) {
            return $this->item['gallery_id'];
        }
    }

    function get_gallery_name() {
        if ( $this->item['gallery_name'] ) {
            return $this->item['gallery_name'];
        }
    }

}
