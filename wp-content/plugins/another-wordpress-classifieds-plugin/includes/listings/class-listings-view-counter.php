<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Increments the ad view counter.
 */
class AWPCP_ListingsViewCounter extends AWPCP_AjaxHandler {

    private $response;
    private $request;
    private $listings_logic;

    public function __construct( $response, $request, $listings_login ) {
        parent::__construct( $response );
        $this->request        = $request;
        $this->response       = $response;
        $this->listings_logic = $listings_login;
    }

    public function ajax() {
        if ( ! $this->request->is_bot() ) {
            $listing_id = $this->request->post( 'listing_id' );
            $listing    = get_post( $listing_id );
            $this->listings_logic->increase_visits_count( $listing );
            $placeholder = awpcp_do_placeholder_legacy_views( $listing, 'awpcpadviews' );
            $this->success( [ 'placeholder' => $placeholder ] );
        }
    }
}
