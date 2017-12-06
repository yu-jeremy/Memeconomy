<?php
  session_start();
  require_once("connect_to_database.php");
  
  $stmt = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
  if (!$stmt) {
    printf("Query preparation failed: %s\n", $mysqli->error);
    exit;
  }
  $stmt->bind_param("i", $_SESSION["id"]);
  $stmt->execute();
  $stmt->bind_result($current_crypto);
  $stmt->fetch();
  $stmt->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Memeconomy</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    </head>
    <body ng-app="">
        <!-- navigation bar; what pages will we have? -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
          
          <ul class="navbar-nav mr-auto">
              <li class="nav-item">
                <a class="navbar-brand">
                  <img src="assets/logo.png" width="100" height="30" class="d-inline-block align-left" alt="">
                </a>
              </li>
              <li class="nav-item align-right"><a class="nav-link" href="index.php">Home</a></li>
              
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
          <form class="form-inline my-2 my-lg-0" action="process_search.php" method="POST">
          <input class="form-control mr-sm-2" name="search" type="text" placeholder="Search">
          <button class="btn btn-outline-primary my-2 my-sm-0" name="submit" type="submit">Search</button>
        </form>
          <ul class="navbar-nav ml-auto">
            <?php if (!isset($_SESSION["username"])) : ?>
              <li class="nav-item"><a class="nav-link" href="" data-toggle="modal" data-target="#login_modal">Login</a></li>
              <li class="nav-item"><a class="nav-link" href="" data-toggle="modal" data-target="#register_modal">Register</a></li>
            <?php else : ?>
              <li class="nav-item"><a class="nav-link" href="#" disabled>Your Crypto: $<?php echo $current_crypto ?></a></li>
              <li class="nav-item"><a class="nav-link" href="process_logout.php">Logout</a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php
            $memeid = (int)$_GET["id"];
            
            $statement = $mysqli->prepare("SELECT title, description, authorid, licensedto, datemade, price, filepath, forsale, keywords FROM memes WHERE id = ?");
            if (!$statement) {
                printf("Query preparation failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param("i", $memeid);
            $statement->execute();
            $statement->bind_result($title, $description, $authorid, $licensedto, $datemade, $price, $filepath, $forsale, $keyword);
            $statement->fetch();
            $statement->close();
            
        ?>
        <div class="container undernav">
            <div class="row">
                <div class="col-5">
                  <div class="card">
                    <div class="card-body">
                      <h4 class="card-title"><?php echo htmlentities($title); ?></h4>
                      <p class="card-text"><?php echo htmlentities($description); ?></p>
                      <?php
                      // only show if you're the author
                        if ($_SESSION["id"] == $authorid && $_SESSION["username"] == $licensedto) : 
                      ?>
                      <form action="process_delete_meme.php" method="POST">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#edit_modal">
                          Edit Meme
                        </button>
                        <input type="hidden" name="memeid" value="<?php echo $memeid; ?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                        <button name="submit" type="submit" class="btn btn-danger">Delete</button>
                      </form>
                      <?php endif; ?>
                      <hr>
                    </div>
                    <div class="card-body">
                      <?php
                        $statement = $mysqli->prepare("SELECT * FROM meme_comments WHERE memeid = ? ORDER BY id DESC");
                        if (!$statement) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                        }
                        $statement->bind_param("i", $memeid);
                        $statement->execute();
                        $comments = $statement->get_result();
                        $statement->close();
                        
                      ?>
                      <?php if ($comments): ?>
                            <?php while($comment = $comments->fetch_assoc()) : ?>
                            <?php
                              $statement = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
                              if (!$statement) {
                                  printf("Query preparation failed: %s\n", $mysqli->error);
                                  exit;
                              }
                              $statement->bind_param("i", $comment["authorid"]);
                              $statement->execute();
                              $statement->bind_result($usernameofcommenter);
                              $statement->fetch();
                              $statement->close();
                            ?>
                            <div class="alert alert-secondary" role="alert">
                              <strong><?php echo htmlentities($usernameofcommenter); ?> says: </strong> <?php echo htmlentities($comment["comment"]); ?>
                            </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <p>You have no comments.</p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION["id"])) : ?>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#comment_modal">
                        Leave a Comment
                      </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="row-sm">
                    <?php 
                        $statement = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
                        if (!$statement) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                        }
                        $statement->bind_param("i", $authorid);
                        $statement->execute();
                        $statement->bind_result($author_username);
                        $statement->fetch();
                        $statement->close();
                      ?>
                    <div class="card text-center">
                      <h4 class="card-header">Meme Info</h4>
                      <?php if ($forsale) { ?> 
                        <h4 class="card-body">Listed Price:
                          <?php if ($price == 0) { 
                                  echo "Free";
                                } else { 
                                  echo "$" . $price;
                                } ?>
                        </h4>
                      <?php } else { ?>
                        <h4 class="card-body">For Viewing Only</h4>
                      <?php } ?>
                    </div>
                    <div class="card text-center top_buffer">
                      <h4 class="card-header">Author/Creator</h4>
                      <h4 class="card-body"><?php echo htmlentities($licensedto); ?></h4>
                    </div>
                    <?php if ($author_username != $licensedto) { ?>
                      <div class="card text-center top_buffer">
                        <h4 class="card-header">Licensed to</h4>
                        <h4 class="card-body"><?php echo htmlentities($author_username); ?></h4>
                      </div>
                    <?php } ?>
                    <?php if ($authorid != $_SESSION["id"] && $forsale) { ?>
                      <form action="purchase_license.php" method="POST">
                        <div class="card top_buffer">
                          <input type="hidden" name="meme_id" value="<?php echo $memeid ?>">
                          <button type="submit" name="submit" class="btn btn-success">Purchase this meme</button>
                        </div>
                      </form>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4">
                  <div class="row-sm">
                    <div class="card">
                      <img class="card-img-top" src="<?php echo $filepath; ?>" alt="Meme Image">
                    </div>
                    <?php
                      $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE memeid = ? AND value = 2");
                      if (!$statement) {
                          printf("Query preparation failed: %s\n", $mysqli->error);
                          exit;
                      }
                      $statement->bind_param("i", $memeid);
                      $statement->execute();
                      $statement->bind_result($num_upvotes);
                      $statement->fetch();
                      $statement->close();
                      $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE memeid = ? AND value = 1");
                      if (!$statement) {
                          printf("Query preparation failed: %s\n", $mysqli->error);
                          exit;
                      }
                      $statement->bind_param("i", $memeid);
                      $statement->execute();
                      $statement->bind_result($num_downvotes);
                      $statement->fetch();
                      $statement->close();
                      
                      // votes by user
                      $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE (memeid = ? AND value = 2 AND authorid = ?)");
                      if (!$statement) {
                          printf("Query preparation failed: %s\n", $mysqli->error);
                          exit;
                      }
                      $statement->bind_param("ii", $memeid, $_SESSION["id"]);
                      $statement->execute();
                      $statement->bind_result($num_loggedupvotes);
                      $statement->fetch();
                      $statement->close();
                      
                      // votes by user
                      $statement = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE (memeid = ? AND value = 1 AND authorid = ?)");
                      if (!$statement) {
                          printf("Query preparation failed: %s\n", $mysqli->error);
                          exit;
                      }
                      $statement->bind_param("ii", $memeid, $_SESSION["id"]);
                      $statement->execute();
                      $statement->bind_result($num_loggeddownvotes);
                      $statement->fetch();
                      $statement->close();
                    ?>
                    <?php if ($_SESSION["username"] != $licensedto && $_SESSION["username"] != $author_username) { ?>
                    <form action="process_meme_vote.php" method="POST">
                      <input type="hidden" name="memeid" value="<?php echo $memeid; ?>">
                      <button name="upvote" type="submit" style="margin-top: 8px;" type="button" class="btn btn-block <?php if ($num_loggedupvotes > 0) { echo "btn-dark"; } else { echo "btn-secondary";} ?>"<?php if (($num_loggeddownvotes > 0) || (!isset($_SESSION["username"]))) { echo "disabled"; } ?>>Upvotes [<?php echo $num_upvotes; ?>]</button>
                      <button name="downvote" type="submit" class="btn btn-secondary btn-block <?php if ($num_loggeddownvotes > 0) { echo "btn-dark"; } else { echo "btn-secondary";} ?>" <?php if (($num_loggedupvotes > 0) || (!isset($_SESSION["username"]))) { echo "disabled"; } ?>>Downvotes [<?php echo $num_downvotes; ?>]</button>
                    </form>
                    <?php } ?>
                  </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <div class="modal fade" id="comment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Leave a Comment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form action="process_meme_comment.php" method="POST">
                <div class="modal-body">
                  <div class="form-group">
                    <textarea class="form-control" name="comment"></textarea>
                    <input type="hidden" name="memeid" value="<?php echo $memeid; ?>">
                  </div>
                </div>
                <div class="modal-footer">
                    <small class="form-text text-muted">Please don't leave profanity.</small>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
            </div>
          </div>
        </div>
        
        <!-- Edit Meme Modal -->
        <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Currently Editing Meme: <?php echo $memeid; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form action="process_meme_edit.php" method="POST">
                <div class="modal-body">
                  <div class="form-group">
                    <label for="title">Title</label>
                    <input class="form-control" name="title" type="text"id="title" value="<?php echo htmlentities($title); ?>">
                    <input type="hidden" name="memeid" value="<?php echo $memeid; ?>">
                  </div>
                  <div class="form-group">
                    <label for="meme_description">Description</label>
                    <textarea class="form-control" name="description"><?php echo htmlentities($description); ?></textarea>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                  </div>
                  <div class="form-group">
                    <label for="keyword">Keyword/Tag (so others can look up your meme!)</label>
                    <input type="text" class="form-control" name="keyword" value=<?php echo htmlentities($keyword); ?>></textarea> 
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
                      <input class="form-control" name="price" id="price" type="number" min="0" value=<?php echo $price; ?>>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                    <small class="form-text text-muted">Please don't leave profanity.</small>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
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
        
        

        <!-- New Meme Modal -->
        <div class="modal fade" id="new_meme_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Your Meme's Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form>
                  <div class="form-group">
                    <label for="meme_file">Your File</label>
                    <input type="file" class="form-control-file" id="meme_file" value="Upload">
                  </div>
                  <div class="form-group">
                    <label for="meme_description">Description</label>
                    <textarea class="form-control" id="meme_description" rows="3"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="keywords">Keywords (so others can look up your meme!)</label>
                    <textarea class="form-control" name="keywords"></textarea> 
                  </div>
                  <div class="form-check form-check-inline">
                    <label class="form-check-label">
                      <input class="form-check-input" type="radio" id="for_sale_radio">For Sale
                    </label>
                  </div>
                  <div class="form-check form-check-inline">
                    <label class="form-check-label">
                      <input class="form-check-input" type="radio" id="view_only_radio">For Viewing Only
                    </label>
                  </div>
                  <div class="input-group">
                    <span class="input-group-addon">Listed Price in $</span>
                    <input type="text" class="form-control" aria-label="Amount (to the nearest dollar)">
                  </div>
                  
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Upload</button>
              </div>
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




    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="script.js"></script>
</html>


