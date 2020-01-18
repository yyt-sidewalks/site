"use strict";

if (!Array.prototype.indexOf) Array.prototype.indexOf = function (Object, max, min) {
	"use strict";

	return function indexOf(member, fromIndex) {
		if (this === null || this === undefined) throw TypeError("Array.prototype.indexOf called on null or undefined");

		var that = Object(this),
		    Len = that.length >>> 0,
		    i = min(fromIndex | 0, Len);
		if (i < 0) i = max(0, Len + i);else if (i >= Len) return -1;

		if (member === void 0) {
			// undefined
			for (; i !== Len; ++i) {
				if (that[i] === void 0 && i in that) return i;
			}
		} else if (member !== member) {
			// NaN
			return -1; // Since NaN !== NaN, it will never be found. Fast-path it.
		} else // all else
			for (; i !== Len; ++i) {
				if (that[i] === member) return i;
			}return -1; // if the value was not found, then return -1
	};
}(Object, Math.max, Math.min);
function getUrlParameter(name) {
	name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
	var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
	var results = regex.exec(location.search);
	return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};
var collecting = false;
$(function () {
	if ($('.navbar-burger').length > 0) {
		// Add a click event on each of them
		$('.navbar-burger').each(function () {
			$(this).click(function () {
				var target = $(this).data('target');
				$(this).toggleClass('is-active');
				$('#' + target).toggleClass('is-active');
			});
		});
	}
	$('#zoommap').click(function () {
		$('html,body').css('overflow', 'hidden');
		$('.map').addClass('fullscreenoverlay').css({ "width": $(window).width(), "height": $(window).height() });
		$('#map').css("height", "100%");
		map._onResize();
		map.setZoom(15);
		if (typeof collectData === 'function' && collecting) {
			map.on('click', collectData);
		}
		$('#shrinkmap').show();
		$('#zoomcontrols').css({ "position": "fixed", "top": "10px", "left": "53px", "z-index": "20000" });
	});
	$('#shrinkmap').click(function () {
		$('html,body').attr('style', '');
		map.closePopup();
		$('#shrinkmap').hide();
		if (typeof collectData === 'function' && collecting) {
			map.off('click', collectData);
		}
		$('.map').removeClass('fullscreenoverlay').attr('style', '');
		$('#map,#zoomcontrols').attr('style', '');
		map._onResize();
		map.setZoom(14);
	});
});