<?php
  session_start();
  require_once("connect_to_database.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Chris Mills">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Memeconomy</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    </head>
    <body ng-app="">
        <!-- a bootstrap navigation bar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <!-- items to go on the left of the navigation bar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="navbar-brand">
                        <img src="assets/logo.png" width="100" height="30" class="d-inline-block align-left" alt="">
                    </a>
                </li>
                <li class="nav-item active align-right"><a class="nav-link" href="index.php">Home</a></li>
                <!-- we only allow users to see their profile if they are logged in -->
                <?php if(isset($_SESSION["username"])) : ?>
                    <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Profile</a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="your_memes.php">My Memes</a>
                            <a class="dropdown-item" href="user_profile.php">Profile</a>
                            <a class="dropdown-item active" href="mailbox.php">Mailbox</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
            <!-- the search function -->
            <form class="form-inline my-2 my-lg-0" action="process_search.php" method="POST">
                <input class="form-control mr-sm-2" name="search" type="text" placeholder="Search">
                <button class="btn btn-outline-primary my-2 my-sm-0" name="submit" type="submit">Search</button>
            </form>
            <!-- login/register/logout -->
            <ul class="navbar-nav ml-auto">
                <?php if (!isset($_SESSION["username"])) : ?>
                    <li class="nav-item"><a class="nav-link" href="" data-toggle="modal" data-target="#login_modal">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="" data-toggle="modal" data-target="#register_modal">Register</a></li>
                <?php else : ?>
                    <li class="nav-item"><a class="nav-link" href="process_logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="container undernav">
            <div class="row">
                <div class="col-8">
                    <?php
                        // get all your mail
                        $statement = $mysqli->prepare("SELECT * FROM mail WHERE tousername = ? ORDER BY id DESC ");
                        if (!$statement) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                        }
                        $statement->bind_param("s", $_SESSION["username"]);
                        $statement->execute();
                        $mails = $statement->get_result();
                        $statement->close();
                    ?>
                    <div class="card">
                        <div class="card-header">
                            <h4>Your Inbox</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php if ($mails): ?>
                                    <?php while($mail = $mails->fetch_assoc()) : ?>
                                        <li class="list-group-item">
                                            <strong>From: </strong><?php echo htmlentities($mail["fromusername"]); ?>   ||   <strong>Header: </strong><?php echo htmlentities($mail["header"]); ?>
                                            <a href="view_mail.php?<?php echo "id=" . $mail["id"]; ?>" class="btn btn-primary btn-sm float-right" role="button" aria-pressed="true">Read</a>
                                            <?php if ($mail["readornot"] == 0) : ?>
                                                <span class="badge badge-warning badge-pill">unread</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <p>You have no memes.</p>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <div class="col-4">
                <?php if (isset($_SESSION["username"])) : ?>
                    <div class="row-sm">
                        <div class="card text-center">
                            <div class="card-header">
                                <h4 class="card-title">Compose Mail</h4>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_mail_modal">Compose Mail</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                    $statement = $mysqli->prepare("SELECT * FROM mail WHERE fromusername = ? ORDER BY id DESC ");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->bind_param("s", $_SESSION["username"]);
                    $statement->execute();
                    $sentmails = $statement->get_result();
                    $statement->close();
                ?>
                <!-- mail you've sent -->
                <div class="row-sm top_buffer">
                    <div class="card text-center">
                        <div class="card-header">
                            <h4>Mail You've Sent</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php if ($sentmails): ?>
                                    <?php while($sent = $sentmails->fetch_assoc()) : ?>
                                        <li class="list-group-item">
                                            <strong>To: </strong><?php echo htmlentities($sent["tousername"]); ?>   ||   <strong>Header: </strong><?php echo htmlentities($sent["header"]); ?>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <p>You have no memes.</p>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
            </div>
            </div>
        </div>
        
        <!-- Compose new mail -->
        <div class="modal fade" id="new_mail_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Compose Mail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="process_mail.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="to">Username of Recipient: </label>
                                <input class="form-control" name="to" type="text" id="to">
                            </div>
                            <div class="form-group">
                                <label for="header">Header</label>
                                <input class="form-control" name="header" type="text" id="header">
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" class="form-control" name="message"></textarea>
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>"/>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button name="submit" type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- login modal -->
        <div class="modal fade" id="login_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Log In</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="process_login.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Username</label>
                                <input name="username" type="text" class="form-control" placeholder="">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input name="password" type="password" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <small class="form-text text-muted">Don't have an account? Create one <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#register_modal">here</a>.</small>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- register modal -->
        <div class="modal fade" id="register_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Register</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="process_register.php" method="POST">
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">First Name</label>
                                    <input name="first_name" type="text" class="form-control" placeholder="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Last Name</label>
                                    <input name="last_name" type="text" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input name="username" type="text" class="form-control" placeholder="">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input name="password" type="password" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <small class="form-text text-muted">Already have an account? Create one <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#login_modal">here</a>.</small>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="script.js"></script>
    </body>
</html>