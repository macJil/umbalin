-- Update enum to include 'landslide' while keeping legacy 'accident'
ALTER TABLE `disasters`
  MODIFY COLUMN `type` ENUM('flood','earthquake','typhoon','landslide','accident') NOT NULL;