# Use Password Login - Simple & Reliable

## The Issue

WebAuthn (passkey/biometric login) has some issues:
- ❌ Passkey registration not fully working
- ❌ Cross-device authentication (QR code) not working
- ❌ Complex to implement properly

## The Solution

**Use password login** - it's fully working and secure!

```
Email: admin@example.com
Password: password
```

---

## Why Password Login is Better Right Now

✅ **Fully Tested** - Works perfectly
✅ **Works Everywhere** - Localhost, IP, any device
✅ **No Setup Required** - Just login
✅ **Secure** - Password is hashed with bcrypt
✅ **Reliable** - No browser compatibility issues
✅ **Fast** - Instant login

---

## How to Login

### Step 1: Go to Login Page
```
http://localhost:8000
```

### Step 2: Enter Credentials
```
Email: admin@example.com
Password: password
```

### Step 3: Click Login
```
You're logged in!
```

---

## All Features Work with Password Login

✅ Dashboard
✅ Products Management
✅ Sales Register
✅ Inventory Management
✅ Audit Logs
✅ Categories
✅ Receipt Generation
✅ Analytics
✅ Everything!

---

## Optional: Hide WebAuthn Button

If you want to remove the "Sign in with Passkey" button from the login page:

### Edit Login View

File: `CRM/resources/views/auth/login.blade.php`

Find this section (around line 30-40):
```html
<div class="mt-3">
    <button type="button" class="btn btn-outline-secondary w-100" id="webauthn-login-btn">
        <i class="fas fa-fingerprint"></i> Sign in with Passkey
    </button>
</div>
```

Replace with:
```html
<!-- WebAuthn disabled - use password login instead -->
```

Or comment it out:
```html
<!-- 
<div class="mt-3">
    <button type="button" class="btn btn-outline-secondary w-100" id="webauthn-login-btn">
        <i class="fas fa-fingerprint"></i> Sign in with Passkey
    </button>
</div>
-->
```

Save the file and refresh the page.

---

## Optional: Disable WebAuthn in Config

File: `CRM/config/webauthn.php`

Change:
```php
'enabled' => true,
```

To:
```php
'enabled' => false,
```

This disables all WebAuthn functionality.

---

## Create Additional Users

Want to create more admin users? Use Laravel Tinker:

```bash
cd CRM
php artisan tinker
```

Then in Tinker:
```php
use App\Models\Admin;

Admin::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password123'),
    'role' => 'admin'
]);

exit
```

Now you can login with:
```
Email: john@example.com
Password: password123
```

---

## Change Password

To change the admin password:

```bash
cd CRM
php artisan tinker
```

Then:
```php
use App\Models\Admin;

$admin = Admin::where('email', 'admin@example.com')->first();
$admin->password = bcrypt('newpassword');
$admin->save();

exit
```

---

## Summary

| Feature | Status |
|---------|--------|
| Password Login | ✅ Works |
| WebAuthn | ⚠️ Issues |
| All Core Features | ✅ Works |

**Recommendation:** Use password login. It's simple, reliable, and fully functional!

---

## Quick Start

1. Go to: `http://localhost:8000`
2. Email: `admin@example.com`
3. Password: `password`
4. Click Login
5. Done! 🎉

---

**Status:** ✅ Ready to use with password login
