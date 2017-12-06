<?php
    // things we need
    session_start();
	require_once("connect_to_database.php");
	// check that submit is pressed
    if (isset($_POST["submit"])) {
        
        if ($_SESSION["token"] !== $_POST["token"]) {
            die("Request forgery detected");
        } else {
            // get the id of the meme we want to delete
            $memeid = (int)$_POST["memeid"];
            
            // get number of upvotes this meme got
            $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE value = 2 AND memeid = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->bind_result($numupvotes);
            $statement->fetch();
            $statement->close();
            
            // get number of downvotes this meme got
            $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE value = 1 AND memeid = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->bind_result($numdownvotes);
            $statement->fetch();
            $statement->close();
            
            // get number of comments this meme got
            $statement = $mysqli->prepare("SELECT COUNT(*) FROM meme_comments WHERE memeid = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->bind_result($numcomments);
            $statement->fetch();
            $statement->close();
            
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
            
            // get the number of upvotes, downvotes, and comments the user currenly has
            $statement = $mysqli->prepare("SELECT upvotes, downvotes, comments FROM users WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $authorofmeme);
            $statement->execute();
            $statement->bind_result($userupvotes, $userdownvotes, $usercomments);
            $statement->fetch();
            $statement->close();
            
            // update user's upvotes 
            $userupvotes = $userupvotes - $numupvotes;
            $userdownvotes = $userdownvotes - $numdownvotes;
            $usercomments = $usercomments - $numcomments;
            
            // change the author of the meme's info to reflect change in 
            // upvotes, downvotes, and comment numbers
            $statement = $mysqli->prepare("UPDATE users SET upvotes = ?, downvotes = ?, comments = ? WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("iiii", $userupvotes, $userdownvotes, $usercomments, $authorofmeme);
            $statement->execute();
            $statement->close();
            
            // delete meme votes
            $statement = $mysqli->prepare("DELETE FROM votes WHERE memeid = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->close();
            
            // delete meme comments
            $statement = $mysqli->prepare("DELETE FROM meme_comments WHERE memeid = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->close();
            
            // delete meme itself
            $statement = $mysqli->prepare("DELETE FROM memes WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->close();
            header("Location: your_memes.php");
        }
    } else {
        header("Location: index.php");
    }
?>