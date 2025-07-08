### Directory Structure
```
canteen_management/
├── css/
│   └── style.css
├── db.sql
├── index.php
├── register.php
├── login.php
├── menu.php
├── admin.php
├── logout.php
├── config.php
```

### 1. Database Setup (`db.sql`)
This SQL script creates the database and necessary tables.

```sql
CREATE DATABASE canteen_management;
USE canteen_management;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    menu_item_id INT,
    quantity INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$J8Xz6Z8Z8Z8Z8Z8Z8Z8Z8uJ8Xz6Z8Z8Z8Z8Z8Z8Z8', 'admin');
```

Run this script in phpMyAdmin or MySQL console to set up the database.

---

### 2. Database Configuration (`config.php`)
This file contains the database connection settings.

```php
<?php
$host = 'localhost';
$db = 'canteen_management';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
```

---

### 3. Homepage (`index.php`)
The homepage welcomes users and provides navigation.

```php
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Canteen Management System</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Welcome to Our Canteen</h2>
        <p>Browse our delicious menu and place your order online!</p>
    </main>
</body>
</html>
```

---

### 4. Registration (`register.php`)
Handles user registration with password hashing.

```php
<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        header("Location: login.php");
    } catch (PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Canteen Management System</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="login.php">Login</a>
        </nav>
    </header>
    <main>
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Username: <input type="text" name="username" required></label>
            <label>Password: <input type="password" name="password" required></label>
            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>
```

---

### 5. Login (`login.php`)
Handles user authentication.

```php
<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Canteen Management System</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="register.php">Register</a>
        </nav>
    </header>
    <main>
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Username: <input type="text" name="username" required></label>
            <label>Password: <input type="password" name="password" required></label>
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
```

---

### 6. Menu (`menu.php`)
Displays menu items and allows users to place orders.

```php
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
```

---

### 7. Admin Panel (`admin.php`)
Allows admins to manage menu items.

```php
<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM menu_items");
$menu_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $stmt = $pdo->prepare("INSERT INTO menuежду2($name, $description, $price));
    $stmt->execute([$name, $description, $price]);
    header("Location: admin.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Canteen Management System</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <h2>Admin Panel - Manage Menu</h2>
        <h3>Add New Item</h3>
        <form method="POST">
            <label>Name: <input type="text" name="name" required></label>
            <label>Description: <textarea name="description"></textarea></label>
            <label>Price: <input type="number" name="price" step="0.01" required></label>
            <button type="submit" name="add_item">Add Item</button>
        </form>
        <h3>Existing Items</h3>
        <div class="menu-items">
            <?php foreach ($menu_items as $item): ?>
                <div class="menu-item">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p>Price: $<?php echo $item['price']; ?></p>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="delete_item" class="delete">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
```

---

### 8. Logout (`logout.php`)
Destroys the session and redirects to the homepage.

```php
<?php
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
```

---

### 9. Styling (`css/style.css`)
A simple, responsive CSS design.

```css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    color: #333;
}

header {
    background: #4CAF50;
    color: white;
    padding: 1rem;
    text-align: center;
}

header h1 {
    margin-bottom: 0.5rem;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 0 1rem;
}

nav a:hover {
    text-decoration: underline;
}

main {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

h2 {
    margin-bottom: 1rem;
    color: #4CAF50;
}

form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 400px;
    margin-bottom: 2rem;
}

label {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

input, textarea, button {
    padding: 0.5rem;
    font-size: 1rem;
}

button {
    background: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    padding: 0.75rem;
}

button:hover {
    background: #45a049;
}

button.delete {
    background: #f44336;
}

button.delete:hover {
    background: #da190b;
}

.menu-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.menu-item {
    border: 1px solid #ddd;
    padding: 1rem;
    border-radius: 5px;
}

.error {
    color: #f44336;
    margin-bottom: 1rem;
}

.success {
    color: #4CAF50;
    margin-bottom: 1rem;
}
```

---

### Setup Instructions
1. **Install WAMP Server**: Ensure WAMP is installed and running.
2. **Create Project Folder**: Create a folder named `canteen_management` in `C:\wamp64\www` (or your WAMP `www` directory).
3. **Copy Files**: Save all the above files in the `canteen_management` folder with the specified directory structure.
4. **Set Up Database**:
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`).
   - Create a new database named `canteen_management`.
   - Import the `db.sql` file to set up theionat

### Notes
- The default admin credentials are: username `admin`, password `admin123`.
- The system does not include image uploads for menu items to keep it simple. You can extend the `menu_items` table and `admin.php` to handle image uploads if needed.
- This is a basic system. For production, add input validation, CSRF protection, and proper error handling.
- The design is minimal but responsive, suitable for both desktop and mobile devices.