<?php
    session_start();
    require_once("connect_to_database.php");
    
    if (isset($_POST["submit"])) {
        if ($_SESSION["token"] !== $_POST["token"]) {
            die("Request forgery detected");
        } else {
            $tousername = $_POST["to"];
            $header = $_POST["header"];
            $message = $_POST["message"];
            $fromusername = $_SESSION["username"];
            
            // create the entry in mailbox table
            $stmt = $mysqli->prepare("INSERT INTO mail (fromusername, tousername, content, header) values (?, ?, ?, ?)");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param("ssss", $fromusername, $tousername, $message, $header);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
?>