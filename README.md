# BD Shop Optimizer

A high-performance WooCommerce optimization suite designed specifically for the Bangladesh e-commerce market. This plugin improves conversion rates by simplifying the checkout process, localizing the user experience, and providing a custom size-based inventory system for Simple Products.

## 🌟 Key Features

### 1. Custom Size Inventory Management
Traditional WooCommerce requires "Variable Products" for different sizes, which can be slow and complex. This plugin adds a custom stock management system to **Simple Products**:
* **Per-Size Stock Tracking:** Manage quantities for sizes 39 through 45 directly from a custom metabox.
* **Smart Redirects:** Automatically replaces the "Add to Cart" button on shop pages with a "Select Size" link to prevent customer errors.
* **Dynamic Validation:** Prevents users from adding out-of-stock sizes to the cart.
* **Auto-Deduction:** Hooks into `woocommerce_reduce_order_stock` to update size-specific quantities when an order is placed.

### 2. Bangla Localization & Typography
* **Custom Product Tab:** Adds an "বিস্তারিত" (Details) tab to provide product info in the local language.
* **Font Optimization:** Enqueues the **Hind Siliguri** Google Font specifically for Bangla content to ensure a professional look.

### 3. Conversion Rate Optimization (CRO)
* **Minimalist Checkout:** Removes 7+ unnecessary fields (Postcode, Company, Address Line 2, etc.) to reduce checkout friction.
* **Localized Checkout Labels:** Replaces standard labels with Bangla equivalents like "আপনার নাম" (Your Name).
* **Speed-Focused Logic:** Forces Bangladesh (BD) as the default country and disables unnecessary shipping calculations.

### 4. Size Chart Modal
* **Interactive Guide:** Displays a popup size guide using Vanilla JavaScript to keep the frontend lightweight.
* **Admin Flexibility:** Allows shop managers to set unique size chart images and button text for every product.

## 🛠️ Technical Implementation
* **WordPress Hook System:** Extensive use of `add_filter` and `add_action` for modularity.
* **Security:** Implements `wp_kses_post`, `sanitize_text_field`, and `esc_url_raw` for data integrity.
* **Asset Management:** Uses `wp_enqueue_scripts` to load CSS/JS only on required pages (Product/Checkout/Shop), optimizing site performance.

## 📁 Repository Structure
```text
bd-shop-optimizer/
├── woocommerce-bd-conversion-kit.php   # Main Logic & Hooks
├── README.md                           # Documentation
└── assets/
    ├── css/
    │   └── wc-extra-styles.css         # UI & Typography
    └── js/
        ├── size-chart.js               # Modal Logic
        └── shop-redirect.js            # Shop Page UX
