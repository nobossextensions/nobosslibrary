// Lazyload images
$.fn.lazyloadImages = function() {
	var lazyloadEls = $(this);

	lazyloadEls.bind('enterviewport', function() {
		var el = $(this),
			src = el.attr('data-high-resolution-src'),
			image;

		if (src) {
			image = new Image();
			
			image.onload = function() {
				el.attr('src', src).removeAttr('data-high-resolution-src');
			};
			
			image.src = src;
			if (image.complete) {
				el.attr('src', src).removeAttr('data-high-resolution-src');
			}
		}
	}).bullseye();
};

// Lazyload
$.fn.lazyload = function(options) {

	var lazyloadEls = $(this),
		callbacks = options.callbacks,
		lazyloadCallback;

	lazyloadCallback = function() {
		var el = $(this),
			newEl,
			callback;

		callback = function() {
			var arrCallbacks,
				index,
				cb;

			if (el.attr('data-callback') && !el.attr('data-loaded')) {
				el.attr('data-loaded', '1');
				arrCallbacks = el.attr('data-callback').split(' ');

				for (index = 0; index < arrCallbacks.length; index++) {
					cb = arrCallbacks[index];
					if (callbacks[cb]) {
						callbacks[cb](newEl, el.data());
					}
				}
			}
		};

		if (!el.attr('data-url')) {
			newEl = el;
			callback();

			return;
		}

		$.ajax({
			url: el.attr('data-url')
		}).done(function(html) {
			newEl = $(html);
			el.after(newEl.hide());
			el.remove();
			newEl.fadeIn();

			callback();
		});
	};

	lazyloadEls.bind('enterviewport', lazyloadCallback).bullseye();
};