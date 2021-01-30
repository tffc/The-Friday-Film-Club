(function($, Drupal, undefined){

	toggleNav();
	floatingLabels();

	function toggleNav() {
		$('.nav-toggle').click(function() {
			confetti({
				particleCount: 100,
				startVelocity: 30,
				spread: 360,
				gravity: 0.5,
				colors: ['#e85da4', '#9cf5fd', '#4E35F2', '#dc2ea5', '#03f5a0'],
				shapes: ['circle', 'square']
			});
			console.log('confetti');
			if ($('.navbar-collapse').hasClass('in-view')) {
				$('.navbar-collapse').removeClass('in-view');
				$(this).removeClass('active');
			} else {
				$('.navbar-collapse').addClass('in-view');
				$(this).addClass('active');
			}
		});
	}
	

	function floatingLabels() {
		var formFields = $('.user-register-form .form-control');

		formFields.focus(function() {
			$(this).prev('.control-label').addClass('focus');
		});

		formFields.blur(function() {
			$(this).prev('.control-label').removeClass('focus');
		});
  
		formFields.each(function() {
			console.log('field');
			var field = $(this);
			var input = field.find('input');
			var label = field.prev('.control-label');
			console.log(field);
			console.log(label);

			function checkInput() {
			  var valueLength = field.val().length;
			  
			  if (valueLength > 0 ) {
			    label.addClass('focus');
			    console.log('focus');
			  } else {
			        label.removeClass('focus')
			  }
			}

			field.change(function() {
			  checkInput();
			  console.log('changes');
			});

			field.change(function() {
			  checkInput();
			  console.log('changes');
			});

			field.change(function() {
			  checkInput();
			  console.log('changes');
			});
		});
	}

})(jQuery, Drupal);