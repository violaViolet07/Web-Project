<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // start the session if it hasn't been started already
}

include('orderServer.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="CartCss.css">
</head>
<body>
<div class="shopping-cart">
    <div class="title">
        <h3>Shopping cart</h3>
    </div>
    <?php
    if (isset($_SESSION["shopping_cart"]) && count($_SESSION["shopping_cart"]) > 0) {
        $total_Price = 0;
        ?>
        <table class="table">
            <tbody>
            <tr>
                <td></td>
                <td>ITEM NAME</td>
                <td>QUANTITY</td>
                <td>UNIT PRICE</td>
                <td>ITEMS TOTAL</td>
            </tr>
            <?php foreach ($_SESSION["shopping_cart"] as $key => $product_mp) { ?>
                <tr>
                    <td>
                        <?php
                        $folder = 'productsImgs/';
                        if (!empty($product_mp['Image'])) {
                            echo '<img src="' . $folder . $product_mp['Image'] . '" width="50" height="40" style="display:block; margin:auto;" />';
                        }
                        ?>
                    </td>
                    <td><?php echo $product_mp["Product_Name"]; ?><br />
                        <form method='post' action="shoppingCart.php">
                            <input type='hidden' name='Product_ID' value="<?php echo $product_mp["Product_ID"]; ?>" />
                            <input type='hidden' name='action' value="remove" />
                            <button type='submit' class='remove'>Remove Item</button>
                        </form>
                    </td>
                    <td>
                        <form method='post' action="shoppingCart.php">
                            <input type='hidden' name='Product_ID' value="<?php echo $product_mp["Product_ID"]; ?>" />
                            <input type='hidden' name='action' value="change" />
                            <input type="number" name="quantity[<?php echo $key; ?>]" value="<?php echo $product_mp["quantity"]; ?>" min="1" required onchange="console.log('Quantity changed', this.value); this.form.submit()" />

                        </form>
                    </td>
                    <td><?php echo "$" . $product_mp["Price"]; ?></td>
                    <td><?php echo "$" . $product_mp["quantity"] * $product_mp["Price"]; ?></td>
                </tr>
                <?php $total_Price += $product_mp["quantity"] * $product_mp["Price"];
            } ?>
            <tr>
                <td colspan="4" align="right">Total:</td>
                <td align="right"><?php echo "$" . $total_Price; ?></td>
            </tr>
            </tbody>
        </table>

        <form method="post" action="shoppingCart.php">
            <button type="submit" class="btn" name="checkout"><a style="color:white" href="checkout.php">Checkout</a></button>
            <button type="submit" class="btn"><a style="color:white" href="productsDIsplay.php">Go back shopping</a></button>
        </form>
    <?php } else {
        echo "<p>Your cart is empty.</p>";
    } ?>
</div>
</body>
</html>
