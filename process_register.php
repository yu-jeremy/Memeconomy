<?php
	session_start();
	require_once("connect_to_database.php");
	if (isset($_POST["submit"])) {
		if (!isset($_POST["first_name"]) ||
		trim($_POST["first_name"]) == "" ||
		!isset($_POST["last_name"]) ||
		trim($_POST["last_name"]) == "" ||
		!isset($_POST["username"]) ||
		trim($_POST["username"]) == "" ||
		!isset($_POST["password"]) ||
		trim($_POST["password"]) == ""
		) {
			header("Location: index.php");
		} else {
			$first_name = $_POST["first_name"];
			$last_name = $_POST["last_name"];
			$username = $_POST["username"];
			$password = $_POST["password"];
			$propic_path = "assets/propic_default.png";
			
		
			if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
				header("Location: index.php");
			}
			if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
				header("Location: index.php");
			}
			if (strlen($password) < 8 || strlen($password) > 20) {
				header("Location: index.php");
			} 
			if (!preg_match('/^[\w_\-]+$/', $username)) {
				header("Location: index.php");
			}
			// hash the password!
			
			$new_propic_path = "User_Content/" . $username . "/propic_default.png";
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			$statement = $mysqli->prepare("SELECT first_name FROM users WHERE username=?");
			if (!$statement) {
					printf("Query preparation failed: %s\n", $mysqli->error);
					exit;
			}
			$statement->bind_param("s", $username);
			$statement->execute();
			$statement->store_result();
			if ($statement->num_rows == 0) {
				$statement->close();
				$statement = $mysqli->prepare("INSERT INTO users (first_name, last_name, username, password, propic) values (?, ?, ?, ?, ?)");
				if (!$statement) {
						printf("Query preparation failed: %s\n", $mysqli->error);
						exit;
				}
				$statement->bind_param("sssss", $first_name, $last_name, $username, $hashed_password, $new_propic_path);
				$statement->execute();
				$statement->close();
				if (!file_exists("User_Content" . $username)) {
					// using umask to remove whatever umask is present
					// this allows us to actually create a 777 folder
					$old = umask(0);
					mkdir("User_Content/" . $username, 0777);
					chmod("User_Content/" . $username, 0777);
					umask($old);
				}
				copy($propic_path, $new_propic_path);
				header('Location: ' . $_SERVER['HTTP_REFERER']);
			} else {
				header("Location: index.php");
			}
		}
	} else {
			header("Location: index.php");
	}
?>