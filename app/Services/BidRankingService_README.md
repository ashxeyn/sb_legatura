# Bid Ranking Service - Usage Guide

## 📍 Location
`backend/app/Services/BidRankingService.php`

## 🎯 Purpose
This service provides **centralized bid ranking logic** for the entire platform. All bid sorting and ranking goes through this single file, making it easy to modify the algorithm without touching multiple files.

---

## 🚀 How to Use

### Basic Usage (Automatic)
The service is **already integrated** into `projectsClass.php`. Every time you call `getProjectBids($projectId)`, the bids will be automatically ranked.

```php
// In any controller
$projectsClass = new projectsClass();
$rankedBids = $projectsClass->getProjectBids(1046);

// Bids are now sorted by ranking score (highest first)
foreach ($rankedBids as $bid) {
    echo "Company: {$bid->company_name}\n";
    echo "Ranking Score: {$bid->ranking_score}/100\n";
    echo "Price: ₱{$bid->proposed_cost}\n\n";
}
```

### Manual Usage (If Needed)
You can also call the service directly from any controller:

```php
use App\Services\BidRankingService;

$rankingService = new BidRankingService();

// Rank bids for a specific project
$rankedBids = $rankingService->rankBids($projectId);

// Get contractor info
$tier = $rankingService->getContractorSubscriptionTier($contractorId);
$rating = $rankingService->getContractorAverageRating($contractorId);
$reviewCount = $rankingService->getContractorReviewCount($contractorId);
```

---

## ⚙️ How to Modify the Ranking Algorithm

All configuration is at the **top of the BidRankingService.php** file. Just edit the constants:

### 1️⃣ Change Ranking Weights

```php
private const WEIGHTS = [
    'price' => 35,           // How important is price? (currently 35%)
    'experience' => 30,      // How important is experience? (currently 30%)
    'reputation' => 25,      // How important are reviews? (currently 25%)
    'subscription' => 10,    // Premium boost (currently 10%)
];
```

**Example Change:**
If you want to prioritize experience over price:

```php
private const WEIGHTS = [
    'price' => 25,           // ⬇️ Reduced from 35%
    'experience' => 40,      // ⬆️ Increased from 30%
    'reputation' => 25,      // ✅ Same
    'subscription' => 10,    // ✅ Same
];
```

### 2️⃣ Adjust Subscription Boost

```php
private const SUBSCRIPTION_SCORES = [
    'premium' => 100,        // Full boost for premium (₱5,000+/month)
    'professional' => 60,    // Medium boost (₱2,000+/month)
    'free' => 0,             // No boost
];
```

**Example Change:**
If you want to give free users a slight boost to be fair:

```php
private const SUBSCRIPTION_SCORES = [
    'premium' => 100,        
    'professional' => 60,    
    'free' => 20,            // ⬆️ Now free users get a small base score
];
```

### 3️⃣ Change Experience Scoring

```php
private const EXPERIENCE_CONFIG = [
    'completed_projects_multiplier' => 2,  // Each project = 2 points
    'years_experience_multiplier' => 3,    // Each year = 3 points
    'max_score' => 100,
];
```

**Example Change:**
If you want to value completed projects more than years:

```php
private const EXPERIENCE_CONFIG = [
    'completed_projects_multiplier' => 5,  // ⬆️ Increased from 2
    'years_experience_multiplier' => 2,    // ⬇️ Reduced from 3
    'max_score' => 100,
];
```

### 4️⃣ Adjust Price Scoring

```php
private const PRICE_CONFIG = [
    'within_budget_score' => 100,          // Perfect score if within budget
    'below_budget_penalty' => 20,          // Penalty if too cheap (suspicious)
    'above_budget_penalty_rate' => 100,    // Penalty rate for over-budget bids
];
```

---

## 🧮 How Scoring Works

### Price Score (0-100)
- **Within Budget Range**: 80-100 points (higher score for lower bids)
- **Below Budget**: 50-100 points (too cheap = suspicious)
- **Over Budget**: 0-80 points (heavily penalized)

### Experience Score (0-100)
```
Score = (Completed Projects × 2) + (Years Experience × 3)
Capped at 100 points
```

**Examples:**
- 10 projects, 5 years = (10×2) + (5×3) = **35 points**
- 20 projects, 10 years = (20×2) + (10×3) = **70 points**
- 30 projects, 15 years = **100 points** (capped)

### Reputation Score (0-100)
```
Base Score = (Average Rating / 5) × 100
Trust Bonus = +10 if 20+ reviews, +5 if 10+ reviews, +2 if 5+ reviews
No reviews = 50 points (neutral)
```

**Examples:**
- 4.5★ with 25 reviews = (4.5/5)×100 + 10 = **100 points**
- 4.0★ with 8 reviews = (4.0/5)×100 = **80 points**
- 3.0★ with 2 reviews = (3.0/5)×100 = **60 points**

### Subscription Score (0-100)
- **Premium** (₱5,000+): **100 points**
- **Professional** (₱2,000+): **60 points**
- **Free**: **0 points**

### Final Score Calculation
```
Total Score = 
  (Price Score × 35%) +
  (Experience Score × 30%) +
  (Reputation Score × 25%) +
  (Subscription Score × 10%)
```

---

## 📊 Viewing Bid Rankings (For Debugging)

Each bid now has a `score_breakdown` property (visible in development):

```php
foreach ($rankedBids as $bid) {
    echo "Company: {$bid->company_name}\n";
    echo "Total Score: {$bid->ranking_score}\n";
    echo "Breakdown:\n";
    echo "  - Price: {$bid->score_breakdown['price_score']}\n";
    echo "  - Experience: {$bid->score_breakdown['experience_score']}\n";
    echo "  - Reputation: {$bid->score_breakdown['reputation_score']}\n";
    echo "  - Subscription: {$bid->score_breakdown['subscription_score']}\n\n";
}
```

---

## 🔧 Customization Examples

### Example 1: Prioritize Budget-Friendly Bids
```php
private const WEIGHTS = [
    'price' => 50,           // ⬆️ Price is most important
    'experience' => 20,      // ⬇️ Less important
    'reputation' => 20,      
    'subscription' => 10,    
];
```

### Example 2: Prioritize Quality Over Price
```php
private const WEIGHTS = [
    'price' => 20,           // ⬇️ Price matters less
    'experience' => 35,      // ⬆️ Experience is critical
    'reputation' => 35,      // ⬆️ Reviews matter a lot
    'subscription' => 10,    
];
```

### Example 3: Boost Premium Contractors More
```php
private const WEIGHTS = [
    'price' => 30,           
    'experience' => 30,      
    'reputation' => 20,      
    'subscription' => 20,    // ⬆️ Doubled premium importance
];

private const SUBSCRIPTION_SCORES = [
    'premium' => 100,        
    'professional' => 40,    // ⬇️ Reduced
    'free' => 0,             
];
```

---

## 🎨 Frontend Display

You can display the ranking score to property owners:

```php
// In your frontend
"Rank #{$index + 1} - Score: {$bid->ranking_score}/100"

// Or show tier badge
$tier = $rankingService->getContractorSubscriptionTier($bid->contractor_id);
if ($tier === 'premium') {
    echo '<span class="badge-premium">⭐ Premium Contractor</span>';
}
```

---

## ⚠️ Important Notes

1. **All bids are ranked automatically** - you don't need to call the service manually unless you have a special case
2. **Modify weights anytime** - changes take effect immediately, no database changes needed
3. **Subscription detection** - currently based on `platform_payments` table with `payment_for = 'boosted_post'`
4. **Review integration** - uses existing `reviews` table with `reviewee_user_id`
5. **Score breakdown** - available for debugging, can be removed in production if needed

---

## 🧪 Testing Your Changes

After modifying weights:

1. Open any project with bids
2. Check the order of bids
3. Review the `score_breakdown` to verify calculations
4. Adjust weights until you're satisfied with the results

---

## 📝 Future Enhancements

You can easily add more scoring factors by:

1. Adding new config constants at the top
2. Creating a new `calculateXScore()` method
3. Including it in the `calculateBidScore()` method
4. Adjusting weights accordingly

**Example future additions:**
- Distance score (local contractors ranked higher)
- Response time score (fast responders ranked higher)
- Certification score (PCAB category bonus)
- Project type match score (specialty matching)

---

## 🆘 Support

If you need to modify the algorithm or add new features:
1. All configuration is at the **top of the file** (constants)
2. All calculation logic is in **private methods** (easy to modify)
3. The service is **fully documented** with comments

**Need help?** Check the comments in `BidRankingService.php` - each method is thoroughly documented.

