<?php
  session_start();
  require_once("connect_to_database.php");
  
  $stmt = $mysqli->prepare("SELECT COUNT(*) FROM mail WHERE tousername = ?");
  if (!$stmt) {
    printf("Query preparation failed: %s\n", $mysqli->error);
    exit;
  }
  $stmt->bind_param("s", $_SESSION["username"]);
  $stmt->execute();
  $stmt->bind_result($num_mail);
  $stmt->fetch();
  $stmt->close();

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
                            <a class="dropdown-item" href="mailbox.php">Mailbox</a>
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
                    <li class="nav-item"><a class="nav-link" href="#" disabled>Welcome, <?php echo $_SESSION["username"] ?>!</a></li>
                    <li class="nav-item"><a class="nav-link" href="process_logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="container undernav">
            <?php if ($num_mail > 0) { ?>
                <div class="container row">
                    <div class="alert alert-info" role="alert">
                      <strong>Heads up!</strong><p>You have <?php echo $num_mail ?> messages!</p>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-8">
                    <!-- a big welcome poster -->
                    <div class="jumbotron jumbotron-fluid bg-light">
                        <div class="container">
                            <h1 class="display-3">Welcome to...Memeconomy!</h1>
                            <p class="lead">The future is here. Broaden the horizons of licensing and content exchange with approximately <strong>2</strong> other content creators.</p>
                        </div>
                    </div>
                    <?php
                        // select the top 6 memes based on number of upvotes
                        $statement = $mysqli->prepare("SELECT * FROM memes ORDER BY upvotes DESC LIMIT 6");
                        if (!$statement) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                        }
                        $statement->execute();
                        $memes = $statement->get_result();
                        $statement->close();
                    ?>
                    <div class="card top_buffer">
                        <div class="card-header">
                            Hottest Memes
                        </div>
                        <div class="card-body">
                        <div class="card-columns">
                        <?php if ($memes): ?>
                            <?php while($meme = $memes->fetch_assoc()) : ?>
                                <div class="card">
                                <img class="card-img-top" src="<?php echo htmlentities($meme["filepath"]); ?>" alt="Meme Image">
                                <div class="card-body">
                                    <h4 class="card-title"><a href="view_meme.php?<?php echo "id=" . $meme["id"]; ?>"><?php echo htmlentities($meme["title"]); ?></a></h4>
                                    <p class="card-text"><?php echo htmlentities($meme["description"]); ?></p>
                                </div>
                            </div>
                             <?php endwhile; ?>
                        <?php else : ?>
                            <p>You have no memes.</p>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <?php if (isset($_SESSION["username"])) : ?>
                    <div class="row-sm">
                        <div class="card text-center">
                            <div class="card-header">
                                <h4 class="card-title">Content Creation Panel</h4>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_meme_modal">Upload a Meme</button>
                                <?php if ($_SESSION["username"] == "admin") : ?>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_event_modal">Make an Event</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                    // get the author with the most recent post
                    $statement = $mysqli->prepare("SELECT authorid FROM memes ORDER BY id DESC LIMIT 1");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->execute();
                    $statement->bind_result($recentauthorid);
                    $statement->fetch();
                    $statement->close();
                  
                    // get the username and profile picture of that author
                    $statement = $mysqli->prepare("SELECT username, propic FROM users WHERE id = ?");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->bind_param("i", $recentauthorid);
                    $statement->execute();
                    $statement->bind_result($recentauthor, $propic_path);
                    $statement->fetch();
                    $statement->close();
                ?>
                <!-- today's featured artist! -->
                <div class="row-sm top_buffer">
                    <div class="card text-center">
                        <div class="card-header">
                            <h4>Today's Featured Artist</h4>
                        </div>
                        <div class="card-body">
                            <img class="card-img-top" src="<?php echo $propic_path; ?>" alt="Featured User Profile Picture">
                            <h4 class="card-title top_buffer"><?php echo htmlentities($recentauthor); ?></h4>
                        </div>
                    </div>
                </div>
                
                <?php
                    // get the username and profile picture of that author
                    $statement = $mysqli->prepare("SELECT id, eventname, information FROM events ORDER BY id DESC LIMIT 1");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->execute();
                    $statement->bind_result($eventid, $eventname, $information);
                    $statement->fetch();
                    $statement->close();
                ?>
                <div class="row-sm top_buffer">
                    <div class="card">
                        <div class="card-header text-center">
                            <h4>Current Competition</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong><?php echo htmlentities($eventname); ?></strong>
                                </li>
                                <li class="list-group-item">
                                    <p><?php echo htmlentities($information); ?></p>
                                </li>
                            </ul>
                        </div>
                        <?php if (isset($eventname) && isset($information) && isset($_SESSION["id"]) && $_SESSION["username"] != "admin") { ?>
                            <div class="card body">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#submission_modal">Enter Contest</button>
                            </div>
                        <?php } ?>
                        </div>
                    </div>
                    
                
                
                <?php
                    // get all the categories
                    $statement = $mysqli->prepare("SELECT DISTINCT keywords FROM memes");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->execute();
                    $categories = $statement->get_result();
                    $statement->close();
                ?>
                <div class="row-sm top_buffer">
                    <div class="card text-center">
                        <div class="card-header">
                            <h4>List of Categories</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php if ($categories): ?>
                                    <?php while($category = $categories->fetch_assoc()) : ?>
                                        <li class="list-group-item">
                                            <?php echo htmlentities($category["keywords"]); ?>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <p>No categories.</p>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        
        <!-- New Meme Modal -->
        <div class="modal fade" id="new_meme_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Your Meme's Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form enctype="multipart/form-data" action="process_meme.php" method="POST" id="meme_form">
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="hidden" name="MAX_FILE_SIZE" value="20000000">
                                <label for="meme_file">Your File</label>
                                <input name="meme_file" type="file" id="meme_file">
                            </div>
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input class="form-control" name="title" type="text" id="title">
                            </div>
                            <div class="form-group">
                                <label for="meme_description">Description</label>
                                <textarea id="meme_description" class="form-control" name="description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="keyword">Keyword/Tag (so others can look up your meme!)</label>
                                <input id="keyword" type="text" class="form-control" name="keyword">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>"/>
                            </div>
                            <label>Market Status</label>
                            <div class="form-group"> 
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                    <input ng-model="checked" aria-label="Toggle ngShow" class="form-check-input" type="checkbox" name="market_status" id="for_sale" value="For Sale">
                                        For Sale 
                                    </label>
                                </div>
                                <div class="form-group check-element" ng-show="checked">
                                    <label for="price">Price (in $)</label>
                                    <input class="form-control" name="price" id="price" type="number" min="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button name="submit" type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- modal to make an event -->
        <div class="modal fade" id="new_event_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Make New Event</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="process_event.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="eventname">Event Name</label>
                                <input class="form-control" name="eventname" type="text" id="eventname">
                            </div>
                            <div class="form-group">
                                <label for="information">Information</label>
                                <textarea id="information" class="form-control" name="information"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button name="submit" type="submit" class="btn btn-primary">Create</button>
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
        
        <!-- contest submission modal -->
        <div class="modal fade" id="submission_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Submit Your Contest Submission</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form enctype="multipart/form-data" action="process_submission.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="hidden" name="MAX_FILE_SIZE" value="20000000">
                                <label for="submission_file">Your Submission File</label>
                                <input name="submission_file" type="file" id="submission_file">
                            </div>
                            <div class="form-group">
                                <label for="submission_description">Description</label>
                                <textarea id="submission_description" class="form-control" name="description"></textarea>
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>"/>
                                <input type="hidden" name="eventid" value="<?php echo $eventid;?>">
                            </div>
                        </div>
                        <div class="modal-footer">
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