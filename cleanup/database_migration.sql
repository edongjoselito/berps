-- Database migration script for POS product enhancements
-- Run this script in your MySQL database to add industry-specific fields

-- Add basic business type field
ALTER TABLE POS_products ADD COLUMN business_type VARCHAR(50) NULL DEFAULT 'general' AFTER status;

-- Add brand field
ALTER TABLE POS_products ADD COLUMN brand VARCHAR(100) NULL AFTER business_type;

-- Add pharmacy-specific fields
ALTER TABLE POS_products ADD COLUMN generic_name VARCHAR(200) NULL AFTER brand;
ALTER TABLE POS_products ADD COLUMN dosage_form VARCHAR(50) NULL AFTER generic_name;
ALTER TABLE POS_products ADD COLUMN strength VARCHAR(50) NULL AFTER dosage_form;
ALTER TABLE POS_products ADD COLUMN prescription_required TINYINT(1) NULL DEFAULT 0 AFTER strength;
ALTER TABLE POS_products ADD COLUMN fda_registration VARCHAR(100) NULL AFTER prescription_required;
ALTER TABLE POS_products ADD COLUMN drug_classification VARCHAR(50) NULL AFTER fda_registration;
ALTER TABLE POS_products ADD COLUMN storage_requirements VARCHAR(50) NULL AFTER drug_classification;
ALTER TABLE POS_products ADD COLUMN expiry_tracking TINYINT(1) NULL DEFAULT 0 AFTER storage_requirements;

-- Add grocery-specific fields
ALTER TABLE POS_products ADD COLUMN product_type VARCHAR(50) NULL AFTER expiry_tracking;
ALTER TABLE POS_products ADD COLUMN organic_certified TINYINT(1) NULL DEFAULT 0 AFTER product_type;
ALTER TABLE POS_products ADD COLUMN allergens TEXT NULL AFTER organic_certified;
ALTER TABLE POS_products ADD COLUMN nutritional_info TINYINT(1) NULL DEFAULT 0 AFTER allergens;
ALTER TABLE POS_products ADD COLUMN storage_instructions VARCHAR(50) NULL AFTER nutritional_info;
ALTER TABLE POS_products ADD COLUMN shelf_life INT(11) NULL AFTER storage_instructions;
ALTER TABLE POS_products ADD COLUMN country_of_origin VARCHAR(100) NULL AFTER shelf_life;

-- Add restaurant-specific fields
ALTER TABLE POS_products ADD COLUMN menu_category VARCHAR(50) NULL AFTER country_of_origin;
ALTER TABLE POS_products ADD COLUMN preparation_time INT(11) NULL AFTER menu_category;
ALTER TABLE POS_products ADD COLUMN temperature_requirement VARCHAR(50) NULL AFTER preparation_time;
ALTER TABLE POS_products ADD COLUMN dietary_restrictions TEXT NULL AFTER temperature_requirement;
ALTER TABLE POS_products ADD COLUMN allergen_warnings TEXT NULL AFTER dietary_restrictions;
ALTER TABLE POS_products ADD COLUMN cooking_method VARCHAR(50) NULL AFTER allergen_warnings;
ALTER TABLE POS_products ADD COLUMN spice_level VARCHAR(50) NULL AFTER cooking_method;

-- Add electronics-specific fields
ALTER TABLE POS_products ADD COLUMN electronics_category VARCHAR(50) NULL AFTER spice_level;
ALTER TABLE POS_products ADD COLUMN model_number VARCHAR(100) NULL AFTER electronics_category;
ALTER TABLE POS_products ADD COLUMN warranty_period INT(11) NULL AFTER model_number;
ALTER TABLE POS_products ADD COLUMN power_requirements VARCHAR(100) NULL AFTER warranty_period;
ALTER TABLE POS_products ADD COLUMN technical_specs TEXT NULL AFTER power_requirements;
ALTER TABLE POS_products ADD COLUMN compatibility VARCHAR(200) NULL AFTER technical_specs;
ALTER TABLE POS_products ADD COLUMN color_options VARCHAR(200) NULL AFTER compatibility;
ALTER TABLE POS_products ADD COLUMN serial_tracking TINYINT(1) NULL DEFAULT 0 AFTER color_options;

-- Add clothing-specific fields
ALTER TABLE POS_products ADD COLUMN clothing_category VARCHAR(50) NULL AFTER serial_tracking;
ALTER TABLE POS_products ADD COLUMN material VARCHAR(200) NULL AFTER clothing_category;
ALTER TABLE POS_products ADD COLUMN sizes TEXT NULL AFTER material;
ALTER TABLE POS_products ADD COLUMN colors VARCHAR(200) NULL AFTER sizes;
ALTER TABLE POS_products ADD COLUMN season VARCHAR(50) NULL AFTER colors;
ALTER TABLE POS_products ADD COLUMN fit_type VARCHAR(50) NULL AFTER season;
ALTER TABLE POS_products ADD COLUMN care_instructions VARCHAR(50) NULL AFTER fit_type;

-- Add general fields
ALTER TABLE POS_products ADD COLUMN description TEXT NULL AFTER care_instructions;
ALTER TABLE POS_products ADD COLUMN specifications TEXT NULL AFTER description;
ALTER TABLE POS_products ADD COLUMN usage_instructions TEXT NULL AFTER specifications;
ALTER TABLE POS_products ADD COLUMN safety_info TEXT NULL AFTER usage_instructions;

-- Add indexes for better performance
CREATE INDEX idx_business_type ON POS_products (business_type);
CREATE INDEX idx_brand ON POS_products (brand);
CREATE INDEX idx_generic_name ON POS_products (generic_name);
CREATE INDEX idx_model_number ON POS_products (model_number);

-- Migration completed successfully!
