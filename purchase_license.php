<?php

    session_start();
    require_once("connect_to_database.php");
    
    if (isset($_POST["submit"])) {
        
        // we get the credits from the user who is purchasing
        $stmt = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
			exit;
        }
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $stmt->bind_result($current_credits);
        $stmt->fetch();
        $stmt->close();
        
        // then we get the price of the meme being purchased
        $stmt = $mysqli->prepare("SELECT authorid, price FROM memes WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
			exit;
        }
        
        $stmt->bind_param("i", $_POST["meme_id"]);
        $stmt->execute();
        $stmt->bind_result($authorid, $meme_price);
        $stmt->fetch();
        $stmt->close();
        
        // if the purchaser has enough money
        if ($current_credits < $meme_price) { 
            echo "You don't have enough crypto to purchase this meme.";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else { 
            
            $new_buyer_credits = $current_credits - $meme_price;
            
            // get all the info for this meme, excluding datemade, upvotes, downvotes
            $stmt = $mysqli->prepare("SELECT title, description, authorid, licensedto, filepath, keywords FROM memes WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
			    exit;
            }
            $stmt->bind_param("i", $_POST["meme_id"]);
            $stmt->execute();
            $stmt->bind_result($title, $description, $authorid, $licensedto, $filepath, $keywords);
            $stmt->fetch();
            $stmt->close();
            
            
            $new_directory = "User_Content/" . $_SESSION["username"] . "/"; 
            $file_parts = explode("/", $filepath);
    		$new_file_path = $new_directory . $file_parts[2];
    	
            $stmt = $mysqli->prepare("INSERT INTO memes (title, description, authorid, licensedto, filepath, keywords) values (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
			    exit;
            }
        
    		// licensedto here is the original author
    		$stmt->bind_param("ssisss", $title, $description, $_SESSION["id"], $licensedto, $new_file_path, $keywords);
            $stmt->execute();
            $stmt->close();
            
            /// update purchaser's currency
            $stmt = $mysqli->prepare("UPDATE users SET credits = ? WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param("ii", $new_buyer_credits, $_SESSION["id"]);
            $stmt->execute();
            $stmt->close();
            
            $stmt = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
    			exit;
            }
            $stmt->bind_param("i", $authorid);
            $stmt->execute();
            $stmt->bind_result($current_seller_creds);
            $stmt->fetch();
            $stmt->close();
            
            $new_seller_credits = $current_seller_creds + $meme_price;
            
            /// update seller's currency
            $stmt = $mysqli->prepare("UPDATE users SET credits = ? WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param("ii", $new_seller_credits, $authorid);
            $stmt->execute();
            $stmt->close();
            
    		// make a copy into the purchaser's folder
            if(copy($filepath, $new_file_path)) {
                echo "Success";
                header("Location: your_memes.php");
            } else {
                echo "Purchasing this meme failed.";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
        }
    } else {
        echo "Form not submitted";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }