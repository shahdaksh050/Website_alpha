<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?action=login");
    exit();
}

$user_id = $_SESSION['user_id'];
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Fetch user data
$user = [];
$stmt = $mysqli->prepare("
    SELECT username, email, address
    FROM users 
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch dietary preferences
$diet = [
    'vegetarian'  => 0,
    'spice_level' => 'Medium',
    'allergies'   => 'None'
];
$stmt = $mysqli->prepare("
    SELECT vegetarian, spice_level, allergies 
    FROM dietary_preferences 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $diet = $result->fetch_assoc();
}
$stmt->close();

// Fetch favorites
$favorites = [];
$stmt = $mysqli->prepare("
    SELECT r.title 
    FROM favorites f
    JOIN recipes r ON f.recipe_id = r.id
    WHERE f.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row['title'];
}
$stmt->close();

// Fetch orders
$orders = [];
$stmt = $mysqli->prepare("
    SELECT id, order_date, total
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($order = $result->fetch_assoc()) {
    // Fetch order items
    $items = [];
    $stmt_items = $mysqli->prepare("
        SELECT r.title, oi.price
        FROM order_items oi
        JOIN recipes r ON oi.recipe_id = r.id
        WHERE oi.order_id = ?
    ");
    $stmt_items->bind_param("i", $order['id']);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    $orders[]       = $order;
    $stmt_items->close();
}

$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FoodieHub Profile</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <style>
    /* Global Styles */
    body {
      background-color: #0f172a; /* Dark background */
      color: #ffffff;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }
    a, a:hover {
      text-decoration: none;
      color: #e2e8f0;
    }
    /* Navbar */
    .navbar-dark .navbar-brand {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
    }
    .navbar-brand span {
      color: #6366f1; /* Purple accent color */
    }
    .nav-link {
      color: #cbd5e1 !important;
      font-weight: 500;
    }
    .nav-link:hover {
      color: #ffffff !important;
    }
    /* Profile Card */
    .profile-card {
      background-color: #1e293b;
      border: none;
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .profile-card .avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 1rem;
    }
    .profile-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }
    .profile-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .profile-details {
      display: flex;
      flex-direction: column;
    }
    .profile-details h4 {
      margin: 0;
      font-weight: 700;
      color: #ffffff;
    }
    .profile-details p {
      margin: 0;
      font-size: 0.95rem;
      color: #cbd5e1;
    }
    .btn-edit {
      background-color: #6366f1;
      color: #ffffff;
      font-weight: 500;
      border: none;
    }
    .btn-edit:hover {
      background-color: #4f51c0;
    }
    /* Common Card Styling */
    .dark-card {
      background-color: #1e293b;
      border: none;
      border-radius: 0.5rem;
      padding: 1.5rem;
      color: #cbd5e1;
    }
    .dark-card h5 {
      margin-bottom: 1rem;
      font-weight: 600;
      color: #ffffff;
    }
    /* Enhanced Order History Styling */
    .order-history-container {
      margin-top: 1rem;
    }
    .order-card {
      background-color: #1e293b;
      border: 1px solid #374151;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
    }
    .order-card .card-header {
      background-color: #111827;
      border-bottom: 1px solid #374151;
      color: #e2e8f0;
      font-weight: 600;
    }
    .order-card .card-body {
      color: #e2e8f0;
    }
    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem 0;
      border-bottom: 1px solid #374151;
    }
    .order-item:last-child {
      border-bottom: none;
    }
    .order-date {
      font-size: 0.9rem;
      color: #9ca3af;
    }
    /* Edit Preferences Button at Bottom */
    .btn-pref {
      background-color: #6366f1;
      color: #ffffff;
      font-weight: 500;
      border: none;
      padding: 0.75rem 1.25rem;
      border-radius: 0.5rem;
      transition: background-color 0.2s ease-in-out;
    }
    .btn-pref:hover {
      background-color: #4f51c0;
    }
    /* Adjust spacing in the bottom section */
    .bottom-section {
      margin-bottom: 4rem;
    }
    /* Responsive Tweaks */
    @media (max-width: 576px) {
      .profile-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .profile-info {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-dark navbar-expand-lg" style="background-color: #0f172a;">
    <div class="container">
      <a class="navbar-brand" href="#">
        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: #6366f1;"></div>
        <span>FoodieHub</span>
      </a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="homepage.php">Homepage</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="homepage.php">Cuisines</a>
          </li>
          <li class="nav-item">
    <a class="nav-link" href="logout.php">Logout</a>
  </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Container -->
  <div class="container py-4">
    <!-- Profile Card -->
    <div class="card profile-card">
      <div class="profile-header">
        <div class="profile-info">
          <!-- User Avatar -->
          <img
            src="https://via.placeholder.com/150x150"
            alt="Profile Image"
            class="avatar"
          />
          <!-- User Details -->
          <div class="profile-details">
            <h4><?= htmlspecialchars($user['username'] ?? 'N/A') ?></h4>
            <p>
              <?= htmlspecialchars($user['email'] ?? 'N/A') ?><br/>
              <?= htmlspecialchars($user['address'] ?? 'N/A') ?>
            </p>
          </div>
        </div>
        <!-- Edit Profile Button -->
        <!-- You can change this to an anchor if needed -->
        <button class="btn btn-edit" onclick="window.location.href='edit-profile.php'">
          Edit Profile
        </button>
      </div>
    </div>

    <!-- Row of Two Cards: Dietary Preferences & Favorite Items -->
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="dark-card h-100">
          <h5>Dietary Preferences</h5>
          <p class="mb-1">
            <strong>Diet Type:</strong> 
            <?= ($diet['vegetarian'] ?? 0) ? 'Vegetarian' : 'Non-vegetarian' ?>
          </p>
          <p class="mb-1">
            <strong>Spice Level:</strong> 
            <?= htmlspecialchars($diet['spice_level'] ?? 'Medium') ?>
          </p>
          <p class="mb-0">
            <strong>Allergies:</strong> 
            <?= htmlspecialchars($diet['allergies'] ?? 'None') ?>
          </p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="dark-card h-100">
          <h5>Favorite Items</h5>
          <?php if (!empty($favorites)): ?>
            <?php 
              // For spacing consistency, let's show each favorite item in its own paragraph.
              $i = 0;
              foreach ($favorites as $fav):
            ?>
              <p class="<?= $i < count($favorites) - 1 ? 'mb-2' : 'mb-0' ?>">
                <?= htmlspecialchars($fav) ?>
              </p>
              <?php $i++; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="mb-0">No favorite items</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- New Row for "Edit Preferences & Favorites" Button -->
    <div class="mb-4 text-center">
      <!-- Adjust link if needed (e.g., "edit-preferences.php") -->
      <a href="edit-preferences.php" class="btn-pref">
        Edit Preferences &amp; Favorites
      </a>
    </div>

    <!-- Order History Section -->
    <div class="bottom-section">
      <div class="dark-card">
        <h5>Order History</h5>
        <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
            <div class="card order-card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span>Order #<?= htmlspecialchars($order['id']) ?></span>
                <span class="order-date">
                  <?= date('Y-m-d', strtotime($order['order_date'])) ?>
                </span>
              </div>
              <div class="card-body">
                <?php foreach ($order['items'] as $item): ?>
                  <div class="order-item">
                    <span><?= htmlspecialchars($item['title']) ?></span>
                    <span>$<?= number_format($item['price'], 2) ?></span>
                  </div>
                <?php endforeach; ?>
                <!-- If you want to display the total -->
                <?php if (isset($order['total'])): ?>
                  <div class="order-item pt-2">
                    <span><strong>Total</strong></span>
                    <span><strong>$<?= number_format($order['total'], 2) ?></strong></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="mt-3">No orders found</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (Optional for interactivity) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
