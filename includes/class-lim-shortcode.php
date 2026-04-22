<?php
/**
 * Frontend shortcode output.
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the lead intake form.
 */
class LIM_Shortcode {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_shortcode( 'lead_intake_form', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register frontend CSS.
	 */
	public function register_assets() {
		wp_register_style(
			'lim-public',
			LIM_PLUGIN_URL . 'assets/css/public.css',
			array(),
			LIM_VERSION
		);
	}

	/**
	 * Render the shortcode.
	 *
	 * @return string
	 */
	public function render() {
		wp_enqueue_style( 'lim-public' );

		ob_start();
		?>
		<div class="lim-form-wrap">
			<?php $this->render_notice(); ?>

			<form class="lim-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="lim_submit_lead">
				<?php wp_nonce_field( 'lim_submit_lead', 'lim_lead_nonce' ); ?>

				<p class="lim-field">
					<label for="lim_name"><?php esc_html_e( 'Name', 'lead-intake-manager' ); ?> <span aria-hidden="true">*</span></label>
					<input id="lim_name" name="lim_name" type="text" required>
				</p>

				<p class="lim-field">
					<label for="lim_email"><?php esc_html_e( 'Email', 'lead-intake-manager' ); ?> <span aria-hidden="true">*</span></label>
					<input id="lim_email" name="lim_email" type="email" required>
				</p>

				<p class="lim-field">
					<label for="lim_phone"><?php esc_html_e( 'Phone', 'lead-intake-manager' ); ?></label>
					<input id="lim_phone" name="lim_phone" type="tel">
				</p>

				<p class="lim-field">
					<label for="lim_service_needed"><?php esc_html_e( 'Service Needed', 'lead-intake-manager' ); ?> <span aria-hidden="true">*</span></label>
					<input id="lim_service_needed" name="lim_service_needed" type="text" required>
				</p>

				<p class="lim-field">
					<label for="lim_notes"><?php esc_html_e( 'Notes', 'lead-intake-manager' ); ?></label>
					<textarea id="lim_notes" name="lim_notes" rows="5"></textarea>
				</p>

				<p class="lim-actions">
					<button type="submit"><?php esc_html_e( 'Submit Request', 'lead-intake-manager' ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render form success/error notices from the redirect.
	 */
	private function render_notice() {
		if ( empty( $_GET['lim_status'] ) || empty( $_GET['lim_message'] ) ) {
			return;
		}

		$status  = sanitize_key( wp_unslash( $_GET['lim_status'] ) );
		$message = sanitize_text_field( wp_unslash( $_GET['lim_message'] ) );

		if ( ! in_array( $status, array( 'success', 'error' ), true ) ) {
			return;
		}
		?>
		<div class="lim-notice lim-notice-<?php echo esc_attr( $status ); ?>">
			<?php echo esc_html( $message ); ?>
		</div>
		<?php
	}
}
