# Setup Instructions for Kids Bazaar E-commerce Platform

## Prerequisites

- XAMPP (or any PHP/MySQL server)
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled

## Step-by-Step Setup

### 1. Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `kids_bazaar`
3. Import the schema:
   - Click on `kids_bazaar` database
   - Go to "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

Alternatively, use command line:
```bash
mysql -u root -p < database/schema.sql
```

### 2. Configuration

1. **Database Configuration** (`app/config/database.php`):
   - Update database credentials if different from default:
     - Host: `localhost`
     - Database: `kids_bazaar`
     - Username: `root`
     - Password: `` (empty by default)

2. **Site Configuration** (`app/config/config.php`):
   - Update `SITE_URL` to match your local setup:
     ```php
     define('SITE_URL', 'http://localhost/kid-bazar-ecom/public');
     ```
   - Configure email settings if you want email functionality

### 3. File Permissions

Ensure the following directories are writable:
- `public/assets/uploads/`
- `public/assets/images/products/`
- `public/assets/images/categories/`
- `public/assets/images/banners/`
- `public/assets/images/avatars/`

On Windows, right-click folders → Properties → Security → Ensure write permissions

### 4. Apache Configuration

1. Ensure mod_rewrite is enabled in `httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

2. The `.htaccess` file in `public/` directory handles URL rewriting

### 5. Access the Application

1. Start XAMPP (Apache and MySQL)
2. Open browser and navigate to:
   ```
   http://localhost/kid-bazar-ecom/public
   ```

### 6. Default Login Credentials

**Admin Account:**
- Email: `admin@kidsbazaar.com`
- Password: `admin123`

**⚠️ IMPORTANT:** Change the admin password after first login!

## Directory Structure

```
kid-bazar-ecom/
├── public/                 # Public entry point
│   ├── index.php          # Main router
│   ├── .htaccess          # URL rewriting rules
│   └── assets/            # Static assets
│       ├── css/
│       ├── js/
│       └── images/
├── app/
│   ├── config/            # Configuration files
│   ├── controllers/       # Application controllers
│   ├── models/            # Database models
│   ├── views/             # View templates
│   └── helpers/           # Helper classes
├── database/
│   └── schema.sql         # Database schema
└── README.md
```

## Troubleshooting

### Issue: "404 Not Found" errors
- Check that mod_rewrite is enabled in Apache
- Verify `.htaccess` file exists in `public/` directory
- Check Apache error logs

### Issue: "Database connection failed"
- Verify MySQL is running
- Check database credentials in `app/config/database.php`
- Ensure database `kids_bazaar` exists

### Issue: "Class not found" errors
- Check autoloader in `public/index.php`
- Verify file names match class names (case-sensitive)

### Issue: Images not uploading
- Check directory permissions
- Verify upload directories exist
- Check `MAX_UPLOAD_SIZE` in `app/config/config.php`

## Next Steps

1. Add sample products through admin panel (when implemented)
2. Configure email settings for order confirmations
3. Set up payment gateway credentials (Razorpay/PayU)
4. Customize site settings and branding
5. Add product images and categories

## Development Notes

- All passwords are hashed using `password_hash()` with bcrypt
- PDO prepared statements are used for all database queries (SQL injection protection)
- Input sanitization is done using `Validator::sanitize()` and `htmlspecialchars()`
- Session management handled by `Session` helper class

## Support

For issues or questions, refer to the main README.md file or contact the development team.

