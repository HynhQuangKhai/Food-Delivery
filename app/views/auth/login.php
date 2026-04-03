<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>🍔 Food Delivery</h1>
            <h2>Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/food_delivery/login">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-link">
                Don't have account? <a href="/food_delivery/register">Register</a>
            </div>
        </div>
    </div>
</body>
</html>
