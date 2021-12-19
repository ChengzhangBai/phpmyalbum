<?php
session_start();

if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "UploadPictures";
    header("Location: Login.php");
    exit();
} else {
    $_SESSION['activePage'] = "UploadPictures";
}

$siteTitle = "Upload Pictures";

include("../common/functions.php");
include("../common/Constants.php");
include("../common/ImageHandler.php");


$dbConnection = parse_ini_file("../common/db_connection.ini");
extract($dbConnection);
$myPdo = new PDO($dsn, $user, $password);

if (isset($_POST['btnReset'])) {
    $_POST['album'] = $_POST['description'] = $_POST['title'] = "";
} else if (isset($_POST['btnUpload'])) {
    $title = htmlspecialchars(trim($_POST["title"]));
    $album = $_POST["album"] ?: "1";
    $desc = htmlspecialchars(trim($_POST["description"]));
  

    file_exists(ORIGINAL_PICTURES_DIR) ?: mkdir(ORIGINAL_PICTURES_DIR);
    file_exists(ALBUM_PICTURES_DIR) ?: mkdir(ALBUM_PICTURES_DIR);
    file_exists(THUMBNAILS_DIR) ?: mkdir(THUMBNAILS_DIR);
    for ($j = 0; $j < count($_FILES['file-upload']['tmp_name']); $j++) {
        if ($_FILES['file-upload']['error'][$j] == 0) {                //$fileName = $pathInfo['filename'];
            $fileTempPath = $_FILES['file-upload']['tmp_name'][$j];
            $filePath = ORIGINAL_PICTURES_DIR . "/" . $_FILES['file-upload']['name'][$j];
            //$filePath = $destination."/".$fileName;
            $pathInfo = pathinfo($filePath);
            $dir = $pathInfo['dirname'];
            $ext = $pathInfo['extension'];

            $rand = rand(10000, 99999);
            $fileName = date("Ymdhms") . $rand . "." . $ext;
            $filePathName = $dir . "/" . $fileName;
            //echo $filePathName;
            $filePathThumbName = THUMBNAILS_DIR . "/" . $fileName . "." . $ext;
            $waterMarkFilePath = ALBUM_PICTURES_DIR . "/watermarked" . $fileName;
            
            move_uploaded_file($fileTempPath, $filePathName);//SAVE to original folder
            $imageDetails = getimagesize($filePathName);
            if ($imageDetails && in_array($imageDetails[2], $supportedImageTypes)) {
                resamplePicture($filePathName, ALBUM_PICTURES_DIR, IMAGE_MAX_WIDTH, IMAGE_MAX_HEIGHT);
                CreateWaterMark($filePathName, $waterMarkFilePath, GROUP_NAME,4);//1-logo top left, text bottom right; 4:logo bottom right
                //watermarkImage($filePathName, $waterMarkFilePath, GROUP_NAME,1);//1,top left, 2: 4.center
                resamplePicture($filePathName, THUMBNAILS_DIR, THUMB_MAX_WIDTH, THUMB_MAX_HEIGHT);
            }

            InsertPicInfo($fileName, $album, $title, $desc, $myPdo);
            $err = "Uploaded " . count($_FILES['file-upload']['tmp_name']) . " file(s) successfully! <a href='MyPictures.php?id=" . $album . "'>View Uploaded Pictures</a>";
        } elseif ($_FILES['file-upload']['error'][$j] == 1) {
            $err = "$fileName is too large <br/>";
        } elseif ($_FILES['file-upload']['error'][$j] == 4) {
            $err = "No upload file specified <br/>";
        } else {
            $err = "Error happened while uploading the file(s). Try again late<br/>";
        }
    }
}

function InsertPicInfo($fileName, $album, $title, $desc, $myPdo) {
    ValidateUser($album, $myPdo);

    $sql = "INSERT INTO picture (Album_Id, FileName,Title, Description, Date_Added) VALUES (:Album_Id, :FileName, :Title, :Description, CURDATE())";
    //$sqlEcho = "INSERT INTO picture (Album_Id, FileName,Title, Description, Date_Added) VALUES ($album,'$fileName','$title','$desc', CURDATE())";
    //echo $sqlEcho;
    $date = date("Y/m/d");
    $uSql = "UPDATE album set date_updated = :dateUpdated WHERE album_id = :albumId";
    $pSql = $myPdo->prepare($sql);
    $pSql->execute(["Album_Id" => $album, "FileName" => $fileName, "Title" => $title, "Description" => $desc]);
    $pStmt = $myPdo->prepare($uSql);
    $pStmt->execute(["albumId" => $album, "dateUpdated" => $date]);
}

function ValidateUser($album, $myPdo) {
    //echo "validate".$album;
    // $sqlValidate="SELECT Owner_Id FROM album where Owner_Id="".$_SESSION["userId"]."' AND Album_Id=".$album;
    $sqlValidate = "SELECT Owner_Id FROM album where Owner_Id=:userId AND Album_Id=:album";
    $pSqlValidate = $myPdo->prepare($sqlValidate);
    $pSqlValidate->execute(["userId" => $_SESSION["userId"], "album" => $album]);
    if (!$pSqlValidate->rowCount()) {
        echo "Sorry, this album do not belong to you!";
        return;
    }
}

include ('../common/Header.php');
?>

<style>
<?php include '../common/css/site.css'; ?>
</style>

<div class="container">
    <form method="post"  enctype="multipart/form-data">

        <style>
            .bodyText
            {
                margin-left: 50px;
                margin-top: 40px;
            }
            .top5{
                margin-top: 5px;
                margin-bottom: 5px;
            }
            .custom-file-upload {
                border: 1px solid #ccc;
                display: inline-block;
                padding: 6px 12px;
                width:256px;
                cursor: pointer;
            }

        </style>
        <div class="row">
            <div class="offset-md-1 col-md-1 "></div>
            <div class="col-md-5 col-sm-5 col-xl-5 col-xs-5"><h1>Upload Pictures</h1></div>
        </div>    

        <div class="row">
            <div class="offset-md-1 col-md-6 col-sm-6">
                <p style="color:red"><?php echo $err ?></p>
                <p>Accepted picture type: JPG(JPEG), GIF and PNG.</p>
                <p>You can upload multiple pictures at a time by pressing the shift key while selecting pictures.</p>
                <p>When uploading multiple pictures, the title and description fields will be applied to all pictures.
                </p></div>
        </div>
        <div class="row">
            <div class="offset-md-1 col-md-2"><label for="title">Upload to Album:</label></div>
            <div class="col-md-2 col-sm-2">
                <select name="album" class="form-control" style="width:256px">
                    <?php getAlbum($myPdo, $_GET["albumId"]) ?>
                </select>
            </div>

        </div>
        <div class="row">
            <div class="offset-md-1 col-md-2 top5"><label for="title">File to Upload:</label></div>
            <div class="col-md-6 col-sm-6 top5">
                <label for="file-upload" class="custom-file-upload">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Image(s)
                </label>
                <input id="file-upload" class="form-control" name="file-upload[]" type="file" style="display:none;" accept="image/png, image/gif, image/jpg, image/jpeg" multiple onchange="updateList()">
                <div id="fileList"></div>
            
            </div>
        </div>
        <div class="row">
            <div class="offset-md-1 col-md-2"><label for="title">Title:</label></div>
            <div class="col-md-2 col-sm-2"><input class="form-control resizable" type="text" name="title" id="title" style="width:255px"></div>
        </div>
        <div class="row top5">
            <div class="offset-md-1 col-md-2"><label for="description">Description:</label></div>
            <div class="col-md-2 col-sm-2"><textarea class="form-control md-textarea resizable" name="description" id="description" style='height:150px;width:255px'></textarea></div>
        </div>
        <div class="row top5">
            <div class="offset-md-1 col-md-1"></div>
            <div class="offset-md-1 col-md-1 top5">
                <input type="hidden" name="btnUpload">
                <button class="btn btn-primary" name="btnUpload1" id="submit" onclick="loading()" type="submit"/>
                <i class="fa fa-spinner fa-spin" style="display: none"></i>
                <span class="btn-text">Upload</span>
            </div>
            <div class="col-md-1 col-sm-1 top5"><input type="submit" class="btn btn-primary" name="btnReset" value="Clear" /></div>
        </div>
    </form>
</div>
<script>
    updateList = function () {
        var input = document.getElementById('file-upload');
        var output = document.getElementById('fileList');
        var j=0;
        output.innerHTML = '<ol>';
        for (var i = 0; i < input.files.length; ++i) {
            output.innerHTML += '<li>' + input.files.item(i).name + '</li>';
            j++;
        }
        output.innerHTML += '</ol>';
        output.innerHTML += "<span style='color:red'>"+j+"</span> files selected.";
    }
</script>

<br/>
<br/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="../common/js/validate.js"></script>
<?php
include "../common/footer.php";
?>