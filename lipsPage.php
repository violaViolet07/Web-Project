<?php
include('orderServer.php');

// Connect to database
$con = mysqli_connect("localhost", "root", "", "beauté_rose");

// Check for errors
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Define the category ID for Lips
$category_id = 1;

// Get the category name
$category_query = mysqli_query($con, "SELECT Category_Name FROM category_mp WHERE Category_ID = $category_id");

// Check if the query was successful
if (!$category_query) {
    echo "Error: " . mysqli_error($con);
    exit();
}

$category = mysqli_fetch_assoc($category_query)['Category_Name'];

// Get products for the selected category
$products_query = mysqli_query($con, "SELECT Product_Name, Price, Description, Image, Brand, Date_Expire, Date_Creation FROM product_mp WHERE Category_ID = $category_id");


// Check if the query was successful
if (!$products_query) {
    echo "Error: " . mysqli_error($con);
    exit();
}

// Check if any products were found
if (mysqli_num_rows($products_query) > 0) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $category; ?></title>
        <link rel="stylesheet" href="categoryStyle.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
        integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

      
        <style>
            body {
    align-items: center;
    font-size: 100%;
    background: #ffe5ec;
}
.container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  grid-gap: 20px;
  margin-bottom: 20vh; /* add margin-bottom equal to the height of the footer */
  position: relative;
}

.product {
  border: 1px solid black;
  padding: 20px;
  text-align: center;
  border-radius: 10px;
  background: white;
  max-width: 300px;
  height: auto;
  z-index: 1;
}


.product img {
  position: relative;
  display: block;
  width: 85%;
  height: auto;

}

            .product-image:hover {
                transform: scale(1.2);
            }
      
    .overlay {
      position: absolute;
      bottom: 0;
      background: rgb(0, 0, 0);
      background: rgba(0, 0, 0, 0.5);
      color: #f1f1f1;
      width: 300%;
      transition: .5s ease;
      opacity: 0;
      color: white;
      font-size: 100px;
      padding: 40px;
      text-align: center;
    }

    .product img:hover .overlay {
      opacity: 1;
    }
            .btn {
    font-size: 15px;
    background: #ff0a54;
    color: white;
    border: 2px solid;
    border-radius: 5px;
    padding: 10px;
    font-size: 1em;
    transition: all 0.25s;
}

.btn:hover {
    border-color: #FFE5EC;
    color: white;
    box-shadow: 0 0.5em 0.5em -0.4em #FFE5EC;
    transform: translateY(-0.25em);

}



        </style>
    </head>
    <body>
    <header>
<div class="navbar" style="background: #ff0a54">
  <div class="logo">
    <a href="wepPage.php" style=color:#fb6f92;;>Beauté Rose</a>
  </div>
  <ul class="menu">
    <li><a href="wepPage.php">Home</a></li>
    <li class="dropdown">
      <a href="#">Categories</a>
      <ul>
        <li><a href="lipsPage.php">Lips</a></li>
        <li><a href="facePage.php">Face</a></li>
        <li><a href="eyesPage.php">Eyes</a></li>
      </ul>
    </li>
    <li><a href="#">About</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
</div>
        <h1><?php echo $category; ?></h1>
        
        <div class="container">
        <?php
      // Loop through products and display them in product divs
while ($product = mysqli_fetch_assoc($products_query)) {
    ?>
    <div class="product">
        <?php if (!empty($product['Image'])): ?>
            <img src="./lip/<?php echo $product['Image']; ?>" alt="<?php echo $product['Product_Name']; ?>">

        
        <?php endif; ?>
        <h2><?php echo $product['Product_Name']; ?></h2>
        <p style="font-size:1.5rem;;"><?php echo $product['Brand']; ?></p>
        <p><?php echo "$" . $product['Price']; ?></p>
        <p><?php echo $product['Description']; ?></p>
        <button class="btn"><a style="color:white" href="shoppingCart.php?product_id=<?php echo $product['Product_Name']; ?>">Add to Cart</a></button>
    </div>
    <?php
}

            
    }
    ?>


    
    </div>
    
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h3>About Us</h3>
        <p>We are a beauty company that provides high-quality beauty products for our customers.</p>
        
      </div>
      
      <div class="col-md-6">
        <h3>Contact Us</h3>
        <ul>
          <li>Address: 123 Main St, Anytown USA</li>
          <li>Phone: 555-555-5555</li>
          <li>Email: info@beauterose.com</li>
        </ul>
      </div>
      
    </div>
    <div class="row">
      <div class="col-md-12">
        <p>&copy; 2023 Beauté Rose. All rights reserved.</p>
      </div>
    </div>
  </div>
</footer>
  
    </body>
    </html>

