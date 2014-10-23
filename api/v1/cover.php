<?php
    ///////////////////////////////////////////////////////////////////////
    // cover.php - fetches album art from given file (internal use only) //
    ///////////////////////////////////////////////////////////////////////
    
    # Requires getid3() lib
	require_once('../../getid3/getid3.php');
    $MEDIA_DIR = '../../media_objects/';
    $CACHE_DIR = '../../cache/';
    
    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath( $MEDIA_DIR . strip_tags( trim( $_GET[ 'file' ] ) ) );
    }
  
    if ( file_exists( $filename ) ) {
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $CACHE_DIR . $FILE_MD5 . '.gif';
        if ( file_exists( $CACHE_FILE ) ) {
            header("Content-type: image/gif");
            $size= filesize( $CACHE_FILE );
            header("Content-Length: $size bytes");
            readfile( $CACHE_FILE );
            
            #Debug
            #file_put_contents('debug.txt','Fetched cached image', FILE_APPEND);
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
            
            #Debug
            #file_put_contents('debug.txt','Got image from tags', FILE_APPEND);
            
            if (!is_null($cover)) {
                // Send file 
                header("Content-Type: " . $mimetype);
                
                if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
                    header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
                }

                echo($cover);
                
                # Create a cache image, because it didn't exist
                $img = imagecreatefromstring($cover);
                # Save the image to disk, for later retrieval
                imagegif($img, $CACHE_FILE);
                # Destroy the image object to free up mem
                imagedestroy($img);
                
                #Debug
                #file_put_contents('debug.txt','Saved image to disk', FILE_APPEND);
            } else {
                header("Content-type: image/png");
                $size= filesize( '../../res/dickbutt.png' );
                header("Content-Length: $size bytes");
                readfile( '../../res/dickbutt.png' );
                
                #Debug
                #file_put_contents('debug.txt','Served a dickbutt', FILE_APPEND);
            }
        }
    } else {
        header("Content-type: image/png");
        $size= filesize( '../../res/dickbutt.png' );
        header("Content-Length: $size bytes");
        readfile( '../../res/dickbutt.png' );
    }