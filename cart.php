<?php
session_start();
include 'connection.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database
    $query = "SELECT u.profile_picture, u.username 
              FROM users u 
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $profile_picture = $user['profile_picture'];
    $username = $user['username'];

    // Store the profile picture in the session
    $_SESSION['profile_picture'] = $profile_picture;
} else {
    // If the user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch cart items with product details
$query = "SELECT ci.*, p.product_name, p.rate, p.quantity as stock, 
          (p.rate * ci.quantity) as total_price 
          FROM cart_items ci 
          JOIN product p ON ci.product_id = p.product_id 
          WHERE ci.user_id = $user_id";
$result = mysqli_query($conn, $query);

// After your existing queries, add this:
$cart_count_query = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = $user_id";
$cart_count_result = mysqli_query($conn, $cart_count_query);
$cart_count = mysqli_fetch_assoc($cart_count_result)['total_items'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Shopping Cart - Explore Batangas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html, body {
            height: 100%;
            background-color: rgb(244, 244, 244);
            color: rgb(51, 51, 51);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navigation Bar */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #8B0000;
            padding: 10px 20px;
            color: rgb(255, 255, 255);
            font-size: 25px;
        }

        /* Logo Section */
        header .logo img {
            max-height: 50px;
            margin-top: 10px;
        }

        /* Navigation Links */
        header nav ul {
            list-style: none;
            display: flex;
            align-items: center;
        }

        header nav ul li {
            margin-right: 30px;
        }

        header nav ul li a {
            text-decoration: none;
            color: rgb(255, 255, 255);
            font-weight: bold;
        }

        header nav ul li a:hover {
            color: rgb(0, 0, 0);
        }

        /* User Profile Styling */
        .user-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: #333;
            border: 2px solid #333;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a, .dropdown-content p {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .user-menu:hover .dropdown-content {
            display: block;
        }

        /* Cart Icon Styling */
        .cart-icon {
            position: relative;
            margin-right: 20px;
        }

        .cart-icon a {
            font-size: 24px;
            color: white;
            text-decoration: none;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #2ecc71;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 18px;
            text-align: center;
        }

        /* Footer Section */
        footer {
            background-color: #8B0000;
            color: rgb(255, 255, 255);
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .footer-content p {
            margin-bottom: 10px;
            color: white;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Your existing cart styles here */
        /* ... (keep your existing cart-specific styles) ... */

        /* Add these styles after your existing header/footer styles */

        /* Cart Container Styles */
        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cart-container h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
            border-bottom: 2px solid #8B0000;
            padding-bottom: 15px;
        }

        /* Cart Item Styles */
        .cart-item {
            display: flex;
            align-items: center;
            padding: 25px;
            border-bottom: 1px solid #eee;
            gap: 30px;
            transition: background-color 0.3s;
        }

        .cart-item:hover {
            background-color: #f8f8f8;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-details h3 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }

        /* Price Styles */
        .price-details {
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .price {
            font-size: 18px;
            font-weight: 600;
            color: #8B0000;
        }

        /* Quantity Controls */
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 15px;
        }

        .quantity-controls button {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quantity-controls button:not(.remove-button) {
            background: #8B0000;
            color: white;
            min-width: 40px;
        }

        .quantity-controls span {
            min-width: 40px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        .remove-button {
            background: #fff;
            color: #ff4444;
            border: 1px solid #ff4444;
            margin-left: auto;
            padding: 8px 20px;
        }

        .remove-button:hover {
            background: #ff4444;
            color: white;
        }

        /* Checkout Section */
        .checkout-section {
            margin-top: 40px;
            padding: 25px;
            background: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .total-amount {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }

        .subtotal, .shipping, .final-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 15px;
        }

        .final-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #8B0000;
            font-size: 20px;
        }

        .shipping {
            color: #666;
            font-size: 0.95em;
            background: #f0f0f0;
            padding: 10px 15px;
            border-radius: 6px;
        }

        /* Button Group */
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 25px;
        }

        .continue-shopping, .checkout-button {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .continue-shopping {
            background: #fff;
            color: #8B0000;
            border: 2px solid #8B0000;
        }

        .continue-shopping:hover {
            background: #8B0000;
            color: white;
        }

        .checkout-button {
            background: #2ecc71;
            color: white;
        }

        .checkout-button:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }

        /* Empty Cart Message */
        .cart-container > p {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 18px;
            background: #f8f8f8;
            border-radius: 8px;
            margin: 20px 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .quantity-controls {
                justify-content: center;
            }

            .button-group {
                flex-direction: column;
            }

            .checkout-section {
                text-align: center;
            }
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            opacity: 1;
            height: 24px;
        }

        .gst {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 15px;
            color: #666;
            font-size: 0.95em;
            background: #f0f0f0;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .manage-address {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: #f39c12;
            color: white;
        }

        .manage-address:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo3.png" alt="Logo" />
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="library.php">Library</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="#">About</a></li>
                
                <!-- Cart Icon -->
                <li class="cart-icon">
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </li>

                <!-- User Profile Section -->
                <div class="user-menu">
                    <?php 
                    // Clean up the profile picture path
                    $profile_path = $profile_picture;
                    ?>
                    
                    <?php if (!empty($profile_path)): ?>
                        <img src="<?php echo $profile_path; ?>" 
                             alt="Profile" 
                             class="user-icon"
                             onerror="this.src='assets/images/default-avatar.png'">
                    <?php else: ?>
                        <div class="user-icon">
                            <?php echo !empty($username) ? strtoupper(substr($username, 0, 1)) : '?' ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="dropdown-content">
                        <p><?php echo !empty($username) ? htmlspecialchars($username) : 'User'; ?></p>
                        <a href="userpanel.php">Dashboard</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </ul>
        </nav>
    </header>

    <div class="cart-container">
        <h1>Your Shopping Cart</h1>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php 
            $total = 0;
            while ($item = mysqli_fetch_assoc($result)): 
                $total += $item['total_price'];
            ?>
                <div class="cart-item">
                    <?php
                    // Determine image path based on product name
                    switch($item['product_name']) {
                        // Traditional Foods
                        case 'Suman':
                            $image_path = "assets/images/products/Suman.png";
                            break;
                        case 'Kalamay':
                            $image_path = "assets/images/products/Kalamay.jpg";
                            break;
                        case 'Puto':
                            $image_path = "assets/images/products/Puto.jpg";
                            break;
                        case 'Sinukmani':
                            $image_path = "assets/images/products/Sinukmani.png";
                            break;
                        case 'Tinapay':
                            $image_path = "assets/images/products/Tinapay.png";
                            break;
                        case 'Lomi':
                            $image_path = "assets/images/products/Lomi.jpg";
                            break;
                        case 'Longganisang Batangas':
                            $image_path = "assets/images/products/Longganisang Batangas.jpg";
                            break;

                        // Beverages
                        case 'Kapeng Barako':
                            $image_path = "assets/images/products/Kapeng-barako.jpg";
                            break;
                        case 'Lambanog':
                            $image_path = "assets/images/products/Lambanog.jpg";
                            break;
                        case 'Kapeng Tablea':
                            $image_path = "assets/images/products/Kapeng Tablea.png";
                            break;
                        case 'El Pasubat':
                            $image_path = "assets/images/products/El pasubat.png";
                            break;
                        case 'Coconut Wine':
                            $image_path = "assets/images/products/Coconut Wine.jpg";
                            break;

                        // Seafood & Preserved Foods
                        case 'Dried Fish':
                            $image_path = "assets/images/products/Dried-fish.jpg";
                            break;
                        case 'Bagoong Balayan':
                            $image_path = "assets/images/products/bagoong-balayan.jpg";
                            break;
                        case 'Burdang Taal':
                            $image_path = "assets/images/products/Burdang Taal.jpg";
                            break;

                        // Natural Products
                        case 'Honey':
                            $image_path = "assets/images/products/Honey.jpg";
                            break;
                        case 'Local Honey Products':
                            $image_path = "assets/images/products/Local Honey-Infused Products.jpg";
                            break;
                        case 'Luyang Dilaw':
                            $image_path = "assets/images/products/Luyang Dilaw.png";
                            break;
                        case 'Saging na Saba':
                            $image_path = "assets/images/products/Saging na saba.jpg";
                            break;
                        case 'Cashew Nuts':
                            $image_path = "assets/images/products/Cashew Nuts.png";
                            break;

                        // Handicrafts
                        case 'Balisong':
                            $image_path = "assets/images/products/Balisong.jpg";
                            break;
                        case 'Taal Lace':
                            $image_path = "assets/images/products/Taal Lace.png";
                            break;
                        case 'Banig':
                            $image_path = "assets/images/products/Banig.jpg";
                            break;
                        case 'Handwoven Basket':
                            $image_path = "assets/images/products/handwoven basket.jpg";
                            break;
                        case 'Native Baskets':
                            $image_path = "assets/images/products/Native Baskets and Mats.jpg";
                            break;
                        case 'Palayok':
                            $image_path = "assets/images/products/palayok.jpg";
                            break;
                        case 'Embroidered Products':
                            $image_path = "assets/images/products/Embroidered Taal Products.jpg";
                            break;

                        // Meat Products
                        case 'Beef':
                            $image_path = "assets/images/products/BEEF.png";
                            break;
                        case 'Lechon':
                            $image_path = "assets/images/products/parada ng lechon.png";
                            break;

                        default:
                            $image_path = "assets/images/products/default.png";
                            break;
                    }
                    ?>
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                         onerror="this.src='assets/images/products/default.jpg'">
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <div class="price-details">
                            <span class="price">₱<?php echo number_format($item['rate'], 2); ?></span>
                        </div>
                        <div class="price-details">
                            <span class="price">Total: ₱<?php echo number_format($item['total_price'], 2); ?></span>
                        </div>
                        
                        <div class="quantity-controls">
                            <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 'decrease')">-</button>
                            <input type="number" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="1" 
                                   max="<?php echo $item['stock']; ?>" 
                                   data-stock="<?php echo $item['stock']; ?>"
                                   onchange="handleQuantityChange(this, <?php echo $item['cart_id']; ?>)"
                                   class="quantity-input">
                            <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 'increase')">+</button>
                            <button class="remove-button" onclick="removeItem(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php
            $gst = $total * 0.18; // Calculate 18% GST
            $final_total = $total + $gst; // Removed shipping_fee from calculation
            ?>

            <div class="checkout-section">
                <div class="total-amount">
                    <div class="subtotal">
                        <span>Subtotal:</span>
                        <span class="price">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="gst">
                        <span>GST (18%):</span>
                        <span class="price">₱<?php echo number_format($gst, 2); ?></span>
                    </div>
                    <div class="final-total">
                        <h2>Total:</h2>
                        <h2 class="price">₱<?php echo number_format($final_total, 2); ?></h2>
                    </div>
                </div>
                <div class="button-group">
                    <button class="continue-shopping" onclick="window.location.href='shop.php'">
                        Continue Shopping
                    </button>
                    <button class="manage-address" onclick="window.location.href='manage_addresses.php'">
                        Manage Address
                    </button>
                    <button class="checkout-button" onclick="window.location.href='checkout.php'">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        <?php else: ?>
            <p>Your cart is empty</p>
            <button class="checkout-button" onclick="window.location.href='shop.php'" style="background: #8B0000;">
                Continue Shopping
            </button>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All rights reserved.</p>
         
        </div>
    </footer>

    <script>
    function updateQuantity(cartId, action) {
        const input = document.querySelector(`input[onchange*="${cartId}"]`);
        const currentValue = parseInt(input.value);
        const stock = parseInt(input.dataset.stock);

        let newValue;
        if (action === 'increase') {
            newValue = currentValue + 1;
            if (newValue > stock) {
                alert(`Sorry, only ${stock} items are available in stock.`);
                return;
            }
        } else {
            newValue = currentValue - 1;
            if (newValue < 1) {
                return;
            }
        }

        updateCartQuantity(cartId, newValue);
    }

    function updateCartQuantity(cartId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    function removeItem(cartId) {
        if (confirm('Are you sure you want to remove this item?')) {
            fetch('remove_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }

    function handleQuantityChange(input, cartId) {
        const stock = parseInt(input.dataset.stock);
        let value = parseInt(input.value);

        // Validate input
        if (isNaN(value) || value < 1) {
            value = 1;
        } else if (value > stock) {
            alert(`Sorry, only ${stock} items are available in stock.`);
            value = stock;
        }

        // Update input value if it was adjusted
        input.value = value;

        // Update cart in database
        updateCartQuantity(cartId, value);
    }
    </script>
</body>
</html> 