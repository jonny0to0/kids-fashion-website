# Admin Dashboard Navigation Standards

This document outlines the enterprise-level navigation and linking standards implemented for the admin dashboard.

## Overview

Every admin sub-page follows a strict parent-child relationship model to ensure:
- Clear navigation hierarchy
- Predictable user experience
- Consistent URL structure
- Proper permission and data validation

## Core Rules

### 1. Mandatory Navigation Rule (Single Parent Link)

**Rule:** Every sub page must be connected to exactly ONE parent page.

**Benefits:**
- Prevents confusion
- Improves usability
- Matches enterprise dashboards

**Example:**
```
Parent: Category List (/admin/categories)
Child: Edit Category (/admin/categories/edit/12)
```

### 2. Breadcrumb System (Required)

**Format:** `Dashboard > Category > Edit Category`

**Rules:**
- Breadcrumb must be clickable except the current page
- Breadcrumb always reflects the actual navigation hierarchy
- No multiple parent paths allowed

**Implementation:**
```php
renderBreadcrumb([
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Categories', 'url' => '/admin/categories'],
    ['label' => 'Edit Category'] // Current page (no URL)
]);
```

### 3. Back to Parent Button (Required)

Each sub page must include a visible "Back" action.

**Button Placement:**
- Top-left (preferred)
- Or next to page title

**Label Standard:**
```
← Back to Categories
```

**Rule:** This button must redirect to the parent listing page, not browser history.

**Implementation:**
```php
renderBackButton('Categories', '/admin/categories', 'top-left');
```

### 4. URL Structure Rules (SEO + Clarity)

**Correct Structure:**
```
/admin/categories
/admin/categories/edit/12
/admin/products
/admin/products/edit/5
```

**Rules:**
- Child URL contains parent resource
- No flat or unrelated URLs
- ID-based or slug-based allowed

**❌ Bad:**
```
/admin/edit-category/12  (No parent context)
/admin/category-12       (Unclear hierarchy)
```

**✅ Good:**
```
/admin/categories/edit/12
/admin/products/add
```

### 5. Permission & Access Control

**Rules:**
- If admin has no access to parent page → child page access denied
- Direct URL access without permission → redirect to parent

**Example:**
```
403 – You do not have permission to edit categories
```

**Implementation:**
All admin controllers check permissions in `requireAdmin()` method, which is called in the constructor.

### 6. Data Dependency Rule

**Rule:** Child page cannot exist without parent data.

**Example:**
If Category ID is invalid or deleted → redirect to Category List

**Fallback Message:**
```
Category not found. Redirecting to category list.
```

**Implementation:**
```php
// In AdminController
private function validateDataDependency($data, $parentUrl, $entityName = 'Item') {
    if (!$data || empty($data)) {
        Session::setFlash('error', $entityName . ' not found. Redirecting to ' . strtolower($entityName) . ' list.');
        header('Location: ' . SITE_URL . $parentUrl);
        exit;
    }
    return true;
}
```

### 7. Tab & Sub-navigation Rule (Optional but Recommended)

If a parent has multiple sub pages:

```
Category
 ├── Edit Details
 ├── SEO
 ├── Attributes
 └── Status
```

**Rules:**
- All tabs belong to same parent
- Tabs do NOT create new parent links
- Parent breadcrumb remains unchanged

### 8. Admin UX Rules Followed by Big Companies

✅ One parent only  
✅ Clear breadcrumb trail  
✅ Predictable URLs  
✅ Consistent back navigation  
✅ Permission-based access  
✅ No orphan pages

### 9. Recommended Standard (Enterprise Level)

| Element | Required |
|---------|----------|
| Parent Page | ✅ |
| Single Parent Link | ✅ |
| Breadcrumb | ✅ |
| Back Button | ✅ |
| Structured URL | ✅ |
| Permission Check | ✅ |

### 10. Complete Flow Example

```
Dashboard
  → Categories
      → Edit Category
          ↳ Back to Categories
```

**Breadcrumb:**
```
Dashboard > Categories > Edit Category
```

**URL:**
```
/admin/categories/edit/12
```

## Implementation Files

### Helper Components

1. **`app/views/admin/_breadcrumb.php`**
   - Renders breadcrumb navigation
   - Function: `renderBreadcrumb($items)`

2. **`app/views/admin/_back_button.php`**
   - Renders back button
   - Function: `renderBackButton($parentLabel, $parentUrl, $position)`

### Controller Methods

1. **`AdminController::validateDataDependency()`**
   - Validates parent data exists
   - Redirects to parent if invalid

2. **`AdminController::requireAdmin()`**
   - Checks admin permissions
   - Redirects to login if unauthorized

## Usage Examples

### Category Edit Page

```php
<?php
// Include helpers
require_once __DIR__ . '/_breadcrumb.php';
require_once __DIR__ . '/_back_button.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php
    // Render breadcrumb
    renderBreadcrumb([
        ['label' => 'Dashboard', 'url' => '/admin'],
        ['label' => 'Categories', 'url' => '/admin/categories'],
        ['label' => 'Edit Category']
    ]);
    ?>
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Edit Category</h1>
        <?php renderBackButton('Categories', '/admin/categories', 'top-left'); ?>
    </div>
    
    <!-- Form content -->
</div>
```

### Controller Validation

```php
public function categoryEdit($id) {
    // Validate ID
    if (!is_numeric($id)) {
        Session::setFlash('error', 'Invalid category ID');
        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }
    
    $category = $this->categoryModel->getById((int)$id);
    
    // Validate data dependency
    if (!$category) {
        Session::setFlash('error', 'Category not found. Redirecting to category list.');
        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }
    
    // Continue with edit logic...
}
```

## Current Implementation Status

✅ **Dashboard** - Breadcrumb implemented  
✅ **Categories** - List, Add, Edit with breadcrumbs and back buttons  
✅ **Products** - List, Add, Edit with breadcrumbs and back buttons  
✅ **Attributes** - List, Add, Edit with breadcrumbs and back buttons  
✅ **Hero Banners** - List, Add, Edit with breadcrumbs and back buttons  
✅ **Attribute Groups** - List, Add, Edit, Assign with breadcrumbs and back buttons  

## Best Practices

1. **Always include breadcrumb** on every admin page
2. **Always include back button** on sub-pages (edit, add, etc.)
3. **Validate data dependencies** in controllers before rendering views
4. **Use consistent URL structure** following parent-child pattern
5. **Redirect to parent** when data is invalid or missing
6. **Check permissions** at controller level, not just view level

## Maintenance

When adding new admin pages:

1. Create parent listing page first
2. Add breadcrumb to listing page
3. Create child pages (edit, add, etc.)
4. Add breadcrumb and back button to child pages
5. Implement data validation in controller
6. Test navigation flow

---

**Last Updated:** 2024  
**Version:** 1.0

