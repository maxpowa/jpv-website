<?php
    /////////////////////////////////////////////////////////////////////
    // song.php - displays song id3 information (cached if available)  //
    //   Caching uses MD5 as the identifier, so any change to a file   //
    //   will cause a new cache to be created.                         //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/song.php?file={filename}                          //
    //   {filename} is a file in ../../media/                          //
    // Response:                                                       //
    //   JSON data corresponding to the song information               //
    /////////////////////////////////////////////////////////////////////
    
    # Requires getid3() lib
    require_once('../../getid3/getid3.php');
    
    //TODO: Move these to a config file
    $MEDIA_DIR = '../../media/';
    $CACHE_DIR = '../../cache/' . 'tags/';
     
    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath( $MEDIA_DIR . strip_tags( trim( $_GET[ 'file' ] ) ) );
    } else {
        echo '{"status":"400", "message":"file parameter not provided"}';
        return;
    }
    
    if ( file_exists( $filename ) ) {
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $CACHE_DIR . $FILE_MD5 . '.json';
        if ( file_exists( $CACHE_FILE ) ) {
            header('Content-Type: application/json');
            $size= filesize( $CACHE_FILE );
            header("Content-Length: $size bytes");
            readfile( $CACHE_FILE );
        } else {
            $getID3 = new getID3;
            #$getID3->option_tag_id3v2 = true; # We don't /need/ to force tags to be id3v2
            $filetags = $getID3->analyze($filename);
            $songname = $filetags['tags']['id3v2']['title'][0];
            $artist = $filetags['tags']['id3v2']['artist'][0];
            $albumartist = $filetags['tags']['id3v2']['band'][0];
            $album = $filetags['tags']['id3v2']['album'][0];
            $bitrate = floor((int) $filetags['audio']['bitrate'] / 1000);
            $bmode = $filetags['audio']['bitrate_mode'];
            $genre = $filetags['tags']['id3v2']['genre'][0];
            $sizeraw = $filetags['filesize'];
            $bitrateraw = $filetags['audio']['bitrate'];
            $len = @$filetags['playtime_string'];
            
            /** JSON response:
            { 
              "bitrate": "$bitrateraw",
              "size": "$sizeraw",
              "bitrate_mode": "$bmode",
              "album": "$album",
              "albumartist": "$albumartist",
              "artist": "$artist",
              "genre": "$genre",
              "title": "$songname",
              "html_page": "http://jpv.everythingisawesome.us/song/$file",
              "download_url": "http://jpv.everythingisawesome.us/$file"
              "status": "200",
              "message": "request successful"
            }**/
            header('Content-Type: application/json');
            $output = "{\"bitrate\":\"$bitrateraw\",\"size\":\"$sizeraw\",\"bitrate_mode\":\"$bmode\",\"album\":\"$album\",\"albumartist\":\"$albumartist\",\"artist\":\"$artist\",\"genre\":\"$genre\",\"title\":\"$songname\",\"html_page\":\"http://jpv.everythingisawesome.us/song/$file\",\"download_url\":\"http://jpv.everythingisawesome.us/$file\",\"status\":\"200\",\"message\":\"request successful\"}" ;
            echo $output ;
            file_put_contents( $CACHE_FILE , $output );
            return;
        }
    } else {
        echo '{"status":"404", "message":"file provided does not exist"}';
        return;
    }