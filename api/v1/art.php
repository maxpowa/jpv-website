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
    require_once(LIB_DIR . 'cache_util.php');

    if(isset($_GET['file'])) {
        $filename = realpath(MEDIA_DIR . strip_tags(trim($_GET['file'])));
    } else fallback();

	if($_GET['file'] == 'invalid')
		invalid();

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

    fallback();

	function fallback() {
	    fallbackTo(FALLBACK_IMAGE);
	}

	function invalid() {
		fallbackTo(INVALID_IMAGE);
	}

	function fallbackTo($img) {
	    header("Content-Type: image/png");
        header("Status-Code: 400");
        header("Content-Length: " . filesize($img));
		echo file_get_contents($img);
		exit;
	}
