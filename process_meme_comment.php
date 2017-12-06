<?php
    // things we need
    session_start();
	require_once("connect_to_database.php");
	
	// check that submit was pressed
	if (isset($_POST["submit"])) {
	    
	    // grab relevant info
	    $memeid = (int)$_POST["memeid"];
	    $comment = trim($_POST["comment"]);
	    $authorid = (int)$_SESSION["id"];
	    
	    // get the author of this meme
        $statement = $mysqli->prepare("SELECT authorid FROM memes WHERE id = ?");
        if (!$statement) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        } 
        $statement->bind_param("i", $memeid);
        $statement->execute();
        $statement->bind_result($authorofmeme);
        $statement->fetch();
        $statement->close();
        
        // get the number of comments there are 
        $statement = $mysqli->prepare("SELECT comments FROM users WHERE id = ?");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("i", $authorofmeme);
        $statement->execute();
        $statement->bind_result($numcomments);
        $statement->fetch();
        $statement->close();
        
        // increment num comments by 1
        $numcomments = $numcomments + 1;
        $statement = $mysqli->prepare("UPDATE users SET comments = ? WHERE id = ?");
        if (!$statement) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        $statement->bind_param("ii", $numcomments, $authorofmeme);
        $statement->execute();
        $statement->fetch();
        $statement->close();
	    
	    // insert the actual comment
	    $statement = $mysqli->prepare("INSERT INTO meme_comments (authorid, memeid, comment) values (?, ?, ?)");
		if (!$statement) {
			printf("Query preparation failed: %s\n", $mysqli->error);
			exit;
		}
		$statement->bind_param("iis", $authorid, $memeid, $comment);
		$statement->execute();
		$statement->close();
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	} else {
	    header("Location: index.php");
	}

?>