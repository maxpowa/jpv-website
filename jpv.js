$(function() {
	var hash = getHash('all');
	getContents(hash);
});

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