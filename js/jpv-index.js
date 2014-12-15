var genreContents = { };

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
	window.open('https://link.getsync.com/#f=JPV%20Media%20Folder&sz=63E8&s=P3MGCJYFIYDLHH5OTZ2IHUJK5HRXUHKS&i=CCN2KITXPEWKGQO2MLLIFSGZ2OBQPKVJZ&p=CDN4Q7AM5O24H7UOYJU5ESFEN6MA6WYM', '_blank');
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