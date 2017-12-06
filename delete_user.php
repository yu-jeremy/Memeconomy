<?php 

    session_start();
    require_once("connect_to_database.php");
    
    if (isset($_POST["submit"])) {
        
        $id = $_SESSION["id"];
        
        // delete 
        $stmt = $mysqli->prepare("DELETE FROM votes WHERE authorid = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $mysqli->prepare("DELETE FROM meme_comments WHERE authorid = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // we're actually gonna base this off of licensedto
        
        $stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($author_username);
        $stmt->fetch();
        $stmt->close();
        
        $stmt = $mysqli->prepare("DELETE FROM memes WHERE licensedto = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param("s", $author_username);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $user_folder = "User_Content/" . $_SESSION["username"];
        array_map('unlink', glob($user_folder . "/*"));
        
        unset($_SESSION["id"]);
        unset($_SESSION["username"]);
        
        if (rmdir($user_folder)) {
            echo "Successfully deleted user";
            header("Location: index.php");
        } else {
            echo "Unable to delete user";
            header("Location: user_profile.php");
        }
    } else {
        echo "Delete button not pressed";
        header("Location: index.php");
    }
?>