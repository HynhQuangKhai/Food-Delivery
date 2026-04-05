# Food Delivery System

A comprehensive food delivery platform with role-based access control, responsive design, and modern UI features.

## Features Overview

### User Features
- Browse food items with categories
- Search and filter food items
- View product details
- Add to cart and checkout
- Order history tracking
- Delivery address management per order
- Review and rate food items
- Apply voucher codes for discounts
- Favorites list
- Contact form for support
- Dark/Light theme toggle
- Responsive design for mobile/tablet/desktop

### Admin Features
- Super admin system with full privileges
- Role management (promote/demote users to admin)
- Food items CRUD management
- Order management with status tracking
- User management with role editing
- Voucher management
- Contact messages management
- Review/reply management
- Statistics dashboard
- Dark/Light theme toggle
- Responsive admin panel

### Deliverer Features
- View available orders for pickup
- Accept and deliver orders
- Track active deliveries
- Delivery history with earnings
- View customer delivery address
- Dark/Light theme toggle

## User Roles

| Role | Description | Access |
|------|-------------|--------|
| **Super Admin** | Hardcoded admin account with full privileges | All admin features + role management |
| **Admin** | Database admin users | Admin panel access |
| **User** | Regular customers | Browse, order, review |
| **Deliverer** | Delivery personnel | Pickup and deliver orders |

## Default Login Credentials

### Super Admin
- **Username:** `admin`
- **Password:** `admin123`

### Test Accounts
Register new accounts through the login page or use existing database users.

## File Structure

```
food_delivery/
├── admin/                      # Admin panel
│   ├── index.php              # Admin login
│   ├── dashboard.php          # Statistics dashboard
│   ├── food_items.php         # Food management
│   ├── users.php              # User management
│   ├── orders.php             # Order management
│   ├── vouchers.php           # Voucher management
│   ├── reviews.php            # Review management
│   ├── contact_messages.php   # Contact messages
│   └── includes/
│       └── db.php             # Admin DB functions
├── app/                       # MVC application structure
│   ├── controllers/
│   ├── models/
│   └── views/
├── home.php                   # User homepage
├── product.php                # Product details
├── cart.php                   # Shopping cart
├── order.php                  # Order history & checkout
├── profile.php                # User profile
├── favorites.php              # Favorites list
├── contact.php                # Contact form
├── deliverer.php              # Deliverer dashboard
├── deliverer_orders.php     # Deliverer order list
├── login.php                  # User login
├── register.php               # User registration
├── db.php                     # Database connection
├── theme.css                  # Theme variables (dark/light)
├── theme.js                   # Theme toggle functionality
├── responsive.css             # Responsive design styles
├── home.css                   # Homepage styles
├── style.css                  # General styles
├── product.css                # Product page styles
├── order-style.css            # Order page styles
├── cart_functions.php         # Cart operations
├── checkout.php               # Checkout processing
├── database.sql               # Database schema
└── README.md                  # This file
```

## Key Features Details

### 1. Theme System (Dark/Light Mode)
- Persistent theme preference using localStorage
- Automatic theme application on page load
- Smooth theme transitions
- Toggle button on all pages

### 2. Responsive Design
- Mobile-first approach
- Breakpoints: 640px (mobile), 1024px (tablet), 1440px+ (desktop)
- Touch-friendly interfaces
- Adaptive navigation (bottom nav on mobile)
- Responsive tables and forms

### 3. Top Selling & Recommended Products
**Homepage Sections:**
- **🔥 Top Selling Products**: Displays 4 most-ordered items with order counts
- **⭐ Recommended For You**: Displays 4 high-rated items based on reviews
- 4 cards per row layout on desktop
- Responsive grid (2 on tablet, 1 on mobile)

### 4. Delivery Address System
- Per-order delivery address (not just profile address)
- Deliverer can see delivery address in dashboard
- Address validation during checkout
- Fallback to profile address if not specified

### 5. Order Status Flow
```
Pending → Confirmed → Ready to Pickup → Delivering → Delivered
```

| Status | Description |
|--------|-------------|
| `pending` | Order placed, awaiting confirmation |
| `confirmed` | Order confirmed by admin |
| `ready_to_pickup` | Ready for deliverer pickup |
| `delivering` | Out for delivery |
| `delivered` | Successfully delivered |

### 6. Voucher System
- Percentage-based discounts
- Voucher codes validation
- Automatic discount calculation at checkout
- Admin can create/manage vouchers

### 7. Review System
- Users can review delivered orders
- 5-star rating system
- Admin can reply to reviews
- Reviews affect "Recommended" products

### 8. Role Management
- Real-time role checking on every page load
- Automatic logout when role changes
- Super admin protection (cannot be deleted)
- Role badges in admin panel

## Database Schema Highlights

### Core Tables
- **users**: User accounts with role field
- **food_items**: Food products with categories
- **orders**: Orders with status tracking and delivery address
- **reviews**: Product reviews with ratings
- **vouchers**: Discount codes
- **contact_messages**: Support messages
- **favorites**: User favorite items
- **product_images**: Multiple images per product

## Security Features

1. **Session-based Authentication**: Secure PHP sessions
2. **Password Hashing**: MD5 with salt (upgrade recommended to bcrypt)
3. **Role-based Access Control**: Separate login pages and access checks
4. **SQL Injection Protection**: Prepared statements throughout
5. **XSS Protection**: HTML escaping with `htmlspecialchars()`
6. **CSRF Protection**: Form tokens (recommended for production)

## Installation

1. Import `database.sql` to MySQL
2. Configure database credentials in `db.php`
3. Ensure `images/` directory is writable for uploads
4. Access via web server (XAMPP/LAMP/Nginx)

## Configuration

### Database (db.php)
```php
$host = 'localhost';
$dbname = 'food_delivery';
$username = 'root';
$password = '';
```

### Theme Colors (theme.css)
Edit CSS variables for custom theming:
```css
:root {
  --primary-color: #667eea;
  --secondary-color: #764ba2;
  /* ... */
}
```

## API Endpoints (for future expansion)

- `cart_functions.php` - Cart operations
- `checkout.php` - Order processing
- Various AJAX endpoints for dynamic operations

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements

- [ ] Payment gateway integration (Stripe/PayPal)
- [ ] Real-time notifications (WebSockets)
- [ ] Mobile app (React Native/Flutter)
- [ ] Email notifications (SMTP)
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Geolocation for delivery tracking

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection error | Check credentials in `db.php` |
| Images not loading | Check `images/` directory permissions |
| Theme not persisting | Clear browser localStorage |
| Session expired | Login again, session timeout is normal |

## Credits

- Built with PHP, MySQL, HTML5, CSS3, JavaScript
- Icons: Emoji and native browser support
- Fonts: System fonts for performance

## License

This project is for educational purposes.
