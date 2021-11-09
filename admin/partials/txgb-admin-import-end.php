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
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="message" class="updated notice is-dismissible">
	<p>Successfully imported <?php echo $imported_count ?> service<?php echo $imported_count != 1 ? 's' : '' ?>.</p>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text">Dismiss this notice.</span>
	</button>
</div>

</div>
