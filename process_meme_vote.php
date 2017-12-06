<?php
    // things we need
    session_start();
	require_once("connect_to_database.php");
	
	// check if upvote was pressed
	// as opposed to downvote, which is below this if statement
	if (isset($_POST["upvote"])) {
	    
	    // collect relevant information
        $memeid = (int)$_POST["memeid"];
        $authorid = (int)$_SESSION["id"];
        // vote value for upvote is 2
        $vote_value = 2;
        
        // get the existing vote that the author has on this particular meme
        $statement = $mysqli->prepare("SELECT COUNT(*), value FROM votes WHERE (memeid = ? AND authorid = ?)");
        if (!$statement) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        $statement->bind_param("ii", $memeid, $authorid);
        $statement->execute();
        $statement->bind_result($existingvote, $valueofvote);
        $statement->fetch();
        $statement->close();
        
        // get the author of the meme
        $statement = $mysqli->prepare("SELECT authorid FROM memes WHERE id = ?");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("i", $memeid);
        $statement->execute();
        $statement->bind_result($authorofmemeid);
        $statement->fetch();
        $statement->close();
        
        // get the number of upvotes and downvotes that the author of the meme has
        $statement = $mysqli->prepare("SELECT credits, upvotes, downvotes FROM users WHERE id = ?");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("i", $authorofmemeid);
        $statement->execute();
        $statement->bind_result($authorcredits, $authorofmemeupvotes, $authorofmemedownvotes);
        $statement->fetch();
        $statement->close();

        if ($existingvote > 0) { // there already exists a vote
            if ($valueofvote == 0) { // it has value 0
                // the person is voting, so set the value to 2
                $statement = $mysqli->prepare("UPDATE votes SET value = 2 WHERE authorid = ? and memeid = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("ii", $authorid, $memeid);
                $statement->execute();
                $statement->fetch();
                $statement->close();
                
                // increment upvote count for the author of the meme AND update credit for author of the meme
                $updated_credits = $authorcredits + 2;
                $authorofmemeupvotes = $authorofmemeupvotes + 1;
                $statement = $mysqli->prepare("UPDATE users SET upvotes = ?, credits = ? WHERE id = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("iii", $authorofmemeupvotes, $updated_credits, $authorofmemeid);
                $statement->execute();
                $statement->fetch();
                $statement->close();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                // the person is rescinding their vote
                $statement = $mysqli->prepare("UPDATE votes SET value = 0 WHERE authorid = ? and memeid = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("ii", $authorid, $memeid);
                $statement->execute();
                $statement->fetch();
                // decrement the meme author's upvote count by 1
                $authorofmemeupvotes = $authorofmemeupvotes - 1;
                $updated_credits = $authorcredits - 2;
                $statement = $mysqli->prepare("UPDATE users SET upvotes = ?, credits = ? WHERE id = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("iii", $authorofmemeupvotes, $updated_credits, $authorofmemeid);
                $statement->execute();
                $statement->fetch();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
        } else {
            // insert into vote an upvote
            $statement = $mysqli->prepare("INSERT INTO votes (authorid, memeid, value) VALUES (?, ?, ?)");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("iii", $authorid, $memeid, $vote_value);
            $statement->execute();
            $statement->fetch();
            // increment meme author upvote count by 1
            $authorofmemeupvotes = $authorofmemeupvotes + 1;
            $updated_credits = $authorcredits + 2;
            $statement = $mysqli->prepare("UPDATE users SET upvotes = ?, credits = ? WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("iii", $authorofmemeupvotes, $updated_credits, $authorofmemeid);
            $statement->execute();
            $statement->fetch();
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
    
    // if not upvote, check if downvote
    if (isset($_POST["downvote"])) {
        // collect relevant info
        $memeid = (int)$_POST["memeid"];
        $authorid = (int)$_SESSION["id"];
        $vote_value = 1;
        
        // everything in this section is identical to the section above
        // except that upvotes become downvotes
        
        
        $statement = $mysqli->prepare("SELECT COUNT(*), value FROM votes WHERE (memeid = ? AND authorid = ?)");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("ii", $memeid, $authorid);
        $statement->execute();
        $statement->bind_result($existingvote, $valueofvote);
        $statement->fetch();
        $statement->close();
        
        $statement = $mysqli->prepare("SELECT authorid FROM memes WHERE id = ?");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("i", $memeid);
        $statement->execute();
        $statement->bind_result($authorofmemeid);
        $statement->fetch();
        $statement->close();
        
        $statement = $mysqli->prepare("SELECT credits, upvotes, downvotes FROM users WHERE id = ?");
        if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
        }
        $statement->bind_param("i", $authorofmemeid);
        $statement->execute();
        $statement->bind_result($authorcredits, $authorofmemeupvotes, $authorofmemedownvotes);
        $statement->fetch();
        $statement->close();
        
        if ($existingvote > 0) { // there already exists a vote
            if ($valueofvote == 0) { // it has value 0
                $statement = $mysqli->prepare("UPDATE votes SET value = 1 WHERE authorid = ? and memeid = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("ii", $authorid, $memeid);
                $statement->execute();
                $statement->fetch();
                $authorofmemedownvotes = $authorofmemedownvotes + 1;
                $updated_credits = $authorcredits - 2;
                $statement = $mysqli->prepare("UPDATE users SET downvotes = ?, credits = ? WHERE id = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("iii", $authorofmemedownvotes, $updated_credits, $authorofmemeid);
                $statement->execute();
                $statement->fetch();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                $statement = $mysqli->prepare("UPDATE votes SET value = 0 WHERE authorid = ? and memeid = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("ii", $authorid, $memeid);
                $statement->execute();
                $statement->fetch();
                $authorofmemedownvotes = $authorofmemedownvotes - 1;
                $updated_credits = $authorcredits + 2;
                $statement = $mysqli->prepare("UPDATE users SET downvotes = ?, credits = ? WHERE id = ?");
                if (!$statement) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $statement->bind_param("iii", $authorofmemedownvotes, $updated_credits, $authorofmemeid);
                $statement->execute();
                $statement->fetch();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
        } else {
            // insert into vote an downvote
            $statement = $mysqli->prepare("INSERT INTO votes (authorid, memeid, value) VALUES (?, ?, ?)");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("iii", $authorid, $memeid, $vote_value);
            $statement->execute();
            $statement->fetch();
            $authorofmemedownvotes = $authorofmemedownvotes + 1;
            $updated_credits = $authorcredits - 2;
            $statement = $mysqli->prepare("UPDATE users SET downvotes = ?, credits = ? WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("iii", $authorofmemedownvotes, $updated_credits, $authorofmemeid);
            $statement->execute();
            $statement->fetch();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }


?>