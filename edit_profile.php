<?php
    session_start();
    require_once("connect_to_database.php");
    
    // also need to change filepath of profile picture if profile is edited
    if (isset($_POST["submit"])) {
        
        if (!isset($_POST["first_name"]) || trim($_POST["first_name"]) == "" ||
        !isset($_POST["last_name"]) || trim($_POST["last_name"]) == "" ||
        !isset($_POST["username"]) || trim($_POST["username"]) == "") {
            header("user_profile.php");
        } else {
            
            $old_username = $_SESSION["username"];
            $new_first_name = $_POST["first_name"];
            $new_last_name = $_POST["last_name"];
            $new_username = $_POST["username"];
            
            // check these first 
            if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
				header("Location: user_profile.php");
			}
			if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
				header("Location: user_profile.php");
			}
			if (!preg_match('/^[\w_\-]+$/', $username)) {
    			header("Location: user_profile.php");
    		}
            
            // if the user doesn't specify a new password...
            if (!isset($_POST["new_password"]) || trim($_POST["new_password"]) == "") {
                
                // only submit three fields
                $stmt = $mysqli->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ? WHERE id = ?");
                if (!$stmt) {
    			    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
    			} 
    			$stmt->bind_param("sssi", $new_first_name, $new_last_name, $new_username, $_SESSION["id"]);
    			$stmt->execute();
			    $stmt->close();
			    
                $_SESSION["username"] = $new_username;
    		    $old_folder_path = "User_Content/" . $old_username . "/";
                $new_folder_path = "User_Content/" . $_SESSION["username"] . "/";
    			rename($old_folder_path, $new_folder_path);

    			$stmt = $mysqli->prepare("SELECT * FROM memes WHERE authorid = ?");
    			if (!$stmt) {
    			    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
    			}
    			$stmt->bind_param("i", $_SESSION["id"]);
    			$stmt->execute();
    			$user_memes = $stmt->get_result();
    			$stmt->close();
    		    
    		    while($user_meme = $user_memes->fetch_assoc()) {
    		        
    		        $file_parts = explode("/", $user_meme["filepath"]);
    		        $new_file_path = $new_folder_path . $file_parts[2];
    		        $stmt = $mysqli->prepare("UPDATE memes SET licensedto = ?, filepath = ? WHERE authorid = ?");
        			if (!$stmt) {
        			    printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
        			}
        			$stmt->bind_param("ssi", $_SESSION["username"], $new_file_path, $_SESSION["id"]);
        			$stmt->execute();
        			$stmt->close();
    		    }
    		    
    		    // change directory of profile picture
    		    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    		    if (!$stmt) {
    		        printf("Query preparation failed: %s\n", $mysqli->error);
    		        exit;
    		    }
    		    $stmt->bind_param("i", $_SESSION["id"]);
    		    $stmt->execute();
    		    $old_propic_path = $stmt->get_result();
    		    $stmt->close();
    		    
    		    while ($path = $old_propic_path->fetch_assoc()) {
    		        $propic_parts = explode("/", $path["propic"]);
        		    $new_propic_path = $new_folder_path . $propic_parts[2];
        		    $stmt = $mysqli->prepare("UPDATE users SET propic = ? WHERE id = ?");
        		    if (!$stmt) {
        		        printf("Query preparation failed: %s\n", $mysqli->error);
        		        exit;
        		    }
        		    $stmt->bind_param("si", $new_propic_path, $_SESSION["id"]);
        		    $stmt->execute();
        		    $stmt->close();
    		    }
    		    header("Location: user_profile.php");
            } else {
                
                // only check the password if a new one has been specified
                $new_pwd = $_POST["new_password"];
                
    			if (strlen($new_pwd) < 8 || strlen($new_pwd) > 20) {
    				header("Location: user_profile.php");
    			} 
    			
    			$hashed_password = password_hash($new_pwd, PASSWORD_DEFAULT);
    			$stmt = $mysqli->prepare("SELECT first_name FROM users WHERE username=?");
    			if (!$stmt) {
    					printf("Query preparation failed: %s\n", $mysqli->error);
    					exit;
    			}
    			$stmt->bind_param("s", $new_username);
    			$stmt->execute();
    			$stmt->store_result();
    			
    			// if there's no match to the new username
    			if ($stmt->num_rows == 0) {
    			    $stmt->close();
    				$stmt = $mysqli->prepare("UPDATE users SET first_name = '$new_first_name', last_name = '$new_last_name', username = '$new_username', password = '$hashed_password' where id = ?");
        			if (!$stmt) {
        			    printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
        			}
        			$stmt->bind_param("i", $_SESSION["id"]);
        			$stmt->execute();
    			    $stmt->close();
    			    
    			    // so in here, we need to change all the file pathing for the user's memes
                    $_SESSION["username"] = $new_username;
        		    $old_folder_path = "User_Content/" . $old_username . "/";
                    $new_folder_path = "User_Content/" . $_SESSION["username"] . "/";
        			rename($old_folder_path, $new_folder_path);
        			
        			$stmt = $mysqli->prepare("SELECT * FROM memes WHERE authorid = ?");
        			if (!$stmt) {
        			    printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
        			}
        			$stmt->bind_param("i", $_SESSION["id"]);
        			$stmt->execute();
        			$user_memes = $stmt->get_result();
        			$stmt->close();
        		    
        		    while($user_meme = $user_memes->fetch_assoc()) {
        		        
        		        $file_parts = explode("/", $user_meme["filepath"]);
    		            $new_file_path = $new_folder_path . $file_parts[2];
        		        $stmt = $mysqli->prepare("UPDATE memes SET licensedto = ?, filepath = ? WHERE authorid = ?");
            			if (!$stmt) {
            			    printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
            			}
            			$stmt->bind_param("ssi", $_SESSION["username"], $new_file_path, $_SESSION["id"]);
            			$stmt->execute();
            			$stmt->close();
        		    }
        		    // change directory of profile picture
        		    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
        		    if (!$stmt) {
        		        printf("Query preparation failed: %s\n", $mysqli->error);
        		        exit;
        		    }
        		    $stmt->bind_param("i", $_SESSION["id"]);
        		    $stmt->execute();
        		    $old_propic_path = $stmt->get_result();
        		    $stmt->close();
        		    
        		    while ($path = $old_propic_path->fetch_assoc()) {
        		        $propic_parts = explode("/", $path["propic"]);
            		    $new_propic_path = $new_folder_path . $propic_parts[2];
            		    $stmt = $mysqli->prepare("UPDATE users SET propic = '$new_propic_path' WHERE id = ?");
            		    if (!$stmt) {
            		        printf("Query preparation failed: %s\n", $mysqli->error);
            		        exit;
            		    }
            		    $stmt->bind_param("i", $_SESSION["id"]);
            		    $stmt->execute();
            		    $stmt->close();
        		    }
            		header("Location: user_profile.php");
    			} else {
    				header("Location: user_profile.php");
    			}
            }
        }
    } else {
        header("Location: user_profile.php");
    }
    
?>