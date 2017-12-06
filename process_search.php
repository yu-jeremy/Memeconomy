<?php
    session_start();
	require_once("connect_to_database.php");
	// this is all that is needed to search for a keyword
	if (isset($_POST["submit"])) {
	    $keyword = $_POST["search"];
	    $statement = $mysqli->prepare("SELECT * FROM memes WHERE keywords LIKE ?");
        if (!$statement) {
            printf("Query preparation failed: %s\n", $mysqli->error);
            exit;
        }
        $statement->bind_param("s", $keyword);
        $statement->execute();
        $searches = $statement->get_result();
        $statement->close();
	} else {
	    header("Location: index.php");
	}
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
    
    <!--
    Using AngularJS for...
    
    1). Showing/hiding "price" input based on checkbox
    2).
    3).
    4).
    5). 
    
    Thing to use AngularJS for: search function
    *Uploading files would happen on the home page when the user is logged in
    Search Page: have a separate page or keep on home?
        - if separate, we can have it display all memes (limiting the number per page)
            - we can then dynamically display memes based on what we type in
            - on this search page, we would also have tags on the side (for sale, not for sale, etc)
        - if not separate, we would probably have the home page display memes categorically and then typing in the search
        would once again only show memes with those keywords
    
    *Bootstrap allows us to upload files pretty easily but we can still do it the way we did it in module 2
    -->
    <body ng-app="">
        <!-- navigation bar; what pages will we have? -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="navbar-brand">
                        <img src="assets/logo.png" width="100" height="30" class="d-inline-block align-left" alt="">
                    </a>
                </li>
                <li class="nav-item active align-right"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <?php if(isset($_SESSION["username"])) : ?>
                <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Profile</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="your_memes.php">My Memes</a>
                        <a class="dropdown-item" href="user_profile.php">Profile</a>
                    </div>
                </li>
                <?php endif; ?>
          </ul>
          <form class="form-inline my-2 my-lg-0" action="" method="POST">
          <input class="form-control mr-sm-2" name="search" type="text" placeholder="Search">
          <button class="btn btn-outline-primary my-2 my-sm-0" name="submit" type="submit">Search</button>
        </form>
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
                      $statement = $mysqli->prepare("SELECT * FROM memes ORDER BY upvotes DESC LIMIT 6");
                      if (!$statement) {
                          printf("Query preparation failed: %s\n", $mysqli->error);
                          exit;
                      }
                      $statement->execute();
                      $memes = $statement->get_result();
                      $statement->close();
                    ?>
                  <div class="card">
                    <div class="card-header">
                      Here are the results for: <strong><?php echo htmlentities($keyword); ?></strong>
                    </div>
                    <div class="card-body">
                      <div class="card-columns">
                        <?php $tempnum = 0; ?>
                        <?php if ($searches): ?>
                              <?php while($search = $searches->fetch_assoc()) : ?>
                              <?php $tempnum = $tempnum + 1; ?>
                                <div class="card">
                                <img class="card-img-top" src="<?php echo $search["filepath"]; ?>" alt="Meme Image">
                                <div class="card-body">
                                  <h4 class="card-title"><a href="view_meme.php?<?php echo "id=" . $search["id"]; ?>"><?php echo htmlentities($search["title"]); ?></a></h4>
                                  <p class="card-text"><?php echo htmlentities($search["description"]); ?></p>
                                </div>
                              </div>
                              <?php endwhile; ?>
                          <?php else : ?>
                              <p>You have no memes.</p>
                          <?php endif; ?>
                          <?php if ($tempnum == 0) : ?>
                              <p>Your search ended in no results.</p>
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
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php
                    $statement = $mysqli->prepare("SELECT authorid FROM memes ORDER BY id DESC LIMIT 1");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->execute();
                    $statement->bind_result($recentauthorid);
                    $statement->fetch();
                    $statement->close();
                  
                    $statement = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
                    if (!$statement) {
                        printf("Query preparation failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $statement->bind_param("i", $recentauthorid);
                    $statement->execute();
                    $statement->bind_result($recentauthor);
                    $statement->fetch();
                    $statement->close();
                  ?>
                  <div class="row-sm top_buffer">
                    <div class="card text-center">
                      <div class="card-header">
                        <h4>Today's Featured Artist</h4>
                      </div>
                      <div class="card-body">
                        <h4 class="card-title"><?php echo htmlentities($recentauthor); ?></h4>
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
                    <input class="form-control" name="title" type="text"id="title">
                  </div>
                  <div class="form-group">
                    <label for="meme_description">Description</label>
                    <textarea class="form-control" name="description"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="keyword">Keyword/Tag (so others can look up your meme!)</label>
                    <input type="text" class="form-control" name="keyword"></textarea> 
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
        
    </body>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="script.js"></script>
</html>


