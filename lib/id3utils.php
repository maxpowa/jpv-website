<?php
    /////////////////////////////////////////////////////////////////////
    //                                                                 //
    // id3utils.php - does the heavy lifting for the endpoints         //
    //                                                                 //
    // Requires PHP5-GD for image caching                              //
    //   Caching uses MD5 as the identifier, so any change to a file   //
    //   will cause a new cache to be created.                         //
    //                                                                 //
    /////////////////////////////////////////////////////////////////////
    
    require_once('config.php');
    require_once(GETID3_DIR . 'getid3.php');
    
    $ART_DIR = CACHE_DIR . 'art/';
    $TAG_DIR = CACHE_DIR . 'tags/';
    
    /**
     * Gets art file, will return it as an image stream
     */
    function get_art($filename) {
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $ART_DIR . $FILE_MD5 . '.gif';
        if ( file_exists( $CACHE_FILE ) ) {
            return $CACHE_FILE;
        } else {
            $getID3 = new getID3;
            #$getID3->option_tag_id3v2 = true; # We don't /need/ to force tags to be id3v2
            $getID3->analyze($filename);
            
            if (isset($getID3->info['id3v2']['APIC'][0]['data'])) {
                $cover = $getID3->info['id3v2']['APIC'][0]['data'];
            }
            elseif (isset($getID3->info['id3v2']['PIC'][0]['data'])) {
                $cover = $getID3->info['id3v2']['PIC'][0]['data'];
            } else {
                $cover = null;
            }

            
            if (isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
                $mimetype = $getID3->info['id3v2']['APIC'][0]['image_mime'];
            } else {
                $mimetype = 'image/jpeg';
                // or null; depends on your needs 
            }
            
            if (!is_null($cover)) {
                // Send file 
                header("Content-Type: " . $mimetype);
                
                if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
                    header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
                }
                
                $img = imagecreatefromstring($cover); # Create a cache image, because it didn't exist
                imagegif($img, $CACHE_FILE); # Save the image to disk, for later retrieval
                imagedestroy($img); # Destroy the image object to free up mem
                # If GD isn't loaded, you're gonna have a bad time.
                
                return $CACHE_FILE;
            }
            return null;
        }
    }
    
    function get_info($filename) {    
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $TAG_DIR . $FILE_MD5 . '.json';
        if ( file_exists( $CACHE_FILE ) ) {
            return $CACHE_FILE ;
        } else {
            $getID3 = new getID3;
            #$getID3->option_tag_id3v2 = true; # We don't /need/ to force tags to be id3v2
            $filetags = $getID3->analyze($filename);
            $songname = $filetags['tags']['id3v2']['title'][0];
            $artist = $filetags['tags']['id3v2']['artist'][0];
            $albumartist = $filetags['tags']['id3v2']['band'][0];
            $album = $filetags['tags']['id3v2']['album'][0];
            $bitrate = floor((int) $filetags['audio']['bitrate'] / 1000);
            $bmode = $filetags['audio']['bitrate_mode'];
            $genre = $filetags['tags']['id3v2']['genre'][0];
            $sizeraw = $filetags['filesize'];
            $bitrateraw = $filetags['audio']['bitrate'];
            $len = @$filetags['playtime_string'];
            
            /** JSON response:
            { 
              "bitrate": "$bitrateraw",
              "size": "$sizeraw",
              "bitrate_mode": "$bmode",
              "album": "$album",
              "albumartist": "$albumartist",
              "artist": "$artist",
              "genre": "$genre",
              "title": "$songname",
              "html_page": "http://jpv.everythingisawesome.us/song/$file",
              "download_url": "http://jpv.everythingisawesome.us/$file"
              "status": "200",
              "message": "request successful"
            }**/
            header('Content-Type: application/json');
            $output = "{\"md5\":\"$FILE_MD5\",\"bitrate\":\"$bitrateraw\",\"size\":\"$sizeraw\",\"bitrate_mode\":\"$bmode\",\"album\":\"$album\",\"albumartist\":\"$albumartist\",\"artist\":\"$artist\",\"genre\":\"$genre\",\"title\":\"$songname\",\"html_page\":\"http://jpv.everythingisawesome.us/song/$file\",\"download_url\":\"http://jpv.everythingisawesome.us/$file\",\"status\":\"200\",\"message\":\"request successful\"}" ;
            file_put_contents( $CACHE_FILE , $output );
            return $CACHE_FILE ;
        }
    }