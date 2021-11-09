<article class="txgb__availability-product txgb__availability-product--default">
	<div class="txgb__availability-product__inner">
		<div class="txgb__availability-product__info">
			<header class="txgb__availability-product__header">
				<h1 class="txgb__availability-product__title"><?php echo $product->name; ?></h1>
				<div class="txgb__availability-product__dates">
					<time datetime="<?php echo $product->starts_at->format(DateTimeInterface::W3C); ?>">
						<?php echo $product->starts_at->format('l jS F Y'); ?>
					</time>
					to
					<time datetime="<?php echo $product->ends_at->format(DateTimeInterface::W3C); ?>">
						<?php echo $product->ends_at->format('l jS F Y'); ?>
					</time>
				</div>
			</header>

			<section class="txgb__availability-product__body">
				<?php if ($product->description) : ?>
					<div class="txgb_availability-product__description">
						<?php echo $product->description ?>
					</div>
				<?php endif; ?>
			</section>
		</div>

		<aside class="txgb_availability-product__images">
			<?php if (count($product->images) == 0) : ?>
				<p>No images</p>
			<?php else : ?>
				<ul class="txgb_availability-product__image_list">
					<?php foreach ($product->images as $index => $image) : ?>
						<li class="txgb_availability-product__image_item">
							<img class="txgb_availability-product__image txgb_availability-product__image--thumb" src="<?php echo $image->url ?>" alt="<?php echo $image->description ?>" />
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</aside>
	</div>

	<footer class="txgb__availability-product__booking">
		<!-- <dl class="txgb__availability-product__rates">
			<dt>Highest rate</dt>
			<dd><?php echo $product->highest_rate->formatted ?></dd>
			<dt>Lowest rate</dt>
			<dd><?php echo $product->lowest_rate->formatted ?></dd>
			<dt>Average rate</dt>
			<dd><?php echo $product->average_rate->formatted ?></dd>
		</dl> -->
		<?php $product->output_booking_form() ?>
	</footer>
</article>
