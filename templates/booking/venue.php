<?php
$starts_at = array_key_exists('starts_at', $_REQUEST) ? $_REQUEST['starts_at'] : '';
$ends_at = array_key_exists('ends_at', $_REQUEST) ? $_REQUEST['ends_at'] : '';

$adults = array_key_exists('adults', $_REQUEST) ? $_REQUEST['adults'] : 1;
$children = array_key_exists('children', $_REQUEST) ? $_REQUEST['children'] : 1;
$concessions = array_key_exists('concessions', $_REQUEST) ? $_REQUEST['concessions'] : 1;

$min_date = (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d');
?>
<form action="#venue-availability" class="txgb__venue-search alignwide js-txgb__has-dates" id="venue-availability">
	<h2 class="txgb__venue-search__title">Find availability</h2>

	<input type="hidden" name="booking/availability" value="" />
	<input type="hidden" name="service_id" value="<?php echo get_post_meta(get_the_ID(), 'uuid', true); ?>" />

	<div class="txgb__venue-search__fields">
		<div class="txgb__venue-search__date">
			<div>
				<label class="txgb__venue-search__label">From</label>
				<input name="starts_at" type="date" value="<?php echo $starts_at ?>" min="<?php echo $min_date ?>" />
			</div>

			<div>
				<label class="txgb__venue-search__label">To</label>
				<input type="date" name="ends_at" value="<?php echo $ends_at ?>" min="<?php echo $min_date ?>" />
			</div>
		</div>

		<div class="txgb__venue-search__people">
			<div>
				<label class="txgb__venue-search__label">Adults</label>
				<input type="number" name="adults" min="0" step="1" value="<?php echo $adults ?>" />
			</div>

			<div>
				<label class="txgb__venue-search__label">Children</label>
				<input type="number" name="children" min="0" step="1" value="<?php echo $children ?>" />
			</div>

			<div>
				<label class="txgb__venue-search__label">Concessions</label>
				<input type="number" name="concessions" min="0" step="1" value="<?php echo $concessions ?>" />
			</div>
		</div>

		<button class="txgb__venue-search__button cta primary">
			Search
		</button>
	</div>
</form>
