<?php
  session_start();
  include('connection.php');
  include('functions.php');

  $user_data = check_login($con);
  $errors = [];
  $successMessage = '';

  // Create the marketplace table if it does not already exist.
  // This keeps the marketplace usable even if the SQL file has not been run yet.
  $createMarketplaceTable = "CREATE TABLE IF NOT EXISTS marketplace_items (
      itemID INT NOT NULL AUTO_INCREMENT,
      sellerID INT NOT NULL,
      title VARCHAR(100) NOT NULL,
      description TEXT NOT NULL,
      price DECIMAL(10,2) NOT NULL,
      category VARCHAR(50) NOT NULL DEFAULT 'General',
      itemCondition VARCHAR(30) NOT NULL DEFAULT 'Used',
      imageData LONGBLOB NULL,
      isSold TINYINT(1) NOT NULL DEFAULT 0,
      createdOn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updatedOn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (itemID),
      INDEX idx_sellerID (sellerID)
  )";
  mysqli_query($con, $createMarketplaceTable);

  function clean_marketplace_input($value){
      return trim($value ?? '');
  }

  function marketplace_error($message){
      return '<div class="alert alert-danger" role="alert">'.htmlspecialchars($message).'</div>';
  }

  function user_can_change_item($user_data, $item){
      if(!$user_data || !$item){
          return false;
      }

      $isSeller = (int)$user_data['userID'] === (int)$item['sellerID'];
      $isAdmin = isset($user_data['isAdmin']) && $user_data['isAdmin'] !== null;

      return $isSeller || $isAdmin;
  }

  function get_marketplace_item($con, $itemID){
      $query = "SELECT marketplace_items.*, `user`.username
                FROM marketplace_items
                JOIN `user` ON `user`.userID = marketplace_items.sellerID
                WHERE marketplace_items.itemID = ?
                LIMIT 1";
      $statement = mysqli_prepare($con, $query);
      mysqli_stmt_bind_param($statement, 'i', $itemID);
      mysqli_stmt_execute($statement);
      $result = mysqli_stmt_get_result($statement);

      if($result && mysqli_num_rows($result) > 0){
          return mysqli_fetch_assoc($result);
      }

      return null;
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST'){
      if(!$user_data){
          $errors[] = 'You must be logged in to manage marketplace listings.';
      }else{
          $action = $_POST['action'] ?? '';

          if($action === 'create'){
              $title = clean_marketplace_input($_POST['title'] ?? '');
              $description = clean_marketplace_input($_POST['description'] ?? '');
              $price = clean_marketplace_input($_POST['price'] ?? '');
              $category = clean_marketplace_input($_POST['category'] ?? 'General');
              $itemCondition = clean_marketplace_input($_POST['itemCondition'] ?? 'Used');
              $image = null;
              if(isset($_FILES['imageData']) && $_FILES['imageData']['error'] === UPLOAD_ERR_OK){
                  $image = file_get_contents($_FILES['imageData']['tmp_name']);
              }

              if($title === ''){
                  $errors[] = 'Please enter a listing title.';
              }

              if($description === ''){
                  $errors[] = 'Please enter a listing description.';
              }

              if($price === '' || !is_numeric($price) || (float)$price < 0){
                  $errors[] = 'Please enter a valid price.';
              }

              if($category === ''){
                  $category = 'General';
              }

              if($itemCondition === ''){
                  $itemCondition = 'Used';
              }

              if(empty($errors)){
                  $sellerID = (int)$user_data['userID'];
                  $priceValue = (float)$price;

                  $query = "INSERT INTO marketplace_items 
                            (sellerID, title, description, price, category, itemCondition, imageData)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";

                  $statement = mysqli_prepare($con, $query);

                  mysqli_stmt_bind_param(
                      $statement,
                      'issdsss',
                      $sellerID,
                      $title,
                      $description,
                      $priceValue,
                      $category,
                      $itemCondition,
                      $image
                  );

                  if(mysqli_stmt_execute($statement)){
                      header('Location: marketplace.php?created=1');
                      exit;
                  }else{
                      $errors[] = 'Error creating listing.';
                  }
              }
          }

          if($action === 'delete'){
              $itemID = (int)($_POST['itemID'] ?? 0);
              $item = get_marketplace_item($con, $itemID);

              if(!$item){
                  $errors[] = 'Listing not found.';
              }elseif(!user_can_change_item($user_data, $item)){
                  $errors[] = 'You do not have permission to delete this listing.';
              }else{
                  $query = "DELETE FROM marketplace_items WHERE itemID = ?";
                  $statement = mysqli_prepare($con, $query);
                  mysqli_stmt_bind_param($statement, 'i', $itemID);

                  if(mysqli_stmt_execute($statement)){
                      header('Location: marketplace.php?deleted=1');
                      exit;
                  }else{
                      $errors[] = 'Error deleting listing.';
                  }
              }
          }

          if($action === 'toggle_sold'){
              $itemID = (int)($_POST['itemID'] ?? 0);
              $item = get_marketplace_item($con, $itemID);

              if(!$item){
                  $errors[] = 'Listing not found.';
              }elseif(!user_can_change_item($user_data, $item)){
                  $errors[] = 'You do not have permission to update this listing.';
              }else{
                  $newSoldValue = $item['isSold'] ? 0 : 1;

                  $query = "UPDATE marketplace_items SET isSold = ? WHERE itemID = ?";
                  $statement = mysqli_prepare($con, $query);
                  mysqli_stmt_bind_param($statement, 'ii', $newSoldValue, $itemID);

                  if(mysqli_stmt_execute($statement)){
                      header('Location: marketplace.php?updated=1');
                      exit;
                  }else{
                      $errors[] = 'Error updating listing.';
                  }
              }
          }
      }
  }

  if(isset($_GET['created'])){
      $successMessage = 'Listing created successfully.';
  }elseif(isset($_GET['deleted'])){
      $successMessage = 'Listing deleted successfully.';
  }elseif(isset($_GET['updated'])){
      $successMessage = 'Listing updated successfully.';
  }

  $search = clean_marketplace_input($_GET['search'] ?? '');
  $categoryFilter = clean_marketplace_input($_GET['category'] ?? '');
  $showSold = isset($_GET['showSold']) && $_GET['showSold'] === '1';

  $categoryQuery = "SELECT DISTINCT category FROM marketplace_items ORDER BY category ASC";
  $categoryResult = mysqli_query($con, $categoryQuery);

  $categories = [];

  if($categoryResult){
      while($row = mysqli_fetch_assoc($categoryResult)){
          $categories[] = $row['category'];
      }
  }

  $whereClauses = [];
  $params = [];
  $types = '';

  if(!$showSold){
      $whereClauses[] = 'marketplace_items.isSold = 0';
  }

  if($search !== ''){
      $whereClauses[] = '(marketplace_items.title LIKE ? OR marketplace_items.description LIKE ? OR marketplace_items.category LIKE ?)';
      $searchLike = '%'.$search.'%';

      $params[] = $searchLike;
      $params[] = $searchLike;
      $params[] = $searchLike;

      $types .= 'sss';
  }

  if($categoryFilter !== ''){
      $whereClauses[] = 'marketplace_items.category = ?';
      $params[] = $categoryFilter;

      $types .= 's';
  }

  $marketplaceQuery = "SELECT marketplace_items.*, `user`.username
                       FROM marketplace_items
                       JOIN `user` ON `user`.userID = marketplace_items.sellerID";

  if(!empty($whereClauses)){
      $marketplaceQuery .= ' WHERE '.implode(' AND ', $whereClauses);
  }

  $marketplaceQuery .= " ORDER BY marketplace_items.isSold ASC, marketplace_items.createdOn DESC";

  $statement = mysqli_prepare($con, $marketplaceQuery);

  if(!empty($params)){
      mysqli_stmt_bind_param($statement, $types, ...$params);
  }

  mysqli_stmt_execute($statement);
  $marketplaceResult = mysqli_stmt_get_result($statement);
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Awesome Marketplace</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link 
      rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >

    <style>
      body {
        background-color: #fbbf77;
      }

      .market-hero {
        background-color: #ff5c00;
      }

      .market-hero h1,
      .market-hero p {
        color: blue;
      }

      .market-card {
        background-color: white;
        border: 2px solid #ff5c00;
        border-radius: 12px;
        height: 100%;
      }

      .market-card img {
        height: 180px;
        object-fit: cover;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        background-color: #ffe1ca;
      }

      .market-placeholder {
        height: 180px;
        background-color: #ffe1ca;
        color: blue;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
      }

      .market-badge {
        background-color: #00b7eb;
        color: black;
      }

      .sold-badge {
        background-color: #6c757d;
        color: white;
      }

      .section-box {
        background-color: white;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #ff5c00;
      }

      .btn-orange {
        background-color: #ff5c00;
        color: white;
        border: none;
      }

      .btn-orange:hover {
        background-color: #dc4f00;
        color: white;
      }

      .link-blue {
        color: blue;
      }
    </style>
  </head>

  <body>
    <?php set_header(); ?>

    <div class="p-5 text-white rounded text-center market-hero">
      <h1>The Awesome Marketplace</h1>
      <p>Selling things that are awesome!</p>
    </div>
    <br>

    <div class="container mb-5">
      <?php if($successMessage !== ''): ?>
        <div class="alert alert-success" role="alert">
          <?php echo htmlspecialchars($successMessage); ?>
        </div>
      <?php endif; ?>

      <?php foreach($errors as $error): ?>
        <?php echo marketplace_error($error); ?>
      <?php endforeach; ?>

      <div class="row g-4">
        <div class="col-lg-4">
          <div class="section-box">
            <h3 class="mb-3">Create Listing</h3>

            <?php if($user_data): ?>
              <form method="POST" action="marketplace.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">

                <div class="mb-3">
                  <label class="form-label">
                    Title <span class="text-danger">*</span>
                  </label>
                  <input 
                    type="text" 
                    class="form-control" 
                    name="title" 
                    maxlength="100" 
                    required
                  >
                </div>

                <div class="mb-3">
                  <label class="form-label">
                    Description <span class="text-danger">*</span>
                  </label>
                  <textarea 
                    class="form-control" 
                    name="description" 
                    rows="4" 
                    required
                  ></textarea>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">
                      Price <span class="text-danger">*</span>
                    </label>
                    <input 
                      type="number" 
                      class="form-control" 
                      name="price" 
                      min="0" 
                      step="0.01" 
                      required
                    >
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Condition</label>
                    <select class="form-select" name="itemCondition">
                      <option value="New">New</option>
                      <option value="Like New">Like New</option>
                      <option value="Used" selected>Used</option>
                      <option value="Needs Repair">Needs Repair</option>
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Category</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    name="category" 
                    maxlength="50" 
                    placeholder="Books, Electronics, Furniture..."
                  >
                </div>

                <div class="mb-3">
                  <label class="form-label">Image</label>
                  <input 
                    type="file" 
                    class="form-control" 
                    name="imageData" 
                    accept="image/*"
                  >
                </div>

                <button type="submit" class="btn btn-orange w-100">
                  Post Item
                </button>
              </form>
            <?php else: ?>
              <p>You must be logged in to create a marketplace listing.</p>
              <a href="login.php" class="btn btn-orange">Log In</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="section-box mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
              <div>
                <h3 class="mb-1">Awesome Products</h3>
                <p class="mb-0">Browse items posted by site members.</p>
              </div>
            </div>

            <form method="GET" action="marketplace.php" class="row g-2 mt-3">
              <div class="col-md-5">
                <input 
                  type="text" 
                  class="form-control" 
                  name="search" 
                  placeholder="Search listings" 
                  value="<?php echo htmlspecialchars($search); ?>"
                >
              </div>

              <div class="col-md-4">
                <select class="form-select" name="category">
                  <option value="">All Categories</option>

                  <?php foreach($categories as $category): ?>
                    <option 
                      value="<?php echo htmlspecialchars($category); ?>" 
                      <?php echo $categoryFilter === $category ? 'selected' : ''; ?>
                    >
                      <?php echo htmlspecialchars($category); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                  Filter
                </button>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="showSold" 
                    value="1" 
                    id="showSold" 
                    <?php echo $showSold ? 'checked' : ''; ?>
                  >
                  <label class="form-check-label" for="showSold">
                    Show sold items
                  </label>
                </div>
              </div>
            </form>
          </div>

          <div class="row g-4">
            <?php if($marketplaceResult && mysqli_num_rows($marketplaceResult) > 0): ?>
              <?php while($item = mysqli_fetch_assoc($marketplaceResult)): ?>
                <div class="col-md-6">
                  <div class="market-card shadow-sm">
                    <?php if(!empty($item['ImageData'])): ?>
                      <img 
                          src="image.php?id=<?php echo (int)$item['itemID']; ?>" 
                          class="card-img-top"
                        >
                    <?php else: ?>
                      <div class="market-placeholder">Awesome Item</div>
                    <?php endif; ?>

                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start gap-2">
                        <h4 class="card-title mb-1">
                          <?php echo htmlspecialchars($item['title']); ?>
                        </h4>

                        <?php if($item['isSold']): ?>
                          <span class="badge sold-badge">Sold</span>
                        <?php endif; ?>
                      </div>

                      <p class="h4 text-primary mb-2">
                        $<?php echo number_format((float)$item['price'], 2); ?>
                      </p>

                      <div class="mb-2">
                        <span class="badge market-badge">
                          <?php echo htmlspecialchars($item['category']); ?>
                        </span>

                        <span class="badge market-badge">
                          <?php echo htmlspecialchars($item['itemCondition']); ?>
                        </span>
                      </div>

                      <p class="card-text">
                        <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                      </p>

                      <p class="small mb-3">
                        Posted by 
                        <a 
                          class="link-blue" 
                          href="profile.php?userID=<?php echo (int)$item['sellerID']; ?>"
                        >
                          <?php echo htmlspecialchars($item['username']); ?>
                        </a>
                        <br>
                        <?php echo htmlspecialchars($item['createdOn']); ?>
                      </p>

                      <div class="d-flex flex-wrap gap-2">
                        <?php if($user_data && (int)$user_data['userID'] !== (int)$item['sellerID']): ?>
                          <a 
                            class="btn btn-sm btn-primary" 
                            href="message.php?userID=<?php echo (int)$item['sellerID']; ?>"
                          >
                            Message Seller
                          </a>
                        <?php endif; ?>

                        <?php if(user_can_change_item($user_data, $item)): ?>
                          <form method="POST" action="marketplace.php" class="d-inline">
                            <input type="hidden" name="action" value="toggle_sold">
                            <input 
                              type="hidden" 
                              name="itemID" 
                              value="<?php echo (int)$item['itemID']; ?>"
                            >

                            <button type="submit" class="btn btn-sm btn-default">
                              <?php echo $item['isSold'] ? 'Mark Available' : 'Mark Sold'; ?>
                            </button>
                          </form>

                          <form 
                            method="POST" 
                            action="marketplace.php" 
                            class="d-inline" 
                            onsubmit="return confirm('Delete this marketplace listing?');"
                          >
                            <input type="hidden" name="action" value="delete">
                            <input 
                              type="hidden" 
                              name="itemID" 
                              value="<?php echo (int)$item['itemID']; ?>"
                            >

                            <button type="submit" class="btn btn-sm btn-danger">
                              Delete
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="section-box text-center">
                  <h4>No marketplace listings found.</h4>
                  <p class="mb-0">Create the first listing or adjust your filters.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <script 
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>
  </body>
</html>