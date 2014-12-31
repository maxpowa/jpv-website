<?php
    define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR);
    define('MEDIA_DIR', ROOT_DIR . 'media' . DIRECTORY_SEPARATOR);
    define('CACHE_DIR', ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR);
    define('GETID3_DIR', ROOT_DIR . 'getid3' . DIRECTORY_SEPARATOR);
    define('LIB_DIR', ROOT_DIR . 'lib' . DIRECTORY_SEPARATOR);
    define('TAG_DB', 'sqlite:' . CACHE_DIR . DIRECTORY_SEPARATOR . 'tag_db.sqlite');
    define('THUMBNAIL_IMAGE_MAX_WIDTH', 70);
    define('THUMBNAIL_IMAGE_MAX_HEIGHT', 70);

    define('FALLBACK_IMAGE', ROOT_DIR . 'img/song-fallback.png');
    define('INVALID_IMAGE', ROOT_DIR . 'img/song-invalid.png');
?>
