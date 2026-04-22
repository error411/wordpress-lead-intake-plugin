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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add the admin menu page.
	 */
	public function add_menu_page() {
		$this->hook_suffix = add_menu_page(
			__( 'Lead Intake', 'lead-intake-manager' ),
			__( 'Lead Intake', 'lead-intake-manager' ),
			'manage_options',
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

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to update leads.', 'lead-intake-manager' ) );
		}

		if ( ! isset( $_POST['lim_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lim_status_nonce'] ) ), 'lim_update_status' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'lead-intake-manager' ) );
		}

		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;
		$status  = isset( $_POST['lead_status'] ) ? sanitize_key( wp_unslash( $_POST['lead_status'] ) ) : '';

		LIM_DB::update_status( $lead_id, $status );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'lead-intake-manager',
					'lim_updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render the admin page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$leads = LIM_DB::get_leads();
		?>
		<div class="wrap lim-admin-wrap">
			<h1><?php esc_html_e( 'Lead Intake Manager', 'lead-intake-manager' ); ?></h1>

			<?php if ( ! empty( $_GET['lim_updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Lead status updated.', 'lead-intake-manager' ); ?></p>
				</div>
			<?php endif; ?>

			<table class="widefat fixed striped lim-leads-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Email', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Service', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Notes', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Status', 'lead-intake-manager' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'lead-intake-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $leads ) ) : ?>
						<tr>
							<td colspan="7"><?php esc_html_e( 'No leads submitted yet.', 'lead-intake-manager' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $leads as $lead ) : ?>
							<tr>
								<td><?php echo esc_html( $lead['name'] ); ?></td>
								<td><a href="mailto:<?php echo esc_attr( $lead['email'] ); ?>"><?php echo esc_html( $lead['email'] ); ?></a></td>
								<td><?php echo esc_html( $lead['phone'] ); ?></td>
								<td><?php echo esc_html( $lead['service_needed'] ); ?></td>
								<td><?php echo esc_html( wp_trim_words( $lead['notes'], 18 ) ); ?></td>
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
		?>
		<form method="post" class="lim-status-form">
			<?php wp_nonce_field( 'lim_update_status', 'lim_status_nonce' ); ?>
			<input type="hidden" name="lead_id" value="<?php echo esc_attr( $lead['id'] ); ?>">
			<select name="lead_status">
				<?php foreach ( LIM_DB::get_statuses() as $status ) : ?>
					<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $lead['status'], $status ); ?>>
						<?php echo esc_html( ucfirst( $status ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button class="button button-small" type="submit" name="lim_update_status" value="1">
				<?php esc_html_e( 'Update', 'lead-intake-manager' ); ?>
			</button>
		</form>
		<?php
	}
}
