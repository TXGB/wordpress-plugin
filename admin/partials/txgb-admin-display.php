<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    Txgb
 * @subpackage Txgb/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2>TXGB Setup</h2>
	<form action="options.php" method="post">
		<?php
		settings_fields('txgb_options');
		do_settings_sections('txgb');
		?>

		<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
	</form>

	<?php
	if (defined('TXGB_ADVANCED_OPTIONS')) :
		if (TXGB_ADVANCED_OPTIONS) :
	?>
			<div style="margin-top: 3rem">
				<h2>Advanced Tools</h2>
				<p>Here thar be dragons.</p>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								Regenerate product cache
							</th>
							<td>
								<form method="POST">
									<?php $txgb_reset_product_cache_form_action_nonce = wp_create_nonce('txgb_reset_product_cache_form_action_nonce'); ?>
									<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
									<input type="hidden" name="txgb_reset_product_cache_form_action_nonce" value="<?php echo $txgb_reset_product_cache_form_action_nonce ?>" />
									<button type="submit" class="button button-secondary">Regenerate</button>
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
	<?php
		endif;
	endif;
	?>
</div>
