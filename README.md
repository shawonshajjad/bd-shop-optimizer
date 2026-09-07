# WooCommerce BD Conversion Kit

A custom WooCommerce extension built for Bangladesh-focused stores. It combines a lightweight size-inventory workflow for simple products, Bangla product content, per-product size charts and a streamlined checkout experience.

## Core Features

### Custom Size Inventory for Simple Products

- Adds size-level inventory fields for sizes **39–45** to WooCommerce products
- Lets customers select a size before adding a simple product to the cart
- Disables sizes whose custom quantity is zero
- Validates that a size has been selected before add-to-cart
- Carries the selected size into cart and order-line metadata
- Deducts the custom size quantity when WooCommerce reduces order stock

This provides a focused alternative to creating a full variable-product configuration when the store only needs a fixed shoe-size inventory workflow.

### Bangladesh-Focused Product Experience

- Adds a dedicated **বিস্তারিত** product tab for Bangla content
- Uses **Hind Siliguri** typography on product pages
- Sets Bangladesh (`BD`) as the default checkout country
- Localizes key checkout labels in Bangla

### Size Chart

Each product can have its own:

- Size-chart button text
- Size-chart image URL
- Frontend modal displaying the configured chart

### Streamlined Checkout

The plugin simplifies the billing form by removing several fields from the standard WooCommerce checkout and relabeling key customer fields for the intended local workflow. It also disables the separate shipping-address requirement used by the target store setup.

## Request / Data Flow

1. A shop manager enters size stock, Bangla description and size-chart settings on a product.
2. The product page renders the available size selector and optional size-chart modal.
3. Add-to-cart validation requires a size for simple products.
4. The selected size is stored in cart-item data and then copied to the WooCommerce order line.
5. When WooCommerce reduces order stock, the matching custom size quantity is reduced as well.

## Project Structure

```text
bd-shop-optimizer/
├── woocommerce-bd-conversion-kit.php
├── README.md
└── assets/
    ├── css/
    │   └── wc-extra-styles.css
    └── js/
        ├── size-chart.js
        └── shop-redirect.js
```

## Technical Stack

- WordPress hooks and filters
- WooCommerce product, cart, checkout and order hooks
- WordPress post metadata
- PHP
- jQuery / Vanilla JavaScript
- CSS

## Installation

1. Install and activate WooCommerce.
2. Download or clone this repository into `wp-content/plugins/`.
3. Activate **WooCommerce BD Conversion Kit**.
4. Configure the custom product fields from the WooCommerce product editor.

## Implementation Notes

This plugin is intentionally opinionated for a specific Bangladesh e-commerce workflow. The built-in size set is 39–45 and the checkout simplification assumes the target store does not require WooCommerce's normal separate shipping-address flow. Review those assumptions before using it in a different store.

## Portfolio Note

The project demonstrates custom WooCommerce product metadata, cart validation, order metadata, stock lifecycle integration, frontend UI behavior and localized checkout customization without replacing WooCommerce core.

## Author

**Sajjadur Rahaman Shawon**  
GitHub: https://github.com/shawonshajjad
