<?php
    /////////////////////////////////////////////////////////////////////
    // search.php - searches for songs matching query                  //
    //                                                                 //
    // Request Scheme:                                                 //
    //   GET /api/v1/search.php?q={query}                              //
    //   {query} is a statement LIKE some row in the db                //
    // Response:                                                       //
    //   JSON data corresponding to the search results                 //
    /////////////////////////////////////////////////////////////////////

    require_once('config.php');
    require_once(LIB_DIR . 'cache_util.php');

    header('Content-Type: application/json');
    if ( isset( $_GET[ 'q' ] ) ) {
        $query = strip_tags( trim( $_GET[ 'q' ] ) );
    } else {
        header("Status-Code: 400");
        echo '{"status":"400", "message":"q parameter not provided"}';
        return;
    }

    header("Status-Code: 200");
    echo search_db($query);
