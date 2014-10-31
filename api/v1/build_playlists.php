<?php
	require_once('config.php');
	$PLAYLISTS_JSON = MEDIA_DIR . 'playlists.json';
	if(!file_exists($PLAYLISTS_JSON)) {
		echo('playlists.json is not present!');
		return;
	}
	
	$playlist_data = json_decode(file_get_contents($PLAYLISTS_JSON), true);
	foreach($playlist_data['playlists'] as $playlist_array)
		build_playlist($playlist_array);
	
	function build_playlist($playlist_array) {
		$name = $playlist_array['name'];
		$comment = $playlist_array['comment'];
		$inclusions = $playlist_array['include'];
		$exclusions = $playlist_array['exclude'];
		$current_list = "# $comment";
		
		echo("Building $name...<br>");		
		
		foreach($inclusions as $file) {
			if(strpos($file, '.mp3') != 0)
				$current_list .= "\n$file";
			else $current_list = iterate_dir(MEDIA_DIR . $file, $current_list, $exclusions);
		}
        $existing = file_get_contents(MEDIA_DIR . $name);
        if strcmp($existing, $current_list) == 0 {
            return;
        }
		file_put_contents(MEDIA_DIR . $name, $current_list);
	}
	
	function iterate_dir($dir, $current_list, $exclusions) {
		if(!file_exists($dir) or !is_dir($dir))
			return;
        
        if(strpos($dir,'.sync') != 0)
            return;
	
        $files = scandir($dir);
        sort($files);
        foreach($files as $file) {
            if(strlen($file) > 2 && ((strpos($file, '.mp3') != 0 && strpos($file, '!sync') == 0) || strpos($file, '.') == 0)) {
                $href = "$dir/$file";
				$playlist_line = substr($href, strlen(MEDIA_DIR));
				
				if(in_array($file, $exclusions))
					continue;
				
                if(strpos($file, '.') == 0)
                    $current_list = iterate_dir($href, $current_list, $exclusions);
                else $current_list .= "\n$playlist_line";
            }	
        }
		
		return $current_list;
    }
	
?>