# Product Image Guidelines for Kids Bazaar E-commerce

## Overview

This document outlines the ideal image requirements and rules for product images in the Kids Bazaar e-commerce platform. Following these guidelines ensures consistent, professional, and trustworthy product listings that help customers make informed purchasing decisions.

---

## 📸 Ideal Product Image Rules

### 1️⃣ Main Image (Mandatory)

**Purpose:** First impression & listing consistency

**Rules:**
- ✅ Pure white background (#FFFFFF)
- ✅ Product should cover 85–90% of the image
- ✅ No text, no watermark, no props
- ✅ Centered and properly cropped (no cutting edges)
- ✅ For baby clothing:
  - Baby wearing the product OR product laid flat (mannequin-style)
  - Full product visible (hood, zipper, legs)

**📐 Recommended Size:**
- **2000 × 2000 px** (minimum 1000 × 1000 px)
- Square (1:1 ratio)

**Technical Requirements:**
- Format: JPG, PNG, or WebP
- Quality: High resolution, optimized for web
- Aspect Ratio: 1:1 (square)

---

### 2️⃣ Lifestyle Image (Very Important)

**Purpose:** Build trust & emotional connection

**Rules:**
- ✅ Show product in real-life use
- ✅ Natural lighting preferred
- ✅ Baby on bed / stroller / room
- ✅ Background should be clean, not distracting
- ✅ Helps parents imagine real usage

**📐 Recommended Size:**
- 2000 × 2000 px or 2000 × 1500 px
- Square (1:1) or 4:3 ratio

---

### 3️⃣ Fabric & Quality Close-Up

**Purpose:** Answer "Is it soft & warm?"

**Show:**
- ✅ Fabric texture (flannel softness)
- ✅ Zipper quality
- ✅ Stitching details
- ✅ Material quality

**📝 Note:** Optional small tag like "Soft Flannel Fabric" allowed only in gallery images, NOT in main image.

**📐 Recommended Size:**
- 1500 × 1500 px minimum
- Square (1:1) or close-up crop

---

### 4️⃣ Fit & Size Reference Image

**Purpose:** Reduce returns and increase customer confidence

**Examples:**
- ✅ Baby standing or lying straight
- ✅ Caption like: "Baby age: 9 months | Wearing size: 9–12M"
- ✅ Clear size reference visible

**💡 Why Important:** Parents trust size reference images a lot - helps reduce returns and increase conversions.

**📐 Recommended Size:**
- 2000 × 2000 px or 2000 × 1500 px
- Square or 4:3 ratio

---

### 5️⃣ Back / Side View

**Purpose:** Completeness and full product visibility

**Show:**
- ✅ Back design details
- ✅ Hood shape (for clothing)
- ✅ Tail or costume detail (important for cosplay/character items)
- ✅ Side angles showing depth/3D aspects

**📐 Recommended Size:**
- 2000 × 2000 px
- Square (1:1) ratio

---

### 6️⃣ Color / Variant Image

**Purpose:** Increase exploration and showcase options

**Options:**
- ✅ One image showing all available colors
- ✅ OR main image + color swatches below
- ✅ Badge like: "Available in 4 colors"

**💡 Tip:** This helps customers discover color variants they might not have considered.

**📐 Recommended Size:**
- 2000 × 2000 px or 2000 × 1200 px
- Square or 5:3 ratio

---

### 7️⃣ Image Consistency Rule (Very Important)

**All products should:**
- ✅ Use same background style (white for main image)
- ✅ Same angle for main images (consistent positioning)
- ✅ Same lighting & spacing
- ✅ Consistent image quality across all products

**👉 Why:** This makes your site look professional & trustworthy, creating a cohesive brand experience.

---

## ❌ What to Avoid

### Image Quality Issues
- ❌ Blurry images
- ❌ Overexposed lighting
- ❌ Too much background (product should be the focus)
- ❌ Cropped heads or feet (for baby clothing)
- ❌ Different image styles per product
- ❌ Low resolution images
- ❌ Distorted aspect ratios

### Content Issues
- ❌ Watermarks on main image
- ❌ Text overlays on main image
- ❌ Props that distract from product
- ❌ Cluttered backgrounds (main image)
- ❌ Multiple products in one image (main image)

---

## 📋 Ideal Image Count per Product

### Minimum Requirements
- **Minimum: 4 images**
  - Main image (white background) ✅ **Required**
  - Lifestyle image ✅ **Highly Recommended**
  - Fabric close-up ✅ **Recommended**
  - Back/side view ✅ **Recommended**

### Best Practice
- **Best: 6–7 images**
  1. Main image (white background) ✅
  2. Lifestyle image ✅
  3. Fabric close-up ✅
  4. Fit / size reference ✅
  5. Back or side view ✅
  6. Color variants ✅
  7. Packaging (optional) ✅

---

## 💡 Category-Specific Guidelines

### Baby Clothing
- **Mandatory:** Lifestyle + Size Reference
- **Recommended:** Fabric close-up showing softness
- **Important:** Show full product (hood, zipper, legs visible)
- **Style:** Baby wearing product OR flat lay

### Electronics & Toys
- **Mandatory:** Back view showing ports/controls
- **Recommended:** Product in use (lifestyle)
- **Important:** Clear size reference next to common objects

### Accessories
- **Mandatory:** Multiple angles (front, back, side)
- **Recommended:** Close-up of details/texture
- **Important:** Size comparison reference

---

## 🔧 Technical Specifications

### File Formats
- **Preferred:** WebP (best compression, modern browsers)
- **Accepted:** JPG/JPEG, PNG, WebP
- **Maximum File Size:** As configured in system (typically 5-10 MB)

### Dimensions
- **Main Image:** 2000 × 2000 px (1:1 ratio) - Minimum 1000 × 1000 px
- **Gallery Images:** 2000 × 2000 px or 2000 × 1500 px
- **Aspect Ratios:** 1:1 (square) preferred, 4:3 acceptable for lifestyle

### Optimization
- Images should be optimized for web (compressed but high quality)
- Use appropriate format (WebP when possible)
- Ensure fast loading times
- Balance quality vs. file size

---

## 📝 Implementation Notes

### Current System Support

The current `product_images` table structure:
```sql
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    display_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

**Current Features:**
- ✅ Multiple images per product
- ✅ Primary image flag (`is_primary`)
- ✅ Display order (`display_order`)
- ✅ Alt text support

**Future Enhancements (Optional):**
- Consider adding `image_type` field (main, lifestyle, close-up, size-reference, back-view, variant)
- Category-specific image requirements validation
- Automated image quality checks
- Background color validation for main images

---

## 🎯 Image Upload Workflow

### Step-by-Step Process

1. **Upload Main Image** (White background, product centered)
   - First image uploaded becomes primary if none exists
   - Must be square (1:1 ratio)
   - Pure white background (#FFFFFF)

2. **Upload Lifestyle Image**
   - Real-life usage scenario
   - Natural lighting
   - Clean background

3. **Upload Additional Images**
   - Fabric close-up
   - Size reference
   - Back/side view
   - Color variants

4. **Set Display Order**
   - Arrange images in logical sequence
   - Main image = display_order 0 (or lowest)
   - Gallery images follow in order

5. **Add Alt Text**
   - Descriptive alt text for accessibility
   - Include product name and image type
   - Example: "Blue baby romper - Lifestyle image showing baby wearing product"

---

## ✅ Quality Checklist

Before publishing a product, verify:

- [ ] Main image has pure white background (#FFFFFF)
- [ ] Main image is square (1:1 ratio)
- [ ] Main image shows full product (no cropped edges)
- [ ] Minimum 4 images uploaded
- [ ] At least one lifestyle image included
- [ ] Images are high quality (not blurry)
- [ ] Images are properly sized (2000×2000px recommended)
- [ ] Alt text added for accessibility
- [ ] Display order set correctly
- [ ] Images match product description
- [ ] Consistent style across all images

---

## 📚 Examples

### Good Main Image ✅
- White background (#FFFFFF)
- Product centered, covers 85-90% of frame
- Full product visible
- High quality, sharp focus
- Square aspect ratio (1:1)

### Good Lifestyle Image ✅
- Baby/product in natural setting
- Clean, uncluttered background
- Good lighting
- Shows product being used/enjoyed
- High quality

### Bad Main Image ❌
- Colored background
- Product too small in frame
- Blurry or low quality
- Cropped edges
- Watermark or text overlay

---

## 🚀 Benefits of Following Guidelines

1. **Increased Conversions**
   - Professional appearance builds trust
   - Complete image set answers customer questions
   - Size reference reduces return rate

2. **Better User Experience**
   - Fast-loading, optimized images
   - Consistent, professional look
   - Clear product information

3. **SEO & Marketing**
   - High-quality images improve search ranking
   - Social media ready (square format)
   - Better product listings

4. **Reduced Support**
   - Size reference images reduce size-related questions
   - Complete image set answers common questions
   - Less confusion = fewer support tickets

---

## 📞 Support & Questions

For questions about image guidelines or technical issues:
- Refer to this document first
- Check existing product examples in the system
- Contact the development team for clarification

---

**Last Updated:** 2025-01-15  
**Version:** 1.0


