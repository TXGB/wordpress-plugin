<?php do_action('txgb_show_availability_form', get_post_meta(get_the_ID(), 'uuid', true)); ?>

<div class="txgb__booking-container txgb__booking-bg alignwide">
	<h1 class="txgb__title">Choose availability</h1>

	<div class="txgb__availability-products">
		<?php if (!$products || count($products) === 0) : ?>
			<div class="txgb__availability-products-no_results">
				<h2 class="txgb__availability-products-no_results__title">No results found.</h2>
				<p>Sorry, there were no results for your search.</p>
			</div>
		<?php
		else :
			foreach ($products as $product) :
				switch ($product->category) {
					case 'accommodation':
					case 'nonservicedaccommodation':
						include plugin_dir_path(dirname(__FILE__)) . 'products/accommodation.php';
						break;

					default:
						include plugin_dir_path(dirname(__FILE__)) . 'products/default.php';
				}
			endforeach;
		endif;
		?>
	</div>
</div>
