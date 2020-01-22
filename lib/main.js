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
var collecting = false,
    activestories = [];
var gradient = ["#4be051", "#3cc146", "#2da43b", "#208730", "#347d20", "#407310", "#476900", "#6c6b00", "#996400", "#cc5000", "#ff0000"];
var priorities = { "school_zones": { "colour": "#f2ff00", "size": "24", "opacity": "0.6", "legend": "School Zones" }, "singleside": { "colour": "#3c00ff", "size": "14", "opacity": "0.4", "legend": "Single Side Cleared" }, "doubleside": { "colour": "#ff8000", "size": "14", "opacity": "0.4", "legend": "Both Sides Cleared" }, "priority_3": { "colour": "#42ff65", "size": "4", "opacity": "1", "legend": "Priority 3" }, "priority_2": { "colour": "#02e2f2", "size": "4", "opacity": "1", "legend": "Priority 2" }, "priority_1": { "colour": "#ff4545", "size": "4", "opacity": "1", "legend": "Priority 1" } };
var obtypes = ['hydrant', 'pole', 'light', 'bridge', 'sign', 'traffic', 'other', 'parking'];
var obtypeicons = {};
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
	$('.area').each(function () {
		$(this).css({ "background-color": priorities[$(this).data('area')].colour, "opacity": priorities[$(this).data('area')].opacity });
	});
	$('#zoommap,#addobstruction').click(function () {
		$('html,body').css('overflow', 'hidden');
		$('.map').addClass('fullscreenoverlay').css({ "width": $(window).width(), "height": $(window).height() });
		$('#map').css("height", "100%");
		map._onResize();
		map.setZoom(15);
		if (typeof collectData === 'function' && collecting) {
			map.on('click', collectData);
		}
		$('#shrinkmap,#zoommsg').show();
		$('#zoomcontrols,#zoommsg').css({ "position": "fixed", "top": "10px", "left": "53px", "z-index": "20000" });
	});
	$('#shrinkmap').click(function () {
		$('html,body').attr('style', '');
		map.closePopup();
		$('#shrinkmap,#zoommsg').hide();
		if (typeof collectData === 'function' && collecting) {
			map.off('click', collectData);
		}
		$('.map').removeClass('fullscreenoverlay').attr('style', '');
		$('#map,#zoomcontrols,#zoommsg').attr('style', '');
		map._onResize();
		map.setZoom(14);
	});
	var d = getUrlParameter('do');
	if (d != '') {
		setTimeout(function () {
			$('#' + d).click();
		}, 1000);
	}
});
function makestories(c) {
	if (activestories.length < c) {
		c = activestories.length;
	}
	$('#stories').empty();
	var randomkeys = [];
	var i = 0;
	do {
		var key = Math.floor(Math.random() * activestories.length);
		if (!randomkeys.includes(key)) {
			randomkeys.push(key);
			i++;
		}
	} while (i < c);
	$.each(randomkeys, function (k, v) {
		var thisstory = activestories[v];
		var dateinfo = [];
		if (thisstory['Date'] !== '') {
			dateinfo.push(thisstory['Date']);
		} else {
			dateinfo.push(thisstory['Time of Day']);
			dateinfo.push(thisstory['Time of Year']);
			dateinfo.push(thisstory['Year']);
		}
		var description = thisstory['Description'].substring(0, 300);
		$('#stories').append('<li class="storybox"><div class="message"><div class="message-body"><h5>' + thisstory['You were'] + ' - ' + dateinfo.join(' ') + '</h5><h6>' + thisstory['Tell us who you are'] + ' &#183; ' + thisstory['Timestamp'] + '</h6><p>' + description + '</p><p><a href="https://yyt-sidewalks.github.io/site/stories/?story=' + thisstory['Identifier'] + '">Read More</a></p></div></div></li>');
	});
}
function getobicontype(t) {
	var it;
	switch (t) {
		case "Fire Hydrant":
			it = "hydrant";
			break;
		case "Telephone or Power Pole (pole and wires)":
			it = "pole";
			break;
		case "Street Light":
			it = "light";
			break;
		case "Bridge with Narrow sidewalks":
			it = "bridge";
			break;
		case "Street Sign (parking, stop sign, etc)":
			it = "sign";
			break;
		case "Traffic Light":
			it = "traffic";
			break;
		case "Other Obstruction":
			it = "other";
			break;
		case "Parking Metre":
			it = "parking";
			break;
	}
	return it;
}