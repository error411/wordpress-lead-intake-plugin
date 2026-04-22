<?php
/**
 * Handles frontend form submissions.
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates, sanitizes, and saves submitted leads.
 */
class LIM_Form_Handler {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_post_nopriv_lim_submit_lead', array( $this, 'handle_submission' ) );
		add_action( 'admin_post_lim_submit_lead', array( $this, 'handle_submission' ) );
	}

	/**
	 * Handle a submitted lead.
	 */
	public function handle_submission() {
		if ( ! isset( $_POST['lim_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lim_lead_nonce'] ) ), 'lim_submit_lead' ) ) {
			$this->redirect_with_message( 'error', __( 'We could not verify the form. Please refresh the page and try again.', 'lead-intake-manager' ) );
		}

		$lead = array(
			'name'           => isset( $_POST['lim_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lim_name'] ) ) : '',
			'email'          => isset( $_POST['lim_email'] ) ? sanitize_email( wp_unslash( $_POST['lim_email'] ) ) : '',
			'phone'          => isset( $_POST['lim_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['lim_phone'] ) ) : '',
			'service_needed' => isset( $_POST['lim_service_needed'] ) ? sanitize_text_field( wp_unslash( $_POST['lim_service_needed'] ) ) : '',
			'notes'          => isset( $_POST['lim_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lim_notes'] ) ) : '',
		);

		$error = $this->validate( $lead );
		if ( $error ) {
			$this->redirect_with_message( 'error', $error );
		}

		$lead_id = LIM_DB::insert_lead( $lead );
		if ( ! $lead_id ) {
			$this->redirect_with_message( 'error', __( 'We could not save your request. Please try again.', 'lead-intake-manager' ) );
		}

		$this->redirect_with_message( 'success', __( 'Thanks. Your request has been received and is ready for follow-up.', 'lead-intake-manager' ) );
	}

	/**
	 * Validate sanitized form data.
	 *
	 * @param array $lead Sanitized lead data.
	 * @return string Empty string when valid, otherwise an error message.
	 */
	private function validate( $lead ) {
		if ( '' === $lead['name'] ) {
			return __( 'Please enter your name.', 'lead-intake-manager' );
		}

		if ( '' === $lead['email'] ) {
			return __( 'Please enter your email address.', 'lead-intake-manager' );
		}

		if ( ! is_email( $lead['email'] ) ) {
			return __( 'Please enter a valid email address.', 'lead-intake-manager' );
		}

		if ( '' === $lead['service_needed'] ) {
			return __( 'Please tell us what service you need.', 'lead-intake-manager' );
		}

		return '';
	}

	/**
	 * Redirect back to the form with a compact status message.
	 *
	 * @param string $status  Message status.
	 * @param string $message Message text.
	 */
	private function redirect_with_message( $status, $message ) {
		$redirect_url = wp_get_referer();

		if ( ! $redirect_url ) {
			$redirect_url = home_url( '/' );
		}

		$redirect_url = remove_query_arg( array( 'lim_status', 'lim_message' ), $redirect_url );

		$redirect_url = add_query_arg(
			array(
				'lim_status'  => $status,
				'lim_message' => $message,
			),
			$redirect_url
		);

		$redirect_url .= '#lim-lead-intake-form';

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
