<?php
  include("connection.php");
  include("functions.php");

  $secret_key = getenv("PASSWORD_PEPPER"); // <-- get the pepper value from an environment variable
  if($_SERVER['REQUEST_METHOD'] == "POST")
  {
      //Something was posted
      $user_name = $_POST['username'];
      $password = $_POST['password'];
      $confirm_password = $_POST['confirm_password'];
      $fname = $_POST['first_name'];
      $lname = $_POST['last_name'];
      $email = $_POST['email'];
      $phone = 1;

      if($password !== $confirm_password){
          echo "Passwords do not match!";
          exit;
      }

      if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
      {
          $query = "select * from user where username = '$user_name' limit 1";
          $result = mysqli_query($con, $query);
          if($result){
              if($result && mysqli_num_rows($result) > 0)
              {
                  //Change this to be more user friendly
                  echo "Username already exists!";
                  exit;
              }
          }

          //save to database
          $password = $password . $secret_key; // <-- append the pepper to the password before hashing
          $password_hash = password_hash($password, PASSWORD_DEFAULT);
          $query = "insert into user (username,password,fname,lname,email,phone,auth_key) values ('$user_name', '$password_hash', '$fname', '$lname', '$email', '$phone', '0')"; // <-- save the salt in the database
          mysqli_query($con, $query);
          header("Location: login.php");
          die;
      }else{
          echo "Please enter some valid information!";
      } 
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Awesome Site - Register</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />

    <style>
      /* (Optional) Example style if you use .post later */
      .post {
        background-color: #e9ecef;
        margin: 5px;
        padding: 10px; /* <-- you were missing the colon before */
        border-radius: 8px;
      }
    </style>
  </head>

  <body class="bg-info">
    <!-- Navbar -->
    <?php set_header(); ?>

    <!-- Page content -->
    <div class="container my-5">
      <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h1 class="h3 text-center mb-4">Register an Account</h1>

              <!-- Form -->
              <!-- Later, you can connect this to your server by changing action="" -->
              <form method="post">
                <!-- First + Last Name -->
                <div class="row g-3 mb-3">
                  <div class="col">
                    <label class="form-label">First name</label>
                    <input
                      type="text"
                      class="form-control"
                      name="first_name"
                      placeholder="First name"
                      required
                    />
                  </div>
                  <div class="col">
                    <label class="form-label">Last name</label>
                    <input
                      type="text"
                      class="form-control"
                      name="last_name"
                      placeholder="Last name"
                      required
                    />
                  </div>
                </div>

                <!-- Username (recommended for a forum) -->
                <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input
                    type="text"
                    class="form-control"
                    name="username"
                    placeholder="Choose a username"
                    required
                  />
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label class="form-label">Email address</label>
                  <input
                    type="email"
                    class="form-control"
                    name="email"
                    placeholder="Enter email"
                    required
                  />
                  <div class="form-text">
                    We'll never share your email with anyone else.
                  </div>
                </div>

                <! -- Phone Number -->
                <!-- Phone Number -->
                <div class="mb-3">
                  <label class="form-label">Phone Number</label>
                  <input
                    type="tel"
                    class="form-control"
                    name="phone"
                    placeholder="Enter phone number"
                    pattern="[0-9]{10}"
                    required
                  />
                  <div class="form-text">Enter a 10-digit phone number (numbers only).
                </div>

                <!-- Password -->
                <div class="mb-3">
                  <label class="form-label">Password</label>
                  <input
                    type="password"
                    class="form-control"
                    name="password"
                    placeholder="Password"
                    minlength="6"
                    required
                  />
                  <div class="form-text">Use at least 6 characters.</div>
                </div>

                <!-- Confirm Password (optional but good) -->
                <div class="mb-3">
                  <label class="form-label">Confirm password</label>
                  <input
                    type="password"
                    class="form-control"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                  />
                </div>

                <!-- Terms checkbox -->
                <div class="form-check mb-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="terms"
                    required
                  />
                  <label class="form-check-label" for="terms">
                    I agree to the rules of the Awesome Site
                  </label>
                </div>

                <!-- Submit -->
                <input type="submit" class="btn btn-primary w-100" value="Create Account">

                <p class="text-center mt-3 mb-0">
                  Already have an account?
                  <a href="login.html">Log in</a>
                </p>
              </form>
              <!-- End Form -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
