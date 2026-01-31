# Setup Product Details - Quick Guide

## What's New

8 new product detail fields have been added to capture comprehensive product information:

1. ✅ Product Description
2. ✅ Brand Name
3. ✅ Manufacturing Date
4. ✅ Expiry Date
5. ✅ Warranty Period
6. ✅ Guarantee Period
7. ✅ Tax Percentage
8. ✅ Discount Percentage

---

## Step 1: Run Migration

```bash
cd CRM
php artisan migrate
```

This creates all new columns in the database.

**Expected Output:**
```
Migrating: 2026_01_30_000001_add_product_details_to_products_table
Migrated:  2026_01_30_000001_add_product_details_to_products_table (XXms)
```

---

## Step 2: Test the New Fields

### Create a Product with Details

1. Go to: `http://localhost:8000/products/create`
2. Fill in basic info:
   - Product Name: "Samsung Galaxy S24"
   - SKU: "SGS24-001"
   - Price: 999.99
   - Stock: 10
   - Category: Electronics

3. Scroll down to "Product Details" section
4. Fill in details:
   - Description: "Latest flagship smartphone with advanced features"
   - Brand: "Samsung"
   - Manufacturing Date: 2026-01-01
   - Expiry Date: 2028-01-01
   - Warranty: 12 months
   - Guarantee: 6 months
   - Tax: 18%
   - Discount: 5%

5. Click "Add Product"
6. ✅ Product created with all details!

### Edit Product to See Details

1. Go to Products page
2. Click Edit on any product
3. Scroll down to "Product Details"
4. See all fields pre-filled
5. Update any field
6. Click "Update Product"
7. ✅ Changes saved!

---

## Step 3: Verify Features

### Expiry Date Alerts

1. Edit a product
2. Set expiry date to today or past date
3. See red alert: "Product has expired!"

### Expiry Soon Alert

1. Edit a product
2. Set expiry date to 15 days from now
3. See yellow alert: "Expiring soon!"

### Search by Brand

1. Go to Products page
2. Search for "Samsung"
3. See all Samsung products

---

## Field Details

### Product Description
- **Type:** Long text area
- **Use:** Complete product details, features, specifications
- **Example:** "High-performance laptop with 16GB RAM, 512GB SSD, Intel i7 processor"

### Brand Name
- **Type:** Text (100 chars max)
- **Use:** Manufacturer name
- **Example:** "Apple", "Samsung", "Sony"

### Manufacturing Date
- **Type:** Date picker
- **Use:** When product was made
- **Format:** YYYY-MM-DD

### Expiry Date
- **Type:** Date picker
- **Use:** When product expires
- **Format:** YYYY-MM-DD
- **Note:** Optional, leave empty if no expiry

### Warranty Period
- **Type:** Number (months)
- **Use:** Warranty duration
- **Example:** 12, 24, 36

### Guarantee Period
- **Type:** Number (months)
- **Use:** Guarantee duration
- **Example:** 6, 12, 24

### Tax Percentage
- **Type:** Decimal (0-100)
- **Use:** Tax rate on product
- **Default:** 0%
- **Example:** 5, 10, 18, 28

### Discount Percentage
- **Type:** Decimal (0-100)
- **Use:** Discount rate on product
- **Default:** 0%
- **Example:** 5, 10, 15, 20

---

## Common Use Cases

### Electronics
- Description: Detailed specs
- Brand: Manufacturer
- Manufacturing: Purchase date
- Expiry: Warranty expiry
- Warranty: 12-24 months
- Guarantee: 6-12 months
- Tax: 18%
- Discount: 5-10%

### Food/Beverages
- Description: Ingredients, allergens
- Brand: Manufacturer
- Manufacturing: Production date
- Expiry: Expiry date (important!)
- Warranty: N/A
- Guarantee: N/A
- Tax: 5-12%
- Discount: 0-5%

### Medicines
- Description: Dosage, usage
- Brand: Manufacturer
- Manufacturing: Batch date
- Expiry: Expiry date (critical!)
- Warranty: N/A
- Guarantee: N/A
- Tax: 0-5%
- Discount: 0%

### Clothing
- Description: Size, material, care
- Brand: Designer/brand
- Manufacturing: N/A
- Expiry: N/A
- Warranty: N/A
- Guarantee: N/A
- Tax: 5-18%
- Discount: 10-30%

---

## Tips & Tricks

### Bulk Import
If you have existing products, you can:
1. Export to CSV
2. Add new columns
3. Import back with details

### Expiry Management
- Set expiry dates for perishables
- Get automatic alerts
- Plan stock rotation

### Tax & Discount
- Store per-product tax rates
- Store per-product discounts
- Use for invoice generation

### Brand Tracking
- Search by brand
- Analyze brand performance
- Track supplier information

---

## Troubleshooting

### Migration Failed
```bash
# Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Fields Not Showing
1. Clear browser cache: `Ctrl+Shift+Delete`
2. Refresh page: `Ctrl+F5`
3. Restart server: `Ctrl+C` then `php artisan serve`

### Validation Errors
- Tax/Discount must be 0-100
- Dates must be valid format
- All fields are optional

---

## Next Steps

1. ✅ Run migration
2. ✅ Create products with details
3. ✅ Test expiry alerts
4. ✅ Search by brand
5. ✅ Use tax/discount in invoices

---

## Support

For issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify migration ran: `php artisan migrate:status`
3. Clear cache: `php artisan cache:clear`
4. Restart server

---

**Status:** ✅ Ready to use!

Start adding detailed product information now!
