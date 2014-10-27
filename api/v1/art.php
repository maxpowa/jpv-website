<?php
    /////////////////////////////////////////////////////////////////////
    // art.php - fetches album art from given file in $MEDIA_DIR       //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/art.php?file={filename}                           //
    //   {filename} is a file in ../../media/                          //
    // Response:                                                       //
    //   Image Data corresponding to the requested file's art          //
    /////////////////////////////////////////////////////////////////////
    
    require_once('config.php');
    require_once(LIB_DIR . 'id3utils.php');
	
	$fallback = file_get_contents('song-fallback.png');
	
    if(isset($_GET['file'])) {
        $filename = realpath(MEDIA_DIR . strip_tags(trim($_GET['file'])));
    } else {
        header("Content-Type: image/png");
        header("Status-Code: 400");
        header("Content-Length: 1249");
        echo($fallback);
        return;
    }
  
	if(file_exists($filename) && is_file($filename)) {
        $FILE = get_art($filename);
        if(!is_null($FILE)) {
            header("Content-type: image/jpeg");
            header("Status-Code: 200");
            $size = filesize($FILE);
            header("Content-Length: $size");
            readfile($FILE);
            return;
        }
    }
    
    header("Content-Type: image/png");
    header("Status-Code: 404");
    header("Content-Length: 1249");
    echo($fallback);
    return;