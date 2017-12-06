<?php
    // things we need
    session_start();
	require_once("connect_to_database.php");
	// check that submit was pressed
    if (isset($_POST["submit"])) {
        
        if ($_SESSION["token"] !== $_POST["token"]) {
            die("Request forgery detected");
        } else {
            // grab relevant info
            $memeid = (int)$_POST["memeid"];
            $title = $_POST["title"];
            $description = $_POST["description"];
            $price = (int)$_POST["price"];
            $market_status = isset($_POST["market_status"]);
            $keyword = $_POST["keyword"];
            
            // update the meme
            $stmt = $mysqli->prepare("UPDATE memes SET title = ?, description = ?, price = ?, forsale = ?, keywords = ? WHERE id = ?");
            if (!$stmt) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            } 
            $stmt->bind_param("ssiisi", $title, $description, $price, $market_status, $keyword, $memeid);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    } else {
        header("Location: index.php");
    }
?>