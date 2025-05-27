<?php
session_start();
$conn = new mysqli("localhost", "root", "Yesuijin", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_113021192.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = "";
$errors = [];

// Fetch logged-in user's name from `user` table, column `user_name`
$username = "";
$stmtUser = $conn->prepare("SELECT user_name FROM user WHERE user_id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$stmtUser->bind_result($username);
$stmtUser->fetch();
$stmtUser->close();

// Handle removal from wishlist
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_book_id'])) {
    $book_id = intval($_POST['remove_book_id']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    if ($stmt->execute()) {
        $success = "Book removed from wishlist.";
    } else {
        $errors[] = "Failed to remove book.";
    }
    $stmt->close();
}

// Fetch wishlist books
$query = "
    SELECT b.book_id, b.title, b.author, b.description
    FROM wishlist w
    JOIN book b ON w.book_id = b.book_id
    WHERE w.user_id = ?
    ORDER BY b.title ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_books = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Wishlist - Book Store</title>
  <link rel="stylesheet" href="styles_113021192.css" />
  <style>
    .wishlist-container {
      max-width: 1100px;
      margin: auto;
      padding: 40px 20px;
    }
    .book-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
    }
    .book-title {
      font-size: 1.3em;
      font-weight: bold;
    }
    .book-author {
      font-style: italic;
      color: #666;
    }
    .book-description {
      margin-top: 10px;
      color: #444;
    }
    .btn {
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background-color: #c0392b;
    }
    .message {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
    }
    .success {
      background-color: #e6ffed;
      color: #006600;
    }
    .error {
      background-color: #ffe6e6;
      color: #b30000;
    }
    .browse-link {
      display: block;
      margin-top: 30px;
      text-align: center;
    }
    .browse-link a {
      background-color: #3498db;
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      border-radius: 6px;
      font-weight: 600;
    }
    .browse-link a:hover {
      background-color: #2980b9;
    }
    /* Remove welcome from header, so no style needed there */
  </style>
</head>
<body>

<!-- Header -->
<header class="site-header" style="background-color:#34495e; padding:15px 0;">
  <div class="container header-flex" style="max-width:1100px; margin:auto; display:flex; align-items:center; justify-content:space-between;">
    <h1 class="logo" style="margin:0;">
      <a href="index.php" style="color:#fff; text-decoration:none;">Book Store</a>
    </h1>

    <nav>
      <ul
        class="nav-links"
        style="
          list-style: none;
          display: flex;
          gap: 20px;
          margin: 0;
          padding: 0;
          color: #fff;
        "
      >
        <li><a href="index_113021192.php" style="color:#fff; text-decoration:none;">Home</a></li>

        <?php if (!isset($_SESSION['user_id'])): ?>
          <li><a href="register_113021192.php" style="color:#fff; text-decoration:none;">Register</a></li>
          <li><a href="login_113021192.php" style="color:#fff; text-decoration:none;">Login</a></li>
        <?php endif; ?>

        <li><a href="books_113021192.php" style="color:#fff; text-decoration:none;">Browse Books</a></li>
        <li><a href="wishlist_113021192.php" style="color:#fff; text-decoration:none;">Wishlist</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="logout.php" style="color:#fff; text-decoration:none;">Logout</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<!-- Wishlist Content -->
<div class="wishlist-container">
  <h2>Your Wishlist</h2>

  <?php if (!empty($username)): ?>
    <p style="font-weight:bold; font-size:1.2em; margin-bottom: 20px;">
      Welcome, <?php echo htmlspecialchars($username); ?>!
    </p>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="message error">
      <?php foreach ($errors as $error): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($wishlist_books->num_rows > 0): ?>
    <?php while ($book = $wishlist_books->fetch_assoc()): ?>
      <div class="book-card">
        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
        <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
        <div class="book-description"><?php echo nl2br(htmlspecialchars($book['description'])); ?></div>
        <form method="POST" onsubmit="return confirm('Remove this book from your wishlist?');" style="margin-top: 15px;">
          <input type="hidden" name="remove_book_id" value="<?php echo $book['book_id']; ?>">
          <button type="submit" class="btn">Remove from Wishlist</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>You haven’t added any books to your wishlist yet.</p>
  <?php endif; ?>

  <div class="browse-link">
    <a href="books_113021192.php">Browse More Books</a>
  </div>
</div>

<!-- Footer -->
<footer
  class="footer-clean"
  style="background:#f2f2f2; padding:20px 0; text-align:center; margin-top: 50px;"
>
  <div class="container" style="max-width:1100px; margin:auto;">
    <p>© 2025 <strong>Book Store</strong>. All rights reserved.</p>
  </div>
</footer>

</body>
</html>

<?php $conn->close(); ?>
