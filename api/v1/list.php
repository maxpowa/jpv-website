<?php
    /////////////////////////////////////////////////////////////////////
    // list.php - provides a simple way to list all available tracks   //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/list.php?genre={genre}                        //
    //   {genre} is a folder in MEDIA_DIR                              //
    // Response:                                                       //
    //   JSON list of all songs for that genre                         //
    /////////////////////////////////////////////////////////////////////
    
    require_once('config.php');
    require_once(LIB_DIR . 'id3utils.php');
    
    if ( isset( $_GET[ 'genre' ] ) ) {
        get_genre_tracks( strip_tags( trim( $_GET[ 'genre' ] ) ) );
    } else {
        header("Status-Code: 400");
        header('Content-Type: application/json');
        echo '{"status":"400", "message":"genre parameter not provided"}';
        return;
    }
    
    $RESPONSE_LIST = array();
    
    function get_genre_tracks($genre) {
        global $RESPONSE_LIST;
        if ($genre == 'all') {
            $CACHED_LIST = CACHE_DIR . 'all.json';
            if ( file_exists( $CACHED_LIST ) && !check_file_age( $CACHED_LIST , 3600 )  ) {
                header("Status-Code: 200");
                header('Content-Type: application/json');
                $size= filesize( $CACHED_LIST );
                header("Content-Length: $size bytes");
                readfile( $CACHED_LIST );
                return;
            } else {
                iterate_dir( MEDIA_DIR );
                header('Content-Type: application/json');
                $output = json_encode($RESPONSE_LIST);
                echo($output);
                file_put_contents( $CACHED_LIST , $output );
            }
        } else {
            cache_check($genre);
        }
    }
    
    function cache_check($genre) {
        global $RESPONSE_LIST;
        $CACHED_LIST = CACHE_DIR . $genre . '.json';
        if ( file_exists( $CACHED_LIST ) && !check_file_age( $CACHED_LIST , 3600 )  ) {
            header("Status-Code: 200");
            header('Content-Type: application/json');
            $size= filesize( $CACHED_LIST );
            header("Content-Length: $size bytes");
            readfile( $CACHED_LIST );
            return;
        } else {
            iterate_dir( MEDIA_DIR . $genre );
            header('Content-Type: application/json');
            $output = json_encode($RESPONSE_LIST);
            echo($output);
            file_put_contents( $CACHED_LIST , $output );
        }
    }
    
    /**
     *
     * Check file age, return true if older than $age (seconds), false otherwise
     *
     */
    function check_file_age($file, $age) {
        $now = time();
        $filetime = filemtime($file);
        if(($now - $filetime) >= $age){
            return true;
        } 
        return false;
    }
    
    function iterate_dir($dir) {
        $files = scandir($dir);
        sort($files);
        foreach($files as $file) {
            if(strlen($file) > 2 && (strpos($file, '.mp3') != 0 || strpos($file, '.') == 0)) {
                $href = "$dir/$file";
                if(strpos($file, '.') == 0) {
                    iterate_dir($href);
                } else {
                    build_json($file, str_replace( MEDIA_DIR , '' , $href ));
                }
            }	
        }
    }
    
    function build_json($filename, $rel_path) {
        global $RESPONSE_LIST;
        $INFO_FILE = get_info( MEDIA_DIR . $rel_path);
		$RESPONSE_LIST[] = json_decode(file_get_contents($INFO_FILE));
	}