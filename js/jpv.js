const colors = {
	'all' : '#00ff84',
	'jpop' : '#ff9c00',
	'vocaloid' : '#ff00de',
	'nightcore': '#00a8ff'
};

$(function() {

	var hash = getHash('all');
	selectAndSetContents(hash);
});

$('.nav li').click(function() {
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