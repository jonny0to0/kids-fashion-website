# Dynamic Product Attributes System

## Overview

This system implements a flexible, category-based product attribute management system that allows different product types to have different attributes without changing the form structure. This is the professional approach used by major e-commerce platforms like Amazon and Flipkart.

## Features

1. **Category-Based Attributes**: Each category can have its own set of attributes
2. **Dynamic Form Loading**: Product form automatically loads relevant attributes when a category is selected
3. **Multiple Attribute Types**: Supports text, select, number, textarea, and color inputs
4. **Product Variants**: Manage size-color combinations with individual stock and pricing
5. **Scalable Design**: Add new categories and attributes without code changes

## Database Setup

### Step 1: Run the Migration

Execute the SQL file to create the necessary tables:

```sql
-- Run this file
database/add_attributes_system.sql
```

This creates:
- `category_attributes` - Defines attributes for each category
- `product_attributes` - Stores attribute values for products
- Updates `product_variants` table with additional indexes

### Step 2: Add Sample Attributes (Optional)

You can add sample attributes for your categories using the template in:

```sql
database/sample_attributes.sql
```

Example for Footwear category:
```sql
INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
(YOUR_CATEGORY_ID, 'Size', 'select', '["6","7","8","9","10","11","12"]', TRUE, 1),
(YOUR_CATEGORY_ID, 'Color', 'select', '["Black","White","Brown","Blue","Red"]', TRUE, 2),
(YOUR_CATEGORY_ID, 'Material', 'select', '["Leather","Canvas","Synthetic"]', FALSE, 3);
```

## How It Works

### 1. Category Attributes

Attributes are defined per category in the `category_attributes` table:

- **attribute_name**: Display name (e.g., "Size", "Color")
- **attribute_type**: Input type (`text`, `select`, `number`, `textarea`, `color`)
- **attribute_options**: JSON array for select type (e.g., `["S", "M", "L"]`)
- **is_required**: Whether the attribute is mandatory
- **display_order**: Order in which attributes appear

### 2. Product Form Behavior

When adding/editing a product:

1. Admin selects a category
2. System fetches attributes for that category via AJAX
3. Attribute fields appear dynamically
4. Admin fills in product details and attributes
5. Data is saved to `products` and `product_attributes` tables

### 3. Product Variants

Products can have multiple variants (size-color combinations):

- Each variant has its own stock quantity
- Each variant can have an additional price
- Variants are managed in the "Product Variants" section

## Usage

### Adding a Product

1. Go to Admin → Products → Add Product
2. Fill in basic information (name, category, price, etc.)
3. Select a category - attributes will load automatically
4. Fill in category-specific attributes
5. Add variants if needed (size, color, stock, price)
6. Upload product images
7. Save

### Managing Attributes

Currently, attributes are managed directly in the database. To add attributes for a category:

```sql
INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) 
VALUES (1, 'Size', 'select', '["XS","S","M","L","XL"]', TRUE, 1);
```

**Attribute Types:**
- `text` - Simple text input
- `select` - Dropdown (requires `attribute_options` as JSON array)
- `number` - Number input
- `textarea` - Multi-line text
- `color` - Color picker with text input

## API Endpoints

### Get Category Attributes

**Endpoint:** `/admin/get-category-attributes`

**Method:** GET

**Parameters:**
- `category_id` (required) - The category ID

**Response:**
```json
{
  "success": true,
  "attributes": [
    {
      "attribute_id": 1,
      "category_id": 1,
      "attribute_name": "Size",
      "attribute_type": "select",
      "attribute_options": "[\"S\",\"M\",\"L\"]",
      "options": ["S", "M", "L"],
      "is_required": true,
      "display_order": 1
    }
  ]
}
```

## File Structure

### Models

- `app/models/Attribute.php` - Handles category attributes and product attribute values
- `app/models/Product.php` - Updated with variant and attribute methods
- `app/models/Category.php` - Updated with attribute retrieval methods

### Controllers

- `app/controllers/AdminController.php` - Updated with:
  - `getCategoryAttributes()` - AJAX endpoint for fetching attributes
  - `handleProductAttributes()` - Saves product attributes
  - `handleProductVariants()` - Saves product variants

### Views

- `app/views/admin/product_form.php` - Updated with:
  - Dynamic attribute loading
  - Variant management UI
  - JavaScript for AJAX attribute loading

## Database Schema

### category_attributes
```sql
- attribute_id (PK)
- category_id (FK)
- attribute_name
- attribute_type (enum: text, select, number, textarea, color)
- attribute_options (JSON for select type)
- is_required (boolean)
- display_order
- is_active (boolean)
```

### product_attributes
```sql
- product_attribute_id (PK)
- product_id (FK)
- attribute_id (FK)
- attribute_value (TEXT)
```

### product_variants
```sql
- variant_id (PK)
- product_id (FK)
- size
- color
- color_code
- additional_price
- stock_quantity
- sku
- is_active
```

## Benefits

1. **Scalability**: Add new product types without code changes
2. **Flexibility**: Each category can have unique attributes
3. **User-Friendly**: Single form adapts to product type
4. **Professional**: Matches industry-standard e-commerce platforms
5. **Maintainable**: Clean separation of concerns

## Future Enhancements

Potential improvements:
- Admin UI for managing category attributes (without SQL)
- Attribute validation rules
- Attribute groups/categories
- Bulk attribute assignment
- Attribute templates

## Troubleshooting

### Attributes not loading

1. Check browser console for JavaScript errors
2. Verify the route `/admin/get-category-attributes` is accessible
3. Check database - ensure `category_attributes` table exists
4. Verify category has attributes assigned

### Variants not saving

1. Check form data is being submitted correctly
2. Verify `product_variants` table structure
3. Check for validation errors in form submission

### Attribute values not displaying in edit mode

1. Ensure `product_attributes` table has data
2. Check attribute IDs match between category and product attributes
3. Verify JavaScript is loading existing values correctly

