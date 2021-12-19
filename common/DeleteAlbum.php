<?php
    

$dbConnection = parse_ini_file("./db_connection.ini");
        	extract($dbConnection);
        	$myPdo = new PDO($dsn, $user, $password);
                

include("./functions.php");

$albumId = htmlspecialchars($_GET["albumId"]);

// Delete pictures
deleteComments($myPdo,$albumId);
deletePictures($myPdo, $albumId);



// delete Album
deleteALbum($myPdo, $albumId);



header("Location: ../MyAlbums.php?msg='Your album has been deleted.'");

?>