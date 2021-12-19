<?php
//CODE TO INCLUDE ON THE FILES OF THE OTHER GROUP MEMBERS
session_start();
if ($_SESSION['sessionId'] == "") {
    $_SESSION['previousPage'] = "AddFriend" ;
    header("Location: Login.php");
    exit();
}
else{
    $_SESSION['activePage'] = "AddFriend";
}

$siteTitle = "My Friend";



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

    $friendId = htmlspecialchars($_POST["friendId"]);
    $validateError = ""; 
    $_SESSION['friendId'] = htmlspecialchars($_POST['friendId']);
    

    //validators
    if(isset($_POST['sendFriendRequest']))
    {
        
        //checking if ID exists in application         
        $sqlStatement = 'SELECT * FROM User WHERE UserId = :PlaceHolderUserID ';
        $pStmt = $myPdo->prepare($sqlStatement);       
        $pStmt ->execute([':PlaceHolderUserID' => $friendId]);      
        $chkAccount = $pStmt->fetch();        
        
        //user cannot send a request to someone who is already a friend
        //a) if user is a requester and invite was accepeted:
        $sqlStatement = 'SELECT * FROM friendship '
                . 'WHERE Friend_RequesterId = :resquesterId AND Friend_RequesteeId = :requesteeId AND Status = :status';
        $pStmt = $myPdo->prepare($sqlStatement);        
        $pStmt ->execute(array(':resquesterId' => $_SESSION['userId'] , ':requesteeId' => $_SESSION['friendId'], ':status' => 'accepted' ));      
        $requester = $pStmt->fetch(); 
        
        //b) if user is a requestee and invite was accepeted
        $sqlStatement = 'SELECT * FROM friendship '
                . 'WHERE Friend_RequesterId = :resquesterId AND Friend_RequesteeId = :requesteeId AND Status = :status';
        $pStmt = $myPdo->prepare($sqlStatement);        
        $pStmt ->execute(array(':resquesterId' => $_SESSION['friendId'] , ':requesteeId' => $_SESSION['userId'] , ':status' => 'accepted' ));      
        $requestee = $pStmt->fetch(); 
        
        //if user is a requestee and invite is pending:
        $sqlStatement = 'SELECT * FROM friendship '
                . 'WHERE Friend_RequesterId = :friend AND Friend_RequesteeId = :user AND Status = :status';
        $pStmt = $myPdo->prepare($sqlStatement);        
        $pStmt ->execute(array(':user' => $_SESSION['userId'] , ':friend' => $_SESSION['friendId'], ':status' => 'request' ));      
        $pending = $pStmt->fetch();  
        
        //if user is a requester and invite is pending:
        $sqlStatement = 'SELECT * FROM friendship '
                . 'WHERE Friend_RequesterId = :user AND Friend_RequesteeId = :friend AND Status = :status';
        $pStmt = $myPdo->prepare($sqlStatement);        
        $pStmt ->execute(array(':user' => $_SESSION['userId'] , ':friend' => $_SESSION['friendId'], ':status' => 'request' ));      
        $pendingFriend = $pStmt->fetch(); 
        
        //checking if this request was already sent
        if ($pendingFriend != null){
            $validateError = "You can't send this request twice! The invitation is still pending.";
        }       
        else {    
            //retrieving information on requestee
            $sqlStatement = "SELECT UserId, Name FROM user WHERE UserId = :requesteeId";
            $pStmt = $myPdo->prepare($sqlStatement);        
            $pStmt ->execute([':requesteeId' => $_SESSION['friendId']]);  
            $identity = $pStmt->fetch();
            
            //if user is not in social media yet
            if ($chkAccount == null){
                $validateError = "This user does not exist in this social media site yet!";
            }       
            //user cannot send a friend request to himself/herself
            else if ($_SESSION['userId'] == $_SESSION['friendId']) {
                $validateError = "You cannot send a friend request to yourself!";
            }
            //user cannot send a request to someone who is already a friend
            else if ($requester != null || $requestee != null){
                $validateError = "This user is already your friend!";
            }
            //If A sends a friend request to B, while A has a friend request from B 
            //waiting for A to accept, A and B become friends.
            else if ($pending != null)  {
                //update requestee status
                $sqlStatement = "UPDATE friendship SET status = 'accepted' "
                    . "WHERE Friend_RequesterId = :requesteeId AND Friend_RequesteeId = :requesterId "; 
                $pStmt = $myPdo->prepare($sqlStatement);        
                $pStmt ->execute(array(':requesterId' => $_SESSION['userId'] , ':requesteeId' => $_SESSION['friendId'] ));      
                $pStmt->commit;
                
                //update requester status            
                $sqlStatement = "INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) "
                        . "VALUES (:requesterId, :requesteeId, :status)";
                $pStmt = $myPdo->prepare($sqlStatement);        
                $pStmt ->execute(array(':requesterId' => $_SESSION['userId'] , ':requesteeId' => $_SESSION['friendId'], ':status' => 'accepted' ));      
                $pStmt->commit;    
                $validateError = "You and  ". $identity[1] . " (ID:" . $identity[0] . ") are now friends.";        
            }          
            //sending the invitation which will be pending, until accepted by new friend
            else { 
                //inserting friendship into table
                $sqlStatement = "INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) "
                        . "VALUES (:requesterId, :requesteeId, :status)";
                $pStmt = $myPdo->prepare($sqlStatement);        
                $pStmt ->execute(array(':requesterId' => $_SESSION['userId'] , ':requesteeId' => $_SESSION['friendId'], ':status' => 'request' ));      
                $pStmt->commit;     
                //confirmation message
                $validateError = "Your request was sent to ". $identity[1] . " (ID:" . $identity[0] . "). "
                        . "<br>" . "Once " . $identity[1] . " accepts your request, you and ". $identity[1] . " will be friends "
                        . "and will be able to see each others' shared albums.";
            }  
        }
    }        
    

include '../common/Header.php';
//CODE TO INCLUDE ON THE FILES OF THE OTHER GROUP MEMBERS
?>

<style>
<?php include '../common/css/site.css'; ?>
.form-control {width: unset; min-width: 90%; max-width: 100%;}
.form-fix {width: unset; min-width: 90%; max-width: 95%;}
</style>

<div class="container">
 <div  class="">
    <h1>Add Friend</h1>    
    <br><p>Welcome <b><?php print $_SESSION['name'];?></b>! (Not you? Change user <a href="Logout.php">here</a>).</p>
    <p>Enter the ID of the user you want to be friends with.</p>
    
    <form method='post' class='form-fix' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
        <table class="tableSpacing">           

                        <tr>
                            <td  class="font-weight-bold"><label for='friendId'>ID:</label></td>
                            <td style="padding-left: 30px;"><input type='text' class='form-control' id='friendId' name='friendId' value='<?php print $_SESSION['friendId']; ?>'></td>
                            <td style="padding-left: 30px;"><button type='submit' name='sendFriendRequest' class='btn btn-primary'>
                            <!--<td style="padding-left: 30px;"><button type='submit' name='sendFriendRequest' class='btn btn-primary' onclick='loading()'><i class="fa fa-spinner fa-spin" style="display: none"></i>-->
                            <span class="btn-text">Send Friend Request</span></button></td>
                        </tr>
                                              
        </table>
        <div style='color:red'><?php print $validateError;?></div>
                                  
       
    </form>
    <br>
</div>    
</div>
<!--code to include in the files of other group members -->
<script src="../common/js/validate.js"></script>
<?php include '../common/Footer.php'; ?>
<!--code to include in the files of other group members -->