(function ($, Drupal) {

  var initialized;

  /**
   * Init the countdown method
   */
  function initCountdown() {
    // only initialize it once
    if (initialized) {
      return;
    }
    initialized = true;

    // find countdown fields
    findCountdownFields();
  }

  /**
   * Looks for all the countdown fields and attaches the countdown
   */
  function findCountdownFields() {
    // find the fields by class name
    var $countdownField = $('.field-points');

    // loop through each field
    // and if we find the inner class of 'number'
    // attach the countdown timer
    $countdownField.each(function () {
      var $this = $(this);

      var $elem = $this.find('.number');

      if ($elem.length > 0) {

        var start = $this.data('start');
        var end = $this.data('end');
        var speed = $this.data('interval');
        createCountdown($elem, start, end, speed);

      }
    });
  }


  /**
   * The countdown method keeps track of the current time
   * and updates the score in real time.
   *
   * @param elm
   * @param start
   * @param end
   * @param speed
   */
  function createCountdown(elm, start, end, speed) {
    var counter = start;

    if (counter <= end) {
      return;
    }

    var interval = setInterval(function () {
      counter--;
      elm.html(counter);
      if (counter <= end) {
        clearInterval(interval);
      }
    }, speed);
  }


  Drupal.behaviors.tffcCountdown = {
    attach: function (context, settings) {
      initCountdown();
    }
  };
})(jQuery, Drupal);

