<?php

    session_start();
    require_once("connect_to_database.php");
    
    if (isset($_POST["submit"])) {
        
        if (!isset($_POST["amount"]) || trim($_POST["amount"]) == "") {
            echo "Amount not specified";
            header("Location: user_profile.php");
        } else {
            
            $stmt = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            
            $stmt->bind_param("i", $_SESSION["id"]);
            $stmt->execute();
            $stmt->bind_result($current_creds);
            $stmt->fetch();
            $stmt->close();
            
            $to_be_purchased = (int)$_POST["amount"];
            $sum = $current_creds + $to_be_purchased;
            
            $stmt = $mysqli->prepare("UPDATE users SET credits = ? WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            
            $stmt->bind_param("ii", $sum, $_SESSION["id"]);
            $stmt->execute();
            $stmt->close();
            
            header("Location: user_profile.php");
        }
    } else {
        echo "Form not submitted";
        header("Location: user_profile.php");
    }

?>