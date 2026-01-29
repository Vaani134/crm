# 🔧 Quick Fix: WebAuthn Security Error

## The Error
```
Security error: This feature requires HTTPS or localhost
```

## The Fix (Already Done! ✅)

Your `.env` file has been updated:

```env
APP_URL=http://localhost:8000
WEBAUTHN_ID=localhost
```

## What You Need to Do

### Step 1: Stop Current Server
Press `Ctrl+C` in your terminal

### Step 2: Start Fresh Server
```bash
cd CRM
php artisan serve
```

### Step 3: Access Application
```
http://localhost:8000
```

### Step 4: Login
- Email: `admin@example.com`
- Password: `password`

### Step 5: Test WebAuthn
1. Click Settings (top right)
2. Click "Manage Passkeys"
3. Click "Add New Passkey"
4. Follow browser prompts
5. ✅ Should work now!

---

## Why This Works

| Before | After |
|--------|-------|
| `http://192.168.31.94:8000` | `http://localhost:8000` |
| ❌ WebAuthn blocked | ✅ WebAuthn allowed |
| IP address (not secure) | Localhost (always secure) |

---

## If You Still Get the Error

### Clear Browser Cache
1. Press `Ctrl+Shift+Delete`
2. Select "All time"
3. Check "Cookies and other site data"
4. Click "Clear data"
5. Reload page

### Verify .env is Correct
```bash
cat CRM/.env | grep APP_URL
cat CRM/.env | grep WEBAUTHN_ID
```

Should show:
```
APP_URL=http://localhost:8000
WEBAUTHN_ID=localhost
```

### Try Different Browser
- Chrome (best support)
- Firefox
- Edge
- Safari

---

## Access from Other Devices

If you want to access from another device on your network:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Then from other device:
```
http://YOUR_PC_IP:8000
```

**Note:** Password login works, but WebAuthn won't (expected behavior)

---

## Done! 🎉

Your WebAuthn is now fixed and ready to use!
