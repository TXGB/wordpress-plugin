<?php

/**
 * A Product instance.
 *
 * Represents a Service Product entity.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_Product
{

	public $id;
	public $obx_id;
	public $service_id;

	public $name;
	public $description;

	public $starts_at;
	public $ends_at;
	public $guests = 0;

	public $images = array();
	public $features = array();

	public $daily_rates = array();
	public $lowest_rate;
	public $highest_rate;
	public $average_rate;
	public $total_price = 0;
	public $total_price_formatted = '&pound;0.00';

	public $use_external_payment = false;
	public $use_external_booking = false;
	public $on_request_only = false;

	static function make_from_response($entity)
	{
		$self = new static;

		$self->id = $entity->Id;
		$self->service_id = $entity->ParentId;

		$self->name = $entity->Name;
		$self->description = $entity->LongDescription;

		$self->category = strtolower($entity->IndustryCategory);

		if (property_exists($entity, 'Availability')) {
			$lowest_rate = TXGB_Object_Rate::make($entity->Availability->Calendar->LowestRate);
			$highest_rate = $lowest_rate;

			$daily_rates = $entity->Availability->Calendar->DailyRates;
			$daily_rate_model = is_array($daily_rates->DailyRateModel)
				? $daily_rates->DailyRateModel
				: array($daily_rates->DailyRateModel);


			$available_days = 0;
			$total_price = 0;
			foreach ($daily_rate_model as $raw_day_rate) {
				$day_price = !property_exists($raw_day_rate, 'Rate') ? null : TXGB_Object_Rate::make($raw_day_rate->Rate);

				$self->daily_rates[] = (object)array(
					'date' => new DateTime($raw_day_rate->Date),
					'is_available' => $raw_day_rate->IsAvailable,
					'rate' => $day_price,
				);

				if ($raw_day_rate->IsAvailable) {
					$available_days++;

					$highest_rate = $day_price->value > $highest_rate->value ? $day_price : $highest_rate;
					$total_price += $day_price->value;
				}
			}
			$average_rate = $available_days > 0 ? $total_price / $available_days : 0;

			$self->average_rate = TXGB_Object_Rate::make($average_rate / 100);
			$self->highest_rate = $highest_rate;
			$self->lowest_rate = $lowest_rate;
			$self->total_price = TXGB_Object_Rate::make($total_price / 100);
		}

		if (property_exists($entity, 'Images')) {
			$raw_images = $entity->Images;
			if (is_object($raw_images) && property_exists($raw_images, 'Image')) {
				$raw_images = $raw_images->Image;
			} else {
				$raw_images = [];
			}

			if (!is_array($raw_images)) {
				$raw_images = array($raw_images);
			}

			foreach ($raw_images as $raw_image) {
				$self->images[] = TXGB_Object_Image::make_from_response($raw_image);
			}
		}

		if (property_exists($entity, 'Features') && property_exists($entity->Features, 'Feature')) {
			$raw_features = $entity->Features->Feature;
			$raw_features = is_array($raw_features) ? $raw_features : array($raw_features);

			foreach ($raw_features as $raw_feature) {
				$self->features[$raw_feature->Id] = $raw_feature->Name;
			}
		}

		$self->use_external_payment = !property_exists($entity, 'UsesExternalPaymentGateway') ? null : $entity->UsesExternalPaymentGateway;
		$self->use_external_booking = !property_exists($entity, 'UsesExternalBookingSystem') ? null : $entity->UsesExternalBookingSystem;
		$self->on_request_only = property_exists($entity, 'OnRequestOnly') ? $entity->OnRequestOnly : false;

		return $self;
	}

	public function setAvailabilityFromProvider($availability)
	{
		$type = property_exists($availability, 'Days') ? 'days' : 'nights';
		$periods = $type == 'days' ? $availability->Days : $availability->Nights;
		$periods = !is_array($periods) ? [$periods] : $periods;

		foreach ($periods as $period) {
			$day_price = TXGB_Object_Rate::make($period->price);

			$start_date = new DateTime($period->start_date);
			$end_date = new DateTime($period->finish_date);

			$this->starts_at = $start_date;
			$this->ends_at = $end_date;
			$this->nights = $period->$type;
			$this->total_price = $day_price;

			$this->daily_rates[] = (object)[
				'date' => $start_date,
				'is_available' => true,
				'rate' => $day_price,
			];
		}
	}

	public function output_booking_form()
	{
		$availability_vars = apply_filters('txgb_filter_product_availability_params', null, null);

		$options = get_option('txgb_options', []);
		$shortname = array_key_exists('shortname', $options) ? $options['shortname'] : false;
		$return_page_id = array_key_exists('page_id_success', $options) ? $options['page_id_success'] : false;
		$error_page_id = array_key_exists('page_id_error', $options) ? $options['page_id_success'] : false;

		$adults = $availability_vars->adults;
		$children = $availability_vars->children;
		$concessions = $availability_vars->concessions;

		$return_url = $return_page_id ? get_page_link($return_page_id) : home_url();
		$error_url = $error_page_id ? get_page_link($error_page_id) : home_url();

		$payment_url_params = array(
			'exl_dsn' => $shortname,  // Provider short name
			'exl_bku' => $return_url, // Back URL
			'exl_eu'  => $error_url,  // Error return URL
			'exl_lng' => 'en-GB',     // Language & currency settings
		);

		$base_url = txgb_is_production() ? 'https://book.txgb.co.uk/v4/Services/Injection.aspx' : 'https://uatbook.txgb.co.uk/v4/Services/Injection.aspx';
		$payment_url = $base_url . '?' . http_build_query($payment_url_params);
?>
		<div class="txgb__availability-product__booking_actions">
			<?php
			if ($this->category == 'accommodation') :
				$booking_config = json_encode(
					array(
						'Products' => array(
							(object) array(
								'ProductId'  => $this->id,
								'TotalPrice' => $this->total_price->raw / 100,
								'Commence'   => $this->starts_at->format('Y-m-d H:i:s'),
								'Conclude'   => $this->ends_at->format('Y-m-d H:i:s'),
								'Pax'        => (object)array(
									'Adults'   => $adults,
									'Children' => $children,
									'Seniors'  => $concessions,
								),
							),
						),
					)
				);
			?>
				<form action="<?php echo $payment_url; ?>" method="POST" class="txgb__availability-product__booking_action">
					<input type="hidden" name="Type" value="BookingInjection" />
					<input type="hidden" name="Data" value="<?php echo htmlspecialchars($booking_config); ?>" />

					<button type="submit" class="txgb__availability-product__book-button">
						Book Now
					</button>
				</form>

				<?php
			else :
				foreach (array_slice($this->daily_rates, 0, 7) as $daily_rate) :
					if (!$daily_rate->is_available) :
				?>
						<div class="txgb__availability-product__booking_action">
							<span type="submit" class="txgb__availability-product__book-button txgb__availability-product__book-button--disabled">
								<time class="txgb__availability-product__book-button__date" datetime="<?php echo $this->starts_at->format(DateTimeInterface::W3C) ?>">
									<?php echo $daily_rate->date->format('M jS H:i:s') ?>
								</time>
								<span class="txgb__availability-product__book-button__text">Unavailable</span>
							</span>
						</div>
					<?php
					else :
						$starts_at = $daily_rate->date;
						$ends_at = (new DateTime($starts_at->format('Y-m-d H:i:s')))->add(new DateInterval('P1D'))->format('Y-m-d H:i:s');
						$booking_config = json_encode(
							array(
								'Products' => array(
									(object) array(
										'ProductId'  => $this->id,
										'TotalPrice' => $daily_rate->rate->value / 100,
										'Commence'   => $starts_at->format('Y-m-d H:i:s'),
										'Conclude'   => $ends_at,
										'Pax'        => (object)array(
											'Adults'   => $adults,
											'Children' => $children,
											'Seniors'  => $concessions,
										),
									),
								),
							)
						);
					?>
						<form action="<?php echo $payment_url; ?>" method="POST" class="txgb__availability-product__booking_action">
							<input type="hidden" name="Type" value="BookingInjection" />
							<input type="hidden" name="Data" value="<?php echo htmlspecialchars($booking_config); ?>" />

							<button type="submit" class="txgb__availability-product__book-button">
								<time class="txgb__availability-product__book-button__date" datetime="<?php echo $starts_at->format(DateTimeInterface::W3C) ?>">
									<?php echo $daily_rate->date->format('M jS H:i') ?>
								</time>
								<span class="txgb__availability-product__book-button__text">Book Now</span>
								<span class="txgb__availability-product__book-button__price"><?php echo $daily_rate->rate->formatted ?></span>
							</button>
						</form>
			<?php
					endif;
				endforeach;
			endif;
			?>
		</div>
<?php
	}
}
