<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>🍔 Food Delivery</h1>
            <h2>Create Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?> - <a href="/food_delivery/login">Login here</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/food_delivery/register">
                <div class="form-group">
                    <label>Full Name *:</label>
                    <input type="text" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label>Username *:</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Password *:</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                
                <div class="form-group">
                    <label>Email *:</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone">
                </div>
                
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" rows="2"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <div class="login-link">
                Have account? <a href="/food_delivery/login">Login</a>
            </div>
        </div>
    </div>
</body>
</html>
