const colors = {
	'all' : '#259b24',
	'jpop' : '#ffc107',
	'vocaloid' : '#e91e63',
	'nightcore': '#5677fc'
};

var genreContents = { };
var loading = '';
var currentPlaying;

var hamburgerCss = { };

$(function() {
	resetTooltip();
	var hash = getHash('all');
	loading = $('#song-list').html();
	selectAndSetContents(hash);
});

$('.nav li').click(function() {
	$('#song-list').html(loading);
	selectAndSetContents($(this).attr('genre'));
});

$(document).on('click', '.song-play-button', function() {
	var dl = $(this).parent('div').find('a').attr('href');	
	var parent = $(this).closest('.song-box');
	var title = parent.find('.song-title').text();
	var player = $('#player');	
	
	var artist = parent.find('.song-artist').text();
	var albumArtist = parent.find('.song-artist').attr('data-original-title');
	var displayArtist = artist;
	if(artist != albumArtist)
		displayArtist = artist + " / " + albumArtist;
		
	var audio = '<span class="player-song-name" data-toggle="tooltip" title="' + displayArtist + '">' + title + '</span><br><audio controls="controls" autoplay="autoplay"><source src="' + dl + '" type="audio/mpeg"></audio>';
	
	if(currentPlaying != undefined)
		currentPlaying.animate({
			'background-color': '#fff',
		});
	
	parent.animate({
		'background-color': '#dff',
	});
	currentPlaying = parent;
	
	player.html(audio);
	if(player.css('display') == 'none')
		player.show(600);
	resetTooltip();
});

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

$('#btsync-key').click(function() {
	$('#btsync-hide-button').show(600);
	$('#btsync-hidden-key').show(600);
	$('#btsync-hidden-qr').hide(600);
});

$('#btsync-installer').click(function() {
	window.open('https://link.getsync.com/#f=JPV%20Media%20Folder&sz=35E8&s=P3MGCJYFIYDLHH5OTZ2IHUJK5HRXUHKS&i=CVSZIPOSYQ5MKBMPEZ5DKKRV3MVSG35VR&p=CDXRVCURLM4XKYRC4SJH4BFM7JJEMVEX', '_blank');
});

$('#btsync-qr').click(function() {
	$('#btsync-hide-button').show(600);
	$('#btsync-hidden-qr').show(600);
	$('#btsync-hidden-key').hide(600);
});

$('#btsync-hide-button').click(function() {
	$('#btsync-hide-button').hide(600);
	$('#btsync-hidden-key').hide(600);
	$('#btsync-hidden-qr').hide(600);
});

function selectAndSetContents(genre) {
	getContents(genre);
	$('.synced-bgcolor').animate({
		'background-color': colors[genre],
	});
	$('.synced-color').animate({
		'color': colors[genre],
	});

	$('html, body').animate({  scrollTop: 0 }, 500);
	$('.nav li').removeClass('active');
	$('.nav li[genre=' +  genre + ']').addClass('active');
	document.title = $('.nav li[genre=' +  genre + ']').attr('title') + ' | JPV Music Library';
	
	if(genre == 'all')
		$('#content-info').show(600);
	else $('#content-info').hide(600);
}

function getContents(genre) {
	if(genre in genreContents) {
		$('#song-list').html(genreContents[genre]);
		resetTooltip();
	} else $.get('./api/v1/list.php', {
		'genre': genre,
		'format': 'html'
	}, function(resp) {
		var contents = resp.message;
		$('#song-list').html(contents);
		genreContents[genre] = contents;
		resetTooltip();
	});
}

function resetTooltip() {
	$('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
}

function getHash(fallback) {
	var hash = window.location.hash.substring(1);
	return hash.length == 0 ? fallback : hash;
}

String.prototype.startsWith = function(str) {
	return this.indexOf(str) == 0;
}