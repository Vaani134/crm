# 🔐 CRM Inventory Management System with WebAuthn

A modern Laravel-based inventory management system with WebAuthn/Passkey authentication support.

## 🚀 Features

- **Dual Authentication**: Password login + WebAuthn passkeys
- **Product Management**: Add, edit, delete products with categories
- **Sales Register**: Interactive point-of-sale system
- **Inventory Tracking**: Real-time stock management
- **Audit Logs**: Complete activity tracking
- **Analytics**: Sales trends and insights
- **User Management**: Role-based access control

## 🔧 Installation

### Requirements
- PHP 8.1+
- Laravel 12+
- MySQL 5.7+
- Composer

### Setup

1. **Clone/Extract the project**
   ```bash
   cd CRM
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```

4. **Generate app key**
   ```bash
   php artisan key:generate
   ```

5. **Setup database**
   ```bash
   php artisan migrate
   ```

6. **Seed sample data**
   ```bash
   php artisan db:seed
   ```

## 🚀 Running the Application

### Start Development Server
```bash
php artisan serve --host=192.168.31.94 --port=8000
```

### Access URLs
- **Host Machine**: `http://localhost:8000`
- **Other Devices**: `http://192.168.31.94:8000`

### Default Credentials
- **Username**: admin
- **Password**: password

## 📱 Features Overview

### Authentication
- ✅ Password-based login
- ✅ WebAuthn/Passkey support (localhost only)
- ✅ Role-based access control

### Inventory Management
- ✅ Product CRUD operations
- ✅ Category management
- ✅ Stock tracking
- ✅ Barcode support

### Sales
- ✅ Point-of-sale register
- ✅ Receipt generation (PDF)
- ✅ Email receipts
- ✅ Sales history

### Reporting
- ✅ Dashboard analytics
- ✅ Sales analysis
- ✅ Audit logs
- ✅ Export capabilities

## 🔐 WebAuthn / Passkey Support

### Works On
- `http://localhost:8000` - Full WebAuthn support
- `https://yourdomain.com` - Production with HTTPS

### Doesn't Work On
- `http://192.168.31.94:8000` - IP addresses (security limitation)
- Use password login for IP address access

## 📊 Database

The system uses MySQL with the following main tables:
- `admins` - User accounts
- `products` - Inventory items
- `categories` - Product categories
- `sales` - Transaction records
- `sale_items` - Transaction line items
- `audit_logs` - Activity tracking
- `webauthn_keys` - Passkey credentials

## 🛠️ Configuration

### Environment Variables (.env)
```env
APP_URL=http://192.168.31.94:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=inventory_sales
DB_USERNAME=root
DB_PASSWORD=
```

### WebAuthn Configuration (config/webauthn.php)
- Relying Party ID
- Challenge length
- Timeout settings
- User verification requirements

## 📝 Usage

### Login
1. Visit login page
2. Enter credentials (admin/password)
3. Click "Sign In"

### Manage Products
1. Go to Products section
2. Add/Edit/Delete products
3. Organize by categories

### Process Sales
1. Go to Sales Register
2. Search and add products
3. Enter customer info
4. Complete sale

### View Reports
1. Go to Dashboard for overview
2. Check Analysis for trends
3. Review Audit Logs for activity

## 🔒 Security

- Password hashing with bcrypt
- CSRF protection
- Session management
- Audit logging
- WebAuthn for enhanced security

## 📞 Support

For issues or questions, check the application logs:
```bash
tail -f storage/logs/laravel.log
```

## 📄 License

This project is open source and available under the MIT License.