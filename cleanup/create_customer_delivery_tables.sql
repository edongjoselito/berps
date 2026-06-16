-- Create customer delivery tables for BERPS system
-- Execute this in phpMyAdmin, MySQL Workbench, or command line

-- Drop tables if they exist (for clean re-creation)
DROP TABLE IF EXISTS customer_delivery_items;
DROP TABLE IF EXISTS customer_deliveries;

-- Create customer_deliveries table
CREATE TABLE customer_deliveries (
  deliveryID INT AUTO_INCREMENT PRIMARY KEY,
  deliveryNo VARCHAR(50) NOT NULL,
  invoiceNo VARCHAR(50),
  orderID INT,
  customerID INT,
  customerName VARCHAR(255) NOT NULL,
  customerAddress TEXT,
  customerContact VARCHAR(100),
  deliveryDate DATE,
  deliveryStatus ENUM('pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
  paymentStatus ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
  totalAmount DECIMAL(10,2) DEFAULT 0.00,
  amountPaid DECIMAL(10,2) DEFAULT 0.00,
  balance DECIMAL(10,2) DEFAULT 0.00,
  deliveryFee DECIMAL(10,2) DEFAULT 0.00,
  notes TEXT,
  deliveryPerson VARCHAR(100),
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  settingsID INT NOT NULL,
  
  -- Indexes for better performance
  INDEX idx_settingsID (settingsID),
  INDEX idx_deliveryNo (deliveryNo),
  INDEX idx_customerID (customerID),
  INDEX idx_invoiceNo (invoiceNo),
  INDEX idx_deliveryStatus (deliveryStatus),
  INDEX idx_deliveryDate (deliveryDate),
  INDEX idx_customerName (customerName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer_delivery_items table
CREATE TABLE customer_delivery_items (
  itemID INT AUTO_INCREMENT PRIMARY KEY,
  deliveryID INT NOT NULL,
  productID INT,
  productName VARCHAR(255) NOT NULL,
  productDescription TEXT,
  quantity INT NOT NULL DEFAULT 1,
  unitPrice DECIMAL(10,2) DEFAULT 0.00,
  totalPrice DECIMAL(10,2) DEFAULT 0.00,
  weight DECIMAL(8,2),
  dimensions VARCHAR(50),
  specialInstructions TEXT,
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  settingsID INT NOT NULL,
  
  -- Indexes for better performance
  INDEX idx_settingsID (settingsID),
  INDEX idx_deliveryID (deliveryID),
  INDEX idx_productID (productID),
  INDEX idx_productName (productName),
  
  -- Foreign key constraint
  FOREIGN KEY (deliveryID) REFERENCES customer_deliveries(deliveryID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show table structures after creation
DESCRIBE customer_deliveries;
DESCRIBE customer_delivery_items;

-- Success message
SELECT 'Customer delivery tables created successfully!' as message;
