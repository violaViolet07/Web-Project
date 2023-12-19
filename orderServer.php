<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // start the session if it hasn't been started already
}

$conn = mysqli_connect("localhost", "root", "", "beauté_rose");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$status = "";

if (isset($_SESSION['Email'])) {
    $Email = $_SESSION['Email'];
    if (isset($_POST['Product_ID']) && isset($_POST['quantity'])) {
        $product_ID = $_POST['Product_ID'];
        $quantity = $_POST['quantity'];

        $sql = "SELECT * FROM shopping_cart WHERE CID=? AND Product_ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $CID, $product_ID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Rest of your code...
        } else {
            $sql = "SELECT * FROM product_mp WHERE Product_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_ID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $price = $row['Price'];
                $total_price = $quantity * $price;

                // Insert shopping cart data
                $sql_insert_cart = "INSERT INTO shopping_cart (CID, Product_ID, Quantity, Total_Price) VALUES (?, ?, ?, ?)";
                $stmt_insert_cart = $conn->prepare($sql_insert_cart);
                $stmt_insert_cart->bind_param("iiid", $CID, $product_ID, $quantity, $total_price);

                if ($stmt_insert_cart->execute()) {
                    $cartID = $stmt_insert_cart->insert_id;
                    $_SESSION['cart_id'] = $cartID;

                    $_SESSION["shopping_cart"][] = array(
                        "CartID" => $cartID,
                        "Product_ID" => $product_ID,
                        "quantity" => $quantity,
                        "Price" => $price,
                        "Product_Name" => $row['Product_Name'],
                        "Image" => $row['Image']
                    );
                } else {
                    echo "Error inserting record into shopping cart: " . $stmt_insert_cart->error;
                }
            } else {
                echo "No product found.";
            }
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] == "remove") {
    if (!empty($_SESSION["shopping_cart"])) {
        $product_ID = $_POST["Product_ID"];
        $index = array_search($product_ID, array_column($_SESSION["shopping_cart"], 'Product_ID'));
        if ($index !== false) {
            unset($_SESSION["shopping_cart"][$index]);
            $status = "<div class='box' style='color:red;'>Product is removed from your cart!</div>";
        }
        $_SESSION["shopping_cart"] = array_values($_SESSION["shopping_cart"]);
    } else {
        unset($_SESSION["shopping_cart"]);
    }
}

if (isset($_POST['action']) && $_POST['action'] == "change" && isset($_POST["Product_ID"]) && isset($_POST["quantity"])) {
    $product_ID = $_POST["Product_ID"];
    $quantity = $_POST["quantity"];
    foreach ($_SESSION["shopping_cart"] as &$product) {
        if ($product['Product_ID'] === $product_ID) {
            $product['quantity'] = $quantity;
            break;
        }
    }
}

if (isset($_POST['checkout'])) {
    if (isset($_SESSION['Email']) && isset($_SESSION['CID'])) {
        // User is logged in, proceed to checkout
        $email = $_SESSION['Email'];
        $CID = $_SESSION['CID'];

        // Check if the shopping cart is not empty
        if (!empty($_SESSION["shopping_cart"])) {
            // Get the address information
            $address = isset($_POST['address']) ? $_POST['address'] : '';
            $city = isset($_POST['city']) ? $_POST['city'] : '';
            $state = isset($_POST['state']) ? $_POST['state'] : '';
            $zip = isset($_POST['zip']) ? $_POST['zip'] : '';

            // Validate and sanitize user input
            $address = filter_var($address, FILTER_SANITIZE_STRING);
            $city = filter_var($city, FILTER_SANITIZE_STRING);
            $state = filter_var($state, FILTER_SANITIZE_STRING);
            $zip = filter_var($zip, FILTER_SANITIZE_NUMBER_INT);

            // Insert address data
            $sql_insert_address = "INSERT INTO address (CID, Address, State, City, ZIPcode, Email) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert_address = $conn->prepare($sql_insert_address);
            if ($stmt_insert_address === false) {
                die("Error occurred while preparing the address statement: " . $conn->error);
            }
            $stmt_insert_address->bind_param("isssis", $CID, $address, $state, $city, $zip, $email);
            if ($stmt_insert_address->execute()) {
                $addressID = $stmt_insert_address->insert_id;

                // Insert an order into the order_mp table
                $orderDate = date("Y-m-d"); // Get the current date
                $orderStatus = "Pending"; // Assuming the initial status is "Pending"
                $sql_insert_order = "INSERT INTO order_mp (Email, Order_Date, Order_Status, AddressID) VALUES (?, ?, ?, ?)";
                $stmt_insert_order = $conn->prepare($sql_insert_order);
                if ($stmt_insert_order === false) {
                    die("Error occurred while preparing the order statement: " . $conn->error);
                }
                $stmt_insert_order->bind_param("sssi", $email, $orderDate, $orderStatus, $addressID);
                if ($stmt_insert_order->execute()) {
                    // Order insertion successful
                    $orderID = $stmt_insert_order->insert_id;

                    // Get the payment information
                    $paymentStatus = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'Pending'; // Set a default value if not provided
                    $cardName = isset($_POST['card_name']) ? $_POST['card_name'] : '';
                    $creditNumber = isset($_POST['credit_number']) ? $_POST['credit_number'] : '';
                    $expDate = isset($_POST['exp_date']) ? $_POST['exp_date'] : '';
                    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';

                    // Insert a payment into the payment table
                    $sql_insert_payment = "INSERT INTO payment (Order_ID, Payment_Date, Payment_Status, `Name Card`, Credit_Number, Exp_Date, CVV) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_insert_payment = $conn->prepare($sql_insert_payment);
                    if ($stmt_insert_payment === false) {
                        die("Error occurred while preparing the payment statement: " . $conn->error);
                    }
                    $stmt_insert_payment->bind_param("isssiss", $orderID, $orderDate, $paymentStatus, $cardName, $creditNumber, $expDate, $cvv);

                    if ($stmt_insert_payment->execute()) {
                        // Payment insertion successful
                        $message = "Checkout successful.";
                    } else {
                        $message = "Error occurred while executing the payment query: " . $stmt_insert_payment->error;
                    }
                } else {
                    $message = "Error occurred while executing the order query: " . $stmt_insert_order->error;
                }
            } else {
                $message = "Error occurred while executing the address query: " . $stmt_insert_address->error;
            }

            // Close the database connection
            $conn->close();
        } else {
            $message = "Your cart is empty.";
        }
    } else {
        $message = "You need to be logged in to proceed to checkout.";
    }
}
?>
