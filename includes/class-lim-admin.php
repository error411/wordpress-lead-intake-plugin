<?php
/**
 * Admin screen for submitted leads.
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a simple admin menu and lead table.
 */
class LIM_Admin {
	/**
	 * Capability required to manage leads.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu hook suffix.
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'handle_status_update' ) );
		add_action( 'admin_init', array( $this, 'handle_csv_export' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add the admin menu page.
	 */
	public function add_menu_page() {
		$this->hook_suffix = add_menu_page(
			__( 'Lead Intake', 'lead-intake-manager' ),
			__( 'Lead Intake', 'lead-intake-manager' ),
			self::CAPABILITY,
			'lead-intake-manager',
			array( $this, 'render_page' ),
			'dashicons-feedback',
			26
		);
	}

	/**
	 * Enqueue admin CSS only on this plugin page.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $hook_suffix !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'lim-admin',
			LIM_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			LIM_VERSION
		);
	}

	/**
	 * Handle lead status updates from the admin table.
	 */
	public function handle_status_update() {
		if ( empty( $_POST['lim_update_status'] ) ) {
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to update leads.', 'lead-intake-manager' ) );
		}

		if ( ! isset( $_POST['lim_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lim_status_nonce'] ) ), 'lim_update_status' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'lead-intake-manager' ) );
		}

		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;
		$status  = isset( $_POST['lead_status'] ) ? sanitize_key( wp_unslash( $_POST['lead_status'] ) ) : '';

		$updated = LIM_DB::update_status( $lead_id, $status );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'lead-intake-manager',
					'lim_notice'  => $updated ? 'updated' : 'invalid_status',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Export current leads as a CSV file.
	 */
	public function handle_csv_export() {
		if ( empty( $_GET['lim_export'] ) || 'csv' !== sanitize_key( wp_unslash( $_GET['lim_export'] ) ) ) {
			return;
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to export leads.', 'lead-intake-manager' ) );
		}

		check_admin_referer( 'lim_export_leads_csv' );

		$filename = 'lead-intake-export-' . gmdate( 'Y-m-d' ) . '.csv';
		$leads    = LIM_DB::get_leads( 200 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, array( 'Name', 'Email', 'Phone', 'Service Needed', 'Notes', 'Status', 'Submitted' ) );

		foreach ( $leads as $lead ) {
			fputcsv(
				$output,
				array(
					$lead['name'],
					$lead['email'],
					$lead['phone'],
					$lead['service_needed'],
					$lead['notes'],
					LIM_DB::get_status_label( $lead['status'] ),
					$lead['created_at'],
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Render the admin page.
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$leads = LIM_DB::get_leads();
		?>
		<div class="wrap lim-admin-wrap">
			<div class="lim-admin-header">
				<div>
					<h1><?php esc_html_e( 'Lead Intake Manager', 'lead-intake-manager' ); ?></h1>
					<p class="description">
						<?php esc_html_e( 'Review recent lead submissions and update their follow-up status.', 'lead-intake-manager' ); ?>
					</p>
				</div>

				<a class="button button-secondary" href="<?php echo esc_url( $this->get_export_url() ); ?>">
					<?php esc_html_e( 'Export CSV', 'lead-intake-manager' ); ?>
				</a>
			</div>

			<?php $this->render_admin_notice(); ?>

			<table class="widefat fixed striped lim-leads-table">
				<thead>
					<tr>
						<th class="lim-col-contact"><?php esc_html_e( 'Contact', 'lead-intake-manager' ); ?></th>
						<th class="lim-col-service"><?php esc_html_e( 'Service Needed', 'lead-intake-manager' ); ?></th>
						<th class="lim-col-notes"><?php esc_html_e( 'Notes', 'lead-intake-manager' ); ?></th>
						<th class="lim-col-status"><?php esc_html_e( 'Status', 'lead-intake-manager' ); ?></th>
						<th class="lim-col-submitted"><?php esc_html_e( 'Submitted', 'lead-intake-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $leads ) ) : ?>
						<tr>
							<td colspan="5" class="lim-empty-state">
								<strong><?php esc_html_e( 'No leads yet.', 'lead-intake-manager' ); ?></strong>
								<span><?php esc_html_e( 'Lead submissions will appear here after someone uses the intake form.', 'lead-intake-manager' ); ?></span>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $leads as $lead ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $lead['name'] ); ?></strong>
									<div><a href="<?php echo esc_url( 'mailto:' . $lead['email'] ); ?>"><?php echo esc_html( $lead['email'] ); ?></a></div>
									<?php if ( '' !== $lead['phone'] ) : ?>
										<div class="lim-muted"><?php echo esc_html( $lead['phone'] ); ?></div>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $lead['service_needed'] ); ?></td>
								<td><?php echo esc_html( $this->format_notes( $lead['notes'] ) ); ?></td>
								<td><?php $this->render_status_form( $lead ); ?></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lead['created_at'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render a compact status update form.
	 *
	 * @param array $lead Lead row.
	 */
	private function render_status_form( $lead ) {
		$current_status = LIM_DB::is_valid_status( $lead['status'] ) ? $lead['status'] : 'new';
		?>
		<form method="post" class="lim-status-form">
			<?php wp_nonce_field( 'lim_update_status', 'lim_status_nonce' ); ?>
			<input type="hidden" name="lead_id" value="<?php echo esc_attr( $lead['id'] ); ?>">
			<span class="lim-status-badge lim-status-<?php echo esc_attr( $current_status ); ?>">
				<?php echo esc_html( LIM_DB::get_status_label( $current_status ) ); ?>
			</span>
			<select name="lead_status">
				<?php foreach ( LIM_DB::get_statuses() as $status ) : ?>
					<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current_status, $status ); ?>>
						<?php echo esc_html( LIM_DB::get_status_label( $status ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button class="button button-small" type="submit" name="lim_update_status" value="1">
				<?php esc_html_e( 'Save', 'lead-intake-manager' ); ?>
			</button>
		</form>
		<?php
	}

	/**
	 * Render admin result notices.
	 */
	private function render_admin_notice() {
		if ( empty( $_GET['lim_notice'] ) ) {
			return;
		}

		$notice = sanitize_key( wp_unslash( $_GET['lim_notice'] ) );

		if ( 'updated' === $notice ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Lead status updated.', 'lead-intake-manager' ); ?></p>
			</div>
			<?php
			return;
		}

		if ( 'invalid_status' === $notice ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Lead status could not be updated.', 'lead-intake-manager' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Build a nonce-protected CSV export URL.
	 *
	 * @return string
	 */
	private function get_export_url() {
		$url = add_query_arg(
			array(
				'page'       => 'lead-intake-manager',
				'lim_export' => 'csv',
			),
			admin_url( 'admin.php' )
		);

		return wp_nonce_url( $url, 'lim_export_leads_csv' );
	}

	/**
	 * Format notes for table display.
	 *
	 * @param string $notes Lead notes.
	 * @return string
	 */
	private function format_notes( $notes ) {
		if ( '' === trim( $notes ) ) {
			return __( 'No notes provided.', 'lead-intake-manager' );
		}

		return wp_trim_words( $notes, 18 );
	}

}
