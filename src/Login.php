<?php

//SESSION
session_start();

if ($_SESSION['sessionId'] == "") {
     $_SESSION['activePage'] = "Login";
}
else{
    header("Location: MyAlbums.php");
    exit();
}
//$_SESSION['activePage'] = "Login";
$siteTitle = "Login";


//VARIABLES: setting variable values in PHP to use later on
$userId = htmlspecialchars($_POST["userId"]);
$userIdError = "";
$userPassword = htmlspecialchars($_POST["password"]);
$userPasswordError = "";
$mismatchIdAndPasswordError = "";
$valid = false;

//ENSURING THAT PREVIOUSLY ENTERED VALUES REMAIN AS INPUTS FOR THE USER (UNLESS CHANGED)
if ($_SESSION['loginUserSuccess'] == true) { //if it's successful, it still saves the variables to later on display the inputted information unless it's cleared or changed
    $_POST['userId'] = $_SESSION['userId'];
    $_POST["password"] = $_SESSION['password'];
}
$_SESSION["loginUserSuccess"] = false;


//FUNCTIONS FOR VALIDATION
function ValidateUserId($userId) {
    if ($userId == ""){
        return "empty";
    }
}

function ValidatePassword($userPassword) {
        if ($userPassword== ""){
        return "empty";}
}


// VALIDATING ON SUBMIT:
if (isset($_POST['submit'])) {

    if (ValidateUserId($userId) == "empty") {
        $userIdError = "User ID is required!";
    }

   
    if (ValidatePassword ($userPassword) == "empty") {
        $userPasswordError = "Password is required!";
    }

    //saving the values as session variables if there are no errors
    if ($userIdError == "" && $userPasswordError == "") {
        
        //Connection to DBO
        $dbConnection = parse_ini_file("../common/db_connection.ini");        	
        extract($dbConnection);
        $myPdo = new PDO($dsn, $user, $password);
        
        //$hashedPassword = sha1($userPassword); 
        $hashedPassword = hash("sha256", $userPassword);
        
        //Query database for requested UserId  
        $sqlStatement = 'SELECT * FROM User WHERE UserId = :placeHolderUserID AND Password = :placeHolderPassword';
        $pStmt = $myPdo->prepare($sqlStatement);       
        $pStmt ->execute(array(':placeHolderUserID' => $userId, ':placeHolderPassword' => $hashedPassword));      
        $chkAccount = $pStmt->fetch(); //store first result of query to $chkAccount     
        
        if ($chkAccount['UserId'] != "") //user is in database
        {                
                
            $valid = true;
            $_SESSION['userId'] = htmlspecialchars($_POST['userId']);
            $_SESSION['password'] = htmlspecialchars($_POST['password']);
            $_SESSION["loginUserSuccess"] = true; //to use so inputted values remain (this can be removed later for security purposes)
            
            $_SESSION['sessionId'] = "active"; //this is used to detect that we're logged in on the pages to be accessed by logging in
            $_SESSION['loggedInUserId'] = $chkAccount[1] ; //storing user's id in a session 
            
            //previous pages should include the 4 redirects from my friends, add friend, my albums, add album, and 2 on the navbar (my pictures, and upload pictures)
            if ($_SESSION['previousPage'] == "MyFriends") //once successful, it goes to the previously chosen page on the header before having been re-directed to the login page
            {
                header('Location: MyFriends.php');
                exit();  
            }
            elseif ($_SESSION['previousPage'] == "AddFriend")
            {
                header('Location: AddFriend.php');
                exit();  
            }
            elseif ($_SESSION['previousPage'] == "MyAlbums")
            {
                header('Location: MyAlbums.php');
                exit();  
            }
            elseif ($_SESSION['previousPage'] == "AddAlbum")
            {
                header('Location: AddAlbum.php');
                exit();  
            }
            elseif ($_SESSION['previousPage'] == "MyPictures")
            {
                header('Location: MyPictures.php');
                exit();  
            }
            elseif ($_SESSION['previousPage'] == "UploadPictures")
            {
                header('Location: UploadPictures.php');
                exit();  
            }
            else 
            {
                header('Location: MyFriends.php'); //if there was no previous page, it goes to the "My Friends" page (this can be changed)
                exit();  
            }
        }
        else //if user does not match the database
        { 
            $mismatchIdAndPasswordError = "Incorrect ID and/or password!";                 
        }       
    }
}


//RESETTING VALUES ON CLEAR
if(isset($_POST['reset']))
{
    $_POST = array();
}


if ($valid) : 
    
endif; 

include '../common/Header.php';?>

<style>
<?php include '../common/css/site.css'; ?>
</style>
<style type = "text/css">
    td {
        padding: 5px;
    }
    .outputTitle {font-size: 1.8rem;}
    .outputText {font-size: 1rem;}
    .table {max-width: 690px;}
    .indentPage {margin-left: 20px;}
    .form-control {width: unset; min-width: 90%; max-width: 100%;}
    .form-fix {width: unset; min-width: 90%; max-width: 95%;}
</style>
<script>
    function myFunction() {
      var x = document.getElementById("myInput");
      if (x.type === "password") {
        x.type = "text";
      } else {
        x.type = "password";
      }
    }
</script>
    
<div class="container">
<?php if (!$valid) : ?>
            <div>
                <!--<h1 class="display-4">Log In</h1><br>-->
                <h1>Log In</h1><br>
                <p>You need to <a href="/src/NewUser.php">sign up</a> if you are a new user.</p>

                <form method = "post" class="form-fix form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" name="loginUser">
                    <span style='color:red' ><?php print $mismatchIdAndPasswordError ?></span>
                    <table class="tableSpacing">           

                        <tr>
                            <td class="font-weight-bold">User ID:</td><td><input type = "text" name = "userId" class="form-control ml-4"/></td>
                            <td><span style='color:red' ><?php print $userIdError; ?></span></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Password:</td><td><input name = "password" class="form-control ml-4"  id="myInput" type="password"/>
                            </td>
                            <td><i class="fa fa-eye" id="togglePassword"></i>&nbsp; <span style='color:red' ><?php print $userPasswordError; ?></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <button class="btn btn-primary ml-3" id="submit" type="submit" name="submit">
                                   <span class="btn-text">Submit</span>
                                </button>
                                <!--
                                <button class="btn btn-primary ml-3" id="submit" onclick="loading()" type="submit" name="submit">
                                <i class="fa fa-spinner fa-spin" style="display: none"></i>
                                <span class="btn-text">Submit</span>
                             </button>
                                -->
                             <!--
                            <script>
                                function loading() {
                                  $(".btn .fa-spinner").show();
                                  $(".btn .btn-text").html("Loading");
                                }
                            </script>
                                -->
                                <!--<input class="btn btn-primary ml-3" type = "submit" name="submit" value = "Submit" />-->
                                <input class="indentPage btn btn-primary ml-3" type = "submit" name="reset" value = "Clear" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
<?php endif; ?>
</div>   

<?php if (!empty($_POST)): ?>
        <script>
            <?php foreach ($_POST as $field => $value) : ?>
                <?php if ($value !== "" && $field !== "submit" && $field !== "reset" && $field !== "chkAccount") : ?>
                                document.loginUser.<?php echo $field; ?>.value = <?php echo '"' . $value . '"'; ?>;
                <?php endif; ?> //usually it's <php echo $variableName> in that same section, or make it value = the php echo
            <?php endforeach; ?>
        </script>

<?php endif; ?>
              
<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#myInput');
 togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    // toggle the eye slash icon
    this.classList.toggle('fa-eye-slash');
});
</script>
<script src="../common/js/validate.js"></script>
<?php include '../common/Footer.php'; ?>