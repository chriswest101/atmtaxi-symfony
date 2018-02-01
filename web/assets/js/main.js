/*
	Projection by TEMPLATED
	templated.co @templatedco
	Released for free under the Creative Commons Attribution 3.0 license (templated.co/license)
*/

(function($) {

	// Breakpoints.
		skel.breakpoints({
			xlarge:	'(max-width: 1680px)',
			large:	'(max-width: 1280px)',
			medium:	'(max-width: 980px)',
			small:	'(max-width: 736px)',
			xsmall:	'(max-width: 480px)'
		});

	$(function() {

		var	$window = $(window),
			$body = $('body');

		// Disable animations/transitions until the page has loaded.
			$body.addClass('is-loading');

			$window.on('load', function() {
				window.setTimeout(function() {
					$body.removeClass('is-loading');
				}, 100);
			});

		// Prioritize "important" elements on medium.
			skel.on('+medium -medium', function() {
				$.prioritize(
					'.important\\28 medium\\29',
					skel.breakpoint('medium').active
				);
			});

	// Off-Canvas Navigation.

		// Navigation Panel.
			$(
				'<div id="navPanel">' +
					$('#nav').html() +
					'<a href="#navPanel" class="close"></a>' +
				'</div>'
			)
				.appendTo($body)
				.panel({
					delay: 500,
					hideOnClick: true,
					hideOnSwipe: true,
					resetScroll: true,
					resetForms: true,
					side: 'left'
				});

		// Fix: Remove transitions on WP<10 (poor/buggy performance).
			if (skel.vars.os == 'wp' && skel.vars.osVersion < 10)
				$('#navPanel')
					.css('transition', 'none');

	});

})(jQuery);

function sendEmail() {

			name = $('#name').val();
			email = $('#email').val();
			message = $('#message').val();
			phone = $('#phone').val();

			var URL = Routing.generate('contact');

			$.post(URL, {
                name:name, email:email, message:message, phone:phone
			}).done(function(response){
                console.log(response);

                if(response['sent'] === false || response['check'] !== true) {
                    $('#errorMessage').show();
                } else {
                    $('#errorMessage').hide();
                }

                if(response['email'] === true || response['emailInvalid'] === true) {
                    $('#email').css("border", "2px solid #FF0000");
                } else {
                    $('#email').css("border", "2px solid #8dcca9");
                }

                if(response['name'] === true) {
                    $('#name').css("border", "2px solid #FF0000");
                } else {
                    $('#name').css("border", "2px solid #8dcca9");
                }

                if(response['message'] === true) {
                    $('#message').css("border", "2px solid #FF0000");
                } else {
                    $('#message').css("border", "2px solid #8dcca9");
                }

                if(response['sent'] === true) {
                    $('#errorMessage').hide();
                    $('#message').css("border", "2px solid #8dcca9");
                    $('#name').css("border", "2px solid #8dcca9");
                    $('#email').css("border", "2px solid #8dcca9");

                    $('#message').val("");
                    $('#name').val("");
                    $('#email').val("");

                    $('#sentMessage').show();
                }
                $("#result").show();
			}).error(function(e){
                console.log(e);
                alert("Oops! Something Went wrong. Why not try again.");
			});
		}
