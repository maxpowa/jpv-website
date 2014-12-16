<?php
    /////////////////////////////////////////////////////////////////////
    //                                                                 //
    // cache_util.php - does the heavy lifting for the endpoints       //
    //                                                                 //
    // Requires PHP5-GD for image caching                              //
    //   Caching uses MD5 as the identifier, so any change to a file   //
    //   will cause a new cache to be created.                         //
    //                                                                 //
    /////////////////////////////////////////////////////////////////////

    require_once('config.php');
    require_once(GETID3_DIR . 'getid3.php');

    $ART_DIR = CACHE_DIR . 'art' . DIRECTORY_SEPARATOR;

    $PERSIST_PDO = null;

    /**
     * Gets art file, will return it as an image stream
     */
    function get_art($filename) {
        global $ART_DIR;
        $FILE_MD5 = md5_file( $filename );
        $CACHE_FILE = $ART_DIR . $FILE_MD5 . '.jpg';
        if(!file_exists($ART_DIR))
            mkdir($ART_DIR);

        if ( file_exists( $CACHE_FILE ) ) {
            header("X-Cache: hit");
            return $CACHE_FILE;
        } else {
            header("X-Cache: miss");
            $getID3 = new getID3;
            $getID3->encoding = 'UTF-8';
            $getID3->option_tag_id3v2 = true; # We need to force the tags to id3v2, because we are pulling pic data from that tag type
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

    function clear_genre($genre) {
        global $PERSIST_PDO;
        check_db();
        $sel=$PERSIST_PDO->prepare("DELETE FROM tags WHERE genre_folder = ? COLLATE NOCASE");
        $sel->execute(array($genre));
        return;
    }

    function get_genre_list($genre) {
        global $PERSIST_PDO;
        check_db();
        $sel=$PERSIST_PDO->prepare("SELECT * FROM tags WHERE genre_folder = ? COLLATE NOCASE");
        $sel->execute(array($genre));
        $result=$sel->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    function search_db($query) {
        global $PERSIST_PDO;
        check_db();
        $query = "%".$query."%";
        $sel=$PERSIST_PDO->prepare("SELECT * FROM tags WHERE artist LIKE :query OR".
                " album_artist LIKE :query OR album LIKE :query OR title LIKE :query COLLATE NOCASE");
        $sel->bindParam(':query', $query);
        $sel->execute();
        $result=$sel->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    function get_all_list() {
        global $PERSIST_PDO;
        check_db();
        $sel=$PERSIST_PDO->prepare("SELECT * FROM tags");
        $sel->execute();
        $result=$sel->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    function get_info_sql($filename) {
        // Apparently this is faster than md5_file.
        // Honestly, I think a potato trying to roll uphill is faster than md5_file
        $safe_fn = escapeshellarg($filename);
        $FILE_MD5 = explode(" ", exec("md5sum $safe_fn"))[0];

        return get_info_sql_with_md5($filename, $FILE_MD5);
    }

    function get_info_sql_with_md5($filename, $FILE_MD5) {
        global $PERSIST_PDO;

        check_db();

        $sel=$PERSIST_PDO->prepare("SELECT * FROM tags WHERE hash = ?");
        $sel->execute(array($FILE_MD5));
        $result=$sel->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            return json_encode($result);
        } else {
            $getID3 = new getID3;
            $getID3->encoding = 'UTF-8';
            $getID3->option_tag_id3v2 = true;
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
            $href = str_replace(array('%2F','%5C'), '/', rawurlencode(str_replace(MEDIA_DIR, '', $filename)));

            $ins = $PERSIST_PDO->prepare("INSERT INTO tags (hash, filename, bitrate,".
                        " size, bitrate_mode, album, album_artist, artist, genre, genre_folder,".
                        " title, length, href) VALUES (:hash, :filename, :bitrate, :size, :bitrate_mode,".
                        " :album, :album_artist, :artist, :genre, :genre_folder, :title, :length, :href)");
            $ins->bindParam(':hash', $FILE_MD5);
            $ins->bindParam(':filename', $filename);
            $ins->bindParam(':bitrate', $bitrateraw);
            $ins->bindParam(':size', $sizeraw);
            $ins->bindParam(':bitrate_mode', $bmode);
            $ins->bindParam(':album', $album);
            $ins->bindParam(':album_artist', $albumartist);
            $ins->bindParam(':artist', $artist);
            $ins->bindParam(':genre', $genre);
            $ins->bindParam(':genre_folder', array_filter(explode('/', $href))[0]);
            $ins->bindParam(':title', $songname);
            $ins->bindParam(':length', $len);
            $ins->bindParam(':href', $href);
            $ins->execute();

            $sel->execute(array($FILE_MD5));
            $result=$sel->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);
        }
    }

    // Simple func to ensure the db object is initialized
    function check_db() {
        global $PERSIST_PDO;

        if (is_null($PERSIST_PDO)) {
            $PERSIST_PDO = new PDO(TAG_DB);

            $PERSIST_PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $PERSIST_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        if (!table_exists($PERSIST_PDO, "tags"))
        {
            //create the table
            $PERSIST_PDO->exec("CREATE TABLE tags (hash VARCHAR(32) PRIMARY KEY, filename TEXT,".
            "bitrate INTEGER, size INTEGER, bitrate_mode TEXT, album TEXT, album_artist TEXT,".
            "artist TEXT, genre TEXT, genre_folder TEXT, title TEXT, length TEXT, href TEXT)");
        }
    }

    /**
    * Check if a table exists in the current database.
    *
    * @param PDO $pdo PDO instance connected to a database.
    * @param string $table Table to search for.
    * @return bool TRUE if table exists, FALSE if no table found.
    */
    function table_exists($pdo, $table) {
        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        } catch (Exception $e) {
            // We got an exception == table not found
            return FALSE;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== FALSE;
    }

    function close_db() {
        global $PERSIST_PDO;
        $PERSIST_PDO = null;
    }
