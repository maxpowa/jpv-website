<?php
    /////////////////////////////////////////////////////////////////////
    // list.php - provides a simple way to list all available tracks   //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/list.php?genre={genre}(&format={format})          //
    //   {genre} is a folder in MEDIA_DIR	                           //
	//   {format} is the format to respond on, json or html            //
    // Response:                                                       //
    //   JSON/HTML list of all songs for that genre                    //
    /////////////////////////////////////////////////////////////////////
    
    require_once('config.php');
    require_once(LIB_DIR . 'id3utils.php');
    
	$format = 'json';
	if(isset($_GET['format']))
		$format = $_GET['format'];
	
	if($format != 'json' && $format != 'html') {
	    header("Status-Code: 400");
        header('Content-Type: application/json');
        echo '{"status":"400", "message":"\'format\' parameter (\''.$format.'\') is invalid"}';
        return;
	}	
	
    if (isset( $_GET[ 'genre' ])) {
        get_genre_tracks(strip_tags(trim($_GET['genre'])));
    } else {
        header("Status-Code: 400");
        header('Content-Type: application/json');
        echo '{"status":"400", "message":"\'genre\' parameter not provided"}';
        return;
    }
    
    $INFO_LIST = array();
    
    function get_genre_tracks($genre) {
        global $INFO_LIST;
		global $format;
		
		$CACHED_LIST = CACHE_DIR . $genre . '.json';
		if (file_exists($CACHED_LIST) && !check_file_age($CACHED_LIST , 3600)) {
			header("Status-Code: 200");
			header('Content-Type: application/json');

			if($format == 'html')
				echo(get_jpv_html(json_decode(file_get_contents($CACHED_LIST), true), 'From CACHED_LIST'));
			else {
				$size = filesize($CACHED_LIST);
				header("Content-Length: $size");
				readfile($CACHED_LIST);
			}
			return;
		} else {
			iterate_dir(MEDIA_DIR . ($genre == 'all' ? '' : $genre));
			header('Content-Type: application/json');
			$time = time();
			$json_list = json_encode($INFO_LIST);
			$output = "{\"status\":\"200\", \"message\":\"$json_list\"}";
			file_put_contents($CACHED_LIST , $json_list);

			if($format == 'html')
				echo(get_jpv_html($INFO_LIST, 'From INFO_LIST'));
			else echo($output);
		}
    }

	function get_jpv_html($list, $error_info) {		
		if($list == null) {
			header("Status-Code: 500");
			header('Content-Type: application/json');
			return '{"status":"500", "message":"Internal Server Error: invalid genre. ' . $error_info . '"}';
		}
	
		$html = '';
		
		foreach($list as $song_data) {
			$title = $song_data['title'];
			$artist = $song_data['artist'];
			$album = $song_data['album'];
			$length = $song_data['length'];
			$filename = $song_data['filename'];
			
			$html = "$html<div class='song-box'><div class='song-image'><img src='./api/v1/art.php?file=$filename'></img></div><div class='song-info'><div class='song-title'>$title</div><div class='song-artist'>$artist</div><div class='song-length'>$length</div><div class='song-buttons'><div class='song-button song-play-button'></div><div class='song-button song-download-button'></div></div></div></div>";
		}
		
		$html = "{\"status\":\"200\", \"message\":\"$html\"}";
		return $html;
	}
    
    function cache_check($genre) {
        global $INFO_LIST;
        $CACHED_LIST = CACHE_DIR . $genre . '.json';
        if(file_exists($CACHED_LIST) && !check_file_age($CACHED_LIST, 3600)) {
            header("Status-Code: 200");
            header('Content-Type: application/json');
            $size = filesize($CACHED_LIST);
            header("Content-Length: $size");
            readfile($CACHED_LIST);
            return;
        } else {
            iterate_dir( MEDIA_DIR . $genre );
            header('Content-Type: application/json');
            $time = time();
            $output = "{\"last-modified\":\"$time\",\"songs\":".json_encode($INFO_LIST).'}';
            echo($output);
            file_put_contents($CACHED_LIST , $output);
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
		if(!file_exists($dir))
			return;
	
        $files = scandir($dir);
        sort($files);
        foreach($files as $file) {
            if(strlen($file) > 2 && (strpos($file, '.mp3') != 0 || strpos($file, '.') == 0)) {
                $href = "$dir/$file";
                if(strpos($file, '.') == 0) {
                    iterate_dir($href);
                } else {
                    build_json($file, str_replace(MEDIA_DIR , '' , $href));
                }
            }	
        }
    }
    
    function build_json($filename, $rel_path) {
        global $INFO_LIST;
        $INFO_FILE = get_info(MEDIA_DIR . $rel_path);
		$INFO_LIST[] = json_decode(file_get_contents($INFO_FILE), true);
	}