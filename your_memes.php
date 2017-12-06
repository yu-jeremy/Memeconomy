<?php
  session_start();
  require_once("connect_to_database.php");
  function shorten($text) {
        // return the string starting from 0 and ending at 500
        $text = substr($text, 0, 500);
        // find the last time a space occurs in the text and 
        // return the string starting from 0 and ending at that space
        $text = substr($text, 0, strrpos($text, " "));
        // append "..." onto the end
        $text = $text . "...";
        // return the now-shortened text
        return $text;
    }
    
    // get the total number of posts
    $statement = $mysqli->prepare("SELECT COUNT(*) FROM memes WHERE authorid = ?");
    if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
    }
    $statement->bind_param("i", $_SESSION["id"]);
    $statement->execute();
    $statement->bind_result($total);
    $statement->fetch();
    $statement->close();
    
    // pagination script, inspired by
    // https://stackoverflow.com/questions/3705318/simple-php-pagination-script
    $limit = 7;
    $pages = ceil($total/$limit);
    $page = min($pages, filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT, array("options" => array("default" => 1, "min_range" => 1),)));
    $offset = ($page - 1) * $limit;
    $num_memes = $total;
    // select posts for one page
    $statement = $mysqli->prepare("SELECT * FROM memes WHERE authorid = ? ORDER BY id DESC LIMIT ? OFFSET ?");
    if (!$statement) {
        printf("Query preparation failed: %s\n", $mysqli->error);
        exit;
    }
    $statement->bind_param("iii", $_SESSION["id"], $limit, $offset);
    $statement->execute();
    $memes = $statement->get_result();
    $statement->close();
    
    $statement = $mysqli->prepare("SELECT credits FROM users WHERE id = ?");
    if (!$statement) {
      printf("Query preparation failed: %s\n", $mysqli->error);
      exit;
    }
    $statement->bind_param("i", $_SESSION["id"]);
    $statement->execute();
    $statement->bind_result($credits);
    $statement->fetch();
    $statement->close();
    
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
                    <a class="active dropdown-item" href="your_memes.php">My Memes</a>
                    <a class="dropdown-item" href="user_profile.php">Profile</a>
                    <a class="dropdown-item" href="mailbox.php">Mailbox</a>
                  </div>
              </li>
              
          </ul>
          <form class="form-inline my-2 my-lg-0" action="process_search.php" method="POST">
          <input class="form-control mr-sm-2" name="search" type="text" placeholder="Search">
          <button class="btn btn-outline-primary my-2 my-sm-0" name="submit" type="submit">Search</button>
        </form>
          <ul class="navbar-nav ml-auto">
              <li class="nav-item"><a class="nav-link" href="#" disabled>Your Crypto: $<?php echo $credits ?></a></li>
              <li class="nav-item"><a class="nav-link" href="process_logout.php">Logout</a></li>
          </ul>
        </nav>
        <div class="container undernav">
            <div class="row">
                <div class="col-8">
                  <div class="card">
                    <div class="card-header">
                      Your Memes
                    </div>
                    <div class="card-body">
                      <nav>
                          <ul class="pagination">
                            <?php if ($page > 1) : ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a></li>
                            <?php else : ?>
                                <li class="page-item disabled"><a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a></li>
                            <?php endif; ?>
                            <?php for ($counter = 1; $counter <= $pages; $counter++) : ?>
                                <li class="page-item <?php if ($page == $counter) { echo "disabled"; } ?>"><a class="page-link" href="?page=<?php echo $counter; ?>"><?php echo $counter; ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $pages) : ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a></li>
                            <?php else : ?>
                                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                            <?php endif; ?>
                          </ul>
                        </nav>
                      <div class="card-columns">
                        <?php if ($memes): ?>
                            <?php while($meme = $memes->fetch_assoc()) : ?>
                              <div class="card">
                              <img class="card-img-top" src="<?php echo $meme["filepath"]; ?>" alt="Meme Image">
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
                  <?php
                    // get total upvotes and downvotes and comments
                    $statement = $mysqli->prepare("SELECT upvotes, downvotes, comments FROM users WHERE id = ?");
                    if (!$statement) {
                      printf("Query preparation failed: %s\n", $mysqli->error);
                      exit;
                    }
                    $statement->bind_param("i", $_SESSION["id"]);
                    $statement->execute();
                    $statement->bind_result($userupvotes, $userdownvotes, $usercomments);
                    $statement->fetch();
                    $statement->close();
                  
                  ?>
                  <div class="row-sm top_buffer">
                    <div class="card">
                      <div class="card-header text-center">
                        <h4 class="card-title">Stats</h4>
                      </div>
                      <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          Total Upvotes
                          <span class="badge badge-primary badge-pill"><?php echo $userupvotes; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          Total Downvotes
                          <span class="badge badge-primary badge-pill"><?php echo $userdownvotes; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          Total Comments
                          <span class="badge badge-primary badge-pill"><?php echo $usercomments; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          Number of Memes
                          <span class="badge badge-primary badge-pill"><?php echo $num_memes ?></span>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="row-sm top_buffer">
                    <div class="card">
                      <div class="card-header text-center">
                        <h4 class="card-title">Achievements</h4>
                      </div>
                      <div class="card-body">
                        <?php if (isset($_SESSION["username"])) { ?>
                          <button type="button" class="btn" data-toggle="modal" data-target="#registered_modal">
                            <img src="achievements/registered.png" alt="Registration Achievement" width=40% height=40%>
                          </button>
                        <?php } if ($num_memes >= 10) { ?>
                          <button type="button" class="btn" data-toggle="modal" data-target="#ten_memes_modal">
                            <img src="achievements/veteran_creator.png" alt="Veteran Creator" width=40% height=40%>
                          </button>
                        <?php } if ($num_memes >= 1000) { ?>
                          <button type="button" class="btn" data-toggle="modal" data-target="#memelord_modal">
                            <img src="achievements/memelord.png" alt="Memelord" width=40% height=40%>
                          </button>
                        <?php } 
                        
                          $stmt = $mysqli->prepare("SELECT * FROM memes where authorid = ?");
                          if (!$statement) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                          }
                          $stmt->bind_param("i", $_SESSION["id"]);
                          $stmt->execute();
                          $collection = $stmt->get_result();
                          $stmt->close();
                          
                          $bought_one = false;
                          
                          while ($this_meme = $collection->fetch_assoc()) {
                            if ($_SESSION["username"] != $this_meme["licensedto"]) {
                              $bought_one = true;
                            }
                          }
                        ?>
                        <?php if ($bought_one) { ?>
                          <button type="button" class="btn" data-toggle="modal" data-target="#bought_one_modal">
                            <img src="achievements/boughtone.png" alt="Bought a meme" width=40% height=40%>
                          </button>
                        <?php } 
                        
                          $stmt = $mysqli->prepare("SELECT COUNT(*) FROM mail WHERE fromusername = ?");
                          if (!$stmt) {
                            printf("Query preparation failed: %s\n", $mysqli->error);
                            exit;
                          }
                          $stmt->bind_param("s", $_SESSION["username"]);
                          $stmt->execute();
                          $stmt->bind_result($msg_count);
                          $stmt->fetch();
                          $stmt->close();
                          
                          if ($msg_count > 0) { ?>
                            <button type="button" class="btn" data-toggle="modal" data-target="#sent_msg_modal">
                              <img src="achievements/sentmessage.png" alt="Sent a message Achievement" width=40% height=40%>
                            </button>
                          <?php } 
                          
                            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM memes WHERE licensedto = ?");
                            if (!$stmt) {
                              printf("Query preparation failed: %s\n", $mysqli->error);
                              exit;
                            }
                            $stmt->bind_param("s", $_SESSION["username"]);
                            $stmt->execute();
                            $stmt->bind_result($my_memes);
                            $stmt->fetch();
                            $stmt->close();
                            
                            if ($my_memes > 0) { ?>
                              <button type="button" class="btn" data-toggle="modal" data-target="#uploaded_one_modal">
                                <img src="achievements/uploadedone.png" alt="Uploaded A Meme Achievement" width=40% height=40%>
                              </button>
                            <?php } ?>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
        
        
        <!-- uploaded one modal -->
        <div class="modal fade" id="uploaded_one_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">One Down, Many To Go!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                  <md-card-content>
                    <p><img src="achievements/uploadedone.png" class="md-card-image" alt="Bought one achievement" width=50% height=50%></p>
                    <h2>Thanks for uploading a meme...</h2>
                    <p>We hope there are many more to come from such a talented content creator!</p>
                  </md-card-content>
                </md-card>
              </div>
            </div>
          </div>
        </div>
        
        <!-- bought one modal -->
        <div class="modal fade" id="bought_one_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Stimulate the Memeconomy!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                  <md-card-content>
                    <p><img src="achievements/boughtone.png" class="md-card-image" alt="Bought one achievement" width=50% height=50%></p>
                    <h2>You bought a meme!</h2>
                    <p>Thank you for being a part of Memeconomy, continue to share and buy!</p>
                  </md-card-content>
                </md-card>
              </div>
            </div>
          </div>
        </div>
        
        <!-- sent message modal -->
        <div class="modal fade" id="sent_msg_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Communication is Key</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                  <md-card-content>
                    <p><img src="achievements/sentmessage.png" class="md-card-image" alt="Bought one achievement" width=50% height=50%></p>
                    <h2>You sent a message to another user!</h2>
                    <p>We love to see that you're interacting with others here at Memeconomy!</p>
                  </md-card-content>
                </md-card>
              </div>
            </div>
          </div>
        </div>
        
        <!-- memelord modal -->
        <div class="modal fade" id="memelord_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">YOU ARE A...!!!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                  <md-card-content>
                    <p><img src="achievements/memelord.png" class="md-card-image" alt="Bought one achievement" width=50% height=50%></p>
                    <h2>You have attained the title of Memelord!</h2>
                    <p>The Memeconomy community is blessed by your presence</p>
                  </md-card-content>
                </md-card>
              </div>
            </div>
          </div>
        </div>
        
        <!-- registered achievement modal -->
        <div class="modal fade" id="registered_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Registered!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                 <md-card-content>
                   <p><img src="achievements/registered.png" class="md-card-image" alt="image caption" width=50% height=50%></p>
                   <h2>Congrats <?php echo $_SESSION["username"] ?>!</h2>
                   <p>You're registered and ready to go!</p>
                 </md-card-content>
                </md-card>
              </div>
            </div>
          </div>
        </div>
    
        <!-- 10 memes achievement modal -->
        <div class="modal fade" id="ten_memes_modal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Veteran Content Creator</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <md-card class="text-center">
                  <md-card-content>
                    <p><img src="achievements/veteran_creator.png" class="md-card-image" alt="image caption" width=50%></p>
                    <h2>Gained some experience huh?</h2>
                    <p>You're well on your way to becoming the next memelord! Keep on creating!</p>
                  </md-card-content>
                </md-card>
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
                    <input type="text" class="form-control" name="keyword">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
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
        
    </body>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
    <script src="script.js"></script>
    
</html>


