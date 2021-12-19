<?php
//header('Content-Type: text/css');
//SESSION
session_start();
$_SESSION['activePage'] = "Home";
$loggedIn;

if ($_SESSION['sessionId'] == "") {
    $loggedIn = false;
}
else{
    $loggedIn = true;
    header('Location: ./src/MyAlbums.php'); //redirect to albums page if logged in (this page can change)
    exit();  

}

$siteTitle = "Home";

include './common/Header.php';?>
<style>
<?php include './common/css/site.css'; ?>
</style>

<div class="container">
   
<?php if (!$loggedIn) : ?>

    
        <h1>Welcome to A-Team's Social Media Website</h1><br>
        <p>If you have never used this before, you have to <a href="./src/NewUser.php">sign up</a> first.</p>
        <p>If you have already signed up, you can <a href="./src/Login.php">log in</a> now.<p>

</div>

<?php include './common/Footer.php'; 

endif; 

?> 