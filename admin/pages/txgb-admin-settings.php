<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    Txgb
 * @subpackage Txgb/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    TXGB
 * @subpackage TXGB/admin
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Admin_Settings
{
	private $slug;

	public function __construct($slug)
	{
		$this->slug = $slug;

		$this->register_settings();
		$this->register_page();
	}

	public function register_page()
	{

		add_options_page(
			'TXGB',             // Page title
			'TXGB',             // Menu title
			'manage_options',   // Capability
			$this->slug,        // Menu slug
			[$this, 'render'],  // Callback
			1                   // Position
		);
	}

	public function register_settings()
	{
		$home_page_id = get_option('page_on_front');
		$pages = get_pages(array());

		$options = array_merge([
			'shortname' => '',
			'key'       => '',
			'page_id_success' => $home_page_id,
			'page_id_error' => $home_page_id,
		], get_option('txgb_options', []));

		register_setting('txgb_options', 'txgb_options');

		add_settings_section('authentication', 'Authentication', function () {
			echo "<p>Add your TXGB Distributor authentication details.</p>";
		}, $this->slug);

		add_settings_field('txgb-id', 'Shortname/ID', function () use ($options) {
			echo "<input id='txgb-shortname' name='txgb_options[shortname]' type='text' value='" . esc_attr($options['shortname']) . "' />";
		}, $this->slug, 'authentication');

		add_settings_field('txgb-key', 'Key', function () use ($options) {
			echo "<input id='txgb-key' name='txgb_options[key]' type='text' value='" . esc_attr($options['key']) . "' />";
		}, $this->slug, 'authentication');

		add_settings_section('return_urls', 'Return Pages', function () {
			echo "<p>Set where a customer should return after booking.</p>";
		}, $this->slug);

		add_settings_field('txgb-url-success', 'Successful Booking', function () use ($options, $pages) {
?>
			<select id="txgb-url-success" name="txgb_options[page_id_success]">
				<?php foreach ($pages as $page) : ?>
					<option value="<?php echo $page->ID ?>" <?php echo $page->ID == $options['page_id_success'] ? ' selected' : ''; ?>>
						<?php echo $page->post_title ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php
		}, $this->slug, 'return_urls');

		add_settings_field('txgb-url-error', 'Unsuccessful Booking', function () use ($options, $pages) {
		?>
			<select id="txgb-url-error" name="txgb_options[page_id_error]">
				<?php foreach ($pages as $page) : ?>
					<option value="<?php echo $page->ID ?>" <?php echo $page->ID == $options['page_id_error'] ? ' selected' : ''; ?>>
						<?php echo $page->post_title ?>
					</option>
				<?php endforeach; ?>
			</select>
<?php
		}, $this->slug, 'return_urls');
	}

	public function render()
	{
		$nonce = isset($_POST['txgb_reset_product_cache_form_action_nonce'])
			? $_POST['txgb_reset_product_cache_form_action_nonce']
			: null;

		if (
			$nonce
			&& wp_verify_nonce(
				$nonce,
				'txgb_reset_product_cache_form_action_nonce'
			)
		) {
			$posts = get_posts([
				'post_type'     => 'txgb_venue',
				'post_status'   => 'any',
			]);

			foreach ($posts as $post) {
				update_post_meta($post->ID, 'last_product_sync', 'null');
			}

			do_action('admin_service_product_sync');
		}

		include __DIR__ . '/../partials/txgb-admin-display.php';
	}
}
