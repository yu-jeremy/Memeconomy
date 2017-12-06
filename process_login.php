<?php
    // things we need
    session_start();
    require_once("connect_to_database.php");
    
    // check that submit is pressed
    if (isset($_POST["submit"])) {
        if (!isset($_POST["username"]) || trim($_POST["username"]) == "" || trim($_POST["password"]) == "" || !isset($_POST["password"]))  {
            header("Location: index.php");
        } else {
            // standard validation process
            $username = $_POST["username"];
            $password = $_POST["password"];
            $statement = $mysqli->prepare("SELECT COUNT(*), password, id FROM users WHERE username = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("s", $username);
            $statement->execute();
            // check hashed password!
            $statement->bind_result($count, $hashed_password_from_database, $user_id);
            $statement->fetch();
            if ($count == 1 && password_verify($password, $hashed_password_from_database)) {
                $statement->close();
                $_SESSION["id"] = $user_id;
                $_SESSION["username"] = $username;
                // initiate a csrf token 
                $_SESSION["token"] = bin2hex(openssl_random_pseudo_bytes(32));
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else { 
                $statement->close();
                header("Location:index.php");
            }
        }
    } else {
        header("Location: index.php");
    }
?>
