<?php
session_start();
// Optionally, include your database/config file if needed:
// require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FoodieHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      background-color: #0e1126;
      color: white;
    }
    .navbar {
      background-color: #0e1126;
    }
    .btn-purple {
      background-color: #7b2cbf;
      color: white;
    }
    .hero {
      background: url('https://source.unsplash.com/1200x500/?food,cuisine') no-repeat center center/cover;
      padding: 100px 0;
      text-align: center;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
    }
    .hero p {
      font-size: 1.2rem;
    }
    .cuisine-img,
    .dish-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }
    .cuisine-img:hover,
    .dish-img:hover {
      transform: scale(1.05);
    }
    .card {
      background-color: #1a1c3b;
      color: white;
      border: none;
      position: relative;
      height: 100%;
    }
    .card .btn {
      background-color: #7b2cbf;
      color: white;
    }
    .section-title {
      font-size: 1.8rem;
      font-weight: 600;
      margin-top: 50px;
      margin-bottom: 20px;
    }
    .rating-badge {
      background: #21244c;
      padding: 5px 10px;
      border-radius: 12px;
      font-size: 0.8rem;
    }
    .favorite-icon {
      position: absolute;
      top: 15px;
      right: 15px;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .favorite-icon.liked {
      color: #ff4d6d;
    }
    /* Adjust profile icon styling */
    .navbar .profile-icon {
      font-size: 1.5rem;
      color: white;
    }
    .navbar-nav .nav-link {
      margin-left: 10px;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">🍴 FoodieHub</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Homepage</a></li>
          <li class="nav-item"><a class="nav-link" href="#cuisines-section">Cuisines</a></li>
          <!-- If user is logged in, link to profile page, else direct to auth page -->
          <li class="nav-item">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
              <a class="nav-link profile-icon" href="profile.php" title="Profile">
                <i class="fas fa-user-circle"></i>
              </a>
            <?php else: ?>
              <a class="nav-link profile-icon" href="auth.php?action=login" title="Login">
                <i class="fas fa-user-circle"></i>
              </a>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero text-white">
    <div class="container">
      <h1>Discover World Cuisines</h1>
      <p>Explore authentic recipes from around the globe</p>
      <div class="mt-4">
        <a href="#cuisines-section" class="btn btn-purple me-2">Browse Recipes</a>
        <a href="#" class="btn btn-outline-light">Share Recipe</a>
      </div>
    </div>
  </section>

  <!-- Explore Cuisines -->
  <section class="container text-white" id="cuisines-section">
    <div class="section-title">Explore Cuisines</div>
    <div class="row g-4">
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/indian1.jpeg" class="cuisine-img" alt="Indian">
        <p class="text-center mt-2">Indian</p>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/italian cuisine .avif" class="cuisine-img" alt="Italian">
        <p class="text-center mt-2">Italian</p>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/chineese1.webp" class="cuisine-img" alt="Chinese">
        <p class="text-center mt-2">Chinese</p>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/american.jpg" class="cuisine-img" alt="American">
        <p class="text-center mt-2">American</p>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/mexican1.jpg" class="cuisine-img" alt="Mexican">
        <p class="text-center mt-2">Mexican</p>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <img src="photos/french.avif" class="cuisine-img" alt="French">
        <p class="text-center mt-2">French</p>
      </div>
    </div>
  </section>

  <!-- Trending Now -->
  <section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
      <div class="section-title">Trending Now</div>
      <a href="#" class="text-light">View All</a>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card p-3">
          <i class="fas fa-heart favorite-icon"></i>
          <img src="photos/chole_bhature.jpg" class="dish-img" alt="Chole Bhature">
          <h5 class="mt-3">Chole Bhature</h5>
          <div class="d-flex justify-content-between">
            <span>⏱ 45 mins</span>
            <span class="rating-badge">⭐ 4.8</span>
          </div>
          <a href="chole_bhature.html" class="btn mt-3">View Recipe</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <i class="fas fa-heart favorite-icon"></i>
          <img src="photos/pancake.jpg" class="dish-img" alt="Pancakes">
          <h5 class="mt-3">Nutella Strawberry Pancakes</h5>
          <div class="d-flex justify-content-between">
            <span>⏱ 20 mins</span>
            <span class="rating-badge">⭐ 3.7</span>
          </div>
          <a href="nutella_strawberry.html" class="btn mt-3">View Recipe</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Dishes -->
  <section class="container mt-5 mb-5" id="favorites-section">
    <div class="d-flex justify-content-between align-items-center">
      <div class="section-title">Featured Dishes</div>
      <a href="#" class="text-light">View All</a>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card p-3">
          <i class="fas fa-heart favorite-icon"></i>
          <img src="photos/tiramisu.jpg" class="dish-img" alt="Tiramisu">
          <h5 class="mt-3">Tiramisu</h5>
          <div class="d-flex justify-content-between">
            <span>⏱ 40 mins</span>
            <span class="rating-badge">⭐ 5.0</span>
          </div>
          <a href="tiramisu.html" class="btn mt-3">View Recipe</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <i class="fas fa-heart favorite-icon"></i>
          <img src="photos/PIZZA.jpg" class="dish-img" alt="Pizza">
          <h5 class="mt-3">Pizza</h5>
          <div class="d-flex justify-content-between">
            <span>⏱ 60 mins</span>
            <span class="rating-badge">⭐ 4.6</span>
          </div>
          <a href="pizza.html" class="btn mt-3">View Recipe</a>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.favorite-icon').forEach(icon => {
      icon.addEventListener('click', () => {
        icon.classList.toggle('liked');
      });
    });
  </script>
</body>

</html>
