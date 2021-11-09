<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    TXGB
 * @subpackage TXGB/admin/partials
 */

$active_nonce = isset($_POST['txgb_import_services_form_action_nonce'])
	? $_POST['txgb_import_services_form_action_nonce']
	: null;
$txgb_import_services_form_action_nonce = wp_create_nonce('txgb_import_services_form_action_nonce');

$txgb_service_query = array_key_exists('txgb_service_query', $_GET)
	? $_GET['txgb_service_query']
	: '';
$txgb_service_type = array_key_exists('txgb_service_type', $_GET)
	? $_GET['txgb_service_type']
	: '';

$options = get_option('txgb_options', []);
$services = get_transient('txgb_import_services');

if (!$services) {
	$api = new TXGB_API_Entity($options['shortname'], $options['key']);

	$services = $api->get_all_services($txgb_service_type, $txgb_service_query);

	$imported_ids = array();
	foreach ($services as $service) {
		$imported_ids[] = $service->id;
	}
	if (count($imported_ids) > 0) {
		$imported_posts = get_posts([
			'meta_query' => [
				'key' => 'uuid',
				'compare' => 'IN',
				'value' => $imported_ids,
			],
		]);
	}
	set_transient('txgb_import_services', $services, 5 * MINUTE_IN_SECONDS);
}

// Check for previously-imported posts

$service_list_table = new Service_List_Table();
$service_list_table->items = $services;
$service_list_table->prepare_items();

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php if (!$active_nonce) include(plugin_dir_path(__FILE__) . 'txgb-admin-import-intro.php'); ?>

<div class="tablenav top">
	<form action="./admin.php" method="GET" id="txgb_import_providers_form_query">
		<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />

		<label for="txgb_service_query" class="screen-reader-text">Search services</label>
		<input type="text" id="txgb_service_query" name="txgb_service_query" value="" placeholder="Search providers" />
		<select name="txgb_service_type">
			<option value="">All</option>
			<option value="1">Accommodation</option>
			<option value="2">Attraction</option>
			<option value="3">Events</option>
			<option value="9">Non-serviced Accommodation</option>
			<option value="8">Tours</option>
		</select>

		<button class="button action" type="submit"><?php esc_attr_e('Search'); ?></button>
	</form>
</div>

<form method="POST">
	<input type="hidden" name="txgb_import_services_form_action_nonce" value="<?php echo $txgb_import_services_form_action_nonce ?>" />

	<?php $service_list_table->display(); ?>
</form>
</div>
