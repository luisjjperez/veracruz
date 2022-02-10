<?php
/**
 * @package AWPCP\Listings
 */

// phpcs:disable

/**
 */
class AWPCP_ListingUploadLimits {

    private $attachments;
    private $file_types;
    private $listing_renderer;
    private $settings;

    public function __construct( $attachments, $file_types, $listing_renderer, $settings ) {
        $this->attachments = $attachments;
        $this->file_types = $file_types;
        $this->listing_renderer = $listing_renderer;
        $this->settings = $settings;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_upload_limits_for_payment_term( $payment_term ) {
        if ( awpcp_are_images_allowed() ) {
            $upload_limits = array( 'images' => $this->get_upload_limits_for_images_in_payment_term( $payment_term ) );
        } else {
            $upload_limits = array();
        }

        return apply_filters( 'awpcp-upload-limits-for-payment-term', $upload_limits, $payment_term );
    }

    private function get_upload_limits_for_images_in_payment_term( $payment_term ) {
        return $this->build_upload_limits_for_images( $payment_term->images );
    }

    private function build_upload_limits_for_images( $allowed_file_count, $uploaded_file_count = 0 ) {
        $mime_types = $this->file_types->get_allowed_file_mime_types_in_group( 'image' );
        $extensions = $this->file_types->get_allowed_file_extesions_in_group( 'image' );

        return array(
            'mime_types' => $mime_types,
            'extensions' => $extensions,
            'allowed_file_count' => $allowed_file_count,
            'uploaded_file_count' => $uploaded_file_count,
            'min_file_size' => $this->settings->get_option( 'minimagesize' ),
            'max_file_size' => $this->settings->get_option( 'maximagesize' ),
            'min_image_width' => $this->settings->get_option( 'imgminwidth' ),
            'min_image_height' => $this->settings->get_option( 'imgminheight' ),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_upload_limits_for_free_board() {
        if ( awpcp_are_images_allowed() ) {
            $upload_limits = array( 'images' => $this->get_upload_limits_for_images_in_free_board() );
        } else {
            $upload_limits = array();
        }

        return apply_filters( 'awpcp-upload-limits-for-free-board', $upload_limits );
    }

    private function get_upload_limits_for_images_in_free_board() {
        return $this->build_upload_limits_for_images( $this->settings->get_option( 'imagesallowedfree', 0 ) );
    }

    public function can_add_file_to_listing( $listing, $file ) {
        $limits = $this->get_listing_upload_limits( $listing );

        $can_add_file = false;
        foreach ( $limits as $type_limits ) {
            if ( in_array( $file->get_mime_type(), $type_limits['mime_types'] ) ) {
                $can_add_file = $type_limits['allowed_file_count'] > $type_limits['uploaded_file_count'];
                break;
            }
        }

        // TODO: do we really need this filter?
        return apply_filters( 'awpcp-can-add-file-to-listing', $can_add_file, $listing, $limits );
    }

    public function are_uploads_allowed_for_listing( $listing ) {
        $allowed_files = $this->get_listing_upload_limits( $listing );

        foreach ( $allowed_files as $limits ) {
            if ( $limits['uploaded_file_count'] || $limits['allowed_file_count'] ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_listing_upload_limits( $listing ) {
        $payment_term = $this->listing_renderer->get_payment_term( $listing );

        if ( awpcp_are_images_allowed() ) {
            $upload_limits = array( 'images' => $this->get_listing_upload_limits_for_images( $listing, $payment_term ) );
        } else {
            $upload_limits = array();
        }

        return apply_filters( 'awpcp-listing-upload-limits', $upload_limits, $listing, $payment_term );
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function get_listing_upload_limits_for_images( $listing, $payment_term ) {
        if ( is_object( $payment_term ) ) {
            $upload_limits = $this->get_upload_limits_for_images_in_payment_term( $payment_term );
        } else {
            $upload_limits = $this->get_upload_limits_for_images_in_free_board();
        }

        $upload_limits['uploaded_file_count'] = $this->attachments->count_attachments_of_type(
            'image', array( 'post_parent' => $listing->ID )
        );

        return $upload_limits;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_listing_upload_limits_by_file_type( $listing, $file_type ) {
        $upload_limits = $this->get_listing_upload_limits( $listing );

        if ( isset( $upload_limits[ $file_type ] ) ) {
            return $upload_limits[ $file_type ];
        } else {
            return array(
                'mime_types' => array(),
                'extensions' => array(),
                'allowed_file_count' => 0,
                'uploaded_file_count' => 0,
                'min_file_size' => 0,
                'max_file_size' => 0,
            );
        }
    }
}
