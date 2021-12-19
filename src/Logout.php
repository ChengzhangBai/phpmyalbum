<?php 
    session_start();
    if ($_SESSION['activePage'] = "AddAlbum" || $_SESSION['activePage'] = "MyAlbums" || $_SESSION['activePage'] = "AddFriend" || $_SESSION['activePage'] = "MyFriends")
    {
            header('Location: Login.php');
    }
    else{
            header('Location: Index.php');
    }
    session_destroy();
    exit;        
    
    $siteTitle = "Logout";
?>



<?php include './common/Header.php';?>
<style>
<?php include './common/css/site.css'; ?>
</style>


<div class="container multiply-font">
</div>


<?php include './common/Footer.php'; ?>

