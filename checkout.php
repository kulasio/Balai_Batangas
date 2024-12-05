<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_details = $user_result->fetch_assoc();

// Set default values if data is missing
$user_details['fullname'] = $user_details['fullname'] ?? '';
$user_details['contact_number'] = $user_details['contact_number'] ?? '';
$user_details['address'] = $user_details['address'] ?? '';

// Fetch cart items
$cart_query = "SELECT c.*, p.product_name, p.rate FROM cart_items c 
               JOIN product p ON c.product_id = p.product_id 
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $item['subtotal'] = $item['quantity'] * $item['rate'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

$gst = $total * 0.18; // Calculate 18% GST
$final_total = $total + $gst; // Removed shipping_fee from calculation

// Fetch all user addresses instead of just default
$addresses_query = "SELECT * FROM user_addresses WHERE user_id = ?";
$stmt = $conn->prepare($addresses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses_result = $stmt->get_result();
$addresses = [];
while ($address = $addresses_result->fetch_assoc()) {
    $addresses[] = $address;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checkout - Explore Batangas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2em;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .shipping-details,
        .cart-summary,
        .payment-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
            font-size: 1.5em;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #8B0000;
        }

        .item-details h4 {
            color: #333;
            margin-bottom: 8px;
        }

        .item-details p {
            color: #666;
            margin: 5px 0;
            font-size: 0.9em;
        }

        .item-total {
            font-weight: bold;
            color: #8B0000;
            font-size: 1.1em;
        }

        .total-amount {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .subtotal, .shipping {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
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

        .final-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #8B0000;
            font-size: 20px;
        }

        .payment-instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .payment-info {
            margin: 15px 0;
            padding: 15px;
            background: #fff;
            border-left: 4px solid #8B0000;
            border-radius: 4px;
        }

        .payment-info p {
            margin: 10px 0;
            color: #555;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            max-width: 200px;
            margin: 15px auto;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .qr-code img {
            width: 100%;
            height: auto;
            display: block;
        }

        .payment-methods {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 15px 0;
        }

        .payment-method img {
            height: 40px;
            width: auto;
        }

        .upload-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .upload-btn {
            border: 2px dashed #8B0000;
            color: #8B0000;
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 1em;
            width: 100%;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background-color: #ffeeee;
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        #preview-image {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #444;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #8B0000;
            outline: none;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .confirm-btn, .cancel-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirm-btn {
            background-color: #8B0000;
            color: white;
            border: none;
        }

        .confirm-btn:hover {
            background-color: #a01010;
            transform: translateY(-2px);
        }

        .confirm-btn:active {
            transform: translateY(0);
        }

        .cancel-btn {
            background-color: #fff;
            color: #8B0000;
            border: 2px solid #8B0000;
        }

        .cancel-btn:hover {
            background-color: #ffeeee;
        }

        .button-group button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .note {
            color: #666;
            font-style: italic;
            margin: 15px 0;
        }

        @media (max-width: 1200px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .shipping-details,
            .cart-summary,
            .payment-section {
                max-width: 800px;
                margin: 0 auto;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .button-group button {
                width: 100%;
                margin: 5px 0;
            }
        }

        /* Update the address actions styles */
        .address-actions {
            margin-top: 20px;
            text-align: right;
        }

        .change-address-btn, .add-address-btn {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .change-address-btn {
            background-color: #8B0000;
            color: white;
        }

        .change-address-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
        }

        .add-address-btn {
            background-color: #2ecc71;
            color: white;
        }

        .add-address-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        .change-address-btn i, .add-address-btn i {
            font-size: 1.1em;
        }

        /* Add active state */
        .change-address-btn:active, .add-address-btn:active {
            transform: translateY(0);
        }

        .back-button-container {
            padding: 20px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-button:hover {
            background-color: #660000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .back-button i {
            font-size: 16px;
        }

        .back-button:active {
            transform: translateY(0);
        }

        .address-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            margin-bottom: 20px;
        }

        .address-select:focus {
            border-color: #8B0000;
            outline: none;
        }

        .no-address-message {
            color: #666;
            font-style: italic;
            margin-bottom: 20px;
        }

        .manage-address-btn {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            background-color: #4a4a4a;
            color: white;
            margin-right: 10px;
        }

        .manage-address-btn:hover {
            background-color: #333333;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="back-button-container">
            <a href="javascript:history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
        <h2>Checkout</h2>
        
        <!-- Go Back Button -->
       
        
        <div class="checkout-container">
            <!-- 1. Shipping Details Section -->
            <div class="shipping-details">
                <h3>Shipping Details</h3>
                <?php if (empty($addresses)): ?>
                    <p class="no-address-message">No shipping addresses found.</p>
                <?php else: ?>
                    <div class="form-group">
                        <label for="selected_address">Select Delivery Address</label>
                        <select id="selected_address" name="selected_address" required class="address-select">
                            <?php foreach ($addresses as $address): ?>
                                <?php
                                    $formatted_address = $address['receiver_name'] . ' - ' . 
                                        $address['phone'] . ' - ' . 
                                        $address['address_line1'];
                                    if (!empty($address['city'])) {
                                        $formatted_address .= ", " . $address['city'];
                                    }
                                    if (!empty($address['state'])) {
                                        $formatted_address .= ", " . $address['state'];
                                    }
                                    if (!empty($address['postal_code'])) {
                                        $formatted_address .= " " . $address['postal_code'];
                                    }
                                ?>
                                <option value="<?php echo $address['address_id']; ?>" 
                                        <?php echo $address['is_default'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($formatted_address); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="address-actions">
                    <button type="button" onclick="window.location.href='manage_addresses.php'" class="manage-address-btn">
                        <i class="fas fa-cog"></i> Manage Addresses
                    </button>
                  
                </div>
            </div>
            
            <!-- 2. Order Summary Section -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Price: ₱<?php echo number_format($item['rate'], 2); ?></p>
                    </div>
                    <div class="item-total">
                        ₱<?php echo number_format($item['subtotal'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="total-amount">
                    <div class="subtotal">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="gst">
                        <span>GST (18%)</span>
                        <span>₱<?php echo number_format($gst, 2); ?></span>
                    </div>
                    <div class="final-total">
                        <h3>Total Amount</h3>
                        <h3>₱<?php echo number_format($final_total, 2); ?></h3>
                    </div>
                </div>
            </div>

            <!-- 3. Payment Section -->
            <div class="payment-section">
                <h3>Payment Details</h3>
                <div class="payment-instructions">
                    <p>Please send your payment using GCash:</p>
                    
                    <div class="qr-section">
                        <div class="qr-code">
                            <img src="assets/images/payments/QRcode.jpg" alt="GCash QR Code">
                        </div>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <img src="assets/images/payments/gcash-logo.png" alt="GCash">
                            </div>
                        </div>
                    </div>

                    <div class="payment-info">
                        <p><strong>GCash Number:</strong> 09270533556</p>
                        <p><strong>Account Name:</strong> PAUL NIKKO MISSION</p>
                    </div>
                    
                    <p class="note">After sending payment, click the button below to confirm your order.</p>
                </div>
                
                <!-- Upload Payment Proof -->
                <div class="upload-section">
                    <h4>Upload Payment Proof</h4>
                    <p class="note">Please upload a screenshot of your payment</p>
                    
                    <div class="upload-btn-wrapper">
                        <button class="upload-btn" id="upload-btn-text">Click or drag to upload payment proof</button>
                        <input type="file" id="payment-proof" accept="image/*" onchange="previewImage(this)"/>
                    </div>
                    
                    <div class="preview-container">
                        <img id="preview-image" src="#" alt="Preview"/>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="button-group">
                    <button onclick="cancelOrder()" class="cancel-btn" id="cancelBtn">Cancel Order</button>
                    <button onclick="submitOrder()" class="confirm-btn" id="submitBtn">Submit Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview-image');
        const uploadBtn = document.getElementById('upload-btn-text');
        const submitBtn = document.getElementById('submitBtn');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                uploadBtn.textContent = 'Change payment proof';
                submitBtn.disabled = false; // Enable submit button when image is uploaded
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function cancelOrder() {
        const cancelBtn = document.getElementById('cancelBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (confirm('Are you sure you want to cancel your order?')) {
            cancelBtn.disabled = true;
            submitBtn.disabled = true;
            window.location.href = 'shop.php';
        }
    }

    function submitOrder() {
        const fileInput = document.getElementById('payment-proof');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const selectedAddress = document.getElementById('selected_address');

        // Check if address is selected
        if (!selectedAddress || selectedAddress.value === "") {
            alert('Please select a shipping address before proceeding with your order.');
            return;
        }

        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please upload your payment proof first!');
            return;
        }

        // First confirmation
        if (!confirm('Please confirm that you have sent the correct amount to the provided GCash number. Continue?')) {
            return;
        }

        // Second confirmation
        if (!confirm('Are you sure you want to submit your order? This action cannot be undone.')) {
            return;
        }

        // Disable buttons during submission
        submitBtn.disabled = true;
        cancelBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const formData = new FormData();
        formData.append('payment_proof', fileInput.files[0]);
        formData.append('cart_items', JSON.stringify(<?php echo json_encode($cart_items); ?>));
        formData.append('total', <?php echo $final_total; ?>);
        formData.append('order_status', '0'); // 0 means pending
        formData.append('address_id', selectedAddress.value); // Add the selected address ID

        // Send to process_order.php
        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order submitted successfully! Your order is now pending for approval.');
                window.location.href = 'userpanel.php';
            } else {
                alert('Error: ' + data.message);
                // Re-enable buttons on error
                submitBtn.disabled = false;
                cancelBtn.disabled = false;
                submitBtn.textContent = 'Submit Order';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting order. Please try again.');
            // Re-enable buttons on error
            submitBtn.disabled = false;
            cancelBtn.disabled = false;
            submitBtn.textContent = 'Submit Order';
        });
    }

    // Disable submit button initially until proof is uploaded
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('submitBtn').disabled = true;
    });
    </script>
</body>
</html> 