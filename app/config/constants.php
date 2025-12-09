<?php
/**
 * Global Constants
 * Application-wide constants for statuses, enums, etc.
 */

// User Types
define('USER_TYPE_CUSTOMER', 'customer');
define('USER_TYPE_ADMIN', 'admin');
define('USER_TYPE_VENDOR', 'vendor');

// User Status
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_DELETED', 'deleted');

// Product Status
define('PRODUCT_STATUS_ACTIVE', 'active');
define('PRODUCT_STATUS_INACTIVE', 'inactive');
define('PRODUCT_STATUS_OUT_OF_STOCK', 'out_of_stock');

// Order Status
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_CONFIRMED', 'confirmed');
define('ORDER_STATUS_PROCESSING', 'processing');
define('ORDER_STATUS_SHIPPED', 'shipped');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');
define('ORDER_STATUS_RETURNED', 'returned');

// Payment Status
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_PAID', 'paid');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Payment Methods
define('PAYMENT_METHOD_COD', 'cod');
define('PAYMENT_METHOD_ONLINE', 'online');
define('PAYMENT_METHOD_WALLET', 'wallet');
define('PAYMENT_METHOD_UPI', 'upi');

// Address Types
define('ADDRESS_TYPE_HOME', 'home');
define('ADDRESS_TYPE_WORK', 'work');
define('ADDRESS_TYPE_OTHER', 'other');

// Age Groups
define('AGE_GROUP_0_1', '0-1');
define('AGE_GROUP_1_3', '1-3');
define('AGE_GROUP_3_5', '3-5');
define('AGE_GROUP_5_8', '5-8');
define('AGE_GROUP_8_12', '8-12');
define('AGE_GROUP_12_14', '12-14');

// Gender Types
define('GENDER_BOY', 'boy');
define('GENDER_GIRL', 'girl');
define('GENDER_UNISEX', 'unisex');

// Notification Types
define('NOTIFICATION_TYPE_ORDER', 'order');
define('NOTIFICATION_TYPE_PROMOTION', 'promotion');
define('NOTIFICATION_TYPE_SYSTEM', 'system');
define('NOTIFICATION_TYPE_PRODUCT', 'product');

// Discount Types
define('DISCOUNT_TYPE_PERCENTAGE', 'percentage');
define('DISCOUNT_TYPE_FIXED', 'fixed');

