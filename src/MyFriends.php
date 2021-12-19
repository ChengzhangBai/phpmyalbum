<?php

//CODE TO INCLUDE ON THE FILES OF THE OTHER GROUP MEMBERS
session_start();
if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "MyFriends" ;
    header("Location: Login.php");
    exit();
}
else{
    $_SESSION['activePage'] = "MyFriends";
}

$siteTitle = "My Friends";

//CODE TO INCLUDE ON THE FILES OF THE OTHER GROUP MEMBERS


    $dbConnection = parse_ini_file("../common/db_connection.ini");        	
    extract($dbConnection);
    $myPdo = new PDO($dsn, $user, $password);
    
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

    $_SESSION['name'] = getUserName($_SESSION['userId'], $myPdo);



//START HERE
$validatorError = "";
    
    
    //Checking friends per user
    //getting a list of userId's where friendshipstatus = accepted
    $sql = "SELECT friendship.Friend_RequesterId, friendship.Friend_RequesteeId FROM friendship "
            . "WHERE (Friend_RequesterId = :userId OR Friend_RequesteeId = :userId) AND Status = 'accepted' ";
    $pStmt = $myPdo->prepare($sql);
    $pStmt->execute ( [':userId' => $_SESSION['userId'] ]);
    $friendsByUser = $pStmt->fetchAll();
    
    //sending userId's to $friendIdArray
    $friendIdArray = array();
    foreach ($friendsByUser as $row){
        if ($row[0] != $_SESSION['userId'] && (!in_array($row[0], $friendIdArray))){
            array_push($friendIdArray, $row[0]);
        }
        if ($row[1] != $_SESSION['userId'] && (!in_array($row[1], $friendIdArray))){
            array_push($friendIdArray, $row[1]);
        }
    }
    
    //Defriend button:    
    if(isset($_POST['defriendBtn'])){
        if (isset($_POST['defriend'])){
            foreach ($_POST['defriend'] as $row) //iterate and look for what was selected
            {
                //for each selected line, delete the corresponding friend from friends' list
                $sql = "DELETE FROM friendship "
                        . "WHERE (friendship.Friend_RequesterId = :userId AND friendship.Friend_RequesteeId = :friendId) "
                        . "OR (friendship.Friend_RequesterId = :friendId AND friendship.Friend_RequesteeId = :userId)"; 
                $pStmt = $myPdo->prepare($sql);
                $pStmt->execute(array(':userId' => $_SESSION['userId'], ':friendId' => $row)); 
                $pStmt->commit;                 
            }
            header('Location: MyFriends.php'); //redirect to update table view
            exit;             
        }
        else 
        {
            $validatorError = "You must select at least one checkbox!"; //at least one checkbox must be selected
        }          
    }
    
    //Accept Selected Button
    if (isset($_POST['acceptBtn'])){
        if (isset($_POST['acceptDeny'])){
            foreach ($_POST['acceptDeny'] as $row){
            //update requestee status to accepted
            $sqlStatement = "UPDATE friendship SET status = 'accepted' "
                . "WHERE Friend_RequesterId = :requesteeId AND Friend_RequesteeId = :requesterId "; 
            $pStmt = $myPdo->prepare($sqlStatement);        
            $pStmt ->execute(array(':requesterId' => $_SESSION['userId'] , ':requesteeId' => $row ));      
            $pStmt->commit;
            
            //insert accepted status for main user         
            $sqlStatement = "INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) "
                    . "VALUES (:requesterId, :requesteeId, :status)";
            $pStmt = $myPdo->prepare($sqlStatement);        
            $pStmt ->execute(array(':requesterId' => $_SESSION['userId'] , ':requesteeId' => $row, ':status' => 'accepted' ));      
            $pStmt->commit;                                
            }
            header('Location: MyFriends.php'); //redirect to update table view
            exit; 
        }
        else 
        {
            $validatorError = "You must select at least one checkbox!"; //at least one checkbox must be selected
        }   
    }
    
    //Deny Selected Button
    if (isset($_POST['denyBtn'])){
        if (isset($_POST['acceptDeny'])){
            foreach ($_POST['acceptDeny'] as $row){
                //delete request(pending) statement from database
                $sqlStatement = "DELETE FROM friendship "
                        . "WHERE friendship.Friend_RequesterId = :requesterId "
                        . "AND friendship.Friend_RequesteeId = :requesteeId ";
                $pStmt = $myPdo->prepare($sqlStatement);        
                $pStmt ->execute(array(':requesteeId' => $_SESSION['userId'] , ':requesterId' => $row ));      
                $pStmt->commit;  
            }
            header('Location: MyFriends.php'); //redirect to update table view
            exit;            
        }
        else 
        {
            $validatorError = "You must select at least one checkbox!"; //at least one checkbox must be selected
        }   
    }

include '../common/Header.php';
?>

<style>
<?php include '../common/css/site.css'; ?>
</style>

<div class="container">

    <h1>My Friends</h1>
    <br>
    <p>Welcome <b><?php print $_SESSION['name'];?></b>! (Not you? Change your session <a href="Logout.php">here</a>)</p>
    <br>
    <form method='post' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
        <!--First table: FRIENDS-->
        <table class="table">
        <!-- display table header -->
        <thead>
            <tr>
                <th>Friends:</th>
                <th></th>
                <th><a href="AddFriend.php">Add Friends</a></th>                                                                             
            </tr>
            <tr>
                <th>Name</th>
                <th>Shared Albums</th>
                <th>Defriend</th>                                                                             
            </tr>
        </thead>   

        <!-- display table body -->             
        <div style='color:red'> <?php print $validatorError;?></div><br>
        <tbody>
        <?php   
        foreach ($friendIdArray as $row){
            $sql="SELECT user.UserId, user.Name, album.Accessibility_Code, album.Album_Id "
                    . "FROM user LEFT JOIN album ON album.Owner_Id = user.UserId "
                    . "WHERE user.UserId = :userId "
                    . "ORDER BY user.UserId ";
            $pStmt = $myPdo->prepare($sql);
            $pStmt->execute ([ ':userId' => $row ]);
            $sharedAlbums = $pStmt->fetchAll(); 
            $albumCount = 0;
            $albumWithPicCount = 0;
            foreach ($sharedAlbums as $albums)
            {
                if ($albums[2] == "shared")
                {
                    $albumCount = $albumCount + 1;   
                    $sql = "SELECT count(*) as num FROM picture WHERE album_Id=:albumId";
                    $pAlbum = $myPdo->prepare($sql);
                    $pAlbum->execute(["albumId"=>$albums["Album_Id"]]);
                    $row = $pAlbum->fetch(PDO::FETCH_ASSOC);
                    if($row["num"]>0){
                        $albumWithPicCount =  $albumWithPicCount + 1;
                    }   
                }
            }    

                echo "<tr>";
                echo "<td><a href='FriendPictures.php?fi"
                . "d=".$albums[0]."'>".$albums[1]."</a></td>"; // Name

                echo "<td>".$albumWithPicCount."/".$albumCount."</td>"; // Shared albums
                echo "<td><input type='checkbox' name='defriend[]' value='$albums[0]'/></td>"; // Defriend            
                echo "</tr>";           
        }
        ?>              

    </tbody>
    </table>

    <!--Defriend button:-->
    <div class='form-group row'>               
        <label for='' class='col-lg-8 col-form-label'><b></b> </label>            
        <div class='col-lg-3'>                    
            <button type='submit' name='defriendBtn' class='btn btn-primary col-lg-4 col-sm-3 col-xs-2' onclick='return confirm("The selected friend will be defriended!");'><span class="btn-text">Defriend</span></button>  <!--Selected-->
        </div> 
    </div>     

    <!--Second table: REQUESTS -->
        <br><br><table class="table">
        <!-- display table header -->
        <thead>
            <tr>
                <th scope="col">Friend Requests:</th>
                <th scope="col"></th>                                                                             
            </tr>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Accept or Deny</th>                                                                             
            </tr>
        </thead>               
        <!--example for table body - MUST BE TWEAKED TO BRING VALUES FROM DATABASE -->             
        <tbody>
        <?php
        //getting a list of userId's where friendshipstatus = requested
        $sql = "SELECT user.UserId, user.Name FROM user "
                . "INNER JOIN friendship ON friendship.Friend_RequesterId = user.UserId "
                . "WHERE friendship.Status = 'request' AND friendship.Friend_RequesteeId = :userId ";        
        $pStmt = $myPdo->prepare($sql);
        $pStmt->execute ( [':userId' => $_SESSION['userId'] ]);
        $requestFriend = $pStmt->fetchAll();
        foreach ($requestFriend as $friendName)
        {
            echo "<tr>";
            echo "<td>".$friendName[1]."</td>"; // Name
            echo "<td><input type='checkbox' name='acceptDeny[]' value='$friendName[0]' /></td>"; // Accept or deny            
            echo "</tr>";
        }            
        ?>   
        </tbody>
    </table>    

    <!--Accept/Deny buttons-->    

    <div class='form-group row'>               
        <label for='' class='col-lg-8 col-form-label'><b></b> </label>            
        <div class='col-lg-4'>                    
            <button type='submit' name='acceptBtn' class='btn btn-primary col-lg-3 col-sm-2 col-xs-2' style="margin-right:5px;"><span class="btn-text">Accept</span></button>  <!--  Selected -->
            <button type='submit' name='denyBtn' class='btn btn-primary col-lg-3 col-sm-2 col-xs-2' style="margin-left:5px;" onclick='return confirm("The selected request will be denied!");'><span class="btn-text">Deny</span></button> <!--  Selected -->
        </div> 
    </div>     
    </form> 
    <br>
</div>



<!--END HERE -->

<!--code to include in the files of other group members -->
<script src="../common/js/validate.js"></script>
<?php include('../common/footer.php'); ?>
<!--code to include in the files of other group members -->