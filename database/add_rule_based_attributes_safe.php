<?php
/**
 * Safe migration script to add rule-based attributes columns
 * This script checks if columns exist before adding them
 */

require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting migration: Add rule-based attributes columns...\n";
    
    // Get existing columns
    $result = $db->query('DESCRIBE category_attributes');
    $existingColumns = [];
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    // Columns to add
    $columnsToAdd = [
        [
            'name' => 'depends_on',
            'definition' => 'INT NULL COMMENT \'Parent attribute ID that this attribute depends on\''
        ],
        [
            'name' => 'show_when',
            'definition' => 'TEXT NULL COMMENT \'JSON condition: {"value": "Sports"} or {"operator": "in", "values": ["Sports", "Formal"]}\''
        ],
        [
            'name' => 'is_filterable',
            'definition' => 'BOOLEAN DEFAULT FALSE COMMENT \'Whether this attribute can be used for filtering products\''
        ],
        [
            'name' => 'is_variant',
            'definition' => 'BOOLEAN DEFAULT FALSE COMMENT \'Whether this attribute is used for product variants\''
        ]
    ];
    
    // Add missing columns
    foreach ($columnsToAdd as $column) {
        if (!in_array($column['name'], $existingColumns)) {
            echo "Adding column: {$column['name']}...\n";
            $db->exec("ALTER TABLE category_attributes ADD COLUMN {$column['name']} {$column['definition']}");
            echo "  ✓ Column {$column['name']} added successfully\n";
        } else {
            echo "  - Column {$column['name']} already exists, skipping...\n";
        }
    }
    
    // Add index for depends_on if it doesn't exist
    try {
        $db->exec("CREATE INDEX idx_depends_on ON category_attributes (depends_on)");
        echo "  ✓ Index idx_depends_on created successfully\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "  - Index idx_depends_on already exists, skipping...\n";
        } else {
            throw $e;
        }
    }
    
    // Add foreign key for depends_on if it doesn't exist
    try {
        $db->exec("ALTER TABLE category_attributes ADD FOREIGN KEY (depends_on) REFERENCES category_attributes(attribute_id) ON DELETE SET NULL");
        echo "  ✓ Foreign key constraint added successfully\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "  - Foreign key constraint already exists, skipping...\n";
        } else {
            throw $e;
        }
    }
    
    // Update existing attributes to have default values
    $db->exec("UPDATE category_attributes SET is_filterable = 0, is_variant = 0 WHERE is_filterable IS NULL OR is_variant IS NULL");
    echo "  ✓ Updated existing attributes with default values\n";
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}


