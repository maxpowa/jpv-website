$(function() {
	resetTooltip();
	var genre = getParameterByName('genre');
	var song = getParameterByName('song');
	selectAndSetContents(genre);
	loadSongInfo(genre, song);
});

function loadSongInfo(genre, name) {
	$('#song-info').html('<b>' + name + '</b> - ' + genre + ' (TODO)');
}