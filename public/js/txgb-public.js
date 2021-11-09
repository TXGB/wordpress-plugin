(function ($) {
  'use strict';

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(function () {
    var $dateContainers = $('.js-txgb__has-dates');
    if ($dateContainers.length) {
      $dateContainers.each(function (i, $dateContainer) {
        var $from = $dateContainers.find('input[name="starts_at"]');
        var $to = $dateContainers.find('input[name="ends_at"]');

        $($dateContainer).on('change', $from, function (event) {
          var value = event.target.value;
          var dateValue = new Date(value);

          var formatted = formatDate(dateValue, 'yyyy-MM-dd');

          $to.attr('min', formatted);

          var toValueDate = new Date($to.val());
          if (isBefore(toValueDate, dateValue) || isEqual(toValueDate, dateValue)) {
            $to.val(formatted);
          }
        });
      });
    }
  });
})(jQuery);
