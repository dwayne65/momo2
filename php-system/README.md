# PHP Mobile Money System

A complete PHP-based mobile money management system with database persistence, user authentication, and external API integration.

## Features

- **User Management**: Verify and manage mobile money users
- **Payment Processing**: Process mobile money payments
- **Transfer Management**: Send money between users
- **Group Management**: Organize users into groups
- **Data Export**: Export data in CSV or JSON format
- **Admin Authentication**: Secure login system
- **API Integration**: Connects to MOPAY API for real transactions

## Installation

1. **Prerequisites**:
   - PHP 8.0 or higher
   - MySQL 5.7 or higher
   - Web server (Apache/Nginx) or PHP built-in server

2. **Database Setup**:
   ```bash
   mysql -u root -p < php-system/database.sql
   ```

3. **Configuration**:
   - Edit `config.php` to set your database credentials
   - Set your MOPAY API token in environment variables or config.php

4. **Web Server Setup**:
   - Place the `php-system` folder in your web server's document root
   - Or use PHP's built-in server:
   ```bash
   cd php-system
   php -S localhost:8000
   ```

5. **Access the Application**:
   - Open `http://localhost/mobile-money-system` (or your server URL)
   - Login with default credentials: `admin` / `admin123`

## File Structure

```
php-system/
├── config.php          # Database and API configuration
├── database.sql        # Database schema
├── login.php           # Admin login page
├── index.php           # Dashboard
├── verify.php          # User verification
├── payments.php        # Payment processing
├── transfers.php       # Transfer management
├── groups.php          # Group management
├── export.php          # Data export
├── api.php             # REST API endpoints
├── logout.php          # Logout handler
└── README.md           # This file
```

## API Endpoints

The system provides REST API endpoints for integration:

- `GET/POST /api/users` - User management
- `GET/POST /api/payments` - Payment processing
- `GET/POST /api/transfers` - Transfer management
- `GET/POST /api/groups` - Group management
- `POST /api/verify-user` - User verification
- `POST /api/process-payment` - Process payment
- `POST /api/process-transfer` - Process transfer

## Database Tables

- `users` - Verified mobile money users
- `payments` - Payment transactions
- `transfers` - Money transfers
- `groups` - User groups
- `group_members` - Group membership
- `admin_users` - System administrators

## Security Features

- Password hashing for admin accounts
- Session-based authentication
- Input sanitization
- Prepared statements for database queries
- CSRF protection on forms

## Configuration

Edit `config.php` to customize:

- Database connection settings
- API endpoints and tokens
- Application settings
- Session configuration

## Default Admin Account

- Username: `admin`
- Password: `admin123`

**Important**: Change the default password after first login!

## External API Integration

The system integrates with MOPAY API for:

- User verification (`/customer-info`)
- Payment processing (`/initiate-payment`)
- Money transfers (`/transfer`)
- Transaction status checking (`/check-status`)

## Troubleshooting

1. **Database Connection Issues**:
   - Verify database credentials in `config.php`
   - Ensure MySQL server is running
   - Check database name matches the one created

2. **API Integration Issues**:
   - Verify MOPAY API tokens are set correctly
   - Check API endpoints in `config.php`
   - Ensure proper network connectivity

3. **Permission Issues**:
   - Ensure web server has write permissions for session files
   - Check file permissions for PHP files

## License

This project is open source and available under the MIT License.
