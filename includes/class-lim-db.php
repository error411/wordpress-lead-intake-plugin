<?php
/**
 * Database helpers for leads.
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom table access.
 */
class LIM_DB {
	/**
	 * Allowed lead statuses.
	 *
	 * @return string[]
	 */
	public static function get_statuses() {
		return array_keys( self::get_status_labels() );
	}

	/**
	 * Get display labels for lead statuses.
	 *
	 * @return string[]
	 */
	public static function get_status_labels() {
		return array(
			'new'       => __( 'New', 'lead-intake-manager' ),
			'contacted' => __( 'Contacted', 'lead-intake-manager' ),
			'closed'    => __( 'Closed', 'lead-intake-manager' ),
		);
	}

	/**
	 * Validate a status value.
	 *
	 * @param string $status Status slug.
	 * @return bool
	 */
	public static function is_valid_status( $status ) {
		return in_array( $status, self::get_statuses(), true );
	}

	/**
	 * Get a display label for a status.
	 *
	 * @param string $status Status slug.
	 * @return string
	 */
	public static function get_status_label( $status ) {
		$labels = self::get_status_labels();

		return isset( $labels[ $status ] ) ? $labels[ $status ] : $labels['new'];
	}

	/**
	 * Get the custom table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'lim_leads';
	}

	/**
	 * Create or update the leads table.
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(190) NOT NULL,
			email varchar(190) NOT NULL,
			phone varchar(50) NOT NULL DEFAULT '',
			service_needed varchar(190) NOT NULL,
			notes text NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'new',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Insert a lead.
	 *
	 * @param array $lead Lead data.
	 * @return int|false Inserted row ID on success, false on failure.
	 */
	public static function insert_lead( $lead ) {
		global $wpdb;

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'name'           => $lead['name'],
				'email'          => $lead['email'],
				'phone'          => $lead['phone'],
				'service_needed' => $lead['service_needed'],
				'notes'          => $lead['notes'],
				'status'         => 'new',
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Fetch recent leads for the admin table.
	 *
	 * @param int $limit Number of leads to return.
	 * @return array
	 */
	public static function get_leads( $limit = 50 ) {
		global $wpdb;

		$limit = max( 1, min( 200, absint( $limit ) ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, name, email, phone, service_needed, notes, status, created_at FROM ' . self::table_name() . ' ORDER BY created_at DESC LIMIT %d',
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Update a lead status.
	 *
	 * @param int    $lead_id Lead ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public static function update_status( $lead_id, $status ) {
		global $wpdb;

		if ( ! self::is_valid_status( $status ) || ! $lead_id ) {
			return false;
		}

		$updated = $wpdb->update(
			self::table_name(),
			array( 'status' => $status ),
			array( 'id' => absint( $lead_id ) ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}
}
