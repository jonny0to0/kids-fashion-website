# Attribute Groups System with Category Inheritance

## Overview

This system implements a scalable, inheritance-based attribute management system for e-commerce products. Instead of manually assigning attributes to every category, attributes are organized into **Attribute Groups** that can be assigned to categories, with automatic inheritance from parent to child categories.

## Core Concepts

### 1. Attribute Groups

Attributes are organized into reusable groups (e.g., "Common", "Fashion Basic", "Footwear Specs"). Each group contains related attributes.

**Example Groups:**
- **Common**: Brand, SKU, Price, Stock
- **Fashion Basic**: Color, Size, Fabric
- **Footwear Specs**: Shoe Size, Sole Material
- **Electronics Specs**: RAM, Storage, Battery
- **Shipping**: Weight, Dimensions

### 2. Category → Attribute Group Assignment

Categories are assigned **Attribute Groups** (not individual attributes). This allows:
- Reusing the same group across multiple categories
- Easy management - change group once, affects all assigned categories
- Better organization and scalability

### 3. Category Inheritance

Child categories automatically inherit attribute groups from their parent categories.

**Example:**
```
Fashion (Parent)
 ├── Men (inherits Fashion's groups)
 │    ├── Shirts (inherits Men's + Fashion's groups)
 │    └── T-Shirts (inherits Men's + Fashion's groups)
 └── Women (inherits Fashion's groups)
      ├── Dresses (inherits Women's + Fashion's groups)
      └── Tops (inherits Women's + Fashion's groups)
```

**Inheritance Rule:**
- Child categories automatically get all parent's attribute groups
- Child categories can have additional groups assigned directly
- Only extra attributes need to be added at child level

## Database Structure

### Tables

1. **attribute_groups** - Stores attribute group definitions
   - `group_id` (PK)
   - `group_name` (unique)
   - `description`
   - `display_order`
   - `is_active`

2. **category_attribute_groups** - Maps categories to attribute groups
   - `mapping_id` (PK)
   - `category_id` (FK)
   - `group_id` (FK)
   - `is_inherited` (boolean) - TRUE if inherited from parent

3. **category_attributes** - Stores individual attributes (updated)
   - `attribute_id` (PK)
   - `category_id` (FK, nullable - for legacy support)
   - `group_id` (FK, nullable - NEW)
   - `attribute_name`
   - `attribute_type`
   - `attribute_options` (JSON)
   - Other fields...

## Setup Instructions

### Step 1: Run Database Migration

Execute the migration SQL file:

```bash
mysql -u your_user -p your_database < database/add_attribute_groups_system.sql
```

Or import via phpMyAdmin.

This will:
- Create `attribute_groups` table
- Create `category_attribute_groups` mapping table
- Add `group_id` column to `category_attributes`
- Insert default attribute groups

### Step 2: Create Attribute Groups

1. Go to Admin → Attribute Groups
2. Click "Add New Group"
3. Create groups like:
   - Common
   - Fashion Basic
   - Footwear Specs
   - Electronics Specs
   - Shipping

### Step 3: Add Attributes to Groups

1. Go to Admin → Attributes → Add
2. Select an **Attribute Group** (recommended) or Category (legacy)
3. Create attributes within the group

### Step 4: Assign Groups to Categories

1. Go to Admin → Attribute Groups → Assign to Categories
2. Select a category
3. Select one or more attribute groups
4. Save

Child categories will automatically inherit parent's groups.

## Usage Examples

### Example 1: Fashion Category Hierarchy

**Setup:**
1. Create "Fashion Basic" group with attributes: Color, Size, Fabric
2. Create "Common" group with attributes: Brand, SKU, Price
3. Assign both groups to "Fashion" category

**Result:**
- Fashion category has: Color, Size, Fabric, Brand, SKU, Price
- Men category (child of Fashion) automatically has: Color, Size, Fabric, Brand, SKU, Price
- Shirts category (child of Men) automatically has: Color, Size, Fabric, Brand, SKU, Price

**Adding Extra Attributes:**
- If "Shirts" needs "Sleeve Type" and "Fit":
  1. Create "Shirt Specific" group with these attributes
  2. Assign "Shirt Specific" group directly to "Shirts" category
  3. Shirts now has: All inherited attributes + Sleeve Type + Fit

### Example 2: Footwear Category

**Setup:**
1. Create "Footwear Specs" group with: Shoe Size, Sole Material, Closure Type
2. Assign "Common" and "Footwear Specs" to "Footwear" category

**Result:**
- Footwear category has: Brand, SKU, Price, Shoe Size, Sole Material, Closure Type
- All child categories (Sneakers, Boots, etc.) inherit all these attributes

## API & Code Usage

### Get Attributes for Category (with Inheritance)

```php
require_once APP_PATH . '/models/Attribute.php';
$attributeModel = new CategoryAttribute();
$attributes = $attributeModel->getByCategoryWithInheritance($categoryId, $activeOnly = true);
```

This method:
1. Traverses up the category tree
2. Collects all attribute groups from category and parents
3. Returns all attributes from those groups

### Get Attribute Groups for Category

```php
$categoryModel = new Category();
$groups = $categoryModel->getAttributeGroupsWithInheritance($categoryId, $includeInherited = true);
```

### Assign Group to Category

```php
$attributeGroupModel = new AttributeGroup();
$attributeGroupModel->assignToCategory($categoryId, $groupId, $isInherited = false);
```

## Admin Interface

### Attribute Groups Management

- **URL**: `/admin/attribute-groups`
- **Features**:
  - List all attribute groups
  - Add/Edit/Delete groups
  - View attribute count per group
  - Assign groups to categories

### Attribute Management

- **URL**: `/admin/attributes`
- **Updated Features**:
  - Create attributes and assign to groups (recommended)
  - Legacy: Assign directly to categories (still supported)
  - Attributes can belong to either a group OR category (or both for migration)

### Category Management

- When assigning attribute groups to categories, inheritance is automatic
- Child categories show inherited groups in the attribute list

## Benefits

1. **Scalability**: Add new categories without manually assigning attributes
2. **Maintainability**: Update a group once, affects all assigned categories
3. **Organization**: Logical grouping of related attributes
4. **Inheritance**: Automatic attribute propagation down category tree
5. **Flexibility**: Mix of group-based and direct category assignment (during migration)

## Migration from Old System

The system supports both old and new approaches during migration:

- **Old**: Attributes directly assigned to categories (`category_id` in `category_attributes`)
- **New**: Attributes assigned to groups, groups assigned to categories

**Migration Path:**
1. Run migration SQL
2. Create attribute groups
3. Reassign existing attributes to groups (optional but recommended)
4. Assign groups to categories
5. Gradually move to group-based system

## Troubleshooting

### Attributes not showing for category

1. Check if category has attribute groups assigned
2. Check if parent categories have groups (inheritance)
3. Verify groups contain active attributes
4. Check `getByCategoryWithInheritance()` method

### Inheritance not working

1. Verify category hierarchy (parent_id relationships)
2. Check `category_attribute_groups` table for mappings
3. Ensure `is_inherited` flag is set correctly for inherited groups

### Group assignment not saving

1. Check foreign key constraints
2. Verify category and group IDs exist
3. Check for duplicate mappings (unique constraint)

## Files Modified/Created

### New Files
- `app/models/AttributeGroup.php` - Attribute group model
- `app/views/admin/attribute_groups/index.php` - Groups list
- `app/views/admin/attribute_groups/form.php` - Add/Edit group
- `app/views/admin/attribute_groups/assign.php` - Assign to categories
- `database/add_attribute_groups_system.sql` - Migration SQL
- `ATTRIBUTE_GROUPS_SYSTEM.md` - This documentation

### Modified Files
- `app/models/Attribute.php` (class: `CategoryAttribute`) - Added inheritance methods
- `app/models/Category.php` - Added inheritance methods
- `app/controllers/AdminController.php` - Added group management, updated attribute methods
- `app/views/admin/attributes/form.php` - Added group selection

## Future Enhancements

- Bulk attribute assignment
- Attribute group templates
- Visual category tree with inherited groups
- Group cloning/duplication
- Attribute validation rules per group

