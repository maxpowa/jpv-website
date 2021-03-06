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
    require_once(LIB_DIR . 'cache_util.php');

    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath( MEDIA_DIR . strip_tags( trim( $_GET[ 'file' ] ) ) );
    } else if ( defined('STDIN') ) {
        $filename = realpath( $argv[1] );
    } else {
        header("Status-Code: 400");
        header('Content-Type: application/json');
        echo '{"status":"400", "message":"file parameter not provided"}';
        return;
    }


    if ( file_exists( $filename ) ) {
        $info = get_info_sql($filename);
        header('Content-Type: application/json');
        header("Status-Code: 200");
        echo $info;
        return;
    } else {
        header("Status-Code: 404");
        header('Content-Type: application/json');
        echo '{"status":"404", "message":"file provided does not exist"}';
        return;
    }
