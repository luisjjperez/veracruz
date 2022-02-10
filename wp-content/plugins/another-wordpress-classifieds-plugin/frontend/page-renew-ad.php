<?php
/**
 * @package AWPCP\Frontend
 */

// phpcs:disable

require_once(AWPCP_DIR . '/frontend/page-place-ad.php');

function awpcp_renew_listing_page() {
    return new AWPCP_RenewAdPage(
        'awpcp-renew-ad',
        null,
        awpcp_attachments_collection(),
        awpcp_listing_upload_limits(),
        awpcp_listing_authorization(),
        awpcp_listing_renderer(),
        awpcp_listings_api(),
        awpcp_listings_collection(),
        awpcp_payments_api(),
        awpcp_template_renderer(),
        awpcp_wordpress(),
        awpcp_request()
    );
}

/**
 * @since  2.1.4
 */
class AWPCP_RenewAdPage extends AWPCP_Place_Ad_Page {

    protected $context = 'renew-ad';

    public $messages = array();

    protected function get_panel_url() {
        if ( awpcp_current_user_is_moderator() ) {
            return awpcp_get_admin_listings_url();
		}
        return awpcp_get_user_panel_url();
    }

    public function get_ad() {
        if (!isset($this->ad))
            $this->ad = null;

        if ( ! is_null( $this->ad ) ) {
            return $this->ad;
        }

        try {
            $this->ad = $this->listings->get( $this->request->param( is_admin() ? 'id' : 'ad_id' ) );
        } catch ( AWPCP_Exception $e ) {
            $this->ad = null;
        }

        return $this->ad;
    }

    public function verify_renew_ad_hash($ad) {
        return awpcp_verify_renew_ad_hash( $ad->ID, $this->request->param( 'awpcprah' ) );
    }

    protected function _dispatch($default=null) {
        $action = $this->get_current_action( $default );
        $ad = $this->get_ad();

		if ( is_null( $ad ) ) {
			$content = do_shortcode( '[AWPCPUSERLISTINGS] ' );
			return $this->render('content', $content );
		}

		if ( ! in_array( $action, array( 'payment-completed', 'finish', true ) ) && ! $this->listing_renderer->is_about_to_expire( $ad ) && ! $this->listing_renderer->has_expired( $ad ) ) {
			$message = __( 'That Ad doesn\'t need to be renewed.', 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
		}

		if ( ! $this->verify_renew_ad_hash( $ad ) ) {
            $message = __("There was an error trying to renew your Ad. The URL is not valid. Please contact the Administrator of this site for further assistance.", 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
        }

        $transaction = $this->get_transaction();

        if (!is_null($transaction) && $transaction->get('context') != $this->context) {
            $page_name = awpcp_get_page_name('renew-ad-page-name');
            $page_url = awpcp_get_renew_ad_url( $ad->ID );
            $message = __( 'You are trying to post an Ad using a transaction created for a different purpose. Please go back to the <a href="%s">%s</a> page.<br>If you think this is an error please contact the administrator and provide the following transaction ID: %s', 'another-wordpress-classifieds-plugin');
            $message = sprintf($message, $page_url, $page_name, $transaction->id);
            return $this->render('content', awpcp_print_error($message));
        }

        if (!is_null($transaction) && $transaction->is_payment_completed()) {
            if ( ! ( $transaction->was_payment_successful() || $transaction->payment_is_not_verified() ) ) {
                $message = __( 'You can\'t renew your Ad at this time because the payment associated with this transaction failed (see reasons below).', 'another-wordpress-classifieds-plugin');
                $message = awpcp_print_message($message);
                $message = $message . $this->payments->render_transaction_errors( $transaction );
                return $this->render('content', $message);
            }

            if ( $transaction->payment_is_not_verified() ) {
                $action = 'payment-completed';
            }

            $forbidden = in_array($action, array('order', 'checkout'));
            if ($forbidden) {
                $action = 'payment-completed';
            }
        }

        if (!is_null($transaction) && $transaction->is_completed()) {
            $action = 'finish';
        }

        $implementation = $this->get_renew_ad_page_implementation($ad);

        if (is_null($implementation)) {
            $message = __("The Ad was posted under a Payment Term that no longer exists or is disabled. The Ad can't be renewed.", 'another-wordpress-classifieds-plugin');
            $content = '<p>' . $this->get_return_link($ad) . '</p>';
            return $this->render('content', awpcp_print_error($message) . $content);
        }

        switch ($action) {
            case 'order':
                return $implementation->order_step();
            case 'checkout':
                return $implementation->checkout_step();
            case 'payment-completed':
                return $implementation->payment_completed_step();
            case 'finish':
                return $implementation->finish_step();
            default:
                return $implementation->order_step();
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function get_renew_ad_page_implementation($ad) {
        $term = $this->listing_renderer->get_payment_term( $ad );

        // the payment term doesn't exists or is not available
        if (is_null($term)) return null;

        // we handle the default implementation
        if ($term->type === AWPCP_FeeType::TYPE) {
            return awpcp_renew_listing_page_implementation( $this );
        } else {
            return apply_filters('awpcp-get-renew-ad-page-implementation', null, $term->type, $this);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function get_return_link($ad) {
        if (is_admin()) {
            return sprintf('<a href="%1$s">%2$s</a>', $this->get_panel_url(), __( 'Return to Listings', 'another-wordpress-classifieds-plugin'));
        } else {
            $title = __( 'You can see your Ad here', 'another-wordpress-classifieds-plugin' );
            return sprintf( '<a href="%1$s">%2$s</a>', url_showad( $ad->ID ), $title );
        }
    }

    public function render_finish_step($ad) {
        $response = __( "The Ad has been successfully renewed. New expiration date is %s.", 'another-wordpress-classifieds-plugin' );
        $response = sprintf( $response, $this->listing_renderer->get_end_date( $ad ) );
        $response = sprintf( "%s %s.", $response, $this->get_return_link( $ad ) );

        $params = compact('response');
        $template = AWPCP_DIR . '/frontend/templates/page-renew-ad-finish-step.tpl.php';

        return $this->render($template, $params);
    }
}

function awpcp_renew_listing_page_implementation( $page ) {
    return new AWPCP_RenewAdPageImplementation(
        $page,
        awpcp_listings_api(),
        awpcp_listing_renderer(),
        awpcp_payments_api(),
        awpcp_request()
    );
}

class AWPCP_RenewAdPageImplementation {

    public $messages = array();

    private $page;
    private $listing_renderer;
    private $payments;
    private $request;

    public function __construct( $page, $listings_logic, $listing_renderer, $payments, $request ) {
        $this->page = $page;
        $this->listings_logic = $listings_logic;
        $this->listing_renderer = $listing_renderer;
        $this->payments = $payments;
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function validate_order($data, &$errors=array()) {
        if ( is_null( $data['payment_term'] ) ) {
            $errors[] = __( 'You should choose one of the available Payment Terms.', 'another-wordpress-classifieds-plugin');
        } else {
            if ( $data['payment_term']->type != $data['fee']->type || $data['payment_term']->id != $data['fee']->id ) {
                $errors[] = __("You are trying to renew your Ad using a different Payment Term. That's not allowed.", 'another-wordpress-classifieds-plugin');
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function order_step() {
        $ad                  = $this->page->get_ad();
        $transaction         = $this->page->get_transaction(true);
        $selected_categories = $this->listing_renderer->get_categories_ids( $ad );

        $fee = $this->listing_renderer->get_payment_term( $ad );

        $form_errors = array();
        $transaction_errors = array();

        // verify pre-conditions

        if ($transaction->is_new()) {
            $this->payments->set_transaction_status_to_open( $transaction, $transaction_errors );
        }

        $transaction->set( 'category', $selected_categories );

        // validate submitted data and prepare transaction

        $payment_terms = array( $fee->type => array( $fee ) );
        $payment_terms_list = awpcp_payment_terms_list();

        if ( awpcp_current_user_is_admin() || ! $this->payments->payment_term_requires_payment( $fee ) ) {
            $accepted_payment_types = $this->payments->get_accepted_payment_types();

            $payment_term = $fee;
            $payment_type = array_shift( $accepted_payment_types );

            $transaction->set('payment-term-type', $payment_term->type);
            $transaction->set('payment-term-id', $payment_term->id);
            $transaction->set( 'ad-id', $ad->ID );

            $transaction->remove_all_items();

            $this->payments->set_transaction_item_from_payment_term(
                $transaction, $payment_term, $payment_type
            );

        } else {
            $payment_term = $this->payments->get_transaction_payment_term( $transaction );

            if (!empty($_POST)) {
                $payment_terms_list->handle_request( $this->request );

                $payment_options = $payment_terms_list->get_data();

                if ( ! is_null( $payment_options ) ) {
                    $payment_term = $payment_options['payment_term'];
                    $payment_type = $payment_options['payment_type'];
                } else {
                    $payment_term = null;
                    $payment_type = '';
                }

                $this->validate_order( compact( 'payment_term', 'fee' ), $form_errors );

                if (empty($form_errors)) {
                    $transaction->set( 'payment-term-type', $payment_term->type );
                    $transaction->set( 'payment-term-id', $payment_term->id );
                    $transaction->set( 'payment-term-payment-type', $payment_type );
                    $transaction->set( 'ad-id', $ad->ID );

                    $transaction->remove_all_items();

                    $this->payments->set_transaction_item_from_payment_term(
                        $transaction, $payment_term, $payment_type
                    );

                    // process transaction to grab Credit Plan information
                    $this->payments->set_transaction_credit_plan( $transaction );
                }
            }
        }

        // let other parts of the plugin know a transaction is being processed
        $this->payments->process_transaction( $transaction );

        // if everything is fine move onto the next step
        if ( ! is_null( $payment_term ) ) {
            $this->payments->set_transaction_status_to_ready_to_checkout( $transaction, $transaction_errors );
            if (empty($transaction_errors)) {
                return $this->checkout_step();
            }
        }

        // otherwise display the order form to grab information and show any errors

        $messages = $this->messages;
        if (awpcp_current_user_is_admin()) {
            $messages[] = __("You are logged in as an administrator. Any payment steps will be skipped.", 'another-wordpress-classifieds-plugin');
        }

        $params = array(
            'payments' => $this->payments,
            'transaction' => $transaction,
            'payment_terms_list'         => $payment_terms_list,
            'payment_terms_list_options' => [
                'payment_terms' => $payment_terms,
                'transaction'   => $transaction,
            ],

            'messages' => $messages,
            'form_errors' => $form_errors,
            'transaction_errors' => $transaction_errors
        );

        $template = AWPCP_DIR . '/frontend/templates/page-renew-ad-order-step.tpl.php';

        return $this->page->render($template, $params);
    }

    public function checkout_step() {
        $transaction = $this->page->get_transaction(true);

        $errors = array();

        // verify transaction pre-conditions

        if (is_null($transaction)) {
            $message = $this->page->transaction_error();
            return $this->page->page->render('content', awpcp_print_error($message));
        }

        if ($transaction->is_payment_completed()) {
            return $this->payment_completed_step();
        }

        if ( $transaction->is_ready_to_checkout() ) {
            $this->payments->set_transaction_status_to_checkout( $transaction, $errors );
        }

        if ( empty($errors) && $transaction->payment_is_not_required() ) {
            $this->payments->set_transaction_status_to_payment_completed( $transaction, $errors );

            return $this->payment_completed_step();
        }

        if ( !$transaction->is_doing_checkout() && !$transaction->is_processing_payment() ) {
            $message = __( 'We can\'t process payments for this Payment Transaction at this time. Please contact the website administrator and provide the following transaction ID: %s', 'another-wordpress-classifieds-plugin');
            $message = sprintf($message, $transaction->id);
            return $this->page->render('content', awpcp_print_error($message));
        }

        // proceess transaction to grab Payment Method information

        $this->payments->set_transaction_payment_method( $transaction );

        // show checkout page.

        // If a Payment Method was already selected, the Payments API already
        // processed the transaction and will (depending of the Payment Method):
        // show a checkout button, show a billing information form or
        // automatically redirect the user to the payment gateway.

        $params = array(
            'payments' => $this->payments,
            'transaction' => $transaction,
            'messages' => $this->messages,
            'hidden' => array('step' => 'checkout')
        );

        // here we reuse the Place Ad template, because is generic enough for our needs
        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-checkout-step.tpl.php';

        return $this->page->render($template, $params);
    }

    public function payment_completed_step() {
        $transaction = $this->page->get_transaction();

        if ($transaction->payment_is_not_required()) {
            return $this->finish_step();
        }

        $params = array(
            'payments' => $this->payments,
            'transaction' => $transaction,
            'messages' => $this->messages,
            'url' => $this->page->url(),
            'hidden' => array('step' => 'finish')
        );

        // here we reuse the Place Ad template, because is generic enough for our needs
        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-payment-completed-step.tpl.php';

        return $this->page->render($template, $params);
    }

    public function finish_step() {
        $transaction = $this->page->get_transaction();

        if (is_null($transaction)) {
            $message = $this->page->transaction_error();
            return $this->page->render('content', awpcp_print_error($message));
        }

        $ad = $this->page->get_ad();

        if (is_null($ad)) {
            $message = __( 'The Ad associated with this transaction doesn\'t exists.', 'another-wordpress-classifieds-plugin');
            return $this->page->render('content', awpcp_print_error($message));
        }

        if (!$transaction->is_completed()) {
            $this->payments->set_transaction_status_to_completed( $transaction, $errors );

            if (!empty($errors)) {
                return $this->page->render('content', join(',', array_map($errors, 'awpcp_print_error')));
            }
        }

        return $this->page->render_finish_step($ad);
    }
}
