<?php
    /////////////////////////////////////////////////////////////////////
    // song.php - displays song id3 information (cached if available)  //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/song.php?file={filename}                          //
    //   {filename} is a file in ../../media/                          //
    // Response:                                                       //
    //   JSON data corresponding to the song information               //
    /////////////////////////////////////////////////////////////////////
    
    require_once('config.php');
    require_once(LIB_DIR . 'id3utils.php');
     
    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath( MEDIA_DIR . strip_tags( trim( $_GET[ 'file' ] ) ) );
    } else {
        header('X-Cache: Miss');
        header('Content-Type: application/json');
        echo '{"status":"400", "message":"file parameter not provided"}';
        return;
    }
    
    
    if ( file_exists( $filename ) ) {
        $FILE = get_info($filename);
        header('Content-Type: application/json');
        header('X-Cache: Hit');
        $size= filesize( $FILE );
        header("Content-Length: $size bytes");
        readfile( $FILE );
    } else {
        header('X-Cache: Miss');
        header('Content-Type: application/json');
        echo '{"status":"404", "message":"file provided does not exist"}';
        return;
    }