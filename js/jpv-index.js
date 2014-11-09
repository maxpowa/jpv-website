var genreContents = { };
var loading = '';
var currentPlaying;
var volume = 1.0;

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
    var htmlPlayer = player.find('audio')[0];
    htmlPlayer.volume = volume;
    htmlPlayer.onvolumechange = function() {
        volume = arguments[0].target.volume;
    };
	if(player.css('display') == 'none')
		player.show(600);
	resetTooltip();
});

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

setContentsStartCallback = function(genre) {
	getContents(genre);
};

setContentsEndCallback = function(genre) {
	$('html, body').animate({  scrollTop: 0 }, 500);
	$('.nav li').removeClass('active');
	$('.nav li[genre=' +  genre + ']').addClass('active');
	document.title = $('.nav li[genre=' +  genre + ']').attr('title') + ' | JPV Music Library';
	
	if(genre == 'all')
		$('#content-info').show(600);
	else $('#content-info').hide(600);
};