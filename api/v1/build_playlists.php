<?php
    require_once('config.php');
    require_once(LIB_DIR . 'cache_util.php');

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
        $sql_column = $playlist_array['sql'];
        $inclusions = $playlist_array['include'];
        $exclusions = $playlist_array['exclude'];
        $current_list = "# $comment";

        echo("Building $name...");
        if ($sql_column != null && $sql_column != "") {
            echo("Clearing sql where genre_folder = $sql_column");
            clear_genre($sql_column);
        }

        foreach($inclusions as $file) {
            if(strpos($file, '.mp3') != 0) {
                $current_list .= "\n$file";
            } else $current_list = iterate_dir(MEDIA_DIR . $file, $current_list, $exclusions);
        }
        $existing = file_get_contents(MEDIA_DIR . $name);
        if (strcmp($existing, $current_list) == 0) {
            echo("Skipped. (UP-TO-DATE)<br />");
            return;
        }
        file_put_contents(MEDIA_DIR . $name, $current_list);
        echo("<br />");
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

                if(strpos($file, '.') == 0) {
                    $current_list = iterate_dir($href, $current_list, $exclusions);
                    continue;
                } else $current_list .= "\n$playlist_line";

                $fn = realpath(MEDIA_DIR . $playlist_line);
                echo("Generating file info for $fn");
                $safe_fn = escapeshellarg($fn);
                $safe_songphp = escapeshellarg(ROOT_DIR . "api/v1/song.php");
                popen("php $safe_songphp $safe_fn", 'r');
            }
        }

        return $current_list;
    }

?>
