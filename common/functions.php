<?php

function getAlbumWithDate($userId, $myPdo, $selectedAlbumId) {
    $sql = $userId == $_SESSION["userId"] ? "SELECT * FROM album WHERE owner_Id=:userId" :
            "SELECT * FROM album WHERE owner_Id=:userId AND Accessibility_Code='shared'";
    $pSql = $myPdo->prepare($sql);
    $pSql->execute(["userId" => $userId]);
    if (!$pSql->rowCount()) {
        echo "<option>Please create album first</option>";
    } else {
        $hasPic = 0;
        foreach ($pSql as $item) {
            $shared = $item["Accessibility_Code"] == "private" ? "&#xf023;" : "&#xf09c; ";
//count picture number of the same album
            $sql = "SELECT count(*) as num FROM picture WHERE album_Id=:albumId";
            $pAlbum = $myPdo->prepare($sql);
            $pAlbum->execute(["albumId" => $item["Album_Id"]]);
            $row = $pAlbum->fetch(PDO::FETCH_ASSOC);
            if ($row["num"] > 0) {
                $hasPic++;
                echo "<option";
                if ($selectedAlbumId == strval($item["Album_Id"])) {
                    echo " selected ";
                }
                echo " value='" . $item["Album_Id"] . "'>" . $shared . " " . $item["Title"];
                echo " [" . $row["num"] . "] - Uploaded on " . $item["Date_Updated"] . "</option>";
            }
        }
        if ($hasPic == 0) {//no album has uploaded pictures, then hide the select list
            echo "<script>document.getElementById('chooseAlbum').style.display='none';</script>";
        }
    }
}

function getAlbum($myPdo, $selectedAlbumId) {
    $sql = "SELECT * FROM album WHERE owner_Id=:userId";
//echo $sql;
    $pSql = $myPdo->prepare($sql);
    $pSql->execute(["userId" => $_SESSION["userId"]]);
    if (!$pSql->rowCount()) {
        echo "<option>Please create album first</option>";
    } else {
        foreach ($pSql as $item) {

            $shared = $item["Accessibility_Code"] == "private" ? "&#xf023;" : "&#xf09c; ";
            //count picture number of the same album
            $sql = "SELECT count(*) as num FROM picture WHERE album_Id=:albumId";
            $pAlbum = $myPdo->prepare($sql);
            $pAlbum->execute(["albumId" => $item["Album_Id"]]);
            $row = $pAlbum->fetch(PDO::FETCH_ASSOC);
            echo "<option";
            if ($selectedAlbumId == strval($item["Album_Id"])) {
                echo " selected ";
            }
            echo " value='" . $item["Album_Id"] . "'>" . $shared . " " . $item["Title"];
            echo " [" . $row["num"] . "]</option>";
        }
    }
}

function getImgNameById($myPdo, $imgId) {
    $sql = "SELECT FileName FROM picture WHERE Picture_Id=:imgId";
//echo $sql;
    $pSql = $myPdo->prepare($sql);
    $pSql->execute(["imgId" => $imgId]);
    $row = $pSql->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "No pictures found!";
    } else {
        return "../uploads/album/watermarked" . $row["FileName"];
    }
}

function CreateWaterMark($originalFile, $outputFile, $string, $pos) {
    $im = imagecreatefromjpeg($originalFile);

// First we create our stamp image manually from GD
    $stamp = imagecreatetruecolor(190, 26);
//bottom-right
    imagefilledrectangle($stamp, 0, 0, 189, 25, 0x0FB0D4);
    imagefilledrectangle($stamp, 2, 2, 187, 23, 0xFFFFFF);
    imagestring($stamp, 7, 5, 5, $string, 0x000000); //fontsize, left margin, top margin

    $wt = imagecreatefrompng('../common/img/logo.png'); //logo:60*60
// Set the margins for the stamp and get the height/width of the stamp image
    $marge_right = 1;
    $marge_bottom = 1;
    $sx = imagesx($stamp);
    $sy = imagesy($stamp);

// Merge the stamp onto our photo with an opacity of 50%
    switch ($pos) {
        case 1://logo top left, text bottom right
            //imagecopymerge($im, $stamp, 1, 1, 0, 0, imagesx($stamp), imagesy($stamp), 50);
            imagecopymerge($im, $wt, 1, 1, 0, 0, 60, 60, 50);
            imagecopymerge($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 50);

            break;
        case 2://top right
            break;
        case 3://bottom left
            break;
        case 4: //bottom right
            imagecopymerge($im, $wt, imagesx($im) - 60 - $marge_right, imagesy($im) - 60 - $marge_bottom, 0, 0, 60, 60, 50);
            imagecopymerge($im, $stamp, 1, 1, 0, 0, $sx, $sy, 50);
            break;
        case 5://center
            break;
        default:
            imagecopymerge($im, $stamp, imagesx($im) - 60 - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 50);
            break;
    }


// Save the image to file and free memory
    imagepng($im, $outputFile);
    imagedestroy($im);
//echo "<img src=".$outputFile.".>";
}

function watermarkImage($imageURL, $outputFile, $string, $pos) {
    $img = imagecreatefromjpeg($imageURL);
    $color = imagecolorallocate($img, 255, 255, 255);
    $size = 25;
    $angle = 0;
// $fontfile=getcwd().'/Economica-Regular.ttf';
    $fontfile = getcwd(). 'BigShoulders.ttf';
    $info = imagettfbbox($size, $angle, $fontfile, $string); //bounding box,
    //0:left bottom x; 1: left bottom y; 2: right btm x, 3: rgt btm y;
    //4:top right x, 5: top right y; 6: top left x; 7: top left y
 $wt = imagecreatefrompng('../common/img/logo.png'); //logo:60*60
 
    $sx = imagesx($wt);
    $sy = imagesy($wt);
    
    $marge_right=1;
    $marge_bottom=1;
    
    switch ($pos) {
        case 1://top left text, bottom right logo
            $string_w = $info[4] - $info[6];
            $string_h = $info[1] - $info[7]; //

            $x = 2; //text start point, x
            $y = $string_h+2;
            imagecopymerge($img, $wt, imagesx($img) - $sx - $marge_right, imagesy($img) - $sy - $marge_bottom, 0, 0,$sx,$sy, 66);
            break;
        case 2:
            break;
        case 3:
            break;
        case 4://center; logo: top left

            $string_w = $info[4] - $info[6];
            $string_h = $info[1] - $info[7]; //

            $x = (imagesx($img) - $string_w) / 2; //in the middle of the picture
            $y = (imagesy($img) + $string_h) / 2; //middle
            imagecopymerge($img, $wt, 1, 1, 0, 0, 60, 60, 50);//top left
            break;
    }

    imagettftext($img, $size, $angle, $x, $y, $color, $fontfile, $string);

    imagepng($img, $outputFile);
    imagedestroy($img);
    echo "<img src='.$outputFile.'>";
}

function noPicture() {
    $errInfo = "Sorry, but maybe you have input an invalid picture ID. Make sure you have permission to this picture.";
    $imgfile = "nodata.jpg";
    include('../common/errpage.php');
    include('../common/footer.php');
}

//ABANOUB'S ADDED FUNCTIONS
// QUERY to get accessbility options from server
function getAccessibility($myPdo) {

    $sqlStatement = 'SELECT * FROM accessibility';


    $resultSet = $myPdo->query($sqlStatement);
    $result = $resultSet->fetchAll();

    return $result;
}

// INSERT new album to database
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function createAlbum($myPdo, $title, $description, $ownerId, $accessibilityCode) {

    // Checks if $description is empty and send empty string
    if (!isset($description) || strlen(trim($description)) == 0) {
        $description = "";
    }

    $sqlStatement = "INSERT INTO ALBUM(Title,Description,Date_Updated,Owner_Id ,Accessibility_Code) VALUES(:title,:description,CURRENT_DATE(),:ownerId,:accessibilityCode)";

    $pStmt = $myPdo->prepare($sqlStatement);

    $pStmt->execute(['title' => $title, 'description' => $description, 'ownerId' => $ownerId, 'accessibilityCode' => $accessibilityCode]);
}

// GET All albums for user
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function getUserAlbums($myPdo, $userId) {


//     $sqlStatement = "SELECT Album.Album_Id , album.Title , album.Description , album.Date_Updated, album.Album_Id , album.Accessibility_Code , album.Description
// FROM Album
// INNER JOIN Accessibility ON Album.Accessibility_Code=Accessibility.Accessibility_Code
// WHERE Owner_id = :ownerId";
    $sqlStatement = "SELECT Album.Album_Id , album.Title , album.Description , album.Date_Updated, album.Album_Id , album.Accessibility_Code , album.Description
FROM Album
WHERE Owner_id = :ownerId";
    $pStmt = $myPdo->prepare($sqlStatement);

    $pStmt->execute(['ownerId' => $userId]);
    $result = $pStmt->fetchAll();

    return $result;
}

// GET count of pictures for album
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function countPicturesInAlbum($myPdo, $albumId) {
    $sqlStatement = "SELECT count(*) FROM Picture WHERE picture.Album_Id=:albumId;";

    $pStmt = $myPdo->prepare($sqlStatement);
    $pStmt->execute(['albumId' => $albumId]);
    $result = $pStmt->fetch();

    return $result[0];
}

// Delete Album and All it's pictures
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function deleteALbum($myPdo, $albumId) {

    $sqlStatement = "DELETE FROM Album WHERE Album_Id=:albumId";

    $pStmt = $myPdo->prepare($sqlStatement);

    $pStmt->execute(['albumId' => $albumId]);
}

// Delete All pictures in album
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function deletePictures($myPdo, $albumId) {

    $sqlStatement = "DELETE FROM picture WHERE Album_Id=:albumId";

    $pStmt = $myPdo->prepare($sqlStatement);

    $pStmt->execute(['albumId' => $albumId]);
}

// update all accessilibty for Albums
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
function updateAccessibility($myPdo, $albumId, $accessCode) {
    if ($accessCode != -1) {
        $sqlStatement = "UPDATE album SET Accessibility_Code=:accessCode WHERE Album_Id=:albumId";

        $pStmt = $myPdo->prepare($sqlStatement);

        $pStmt->execute(['accessCode' => $accessCode, 'albumId' => $albumId]);
        return "Updated successfully!";
    } else {
        return "Please select accessibility!";
    }
}

//%%%%%%
function deleteComments($myPdo, $albumId) {


    $sqlStatement = "SELECT Picture_Id FROM Picture WHERE Album_Id=:albumId";

    $pStmt = $myPdo->prepare($sqlStatement);



    $pStmt->execute(['albumId' => $albumId]);
    $result = $pStmt->fetchAll();

    foreach ($result as $item) {
        $sqlStatement = "DELETE FROM Comment WHERE Picture_id=:pictureId";
        echo $sqlStatement;
        $pStmt = $myPdo->prepare($sqlStatement);
        $pStmt->execute(['pictureId' => $item["Picture_Id"]]);
        //$result = $pStmt->fetchAll();
        //return "";
    }
}

//ADDED MY MOH!!!!!!!!!!!!!!!!!

function getUserName($userId, $myPdo) { // Return name of given user
    $sql = "select Name from User where UserId = :userId";
    try { // Connect to DB with PDO
        //prepared statement
        $pStmt = $myPdo->prepare($sql);
        $pStmt->execute(array(':userId' => $userId));
        return $pStmt->fetch(PDO::FETCH_ASSOC)['Name']; //returns a row from the result set. The parameter PDO::FETCH_ASSOC tells PDO to return the result as an associative array.
    } catch (PDOException $e) {
        //return $e->getMessage();
    }
}

?>