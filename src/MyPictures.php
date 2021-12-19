<?php
session_start();

if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "MyPictures";
    header("Location: Login.php");
    exit();
} else {
    $_SESSION['activePage'] = "MyPictures";
}

$siteTitle = "My Pictures";
$dbConnection = parse_ini_file("../common/db_connection.ini");
extract($dbConnection);
$myPdo = new PDO($dsn, $user, $password);
?>

<?php
session_start();
include("../common/Header.php");
include("../common/Constants.php");
include("../common/Picture.php");
include("../common/functions.php");
include("../common/ImageHandler.php");
?>
<link rel="stylesheet" type="text/css" href="../Common/css/Site.css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<?php
//Get album info based on session
$sql = "SELECT distinct p.album_id as album_id, p.picture_id as picture_id, p.filename as filename, "
        . "p.title as title, p.description as description, p.date_added as date_updated,a.Accessibility_Code as accessibility_code  "
        . "FROM picture p "
        . "INNER JOIN album a WHERE  p.Album_Id=a.Album_Id AND  a.Owner_Id = :userID GROUP BY album_id";
$pStmt = $myPdo->prepare($sql);
$pStmt->execute(["userID" => $_SESSION["userId"]]);
$albums = $pStmt->fetchAll();

//
if ($_GET["pic_id"]) {
    $sql = "SELECT a.album_id as album_id FROM album a INNER JOIN picture p ON a.album_id=p.album_id WHERE p.picture_id=:picture_id";
    $pStmt = $myPdo->prepare($sql);
    $pStmt->execute(["picture_id" => $_GET["pic_id"]]);
    $pic = $pStmt->fetch();
    //echo $pic[0]."recordset ";
}

if ($albums) {
    $selectAlbum = $pic[0] ?? $_GET['id'] ?? $_POST['chooseAlbum'] ?? $albums[0][0]; //get>post>recordset,    
    $selected_img_id = $_GET['pic_id'];


    $imgs = Picture::getPictures($myPdo, $selectAlbum);
    $idx = 0; //initial selection

    if (!empty($imgs)) {//album has pictures
        if (isset($_POST['selectedImage'])) {
            $selected_img_id = intval($_POST['selectedImage']);
        }
        if ($selected_img_id != "") {
            $size = count($imgs);
            //get the array id based on the picture Id
            for ($i = 0; $i < $size; $i++) {
                if ($imgs[$i]->getId() == $selected_img_id) {
                    $idx = $i;
                    break;
                }
            }
        }

        if (isset($_POST['addComment'])) {
            if ($_POST['commentTxt'] != "") {
                //inserts picture comment in DB
                try {
                    $commentTxt = htmlspecialchars($_POST['commentTxt']);
                    $sql = "INSERT INTO comment(Author_Id, Picture_Id, Comment_Text, Date) "
                            . "VALUES (:userId, :pictureId, :commentTxt, NOW())";
                    $pStmt = $myPdo->prepare($sql);
                    $pStmt->execute(['userId' => $_SESSION["userId"], 'pictureId' => $selected_img_id, 'commentTxt' => $commentTxt]);
                    $pStmt->commit;
                    exit(header('Location: MyPictures.php?id=' . $selectAlbum . '&pic_id=' . $selected_img_id));
                } catch (PDOException $e) {
                    $commentError = $e->getMessage();
                }
            } else {
                $commentError = "Comment cannot be blank!";
            }
        }

        //gets the file path to display as main picture
        $imageFilePath = $imgs[$idx]->getAlbumFilePath();
        $selected_img_id = $imgs[$idx]->getId();
    }
}


if (isset($_GET['action'])) {
    //Rotate, downloads or deletes the selected Image, according to the informed action

    switch ($_GET['action']) {
        case 'rotateCounter':
            $img = Picture::getPicture($myPdo, $_GET["pic_id"]);
            if (!$img) {//if no pic found
                noPicture();
                exit();
            }
            $img->rotatePicture(90);
            break;
        case 'rotateClock':
            $img = Picture::getPicture($myPdo, $_GET["pic_id"]);
            if (!$img) {//if no pic found
                noPicture();
                exit();
            }
            $img->rotatePicture(-90);
            break;
        case 'download':
            $img = Picture::getPicture($myPdo, $_GET["pic_id"]);
            if (!$img) {//if no pic found
                noPicture();
                exit();
            }

            $file = $img->downloadFile();
            break;
        case 'delete':
            $img = Picture::getPicture($myPdo, $_GET["pic_id"]);
            if (!$img) {//if no pic found
                noPicture();
                exit();
            }

            $commentError = $img->deleteFile($myPdo);
            if ($commentError == "") { //successfully deleted the file
                exit(header('Location: MyPictures.php?id=' . $_GET["album_id"]));
            }
            break;
    }
}
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <div class="container">
        <div class="row">
            <div class="col-lg-2 col-md-2"></div>

            <div class="col-lg-4 col-sm-4 text-center">  <h1>My Pictures</h1>  </div>
            <!--    </div>
            
                <div class="row">-->
             <div class="col-lg-1 col-md-1"></div> 
            <div class='col-lg-3 col-md-3'>
                <select name='chooseAlbum' class='form-control top5' id="chooseAlbum">
                    <?php getAlbumWithDate($_SESSION["userId"], $myPdo, $selectAlbum); ?>                 
                </select>
            </div>
        </div>
    </div>
    <?php
    if (!empty($imgs)) {
        ?>



        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 row">
            <div class="col-lg-2 col-md-2"></div>
            <div class='col-lg-8 col-md-8'>
                <h2><?php echo $imgs[$idx]->getTitle(); ?></h2>
            </div>
        </div>

        <div class="container-fluid col-lg-12 col-md-12 col-sm-12 col-xs-12 row">
            <div class="col-lg-1 col-md-1"></div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2"></div>
                <div class="img-container col-lg-11 col-md-11 col-sm-11 col-xs-11">
                    <img id="myImg" alt="<?php echo $imgs[$idx]->getTitle() ?: "Uploader: " . $imgs[$idx]->getOwnerId(); ?>" class="img-rounded  img-fluid" src="<?php echo $_GET["pic_id"] ? getImgNameById($myPdo, $_GET["pic_id"]) : $imageFilePath; ?>?rnd=<?php echo rand(); ?>" />


                    <!-- The Modal -->
                    <div id="myModal" class="modal">
                        <div id="caption"></div>
                        <img class="modal-content" id="img01">
                        <div id="modalFooter"> </div>
                    </div>
                    <script>
                        // Get the modal
                        var modal = document.getElementById("myModal");
                        // Get the image and insert it inside the modal - use its "alt" text as a caption
                        var img = document.getElementById("myImg");
                        var modalImg = document.getElementById("img01");
                        var captionText = document.getElementById("caption");
                        var footerText = document.getElementById("modalFooter");
                        img.onclick = function () {
                            modal.style.display = "block";
                            //modalImg.src = this.src;
                            modalImg.src = '<?php echo $imgs[$idx]->getOriginalFilePath(); ?>?rnd=<?php echo rand();?>';
                            modalImg.style.cursor = "pointer";
                            captionText.innerHTML = this.alt;
                            footerText.innerHTML = "<a title='Rotate Clockwise' href=?action=rotateCounter&pic_id=<?php echo $imgs[$idx]->getId(); ?>><i class='fa fa-repeat gly-flip-horizontal'></i></a><a title='Rotate Counterclockwise' href=?action=rotateClock&pic_id=<?php echo $imgs[$idx]->getId(); ?>><i class='fa fa-repeat'></i></a><a title='Download picture' href=?action=download&pic_id=<?php echo $imgs[$idx]->getId(); ?>><span class='fa fa-cloud-download'></span></a><a href='#' title='Delete Picture' onclick='del();'><span class='fa fa-trash'></span></a>";

                        }

                        // Get the <span> element that closes the modal
                        var span = document.getElementsByClassName("close")[0];

                        // When the user clicks on <span> (x), close the modal
                        modalImg.onclick = function () {
                            modal.style.display = "none";
                        }
                        function del(pic_id) {
                            if (confirm("Once deleted, the picture will not be recovered. \nAre you sure to delete?")) {
                                window.location.href = "?action=delete&pic_id=<?php echo $imgs[$idx]->getId(); ?>&album_id=<?php echo $imgs[$idx]->getAlbumId(); ?>";
                            }
                        }
                    </script>

                </div>


                <div class="thumbnails" >
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="overflow-x: auto; white-space: nowrap;">
                        <?php
                        foreach ($imgs as $img) {
                            ?>
                            <a href='?id=<?php echo $selectAlbum ?>&pic_id=<?php echo $img->getId(); ?>'><img class='img-thumbnail rounded' src=<?php echo $img->getThumbnailFilePath(); ?>
                                                                                                              name="imgThumbnail"
                                                                                                              id=<?php
                    echo $img->getId();

                    if ($img->getId() == $selected_img_id) { //highlight selected image
                        echo' style="border: 3px solid blue;"';
                    }
                            ?> style="padding: 5px; white-space: nowrap;"></a>
                                                                                                              <?php
                                                                                                          }
                                                                                                          ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 side-comments">
                <div class="comments-list">
                    <?php
                    if ($imgs[$idx]->getDescription()) {
                        echo"<b>Description:</b>";
                        echo "<p>" . $imgs[$idx]->getDescription() . "</p>";
                    }
                    $comments = $imgs[$idx]->getComments($myPdo);
                    if (count($comments) > 0) {
                        echo"<b>Comments:</b>";
                        foreach ($comments as $comment) {
                            echo'<p><i style="color: blue">' . $comment[1] . ' ('
                            . $comment[2] . '):</i> ' . $comment[0] . '</p>';
                        }
                    }
                    ?>
                </div>
                <br/>
                <div class='form-group row'>
                    <div class='col-lg-11 col-md-11 col-sm-11 col-xs-11'>
                        <textarea  class='form-control' id='commentTxt'
                                   name='commentTxt' placeholder="Leave Comment..."
                                   style='height:150px'><?php
                                       if (isset($_POST['descriptionTxt'])) {
                                           echo $_POST['descriptionTxt'];
                                       }
                                       ?></textarea></div>
                </div>
                <div class='row'>
                    <div class='col-lg-6 col-md-8 col-sm-12 col-xs-12 text-left'>


                        <button class="btn btn-primary" name="addComment" id="submit" onclick="commenting()" type="submit"/>
                        <i class="fa fa-spinner fa-spin" style="display: none"></i>
                        <span class="btn-text">Add Comment</span>
                    </div>
                    <div class='col-lg-6 col-md-12 col-sm-12 col-xs-12 text-left' style="color: red;"><?php echo $commentError; ?></div>
                </div>
            </div>
        </div>
        <input type="submit" style="display: none" id="btnSubmit" name="btnSubmit" >
        <input type="hidden" name="selectedImage" 
               value="<?php echo $_GET["pic_id"] ?: $imgs[$idx]->getId(); ?>" /> 

    </form>



    <!--show large image and thumbnail images as well as comment area-->
    <?php
} else {
    ?>
    <input type="submit" style="display: none" id="btnSubmit" name="btnSubmit" >
    </form>
    <div class="row">
        <div class="col-lg-7 text-center">
            <img src="../common/img/404.gif"/>
            <h4>Click <a href="UploadPictures.php?albumId=<?php echo $selectAlbum ?>">here</a> to Upload Pictures.</h4>

        </div>
    </div>


    <?php
}
?>

<br/>
<br/>
<br/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="../common/js/validate.js"></script>

<?php
include "../common/footer.php";
?>