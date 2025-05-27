
<?php
session_start();
$conn = new mysqli("localhost", "root", "Yesuijin", "bookstore");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
$success = "";

// Check if user is admin
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['user_id'] == 4;

// Handle Insert Book (admin only)
if (isset($_POST['action']) && $_POST['action'] === 'insert' && $isAdmin) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    if (!$title || !$author) {
        $errors[] = "Title and Author are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO book (title, author, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $author, $description, $category);
        if ($stmt->execute()) {
            $success = "Book inserted successfully.";
        } else {
            $errors[] = "Failed to insert book: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Update Book (admin only)
if (isset($_POST['action']) && $_POST['action'] === 'update' && $isAdmin) {
    $book_id = intval($_POST['book_id']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    if (!$title || !$author) {
        $errors[] = "Title and Author are required for update.";
    } else {
        $stmt = $conn->prepare("UPDATE book SET title = ?, author = ?, description = ?, category = ? WHERE book_id = ?");
        $stmt->bind_param("ssssi", $title, $author, $description, $category, $book_id);
        if ($stmt->execute()) {
            $success = "Book updated successfully.";
        } else {
            $errors[] = "Failed to update book: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Delete Book (admin only)
if (isset($_POST['action']) && $_POST['action'] === 'delete' && $isAdmin) {
    $book_id = intval($_POST['book_id']);
    $stmt = $conn->prepare("DELETE FROM book WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    if ($stmt->execute()) {
        $success = "Book deleted successfully.";
    } else {
        $errors[] = "Failed to delete book: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Add to Wishlist (user must be logged in)
if (isset($_POST['action']) && $_POST['action'] === 'add_wishlist' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $book_id = intval($_POST['book_id']);

    // Check if already in wishlist
    $check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Book already in your wishlist.";
    } else {
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $book_id);
        if ($insert->execute()) {
            $success = "Book added to your wishlist.";
        } else {
            $errors[] = "Failed to add book to wishlist.";
        }
        $insert->close();
    }
    $check->close();
}

// Handle Search
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
}

if ($searchTerm) {
    $stmt = $conn->prepare("SELECT * FROM book WHERE title LIKE ? ORDER BY title ASC");
    $likeTerm = "%$searchTerm%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $books_result = $stmt->get_result();
} else {
    $books_result = $conn->query("SELECT * FROM book ORDER BY title ASC");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Browse Books - Book Store</title>
<link rel="stylesheet" href="styles_113021192.css" />
<style>
  .container { max-width: 1100px; margin: auto; padding: 20px; }
  .book-list { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
    gap: 24px; 
  }
  .book-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .book-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.18);
  }
  .book-title {
    font-weight: 700;
    font-size: 1.3em;
    margin-bottom: 6px;
    color: #222;
  }
  .book-author {
    font-style: italic;
    margin-bottom: 8px;
    color: #555;
  }
  .book-category {
    font-size: 0.9em;
    font-weight: 600;
    color: #8e44ad;
    margin-bottom: 12px;
  }
  .book-description {
    flex-grow: 1;
    margin-bottom: 18px;
    color: #444;
    white-space: pre-wrap;
  }
  .btn {
    background-color: #3498db;
    border: none;
    color: white;
    padding: 12px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
    margin-right: 10px;
  }
  .btn:hover { background-color: #2980b9; }
  .btn-delete {
    background-color: #e74c3c;
  }
  .btn-delete:hover {
    background-color: #c0392b;
  }
  .admin-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 40px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  }
  .admin-form input, .admin-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1em;
    font-family: inherit;
  }
  .admin-form textarea {
    resize: vertical;
  }
  .message {
    margin-bottom: 25px;
    padding: 15px;
    border-radius: 6px;
  }
  .error { background-color: #ffe6e6; color: #b30000; }
  .success { background-color: #e6ffe6; color: #006600; }

  /* Search box */
  .search-form {
    margin-bottom: 30px;
    display: flex;
    max-width: 400px;
  }
  .search-input {
    flex-grow: 1;
    padding: 10px 14px;
    font-size: 1em;
    border-radius: 8px 0 0 8px;
    border: 1px solid #ccc;
    border-right: none;
    outline-offset: 2px;
  }
  .search-button {
    background-color: #3498db;
    border: none;
    color: white;
    padding: 11px 18px;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }
  .search-button:hover {
    background-color: #2980b9;
  }

  /* Wishlist & Admin actions container */
  .action-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  details {
    margin-top: 10px;
  }
  summary {
    cursor: pointer; 
    font-weight: bold;
    outline: none;
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

<div class="container">
  <h2>Browse Books</h2>

  <!-- Search form -->
  <form method="GET" action="books_113021192.php" class="search-form" role="search" aria-label="Search Books">
    <input 
      type="search" 
      name="search" 
      placeholder="Search books by title..." 
      value="<?php echo htmlspecialchars($searchTerm); ?>" 
      class="search-input" 
      aria-label="Search books by title"
      />
    <button type="submit" class="search-button">Search</button>
  </form>

  <?php if ($errors): ?>
    <div class="message error" role="alert">
      <?php foreach ($errors as $error) echo "<p>" . htmlspecialchars($error) . "</p>"; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="message success" role="alert">
      <p><?php echo htmlspecialchars($success); ?></p>
    </div>
  <?php endif; ?>

  <?php if ($isAdmin): ?>
    <!-- Admin Insert Book Form -->
    <div class="admin-form" aria-label="Add new book form">
      <h3>Add New Book</h3>
      <form method="POST" action="books_113021192.php">
        <input type="hidden" name="action" value="insert" />
        <input type="text" name="title" placeholder="Book Title" required />
        <input type="text" name="author" placeholder="Author" required />
        <input type="text" name="category" placeholder="Category" />
        <textarea name="description" rows="3" placeholder="Description"></textarea>
        <button type="submit" class="btn">Add Book</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="book-list" aria-live="polite" aria-relevant="additions removals">
    <?php if ($books_result && $books_result->num_rows > 0): ?>
      <?php while($book = $books_result->fetch_assoc()): ?>
        <div class="book-card" role="region" aria-label="Book: <?php echo htmlspecialchars($book['title']); ?>">
          <div>
            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
            <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
            <?php if (!empty($book['category'])): ?>
              <div class="book-category"><?php echo htmlspecialchars($book['category']); ?></div>
            <?php endif; ?>
            <div class="book-description"><?php echo nl2br(htmlspecialchars($book['description'])); ?></div>
          </div>

          <div class="action-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
              <!-- Add to wishlist form -->
              <form method="POST" style="display:inline;" action="books_113021192.php" aria-label="Add <?php echo htmlspecialchars($book['title']); ?> to wishlist">
                <input type="hidden" name="action" value="add_wishlist" />
                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>" />
                <button type="submit" class="btn">Add to Wishlist</button>
              </form>
            <?php else: ?>
              <small><a href="login_113021192.php">Login</a> to add to wishlist</small>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
              <!-- Admin Update/Delete forms -->
              <details>
                <summary>Admin Actions</summary>
                <form method="POST" style="margin-top: 10px;" action="books_113021192.php" aria-label="Update book <?php echo htmlspecialchars($book['title']); ?>">
                  <input type="hidden" name="action" value="update" />
                  <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>" />
                  <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required />
                  <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required />
                  <input type="text" name="category" value="<?php echo htmlspecialchars($book['category']); ?>" placeholder="Category" />
                  <textarea name="description" rows="2"><?php echo htmlspecialchars($book['description']); ?></textarea>
                  <button type="submit" class="btn" style="margin-top:5px;">Update Book</button>
                </form>
                <form method="POST" style="margin-top: 10px;" action="books_113021192.php" onsubmit="return confirm('Are you sure you want to delete this book?');" aria-label="Delete book <?php echo htmlspecialchars($book['title']); ?>">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>" />
                  <button type="submit" class="btn btn-delete">Delete Book</button>
                </form>
              </details>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No books found<?php echo $searchTerm ? " matching \"" . htmlspecialchars($searchTerm) . "\"" : ""; ?>.</p>
    <?php endif; ?>
  </div>
</div>

<footer class="footer-clean">
  <div class="container">
    <p>© 2025 <strong>Book Store</strong>. All rights reserved.</p>
  </div>
</footer>

</body>
</html>

<?php
$conn->close();
?>
