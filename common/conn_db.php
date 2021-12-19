<?php $dbConnection = parse_ini_file("db_connection.ini");
        	extract($dbConnection);
        	$myPdo = new PDO($dsn, $user, $password);
                
?> 