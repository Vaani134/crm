# Laravel CRM - Complete Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Database Schema](#database-schema)
4. [Project Structure](#project-structure)
5. [Core Features](#core-features)
6. [Authentication System](#authentication-system)
7. [Models & Relationships](#models--relationships)
8. [Controllers & Logic](#controllers--logic)
9. [Views & UI](#views--ui)
10. [Services & Helpers](#services--helpers)
11. [Configuration](#configuration)
12. [Setup & Installation](#setup--installation)

---

## Project Overview

**Laravel CRM** is a comprehensive inventory and sales management system built with Laravel 11. It provides:
- Dual authentication (password + WebAuthn/Passkeys)
- Complete product and inventory management
- Sales register with receipt generation
- Comprehensive audit logging
- Product categorization
- Role-based access control (Admin/Employee)
- PDF receipt generation
- Email notifications

**Purpose:** Manage products, track sales, maintain inventory, and audit all system activities with secure authentication.

---

## Technology Stack

- **Framework:** Laravel 11
- **Database:** SQLite (development) / MySQL (production-ready)
- **Authentication:** Laravel Auth + WebAuthn (Passkeys)
- **PDF Generation:** DomPDF
- **Email:** PHPMailer
- **Frontend:** Blade Templates + Bootstrap 5
- **JavaScript:** Vanilla JS for WebAuthn
- **Server:** PHP 8.2+

---

## Database Schema

### 1. **admins** Table
Stores admin/employee user accounts.

```sql
CREATE TABLE admins (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Fields:**
- `id`: Unique identifier
- `name`: User's full name
- `email`: Login email (unique)
- `password`: Hashed password (bcrypt)
- `role`: 'admin' (full access) or 'employee' (limited access)
- `created_at/updated_at`: Timestamps

**Default User:** admin@example.com / password

---

### 2. **categories** Table
Product categories for organization.

```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Fields:**
- `id`: Unique identifier
- `name`: Category name (e.g., "Electronics", "Clothing")
- `description`: Optional category description
- `created_at/updated_at`: Timestamps

**Default Categories:** Electronics, Clothing, Food, Books

---

### 3. **products** Table
Inventory products with stock tracking.

```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 0,
    category_id BIGINT,
    image_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

**Fields:**
- `id`: Unique identifier
- `name`: Product name
- `sku`: Stock Keeping Unit (unique code)
- `description`: Product details
- `price`: Selling price
- `quantity`: Current stock level
- `category_id`: Link to categories table
- `image_path`: Product image file path
- `created_at/updated_at`: Timestamps

**Relationships:** Belongs to Category, Has many SaleItems

---

### 4. **sales** Table
Records of all sales transactions.

```sql
CREATE TABLE sales (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    admin_id BIGINT NOT NULL,
    customer_name VARCHAR(255),
    customer_contact VARCHAR(255),
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);
```

**Fields:**
- `id`: Unique sale identifier
- `admin_id`: Employee who made the sale
- `customer_name`: Customer's name
- `customer_contact`: Phone/email
- `total_amount`: Total sale amount
- `payment_method`: Cash/Card/Check
- `notes`: Additional notes
- `created_at/updated_at`: Timestamps

**Relationships:** Belongs to Admin, Has many SaleItems

---

### 5. **sale_items** Table
Individual items in each sale (line items).

```sql
CREATE TABLE sale_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sale_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

**Fields:**
- `id`: Unique line item identifier
- `sale_id`: Link to sales table
- `product_id`: Link to products table
- `quantity`: Items sold
- `unit_price`: Price per item at time of sale
- `subtotal`: quantity × unit_price
- `created_at/updated_at`: Timestamps

**Relationships:** Belongs to Sale, Belongs to Product

---

### 6. **audit_logs** Table
Complete audit trail of all system activities.

```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    admin_id BIGINT,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255),
    model_id BIGINT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);
```

**Fields:**
- `id`: Unique log identifier
- `admin_id`: User who performed action
- `action`: Action type (create/update/delete/login)
- `model_type`: Model affected (Product/Sale/Admin)
- `model_id`: ID of affected record
- `old_values`: Previous data (JSON)
- `new_values`: New data (JSON)
- `ip_address`: User's IP address
- `user_agent`: Browser information
- `created_at/updated_at`: Timestamps

**Relationships:** Belongs to Admin

---

### 7. **webauthn_keys** Table
Stores WebAuthn/Passkey credentials for passwordless login.

```sql
CREATE TABLE webauthn_keys (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    authenticatable_type VARCHAR(255) NOT NULL,
    authenticatable_id BIGINT NOT NULL,
    name VARCHAR(255),
    credential_id LONGBLOB NOT NULL,
    public_key LONGBLOB NOT NULL,
    counter INT DEFAULT 0,
    transports JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Fields:**
- `id`: Unique key identifier
- `authenticatable_type`: Model type (App\Models\Admin)
- `authenticatable_id`: User ID
- `name`: Device name (e.g., "My iPhone")
- `credential_id`: WebAuthn credential identifier
- `public_key`: Public key for verification
- `counter`: Signature counter for security
- `transports`: Device transports (usb, nfc, ble)
- `created_at/updated_at`: Timestamps

**Relationships:** Polymorphic relationship with Admin

---

## Project Structure

```
CRM/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php          # Admin management
│   │   │   ├── AnalysisController.php       # Sales analytics
│   │   │   ├── AuditLogController.php       # Audit log viewing
│   │   │   ├── AuthController.php           # Login/logout
│   │   │   ├── CategoryController.php       # Category CRUD
│   │   │   ├── DashboardController.php      # Dashboard stats
│   │   │   ├── ProductController.php        # Product CRUD
│   │   │   ├── SalesController.php          # Sales management
│   │   │   └── WebAuthnController.php       # Passkey management
│   │   └── Middleware/
│   │       ├── AdminAuth.php                # Check if logged in
│   │       └── AdminOnly.php                # Check if admin role
│   ├── Models/
│   │   ├── Admin.php                        # User model
│   │   ├── AuditLog.php                     # Audit log model
│   │   ├── Category.php                     # Category model
│   │   ├── Product.php                      # Product model
│   │   ├── Sale.php                         # Sale model
│   │   ├── SaleItem.php                     # Sale item model
│   │   ├── User.php                         # Default Laravel user
│   │   └── WebAuthnKey.php                  # WebAuthn key model
│   ├── Services/
│   │   ├── AuditLogService.php              # Audit logging logic
│   │   ├── PDFService.php                   # PDF generation
│   │   ├── PHPMailerService.php             # Email sending
│   │   ├── SimplePDFService.php             # Simple PDF helper
│   │   └── WebAuthnService.php              # WebAuthn logic
│   ├── Helpers/
│   │   └── InventoryHelper.php              # Inventory utilities
│   ├── Mail/
│   │   └── ReceiptMail.php                  # Receipt email template
│   └── Providers/
│       └── AppServiceProvider.php           # Service registration
├── config/
│   ├── app.php                              # App configuration
│   ├── auth.php                             # Auth configuration
│   ├── database.php                         # Database configuration
│   ├── webauthn.php                         # WebAuthn configuration
│   └── ... (other configs)
├── database/
│   ├── migrations/                          # Database migrations
│   ├── seeders/                             # Database seeders
│   └── database.sqlite                      # SQLite database file
├── resources/
│   ├── views/
│   │   ├── layouts/                         # Layout templates
│   │   ├── auth/                            # Login & WebAuthn views
│   │   ├── admin/                           # Admin panel
│   │   ├── products/                        # Product management
│   │   ├── categories/                      # Category management
│   │   ├── sales/                           # Sales register
│   │   ├── audit/                           # Audit logs
│   │   ├── analysis/                        # Analytics
│   │   ├── emails/                          # Email templates
│   │   └── pdf/                             # PDF templates
│   ├── css/
│   │   └── app.css                          # Custom styles
│   └── js/
│       ├── app.js                           # Main JS
│       └── bootstrap.js                     # Bootstrap setup
├── routes/
│   └── web.php                              # All routes
├── storage/
│   ├── app/                                 # File storage
│   ├── logs/                                # Application logs
│   └── framework/                           # Framework cache
├── .env                                     # Environment variables
├── .env.example                             # Example env file
├── composer.json                            # PHP dependencies
├── artisan                                  # Laravel CLI
└── README.md                                # Quick start guide
```

---

## Core Features

### 1. **Authentication System**
- **Password Login:** Traditional email/password authentication
- **WebAuthn/Passkeys:** Biometric or security key authentication
- **Session Management:** Secure session handling
- **Role-Based Access:** Admin vs Employee roles

### 2. **Product Management**
- Create, read, update, delete products
- SKU tracking for inventory
- Category assignment
- Stock quantity management
- Product images
- Price management

### 3. **Sales Register**
- Quick sales entry interface
- Add multiple items to single sale
- Real-time stock updates
- Customer information capture
- Payment method tracking
- Receipt generation (PDF)

### 4. **Inventory Management**
- Stock level tracking
- Low stock alerts
- Refill/restock functionality
- Inventory history
- Stock adjustments

### 5. **Audit Logging**
- Track all user actions
- Record data changes (before/after)
- IP address logging
- User agent tracking
- Filterable audit trail
- Export to PDF

### 6. **Analytics & Reporting**
- Sales dashboard
- Revenue tracking
- Top products
- Sales by category
- Time-based analysis
- Export capabilities

### 7. **Category Management**
- Create/edit/delete categories
- Assign products to categories
- Filter by category
- Category-based reporting

---

## Authentication System

### Password Authentication Flow

1. **Login Page** (`resources/views/auth/login.blade.php`)
   - User enters email and password
   - Form submits to `/login` route

2. **AuthController::login()**
   ```php
   // Validates credentials
   // Hashes password and compares with database
   // Creates session if valid
   // Logs audit entry
   ```

3. **Session Management**
   - Session stored in `storage/framework/sessions/`
   - Session ID in browser cookie
   - Middleware checks session on each request

### WebAuthn/Passkey Flow

1. **Registration** (`/webauthn/register`)
   - User clicks "Add Passkey"
   - Browser prompts for biometric/security key
   - Public key stored in `webauthn_keys` table
   - Device name saved for identification

2. **Authentication** (`/webauthn/login`)
   - User selects passkey option
   - Browser prompts for biometric
   - Signature verified against stored public key
   - Session created if valid

3. **Security Features**
   - Credential ID prevents replay attacks
   - Counter prevents cloning
   - RP ID (Relying Party) validates domain
   - Transports track device type

---

## Models & Relationships

### Admin Model
```php
class Admin extends Model {
    // Relationships
    hasMany(Sale::class)
    hasMany(AuditLog::class)
    morphMany(WebAuthnKey::class, 'authenticatable')
    
    // Methods
    isAdmin()           // Check if admin role
    isEmployee()        // Check if employee role
    getWebAuthnKeys()   // Get all passkeys
}
```

### Product Model
```php
class Product extends Model {
    // Relationships
    belongsTo(Category::class)
    hasMany(SaleItem::class)
    
    // Methods
    isLowStock()        // Check if stock < 10
    decreaseStock()     // Reduce quantity
    increaseStock()     // Add quantity
}
```

### Sale Model
```php
class Sale extends Model {
    // Relationships
    belongsTo(Admin::class)
    hasMany(SaleItem::class)
    
    // Methods
    getTotalAmount()    // Sum of all items
    getItemCount()      // Number of items
    generateReceipt()   // Create PDF
}
```

### SaleItem Model
```php
class SaleItem extends Model {
    // Relationships
    belongsTo(Sale::class)
    belongsTo(Product::class)
    
    // Methods
    getSubtotal()       // quantity × unit_price
}
```

### Category Model
```php
class Category extends Model {
    // Relationships
    hasMany(Product::class)
    
    // Methods
    getProductCount()   // Count products
}
```

### AuditLog Model
```php
class AuditLog extends Model {
    // Relationships
    belongsTo(Admin::class)
    
    // Methods
    getChanges()        // Show what changed
    getOldValues()      // Previous data
    getNewValues()      // New data
}
```

### WebAuthnKey Model
```php
class WebAuthnKey extends Model {
    // Relationships
    morphTo('authenticatable')
    
    // Methods
    isValid()           // Check if key is valid
    updateCounter()     // Update signature counter
}
```

---

## Controllers & Logic

### AuthController
**File:** `app/Http/Controllers/AuthController.php`

**Methods:**
- `showLogin()` - Display login form
- `login(Request $request)` - Process login
  - Validates email/password
  - Checks credentials against database
  - Creates session
  - Logs audit entry
  - Redirects to dashboard
- `logout()` - Destroy session and redirect

**Logic Flow:**
```
User submits form
  ↓
Validate input (email, password required)
  ↓
Find admin by email
  ↓
Verify password with Hash::check()
  ↓
Create session (Auth::login())
  ↓
Log audit entry (AuditLogService)
  ↓
Redirect to dashboard
```

---

### ProductController
**File:** `app/Http/Controllers/ProductController.php`

**Methods:**
- `index()` - List all products with pagination
- `create()` - Show create form
- `store(Request $request)` - Save new product
- `show(Product $product)` - View product details
- `edit(Product $product)` - Show edit form
- `update(Request $request, Product $product)` - Update product
- `destroy(Product $product)` - Delete product
- `refill()` - Show stock refill form
- `refillSingle(Product $product)` - Refill single product

**Key Logic:**
- Image upload handling
- Stock validation
- Category assignment
- Audit logging for all changes
- Low stock warnings

---

### SalesController
**File:** `app/Http/Controllers/SalesController.php`

**Methods:**
- `register()` - Show sales register form
- `store(Request $request)` - Process sale
  - Validates items and quantities
  - Decreases product stock
  - Creates sale record
  - Creates sale items
  - Generates receipt
  - Logs audit entry
- `history()` - View past sales
- `receipt(Sale $sale)` - Display receipt
- `downloadReceipt(Sale $sale)` - Download PDF receipt

**Sale Processing Logic:**
```
User adds items to cart
  ↓
Validate each item (stock available, price valid)
  ↓
Create Sale record
  ↓
For each item:
  - Create SaleItem record
  - Decrease Product stock
  - Calculate subtotal
  ↓
Calculate total amount
  ↓
Generate receipt (PDF)
  ↓
Send email notification
  ↓
Log audit entry
  ↓
Display receipt
```

---

### CategoryController
**File:** `app/Http/Controllers/CategoryController.php`

**Methods:**
- `index()` - List all categories
- `create()` - Show create form
- `store(Request $request)` - Save category
- `show(Category $category)` - View category details
- `edit(Category $category)` - Show edit form
- `update(Request $request, Category $category)` - Update category
- `destroy(Category $category)` - Delete category

**Logic:**
- Prevent deletion if products exist
- Validate unique category names
- Log all changes

---

### AuditLogController
**File:** `app/Http/Controllers/AuditLogController.php`

**Methods:**
- `index()` - List audit logs with filters
- `show(AuditLog $log)` - View log details
- `export()` - Export logs to PDF
- `filter(Request $request)` - Apply filters

**Filtering Options:**
- By user (admin_id)
- By action type
- By model type
- By date range
- By IP address

---

### WebAuthnController
**File:** `app/Http/Controllers/WebAuthnController.php`

**Methods:**
- `manage()` - Show passkey management page
- `registerOptions(Request $request)` - Get registration challenge
- `register(Request $request)` - Store new passkey
- `loginOptions(Request $request)` - Get login challenge
- `login(Request $request)` - Verify passkey and login
- `delete(WebAuthnKey $key)` - Remove passkey

**WebAuthn Logic:**
```
Registration:
  User clicks "Add Passkey"
    ↓
  Generate challenge (random bytes)
    ↓
  Browser creates credential (biometric/key)
    ↓
  Send credential to server
    ↓
  Verify attestation
    ↓
  Store public key in database
    ↓
  Success message

Login:
  User selects passkey
    ↓
  Generate challenge
    ↓
  Browser signs challenge with private key
    ↓
  Send signature to server
    ↓
  Verify signature with stored public key
    ↓
  Check counter (prevent cloning)
    ↓
  Create session
    ↓
  Redirect to dashboard
```

---

### DashboardController
**File:** `app/Http/Controllers/DashboardController.php`

**Methods:**
- `index()` - Display dashboard with stats

**Dashboard Stats:**
- Total products
- Total sales (today/month/all-time)
- Total revenue
- Low stock products
- Recent sales
- Recent audit logs

---

### AnalysisController
**File:** `app/Http/Controllers/AnalysisController.php`

**Methods:**
- `index()` - Show analytics dashboard
- `getSalesData()` - Sales by date
- `getTopProducts()` - Best selling products
- `getCategoryAnalysis()` - Sales by category
- `getRevenueData()` - Revenue trends

---

## Views & UI

### Authentication Views

**login.blade.php**
- Email input field
- Password input field
- "Add Passkey" button (if supported)
- Login button
- Browser compatibility check
- Error messages

**webauthn-manage.blade.php**
- List of registered passkeys
- Device names and creation dates
- "Add New Passkey" button
- Delete buttons for each key
- Last used timestamps

---

### Product Management Views

**products/index.blade.php**
- Product grid/table
- Search and filter
- Category filter
- Stock status indicators
- Edit/Delete buttons
- Add product button

**products/create.blade.php**
- Product form
- Name, SKU, price fields
- Category dropdown
- Description textarea
- Image upload
- Submit button

**products/edit.blade.php**
- Pre-filled form
- Same fields as create
- Update button

**products/refill.blade.php**
- Product selection
- Quantity input
- Refill button
- Stock history

---

### Sales Views

**sales/register.blade.php**
- Product search/selection
- Shopping cart display
- Quantity inputs
- Real-time total calculation
- Customer info fields
- Payment method dropdown
- Complete sale button

**sales/history.blade.php**
- Sales table with pagination
- Date, customer, amount columns
- View receipt button
- Filter options

**sales/receipt.blade.php**
- Receipt header (company info)
- Sale details
- Itemized list
- Total amount
- Payment method
- Date/time
- Print button
- Download PDF button

---

### Audit Log Views

**audit/index.blade.php**
- Audit log table
- User, action, model columns
- Date/time
- Filter options
- View details button
- Export button

**audit/show.blade.php**
- Full log details
- Old values (before)
- New values (after)
- IP address
- User agent
- Timestamp

---

### Dashboard View

**dashboard.blade.php**
- Welcome message
- Stats cards (products, sales, revenue)
- Recent sales table
- Low stock alerts
- Recent activity
- Quick action buttons

---

## Services & Helpers

### AuditLogService
**File:** `app/Services/AuditLogService.php`

**Methods:**
- `log($action, $modelType, $modelId, $oldValues, $newValues)` - Create audit log
- `logLogin($admin)` - Log user login
- `logLogout($admin)` - Log user logout
- `logProductChange($product, $oldData)` - Log product changes
- `logSale($sale)` - Log sale creation

**Automatic Logging:**
- Captures IP address
- Captures user agent
- Records timestamp
- Stores old and new values as JSON
- Associates with current user

---

### WebAuthnService
**File:** `app/Services/WebAuthnService.php`

**Methods:**
- `getRegistrationOptions($admin)` - Generate registration challenge
- `verifyRegistration($response, $admin)` - Verify and store credential
- `getLoginOptions()` - Generate login challenge
- `verifyLogin($response)` - Verify credential and return user
- `validateRpId()` - Ensure correct domain

**Security Features:**
- Challenge-response protocol
- Signature verification
- Counter checking
- RP ID validation
- Attestation verification

---

### PDFService
**File:** `app/Services/PDFService.php`

**Methods:**
- `generateReceipt($sale)` - Create receipt PDF
- `generateAuditReport($logs)` - Create audit log PDF
- `download($pdf, $filename)` - Send PDF to browser

**PDF Content:**
- Receipt: Sale details, items, total, payment method
- Audit Report: Log entries, changes, user info

---

### InventoryHelper
**File:** `app/Helpers/InventoryHelper.php`

**Methods:**
- `getLowStockProducts()` - Products with stock < 10
- `getTotalInventoryValue()` - Sum of all stock value
- `getStockStatus($product)` - Status indicator
- `formatPrice($price)` - Currency formatting

---

## Configuration

### .env File
```
APP_NAME=CRM
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

WEBAUTHN_ID=localhost
SESSION_LIFETIME=120
```

### config/webauthn.php
```php
return [
    'enabled' => true,
    'rp_id' => env('WEBAUTHN_ID', 'localhost'),
    'rp_name' => 'CRM System',
    'origin' => env('APP_URL', 'http://localhost:8000'),
    'timeout' => 60000,
    'attestation' => 'direct',
];
```

### config/auth.php
```php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],
    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],
];
```

---

## Setup & Installation

### Prerequisites
- PHP 8.2+
- Composer
- SQLite or MySQL
- Node.js (optional, for frontend build)

### Installation Steps

1. **Clone/Extract Project**
   ```bash
   cd CRM
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start Server**
   ```bash
   php artisan serve
   ```

6. **Access Application**
   - URL: http://localhost:8000
   - Email: admin@example.com
   - Password: password

### Database Restoration
If database is corrupted, visit: `http://localhost:8000/restore-database`

This will:
- Drop all tables
- Run all migrations
- Seed default data
- Create admin user

---

## User Roles & Permissions

### Admin Role
- Full access to all features
- Create/edit/delete products
- Create/edit/delete categories
- View all sales
- View all audit logs
- Manage users
- Access analytics

### Employee Role
- Create sales
- View own sales
- View own audit logs
- View products
- View categories
- Cannot delete/edit products
- Cannot access admin panel

---

## Security Features

1. **Password Security**
   - Bcrypt hashing
   - Password validation rules
   - Session timeout (120 minutes)

2. **WebAuthn Security**
   - Biometric/security key authentication
   - Challenge-response protocol
   - Signature verification
   - Counter checking

3. **Audit Logging**
   - All actions logged
   - IP address tracking
   - User agent tracking
   - Data change tracking

4. **CSRF Protection**
   - CSRF tokens on all forms
   - Token validation on POST/PUT/DELETE

5. **SQL Injection Prevention**
   - Parameterized queries (Eloquent ORM)
   - Input validation

6. **XSS Prevention**
   - Blade template escaping
   - HTML entity encoding

---

## Troubleshooting

### WebAuthn Not Working
- Ensure using http://localhost:8000 (not IP address)
- Check browser compatibility (Chrome 67+, Firefox 60+, Safari 14+)
- Verify WEBAUTHN_ID in .env matches domain
- Check browser console for errors

### Database Errors
- Run migrations: `php artisan migrate`
- Seed data: `php artisan db:seed`
- Or visit: `/restore-database`

### Permission Errors
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run: `chmod -R 775 storage bootstrap/cache`

### Email Not Sending
- Configure MAIL_* variables in .env
- Test with Mailtrap (free service)
- Check mail logs in `storage/logs/`

---

## API Endpoints

### Authentication
- `POST /login` - Login with email/password
- `POST /logout` - Logout
- `POST /webauthn/register/options` - Get passkey registration challenge
- `POST /webauthn/register` - Register new passkey
- `POST /webauthn/login/options` - Get passkey login challenge
- `POST /webauthn/login` - Login with passkey

### Products
- `GET /products` - List products
- `POST /products` - Create product
- `GET /products/{id}` - View product
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product

### Sales
- `GET /sales/register` - Sales form
- `POST /sales` - Create sale
- `GET /sales/history` - View sales history
- `GET /sales/{id}/receipt` - View receipt

### Categories
- `GET /categories` - List categories
- `POST /categories` - Create category
- `PUT /categories/{id}` - Update category
- `DELETE /categories/{id}` - Delete category

### Audit Logs
- `GET /audit-logs` - View audit logs
- `GET /audit-logs/{id}` - View log details
- `POST /audit-logs/export` - Export to PDF

---

## File Upload Handling

**Product Images:**
- Stored in: `storage/app/public/products/`
- Accessible at: `/storage/products/filename`
- Supported formats: JPG, PNG, GIF
- Max size: 2MB

**PDF Receipts:**
- Generated on-the-fly
- Not stored permanently
- Downloaded directly to user

---

## Performance Optimization

1. **Database Indexing**
   - Indexes on foreign keys
   - Indexes on frequently searched fields

2. **Pagination**
   - Products: 15 per page
   - Sales: 20 per page
   - Audit logs: 25 per page

3. **Caching**
   - Categories cached in session
   - Dashboard stats cached for 5 minutes

4. **Query Optimization**
   - Eager loading with `with()`
   - Select only needed columns
   - Avoid N+1 queries

---

## Future Enhancements

1. **Multi-location Support**
   - Multiple warehouses
   - Stock transfers

2. **Advanced Reporting**
   - Custom date ranges
   - Export to Excel
   - Scheduled reports

3. **Inventory Alerts**
   - Email notifications for low stock
   - Automatic reorder suggestions

4. **Customer Management**
   - Customer profiles
   - Purchase history
   - Loyalty points

5. **Payment Integration**
   - Stripe/PayPal integration
   - Online payments

6. **Mobile App**
   - React Native app
   - Offline mode

---

## Support & Documentation

For issues or questions:
1. Check troubleshooting section
2. Review audit logs for errors
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify database integrity

---

**Last Updated:** January 29, 2026
**Version:** 1.0.0
**Status:** Production Ready
