<?php

//SESSION
session_start();
$siteTitle = "Sign Up";


//VARIABLES: setting variable values in PHP to use later on
$name = htmlspecialchars($_POST["name"]);
$nameError = "";
$userNameId = htmlspecialchars($_POST["userNameId"]);
$userIdError = "";
$phoneNumber = htmlspecialchars($_POST["phoneNumber"]);
$phoneNumberError = "";
$userPassword = htmlspecialchars($_POST["password"]);
$userPasswordError = "";
$userPasswordAgain = htmlspecialchars($_POST["passwordAgain"]);
$userPasswordAgainError = "";
$contact = htmlspecialchars($_POST["contact"]);
$contactError = "";
$valid = false;


//ENSURING THAT PREVIOUSLY ENTERED VALUES REMAIN AS INPUTS FOR THE USER (UNLESS CHANGED)
if ($_SESSION['signUpUserSuccess'] == true) { //if it's successful, it still saves the variables to later on display the inputted information unless it's cleared or changed
    $_POST['name'] = $_SESSION['name'];
    $_POST['userNameId'] = $_SESSION['userNameId'];
    $_POST['phoneNumber'] = $_SESSION['phoneNumber'];
    $_POST["password"] = $_SESSION['password'];
    $_POST["passwordAgain"] = $_SESSION['passwordAgain'];
    
}
$_SESSION["signUpUserSuccess"] = false;


//FUNCTIONS FOR VALIDATION
function ValidateName($name) {
    if ($name == "") {
        return "empty";
    }
}

function ValidateUserId($userNameId) {
    if ($userNameId == ""){
        return "empty";
    }
}

function ValidatePhoneNumber($phoneNumber) {
    $phoneNumberExpression = "/^[2-9][0-9][0-9]-[2-9][0-9][0-9]-[0-9][0-9][0-9][0-9]$/";
    $validPhoneNumber = (bool) preg_match($phoneNumberExpression, $phoneNumber);

    if ($phoneNumber == "") {
        return "empty";
    } elseif ($validPhoneNumber == false) {
        return "incorrect";
    }
}

function ValidatePassword($userPassword) {
    $userPasswordExpression = '/(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{6,}/';
    $validPassword = (bool) preg_match($userPasswordExpression, $userPassword);
        if ($userPassword== ""){
            return "empty";
        } elseif ($validPassword == false){
            return "incorrect";
        }
}

function ValidatePasswordAgain($userPassword, $userPasswordAgain) {
        if ($userPasswordAgain == ""){
            return "empty";
        } elseif ($userPassword !== $userPasswordAgain){
            return "mismatch";
        }
}


// VALIDATING ON SUBMIT:
if (isset($_POST['submit'])) {

    if (ValidateName($name) == "empty") {
        $nameError = "Name is required!";
    }

    if (ValidateUserId($userNameId) == "empty") {
        $userIdError = "User ID is required!";
    }

    if (ValidatePhoneNumber($phoneNumber) == "empty") {
        $phoneNumberError = "Phone Number is required!";
    } elseif (ValidatePhoneNumber($phoneNumber) == "incorrect") {
        $phoneNumberError = "Incorrect Phone Number!";
    }
   
    if (ValidatePassword ($userPassword) == "empty") {
        $userPasswordError = "Password is required!";
    } elseif (ValidatePassword($userPassword) == "incorrect") {
        $userPasswordError = "Password must be at least 6 characters long, contain at least one upper case, one lowercase and one digit";
    }
    
    if (ValidatePasswordAgain ($userPassword, $userPasswordAgain) == "empty") {
        $userPasswordAgainError = "Password Again is required!";
    } elseif (ValidatePasswordAgain($userPassword, $userPasswordAgain) == "mismatch") {
        $userPasswordAgainError = "Passwords do not match!";
    }

    //saving the values as session variables if there are no errors
    if ($nameError == "" && $userIdError == "" && $phoneNumberError == "" && $userPasswordError == "" && $userPasswordAgainError == "") {
            
            //Connection to DBO
            $dbConnection = parse_ini_file("../common/db_connection.ini");        	
            extract($dbConnection);
            $myPdo = new PDO($dsn, $user, $password);
            
            //Query database for requested UserId            
            $sqlStatement = 'SELECT * FROM User WHERE UserId = :placeHolderUserID';
            $pStmt = $myPdo->prepare($sqlStatement);       
            $pStmt ->execute(array(':placeHolderUserID' => $userNameId));      
            $chkAccount = $pStmt->fetch(); //store first result of query to $chkAccount            
            
            if ($chkAccount['UserId'] == "") //user does not exist
            {
                $valid = true;
                $_SESSION['name'] = htmlspecialchars($_POST['name']);
                $_SESSION['userNameId'] = htmlspecialchars($_POST['userNameId']);
                $_SESSION['phoneNumber'] = htmlspecialchars($_POST['phoneNumber']);
                $_SESSION['password'] = htmlspecialchars($_POST['password']);
                $_SESSION['passwordAgain'] = htmlspecialchars($_POST['passwordAgain']);
                $_SESSION["signUpUserSuccess"] = true; //to use so inputted values remain
                
                //hashed password
                $hashedPassword = hash("sha256", $userPassword);
                $sql = "INSERT INTO User VALUES( :studId, :studName, :studPhoneNumber, :studPassword)";
                $pStmt = $myPdo->prepare($sql);
                $pStmt->execute(array(':studId' => $userNameId, ':studName' => $name, ':studPhoneNumber' => $phoneNumber, ':studPassword' => $hashedPassword));
                $pStmt->commit;      
            }
            else //if user already exists
            { 
                $userIdError = "A user with this ID has already signed up!";                 
            }      
    }
}

//RESETTING VALUES ON CLEAR
if(isset($_POST['reset']))
{
    $_POST = array();
}


if ($valid) : 
    
        $_SESSION['sessionId'] = "";
        header("Location: MyAlbums.php"); //once it directs to this page, it redirects to the login page since the user only signed up and didn't log in yet
        exit();
endif; 
        
include '../common/Header.php';?>

<style>
<?php include '../common/css/site.css'; ?>
</style>
<style type = "text/css">
    .form-control {width: unset; min-width: 90%; max-width: 100%;}
    .form-fix {width: unset; min-width: 90%; max-width: 95%;}
    td {
        padding: 5px;
    }
    .outputTitle {font-size: 1.8rem;}
    .outputText {font-size: 1rem;}
    .table {max-width: 690px;}
    .indentPage {margin-left: 20px;}
</style>
    
<div class="container">
<?php if (!$valid) : ?>
            <div>
            <!--<h1 class="display-4">Sign Up</h1>-->
            <h1>Sign Up</h1>

            <form method = "post" class="form-fix form-horizontal" action="NewUser.php" name="signUpUser">
      
                <table class="tableSpacing">           

                    <tr>
                        <td><h5>All fields are required:</h5></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">User ID:</td><td><input type = "text" name = "userNameId" class="form-control ml-4"/></td>
                        <td><span style='color:red' ><?php print $userIdError; ?></span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Name:</td><td><input type = "text" name = "name" class="form-control ml-4"/></td>
                        <td><span style='color:red' ><?php print $nameError; ?></span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Phone Number: </br> <span class="small">(nnn-nnn-nnn)</span></td><td><input type = "text" name = "phoneNumber" class="form-control ml-4"/></td>
                        <td><span style='color:red' ><?php print $phoneNumberError; ?></span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Password:</td><td><input name = "password" class="form-control ml-4"  id="myInput" type="password"/></td>
                        <td><i class="fa fa-eye" id="togglePassword"></i>&nbsp; <span style='color:red' ><?php print $userPasswordError; ?></span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Password Again:</td><td><input name = "passwordAgain" class="form-control ml-4" type="password"/></td>
                        <td><span style='color:red' ><?php print $userPasswordAgainError; ?></span></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td>
                            
                              <button class="btn btn-primary ml-4" id="submit" type="submit" name="submit">
                                <span class="btn-text">Submit</span>
                             </button>
                            <!--
                             <button class="btn btn-primary ml-3" id="submit" onclick="loading()" type="submit" name="submit">
                                <i class="fa fa-spinner fa-spin" style="display: none"></i>
                                <span class="btn-text">Submit</span>
                             </button>
                            -->
                            <!--
                             <input class="btn btn-primary ml-3" type = "submit" name="submit" value = "Submit" />-->
                             <input class="indentPage btn btn-primary ml-4" type = "submit" name="reset" value = "Clear" />
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
                <?php if ($value !== "" && $field !== "submit" && $field !== "reset") : ?>
                                document.signUpUser.<?php echo $field; ?>.value = <?php echo '"' . $value . '"'; ?>;
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
<?php include('../common/footer.php'); ?>