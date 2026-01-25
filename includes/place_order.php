<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $address = $_POST['customer_address'];

    // Determine source of items
    $cart_items = [];
    session_start();

    if (isset($_SESSION['user_id'])) {
        // LOGGED IN: Fetch from Database
        $user_id = $_SESSION['user_id'];
        $stmt_cart = $pdo->prepare("
            SELECT ci.product_id as id, ci.quantity as qty 
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ?
        ");
        $stmt_cart->execute([$user_id]);
        $cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // GUEST: Use Form Data
        $cart_json = $_POST['cart_data'];
        $cart_items = json_decode($cart_json, true);
    }

    if (empty($cart_items)) {
        die("Error: Cart is empty.");
    }

    // Calculate Total Amount
    try {
        $pdo->beginTransaction();

        // 1. Calculate Total Amount & Validate Stock (Securely)
        $total_amount = 0;
        $order_items_data = []; // Store trusted data to avoid re-querying

        $stmt_check_product = $pdo->prepare("SELECT id, name, price, stock_qty FROM products WHERE id = ? FOR UPDATE");

        foreach ($cart_items as $item) {
            $stmt_check_product->execute([$item['id']]);
            $product = $stmt_check_product->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product ID " . $item['id'] . " not found.");
            }

            if ($product['stock_qty'] < $item['qty']) {
                throw new Exception("Insufficient stock for " . $product['name'] . ". Available: " . $product['stock_qty']);
            }

            // Use Database Price
            $total_amount += ($product['price'] * $item['qty']);

            // Store for insertion step
            $order_items_data[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'qty' => $item['qty']
            ];
        }

        // 2. Insert Order
        $sql = "INSERT INTO orders (customer_name, customer_email, customer_address, total_amount) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $address, $total_amount]);
        $order_id = $pdo->lastInsertId();


        // 3. Insert Items & Deduct Stock
        // (We already checked stock, but we must update it now)
        $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, price_at_purchase, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);

        $stmt_deduct = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");

        foreach ($order_items_data as $data) {
            // Deduct Stock
            $stmt_deduct->execute([$data['qty'], $data['id']]);

            // Insert Item
            $stmt_item->execute([
                $order_id,
                $data['id'],
                $data['name'],
                $data['price'],
                $data['qty']
            ]);
        }

        $pdo->commit();

        // 3. Show Success Page (HTML mode)
        ?>
        <!DOCTYPE html>
        <html lang='en'>

        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Order Confirmed</title>
            <style>
                body {
                    background: #0a0a0a;
                    color: white;
                    font-family: sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    text-align: center;
                }

                .btn {
                    padding: 10px 20px;
                    background: #720e1e;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    display: inline-block;
                    margin-top: 20px;
                }
            </style>
        </head>

        <body>
            <div>
                <h1 style='color: #d4af37;'>Order Confirmed!</h1>
                <p>Thank you,
                    <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>. Your order #
                    <?php echo $order_id; ?> has been placed.
                </p>
                <p>We will contact you shortly.</p>
                <p>We will contact you shortly.</p>
                <a href="../index.php" class="btn" onclick="localStorage.removeItem('lm_cart_guest')">Return Home</a>
            </div>
        </body>

        </html>
                <?php
                // 4. Cleanup Cart (Using session we started earlier)
                if (isset($_SESSION['user_id'])) {
                    // Clear DB Cart
                    $user_id = $_SESSION['user_id'];
                    // Get Cart ID first
                    $stmt_cid = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
                    $stmt_cid->execute([$user_id]);
                    $c = $stmt_cid->fetch();
                    if ($c) {
                        // Delete items
                        $stmt_del = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                        $stmt_del->execute([$c['id']]);
                    }
                }

    } catch (Exception $e) {
        // If anything goes wrong, undo the database changes
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Error placing order: " . $e->getMessage();
    }
}
?>