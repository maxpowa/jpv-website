$(function() {
    resetTooltip();
    var genre = getParameterByName('genre');
    var song = getParameterByName('song');
    selectAndSetContents(genre);
    loadSongInfo(genre, song);
    resetTooltip();

    tintPlaying = false;
});

function getBool(val){
    var num = +val;
    return !isNaN(num) ? !!num : !!String(val).toLowerCase().replace(!!0,'');
}

function loadSongInfo(genre, name) {
    if (name.indexOf('.mp3') < 0) name += '.mp3';
    $.get('../api/v1/song.php', $.param({
        file: name
    }), function(data) {
        var html = '<div class="song-box info-box">';
        html += '<div class="song-image"><img src="../api/v1/art.php?file=' + data['href'] + '"></div><div class="song-info">';
        html += '<div class="song-title">' + data['title'] + '</div><br>';
        html += '<div class="song-artist">' + data['artist'];
        if(data['artist'] != data['album_artist'])
            html += ' (' + data['album_artist'] + ')';
        html += '</div><br><div class="song-artist">' + data['album'] + '</div><br>';
        html += '<div class="song-length">' + data['length'] + 'm - '  + data['genre'] + '</div><br>';

        html += '<div class="song-buttons"><div class="song-button song-play-button glyphicon glyphicon-play-circle"></div><a href="../media/' + data['href'] + '" download><div class="song-button song-download-button glyphicon glyphicon-download"></div></a></div></div>';
        html += '<hr><div class="song-little-info"><b>Bitrate: ' + ((data['bitrate'] / 1000) | 0) + 'kbps (' + data['bitrate_mode'] + ') | Size: ' + (data['size'] / 1048576).toFixed(2) + 'MB</b>';
        html += '<br>© Credit for audio featured on this site goes to the original artists.</div></div>';

        $('#song-info').html(html);
        document.title = data['artist'] + ' - ' + data['title'] + ' | Song Info | JPV Music Library';

        var autoplay = getParameterByName('autoplay');
        if (getBool(autoplay)) {
            var playBtn = $(document.getElementsByClassName('song-play-button')[0]);
            showPlayerFrom(playBtn);
        }
    });
}
