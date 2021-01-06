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
	return results === null ? false : decodeURIComponent(results[1].replace(/\+/g, ''));
};
function isOdd(num) {
	return num % 2;
}
function isEmpty(value) {
	return typeof value == 'string' && !value.replace(/^\s+|\s+$/gm, '') || typeof value == 'undefined' || value === null || typeof value == 'string' && value == '0' || value == 0;
}
var collecting = false,
    activestories = [];
var gradient = ["#4be051", "#3cc146", "#2da43b", "#208730", "#347d20", "#407310", "#476900", "#6c6b00", "#996400", "#cc5000", "#ff0000"];
var priorities = { "priority_4": { "colour": "#4d0db5", "size": "6", "opacity": "1", "legend": "Priority 4" },"priority_3": { "colour": "#00b822", "size": "6", "opacity": "1", "legend": "Priority 3" }, "priority_2": { "colour": "#02b2f2", "size": "6", "opacity": "1", "legend": "Priority 2" }, "priority_1": { "colour": "#c40000", "size": "6", "opacity": "1", "legend": "Priority 1" }, "priority_1a": { "colour": "#ff962b", "size": "6", "opacity": "1", "legend": "Priority 1a" } };
var storyoptions = { "type": ["Select what you were doing", "Walking on a sidewalk", "Walking in the road", "Using a crosswalk", "At an intersection with lights", "Intersection without lights", "Crossing the road", "Other"], "timeofyear": ["Select a Time of Year", "Winter", "Spring", "Fall", "Summer"], "timeofday": ["Select a Time of Day", "Morning", "Afternoon", "Evening", "Late Night"], "source": ["Self-Report", "Reporting about someone else", "News Article or Website"] };
var wards = { "ward_5": { "colour": "#3c00ff", "size": "2", "opacity": "0.5", "legend": "Ward 5" }, "ward_4": { "colour": "#B85300", "size": "2", "opacity": "0.5", "legend": "Ward 4" }, "ward_3": { "colour": "#00AD1D", "size": "4", "opacity": "0.5", "legend": "Ward 3" }, "ward_2": { "colour": "#0262A7", "size": "2", "opacity": "0.5", "legend": "Ward 2" }, "ward_1": { "colour": "#E0001A", "size": "2", "opacity": "0.5", "legend": "Ward 1" } };
var obtypes = ['hydrant', 'pole', 'light', 'bridge', 'sign', 'traffic', 'parking', 'stairs', 'other'];
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
	$('.priorityarea').each(function () {
		$(this).css({ "background-color": priorities[$(this).data('area')].colour, "opacity": priorities[$(this).data('area')].opacity });
	});
	$('.wardarea').each(function () {
		$(this).css({ "background-color": wards[$(this).data('area')].colour, "opacity": wards[$(this).data('area')].opacity });
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
		$('.navbar').hide();
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
		$('.navbar').show();
		$('.map').removeClass('fullscreenoverlay').attr('style', '');
		$('#map,#zoomcontrols,#zoommsg').attr('style', '');
		map._onResize();
		map.setZoom(14);
	});
	var showlegend = false;
	$('#legendctl').click(function () {
		$('#legend').toggle();
		var legendctlicon = $('#legendctl').attr('src');
		var legendctlaction = '';
		if (showlegend) {
			legendctlicon = legendctlicon.replace('collapse', 'expand');
			legendctlaction = 'Expand Legend';
			showlegend = false;
		} else {
			legendctlicon = legendctlicon.replace('expand', 'collapse');
			legendctlaction = 'Collapse Legend';
			showlegend = true;
		}
		$('#legendctl').attr('src', legendctlicon).attr('title', legendctlaction);
	});
	var d = getUrlParameter('do');
	if (d != '') {
		setTimeout(function () {
			$('#' + d).click();
		}, 1000);
	}
});
function makestories(c,s='desc') {
	if (activestories.length < c) {
		c = activestories.length;
	}
	var randomkeys = [],
		i = 0,dostories=[];
	if(s=='random'){
		do {
			var key = Math.floor(Math.random() * activestories.length);
			if (!randomkeys.includes(key)) {
				randomkeys.push(key);
				i++;
			}
		} while (i < c);
		$.each(randomkeys, function (k, v) {
			dostories.push(activestories[v]);
		});
	}else{
		dostories=activestories;
	}
	
	var l = Math.ceil(c / 3),
	    col = 1,
	    p = 0;
	$.each(dostories, function (k, v) {
		p++;
		var thisstory = v,
		    description = '';
		var dateinfo = [];
		if (thisstory.date !== '0000-00-00') {
			dateinfo.push(thisstory.date);
		} else {
			if (thisstory.timeofday) {
				dateinfo.push(storyoptions.timeofday[thisstory.timeofday]);
			}
			if (thisstory.timeofyear) {
				dateinfo.push(storyoptions.timeofyear[thisstory.timeofyear]);
			}
			dateinfo.push(thisstory.year);
		}
		if (thisstory.description !== '') {
			description = thisstory.description.substring(0, 300);
		}
		$('#stories_' + String(col)).append('<div class="storybox message" data-identifier="'+thisstory.identifier+'" data-vehicles="'+thisstory.vehicles+'" data-weather="'+thisstory.weather+'" data-timeofday="'+thisstory.timeofday+'" data-timeofyear="'+thisstory.timeofyear+'" data-modified="'+thisstory.modified+'"><div class="message-body"><h5>' + storyoptions.type[thisstory.type] + ' - ' + dateinfo.join(' ') + '</h5><h6>' + thisstory.who + ' &#183; ' + thisstory.nicedate + '</h6><p>' + description + '</p><p><a href="https://www.yytsidewalks.info/stories/?story=' + thisstory.identifier + '">Read More</a></p></div></div>');
		if (p >= l) {
			col++;
			p = 0;
		}
	});
}
function getobicontype(t) {
	var it;
	switch (t) {
		case 1:
			it = "hydrant";
			break;
		case 2:
			it = "pole";
			break;
		case 3:
			it = "light";
			break;
		case 4:
			it = "bridge";
			break;
		case 5:
			it = "sign";
			break;
		case 6:
			it = "traffic";
			break;
		case 7:
			it = "parking";
			break;
		case 8:
			it = "stairs";
			break;
		case 9:
			it = "other";
			break;
	}
	return it;
}