# Laravel CRM - Quick Reference Guide

## Quick Start

### Access the Application
```
URL: http://localhost:8000
Email: admin@example.com
Password: password
```

### Start Server
```bash
cd CRM
php artisan serve
```

### Reset Database
Visit: `http://localhost:8000/restore-database`

---

## Database Tables at a Glance

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| **admins** | User accounts | id, name, email, password, role |
| **products** | Inventory items | id, name, sku, price, quantity, category_id |
| **categories** | Product categories | id, name, description |
| **sales** | Sales transactions | id, admin_id, customer_name, total_amount |
| **sale_items** | Items in sales | id, sale_id, product_id, quantity, subtotal |
| **audit_logs** | Activity tracking | id, admin_id, action, model_type, old_values, new_values |
| **webauthn_keys** | Passkey credentials | id, authenticatable_id, credential_id, public_key |

---

## Key Files & Their Purpose

### Controllers (Business Logic)
- **AuthController** → Login/logout logic
- **ProductController** → Product CRUD operations
- **SalesController** → Sales processing
- **CategoryController** → Category management
- **AuditLogController** → Audit log viewing
- **WebAuthnController** → Passkey management
- **DashboardController** → Dashboard stats
- **AnalysisController** → Sales analytics

### Models (Database Interaction)
- **Admin** → User model with roles
- **Product** → Product with stock tracking
- **Sale** → Sales transactions
- **SaleItem** → Individual sale items
- **Category** → Product categories
- **AuditLog** → Activity logs
- **WebAuthnKey** → Passkey storage

### Services (Reusable Logic)
- **AuditLogService** → Automatic activity logging
- **WebAuthnService** → Passkey authentication
- **PDFService** → PDF generation
- **PHPMailerService** → Email sending

### Views (User Interface)
- **auth/login.blade.php** → Login page
- **auth/webauthn-manage.blade.php** → Passkey management
- **products/** → Product management pages
- **sales/** → Sales register & history
- **categories/** → Category management
- **audit/** → Audit log viewing
- **dashboard.blade.php** → Main dashboard

---

## Common Tasks

### Add a New Product
1. Go to Products → Add Product
2. Fill in: Name, SKU, Price, Category, Description
3. Upload image (optional)
4. Click Save

### Create a Sale
1. Go to Sales Register
2. Search and add products
3. Enter quantity for each
4. Fill customer info
5. Select payment method
6. Click Complete Sale
7. Download receipt

### View Audit Logs
1. Go to Audit Logs
2. Filter by user, action, or date
3. Click on log to see details
4. Export to PDF if needed

### Add Passkey
1. Go to Settings → Manage Passkeys
2. Click "Add New Passkey"
3. Follow browser prompts
4. Confirm with biometric/security key
5. Name your device

### Manage Categories
1. Go to Categories
2. Create, edit, or delete categories
3. Assign products to categories
4. Use for filtering and reporting

---

## User Roles

### Admin
- ✅ Full access to all features
- ✅ Create/edit/delete products
- ✅ Manage users
- ✅ View all sales & audit logs
- ✅ Access analytics

### Employee
- ✅ Create sales
- ✅ View own sales
- ✅ View products & categories
- ❌ Cannot delete/edit products
- ❌ Cannot access admin panel

---

## Important Routes

### Authentication
- `/login` - Login page
- `/logout` - Logout
- `/webauthn/manage` - Passkey management

### Products
- `/products` - Product list
- `/products/create` - Add product
- `/products/{id}/edit` - Edit product
- `/products/refill` - Refill stock

### Sales
- `/sales/register` - Sales form
- `/sales/history` - Sales history
- `/sales/{id}/receipt` - View receipt

### Categories
- `/categories` - Category list
- `/categories/create` - Add category

### Admin
- `/admin` - Admin panel
- `/audit-logs` - Audit logs
- `/analysis` - Analytics

### Dashboard
- `/dashboard` - Main dashboard
- `/` - Home page

---

## Database Relationships

```
Admin
  ├── has many Sales
  ├── has many AuditLogs
  └── has many WebAuthnKeys (polymorphic)

Product
  ├── belongs to Category
  └── has many SaleItems

Category
  └── has many Products

Sale
  ├── belongs to Admin
  └── has many SaleItems

SaleItem
  ├── belongs to Sale
  └── belongs to Product

AuditLog
  └── belongs to Admin

WebAuthnKey
  └── belongs to Admin (polymorphic)
```

---

## Environment Variables (.env)

```
APP_NAME=CRM                          # Application name
APP_URL=http://localhost:8000         # Application URL
DB_CONNECTION=sqlite                  # Database type
DB_DATABASE=database/database.sqlite  # Database file
WEBAUTHN_ID=localhost                 # WebAuthn domain
SESSION_LIFETIME=120                  # Session timeout (minutes)
```

---

## Troubleshooting Quick Fixes

### WebAuthn Not Working
- Use `http://localhost:8000` (not IP address)
- Check browser compatibility
- Verify WEBAUTHN_ID in .env

### Database Errors
- Run: `php artisan migrate`
- Or visit: `/restore-database`

### Permission Issues
- Run: `chmod -R 775 storage bootstrap/cache`

### Email Not Sending
- Configure MAIL_* in .env
- Use Mailtrap for testing

---

## File Locations

| Item | Location |
|------|----------|
| Database | `database/database.sqlite` |
| Logs | `storage/logs/laravel.log` |
| Product Images | `storage/app/public/products/` |
| Sessions | `storage/framework/sessions/` |
| Cache | `storage/framework/cache/` |
| Config | `config/` |
| Routes | `routes/web.php` |

---

## Security Features

✅ Password hashing (bcrypt)
✅ WebAuthn/Passkey authentication
✅ CSRF protection
✅ SQL injection prevention
✅ XSS prevention
✅ Audit logging
✅ Role-based access control
✅ Session management
✅ IP address tracking

---

## Performance Tips

1. Use pagination (already implemented)
2. Indexes on foreign keys (already set)
3. Eager load relationships with `with()`
4. Cache frequently accessed data
5. Monitor database queries in logs

---

## Default Data

### Admin User
- Email: `admin@example.com`
- Password: `password`
- Role: `admin`

### Categories
- Electronics
- Clothing
- Food
- Books

### Sample Products
- Laptop ($999, 5 units)
- T-Shirt ($19.99, 50 units)
- Coffee ($5.99, 100 units)
- Novel ($14.99, 30 units)

---

## Useful Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear

# View logs
tail -f storage/logs/laravel.log

# Generate app key
php artisan key:generate

# Tinker (interactive shell)
php artisan tinker
```

---

## Support Resources

- **Laravel Docs:** https://laravel.com/docs
- **WebAuthn Docs:** https://webauthn.io
- **DomPDF Docs:** https://github.com/barryvdh/laravel-dompdf
- **Bootstrap Docs:** https://getbootstrap.com

---

**Version:** 1.0.0 | **Last Updated:** January 29, 2026
