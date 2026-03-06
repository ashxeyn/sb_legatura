<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FeatureSeeder — Populates realistic test data for:
 *   - Users (25 contractors + 15 property owners)
 *   - Project Posts (60 social posts)
 *   - Bids (3–12 per open post)
 *   - Reviews (for completed projects, realistic rating distribution)
 *   - Highlights (up to 3 per user)
 *
 * Safe to re-run: checks for existing seed data before inserting.
 */
class FeatureSeeder extends Seeder
{
    /** Seed tag to prevent duplicates on re-run */
    private const SEED_TAG = 'feature_seeder_v1';

    /* ─── Lookup data ──────────────────────────────────────────────── */

    private array $locations = [
        'Manila', 'Quezon City', 'Makati', 'Pasig', 'Cebu City',
        'Davao City', 'Cavite', 'Laguna', 'Taguig', 'Mandaluyong',
        'Paranaque', 'San Juan', 'Marikina', 'Muntinlupa', 'Caloocan',
    ];

    private array $contractorCompanies = [
        'Metro Build Construction', 'Pinnacle Contractors Inc.', 'Titan Engineering Corp.',
        'BridgePoint Builders', 'Paramount Structures', 'Victory Construction Co.',
        'AsiaPrime Developers', 'GoldStar Contracting', 'SteelFrame Build Corp.',
        'ProBuild Solutions', 'CityScape Constructors', 'PrimeTech Engineering',
        'Foundation One Corp.', 'UpperWorks Construction', 'MegaStructure Builders',
        'Apex Civil Works', 'Elite Home Builders', 'Pacific Build Group',
        'Summit Contractors PH', 'BluePrint Build Co.', 'Alliance Construction',
        'CoreBuild Enterprises', 'Prestige Builders Inc.', 'NovaBuild Corp.',
        'Skyline Construction PH',
    ];

    private array $ownerNames = [
        ['Maria', 'Santos'], ['Jose', 'Reyes'], ['Ana', 'Cruz'], ['Juan', 'Garcia'],
        ['Patricia', 'Hernandez'], ['Carlos', 'Lopez'], ['Elena', 'Gonzales'],
        ['Miguel', 'Rivera'], ['Isabella', 'Torres'], ['Roberto', 'Ramos'],
        ['Sofia', 'Mendoza'], ['Daniel', 'Flores'], ['Carmen', 'Castillo'],
        ['Antonio', 'Bautista'], ['Teresa', 'Villanueva'],
    ];

    private array $projectTitles = [
        '2-Storey Residential House Construction', 'Commercial Building Renovation',
        'Kitchen Remodeling Project', 'Electrical System Upgrade',
        'Plumbing System Overhaul', 'Roof Replacement and Waterproofing',
        'Office Interior Fit-Out', 'Swimming Pool Construction',
        'Warehouse Expansion Project', 'Condominium Unit Renovation',
        'Farm House Construction', 'Restaurant Interior Design',
        'HVAC System Installation', 'Solar Panel Installation',
        'Perimeter Wall and Fencing', 'Landscaping and Hardscape',
        'Multi-Storey Parking Structure', 'Residential Subdivision Development',
        'Church Renovation Project', 'School Building Construction',
        'Industrial Plant Expansion', 'Hospital Wing Addition',
        'Water Treatment Facility', 'Bridge Construction',
        'Road Widening Project', 'Fire Station Construction',
        'Modern Townhouse Development', 'Low-Rise Apartment Building',
        'Heritage Building Restoration', 'Green Building Certification Project',
    ];

    private array $projectDescriptions = [
        'Looking for experienced contractors to handle this project from foundation to finishing. Must have proven track record with similar projects in the area.',
        'We need a reliable team with strong references. The project requires attention to detail and adherence to building codes.',
        'Urgent project requiring immediate start. Budget is flexible for quality work. Contractor must have PICAB license.',
        'Long-term project with multiple phases. Looking for a contractor who can commit to the full timeline.',
        'Renovation project requiring careful preservation of existing structures while modernizing systems.',
        'New construction project with sustainable building requirements. LEED certification preferred.',
        'Interior renovation with strict design guidelines. Must coordinate with our architect.',
        'Infrastructure project requiring heavy equipment and experienced crew. Safety record is critical.',
        'Residential project in a gated community. Must follow HOA guidelines and maintain cleanliness.',
        'Commercial build-out with specific MEP requirements. Contractor must handle permits.',
    ];

    private array $postContents = [
        'Looking for a contractor experienced in residential construction. We have a beautiful lot in %s and want to build our dream home. Budget is ₱%s - ₱%s. Must have PICAB license and at least 5 years of experience.',
        'Need a reliable general contractor for a commercial renovation project in %s. The space is approximately 200 sqm and needs complete interior overhaul. Budget range ₱%s - ₱%s.',
        'Seeking electrical contractor for a major system upgrade in our building at %s. Must be licensed and insured. Estimated budget ₱%s - ₱%s. Timeline: 30-60 days.',
        'Property owner looking for plumbing contractor in %s area. Complete piping replacement needed. Budget ₱%s - ₱%s. Prefer someone with experience in old building retrofits.',
        'We are expanding our warehouse in %s and need a civil engineering contractor. The project involves structural steel work and concrete. Budget ₱%s - ₱%s.',
        'Modern kitchen and bathroom remodel needed in %s. Looking for interior specialists who can handle both design and execution. Budget ₱%s - ₱%s.',
        'HVAC system installation for our new office building in %s. Must include ducting, units, and controls. Budget ₱%s - ₱%s. Immediate start needed.',
        'Roof replacement and waterproofing for a 3-storey building in %s. Material costs included. Budget ₱%s - ₱%s. Must warranty work for 5 years.',
        'Swimming pool construction in our residential property at %s. Infinity edge design, 8x4 meters. Budget ₱%s - ₱%s. Looking for experience in similar projects.',
        'Solar panel installation for our warehouse roof in %s. Need 50kW system with monitoring. Budget ₱%s - ₱%s. Must be certified installer.',
    ];

    private array $serviceContents = [
        'Professional general contracting services available in %s and surrounding areas. Specializing in residential and commercial construction. Over %d years of experience. Gold-rated contractor.',
        'Licensed electrical contractor offering full-service electrical installations, upgrades, and maintenance in Metro Manila and %s. PICAB certified. Free estimates available.',
        'Expert plumbing services for residential and commercial projects in %s. Complete pipe installation, repair, and maintenance. Licensed and bonded.',
        'HVAC installation and maintenance services in %s. We handle ducted AC, VRF systems, and ventilation. Factory-trained technicians on staff.',
        'Interior design and build services available in %s. We specialize in modern Filipino design for homes and offices. Full project management included.',
    ];

    private array $reviewComments5 = [
        'Excellent work! Completed ahead of schedule and within budget. Very professional team.',
        'Outstanding quality of workmanship. Would highly recommend to anyone looking for reliable contractors.',
        'Best contractor I\'ve worked with. Communication was excellent throughout the project.',
        'Superb attention to detail and very responsive to feedback. The finished work exceeded expectations.',
    ];

    private array $reviewComments4 = [
        'Very good work overall. Minor delays but the quality made up for it.',
        'Professional team with good communication. A few small issues were resolved quickly.',
        'Solid work and reasonable pricing. Would hire again for future projects.',
        'Good quality construction. The project manager was very attentive and organized.',
        'Reliable contractor. Met most deadlines and the work quality was above average.',
    ];

    private array $reviewComments3 = [
        'Decent work but communication could be improved. Final result was satisfactory.',
        'Average experience. Some delays and a few quality issues that needed rework.',
        'Work was acceptable. Budget overruns were a concern but the end result was okay.',
        'Meets expectations but nothing exceptional. Would consider for smaller projects.',
    ];

    private array $reviewComments2 = [
        'Below expectations. Several delays and quality issues. Communication was poor.',
        'Disappointed with the timeline. Work quality was inconsistent. Had to escalate multiple times.',
        'Not recommended. Too many change orders and the project went over budget significantly.',
    ];

    private array $reviewComments1 = [
        'Very poor experience. Project was severely delayed and work quality was unacceptable.',
        'Would not hire again. Multiple issues with workmanship and professionalism.',
    ];

    /* ═════════════════════════════════════════════════════════════════
     * RUN
     * ═════════════════════════════════════════════════════════════════ */

    public function run(): void
    {
        // Idempotency check — look for our seed tag in a comment-based marker
        $marker = DB::table('users')->where('username', 'like', '%_seed_marker_%')->first();
        if ($marker) {
            $this->command->info('FeatureSeeder already ran (seed marker found). Skipping.');
            return;
        }

        $this->command->info('Starting FeatureSeeder — populating test data...');

        DB::beginTransaction();
        try {
            // Get existing contractor_types
            $contractorTypes = DB::table('contractor_types')->pluck('type_id', 'type_name')->toArray();
            if (empty($contractorTypes)) {
                $this->command->error('No contractor_types found. Run base migrations first.');
                return;
            }

            // Get existing occupations
            $occupations = DB::table('occupations')->pluck('id')->toArray();

            // Get existing valid_ids
            $validIds = DB::table('valid_ids')->pluck('id')->toArray();

            $contractorUserIds = [];
            $ownerUserIds = [];
            $contractorIds = [];
            $ownerIds = [];

            // ─── 1. CONTRACTOR USERS (25) ──────────────────────────────
            $this->command->info('Creating 25 contractor users...');
            $typeIds = array_values($contractorTypes);
            $verificationDistribution = array_merge(
                array_fill(0, 18, 'approved'),   // 70%
                array_fill(0, 5, 'pending'),     // 20%
                array_fill(0, 2, 'rejected')     // 10%
            );
            shuffle($verificationDistribution);

            $subscriptionTiers = array_merge(
                array_fill(0, 15, 'free'),
                array_fill(0, 7, 'silver'),
                array_fill(0, 3, 'gold')
            );
            shuffle($subscriptionTiers);

            for ($i = 0; $i < 25; $i++) {
                $username = 'contractor_' . ($i + 1) . '_seed';
                $email = 'contractor' . ($i + 1) . '@seed.legatura.test';

                $userId = DB::table('users')->insertGetId([
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => bcrypt('SeedPass123!'),
                    'user_type'     => 'contractor',
                    'created_at'    => now()->subDays(rand(30, 365)),
                    'updated_at'    => now(),
                ]);

                $typeId = $typeIds[array_rand($typeIds)];
                $location = $this->locations[array_rand($this->locations)];
                $years = rand(2, 25);
                $verification = $verificationDistribution[$i] ?? 'approved';

                $contractorId = DB::table('contractors')->insertGetId([
                    'user_id'               => $userId,
                    'company_name'          => $this->contractorCompanies[$i],
                    'type_id'               => $typeId,
                    'bio'                   => 'Professional construction company with ' . $years . ' years of experience in ' . $location . '.',
                    'years_of_experience'   => $years,
                    'completed_projects'    => rand(2, 40),
                    'verification_status'   => $verification,
                    'is_active'             => 1,
                    'business_permit_city'  => $location,
                    'business_address'      => $location . ', Philippines',
                    'services_offered'      => $this->generateServicesForType($typeId, $contractorTypes),
                    'picab_category'        => ['A', 'B', 'C', 'D'][array_rand(['A', 'B', 'C', 'D'])],
                    'created_at'            => now()->subDays(rand(30, 365)),
                    'updated_at'            => now(),
                ]);

                // Create contractor_user row (owner role)
                DB::table('contractor_users')->insert([
                    'contractor_id'       => $contractorId,
                    'user_id'             => $userId,
                    'authorized_rep_fname' => $this->contractorCompanies[$i],
                    'authorized_rep_lname' => 'Rep',
                    'phone_number'        => '09' . rand(100000000, 999999999),
                    'role'                => 'owner',
                    'is_deleted'          => 0,
                    'is_active'           => 1,
                ]);

                $contractorUserIds[] = $userId;
                $contractorIds[] = $contractorId;

                // Set subscription tier via platform_payments for silver/gold
                $tier = $subscriptionTiers[$i] ?? 'free';
                if ($tier !== 'free') {
                    $planId = $tier === 'gold' ? 1 : 2; // gold=1, silver=2 from subscription_plans
                    $amount = $tier === 'gold' ? 1999.00 : 1499.00;
                    DB::table('platform_payments')->insert([
                        'subscriptionPlanId' => $planId,
                        'contractor_id'      => $contractorId,
                        'amount'             => $amount,
                        'transaction_number' => 'seed_' . uniqid(),
                        'transaction_date'   => now()->subDays(rand(1, 25)),
                        'is_approved'        => 1,
                        'expiration_date'    => now()->addDays(rand(5, 30)),
                        'payment_type'       => 'PayMongo',
                    ]);
                }
            }

            // ─── 2. PROPERTY OWNER USERS (15) ──────────────────────────
            $this->command->info('Creating 15 property owner users...');
            $ownerVerification = array_merge(
                array_fill(0, 11, 'approved'),
                array_fill(0, 3, 'pending'),
                array_fill(0, 1, 'rejected')
            );
            shuffle($ownerVerification);

            for ($i = 0; $i < 15; $i++) {
                $name = $this->ownerNames[$i];
                $username = strtolower($name[0]) . '_' . strtolower($name[1]) . '_seed';
                $email = strtolower($name[0]) . '.' . strtolower($name[1]) . '@seed.legatura.test';

                $userId = DB::table('users')->insertGetId([
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => bcrypt('SeedPass123!'),
                    'user_type'     => 'property_owner',
                    'created_at'    => now()->subDays(rand(30, 365)),
                    'updated_at'    => now(),
                ]);

                $location = $this->locations[array_rand($this->locations)];
                $verification = $ownerVerification[$i] ?? 'approved';

                $ownerId = DB::table('property_owners')->insertGetId([
                    'user_id'             => $userId,
                    'first_name'          => $name[0],
                    'last_name'           => $name[1],
                    'phone_number'        => '09' . rand(100000000, 999999999),
                    'address'             => $location . ', Philippines',
                    'valid_id_id'         => !empty($validIds) ? $validIds[array_rand($validIds)] : 1,
                    'occupation_id'       => !empty($occupations) ? $occupations[array_rand($occupations)] : 1,
                    'verification_status' => $verification,
                    'is_active'           => 1,
                    'created_at'          => now()->subDays(rand(30, 365)),
                ]);

                $ownerUserIds[] = $userId;
                $ownerIds[] = $ownerId;
            }

            // ─── 3. PROJECTS (60 — traditional, for reviews/bids) ──────
            $this->command->info('Creating 60 projects...');
            $projectIds = [];
            $completedProjectIds = [];
            $openProjectIds = [];

            // 15 completed, 45 open/in-progress
            for ($i = 0; $i < 60; $i++) {
                $ownerIdx = $i % count($ownerIds);
                $ownerId = $ownerIds[$ownerIdx];
                $title = $this->projectTitles[$i % count($this->projectTitles)];
                $typeId = $typeIds[array_rand($typeIds)];
                $location = $this->locations[array_rand($this->locations)];
                $budgetMin = rand(5, 300) * 10000;
                $budgetMax = $budgetMin + rand(5, 100) * 10000;
                $daysAgo = rand(1, 60);

                $isCompleted = ($i < 15);
                $status = $isCompleted ? 'completed' : ($i < 30 ? 'open' : (['open', 'in_progress', 'bidding_closed'][array_rand(['open', 'in_progress', 'bidding_closed'])]));

                $selectedContractor = null;
                if ($isCompleted || $status === 'in_progress') {
                    $selectedContractor = $contractorIds[array_rand($contractorIds)];
                }

                // Create project_relationship first
                $relId = DB::table('project_relationships')->insertGetId([
                    'owner_id'            => $ownerId,
                    'selected_contractor_id' => $selectedContractor,
                    'project_post_status' => 'approved',
                    'bidding_due'         => $isCompleted ? null : now()->addDays(rand(7, 30))->toDateString(),
                    'created_at'          => now()->subDays($daysAgo),
                    'updated_at'          => now(),
                ]);

                $projectId = DB::table('projects')->insertGetId([
                    'relationship_id'        => $relId,
                    'project_title'          => $title . ' #' . ($i + 1),
                    'project_description'    => $this->projectDescriptions[array_rand($this->projectDescriptions)],
                    'project_location'       => $location,
                    'budget_range_min'       => $budgetMin,
                    'budget_range_max'       => $budgetMax,
                    'lot_size'               => rand(50, 500),
                    'floor_area'             => rand(30, 300),
                    'property_type'          => ['Residential', 'Commercial', 'Industrial', 'Agricultural'][array_rand(['Residential', 'Commercial', 'Industrial', 'Agricultural'])],
                    'type_id'                => $typeId,
                    'project_status'         => $status,
                    'selected_contractor_id' => $selectedContractor,
                    'is_highlighted'         => false,
                ]);

                $projectIds[] = $projectId;
                if ($isCompleted) $completedProjectIds[] = $projectId;
                if ($status === 'open') $openProjectIds[] = $projectId;
            }

            // ─── 4. BIDS (3–12 per open project) ──────────────────────
            $this->command->info('Creating bids for open projects...');
            foreach ($openProjectIds as $projId) {
                $bidCount = rand(3, 12);
                $usedContractors = [];

                for ($b = 0; $b < $bidCount; $b++) {
                    // Pick a contractor not yet used for this project
                    $attempts = 0;
                    do {
                        $cIdx = array_rand($contractorIds);
                        $cId = $contractorIds[$cIdx];
                        $attempts++;
                    } while (in_array($cId, $usedContractors) && $attempts < 30);

                    if (in_array($cId, $usedContractors)) continue;
                    $usedContractors[] = $cId;

                    $proj = DB::table('projects')->where('project_id', $projId)->first();
                    $proposedCost = ($proj->budget_range_min ?? 100000) + rand(-50000, 200000);
                    $proposedCost = max(50000, $proposedCost);

                    DB::table('bids')->insert([
                        'project_id'         => $projId,
                        'contractor_id'      => $cId,
                        'proposed_cost'      => $proposedCost,
                        'estimated_timeline' => rand(7, 120),
                        'bid_status'         => 'submitted',
                        'submitted_at'       => now()->subDays(rand(1, 30)),
                    ]);
                }
            }

            // ─── 5. REVIEWS (for completed projects) ───────────────────
            $this->command->info('Creating reviews for completed projects...');
            $ratingDistribution = array_merge(
                array_fill(0, 2, 5),     // 10% → 5 stars
                array_fill(0, 8, 0),     // 40% → 4-4.8 (placeholder, set below)
                array_fill(0, 6, 0),     // 30% → 3-4
                array_fill(0, 3, 0),     // 15% → 2-3
                array_fill(0, 1, 0)      // 5%  → 1-2
            );

            foreach ($completedProjectIds as $idx => $projId) {
                $project = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                    ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                    ->where('p.project_id', $projId)
                    ->select('po.user_id as owner_user_id', 'c.user_id as contractor_user_id')
                    ->first();

                if (!$project || !$project->owner_user_id || !$project->contractor_user_id) continue;

                // Owner reviews contractor
                $rating = $this->generateRating($idx);
                $comment = $this->getCommentForRating($rating);

                DB::table('reviews')->insert([
                    'reviewer_user_id'  => $project->owner_user_id,
                    'reviewee_user_id'  => $project->contractor_user_id,
                    'project_id'        => $projId,
                    'rating'            => $rating,
                    'comment'           => $comment,
                    'created_at'        => now()->subDays(rand(1, 15)),
                ]);

                // Contractor reviews owner (80% chance)
                if (rand(1, 10) <= 8) {
                    $rating2 = $this->generateRating($idx + 100);
                    $comment2 = $this->getCommentForRating($rating2);

                    DB::table('reviews')->insert([
                        'reviewer_user_id'  => $project->contractor_user_id,
                        'reviewee_user_id'  => $project->owner_user_id,
                        'project_id'        => $projId,
                        'rating'            => $rating2,
                        'comment'           => $comment2,
                        'created_at'        => now()->subDays(rand(1, 10)),
                    ]);
                }
            }

            // ─── 6. PROJECT POSTS (60 social posts) ────────────────────
            $this->command->info('Creating 60 social project posts...');
            $allUserIds = array_merge($contractorUserIds, $ownerUserIds);
            $boostSlots = [
                'gold_active'    => 10,
                'silver_active'  => 10,
                'expired'        => 10,
                'none'           => 30,
            ];
            $boostAssignment = [];
            foreach ($boostSlots as $type => $count) {
                for ($j = 0; $j < $count; $j++) {
                    $boostAssignment[] = $type;
                }
            }
            shuffle($boostAssignment);

            $socialPostIds = [];
            for ($i = 0; $i < 60; $i++) {
                $isContractorPost = ($i < 30); // first 30 from contractors, rest from owners
                $uId = $isContractorPost
                    ? $contractorUserIds[array_rand($contractorUserIds)]
                    : $ownerUserIds[array_rand($ownerUserIds)];

                $location = $this->locations[array_rand($this->locations)];
                $budgetMin = rand(5, 300) * 10000;
                $budgetMax = $budgetMin + rand(5, 100) * 10000;
                $typeId = $typeIds[array_rand($typeIds)];
                $daysAgo = rand(0, 60);

                if ($isContractorPost) {
                    // Service offering post
                    $template = $this->serviceContents[array_rand($this->serviceContents)];
                    $content = sprintf($template, $location, rand(3, 20));
                    $postType = 'service';
                } else {
                    // Project seeking post
                    $template = $this->postContents[array_rand($this->postContents)];
                    $content = sprintf($template, $location, number_format($budgetMin), number_format($budgetMax));
                    $postType = 'project';
                }

                $status = ($i < 45) ? 'approved' : 'closed';

                // Boost
                $boost = $boostAssignment[$i] ?? 'none';
                $boostTier = null;
                $boostExp = null;
                if ($boost === 'gold_active') {
                    $boostTier = 'gold';
                    $boostExp = now()->addDays(rand(3, 14));
                } elseif ($boost === 'silver_active') {
                    $boostTier = 'silver';
                    $boostExp = now()->addDays(rand(2, 10));
                } elseif ($boost === 'expired') {
                    $boostTier = ['gold', 'silver'][array_rand(['gold', 'silver'])];
                    $boostExp = now()->subDays(rand(1, 10));
                }

                $postId = DB::table('showcases')->insertGetId([
                    'user_id'                  => $uId,
                    'post_type'                => $postType,
                    'title'                    => $this->projectTitles[array_rand($this->projectTitles)],
                    'content'                  => $content,
                    'budget_min'               => $budgetMin,
                    'budget_max'               => $budgetMax,
                    'location'                 => $location,
                    'contractor_type_required' => $postType === 'project' ? $typeId : null,
                    'property_type'            => ['Residential', 'Commercial', 'Industrial'][array_rand(['Residential', 'Commercial', 'Industrial'])],
                    'status'                   => $status,
                    'boost_tier'               => $boostTier,
                    'boost_expiration'         => $boostExp,
                    'is_highlighted'           => false,
                    'highlighted_at'           => null,
                    'linked_project_id'        => null,
                    'created_at'               => now()->subDays($daysAgo),
                    'updated_at'               => now()->subDays($daysAgo),
                ]);

                $socialPostIds[] = ['post_id' => $postId, 'user_id' => $uId];
            }

            // ─── 7. HIGHLIGHTS (up to 3 per user) ─────────────────────
            $this->command->info('Assigning highlights...');
            $postsByUser = collect($socialPostIds)->groupBy('user_id');
            foreach ($postsByUser as $uId => $userPosts) {
                $highlightCount = min(rand(0, 3), $userPosts->count());
                $toHighlight = $userPosts->random(max(1, $highlightCount));
                if ($highlightCount === 0) continue;

                foreach ($toHighlight as $hp) {
                    DB::table('showcases')->where('post_id', $hp['post_id'])->update([
                        'is_highlighted' => true,
                        'highlighted_at' => now()->subDays(rand(0, 10)),
                    ]);
                }
            }

            // Also highlight some traditional projects
            foreach ($ownerUserIds as $oUserId) {
                $ownerProjects = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                    ->where('po.user_id', $oUserId)
                    ->whereNotIn('p.project_status', ['deleted', 'deleted_post'])
                    ->pluck('p.project_id')
                    ->toArray();

                if (count($ownerProjects) > 0) {
                    $toHighlight = array_slice($ownerProjects, 0, min(rand(0, 2), count($ownerProjects)));
                    foreach ($toHighlight as $pId) {
                        DB::table('projects')->where('project_id', $pId)->update([
                            'is_highlighted' => true,
                            'highlighted_at' => now()->subDays(rand(0, 7)),
                        ]);
                    }
                }
            }

            // ─── 8. Seed marker (idempotency) ─────────────────────────
            DB::table('users')->insert([
                'username'      => '_seed_marker_' . self::SEED_TAG,
                'email'         => 'seed.marker@seed.legatura.test',
                'password_hash' => bcrypt('marker'),
                'user_type'     => 'staff',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::commit();

            $this->command->info('FeatureSeeder completed successfully!');
            $this->command->info('  → 25 contractor users created');
            $this->command->info('  → 15 property owner users created');
            $this->command->info('  → 60 projects created (15 completed, 45 open/in-progress)');
            $this->command->info('  → Bids assigned to open projects (3–12 each)');
            $this->command->info('  → Reviews created for completed projects');
            $this->command->info('  → 60 social posts created with boost/highlight data');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FeatureSeeder failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->command->error('FeatureSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /* ─── Helpers ──────────────────────────────────────────────────── */

    private function generateServicesForType(int $typeId, array $types): string
    {
        $typeNames = array_flip($types);
        $typeName = $typeNames[$typeId] ?? 'General';

        $serviceMap = [
            'General Contractor'   => 'General construction, renovations, project management, concrete works',
            'Electrical'           => 'Electrical installation, wiring, panel upgrades, lighting design',
            'Plumbing'             => 'Pipe installation, water systems, drainage, bathroom remodeling',
            'Civil'                => 'Structural engineering, foundation work, road construction',
            'HVAC'                 => 'AC installation, ducting, ventilation systems, maintenance',
            'Interior'             => 'Interior design, fit-out, finishing works, furniture customization',
        ];

        return $serviceMap[$typeName] ?? 'General construction services, renovations, and project management';
    }

    private function generateRating(int $index): int
    {
        $roll = $index % 20;
        if ($roll < 2) return 5;                          // 10%
        if ($roll < 10) return rand(4, 5);                // 40%
        if ($roll < 16) return rand(3, 4);                // 30%
        if ($roll < 19) return rand(2, 3);                // 15%
        return rand(1, 2);                                 // 5%
    }

    private function getCommentForRating(int $rating): string
    {
        return match ($rating) {
            5 => $this->reviewComments5[array_rand($this->reviewComments5)],
            4 => $this->reviewComments4[array_rand($this->reviewComments4)],
            3 => $this->reviewComments3[array_rand($this->reviewComments3)],
            2 => $this->reviewComments2[array_rand($this->reviewComments2)],
            1 => $this->reviewComments1[array_rand($this->reviewComments1)],
            default => 'The work was completed.',
        };
    }
}
