const colors = {
	'all' : '#259b24',
	'jpop' : '#ffc107',
	'vocaloid' : '#e91e63',
	'nightcore': '#5677fc'
};

var setContentsStartCallback;
var setContentsEndCallback;

function getHash(fallback) {
	var hash = window.location.hash.substring(1);
	return hash.length == 0 ? fallback : hash;
}

$(".navbar-toggle").click(function() {
	if($("#icon-bar0").css("width") == "12px") {
		$("#icon-bar0").animate({
			"textIndent": "0",
			"width": "22px",
			"margin-top": "0px",
			"margin-left": "0px",
			"margin-bottom": "-0px"
		}, {
			duration: 300, 
			step: applyRotation
		});
		
		$("#icon-bar1").animate({
			"width": "22px",
		}, {
			duration: 300, 
			step: applyRotation
		});
	
		$("#icon-bar2").animate({
			"textIndent": "0",
			"width": "22px",
			"margin-left": "0px",
			"margin-top": "4px"
		}, {
			duration: 300, 
			step: applyRotation
		});
	} else {
		$("#icon-bar0").animate({
			"textIndent": "-45",
			"width": "12px",
			"margin-top": "2px",
			"margin-left": "-2px",
			"margin-bottom": "-2px"
		}, {
			duration: 300, 
			step: applyRotation
		});

		$("#icon-bar1").animate({
			"width": "18px",
		}, {
			duration: 300, 
			step: applyRotation
		});
		
		$("#icon-bar2").animate({
			"textIndent": "45",
			"width": "12px",
			"margin-left": "-2px",
			"margin-top": "2px"
		}, {
			duration: 300, 
			step: applyRotation
		});
	}
});

function applyRotation(now, tween) {
	if(tween.prop == "textIndent")
		$(this).css({ "transform": "rotate(" + now + "deg)" });
}

function resetTooltip() {
	$('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
}

function selectAndSetContents(genre) {
	if(setContentsStartCallback != undefined)
		setContentsStartCallback(genre);

	$('.synced-bgcolor').animate({
		'background-color': colors[genre],
	});
	$('.synced-color').animate({
		'color': colors[genre],
	});
	
	if(setContentsEndCallback != undefined)
		setContentsEndCallback(genre);
}

String.prototype.startsWith = function(str) {
	return this.indexOf(str) == 0;
}

// Source: http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript?page=1&tab=active#tab-top
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}