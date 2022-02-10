<?php
/**
 * @package AWPCP\Media
 */

/**
 * Constructor function for AWPCP_UploadListingMediaAjaxHandler.
 */
function awpcp_upload_listing_media_ajax_handler() {
    return new AWPCP_UploadListingMediaAjaxHandler(
        awpcp_attachment_properties(),
        awpcp_listings_collection(),
        awpcp_file_uploader(),
        awpcp_new_media_manager(),
        awpcp_request(),
        awpcp_ajax_response(),
        awpcp()->container['ListingInformationMetabox']
    );
}

class AWPCP_UploadListingMediaAjaxHandler extends AWPCP_AjaxHandler {

    private $attachment_properties;
    private $listings;
    private $uploader;
    private $media_manager;
    private $request;
    private $metabox;

    public function __construct( $attachment_properties, $listings, $uploader, $media_manager, $request, $response, $metabox ) {
        parent::__construct( $response );

        $this->attachment_properties = $attachment_properties;
        $this->listings              = $listings;
        $this->media_manager         = $media_manager;
        $this->uploader              = $uploader;
        $this->request               = $request;
        $this->metabox               = $metabox;
    }

    public function ajax() {
        try {
            $this->try_to_process_uploaded_file();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function try_to_process_uploaded_file() {
        $listing = $this->listings->get( $this->request->post( 'listing' ) );

        if ( ! $this->is_user_authorized_to_upload_media_to_listing( $listing ) ) {
            throw new AWPCP_Exception( __( 'You are not authorized to upload files.', 'another-wordpress-classifieds-plugin' ) );
        }

        return $this->process_uploaded_file( $listing );
    }

    private function is_user_authorized_to_upload_media_to_listing( $listing ) {
        if ( ! wp_verify_nonce( $this->request->post( 'nonce' ), 'awpcp-upload-media-for-listing-' . $listing->ID ) ) {
            return false;
        }

        return true;
    }

    private function process_uploaded_file( $listing ) {
        $uploaded_file = $this->uploader->get_uploaded_file();

        if ( $uploaded_file->is_complete ) {
            $file = $this->media_manager->add_file( $listing, $uploaded_file );
            // @phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
            do_action( 'awpcp-media-uploaded', $file, $listing );

            return $this->success(
                array(
                    'file' => array(
                        'id'           => $file->ID,
                        'name'         => $file->post_title, // TODO: where is the name of the file stored?
                        'listingId'    => $file->post_parent,
                        'enabled'      => $this->attachment_properties->is_enabled( $file ),
                        'status'       => $this->attachment_properties->get_allowed_status( $file ),
                        'mimeType'     => $file->post_mime_type,
                        'isPrimary'    => $this->attachment_properties->is_featured( $file ),
                        'thumbnailUrl' => $this->attachment_properties->get_image_url( $file, 'thumbnail' ),
                        'iconUrl'      => $this->attachment_properties->get_icon_url( $file ),
                        'url'          => $this->attachment_properties->get_image_url( $file, 'large' ),
                    ),
                )
            );
        }

        return $this->success();

    }
}
