<?php
  session_start();
  require_once("connect_to_database.php");
  
  // get the user's profile information
  $stmt = $mysqli->prepare("SELECT first_name, last_name, username, date_joined, password, credits, propic FROM users WHERE id = ?");
  if (!$stmt) {
      printf("Query preparation failed: %s\n", $mysqli->error);
      exit;
  }
  $stmt->bind_param("i", $_SESSION["id"]);
  $stmt->execute();
  $stmt->bind_result($first_name, $last_name, $username, $date_joined, $password, $credits, $propic);
  $stmt->fetch();
  $stmt->close();
  
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Profile</title>
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
              <li class="nav-item active dropdown"><a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Profile</a>
                  <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="your_memes.php">My Memes</a>
                    <a class="dropdown-item active" href="user_profile.php">Profile</a>
                    <a class="dropdown-item" href="mailbox.php">Mailbox</a>
                  </div>
              </li>
              
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
              <li class="nav-item"><button type="button" class="btn btn-link" id="buy_crypto" data-toggle="modal" data-target="#buy_crypto_modal">Buy Cryptocurrency</button></li>
              <li class="nav-item"><a class="nav-link" href="process_logout.php">Logout</a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <div class="container undernav">
            <div class="row">
                <div class="col-8">
                  <div class="card">
                    <div class="card-header">
                      Your Profile Information
                    </div>
                    <div class="card-body">
                      <div class="card-text">
                        <form action="edit_profile.php" method="POST">
                          <div class="form-group">
                            <label for="first_name">First Name: </label>
                            <input name="first_name" class="form-control profile" type="text" value=<?php echo htmlentities($first_name); ?> readonly>
                          </div>
                          <div class="form-group">
                            <label for="last_name">Last Name: </label>
                            <input name="last_name" class="form-control profile" type="text" value=<?php echo htmlentities($last_name); ?> readonly>
                          </div>
                          <div class="form-group">
                            <label for="username">Username: </label>
                            <input name="username" class="form-control profile" type="text" value=<?php echo htmlentities($username); ?> readonly>
                          </div>
                          <div class="form-group">
                            <label id="new_pwd_label" for="password" hidden>Reset Password:</label>
                            <input class="form-control" type="text" id="new_password" name="new_password" hidden>
                          </div>
                          <div class="form-group">
                            <label for="date_joined">Date Joined: </label>
                            <input type="text" readonly class="form-control-plaintext" id="date_joined" value=<?php echo $date_joined?>>
                          </div>
                          <div class="form-group">
                            <button id="edit_profile" type="button" class="btn btn-primary">Edit Profile</button>
                            <button name="submit" id="submit_profile" type="submit" class="btn btn-primary" hidden>Submit</button>
                          </div>
                        </form>
                      </div>
                    </div>
                    </div>
                  </div>
                <div class="col-4">
                  <div>
                    <button class="btn btn-light" id="propic_btn" data-toggle="modal" data-target="#propic_modal"><img src=<?php echo $propic ?> alt="Profile Picture" class="img-thumbnail"></button>
                    <div class="card text-center top_buffer">
                      <div class="card-header">
                        <h4 class="card-title">Content Creation Panel</h4>
                      </div>
                      <div class="card-body">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new_meme_modal">Upload a Meme</button>
                      </div>
                    </div>
                  </div>
                  <div class="top_buffer">
                    <div class="card text-center">
                      <div class="card-header">
                        <h4 class="card-title">Cryptocurrency</h4>
                      </div>
                      <div class="card-body">
                        <h1>$<?php echo $credits ?></h1>
                      </div>
                    </div>
                  </div>
                  <div class="top_buffer">
                    <form action="delete_user.php" method="POST">
                      <div class="card text-center">
                        <button name="submit" type="submit" class="btn btn-danger">Delete Account</button>
                      </div>
                    </form>
                  </div>
                </div>
            </div>
            </div>
        
        <!-- New Profile picture modal -->
        <div class="modal fade" id="propic_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Change Your Profile Picture</h4>
                <button type="button" class="close" data-dismiss"modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form enctype="multipart/form-data" action="set_propic.php" method="POST">
                <div class="modal-body">
                  <div class="form-group">
                    <input type="hidden" name="MAX_FILE_SIZE" value="200000000">
                    <input name="propic_file" type="file" id="propic_file" accept=".jpg, .jpeg, .png, .gif">
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
          
          
        </div>
        

        <!-- New Meme Modal -->
        <div class="modal fade" id="new_meme_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Your Meme's Information</h4>
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
                    <textarea class="form-control" name="description" form="meme_form"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="keyword">Keyword/Tag (so others can look up your meme!)</label>
                    <input type="text" class="form-control" name="keyword"></textarea> 
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
        
        <!-- purchasing more crypto modal -->
        <div class="modal fade" id="buy_crypto_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Purchase Cryptocurrency</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="process_crypto_purchase.php" method="POST">
                        <div class="modal-body">
                          <div class="form-group">
                              <label>Amount To Purchase</label>
                              <input name="amount" type="number" class="form-control" placeholder="Insert desired purchase amount here" min="0">
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
        
    </body>




    
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="script.js"></script>
</html>


