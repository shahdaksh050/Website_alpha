<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?action=login");
    exit();
}

$user_id = $_SESSION['user_id'];

$successMsg = "";
$errorMsg   = "";

// Process the form submission if POST request is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and trim form inputs
    // For radio inputs, we assume values "1" for yes and "0" for no.
    $vegetarian       = isset($_POST['vegetarian']) ? intval($_POST['vegetarian']) : 0;
    $lactoseIntolerant = isset($_POST['lactose_intolerant']) ? intval($_POST['lactose_intolerant']) : 0;
    $allergies        = trim($_POST['allergies'] ?? '');
    $spiceLevel       = trim($_POST['spiceLevel'] ?? '');

    // Basic validation
    if ($spiceLevel === "") {
        $errorMsg = "Please select a spice level.";
    } else {
        $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($mysqli->connect_error) {
            $errorMsg = "Database connection failed: " . $mysqli->connect_error;
        } else {
            // Check if preferences already exist for this user
            $stmt_check = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM dietary_preferences WHERE user_id = ?");
            $stmt_check->bind_param("i", $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();
            $stmt_check->close();

            if ($row_check['cnt'] > 0) {
                // Update the existing record
                $stmt = $mysqli->prepare("UPDATE dietary_preferences SET vegetarian = ?, lactose_intolerant = ?, allergies = ?, spice_level = ? WHERE user_id = ?");
                $stmt->bind_param("iissi", $vegetarian, $lactoseIntolerant, $allergies, $spiceLevel, $user_id);
            } else {
                // Insert a new record if none exists for this user
                $stmt = $mysqli->prepare("INSERT INTO dietary_preferences (user_id, vegetarian, lactose_intolerant, allergies, spice_level) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiss", $user_id, $vegetarian, $lactoseIntolerant, $allergies, $spiceLevel);
            }

            if ($stmt) {
                if ($stmt->execute()) {
                    $successMsg = "Preferences updated successfully.";
                } else {
                    $errorMsg = "Failed to update preferences.";
                }
                $stmt->close();
            } else {
                $errorMsg = "Database error: " . $mysqli->error;
            }
            $mysqli->close();
        }
    }
}

// Retrieve the current dietary preferences for the logged-in user
// Use defaults if no record exists
$dietPreferences = [
    'vegetarian'        => 0,
    'lactose_intolerant'=> 0,
    'allergies'         => "",
    'spice_level'       => "Medium",
];
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$mysqli->connect_error) {
    $stmt = $mysqli->prepare("SELECT vegetarian, lactose_intolerant, allergies, spice_level FROM dietary_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $dietPreferences = $row;
    }
    $stmt->close();
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Preferences - FoodieHub</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Global Styles */
    body {
      background-color: #0f172a;
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
      color: #6366f1;
    }
    .nav-link {
      color: #cbd5e1 !important;
      font-weight: 500;
    }
    .nav-link:hover {
      color: #ffffff !important;
    }
    /* Section Card */
    .section-card {
      background-color: #1e293b;
      border: none;
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .section-header {
      font-size: 1.75rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #ffffff;
    }
    /* Custom Button */
    .btn-custom {
      background-color: #6366f1;
      color: #ffffff;
      border: none;
      font-weight: 500;
      transition: background-color 0.2s ease-in-out;
      padding: 0.75rem 1.25rem;
      border-radius: 0.5rem;
    }
    .btn-custom:hover {
      background-color: #4f51c0;
    }
    /* Message Styles */
    .message {
      margin-bottom: 1rem;
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
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">Homepage</a></li>
          <li class="nav-item"><a class="nav-link" href="cuisines.php">Cuisines</a></li>
        </ul>
      </div>
    </div>
  </nav>
  
  <!-- Main Container -->
  <div class="container py-5">
    <!-- Page Heading -->
    <header class="mb-5 text-center">
      <h1>Edit Preferences</h1>
      <p class="lead">Manage your dietary preferences, favorite items and view your order history.</p>
    </header>
    
    <!-- Section: Dietary Preferences -->
    <div class="section-card">
      <div class="section-header">Dietary Preferences</div>
      <!-- Display success or error messages -->
      <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success message"><?= htmlspecialchars($successMsg) ?></div>
      <?php endif; ?>
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger message"><?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>
      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <!-- Vegetarian -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Are you Vegetarian?</label>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="vegetarian"
              id="vegYes"
              value="1"
              <?= ($dietPreferences['vegetarian'] == 1) ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="vegYes">
              Yes
            </label>
          </div>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="vegetarian"
              id="vegNo"
              value="0"
              <?= ($dietPreferences['vegetarian'] == 0) ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="vegNo">
              No
            </label>
          </div>
        </div>
        <!-- Lactose Intolerance -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Are you Lactose Intolerant?</label>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="lactose_intolerant"
              id="lactoseYes"
              value="1"
              <?= ($dietPreferences['lactose_intolerant'] == 1) ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="lactoseYes">
              Yes
            </label>
          </div>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="lactose_intolerant"
              id="lactoseNo"
              value="0"
              <?= ($dietPreferences['lactose_intolerant'] == 0) ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="lactoseNo">
              No
            </label>
          </div>
        </div>
        <!-- Allergies -->
        <div class="mb-4">
          <label for="allergies" class="form-label fw-semibold">Allergies</label>
          <textarea
            class="form-control"
            id="allergies"
            name="allergies"
            rows="3"
          ><?= htmlspecialchars($dietPreferences['allergies']) ?></textarea>
        </div>
        <!-- Spice Level -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Spice Level</label>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="spiceLevel"
              id="spiceLow"
              value="Low"
              <?= ($dietPreferences['spice_level'] === 'Low') ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="spiceLow">
              Low
            </label>
          </div>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="spiceLevel"
              id="spiceMedium"
              value="Medium"
              <?= ($dietPreferences['spice_level'] === 'Medium') ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="spiceMedium">
              Medium
            </label>
          </div>
          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="spiceLevel"
              id="spiceHigh"
              value="High"
              <?= ($dietPreferences['spice_level'] === 'High') ? 'checked' : '' ?>
            />
            <label class="form-check-label" for="spiceHigh">
              High
            </label>
          </div>
        </div>
        <!-- Save Preferences Button -->
        <button type="submit" class="btn btn-custom">Save Preferences</button>
      </form>
    </div>

    <!-- Section: Favorite Items -->
    <div class="section-card">
      <div class="section-header">Favorite Items</div>
      <div class="row g-3">
        <!-- Example Favorite Item #1 -->
        <div class="col-md-6">
          <div class="card favorite-card">
            <div class="card-body">
              <h5 class="card-title">Butter Chicken</h5>
              <p class="card-text">$14.99</p>
            </div>
          </div>
        </div>
        <!-- Example Favorite Item #2 -->
        <div class="col-md-6">
          <div class="card favorite-card">
            <div class="card-body">
              <h5 class="card-title">Vegetable Biryani</h5>
              <p class="card-text">$12.99</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section: Order History -->
    <div class="section-card">
      <div class="section-header">Order History</div>
      <div class="order-history-container">
        <!-- Order Card #1 -->
        <div class="card order-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Order #1</span>
            <span class="order-date">2024-01-10</span>
          </div>
          <div class="card-body">
            <div class="order-item">
              <span>Butter Chicken</span>
              <span>$24.99</span>
            </div>
            <div class="order-item">
              <span>Raita</span>
              <span>$2.99</span>
            </div>
          </div>
        </div>
        <!-- Order Card #2 -->
        <div class="card order-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Order #2</span>
            <span class="order-date">2024-01-09</span>
          </div>
          <div class="card-body">
            <div class="order-item">
              <span>Vegetable Biryani</span>
              <span>$18.50</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>

  <!-- Bootstrap JS (Optional for interactivity) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
