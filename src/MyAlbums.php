<?php

session_start();

if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "MyAlbums" ;
    header("Location: Login.php");
    exit();
}
else{
    $_SESSION['activePage'] = "MyAlbums";
}

$siteTitle = "My Albums";


include("../common/conn_db.php");
include("../common/functions.php");

    
$_SESSION['name'] = getUserName($_SESSION['userId'], $myPdo);

extract($_POST);

function displayAccessibilityddlSelected($myPdo, $selectedValue, $albumId) {
    $result = getAccessibility($myPdo);

    $html = "<select name='accessCodes[]' id='acc-Code' class='form-control-sm'><option value='-1'>Select Accessibility</option>";

    foreach ($result as $value) {
        $acc = $value['Accessibility_Code']== "private" ? "&#xf023; " : "&#xf09c; ";
        if ($value['Accessibility_Code'] == $selectedValue) {
            
            $html .= "<option value='" . $value['Accessibility_Code'] . "'selected >" .$acc. $value['Description'] . "</option>";
        } else {
            $html .= "<option value='" . $value['Accessibility_Code'] . "' >" .$acc.$value['Description'] . "</option>";
        }
    }
    $html .= "</select>";


    return $html;
}

// Display Albums table
function displayAlbums($pdo, $userId) {
    
    $html = "";

    $result = getUserAlbums($pdo, $userId);


    foreach ($result as $row) {
        $html .= "<tr>";
        $html .= "<td> <a href='./MyPictures.php?id=".$row['Album_Id']."'>" . $row['Title'] . "</a></td>";
        $html .= "<td>" . $row['Date_Updated'] . "</td>";
        $html .= "<td>" . countPicturesInAlbum($pdo, $row['Album_Id']) . "</td>";

        $html .= "<td>" . displayAccessibilityddlSelected($pdo, $row['Accessibility_Code'], $row['Album_Id']) . "</td>";
        $html .= "<td> <a href='../common/DeleteAlbum.php?albumId=" . $row['Album_Id'] . "' onclick='return confirm(\"Once deleted, album cannot be recovered. Are you sure?\")'>" . "delete" . "</a></td>";
        $html .= "</tr>";
    }
 
    $html .= "<tr><td></td><td></td><td></td><td style='text-align:right;'><input type=submit name='save' value='Update' class='btn btn-primary' style='width:30%'/></td></tr>";
    return $html;
}

// When save is clicked
if (isset($_POST['save'])) {
    $albums = getUserAlbums($myPdo, $_SESSION["userId"]);
    $accessCodesList = $accessCodes;

    for ($i = 0; $i < count($accessCodesList); $i++) {
         //if($albums[$i]['accessCodes']==-1){echo "incorrect";}  
        $msg = updateAccessibility($myPdo, $albums[$i]['Album_Id'], $accessCodesList[$i]);
        //$msg="Updated successfully!";
    }
}

include ('../common/Header.php');?>

<style>
<?php include '../common/css/site.css'; ?>
</style>

<div class="container">

    <h1>My Albums</h1>
    <br><p> Welcome <b><?php print $_SESSION['name'];?></b>!
        (Not you? change user <a href="./Logout.php">here</a>), the following is your currently registered courses:</p>
    <br>
    <div style="float:right">
        <a href="./AddAlbum.php">Create New</a>
    </div>
    <div style="color:red"><?php echo $msg?:$_GET["msg"];?></div>
    <br>
    <form method="post"  action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF "]); ?>">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-md-3">Title</th>
                    <th>Date Updated</th>
                    <th>Number of Pictures</th>
                    <th>Accessibility</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <?php echo displayAlbums($myPdo, $_SESSION["userId"]); ?>

            </tbody>
        </table>

    </form>
</div>
<br/>
<br/>
<br/>

<?php include('../common/footer.php'); ?>