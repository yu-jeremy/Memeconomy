<?php
    // standard process for resetting session
    session_start();
    session_destroy();
    header("Location: index.php");
?>