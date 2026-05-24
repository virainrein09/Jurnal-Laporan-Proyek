CREATE TABLE IF NOT EXISTS journals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Add profile avatar to users table
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL AFTER password,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add evidence image + updated_at to journals table
ALTER TABLE journals
  ADD COLUMN IF NOT EXISTS evidence_image VARCHAR(255) DEFAULT NULL AFTER description,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Create uploads folder permission hint (do manually on server)
-- chmod 755 uploads/

-- Verify structure
DESCRIBE users;
DESCRIBE journals;
