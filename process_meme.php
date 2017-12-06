<?php
    // things we need
    session_start();
    require_once("connect_to_database.php");
    
    // check that submit is pressed
    if (isset($_POST["submit"])) {
        if (!isset($_POST["description"]) || 
        trim($_POST["description"] == "") || 
        !isset($_POST["title"]) || 
        trim($_POST["title"] == "")) { // we don't check for price here yet
            header("Location: index.php");
        } else {
            if ($_SESSION["token"] !== $_POST["token"]) {
                die("Request forgery detected");
            } else {
                // grab relevant information
                $userid = (int)$_SESSION["id"];
                $description = trim($_POST["description"]);
                $licensure = trim($_SESSION["username"]);
                $title = trim($_POST["title"]);
                $price = $_POST["price"];
                $forsale = isset($_POST["market_status"]);
                $keyword = $_POST["keyword"];
                $name = trim($_FILES["meme_file"]["name"]);
                
                // validating file
                if (!isset($_FILES['meme_file']['error']) || is_array($_FILES['meme_file']['error'])) {
                    echo "Invalid parameters";
                    header("Location: index.php");
                }
                
                // validating file size
                if ($_FILES['meme_file']['size'] > 20000000) {
                    echo "File size too large";
                    header("Location: index.php");
                }
                
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                    $finfo->file($_FILES['meme_file']['tmp_name']),
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
                
                // get the current number of credits the user has
                $stmt = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
                if (!$stmt) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param("i", $_SESSION["id"]);
                $stmt->execute();
                $stmt->bind_result($current_crypto);
                $stmt->fetch();
                $stmt->close();
                
                $updated_crypto = (int)($current_crypto + 5);
                
                // insert the new value with 5 more added for adding a meme
                $stmt = $mysqli->prepare("UPDATE users SET credits = ? WHERE id = ?");
                if (!$stmt) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param("ii", $updated_crypto, $_SESSION["id"]);
                $stmt->execute();
                $stmt->close();
                
                $target = "User_Content/";
        		$target_dir = $target . $_SESSION["username"] . "/" . basename($name);
        		$tmp_name = $_FILES["meme_file"]["tmp_name"];
                
                // create the entry in memes table
                $stmt = $mysqli->prepare("INSERT INTO memes (title, description, authorid, licensedto, price, filepath, forsale, keywords) values (?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    printf("Query preparation failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param("ssisisis", $title, $description, $userid, $licensure, $price, $target_dir, $forsale, $keyword);
                $stmt->execute();
                $stmt->close();
                
                // check if the file was moved
                if(move_uploaded_file($tmp_name, $target_dir)) {
                	header("Location: your_memes.php");
                } else {
                	header("Location: index.php");
                }  
                
            }
        }
    } else {
        echo "Form not submitted";
        header("Location: index.php");
    }
?>