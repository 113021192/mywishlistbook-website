<?php
session_start();

$errors = [];
$success = "";
$name = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "Yesuijin", "bookstore");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $name = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        $checkEmail = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (user_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            if ($stmt->execute()) {
                $success = "Registration successful! <a href='login_113021192.php'>Login now</a>.";
                $name = $email = ""; // Clear form values
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $checkEmail->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - Book Store</title>
  <link rel="stylesheet" href="styles_113021192.css" />
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <style>
    /* Register form styles (matches your previous form styling) */
    .register-container {
      max-width: 450px;
      background: white;
      margin: 40px auto 80px auto;
      padding: 40px 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    .register-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
      font-size: 26px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }
    .form-group input {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background-color: #f9f9f9;
    }
    .form-group input:focus {
      border-color: #3498db;
      background-color: #fff;
      outline: none;
    }
    .form-message {
      margin-bottom: 15px;
      padding: 12px;
      border-radius: 5px;
      font-size: 15px;
    }
    .form-message.error {
      background-color: #ffe6e6;
      color: #b30000;
    }
    .form-message.success {
      background-color: #e6ffed;
      color: #006600;
    }
    .btn-submit {
      width: 100%;
      padding: 14px;
      background-color: #3498db;
      color: #fff;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .btn-submit:hover {
      background-color: #2980b9;
    }
    .form-footer {
      text-align: center;
      margin-top: 15px;
    }
    .form-footer a {
      color: #3498db;
      text-decoration: none;
    }
    .form-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Header (same as index.php) -->
  <header class="site-header">
    <div class="container header-flex">
      <h1 class="logo"><a href="index.php">Book Store</a></h1>
      <nav>
        <ul class="nav-links">
          <li><a href="index_113021192.php">Home</a></li>

          <?php if (!isset($_SESSION['user_id'])): ?>
            <li><a href="register_113021192.php">Register</a></li>
            <li><a href="login_113021192.php">Login</a></li>
          <?php endif; ?>

          <li><a href="books_113021192.php">Browse Books</a></li>
          <li><a href="wishlist_113021192.php">Wishlist</a></li>

          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Logout</a></li>
          <?php endif; ?>
        </ul>    
      </nav>
    </div>
  </header>

  <div class="register-container">
    <h2>Create Your Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="form-message error">
        <?php foreach ($errors as $error): ?>
          <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="form-message success">
        <p><?php echo $success; ?></p>
      </div>
    <?php endif; ?>

    <form method="POST" action="register_113021192.php">
      <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($name); ?>" />
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required />
      </div>

      <button type="submit" class="btn-submit">Register</button>
    </form>

    <div class="form-footer">
      <p>Already have an account? <a href="login_113021192.php">Login here</a></p>
    </div>
  </div>

  <!-- Footer (same as index.php) -->
  <footer class="footer-clean">
    <div class="container">
      <p>© 2025 <strong>Book Store</strong>. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>
