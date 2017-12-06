<?php
    session_start();
    require_once("connect_to_database.php");
    if (isset($_POST["submit"])) {
        $eventname = $_POST["eventname"];
        $information = $_POST["information"];
        $statement = $mysqli->prepare("INSERT INTO events (eventname, information) values (?, ?)");
		if (!$statement) {
			printf("Query preparation failed: %s\n", $mysqli->error);
			exit;
		}
		$statement->bind_param("ss", $eventname, $information);
		$statement->execute();
		$statement->close();
		
		if (!file_exists("User_Content/admin/" . $eventname)) {
			// using umask to remove whatever umask is present
			// this allows us to actually create a 777 folder
			$old = umask(0);
			mkdir("User_Content/admin/" . $eventname, 0777);
			chmod("User_Content/admin/" . $eventname, 0777);
			umask($old);
		}
		
		header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: index.php");
    }
?>