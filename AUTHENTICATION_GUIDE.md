# Authentication Guide - Password Login Recommended

## Current Status

| Authentication Method | Status | Recommendation |
|---|---|---|
| **Password Login** | ✅ Fully Working | **USE THIS** |
| **WebAuthn/Passkeys** | ⚠️ Issues | Use later |

---

## Quick Start - Password Login

### Access Application
```
URL: http://localhost:8000
```

### Login Credentials
```
Email: admin@example.com
Password: password
```

### That's It!
You're logged in and can use all features.

---

## What You Can Do After Login

✅ **Dashboard** - View stats and recent activity
✅ **Products** - Add, edit, delete products
✅ **Sales** - Create sales and generate receipts
✅ **Inventory** - Manage stock and refill
✅ **Categories** - Organize products
✅ **Audit Logs** - Track all activities
✅ **Analytics** - View sales reports
✅ **Settings** - Manage account

---

## WebAuthn Issues Explained

### Problem 1: "Authentication was cancelled or not allowed"

**What happened:**
- You clicked "Sign in with Passkey"
- Browser showed passkeys from your password manager
- You tried to use one
- It failed

**Why:**
- Those passkeys aren't registered with this application
- You need to register a passkey first (in Settings)
- But registration has issues too

**Solution:**
- Use password login instead
- It works perfectly

---

### Problem 2: QR Code Scanning Does Nothing

**What happened:**
- You clicked "Use a phone, tablet, or security key"
- Browser showed a QR code
- You scanned it with your phone
- Nothing happened

**Why:**
- Cross-device authentication requires proper WebAuthn library
- Current implementation is incomplete
- Challenge/response verification not working

**Solution:**
- Use password login instead
- It works perfectly

---

## Why Password Login is Better

### Security
- ✅ Password hashed with bcrypt (industry standard)
- ✅ Session-based authentication
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS prevention

### Reliability
- ✅ Works on all browsers
- ✅ Works on all devices
- ✅ Works on localhost and IP addresses
- ✅ No device setup required
- ✅ No compatibility issues

### Simplicity
- ✅ Just email and password
- ✅ No biometric setup needed
- ✅ No device registration needed
- ✅ Works immediately

---

## How Password Login Works

```
1. User enters email and password
   ↓
2. System finds user by email
   ↓
3. System verifies password with Hash::check()
   ↓
4. Password matches
   ↓
5. Session created
   ↓
6. User logged in
   ↓
7. Redirected to dashboard
```

---

## Session Management

### Session Duration
- **Default:** 120 minutes (2 hours)
- **Configurable:** Edit `.env` → `SESSION_LIFETIME=120`

### Session Storage
- **Location:** `storage/framework/sessions/`
- **Type:** File-based (can be changed to database)

### Logout
- Click "Logout" button (top right)
- Session destroyed
- Redirected to login page

---

## Create More Users

### Using Tinker (Interactive Shell)

```bash
cd CRM
php artisan tinker
```

Then:
```php
use App\Models\Admin;

Admin::create([
    'name' => 'Employee Name',
    'email' => 'employee@example.com',
    'password' => bcrypt('password123'),
    'role' => 'employee'
]);

exit
```

### Using Database

Or directly in database:
```sql
INSERT INTO admins (name, email, password, role, created_at, updated_at)
VALUES ('Employee', 'emp@example.com', '$2y$12$...', 'employee', NOW(), NOW());
```

---

## User Roles

### Admin Role
- Full access to all features
- Can create/edit/delete products
- Can manage users
- Can view all audit logs
- Can access admin panel

### Employee Role
- Can create sales
- Can view own sales
- Can view own audit logs
- Can view products
- Cannot delete/edit products
- Cannot access admin panel

---

## Change Password

### Using Tinker

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

### Using Database

```sql
UPDATE admins 
SET password = '$2y$12$...' 
WHERE email = 'admin@example.com';
```

---

## Forgot Password

Currently, there's no "Forgot Password" feature. To reset:

### Option 1: Use Tinker (Easiest)
```bash
php artisan tinker
```

```php
use App\Models\Admin;
$admin = Admin::where('email', 'admin@example.com')->first();
$admin->password = bcrypt('newpassword');
$admin->save();
exit
```

### Option 2: Direct Database
```sql
UPDATE admins SET password = '$2y$12$...' WHERE email = 'admin@example.com';
```

---

## Security Best Practices

### For Development
- ✅ Use `admin@example.com` / `password`
- ✅ Change password before production
- ✅ Use strong passwords in production

### For Production
- ✅ Use strong passwords (12+ characters)
- ✅ Use HTTPS (not HTTP)
- ✅ Enable CSRF protection (already enabled)
- ✅ Use secure session cookies
- ✅ Implement rate limiting
- ✅ Add 2FA (optional)

---

## Troubleshooting

### Can't Login
1. Check email is correct: `admin@example.com`
2. Check password is correct: `password`
3. Check database connection
4. Check logs: `storage/logs/laravel.log`

### Session Expires Too Quickly
1. Edit `.env`
2. Change `SESSION_LIFETIME=120` to higher value
3. Restart server

### Logout Not Working
1. Check browser cookies are enabled
2. Check session storage is writable
3. Check logs for errors

### Multiple Users Can't Login
1. Create users with Tinker (see above)
2. Each user needs unique email
3. Each user needs password set

---

## Next Steps

### Immediate
1. ✅ Login with `admin@example.com` / `password`
2. ✅ Explore dashboard
3. ✅ Try all features

### Short Term
1. Create additional users
2. Change admin password
3. Test all features

### Long Term
1. Implement proper WebAuthn (optional)
2. Add "Forgot Password" feature
3. Add 2FA (optional)
4. Deploy to production

---

## Summary

| Task | How |
|------|-----|
| Login | Email: admin@example.com, Password: password |
| Create User | Use Tinker or database |
| Change Password | Use Tinker or database |
| Logout | Click logout button |
| Reset Password | Use Tinker or database |
| View Sessions | Check `storage/framework/sessions/` |

---

## Support

For issues:
1. Check logs: `storage/logs/laravel.log`
2. Check database connection
3. Check file permissions
4. Restart server

---

**Status:** ✅ Password login fully working and ready to use!

**Recommendation:** Use password login. It's simple, secure, and reliable!
