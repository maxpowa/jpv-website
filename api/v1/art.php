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
    
    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath( MEDIA_DIR . strip_tags( trim( $_GET[ 'file' ] ) ) );
    } else {
        header("Content-Type: image/png");
        header("Status-Code: 400");
        header("Content-Length: 1249");
        echo( base64_decode('iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAMAAAAL34HQAAAADFBMVEU/Pj79/f2jo6N+f37vqH6IAAAEkElEQVR4Ae3c4W6rvBJG4eX13v89H6mkI9oYkp5PHrIl+AO12DtPZ8xoMKSMj9z+FdbNulk362bdrJulH8kC0A9kBfDDWOIYBj6L9fAE/CjWQyP5IJYVpPBBLKhD8kGszFnihSxxErhK6VWsMObRIleyyPw4eCFLnEtgXMraHXIQrH4WzOMDo581raXs44OXsjKGaiBTbp0HxC5W+N6c57ZQCUDblFcdZTqo9+Shs8b6e/liHXcWcgnrVQXLJSxxetzP+tv/Sq5mBWfRvJT1esabXMCq+j8tawp4DWsIJIYCEKMApC+JqppEd1EhxeKx2Ti3ZL95Wj/aWNkS4xgOR6ZZCullpVJ1VuMBG1mpzzt1SaCNJcR36pYMoYkVDj4KJgPBBpZ/ue7Il83FrFIp83DNR13NYtsy/lKmwB6WIzloCmcbrGVZLBl/6K6gg5UtbNWwhHj68eJKFkBUwyYxACR42ovCQpZEs68QYduGnAfFpayq8r96iE0k/etblYrtKoy7YLH9nEtYYu1H8imsyl1Ikg0ogNtsJxewpKa6If64woJDvIAFASAO94OOEfiiXbUurwCQuIthqNC1s8DfS10Pm1ruSxfAh2VDU1WtmzXpMi3aA0w76zhDJvscdrPE12f0s+D9M/pY7werlZU3WKOfRV7D+1kdOZyw/nsocgUL5uPOs9zGyotpLl7B8vCxWfVdH8GqYTzNoUC6WeMRLfH0Fq6Vtb8DOv2H0sd6iHCew4K3s+qO9ZCFtW9kVTzIeV1ZEy3xqGpxMuND5bIxiUnNGjwtttgYLfIdB8lQh+okhW0FogaZPPzJL4w4+qIl9dHiFiiHAfzxHk5vlTeP8dLl20ske1Ujq9Z1i0UmT6CgtbHxaRW8lAZbXs+QaXlgem7IHt/JCozhwbKEbU9fcfbkTtxDk5hArDEb73yCz+O6Q2NVWdvuE7Eg8ywJBYSVLPbRIuBG5SC6+4B2rUGY4OA5Fk5+DzpXbATG0KOTrLSvjNZsMfDYXsfE0PXe6fHmr1JnwKUsyf9T6iSrF8Bz7j56QLSWNc7zkfxmdVyJr+IlRyUFG74V9ZdLl+gfr8TVrpBhgLicFU7yeFhZV7HcN6SQ91K4guX+ZGJN3vBOYsCxgLX/aBmp9Q+p1xKPfxUhYw0r1fya3Q10jR0HTcCxgiWO8Q2Titi+G1QA+N2jboFcwgpuO2pKBSfdTb7kVQesGC+K1vc+1Qs4XeIIW7AAoFCrorU/kHmH+ojk4yiJrl0AJ7tJVj/iXg48xF1f1hL3tXq6Pooj1OppC6vKAPgdPeHpFKhgdUVLhV3UyGxNvkLZxPL3YoJHiSZdrDDUF224qVrSx3rsJ4XKnxMwaWOVRnBeaCuN2MfKvEOZLIvIaGNJucJ8ptcreHawJgtY8ySSDedoZAl87eWYtWDj7W9J5g2Ws5jpkluMTFsC8YkF8HSuANGmL9TA84KHhqfoOmo0ZDVLcHYXGAyE+PPkhBBXs4bzJlSOO6Smb64kvvkWThOrtn+a5eUsMmWNa1nzuIT7L0rdrNXbzbpZN+tm3ayb9T8OKVkj7YP6BQAAAABJRU5ErkJggg==') );
        return;
    }
  
    if ( file_exists( $filename ) ) {
        $FILE = get_art($filename);
        if (!is_null($FILE)) {
            header("Content-type: image/jpeg");
            header("Status-Code: 200");
            $size= filesize( $FILE );
            header("Content-Length: $size");
            readfile( $FILE );
            return;
        }
    }
    
    header("Content-Type: image/png");
    header("Status-Code: 404");
    header("Content-Length: 1249");
    echo( base64_decode('iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAMAAAAL34HQAAAADFBMVEU/Pj79/f2jo6N+f37vqH6IAAAEkElEQVR4Ae3c4W6rvBJG4eX13v89H6mkI9oYkp5PHrIl+AO12DtPZ8xoMKSMj9z+FdbNulk362bdrJulH8kC0A9kBfDDWOIYBj6L9fAE/CjWQyP5IJYVpPBBLKhD8kGszFnihSxxErhK6VWsMObRIleyyPw4eCFLnEtgXMraHXIQrH4WzOMDo581raXs44OXsjKGaiBTbp0HxC5W+N6c57ZQCUDblFcdZTqo9+Shs8b6e/liHXcWcgnrVQXLJSxxetzP+tv/Sq5mBWfRvJT1esabXMCq+j8tawp4DWsIJIYCEKMApC+JqppEd1EhxeKx2Ti3ZL95Wj/aWNkS4xgOR6ZZCullpVJ1VuMBG1mpzzt1SaCNJcR36pYMoYkVDj4KJgPBBpZ/ue7Il83FrFIp83DNR13NYtsy/lKmwB6WIzloCmcbrGVZLBl/6K6gg5UtbNWwhHj68eJKFkBUwyYxACR42ovCQpZEs68QYduGnAfFpayq8r96iE0k/etblYrtKoy7YLH9nEtYYu1H8imsyl1Ikg0ogNtsJxewpKa6If64woJDvIAFASAO94OOEfiiXbUurwCQuIthqNC1s8DfS10Pm1ruSxfAh2VDU1WtmzXpMi3aA0w76zhDJvscdrPE12f0s+D9M/pY7werlZU3WKOfRV7D+1kdOZyw/nsocgUL5uPOs9zGyotpLl7B8vCxWfVdH8GqYTzNoUC6WeMRLfH0Fq6Vtb8DOv2H0sd6iHCew4K3s+qO9ZCFtW9kVTzIeV1ZEy3xqGpxMuND5bIxiUnNGjwtttgYLfIdB8lQh+okhW0FogaZPPzJL4w4+qIl9dHiFiiHAfzxHk5vlTeP8dLl20ske1Ujq9Z1i0UmT6CgtbHxaRW8lAZbXs+QaXlgem7IHt/JCozhwbKEbU9fcfbkTtxDk5hArDEb73yCz+O6Q2NVWdvuE7Eg8ywJBYSVLPbRIuBG5SC6+4B2rUGY4OA5Fk5+DzpXbATG0KOTrLSvjNZsMfDYXsfE0PXe6fHmr1JnwKUsyf9T6iSrF8Bz7j56QLSWNc7zkfxmdVyJr+IlRyUFG74V9ZdLl+gfr8TVrpBhgLicFU7yeFhZV7HcN6SQ91K4guX+ZGJN3vBOYsCxgLX/aBmp9Q+p1xKPfxUhYw0r1fya3Q10jR0HTcCxgiWO8Q2Titi+G1QA+N2jboFcwgpuO2pKBSfdTb7kVQesGC+K1vc+1Qs4XeIIW7AAoFCrorU/kHmH+ojk4yiJrl0AJ7tJVj/iXg48xF1f1hL3tXq6Pooj1OppC6vKAPgdPeHpFKhgdUVLhV3UyGxNvkLZxPL3YoJHiSZdrDDUF224qVrSx3rsJ4XKnxMwaWOVRnBeaCuN2MfKvEOZLIvIaGNJucJ8ptcreHawJgtY8ySSDedoZAl87eWYtWDj7W9J5g2Ws5jpkluMTFsC8YkF8HSuANGmL9TA84KHhqfoOmo0ZDVLcHYXGAyE+PPkhBBXs4bzJlSOO6Smb64kvvkWThOrtn+a5eUsMmWNa1nzuIT7L0rdrNXbzbpZN+tm3ayb9T8OKVkj7YP6BQAAAABJRU5ErkJggg==') );
    return;