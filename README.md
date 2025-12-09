# Kids Bazaar - E-commerce Platform

A comprehensive, user-friendly kids' fashion e-commerce platform built with PHP (OOP), MySQL, Tailwind CSS, and Vanilla JavaScript.

## Features

- **User Management**: Registration, login, profile management
- **Product Catalog**: Advanced filtering, search, product variants
- **Shopping Cart**: Persistent cart for logged-in users, session-based for guests
- **Order Management**: Complete order processing and tracking
- **Admin Panel**: Product, order, and customer management
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Secure**: PDO prepared statements, password hashing, CSRF protection

## Technology Stack

- **Backend**: PHP 7.4+ (OOP approach)
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript (ES6+)
- **Additional**: AJAX, PDO for database operations

## Installation

1. **Clone or download the project**
   ```bash
   cd C:\xampp\htdocs\kid-bazar-ecom
   ```

2. **Database Setup**
   - Open phpMyAdmin or MySQL command line
   - Import the database schema:
     ```bash
     mysql -u root -p < database/schema.sql
     ```
   - Or manually create database `kids_bazaar` and import `database/schema.sql`

3. **Configuration**
   - Update database credentials in `app/config/database.php` if needed
   - Update `SITE_URL` in `app/config/config.php` to match your local setup
   - Configure email settings in `app/config/config.php`

4. **Web Server Setup**
   - Ensure Apache is running in XAMPP
   - Access the site at: `http://localhost/kid-bazar-ecom/public`
   - Make sure mod_rewrite is enabled in Apache

5. **File Permissions**
   - Ensure `public/assets/uploads/` directory is writable
   - Create subdirectories for images if needed:
     ```
     public/assets/images/products/
     public/assets/images/categories/
     public/assets/images/banners/
     public/assets/images/avatars/
     ```

## Default Login

- **Admin Email**: admin@kidsbazaar.com
- **Admin Password**: admin123 (Change after first login)

## Project Structure

```
kid-bazar-ecom/
├── public/              # Public-facing files
│   ├── index.php       # Main entry point
│   └── assets/         # CSS, JS, images
├── app/
│   ├── config/         # Configuration files
│   ├── controllers/    # Business logic
│   ├── models/         # Database models
│   ├── views/          # Frontend templates
│   └── helpers/        # Utility classes
├── database/
│   └── schema.sql      # Database schema
└── README.md
```

## Development

### Adding New Features

1. **Controllers**: Add new controller files in `app/controllers/`
2. **Models**: Add new model files in `app/models/`
3. **Views**: Add view files in `app/views/`
4. **Routes**: Update routing in `public/index.php` if needed

### Database Migrations

Run SQL scripts to modify database structure as needed.

## Security Considerations

- SQL Injection: Protected with PDO prepared statements
- XSS: Input sanitization with `htmlspecialchars()`
- CSRF: Token-based protection (to be implemented)
- Password Security: Bcrypt hashing
- File Upload: Type and size validation

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please contact the development team.

