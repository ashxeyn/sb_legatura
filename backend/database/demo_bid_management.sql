-- Demo data for Bid Management Dashboard showcase
-- For user: carl_saludo (user_id: 28, owner_id: 14)
-- This script adds a project with multiple bids for comparison

-- First, let's get the next available IDs
-- Assuming current max rel_id is 29, max project_id is 34, max bid_id is 13

-- Insert Project Relationship for carl_saludo (owner_id = 14)
INSERT INTO `project_relationships` (`rel_id`, `owner_id`, `selected_contractor_id`, `project_post_status`, `admin_reason`, `reason`, `bidding_due`, `created_at`, `updated_at`) VALUES
(30, 14, NULL, 'approved', NULL, NULL, '2025-12-20', NOW(), NOW());

-- Insert the Project
INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `selected_contractor_id`) VALUES
(35, 30, 'Modern 2-Story Residential House', 'Complete construction of a modern 2-story residential house with 4 bedrooms, 3 bathrooms, living area, dining room, kitchen, and 2-car garage. Looking for experienced contractors with strong track record in residential projects. The design emphasizes natural lighting and modern Filipino architecture. Requirements include foundation work, structural framing, roofing, electrical, plumbing, and full finishing works.', 'Brgy. Tumaga, Zamboanga City, Zamboanga del Sur', 2500000.00, 3500000.00, 350, 220, 'Residential', 1, NULL, 12, 'open', NULL);

-- Insert Project Files (using existing placeholder paths)
INSERT INTO `project_files` (`project_id`, `file_type`, `file_path`, `uploaded_at`) VALUES
(35, 'building permit', 'projects/demo_building_permit.pdf', NOW()),
(35, 'blueprint', 'projects/demo_blueprint.pdf', NOW()),
(35, 'title', 'projects/demo_land_title.pdf', NOW()),
(35, 'desired design', 'projects/demo_design.pdf', NOW());

-- Insert Multiple Bids from Different Contractors for comparison
-- Bid 1: From Sandbox (contractor_id: 1) - Civil Works Contractor, AAA rated
INSERT INTO `bids` (`project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(35, 1, 2850000.00, 10, 'We have extensive experience in residential construction with over 50 completed projects. Our team includes licensed engineers and skilled craftsmen. We use premium quality materials and offer a 1-year warranty on all works. Timeline includes foundation (2 months), structural (3 months), finishing (5 months).', 'submitted', NULL, '2025-12-01 09:30:00', NULL);

-- Bid 2: From contractor_id 2 (sad company) - Civil Works Contractor, AAAA rated
INSERT INTO `bids` (`project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(35, 2, 3100000.00, 8, 'As an AAAA-rated contractor, we guarantee premium quality construction with imported materials. We offer faster completion time with our larger workforce. Includes 2-year comprehensive warranty and free architectural consultation. Our portfolio includes luxury residential projects in the region.', 'submitted', NULL, '2025-12-01 14:45:00', NULL);

-- Bid 3: From Niggatura (contractor_id: 3) - Others type, AA rated
INSERT INTO `bids` (`project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(35, 3, 2650000.00, 14, 'Budget-friendly option without compromising quality. We source materials locally to reduce costs. Our team has experience with similar residential projects. Flexible payment terms available. Timeline is longer but ensures careful attention to detail on each phase.', 'submitted', NULL, '2025-12-02 08:15:00', NULL);

-- Bid 4: From adad company (contractor_id: 5) - Electrical Contractor, AAA rated
INSERT INTO `bids` (`project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(35, 5, 2780000.00, 11, 'Competitive pricing with quality assurance. We specialize in electrical systems but have full construction capabilities. Energy-efficient solutions included in our proposal. Smart home integration options available at additional cost. Transparent pricing with detailed breakdown.', 'submitted', NULL, '2025-12-02 16:20:00', NULL);

-- Bid 5: From asdasd company (contractor_id: 6) - Landscaping Contractor, AAA rated
INSERT INTO `bids` (`project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(35, 6, 2920000.00, 12, 'Full-service construction with landscaping expertise. We can complete the house and design beautiful outdoor spaces. Premium finishing options included. Our team handles everything from foundation to final landscaping. References available upon request.', 'submitted', NULL, '2025-12-03 10:30:00', NULL);

-- Get the bid IDs for file attachments (assuming next IDs start from 14)
-- Insert Bid Files for each bid
INSERT INTO `bid_files` (`bid_id`, `file_name`, `file_path`, `description`, `uploaded_at`) VALUES
-- Files for Bid from Sandbox (assuming bid_id = 14)
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 1), 'company_portfolio.pdf', 'bid_files/demo_sandbox_portfolio.pdf', 'Company Portfolio and Past Projects', NOW()),
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 1), 'detailed_quotation.pdf', 'bid_files/demo_sandbox_quotation.pdf', 'Detailed Cost Breakdown', NOW()),

-- Files for Bid from sad company (assuming bid_id = 15)
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 2), 'aaaa_certification.pdf', 'bid_files/demo_sad_certification.pdf', 'AAAA PICAB Certification', NOW()),
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 2), 'project_timeline.pdf', 'bid_files/demo_sad_timeline.pdf', 'Detailed Project Timeline', NOW()),
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 2), 'material_specs.pdf', 'bid_files/demo_sad_materials.pdf', 'Material Specifications', NOW()),

-- Files for Bid from Niggatura (assuming bid_id = 16)
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 3), 'budget_breakdown.pdf', 'bid_files/demo_niggatura_budget.pdf', 'Cost-Effective Budget Breakdown', NOW()),

-- Files for Bid from adad company (assuming bid_id = 17)
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 5), 'smart_home_proposal.pdf', 'bid_files/demo_adad_smarthome.pdf', 'Smart Home Integration Options', NOW()),
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 5), 'electrical_plan.pdf', 'bid_files/demo_adad_electrical.pdf', 'Electrical System Plan', NOW()),

-- Files for Bid from asdasd company (assuming bid_id = 18)
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 6), 'landscape_design.pdf', 'bid_files/demo_asdasd_landscape.pdf', 'Landscape Design Proposal', NOW()),
((SELECT bid_id FROM bids WHERE project_id = 35 AND contractor_id = 6), 'full_quotation.pdf', 'bid_files/demo_asdasd_quotation.pdf', 'Complete Quotation with Landscaping', NOW());

-- Summary of what was added:
-- 1 Project Relationship (rel_id: 30)
-- 1 Project (project_id: 35) - "Modern 2-Story Residential House"
-- 4 Project Files
-- 5 Bids from different contractors with varying:
--    - Prices: ₱2,650,000 to ₱3,100,000
--    - Timelines: 8 to 14 months
--    - Detailed contractor notes
-- 10 Bid Files (documents attached to bids)

-- This showcases the Bid Management Dashboard features:
-- ✓ All bids in one place
-- ✓ Comparable by price (proposed_cost)
-- ✓ Comparable by duration (estimated_timeline)
-- ✓ Contractor profiles (via contractor_id -> contractors table)
-- ✓ Documents to review (bid_files)
-- ✓ Ready for accept/reject actions
