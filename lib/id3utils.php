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
        global $ART_DIR;
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $ART_DIR . $FILE_MD5 . '.jpg';
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
            
            if (!is_null($cover)) {
                list($source_image_width, $source_image_height) = getimagesizefromstring($cover);
                $img = imagecreatefromstring($cover); # Create a cache image, because it didn't exist
                
                // Resizing code - max width/height are set via config.php
                $source_aspect_ratio = $source_image_width / $source_image_height;
                $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
                if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
                    $thumbnail_image_width = $source_image_width;
                    $thumbnail_image_height = $source_image_height;
                } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                    $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                    $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
                } else {
                    $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
                    $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                }
                $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                imagecopyresampled($thumbnail_gd_image, $img, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                // Resizing code
                
                imagejpeg($thumbnail_gd_image, $CACHE_FILE); # Save the image to disk, for later retrieval
                imagedestroy($thumbnail_gd_image); # Destroy the image object to free up mem
                imagedestroy($img); # Destroy the image object to free up mem
                # If GD isn't loaded, you're gonna have a bad time.
                
                return $CACHE_FILE;
            }
            return null;
        }
    }
    
    function get_info($filename) { 
        global $TAG_DIR;    
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
            $md5 = $FILE_MD5;
            $href = str_replace( '+', '%2B', str_replace( MEDIA_DIR, '', $filename ));
            
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
            $output = "{\"filename\":\"$href\",\"md5\":\"$md5\",\"bitrate\":\"$bitrateraw\",\"size\":\"$sizeraw\",\"bitrate_mode\":\"$bmode\",\"album\":\"$album\",\"albumartist\":\"$albumartist\",\"artist\":\"$artist\",\"genre\":\"$genre\",\"title\":\"$songname\",\"length\":\"$len\",\"status\":\"200\",\"message\":\"request successful\"}" ;
            file_put_contents( $CACHE_FILE , $output );
            return $CACHE_FILE ;
        }
    }