(function ($) {
	"use strict";
	var JobPortalFancyHeadingHandler = function ($scope) {
		var $element = $scope.find(".jobportal-fancy-heading");

		var options_default = {
			animationDelay: 4000,
			barAnimationDelay: 3000,
			typingSpeed: 200,
			typingDelay: 2000,
			typingLoop: false,
			typingCursor: false,
		};

		$element.each(function () {
			var $this = $(this);
			var options = $this.data("settings-options");
			var animationDelay = options.animationDelay;
			options = $.extend({}, options_default, options);
			options.barAnimationDelay = options.animationDelay;

			if (options.animationDelay < 3000) {
				options.barWaiting = options.animationDelay * (10 / 100);
			}
			if (options.animationDelay >= 3000) {
				options.barWaiting = options.animationDelay - 3000;
			}

			var duration = animationDelay;

			if ($this.hasClass("loading-bar")) {
				duration = options.barAnimationDelay;
				setTimeout(function () {
					$this.find(".jobportal-fancy-heading-animated").addClass("is-loading");
				}, options.barWaiting);
			}

			if ($this.hasClass("jobportal-fancy-heading-typing")) {
				var txt = $this.data("text");
				$this.find(".jobportal-fancy-heading-animated").typed({
					strings: txt,
					typeSpeed: options.typingSpeed,
					backSpeed: 0,
					startDelay: 300,
					backDelay: options.typingDelay,
					showCursor: options.typingCursor,
					loop: options.typingLoop,
				});
			} else {
				setTimeout(function () {
					hideWord($this.find(".jobportal-fancy-heading-show").eq(0), options);
				}, duration);
			}
		});

		function hideWord($word, options) {
			var nextWord = takeNext($word);
			if (
				$word
					.parents(".jobportal-fancy-heading")
					.hasClass("jobportal-fancy-heading-loading")
			) {
				$word.parent(".jobportal-fancy-heading-animated").removeClass("is-loading");
				switchWord($word, nextWord);
				setTimeout(function () {
					hideWord(nextWord, options);
				}, options.barAnimationDelay);
				setTimeout(function () {
					$word.parent(".jobportal-fancy-heading-animated").addClass("is-loading");
				}, options.barWaiting);
			} else {
				switchWord($word, nextWord);
				setTimeout(function () {
					hideWord(nextWord, options);
				}, options.animationDelay);
			}
		}

		function takeNext($word) {
			return !$word.is(":last-child")
				? $word.next()
				: $word.parent().children().eq(0);
		}

		function switchWord($oldWord, $newWord) {
			$oldWord
				.removeClass("jobportal-fancy-heading-show")
				.addClass("jobportal-fancy-heading-hidden");
			$newWord
				.removeClass("jobportal-fancy-heading-hidden")
				.addClass("jobportal-fancy-heading-show");
		}
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-fancy-heading.default",
			JobPortalFancyHeadingHandler
		);
	});
})(jQuery);
