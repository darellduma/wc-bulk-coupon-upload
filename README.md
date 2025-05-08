# WC Bulk Coupon Uploader

**Bulk upload WooCommerce coupons using CSV files.**  
The **WC Bulk Coupon Uploader** plugin makes it easy to generate multiple WooCommerce coupons by importing a single CSV file — each row becomes a unique coupon.

---

## 🚀 Features

- Bulk import WooCommerce coupons via CSV
- Automatically create coupons from each row in the file
- Supports standard WooCommerce coupon fields
- User-friendly admin interface
- Error handling and import validation

---

## 📦 Installation

1. Upload the plugin to your `/wp-content/plugins/` directory.
2. Activate it through the **Plugins** menu in WordPress.
3. Navigate to **Marketing > Bulk Coupon Upload** to start importing.

---

## 📝 CSV Format

Your CSV should include columns corresponding to WooCommerce coupon fields such as:

| Column Name      | Description                                                   |
|------------------|---------------------------------------------------------------|
| `Code`           | Unique code for the coupon                                    |
| `Product IDs`    | Comma-separated list of product IDs this coupon applies to    |
| `Expiry Date`    | Expiration date in `YYYY-MM-DD` format                        |
| `Description`    | Short internal note for the coupon                            |
| `Usage / Limit`  | Number of times the coupon can be used                        |
| `Coupon Amount`  | Value of the discount (numeric)                               |
| `Coupon Type`    | Type of discount (`percent`, `fixed_cart`, `fixed_product`)   |

> 🛠️ Missing or incorrect values will be skipped or flagged during upload.

---

## 📂 Example CSV

```csv
Code,Product IDs,Expiry Date,Description,Usage / Limit,Coupon Amount,Coupon Type
CouponA,"2960,2959",2025-05-30,Coupon A,1,20,Fixed cart discount
CouponB,2960,2025-05-30,Coupon B,1,50,Fixed Product discount
CouponC,2960,2025-05-30,Coupon C,3,10,Fixed product discount
```

**You can also check the ```wc_bulk_coupon_upload_sample_file.csv``` file saved in the root of the plugin folder.

## ⚠️ Limitations

Only fields explicitly listed in the CSV headers are supported.

The plugin does not parse or map additional custom WooCommerce fields.

Advanced coupon options like usage per user, category restrictions, or metadata are currently not supported.

## ❓FAQ

Q: Can I upload Excel (.xlsx) files?  
A: No, only .csv format is supported at the moment.

Q: What happens if a coupon code already exists?
A: An error message will show and the existing coupon will be skipped to avoid duplicates.

## 🤝 Contributions

Feel free to open an issue or pull request if you want to suggest improvements or report bugs.

## 📃 License

This plugin is licensed under the GPL v2 or later.

## 🧑‍💻 Author

Developed by [Darell Duma](mailto:mailme@darellduma.com)
