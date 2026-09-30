-- Delete disaster records that fall outside the Itogon bounding box (use with caution)
-- Bounding box used by the app (approximate):
--   South: 16.2700
--   North: 16.4800
--   West:  120.6000
--   East:  120.8000

DELETE FROM disasters
WHERE latitude < 16.2700
   OR latitude > 16.4800
   OR longitude < 120.6000
   OR longitude > 120.8000;

-- NOTE: Run this in phpMyAdmin or via mysql client. Consider making a backup first:
--   mysqldump -u root -p disaster_db disasters > disasters-backup.sql
