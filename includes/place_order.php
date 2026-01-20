<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['customer_name'];
    $email = $_POST['customer_email'];
    $address = $_POST['customer_address'];
    $cart_json = $_POST['cart_data'];

    $cart_items = json_decode($cart_json, true);

    if (empty($cart_items)) {
        die("Error: Cart is empty.");
    }

    // Calculate Total
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += ($item['price'] * $item['qty']);
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert Order
        $sql = "INSERT INTO orders (customer_name, customer_email, customer_address, total_amount) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $address, $total_amount]);
        $order_id = $pdo->lastInsertId();

        // 2. Insert Items
        $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, price_at_purchase, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);

        foreach ($cart_items as $item) {
            $stmt_item->execute([
                $order_id,
                $item['id'],
                $item['name'],
                $item['price'],
                $item['qty']
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
                <a href="../index.php" class="btn" onclick="localStorage.removeItem('lm_cart')">Return Home</a>
            </div>
        </body>

        </html>
        <?php

    } catch (Exception $e) {
        // If anything goes wrong, undo the database changes
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Error placing order: " . $e->getMessage();
    }
}
?>