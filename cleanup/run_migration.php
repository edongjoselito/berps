<?php
// Simple migration runner for POS product enhancements
// This script will add the industry-specific fields to the POS_products table

// Include CodeIgniter
define('BASEPATH', __DIR__ . '/system');
define('APPPATH', __DIR__ . '/application/');

// Load CodeIgniter
require_once BASEPATH . '/core/CodeIgniter.php';

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'berps';

try {
    // Connect to database
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully<br>";
    
    // Check if business_type column exists
    $result = $conn->query("SHOW COLUMNS FROM POS_products LIKE 'business_type'");
    
    if ($result->num_rows == 0) {
        echo "Adding industry-specific fields to POS_products table...<br>";
        
        // Add basic business type field
        $sql = "ALTER TABLE POS_products ADD COLUMN business_type VARCHAR(50) NULL DEFAULT 'general' AFTER status";
        if ($conn->query($sql)) {
            echo "Added business_type column<br>";
        }
        
        // Add brand field
        $sql = "ALTER TABLE POS_products ADD COLUMN brand VARCHAR(100) NULL AFTER business_type";
        if ($conn->query($sql)) {
            echo "Added brand column<br>";
        }
        
        // Add pharmacy fields
        $pharmacy_fields = [
            "generic_name VARCHAR(200) NULL AFTER brand",
            "dosage_form VARCHAR(50) NULL AFTER generic_name", 
            "strength VARCHAR(50) NULL AFTER dosage_form",
            "prescription_required TINYINT(1) NULL DEFAULT 0 AFTER strength",
            "fda_registration VARCHAR(100) NULL AFTER prescription_required",
            "drug_classification VARCHAR(50) NULL AFTER fda_registration",
            "storage_requirements VARCHAR(50) NULL AFTER drug_classification",
            "expiry_tracking TINYINT(1) NULL DEFAULT 0 AFTER storage_requirements"
        ];
        
        foreach ($pharmacy_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added pharmacy field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add grocery fields
        $grocery_fields = [
            "product_type VARCHAR(50) NULL AFTER expiry_tracking",
            "organic_certified TINYINT(1) NULL DEFAULT 0 AFTER product_type",
            "allergens TEXT NULL AFTER organic_certified",
            "nutritional_info TINYINT(1) NULL DEFAULT 0 AFTER allergens",
            "storage_instructions VARCHAR(50) NULL AFTER nutritional_info",
            "shelf_life INT(11) NULL AFTER storage_instructions",
            "country_of_origin VARCHAR(100) NULL AFTER shelf_life"
        ];
        
        foreach ($grocery_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added grocery field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add restaurant fields
        $restaurant_fields = [
            "menu_category VARCHAR(50) NULL AFTER country_of_origin",
            "preparation_time INT(11) NULL AFTER menu_category",
            "temperature_requirement VARCHAR(50) NULL AFTER preparation_time",
            "dietary_restrictions TEXT NULL AFTER temperature_requirement",
            "allergen_warnings TEXT NULL AFTER dietary_restrictions",
            "cooking_method VARCHAR(50) NULL AFTER allergen_warnings",
            "spice_level VARCHAR(50) NULL AFTER cooking_method"
        ];
        
        foreach ($restaurant_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added restaurant field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add electronics fields
        $electronics_fields = [
            "electronics_category VARCHAR(50) NULL AFTER spice_level",
            "model_number VARCHAR(100) NULL AFTER electronics_category",
            "warranty_period INT(11) NULL AFTER model_number",
            "power_requirements VARCHAR(100) NULL AFTER warranty_period",
            "technical_specs TEXT NULL AFTER power_requirements",
            "compatibility VARCHAR(200) NULL AFTER technical_specs",
            "color_options VARCHAR(200) NULL AFTER compatibility",
            "serial_tracking TINYINT(1) NULL DEFAULT 0 AFTER color_options"
        ];
        
        foreach ($electronics_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added electronics field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add clothing fields
        $clothing_fields = [
            "clothing_category VARCHAR(50) NULL AFTER serial_tracking",
            "material VARCHAR(200) NULL AFTER clothing_category",
            "sizes TEXT NULL AFTER material",
            "colors VARCHAR(200) NULL AFTER sizes",
            "season VARCHAR(50) NULL AFTER colors",
            "fit_type VARCHAR(50) NULL AFTER season",
            "care_instructions VARCHAR(50) NULL AFTER fit_type"
        ];
        
        foreach ($clothing_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added clothing field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add general fields
        $general_fields = [
            "description TEXT NULL AFTER care_instructions",
            "specifications TEXT NULL AFTER description",
            "usage_instructions TEXT NULL AFTER specifications",
            "safety_info TEXT NULL AFTER usage_instructions"
        ];
        
        foreach ($general_fields as $field) {
            $sql = "ALTER TABLE POS_products ADD COLUMN $field";
            if ($conn->query($sql)) {
                echo "Added general field: " . explode(' ', $field)[0] . "<br>";
            }
        }
        
        // Add indexes for better performance
        $indexes = [
            "CREATE INDEX idx_business_type ON POS_products (business_type)",
            "CREATE INDEX idx_brand ON POS_products (brand)",
            "CREATE INDEX idx_generic_name ON POS_products (generic_name)",
            "CREATE INDEX idx_model_number ON POS_products (model_number)"
        ];
        
        foreach ($indexes as $index) {
            try {
                if ($conn->query($index)) {
                    echo "Created index successfully<br>";
                }
            } catch (Exception $e) {
                echo "Index may already exist: " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br><strong>Migration completed successfully!</strong><br>";
        echo "The POS_products table now supports industry-specific fields.<br>";
        echo "You can now use the enhanced product entry form.";
        
    } else {
        echo "Industry-specific fields already exist in the database.<br>";
        echo "The enhanced product form should work properly.";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
