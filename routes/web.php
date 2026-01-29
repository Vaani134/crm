<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\AdminController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});





// Complete database restoration route
Route::get('/restore-database', function () {
    try {
        $host = '127.0.0.1';
        $dbname = 'inventory_sales';
        $username = 'root';
        $password = '';

        // First, connect without specifying database to create it if needed
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Now connect to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $results = [];
        
        // 1. Create admins table
        $adminsSql = "
        CREATE TABLE IF NOT EXISTS admins (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('admin', 'employee') DEFAULT 'employee',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($adminsSql);
        $results[] = "✓ Admins table created";
        
        // 2. Create categories table
        $categoriesSql = "
        CREATE TABLE IF NOT EXISTS categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            color VARCHAR(7) NOT NULL DEFAULT '#007bff',
            icon VARCHAR(50) NOT NULL DEFAULT 'fas fa-tag',
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active_sort (is_active, sort_order),
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($categoriesSql);
        $results[] = "✓ Categories table created";
        
        // 3. Create products table
        $productsSql = "
        CREATE TABLE IF NOT EXISTS products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id BIGINT UNSIGNED NULL,
            barcode_number VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(120) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            stock_qty INT NOT NULL DEFAULT 0,
            image_path VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category_id (category_id),
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($productsSql);
        $results[] = "✓ Products table created";
        
        // 4. Create sales table
        $salesSql = "
        CREATE TABLE IF NOT EXISTS sales (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            receipt_no VARCHAR(50) UNIQUE NOT NULL,
            total_items INT NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            tax_percent DECIMAL(5,2) DEFAULT 0.00,
            tax_amount DECIMAL(10,2) DEFAULT 0.00,
            grand_total DECIMAL(10,2) NOT NULL,
            customer_name VARCHAR(255) NULL,
            customer_contact VARCHAR(255) NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($salesSql);
        $results[] = "✓ Sales table created";
        
        // 5. Create sale_items table
        $saleItemsSql = "
        CREATE TABLE IF NOT EXISTS sale_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sale_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            qty INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            line_total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($saleItemsSql);
        $results[] = "✓ Sale items table created";
        
        // 6. Create audit_logs table
        $auditSql = "
        CREATE TABLE IF NOT EXISTS audit_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(255) NOT NULL,
            module VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            old_values JSON NULL,
            new_values JSON NULL,
            ip_address VARCHAR(255) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
            INDEX idx_admin_created (admin_id, created_at),
            INDEX idx_action_created (action, created_at),
            INDEX idx_module_created (module, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($auditSql);
        $results[] = "✓ Audit logs table created";
        
        // 7. Create sessions table for Laravel
        $sessionsSql = "
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) NOT NULL PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload LONGTEXT NOT NULL,
            last_activity INT NOT NULL,
            INDEX sessions_user_id_index (user_id),
            INDEX sessions_last_activity_index (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($sessionsSql);
        $results[] = "✓ Sessions table created";
        
        // 8. Create webauthn_keys table
        $webauthnSql = "
        CREATE TABLE IF NOT EXISTS webauthn_keys (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            authenticatable_type VARCHAR(255) NOT NULL,
            authenticatable_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NULL,
            credential_id VARCHAR(255) UNIQUE NOT NULL,
            public_key TEXT NOT NULL,
            aaguid VARCHAR(255) NULL,
            counter INT UNSIGNED DEFAULT 0,
            last_used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX webauthn_keys_authenticatable_type_authenticatable_id_index (authenticatable_type, authenticatable_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($webauthnSql);
        $results[] = "✓ WebAuthn keys table created";
        
        // 9. Insert default admin user
        $adminInsert = "INSERT IGNORE INTO admins (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($adminInsert);
        $stmt->execute(['admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin']);
        $results[] = "✓ Default admin user created (admin/password)";
        
        // 10. Insert default categories
        $categories = [
            ['Electronics', 'electronics', 'Electronic devices and accessories', '#007bff', 'fas fa-laptop', 1],
            ['Accessories', 'accessories', 'Phone and computer accessories', '#28a745', 'fas fa-plug', 2],
            ['Audio & Video', 'audio-video', 'Headphones, speakers, and audio equipment', '#dc3545', 'fas fa-headphones', 3],
            ['Computing', 'computing', 'Computers, keyboards, mice, and peripherals', '#ffc107', 'fas fa-desktop', 4],
            ['Mobile Devices', 'mobile-devices', 'Smartphones, tablets, and mobile accessories', '#17a2b8', 'fas fa-mobile-alt', 5],
            ['General', 'general', 'General products and miscellaneous items', '#6c757d', 'fas fa-box', 99]
        ];
        
        $categoryInsert = "INSERT IGNORE INTO categories (name, slug, description, color, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($categoryInsert);
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        $results[] = "✓ Default categories created";
        
        // 11. Insert sample products
        $products = [
            ['audio-video', 'SKU001', 'Wireless Bluetooth Headphones', 79.99, 25],
            ['accessories', 'SKU002', 'USB-C Charging Cable', 12.99, 3],
            ['mobile-devices', 'SKU003', 'Smartphone Case', 24.99, 15],
            ['electronics', 'SKU004', 'Portable Power Bank', 45.99, 2],
            ['computing', 'SKU005', 'Wireless Mouse', 29.99, 18],
            ['computing', 'SKU006', 'Mechanical Keyboard', 89.99, 8],
            ['electronics', 'SKU007', 'HD Webcam', 65.99, 1],
            ['audio-video', 'SKU008', 'Bluetooth Speaker', 39.99, 12],
            ['accessories', 'SKU009', 'Phone Stand', 15.99, 20],
            ['mobile-devices', 'SKU010', 'Screen Protector', 9.99, 30]
        ];
        
        foreach ($products as $product) {
            $categoryQuery = "SELECT id FROM categories WHERE slug = ?";
            $stmt = $pdo->prepare($categoryQuery);
            $stmt->execute([$product[0]]);
            $categoryId = $stmt->fetchColumn();
            
            if ($categoryId) {
                $productInsert = "INSERT IGNORE INTO products (category_id, barcode_number, name, price, stock_qty) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($productInsert);
                $stmt->execute([$categoryId, $product[1], $product[2], $product[3], $product[4]]);
            }
        }
        $results[] = "✓ Sample products created";
        
        // 12. Insert sample sales data
        $sampleSales = [
            ['RCP-001', 3, 149.97, 0.00, 0.00, 149.97],
            ['RCP-002', 2, 92.98, 0.00, 0.00, 92.98],
            ['RCP-003', 1, 79.99, 0.00, 0.00, 79.99],
            ['RCP-004', 4, 178.96, 0.00, 0.00, 178.96],
            ['RCP-005', 2, 55.98, 0.00, 0.00, 55.98]
        ];
        
        foreach ($sampleSales as $index => $saleData) {
            $saleInsert = "INSERT IGNORE INTO sales (receipt_no, total_items, subtotal, tax_percent, tax_amount, grand_total, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, DATE_SUB(NOW(), INTERVAL ? DAY))";
            $stmt = $pdo->prepare($saleInsert);
            $stmt->execute(array_merge($saleData, [$index + 1]));
            
            $saleId = $pdo->lastInsertId();
            if ($saleId) {
                // Add sample sale items for each sale
                $sampleItems = [
                    1 => [[1, 1, 79.99], [2, 2, 12.99], [3, 2, 24.99]], // Sale 1
                    2 => [[4, 1, 45.99], [5, 1, 29.99], [6, 1, 89.99]], // Sale 2  
                    3 => [[1, 1, 79.99]], // Sale 3
                    4 => [[7, 1, 65.99], [8, 1, 39.99], [9, 2, 15.99], [10, 3, 9.99]], // Sale 4
                    5 => [[2, 1, 12.99], [3, 1, 24.99], [9, 1, 15.99]] // Sale 5
                ];
                
                if (isset($sampleItems[$index + 1])) {
                    foreach ($sampleItems[$index + 1] as $item) {
                        $itemInsert = "INSERT INTO sale_items (sale_id, product_id, qty, unit_price, line_total) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($itemInsert);
                        $lineTotal = $item[1] * $item[2];
                        $stmt->execute([$saleId, $item[0], $item[1], $item[2], $lineTotal]);
                    }
                }
            }
        }
        $results[] = "✓ Sample sales data created";
        
        // 13. Create migrations table and records
        $migrationsSql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($migrationsSql);
        
        $migrations = [
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table', 
            '0001_01_01_000002_create_jobs_table',
            '2026_01_20_181007_create_admins_table',
            '2026_01_20_181022_create_products_table',
            '2026_01_20_181036_create_sales_table',
            '2026_01_20_181051_create_sale_items_table',
            '2026_01_21_032800_create_audit_logs_table',
            '2026_01_21_040000_create_categories_table',
            '2026_01_21_040100_add_category_to_products_table',
            '2026_01_29_000001_create_webauthn_keys_table'
        ];
        
        foreach ($migrations as $index => $migration) {
            $migrationInsert = "INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)";
            $stmt = $pdo->prepare($migrationInsert);
            $stmt->execute([$migration, $index + 1]);
        }
        $results[] = "✓ Migration records created";
        
        return response()->json([
            'success' => true,
            'message' => 'Database fully restored with sample data!',
            'details' => $results,
            'login' => 'Username: admin, Password: password'
        ]);
        
    } catch(Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
});



// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// WebAuthn routes
Route::prefix('webauthn')->group(function () {
    Route::get('/register/options', [App\Http\Controllers\WebAuthnController::class, 'registerOptions'])->middleware('admin.auth');
    Route::post('/register', [App\Http\Controllers\WebAuthnController::class, 'register'])->middleware('admin.auth');
    Route::post('/login/options', [App\Http\Controllers\WebAuthnController::class, 'loginOptions']);
    Route::post('/login', [App\Http\Controllers\WebAuthnController::class, 'login']);
    Route::get('/manage', [App\Http\Controllers\WebAuthnController::class, 'manage'])->middleware('admin.auth')->name('webauthn.manage');
    Route::delete('/keys/{key}', [App\Http\Controllers\WebAuthnController::class, 'deleteKey'])->middleware('admin.auth');
    Route::put('/keys/{key}/name', [App\Http\Controllers\WebAuthnController::class, 'updateKeyName'])->middleware('admin.auth');
});


// Protected routes
Route::middleware(['admin.auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Products
    Route::resource('products', ProductController::class);
    Route::get('/products/refill/stock', [ProductController::class, 'refillStock'])->name('products.refill');
    Route::get('/products/{product}/refill', [ProductController::class, 'refillSingle'])->name('products.refill.single');
    Route::post('/products/update/stock', [ProductController::class, 'updateStock'])->name('products.update.stock');
    Route::get('/api/products/search', [ProductController::class, 'searchAjax'])->name('products.search.ajax');
    
    // Categories
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    
    // Sales
    Route::get('/sales/register', [SalesController::class, 'register'])->name('sales.register');
    Route::post('/sales/checkout', [SalesController::class, 'checkout'])->name('sales.checkout');
    Route::get('/sales/receipt/{sale}', [SalesController::class, 'receipt'])->name('sales.receipt');
    Route::get('/sales/history', [SalesController::class, 'history'])->name('sales.history');
    Route::post('/sales/{sale}/send-email', [SalesController::class, 'sendReceiptEmail'])->name('sales.send-email');
    Route::post('/sales/{sale}/send-email-phpmailer', [SalesController::class, 'sendReceiptEmailPHPMailer'])->name('sales.send-email-phpmailer');
    Route::get('/sales/{sale}/download-pdf', [SalesController::class, 'downloadReceiptPDF'])->name('sales.download-pdf');
    Route::get('/api/products', [SalesController::class, 'getProductData'])->name('api.products');
    
    // Audit Logs
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/export', [App\Http\Controllers\AuditLogController::class, 'export'])->name('audit.export');
    Route::get('/audit-logs/{auditLog}', [App\Http\Controllers\AuditLogController::class, 'show'])->name('audit.show');
    
    // Analysis
    Route::get('/analysis', [App\Http\Controllers\AnalysisController::class, 'index'])->name('analysis.index');
    
    // Admin management (admin only)
    Route::middleware(['admin.only'])->group(function () {
        Route::get('/manage-users', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/manage-users', [AdminController::class, 'store'])->name('admin.store');
        Route::get('/manage-users/{admin}/edit', [AdminController::class, 'edit'])->name('admin.edit');
        Route::put('/manage-users/{admin}', [AdminController::class, 'update'])->name('admin.update');
        Route::delete('/manage-users/{admin}', [AdminController::class, 'destroy'])->name('admin.destroy');
    });
    
    // Test routes for PDF generation
    Route::post('/test-pdf-generation', [SalesController::class, 'testPDFGeneration'])->name('test.pdf');
    Route::post('/test-email-pdf', [SalesController::class, 'testEmailPDF'])->name('test.email-pdf');
    Route::get('/api/recent-sales', [SalesController::class, 'getRecentSales'])->name('api.recent-sales');
});
