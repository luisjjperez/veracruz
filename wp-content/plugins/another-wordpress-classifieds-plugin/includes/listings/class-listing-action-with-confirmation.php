<?php

abstract class AWPCP_ListingActionWithConfirmation extends AWPCP_ListingAction {

    abstract public function get_confirmation_message();

    public function get_cancel_button_label() {
        return __( 'Cancel', 'another-wordpress-classifieds-plugin' );
    }

    protected function get_template() {
        return AWPCP_DIR . '/templates/frontend/listing-action-with-confirmation.tpl.php';
    }
}
