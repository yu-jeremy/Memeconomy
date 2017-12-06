<?php 

    session_start();
    require_once("connect_to_database.php");
    
    
    if (isset($_POST["submit"])) {
        
        $stmt = $mysqli->prepare("SELECT propic FROM users WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $stmt->bind_result($old_propic_filepath);
        $stmt->fetch();
        $stmt->close();
        
        unlink($old_propic_filepath);
        
        $new_propic_filepath = $_FILES["propic_file"]["name"];
        $target = "User_Content/";
		$target_dir = $target . $_SESSION["username"] . "/" . basename($new_propic_filepath);
		$tmp_name = $_FILES["propic_file"]["tmp_name"];
		
		if (!isset($_FILES['propic_file']['error']) || is_array($_FILES['propic_file']['error'])) {
            echo "Invalid parameters";
            header("Location: index.php");
        }
        
        if ($_FILES['propic_file']['size'] > 20000000) {
            echo "File size too large";
            header("Location: index.php");
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES['propic_file']['tmp_name']),
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
    		
        $stmt = $mysqli->prepare("UPDATE users SET propic = ? WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param("si", $target_dir, $_SESSION["id"]);
        $stmt->execute();
        $stmt->close();
        
        if(move_uploaded_file($tmp_name, $target_dir)) {
        	header("Location: user_profile.php");
        } else {
        	header("Location: index.php");
        }  
    } else {
        echo "Image not submitted";
        header("Location: index.php");
    }
    
?>