<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM menu_items");
$menu_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_item_id = $_POST['menu_item_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, menu_item_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $menu_item_id, $quantity]);
    $success = "Order placed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Canteen Management System</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <h2>Menu</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <div class="menu-items">
            <?php foreach ($menu_items as $item): ?>
                <div class="menu-item">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p>Price: $<?php echo $item['price']; ?></p>
                    <form method="POST">
                        <input type="hidden" name="menu_item_id" value="<?php echo $item['id']; ?>">
                        <label>Quantity: <input type="number" name="quantity" min="1" required></label>
                        <button type="submit">Order</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
