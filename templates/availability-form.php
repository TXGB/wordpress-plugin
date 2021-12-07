<?php
global $availability_vars;

$cities = get_transient('txgb_city_values');
if (!$cities) {
	$all_venue_ids = get_posts(
		array(
			'post_type' => 'txgb_venue',
			'nopaging' => true,
			array(
				'key' => 'address_city',
				'compare' => '!=',
				'value' => '',
			),
			'fields' => 'ids',
		),
	);
	$city_list = array();
	foreach ($all_venue_ids as $id) {
		$city = get_post_meta($id, 'address_city', true);
		$city_list[$city] = 1;
	}

	$cities = array_keys($city_list);
	sort($cities, SORT_STRING);

	set_transient('txgb_city_values', $cities, 5 * MINUTE_IN_SECONDS);
}

$availability_vars = $availability_vars ?: apply_filters('txgb_filter_availability_params', $_REQUEST);

$starts_at = !$availability_vars->starts_at
	? ''
	: $availability_vars->starts_at->format('Y-m-d');
$ends_at = !$availability_vars->ends_at
	? ''
	: $availability_vars->ends_at->format('Y-m-d');
$adults = $availability_vars->adults;
$children = $availability_vars->children;
$concessions = $availability_vars->concessions;

$venue_types = get_terms(
	array(
		'taxonomy' => 'txgb_venue_type',
		'hide_empty' => true,
	)
);

$min_date = (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d');

$form_action = get_post_type_archive_link('txgb_venue');
?>
<form action="<?php echo $form_action ?>#venue-availability" class="txgb__venues-search alignwide js-txgb__has-dates" id="venue-availability">
	<h2 class="txgb__venue-search__title">Find availability</h2>
	<input type="hidden" name="venues/availability" value="" />

	<div class="txgb__venue-search__fields">
		<div class="txgb__venue-search__type">
			<div>
				<label class="txgb__venue-search__label">Type</label>

				<select name="type">
					<option value="">Any</option>
					<?php foreach ($venue_types as $venue_type) : ?>
						<option value="<?php echo $venue_type->term_id ?>" <?php if ($availability_vars->type == $venue_type->term_id) echo ' selected' ?>><?php echo $venue_type->name ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="txgb__venue-search__city">
			<div>
				<label class="txgb__venue-search__label">City</label>

				<select name="city">
					<option value="">Any</option>
					<?php foreach ($cities as $city) : ?>
						<option value="<?php echo $city ?>" <?php if ($availability_vars->city == $city) echo ' selected' ?>><?php echo $city ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="txgb__venue-search__date">
			<div>
				<label class="txgb__venue-search__label">From</label>

				<input name="starts_at" type="date" value="<?php echo $starts_at ?>" min="<?php echo $min_date ?>" pattern="\d{4}-\d{2}-\d{2}" />
			</div>

			<div>
				<label class="txgb__venue-search__label">To</label>

				<input type="date" name="ends_at" value="<?php echo $ends_at ?>" min="<?php echo $min_date ?>" pattern="\d{4}-\d{2}-\d{2}" />
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
	</div>

	<div class="txgb__venue-search__fields">
		<button class="txgb__venue-search__button cta primary">
			Search
		</button>
	</div>
</form>
