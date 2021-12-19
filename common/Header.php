<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $siteTitle; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/common/img/SocialMedia.png" />
  <link rel="stylesheet" href="/common/css/bootstrap4.5.2.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
  
  <link rel="stylesheet" href="/common/css/Site.css">
</head>
<body>

<nav class="navbar navbar-expand-md bg-dark navbar-dark sticky-top">
  <a class="" href="#"> <img img src="/common/img/SocialMedia.png" alt="Social Media"  style="width: 40%; height: 20%; max-width:10vm; max-height:10vh;"/></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "index.php" ? "active'" : "'" ?>><a class="nav-link" href="/index.php">Home</a></li>
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "MyFriends.php" ? "active'" : "'" ?>><a class="nav-link" href="/src/MyFriends.php">My Friends</a></li>
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "MyAlbums.php" ? "active'" : "'" ?>><a class="nav-link" href="/src/MyAlbums.php">My Albums</a></li>  
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "MyPictures.php" ? "active'" : "'" ?>><a class="nav-link" href="/src/MyPictures.php">My Pictures</a></li>            
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "UploadPictures.php" ? "active'" : "'" ?>><a class="nav-link" href="/src/UploadPictures.php">Upload Pictures</a></li>                      
        <li class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == "Login.php" ? "active'" : "'" ?>>
        <?php echo $_SESSION["userId"] ? '<a class="nav-link" href="/src/logout.php">Logout</a>' : '<a class="nav-link" href="/src/Login.php">Login</a>' ?></li>
        <?php echo $_SESSION["userId"] ? "<li class='nav-item'><a class='nav-link' href='#'>User: {$_SESSION["userId"]}</a></li>" : "" ?>      
    </ul>
  </div>  
</nav>
