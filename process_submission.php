<?php

    session_start();
    require_once("connect_to_database.php");
    
    if (isset($_POST["submit"])) {
        
        if (!isset($_POST["description"]) || trim($_POST["description"]) == "") {
            echo "Failed to submit submission";
            header("Location: index.php");
        } else {
            if ($_SESSION["token"] !== $_POST["token"]) {
                die("Request forgery detected");
            } else {
                
                $eventid = $_POST["eventid"];
                $authorid = $_SESSION["id"];
                $description = $_POST["description"];
                $filepath = trim($_FILES["submission_file"]["name"]);
                
                // validating file
                if (!isset($_FILES['submission_file']['error']) || is_array($_FILES['submission_file']['error'])) {
                    echo "Invalid parameters";
                    header("Location: index.php");
                }
                
                // validating file size
                if ($_FILES['submission_file']['size'] > 20000000) {
                    echo "File size too large";
                    header("Location: index.php");
                }
                
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                    $finfo->file($_FILES['submission_file']['tmp_name']),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'jpeg' => 'image/jpeg',
                    ),
                    true
                )) {
                    echo "Invalid file type";
                    header("Location: index.php");
                }
                
                $target = "User_Content/admin/";
                
                $stmt = $mysqli->prepare("SELECT eventname FROM events WHERE id = ?");
                if (!$stmt) {
                    printf("Query preparation failed");
                    exit;
                }
                $stmt->bind_param("i", $eventid);
                $stmt->execute();
                $stmt->bind_result($eventname);
                $stmt->fetch();
                $stmt->close();
                
        		$target_dir = $target . $eventname . "/" . basename($filepath);
        		$tmp_name = $_FILES["submission_file"]["tmp_name"];
        		
                // create the entry in memes table
                $stmt = $mysqli->prepare("INSERT INTO event_submissions (authorid, eventid, description, filepath) values (?, ?, ?, ?)");
                if (!$stmt) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param("iiss", $authorid, $eventid, $description, $target_dir);
                $stmt->execute();
                $stmt->close();
                
                // check if the file was moved
                if(move_uploaded_file($tmp_name, $target_dir)) {
                	header("Location: index.php");
                } else {
                	header("Location: user_profile.php");
                }  
            }
        }
    } else {
        header("Location: your_memes.php");
    }
    
?>