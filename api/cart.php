<?php
include '../includes/db.php';
session_start();
header('Content-Type: application/json');

// 1. Auth Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    // 2. Get or Create Cart ID
    $cart_id = null;
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if ($cart) {
        $cart_id = $cart['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    }

    // 3. Handle GET Request (Fetch Items)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("
            SELECT ci.product_id as id, ci.quantity as qty, p.name, p.price, p.type 
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cast types for JS consistency
        foreach ($items as &$item) {
            $item['id'] = (int) $item['id'];
            $item['qty'] = (int) $item['qty'];
            $item['price'] = (float) $item['price'];
        }

        echo json_encode($items);
        exit;
    }

    // 4. Handle POST Requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($action === 'add') {
            $product_id = $input['id'];
            $qty = (int) $input['qty'];

            // Fetch Product Stock
            $stmt_stock = $pdo->prepare("SELECT name, stock_qty FROM products WHERE id = ?");
            $stmt_stock->execute([$product_id]);
            $product = $stmt_stock->fetch();

            if (!$product) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }

            // Check current cart qty
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cart_id, $product_id]);
            $existing = $stmt->fetch();

            $currentInCart = $existing ? (int) $existing['quantity'] : 0;
            $totalRequested = $currentInCart + $qty;

            if ($totalRequested > $product['stock_qty']) {
                $available = max(0, $product['stock_qty'] - $currentInCart);
                http_response_code(400);
                if ($available > 0) {
                    echo json_encode(['error' => "Only $available more available in stock."]);
                } else {
                    echo json_encode(['error' => "Sorry, this item is now out of stock."]);
                }
                exit;
            }

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $stmt->execute([$totalRequested, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$cart_id, $product_id, $qty]);
            }
            echo json_encode(['success' => true]);
        } elseif ($action === 'remove') {
            $product_id = $input['id'];
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cart_id, $product_id]);
            echo json_encode(['success' => true]);
        } elseif ($action === 'migrate') {
            $items = $input['items'] ?? [];
            foreach ($items as $item) {
                $product_id = $item['id'];
                $qty = (int) $item['qty'];

                // Validate Stock during migration
                $stmt_stock = $pdo->prepare("SELECT stock_qty FROM products WHERE id = ?");
                $stmt_stock->execute([$product_id]);
                $p = $stmt_stock->fetch();
                if (!$p)
                    continue;

                $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$cart_id, $product_id]);
                $existing = $stmt->fetch();

                $currentInCart = $existing ? (int) $existing['quantity'] : 0;
                $newQty = min($p['stock_qty'], $currentInCart + $qty); // Cap at stock

                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                    $stmt->execute([$newQty, $existing['id']]);
                } else {
                    if ($newQty > 0) {
                        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$cart_id, $product_id, $newQty]);
                    }
                }
            }
            echo json_encode(['success' => true]);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>