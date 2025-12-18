# Frontend Display Examples for Bid Rankings

## 📱 Mobile App Example (React Native)

### Example 1: Display Bids with Ranking Score

```typescript
// In projectBids.tsx or similar component

interface Bid {
  bid_id: number;
  company_name: string;
  proposed_cost: number;
  ranking_score: number;  // ✨ This is automatically added by BidRankingService
  score_breakdown: {
    price_score: number;
    experience_score: number;
    reputation_score: number;
    subscription_score: number;
  };
  // ... other bid properties
}

const ProjectBidsScreen = () => {
  const [bids, setBids] = useState<Bid[]>([]);

  const fetchBids = async () => {
    const response = await projects_service.get_project_bids(projectId);
    // Bids are already ranked by backend!
    setBids(response.data);
  };

  return (
    <ScrollView>
      {bids.map((bid, index) => (
        <View key={bid.bid_id} style={styles.bidCard}>
          
          {/* Rank Badge */}
          <View style={styles.rankBadge}>
            <Text style={styles.rankText}>#{index + 1}</Text>
          </View>

          {/* Company Info */}
          <Text style={styles.companyName}>{bid.company_name}</Text>
          
          {/* Ranking Score Bar */}
          <View style={styles.scoreContainer}>
            <Text style={styles.scoreLabel}>Match Score</Text>
            <View style={styles.scoreBar}>
              <View 
                style={[
                  styles.scoreBarFill, 
                  { width: `${bid.ranking_score}%` }
                ]} 
              />
            </View>
            <Text style={styles.scoreValue}>{bid.ranking_score}/100</Text>
          </View>

          {/* Price */}
          <Text style={styles.price}>₱{bid.proposed_cost.toLocaleString()}</Text>

          {/* Optional: Show breakdown on expand */}
          {expanded && (
            <View style={styles.breakdown}>
              <Text>Price Score: {bid.score_breakdown.price_score.toFixed(1)}</Text>
              <Text>Experience: {bid.score_breakdown.experience_score.toFixed(1)}</Text>
              <Text>Reviews: {bid.score_breakdown.reputation_score.toFixed(1)}</Text>
              <Text>Premium: {bid.score_breakdown.subscription_score.toFixed(1)}</Text>
            </View>
          )}

        </View>
      ))}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  bidCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  rankBadge: {
    position: 'absolute',
    top: 12,
    right: 12,
    backgroundColor: '#EC7E00',
    borderRadius: 16,
    width: 32,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rankText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 14,
  },
  scoreContainer: {
    marginTop: 8,
  },
  scoreLabel: {
    fontSize: 12,
    color: '#64748B',
    marginBottom: 4,
  },
  scoreBar: {
    height: 8,
    backgroundColor: '#E2E8F0',
    borderRadius: 4,
    overflow: 'hidden',
  },
  scoreBarFill: {
    height: '100%',
    backgroundColor: '#10B981', // Green
    borderRadius: 4,
  },
  scoreValue: {
    fontSize: 12,
    color: '#0F172A',
    marginTop: 4,
    fontWeight: '600',
  },
});
```

### Example 2: Color-Coded Ranking

```typescript
const getRankColor = (rank: number) => {
  if (rank === 1) return '#FFD700'; // Gold
  if (rank === 2) return '#C0C0C0'; // Silver
  if (rank === 3) return '#CD7F32'; // Bronze
  return '#64748B'; // Gray
};

const getRankLabel = (rank: number) => {
  if (rank === 1) return '🥇 Best Match';
  if (rank === 2) return '🥈 Great Match';
  if (rank === 3) return '🥉 Good Match';
  return `#${rank}`;
};

// In your render
<View style={[styles.rankBadge, { backgroundColor: getRankColor(index + 1) }]}>
  <Text style={styles.rankText}>{getRankLabel(index + 1)}</Text>
</View>
```

### Example 3: Subscription Tier Badge

```typescript
// Call the API to get contractor tier
const getSubscriptionBadge = (contractorId: number) => {
  // Backend endpoint: /api/contractor/{id}/subscription-tier
  // Returns: { tier: 'premium' | 'professional' | 'free' }
};

// Display badge
const SubscriptionBadge = ({ tier }: { tier: string }) => {
  if (tier === 'free') return null;
  
  return (
    <View style={[
      styles.tierBadge,
      tier === 'premium' ? styles.premiumBadge : styles.professionalBadge
    ]}>
      <Feather 
        name={tier === 'premium' ? 'star' : 'award'} 
        size={12} 
        color="#fff" 
      />
      <Text style={styles.tierText}>
        {tier === 'premium' ? 'PREMIUM' : 'PRO'}
      </Text>
    </View>
  );
};
```

---

## 🌐 Web Dashboard Example (Blade/PHP)

### Example 1: Project Owner Bid List

```php
<!-- In resources/views/owner/project_bids.blade.php -->

<div class="bids-container">
    @foreach($bids as $index => $bid)
    <div class="bid-card rank-{{ $index + 1 }}">
        
        <!-- Rank Badge -->
        <div class="rank-badge">
            <span class="rank-number">#{{ $index + 1 }}</span>
            @if($index === 0)
                <span class="rank-label">Recommended</span>
            @endif
        </div>

        <!-- Match Score Progress Bar -->
        <div class="match-score">
            <label>Match Score</label>
            <div class="progress-bar">
                <div 
                    class="progress-fill" 
                    style="width: {{ $bid->ranking_score }}%"
                ></div>
            </div>
            <span class="score-value">{{ number_format($bid->ranking_score, 1) }}/100</span>
        </div>

        <!-- Company Info -->
        <h3>{{ $bid->company_name }}</h3>
        <p class="price">₱{{ number_format($bid->proposed_cost, 2) }}</p>

        <!-- Score Breakdown (Optional Tooltip) -->
        <div class="score-breakdown" data-toggle="tooltip">
            <i class="fas fa-info-circle"></i>
            <div class="tooltip-content">
                <p>Price Score: {{ number_format($bid->score_breakdown['price_score'], 1) }}</p>
                <p>Experience: {{ number_format($bid->score_breakdown['experience_score'], 1) }}</p>
                <p>Reviews: {{ number_format($bid->score_breakdown['reputation_score'], 1) }}</p>
                <p>Premium: {{ number_format($bid->score_breakdown['subscription_score'], 1) }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <button class="btn-accept">Accept Bid</button>
    </div>
    @endforeach
</div>

<style>
.bid-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: relative;
}

.bid-card.rank-1 {
    border: 2px solid #FFD700;
    background: linear-gradient(to right, #FFFEF7, white);
}

.rank-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    text-align: center;
}

.rank-number {
    display: inline-block;
    background: #EC7E00;
    color: white;
    padding: 4px 12px;
    border-radius: 16px;
    font-weight: bold;
}

.rank-label {
    display: block;
    font-size: 12px;
    color: #FFD700;
    font-weight: 600;
    margin-top: 4px;
}

.match-score {
    margin: 16px 0;
}

.progress-bar {
    height: 10px;
    background: #E2E8F0;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(to right, #10B981, #059669);
    transition: width 0.3s ease;
}

.score-value {
    font-size: 14px;
    font-weight: 600;
    color: #10B981;
}
</style>
```

---

## 📊 Admin Dashboard Example

### Display All Contractors with Rankings

```php
<!-- In admin dashboard -->

@php
    use App\Services\BidRankingService;
    $rankingService = new BidRankingService();
@endphp

<table class="contractors-table">
    <thead>
        <tr>
            <th>Company</th>
            <th>Subscription</th>
            <th>Rating</th>
            <th>Reviews</th>
            <th>Completed Projects</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contractors as $contractor)
        @php
            $tier = $rankingService->getContractorSubscriptionTier($contractor->contractor_id);
            $rating = $rankingService->getContractorAverageRating($contractor->contractor_id);
            $reviewCount = $rankingService->getContractorReviewCount($contractor->contractor_id);
        @endphp
        <tr>
            <td>{{ $contractor->company_name }}</td>
            <td>
                @if($tier === 'premium')
                    <span class="badge badge-premium">⭐ Premium</span>
                @elseif($tier === 'professional')
                    <span class="badge badge-professional">🏆 Professional</span>
                @else
                    <span class="badge badge-free">Free</span>
                @endif
            </td>
            <td>
                @if($rating > 0)
                    <span class="rating">{{ number_format($rating, 1) }}⭐</span>
                @else
                    <span class="text-muted">No ratings</span>
                @endif
            </td>
            <td>{{ $reviewCount }} reviews</td>
            <td>{{ $contractor->completed_projects }} projects</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## 🎨 UI/UX Recommendations

### 1. Visual Hierarchy
- **Top 3 bids** should have special styling (gold, silver, bronze borders)
- **Top bid** should be highlighted with "Recommended" label
- Use **progress bars** or **score meters** to visualize ranking_score

### 2. Transparency
- Show **score breakdown** in a tooltip or expandable section
- Display **why a bid ranked high** (e.g., "Great reviews", "Best price", "Premium contractor")

### 3. Trust Signals
- Show **subscription badges** prominently (Premium/Pro)
- Display **star ratings** next to company name
- Show **number of reviews** for credibility

### 4. Mobile-Friendly
- Use **cards** instead of tables
- Make **rank badges** visible at a glance
- Show **score bars** horizontally for easy scanning

---

## 🔗 Backend API Endpoints to Add

You may want to expose these endpoints for frontend:

```php
// In api.php routes
Route::get('/contractor/{id}/subscription-tier', function($id) {
    $service = new BidRankingService();
    return response()->json([
        'tier' => $service->getContractorSubscriptionTier($id)
    ]);
});

Route::get('/contractor/{id}/rating', function($id) {
    $service = new BidRankingService();
    return response()->json([
        'rating' => $service->getContractorAverageRating($id),
        'review_count' => $service->getContractorReviewCount($id)
    ]);
});
```

---

## 🎯 Key Takeaways

1. **Bids are pre-sorted** by the backend - just display them in order
2. **ranking_score** is 0-100, perfect for progress bars
3. **score_breakdown** helps explain rankings to users
4. **Top 3 bids** should get special visual treatment
5. **Subscription badges** build trust and justify premium pricing

