<?php
	require_once('../../getid3/getid3.php');
    
    if ( isset( $_GET[ 'file' ] ) ) {
        $filename = realpath(strip_tags( trim( $_GET[ 'file' ] ) ) );
    }
    
    #echo '<pre>'.$filename.'</pre>';
    #define('GETID3_HELPERAPPSDIR', 'D:/Polymer_Playground/getid3/helperapps');
    
    #if (file_exists($filename)) {
    #    echo '<pre>'.'File exists'.'</pre>';
    #}
    
    $getID3 = new getID3;
    #$getID3->option_tag_id3v2 = true; # We don't /need/ to force tags to be id3v2
    $getID3->analyze($filename);
    
    #echo '<pre>'.htmlentities(print_r($getID3, true)).'</pre>';
    
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

    #$cover = null;
    
    if (!is_null($cover)) {
        // Send file 
        header("Content-Type: " . $mimetype);
        
        if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
            header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
        }

        echo($cover);
    }