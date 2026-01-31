# Rule-Based Attributes System

This system implements Flipkart-style conditional attributes that show/hide based on parent attribute values.

## 🎯 Core Concept

Attributes can now have **dependencies** and **conditions**:
- **Base Attributes**: Always visible (e.g., Brand, Gender, Size, Color)
- **Dependent Attributes**: Show only when parent attribute has specific value(s)

## 📋 Example: Footwear Category

### Base Attributes (Always Visible)
- Brand
- Gender
- Size
- Color
- Sole Material
- Upper Material
- **Shoe Type** (Select: Casual, Sports, Formal)

### Dependent Attributes

| Attribute | Depends On | Show When |
|-----------|------------|-----------|
| Grip Type | Shoe Type | = "Sports" |
| Weight | Shoe Type | = "Sports" |
| Toe Shape | Shoe Type | = "Formal" |
| Heel Height | Shoe Type | = "Formal" |

## 🗄️ Database Schema

New columns added to `category_attributes` table:

```sql
depends_on INT NULL          -- Parent attribute ID
show_when TEXT NULL          -- JSON condition
is_filterable BOOLEAN         -- Use for product filtering
is_variant BOOLEAN           -- Use for product variants
```

### Show When JSON Format

**Single Value Match:**
```json
{"value": "Sports"}
```

**Multiple Values (IN):**
```json
{"operator": "in", "values": ["Sports", "Formal"]}
```

**Exclude Values (NOT IN):**
```json
{"operator": "not_in", "values": ["Casual"]}
```

## 🔧 How to Use

### 1. Create Base Attribute

1. Go to **Admin → Attributes → Add**
2. Fill in:
   - Attribute Name: `Shoe Type`
   - Attribute Type: `Select`
   - Options: `Casual, Sports, Formal`
   - **Depends On**: None (leave empty)
3. Save

### 2. Create Dependent Attribute

1. Go to **Admin → Attributes → Add**
2. Fill in:
   - Attribute Name: `Grip Type`
   - Attribute Type: `Select`
   - Options: `High, Medium, Low`
   - **Depends On**: Select `Shoe Type`
   - **Show When Type**: `Equals specific value`
   - **Value**: `Sports`
3. Save

### 3. Frontend Behavior

When creating/editing a product:
1. Base attributes are always visible
2. When user selects "Sports" in "Shoe Type" dropdown:
   - ✅ "Grip Type" appears
   - ✅ "Weight" appears
3. When user selects "Formal":
   - ✅ "Toe Shape" appears
   - ✅ "Heel Height" appears
4. When user selects "Casual":
   - ❌ All dependent attributes are hidden

## 📝 Code Structure

### Backend

**Model (`app/models/Attribute.php`):**
- `shouldShowAttribute()` - Checks if attribute should be visible
- `getDependentAttributes()` - Gets all attributes depending on a parent
- Automatic JSON decoding of `show_when` field

**Controller (`app/controllers/AdminController.php`):**
- `getCategoryAttributes()` - Returns attributes with dependency info
- `attributeAdd()` / `attributeEdit()` - Handles dependency fields

### Frontend

**Product Form (`app/views/admin/product_form.php`):**
- `setupAttributeDependencies()` - Sets up event listeners
- `checkDependency()` - Evaluates conditions and shows/hides fields
- Automatic hiding of dependent fields on page load

**Attribute Form (`app/views/admin/attributes/form.php`):**
- Dependency configuration UI
- Condition type selection (equals, in, not_in)
- Value input fields

## 🚀 Setup Instructions

1. **Run Database Migration:**
   ```sql
   -- Run: database/add_rule_based_attributes.sql
   ```

2. **Clear Cache** (if applicable)

3. **Test:**
   - Create a base attribute (e.g., "Shoe Type")
   - Create a dependent attribute (e.g., "Grip Type" depends on "Shoe Type" = "Sports")
   - Create/edit a product and verify conditional showing

## 💡 Best Practices

1. **Base attributes should be select type** for better UX
2. **Use clear, descriptive condition values** (exact match required)
3. **Test all condition combinations** before going live
4. **Use "is_filterable"** for attributes customers can filter by
5. **Use "is_variant"** for attributes that create product variants

## 🔍 Troubleshooting

**Attribute not showing:**
- Check `depends_on` is set correctly
- Verify `show_when` JSON is valid
- Ensure parent attribute value matches exactly (case-sensitive)
- Check browser console for JavaScript errors

**Attribute always showing:**
- Verify `depends_on` is not NULL
- Check `show_when` condition is set

**Condition not working:**
- Verify JSON format is correct
- Check value matching (exact, case-sensitive)
- Ensure parent attribute is select type

## 📚 Related Files

- `database/add_rule_based_attributes.sql` - Database migration
- `app/models/Attribute.php` - Model with dependency logic
- `app/controllers/AdminController.php` - Controller handling
- `app/views/admin/product_form.php` - Product form with conditional logic
- `app/views/admin/attributes/form.php` - Attribute configuration form


