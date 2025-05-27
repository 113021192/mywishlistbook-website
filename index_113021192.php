<?php
session_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "Yesuijin";
$dbname = "bookstore";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Track visit
$conn->query("INSERT INTO visit () VALUES ()");

// Total visit count
$result = $conn->query("SELECT COUNT(*) AS visit_count FROM visit");
$visit_count = $result->fetch_assoc()['visit_count'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Book Store - Home</title>
  <link rel="stylesheet" href="styles_113021192.css" />
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>

  <!-- Header -->
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

  <!-- Visitor Counter -->
  <div class="visitor-counter-bar container">
    <p><strong>Visitors:</strong> <?php echo $visit_count; ?></p>
  </div>

  <!-- Welcome -->
  <section class="welcome-hero">
    <div class="container">
      <h2>Welcome to <span>Book Store</span></h2>
      <p>Discover your next favorite read. Browse thousands of books and manage your own wishlist.</p>
    </div>
  </section>

  <!-- Map + Calendar Row -->
  <section class="info-section">
    <div class="container info-flex">
      <div class="map-box">
        <h3>Visit Us in Taichung</h3>
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.381388330047!2d120.67364821498132!3d24.140158684395624!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x34693d95f0e8421d%3A0x91f7fd7a4573a78c!2z5Y-w5Lit5biC5rC05YyX5Y2A5ZyL56uL5p2x6Lev!5e0!3m2!1sen!2stw!4v1624008002564!5m2!1sen!2stw"
          allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

      <div class="calendar-box">
        <h3>Upcoming Events</h3>
        <iframe 
          src="https://calendar.google.com/calendar/embed?src=yourcalendarid%40gmail.com&ctz=Asia%2FTaipei">
        </iframe>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-clean">
    <div class="container">
      <p>© 2025 <strong>Book Store</strong>. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>
