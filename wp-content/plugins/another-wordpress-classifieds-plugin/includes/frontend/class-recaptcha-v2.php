<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Integration with reCAPTCHA v3.
 */
class AWPCP_ReCAPTCHAv2 implements AWPCP_ReCAPTCHADelegate {

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $request ) {
        $this->request = $request;
    }

    /**
     * @since 4.0.0
     */
    public function enqueue_scripts( $site_key ) {
        wp_enqueue_script(
            'awpcp-recaptcha',
            'https://www.google.com/recaptcha/api.js?onload=AWPCPreCAPTCHAonLoadCallback&render=explicit',
            array( 'awpcp' ),
            'v2',
            true
        );
    }

    /**
     * @since 4.0.0
     */
    public function get_recaptcha_html( $site_key ) {
        return '<div class="g-recaptcha awpcp-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
    }

    public function get_recaptcha_response() {
        return $this->request->post( 'g-recaptcha-response' );
    }

    /**
     * @since 4.0.0
     */
    public function get_verification_error_message( $error_message ) {
        $message = __( 'There was an error trying to verify the reCAPTCHA answer. <reCAPTCHA-error>', 'another-wordpress-classifieds-plugin' );
        $message = str_replace( '<reCAPTCHA-error>', $error_message, $message );

        return $message;
    }

    /**
     * @since 4.0.0
     */
    public function process_error_codes( array $error_codes ) {
        $errors = array();

        foreach ( $error_codes as $error_code ) {
            switch ( $error_code ) {
                case 'missing-input-secret':
                    $errors[] = _x( 'The secret parameter is missing', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'invalid-input-secret':
                    $errors[] = _x( 'The secret parameter is invalid or malformed.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'missing-input-response':
                    $errors[] = _x( 'The response parameter is missing.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'invalid-input-response':
                default:
                    $errors[] = _x( 'The response parameter is invalid or malformed.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
            }
        }

        return implode( ' ', $errors );
    }

    /**
     * @since 4.0.0
     */
    public function handle_successful_response( $response ) {
        return true;
    }
}
