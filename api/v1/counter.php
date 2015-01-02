<?php
    /////////////////////////////////////////////////////////////////////
    // list.php - provides a simple way to list all available tracks   //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/counter.php?genre={genre}                         //
    //   {genre} is a folder in MEDIA_DIR                              //
    // Response:                                                       //
    //   Plain text number indicating how many songs of {genre} exist  //
    /////////////////////////////////////////////////////////////////////

    require_once('config.php');
    require_once(LIB_DIR . 'cache_util.php');

    if(isset($_GET['genre'])) {
        $count = get_genre_count(strip_tags(trim($_GET['genre'])));
        $genre = strip_tags(trim($_GET['genre']));
    } else {
        $count = get_genre_count("all");
        $genre = "all";
    }

    if ($genre === "all") $genre = "JPV";

    if(isset($_GET['format'])) {
        if (strip_tags(trim($_GET['format'])) === "fancy") {
            header("Content-Type: text/html");
            $nekos = "";
            foreach(str_split($count) as $num)
                $nekos .= "<img src='/img/counter/$num.gif'></img>";
            $nekos .= "<br /> $genre songs and counting!";
            print($nekos);
            die();
        }
    }

    header("Content-Type: text/plain");
    print($count);
