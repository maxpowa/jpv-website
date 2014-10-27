const colors = {
	'all' : '#259b24',
	'jpop' : '#ffc107',
	'vocaloid' : '#e91e63',
	'nightcore': '#5677fc'
};

var genreContents = { };
var loading = '';

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

function selectAndSetContents(genre) {
	getContents(genre);
	$('.nav li').removeClass('active');
	$('.nav li[genre=' +  genre + ']').addClass('active');
}

function animateNavbar(genre) {
	$('#the-navbar').animate({
		'background-color': colors[genre],
	});
	$('html, body').animate({  scrollTop: 0 }, 500);
}

function getContents(genre) {
	if(genre in genreContents) {
		$('#song-list').html(genreContents[genre]);
		animateNavbar(genre);
	} else $.get('./api/v1/list.php', {
		'genre': genre,
		'format': 'html'
	}, function(resp) {
		var contents = resp.message;
		if(resp.status != 200)
			contents = "Status Code " + resp.status + ": " + contents;
		
		animateNavbar(genre);
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