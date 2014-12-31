<?php
    /////////////////////////////////////////////////////////////////////
    // list.php - provides a simple way to list all available tracks   //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/list.php?genre={genre}(&format={format})          //
    //   {genre} is a folder in MEDIA_DIR                               //
    //   {format} is the format to respond on, json or html            //
    // Response:                                                       //
    //   JSON/HTML list of all songs for that genre                    //
    /////////////////////////////////////////////////////////////////////

    require_once('config.php');
    require_once(LIB_DIR . 'cache_util.php');

    $format = 'json';
    if(isset($_GET['format']))
        $format = $_GET['format'];

    if($format != 'json' && $format != 'html')
        error('400', 'Bad Request', '\'format\' parameter (\''.$format.'\') is invalid.');

    if(isset($_GET['genre'])) {
        get_genre_tracks(strip_tags(trim($_GET['genre'])));
    } else
        error('400', 'Bad Request', '\'genre\' parameter not provided.');


    function get_genre_tracks($genre) {
        global $format;

        if($genre != 'all') {
            $valid = false;
            $files = scandir(MEDIA_DIR);
            foreach($files as $file)
                if(is_dir(MEDIA_DIR . $file) && $genre == $file) {
                    $valid = true;
                    break;
                }

            if(!$valid)
                error('400', 'Bad Request', '\'genre\' parameter (\'' . $genre . '\') is invalid.');
        }

        if ($genre == 'all' || $genre == '*') {
            $INFO_LIST = get_all_list();
        } else {
            $INFO_LIST = get_genre_list($genre);
        }

        header("Cache-Control: max-age=86400");
        header('Content-Type: application/json');
        if($format == 'html')
            echo(get_jpv_html($INFO_LIST, 'From INFO_LIST'));
        else echo($INFO_LIST);
    }

    function encode_entities($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML401);
    }

    function get_jpv_html($list, $error_info) {
        if($list == null)
            error('500', 'Internal Server Error', 'Song list is null (' . $error_info . ')');

        $html = '';

        $json_list[] = json_decode($list, true);

        foreach($json_list[0] as $song_data) {
            $title = encode_entities($song_data['title']);
            $artist = encode_entities($song_data['artist']);
            $albumartist = encode_entities($song_data['album_artist']);
            $album = encode_entities($song_data['album']);
            $length =  encode_entities($song_data['length']);

            $filename = $song_data['href'];
            $parsed_filename = str_replace('.mp3', '', urldecode($filename));
            $parsed_filename = substr($parsed_filename, strpos($parsed_filename, '/') + 1);
            $filename_tokens = explode(' - ', $parsed_filename);
            $genre = strtolower($song_data['genre']);
            $valid = sizeof($filename_tokens) > 1;

            if(empty($title))
                $title = $valid ? trim($filename_tokens[sizeof($filename_tokens) - 1]) : 'This song is not available D:';
            if(empty($artist))
                $artist = $valid ? trim($filename_tokens[0]) : 'Please annoy us so we can fix it!';
            if(empty($albumartist))
                $albumartist = $artist;
            if(empty($album))
                $album = $valid ? "$title (Single)" : "x(";
            if(empty($length))
                $length = 'N/A';


            // The chestiest encoding ever, woot
            $info_loc = $filename;
            $info_loc = substr($info_loc, strpos('/', $info_loc));
            $info_loc = str_replace('.mp3', '', $info_loc);
            $info_loc = urldecode($info_loc);
            $info_loc = urlencode($info_loc);
            $info_loc = str_replace('+', ' ', $info_loc);

            $image = "./api/v1/art.php?file=$filename";
            $md5 = $song_data['hash'];
            $md5 = preg_replace("/[^A-Za-z0-9 ]/", '', $md5);

            if (file_exists(CACHE_DIR . 'art' . DIRECTORY_SEPARATOR . $md5 . '.jpg')) {
                $image = "./cache/art/$md5.jpg";
            }

            $classes = 'song-box';
            $buttons = "<div class='song-buttons'><div class='song-button song-play-button glyphicon glyphicon-play-circle' target='_blank' data-toggle='tooltip' title='Play'></div><a href='./media/$filename' download target='_blank' data-toggle='tooltip' title='Download'><div class='song-button song-download-button glyphicon glyphicon-download'></div></a><a href='./song/?genre=$genre&song=$info_loc' target='_blank' data-toggle='tooltip' title='Song Info'><div class='song-button  glyphicon glyphicon-info-sign'></div></a></div>";

            if(!$valid) {
                $image = "./api/v1/art.php?file=invalid";
                $buttons = '';
                $classes .= ' invalid-song';
                $albumartist = 'Whatcha looking at?';
            }

            $html .= "<div class='$classes'><div class='song-image' data-toggle='tooltip' title='$album'><img src='$image'></img></div><div class='song-info'><div class='song-title'>$title</div><br><div class='song-artist' data-toggle='tooltip' title='$albumartist'>$artist</div><br><div class='song-length'>$length</div><br>$buttons</div></div>";
        }

        return "{\"status\":\"200\", \"message\":\"$html\"}";
    }

    function error($status, $errorstr, $message) {
            header("Status-Code: $status");
            header('Content-Type: application/json');
            header("Cache-Control: max-age=86400");
            echo('{"status":"' . $status . '", "message":"<div class=\'song-box invalid-song\'><div class=\'song-image\'><img src=\"./img/error.jpg\"></img></div><div class=\'song-info\'><div class=\'song-title\'>HTTP ' . $status . ': ' . $errorstr . '</div><br><div class=\'song-artist\'>' . $message . '</div></div></div>"}');
            exit;
    }
