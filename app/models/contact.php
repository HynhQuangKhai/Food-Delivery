<?php
/**
 * Contact Us Page - Food Delivery System
 */

// Start session
session_start();

// Include database connection
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Real-time role check - redirect to admin if role changed
checkUserRole();

// Redirect admin to admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Get database connection
$conn = getDBConnection();

// Fetch email from database if not in session
if (!isset($_SESSION['email'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['email'] = $row['email'];
    }
    $stmt->close();
}

$message = '';
$error = '';

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($subject) || empty($message_text)) {
        $error = "Please fill in all fields!";
    } else {
        // Save message to database
        $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, username, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $subject, $message_text);
        
        if ($stmt->execute()) {
            $message = "Thank you for contacting us! We will get back to you soon.";
        } else {
            $error = "Failed to send message. Please try again.";
        }
        
        $stmt->close();
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Food Delivery System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .contact-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .contact-container h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .contact-info {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .contact-info p {
            margin: 10px 0;
            color: #555;
        }
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-submit {
            padding: 15px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background: #5568d3;
        }
        .success-message {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error-message {
            padding: 15px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-brand">
            <h1>🍔 Food Delivery</h1>
        </div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="order.php">My Orders</a>
            <a href="contact.php" class="active">Contact Us</a>
        </div>
        <div class="nav-user">
            <span>Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Contact Section -->
    <main class="contact-container">
        <h2>📞 Contact Us</h2>
        
        <div class="contact-info">
            <p><strong>📧 Email:</strong> support@fooddelivery.com</p>
            <p><strong>📱 Phone:</strong> +1 (555) 123-4567</p>
            <p><strong>📍 Address:</strong> 123 Food Street, Cuisine City, FC 12345</p>
            <p><strong>🕐 Hours:</strong> Mon-Sun: 9:00 AM - 10:00 PM</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="contact-form">
            <div class="form-group">
                <label for="subject">Subject *</label>
                <input type="text" id="subject" name="subject" required placeholder="How can we help you?">
            </div>
            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" rows="6" required placeholder="Describe your issue or question..."></textarea>
            </div>
            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Food Delivery System. All rights reserved.</p>
    </footer>

</body>
</html>
