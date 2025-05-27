<?php
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "Yesuijin", "bookstore");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, user_name, password FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                header("Location: index_113021192.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found with that email.";
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - Book Store</title>
  <link rel="stylesheet" href="styles_113021192.css" />
  <style>
    /* Styling similar to your register page */
    .login-container {
      max-width: 400px;
      background: white;
      margin: 60px auto;
      padding: 40px 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    .login-container h2 {
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
      background-color: #ffe6e6;
      color: #b30000;
    }
    .btn-submit {
      width: 100%;
      padding: 14px;
      background-color: #3498db;
      color: white;
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

  <div class="login-container">
    <h2>Login to Your Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="form-message">
        <?php foreach ($errors as $error): ?>
          <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login_113021192.php" novalidate>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" class="btn-submit">Login</button>
    </form>

    <div class="form-footer">
      <p>Don't have an account? <a href="register_113021192.php">Register here</a></p>
    </div>
  </div>

  <footer class="footer-clean">
    <div class="container">
      <p>© 2025 <strong>Book Store</strong>. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>
