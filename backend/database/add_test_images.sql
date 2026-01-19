-- Add test images to an existing project to test the image collage feature
-- This adds sample image file paths to project_files table

-- For project_id 1050 (Test3) - add some test image entries
INSERT INTO `project_files` (`project_id`, `file_type`, `file_path`, `uploaded_at`) VALUES
(1050, 'desired design', 'projects/test_house_1.jpg', NOW()),
(1050, 'desired design', 'projects/test_house_2.jpg', NOW()),
(1050, 'desired design', 'projects/test_house_3.jpg', NOW());

-- Note: These image files don't actually exist in storage yet.
-- To properly test:
-- 1. Either upload real images through the project creation form
-- 2. Or place sample images named test_house_1.jpg, test_house_2.jpg, test_house_3.jpg 
--    in the backend/storage/app/public/projects/ folder
-- 3. Run: php artisan storage:link (if not already done)
