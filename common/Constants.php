<?php
    define("ORIGINAL_PICTURES_DIR", "../uploads/original");
    define("ALBUM_PICTURES_DIR", "../uploads/album");
    define("THUMBNAILS_DIR", "../uploads/thumbnails");

    define("IMAGE_MAX_WIDTH", 500);
    define("IMAGE_MAX_HEIGHT", 300);

    define("THUMB_MAX_WIDTH", 80);
    define("THUMB_MAX_HEIGHT", 60);
    
    define("GROUP_NAME","A-Team (c) 2021-2022");

    $supportedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
    date_default_timezone_set("America/Toronto");
    ?>