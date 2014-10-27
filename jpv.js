const colors = {
	'all' : '#259b24',
	'jpop' : '#ffc107',
	'vocaloid' : '#e91e63',
	'nightcore': '#5677fc'
};

var loading = '';

$(function() {
	var hash = getHash('all');
	loading = $('#song-list').html();
	selectAndSetContents(hash);
});

$('.nav li').click(function() {
	$('#song-list').html(loading);
	selectAndSetContents($(this).attr('genre'));
});

function selectAndSetContents(genre) {
	getContents(genre);
	$('.nav li').removeClass('active');
	$('.nav li[genre=' +  genre + ']').addClass('active');
	
	$('#the-navbar').animate({
		'background-color': colors[genre],
	});
}

function getContents(genre) {
	$.get('./api/v1/list.php', {
		'genre': genre,
		'format': 'html'
	}, function(resp) {
		var contents = resp.message;
		if(resp.status != 200)
			contents = "Status Code " + resp.status + ": " + contents;
		
		contents = contents.replace(/song-play-button/g, 'song-play-button glyphicon glyphicon-play-circle').replace(/song-download-button/g, 'song-download-button glyphicon glyphicon-download');
		$('#song-list').html(contents);
	});
}

function getHash(fallback) {
	var hash = window.location.hash.substring(1);
	return hash.length == 0 ? fallback : hash;
}

String.prototype.startsWith = function(str) {
	return this.indexOf(str) == 0;
}