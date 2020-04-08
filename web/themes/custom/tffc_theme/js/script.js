(function($, Drupal, undefined){

	$('.nav-toggle').click(function() {
		if ($('.navbar-collapse').hasClass('in-view')) {
			$('.navbar-collapse').removeClass('in-view');
			$(this).removeClass('active');
		} else {
			$('.navbar-collapse').addClass('in-view');
			$(this).addClass('active');
		}
	});

})(jQuery, Drupal);