<?php

session_start();

if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "AddAlbum" ;
    header("Location: Login.php");
    exit();
}
else{
    $_SESSION['activePage'] = "AddAlbum";
}

$siteTitle = "Add Album";


include("../common/conn_db.php");
include("../common/functions.php");

$_SESSION['name'] = getUserName($_SESSION['userId'], $myPdo);

extract($_POST);

// Display accessibility ddl
function displayAccessibilityddl($myPdo,$selectedValue) {
    $result = getAccessibility($myPdo);
    $html = "<select name='accessibilityCode' class='form-control'><option value=-1>Select Accessibility</option>";

    foreach ($result as $value) {
        $sel = $selectedValue==$value['Accessibility_Code']?" selected ":"";
        $acc = $value['Accessibility_Code']== "private" ? "&#xf023; " : "&#xf09c; ";
        $html .= "<option value='" . $value['Accessibility_Code'] . "'".$sel." >" .$acc. $value['Description'] . "</option>";
    }
    $html .= "</select>";


    return $html;
}

// When form is submitted
if (isset($_POST['submit'])) {

    if (isset($title) && strlen(trim($title)) != 0 && $accessibilityCode != '-1') {
        
        createAlbum($myPdo, $title, $description, $_SESSION['userId'], $accessibilityCode);
        $msg = "Created successfully!";
    }
    else{
        $msg="Please make sure to fill in at least the title, and select accessibility!";
    }
}

//RESETTING VALUES ON CLEAR
if(isset($_POST['reset']))
{
    $_POST = array();
}

include ('../common/Header.php');?>

<style>
<link rel="stylesheet" type="text/css" href="./Common/css/Site.css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</style>
        

<div class="container">

    <h1>Create new album</h1>
    <br><p> Welcome <b><?php print $_SESSION['name'];?></b>!
        (Not you? change user <a href="./Logout.php">here</a>), the following is your currently registered courses:</p>
    <br>
   <div style='color:red'><?php echo $msg?></div> <br>
    <form method='POST' action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <table class="form">
            <tr>
                <td><label for="Title" class="font-weight-bold">Title</label></td>
                <td><input type="text" name="title" class="form-control" value="<?php echo $_POST["title"]?>"/></td>
            </tr>
            <tr>
                <td><br><label for="Accessibility" class="font-weight-bold">Accessibility</label></td>

                <!--  Drop down list display-->
                <?php $selectedValue=$_POST["accessibilityCode"]?>
                <td><br><?php echo displayAccessibilityddl($myPdo,$selectedValue); ?></td>
            </tr>

            <tr><td>
                    
                    <label for="textarea"  style="vertical-align:top" class="font-weight-bold">Description</label>
                </td>
                <td><br>
                    <textarea name="description" rows="4" cols="50" class="form-control" style="resize:none"><?php echo $_POST["description"]?></textarea>
                </td>
            </tr>
            <tr >
                <td>
               &nbsp; <span style="display:none">t</span>  
                </td><td><input type="submit" value="Submit" name="submit" class="btn btn-primary top5" /> 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Clear" class="btn btn-primary top5"/> </td>
            </tr>
        </table>
    </form>


</div>

<?php include('../common/footer.php'); ?>