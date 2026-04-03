# Food Delivery - Admin Role Management System

## Overview

This system implements a robust **Role-Based Access Control (RBAC)** for the Food Delivery application with two main roles:
- **User** - Can browse food items, place orders, view order history
- **Admin** - Can manage food items, users, orders, and vouchers

---

## Access Points

| Page | URL | Description |
|------|-----|-------------|
| **User Login** | `/login.php` | For regular users |
| **Admin Login** | `/admin/index.php` | For admin users |
| **User Dashboard** | `/home.php` | Browse and order food |
| **Admin Dashboard** | `/admin/dashboard.php` | Manage the system |

---

## Default Accounts

### Super Admin (Hardcoded)
- **Username:** `admin`
- **Password:** `admin123`
- **Privileges:** Full access, can edit all users including other admins

### Database Users
Any user in the database with `role = 'admin'` can log in through `/admin/index.php`

---

## Role Management

### Super Admin Features
The super admin (`admin` / `admin123`) has special privileges:
- 👑 **Crown icon** displayed next to username
- Can **edit roles** of other users (change User ↔ Admin)
- Can **edit/delete** any user except the super admin
- Protected from being edited or deleted

### Managing User Roles
1. Login as **super admin**
2. Go to **Users** page (`/admin/users.php`)
3. Click **Edit** on any user
4. Select **Role** from dropdown (User/Admin)
5. Click **Update User**

---

## Access Control Rules

### For Users (Regular Accounts)
| Action | Result |
|--------|--------|
| Access user pages (home, product, order, cart) | ✅ Allowed |
| Access admin pages | ❌ Redirected to `home.php` |
| Role changed to Admin | 🔀 Logged out, redirected to admin login |

### For Admins
| Action | Result |
|--------|--------|
| Access admin pages | ✅ Allowed |
| Access user pages | ❌ Redirected to `admin/dashboard.php` |
| Role changed to User | 🔀 Logged out, redirected to user login |

---

## Real-Time Role Checking

The system checks user roles **on every page load**:

### User Pages (checkUserRole)
- `home.php`, `product.php`, `order.php`, `cart.php`
- Queries database for current role
- If role = `admin` → logout + redirect to admin login

### Admin Pages (checkAdminRole)
- `dashboard.php`, `food_items.php`, `users.php`, `orders.php`, `vouchers.php`
- Queries database for current role
- If role = `user` → logout + redirect to user login

---

## Visual Indicators

### Admin Pages Greeting Banner
All admin pages display a greeting:
```
👋 Welcome, [Username]! [👑 Super Admin]
```

### Role Badges (Users Page)
- **Purple badge** = Admin role
- **Gray badge** = User role
- **👑 Crown** = Super admin account

---

## File Structure

```
food_delivery/
├── admin/
│   ├── index.php          # Admin login (checks hardcoded + database)
│   ├── dashboard.php      # Stats overview + greeting
│   ├── food_items.php     # Manage food items + greeting
│   ├── users.php          # Manage users + greeting + role editing
│   ├── orders.php         # Manage orders + greeting
│   ├── vouchers.php       # Manage vouchers + greeting
│   └── includes/
│       └── db.php         # checkAdminLogin() + checkAdminRole()
├── db.php                 # checkUserRole() for user pages
├── home.php               # User homepage
├── product.php            # Food details
├── order.php              # Order history
├── cart.php               # Shopping cart
└── login.php              # User login
```

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,  -- MD5 hashed
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Quick Start Guide

### For Super Admin
1. Go to `/admin/index.php`
2. Login with `admin` / `admin123`
3. You'll see 👑 Super Admin badge
4. Navigate to Users to manage roles

### For Regular Admin
1. Super admin must first change your role to `admin`
2. You'll be logged out from user session
3. Go to `/admin/index.php`
4. Login with your username/password
5. Access admin dashboard

### For Users
1. Go to `/login.php`
2. Login with your credentials
3. Browse food and place orders

---

## Security Features

1. **Session-based authentication** with role checking
2. **Real-time role validation** on every page load
3. **Automatic logout** when role changes
4. **Super admin protection** from edits/deletion
5. **Separate login pages** for users and admins

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Cannot edit users" | Login as super admin (`admin` / `admin123`) |
| "Redirected to wrong page" | Clear browser cookies/session |
| "Role not updating" | Reload page - changes take effect instantly |
| "Access denied" | Check your role in database |

---

## Key Functions

### admin/includes/db.php
```php
checkAdminLogin()   // Verify admin session
checkAdminRole()    // Real-time role check for admin pages
```

### db.php (root)
```php
checkUserRole()     // Real-time role check for user pages
```
