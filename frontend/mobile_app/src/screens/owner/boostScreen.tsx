import React, { useEffect, useState, useCallback } from 'react';
import {
	View,
	Text,
	StyleSheet,
	ScrollView,
	FlatList,
	TouchableOpacity,
	Image,
	ActivityIndicator,
	Alert,
	Linking,
	RefreshControl,
	SafeAreaView,
	TextInput,
	StatusBar,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { api_request, api_config } from '../../config/api';

export default function BoostScreen({ navigation }: any) {
	const [loading, setLoading] = useState(false);
	const [refreshing, setRefreshing] = useState(false);
	const [projects, setProjects] = useState<any[]>([]);
	const [boostedPosts, setBoostedPosts] = useState<any[]>([]);
	const [selectedTab, setSelectedTab] = useState<'active' | 'dashboard'>('active');
	const [searchQuery, setSearchQuery] = useState<string>('');
	const [processingId, setProcessingId] = useState<number | null>(null);

	const calcBoostProgress = (item: any) => {
		// Prefer server-calculated percentage if provided
		const serverPct = typeof item.percentage === 'number' && Number.isFinite(item.percentage)
			? Math.max(0, Math.min(100, Math.round(item.percentage)))
			: null;

		const exp = item.ends_at || item.expiration_date || item.expiration || item.expire_at || null;
		const start = item.starts_at || item.start_at || item.transaction_date || item.created_at || item.createdAt || null;

		// days left (if expiry exists)
		const daysLeft = exp ? Math.ceil((new Date(exp).getTime() - Date.now()) / (1000 * 60 * 60 * 24)) : 0;

		if (serverPct !== null) {
			return { pct: serverPct, daysLeft };
		}

		if (exp && start) {
			const startMs = new Date(start).getTime();
			const expMs = new Date(exp).getTime();
			const total = Math.max(1, expMs - startMs);
			const elapsed = Math.max(0, Date.now() - startMs);
			const pct = Math.max(0, Math.min(100, Math.round((elapsed / total) * 100)));
			return { pct, daysLeft };
		}

		if (exp) {
			return { pct: 100, daysLeft };
		}

		return { pct: 0, daysLeft: 0 };
	};

	const fetchModalData = useCallback(async (isRefresh = false) => {
		if (isRefresh) setRefreshing(true);
		else setLoading(true);
		try {
			const res = await api_request('/subs/modal-data', { method: 'GET' });
			if (res.success && res.data) {
				setProjects(res.data.boostableProjects || []);
				setBoostedPosts(res.data.boostedPosts || []);
			} else {
				Alert.alert('Notice', res.message || 'No projects found');
			}
		} catch (e) {
			Alert.alert('Connection Error', 'Failed to reach server');
		} finally {
			setLoading(false);
			setRefreshing(false);
		}
	}, []);

	useEffect(() => {
		fetchModalData();
	}, [fetchModalData]);

	const handleBoost = async (projectId: number) => {
		setProcessingId(projectId);
		try {
			const returnUrl = `exp://192.168.100.27:8081/--/payment-callback?project_id=${projectId}`;
			const res = await api_request('/api/boost/checkout', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ project_id: projectId, return_url: returnUrl }),
			});
			if (res.success && res.data?.checkout_url) {
				await Linking.openURL(res.data.checkout_url);
				pollForApproval(projectId);
				setProcessingId(null);
			} else {
				Alert.alert('Checkout Error', res.message || 'Payment gateway unavailable');
				setProcessingId(null);
			}
		} catch (e) {
			Alert.alert('Error', 'Could not initiate payment');
			setProcessingId(null);
		}
	};

	const pollForApproval = async (projectId: number) => {
		let attempts = 0;
		const interval = setInterval(async () => {
			attempts++;
			if (attempts > 15) clearInterval(interval);
			const res = await api_request('/subs/modal-data', { method: 'GET' });
			const found = res.data?.boostedPosts?.find((b: any) => b.id === projectId || b.project_id === projectId);
			if (found) {
				clearInterval(interval);
				Alert.alert('Success!', 'Your post is now boosted.');
				fetchModalData(true);
			}
		}, 4000);
	};

	const formatDiffForHumans = (dateStr: any) => {
		if (!dateStr) return 'N/A';
		const then = new Date(dateStr);
		if (isNaN(then.getTime())) return 'N/A';
		const diffMs = then.getTime() - Date.now();
		const absSec = Math.round(Math.abs(diffMs) / 1000);
		const minutes = Math.round(absSec / 60);
		const hours = Math.round(minutes / 60);
		const days = Math.round(hours / 24);
		const weeks = Math.round(days / 7);
		const months = Math.round(days / 30);
		const years = Math.round(days / 365);
		const suffix = diffMs > 0 ? 'from now' : 'ago';
		if (absSec < 60) return diffMs > 0 ? 'in a few seconds' : 'a few seconds ago';
		if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ${suffix}`;
		if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ${suffix}`;
		if (days < 7) return `${days} day${days === 1 ? '' : 's'} ${suffix}`;
		if (weeks < 5) return `${weeks} week${weeks === 1 ? '' : 's'} ${suffix}`;
		if (months < 12) return `${months} month${months === 1 ? '' : 's'} ${suffix}`;
		return `${years} year${years === 1 ? '' : 's'} ${suffix}`;
	};

	const renderItem = ({ item }: { item: any }) => {
		const { pct, daysLeft } = calcBoostProgress(item);
		const endsAt = item.ends_at || item.expiration_date || item.expiration || item.expire_at || item.endsAt || null;
		const endsAtText = formatDiffForHumans(endsAt);
		const img = item.image_url || item.image || (item.files && item.files[0]) || item.project_image || null;
		const imgUrl = img ? (typeof img === 'string' && img.startsWith('http') ? img : `${api_config.base_url}/storage/${img}`) : null;

		const getProgressColor = (d: number | null) => {
			if (d === null) return '#EC7E00';
			if (d <= 1) return '#D84315';
			if (d <= 3) return '#EF6C00';
			if (d <= 7) return '#FB8C00';
			return '#FFC107';
		};
		const getProgressBg = (d: number | null) => {
			if (d === null) return '#FFF3E0';
			if (d <= 1) return '#FFEBEE';
			if (d <= 3) return '#FFCCBC';
			if (d <= 7) return '#FFF3E0';
			return '#FFF8E1';
		};

		const normalizedPct = typeof pct === 'number' && !isNaN(pct) && Number.isFinite(pct) ? Math.max(0, Math.min(100, pct)) : 0;
		const fillPct = normalizedPct > 0 ? Math.max(normalizedPct, 6) : 0;
		const fillWidth = `${Number.isFinite(fillPct) ? fillPct : 0}%` as any;

		return (
			<View style={styles.boostCard}>
				{imgUrl ? (
					<View style={styles.thumb}><Image source={{ uri: imgUrl }} style={{ width: '100%', height: '100%', borderRadius: 8 }} /></View>
				) : (
					<View style={[styles.thumb, { justifyContent: 'center', alignItems: 'center' }]}>
						<Text style={{ color: '#999' }}>No image</Text>
					</View>
				)}
				<View style={styles.boostContent}>
					<Text style={styles.projectTitle}>{item.title || item.project_title || 'Untitled'}</Text>
					<Text style={styles.infoLine}>{item.owner_name || item.owner || 'Only me'}</Text>
					<Text style={styles.infoLine}>{item.address || item.location || 'Unknown location'}</Text>

					{selectedTab === 'active' && (
						(processingId === Number(item.id || item.project_id || item.projectId || 0)) ? (
							<View style={styles.actionButton}>
								<ActivityIndicator size="small" color="#FFF" />
								<Text style={[styles.actionButtonText, { marginLeft: 8 }]}>Processing...</Text>
							</View>
						) : (
							<TouchableOpacity style={styles.actionButton} onPress={() => handleBoost(Number(item.id || item.project_id || item.projectId || 0))}>
								<Text style={styles.actionButtonText}>Boost Now</Text>
							</TouchableOpacity>
						)
					)}

					{selectedTab === 'dashboard' ? (
						<View style={styles.progressRow}>
							<View style={[styles.progressBarBackground, { backgroundColor: getProgressBg(daysLeft) }]}>
								<View style={[styles.progressBarFill, { width: fillWidth, backgroundColor: getProgressColor(daysLeft), zIndex: 2 }]} />
							</View>
							<Text style={styles.progressPercent}>{Number.isFinite(pct) ? `${pct}%` : '0%'}</Text>
						</View>
					) : (
						<Text style={styles.dueText}>{endsAt ? `Due: ${endsAtText}` : (item.date ? `Due: ${item.date}` : 'Due date: N/A')}</Text>
					)}

					{selectedTab === 'dashboard' && (
						<Text style={styles.expireText}>Boost will end in: {endsAtText}</Text>
					)}
				</View>
			</View>
		);
	};

	// Reuse renderItem to render boosted items (accepts raw item)
	const renderBoostedItem = (item: any) => {
		return renderItem({ item });
	};

	return (
		<SafeAreaView style={styles.container}>
			<StatusBar barStyle="light-content" backgroundColor="#EC7E00" />

			{/* Redesigned Header with Gradient */}
			<LinearGradient
				colors={['#EC7E00', '#F9A826']}
				start={{ x: 0, y: 0 }}
				end={{ x: 1, y: 1 }}
				style={styles.headerGradient}
			>
				<View style={styles.headerContent}>
					<TouchableOpacity
						style={styles.backButton}
						onPress={() => {
							try {
								navigation.navigate('Profile');
							} catch (e) {
								navigation.goBack();
							}
						}}
					>
						<Ionicons name="arrow-back" size={24} color="#FFF" />
					</TouchableOpacity>

					<View style={styles.headerTextContainer}>
						<Text style={styles.headerTitle}>Boost Your Posts</Text>
						<Text style={styles.headerSubtitle}>Get more visibility & bids</Text>
					</View>

					<View style={styles.headerRight}>
						<View style={styles.priceBadge}>
							<Text style={styles.priceBadgeText}>₱49</Text>
						</View>
					</View>
				</View>

				{/* Stats Preview Card */}
				<View style={styles.statsPreviewCard}>
					<View style={styles.statItem}>
						<Text style={styles.statValue}>{boostedPosts.length}</Text>
						<Text style={styles.statLabel}>Active Boosts</Text>
					</View>
					<View style={styles.statDivider} />
					<View style={styles.statItem}>
						<Text style={styles.statValue}>{projects.length}</Text>
						<Text style={styles.statLabel}>Available</Text>
					</View>
					<View style={styles.statDivider} />
					<View style={styles.statItem}>
						<Text style={styles.statValue}>49</Text>
						<Text style={styles.statLabel}>Price (₱)</Text>
					</View>
				</View>
			</LinearGradient>

			{/* Tab Bar */}
			<View style={styles.tabBar}>
				<TouchableOpacity
					style={[styles.tabButton, selectedTab === 'active' && styles.tabButtonActive]}
					onPress={() => setSelectedTab('active')}
				>
					<Ionicons
						name={selectedTab === 'active' ? "rocket" : "rocket-outline"}
						size={18}
						color={selectedTab === 'active' ? '#FFF' : '#666'}
						style={styles.tabIcon}
					/>
					<Text style={[styles.tabText, selectedTab === 'active' && styles.tabTextActive]}>Available</Text>
				</TouchableOpacity>

				<TouchableOpacity
					style={[styles.tabButton, selectedTab === 'dashboard' && styles.tabButtonActive]}
					onPress={() => setSelectedTab('dashboard')}
				>
					<Ionicons
						name={selectedTab === 'dashboard' ? "bar-chart" : "bar-chart-outline"}
						size={18}
						color={selectedTab === 'dashboard' ? '#FFF' : '#666'}
						style={styles.tabIcon}
					/>
					<Text style={[styles.tabText, selectedTab === 'dashboard' && styles.tabTextActive]}>Dashboard</Text>
				</TouchableOpacity>
			</View>

			{loading ? (
				<ActivityIndicator size="large" color="#EC7E00" style={{ marginTop: 50 }} />
			) : selectedTab === 'active' ? (
				<>
					<View style={styles.searchContainer}>
						<Ionicons name="search" size={20} color="#999" style={styles.searchIcon} />
						<TextInput
							style={styles.searchInput}
							placeholder="Search projects by title or location..."
							placeholderTextColor="#999"
							value={searchQuery}
							onChangeText={setSearchQuery}
							returnKeyType="search"
						/>
						{searchQuery.length > 0 && (
							<TouchableOpacity onPress={() => setSearchQuery('')}>
								<Ionicons name="close-circle" size={20} color="#999" />
							</TouchableOpacity>
						)}
					</View>

					<FlatList
						data={projects.filter((p: any) => {
							const q = (searchQuery || '').trim().toLowerCase();
							if (!q) return true;
							const title = (p.title || p.project_title || '').toString().toLowerCase();
							const desc = (p.description || p.project_description || '').toString().toLowerCase();
							const loc = (p.location || p.project_location || p.address || '').toString().toLowerCase();
							return title.includes(q) || desc.includes(q) || loc.includes(q);
						})}
						keyExtractor={(item) => String(item.id || item.project_id || Math.random())}
						renderItem={renderItem}
						contentContainerStyle={styles.listContent}
						refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => fetchModalData(true)} tintColor="#EC7E00" />}
						ListEmptyComponent={
							<View style={styles.emptyContainer}>
								<Ionicons name="rocket-outline" size={48} color="#DDD" />
								<Text style={styles.emptyText}>No projects available to boost</Text>
								<Text style={styles.emptySubtext}>Create a project first to boost it</Text>
							</View>
						}
					/>
				</>
			) : (
				<ScrollView contentContainerStyle={{ padding: 12 }}>
					<View style={styles.analyticsRow}>
						<View style={styles.analyticsCard}>
							<Ionicons name="eye-outline" size={24} color="#EC7E00" />
							<Text style={styles.analyticsLabel}>Post reach</Text>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsMeta}>0% • 7d</Text>
						</View>
						<View style={styles.analyticsCard}>
							<Ionicons name="chatbubble-outline" size={24} color="#EC7E00" />
							<Text style={styles.analyticsLabel}>Bids</Text>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsMeta}>0% • 7d</Text>
						</View>
						<View style={styles.analyticsCard}>
							<Ionicons name="hand-right-outline" size={24} color="#EC7E00" />
							<Text style={styles.analyticsLabel}>Clicks</Text>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsMeta}>0% • 7d</Text>
						</View>
					</View>

					<View style={styles.sectionHeader}>
						<Text style={styles.sectionTitle}>Boosted posts</Text>
						<Text style={styles.sectionCount}>{boostedPosts.length}</Text>
					</View>

					{boostedPosts && boostedPosts.length > 0 ? (
						boostedPosts.map((b: any) => (
							<View key={String(b.id || b.project_id || b._id || Math.random())}>
								{renderBoostedItem(b)}
							</View>
						))
					) : (
						<View style={styles.emptyContainer}>
							<Ionicons name="trending-up-outline" size={48} color="#DDD" />
							<Text style={styles.emptyText}>No boosted posts yet</Text>
							<Text style={styles.emptySubtext}>Switch to Available tab to boost your first project</Text>
						</View>
					)}
				</ScrollView>
			)}
		</SafeAreaView>
	);
}

const styles = StyleSheet.create({
	container: {
		flex: 1,
		backgroundColor: '#F8F9FA'
	},
	headerGradient: {
		paddingTop: 20,
		paddingBottom: 30,
		borderBottomLeftRadius: 30,
		borderBottomRightRadius: 30,
		elevation: 8,
		shadowColor: '#EC7E00',
		shadowOffset: { width: 0, height: 4 },
		shadowOpacity: 0.3,
		shadowRadius: 12,
	},
	headerContent: {
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'space-between',
		paddingHorizontal: 20,
		paddingBottom: 20,
	},
	backButton: {
		width: 40,
		height: 40,
		borderRadius: 20,
		backgroundColor: 'rgba(255,255,255,0.2)',
		justifyContent: 'center',
		alignItems: 'center',
	},
	headerTextContainer: {
		flex: 1,
		marginLeft: 12,
	},
	headerTitle: {
		fontSize: 20,
		fontWeight: '800',
		color: '#FFF',
	},
	headerSubtitle: {
		fontSize: 13,
		color: 'rgba(255,255,255,0.9)',
		marginTop: 2,
	},
	headerRight: {
		alignItems: 'flex-end',
	},
	priceBadge: {
		backgroundColor: '#FFF',
		paddingHorizontal: 12,
		paddingVertical: 6,
		borderRadius: 20,
	},
	priceBadgeText: {
		color: '#EC7E00',
		fontWeight: '800',
		fontSize: 16,
	},
	statsPreviewCard: {
		flexDirection: 'row',
		backgroundColor: '#FFF',
		marginHorizontal: 20,
		paddingVertical: 16,
		paddingHorizontal: 12,
		borderRadius: 16,
		elevation: 4,
		shadowColor: '#000',
		shadowOffset: { width: 0, height: 2 },
		shadowOpacity: 0.1,
		shadowRadius: 8,
	},
	statItem: {
		flex: 1,
		alignItems: 'center',
	},
	statValue: {
		fontSize: 20,
		fontWeight: '800',
		color: '#333',
	},
	statLabel: {
		fontSize: 12,
		color: '#999',
		marginTop: 4,
	},
	statDivider: {
		width: 1,
		height: '70%',
		backgroundColor: '#EEE',
		alignSelf: 'center',
	},
	tabBar: {
		flexDirection: 'row',
		padding: 16,
		justifyContent: 'center',
		backgroundColor: '#FFF',
		marginHorizontal: 16,
		marginTop: -20,
		borderRadius: 30,
		elevation: 4,
		shadowColor: '#000',
		shadowOffset: { width: 0, height: 2 },
		shadowOpacity: 0.05,
		shadowRadius: 8,
	},
	tabButton: {
		flexDirection: 'row',
		alignItems: 'center',
		paddingHorizontal: 20,
		paddingVertical: 10,
		borderRadius: 25,
		marginHorizontal: 8,
	},
	tabIcon: {
		marginRight: 6,
	},
	tabButtonActive: {
		backgroundColor: '#EC7E00',
		elevation: 2,
	},
	tabText: {
		color: '#666',
		fontWeight: '600',
		fontSize: 14,
	},
	tabTextActive: {
		color: '#FFF'
	},
	searchContainer: {
		flexDirection: 'row',
		alignItems: 'center',
		backgroundColor: '#FFF',
		marginHorizontal: 16,
		marginVertical: 12,
		paddingHorizontal: 16,
		paddingVertical: 8,
		borderRadius: 12,
		borderWidth: 1,
		borderColor: '#EFEFEF',
		elevation: 2,
		shadowColor: '#000',
		shadowOffset: { width: 0, height: 1 },
		shadowOpacity: 0.05,
		shadowRadius: 4,
	},
	searchIcon: {
		marginRight: 8,
	},
	searchInput: {
		flex: 1,
		fontSize: 14,
		color: '#333',
		paddingVertical: 8,
	},
	listContent: {
		padding: 16,
		paddingTop: 8,
	},
	title: { fontSize: 22, fontWeight: '800', color: '#1A1A1A', marginBottom: 8 },
	headerSection: { backgroundColor: '#FFF', padding: 24, borderBottomWidth: 1, borderBottomColor: '#EEE', alignItems: 'center' },
	priceTag: { backgroundColor: '#FFF3E0', paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20, borderWidth: 1, borderColor: '#FFE0B2', marginBottom: 12 },
	priceText: { color: '#E65100', fontWeight: '700', fontSize: 14 },
	description: { textAlign: 'center', color: '#666', lineHeight: 20, fontSize: 14 },
	card: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 16, flexDirection: 'row', alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2 },
	cardContent: { flex: 1 },
	projectTitle: { fontSize: 16, fontWeight: '700', color: '#333' },
	projectStatus: { fontSize: 12, color: '#999', marginTop: 4 },
	actionButton: {
		backgroundColor: '#EC7E00',
		paddingHorizontal: 16,
		paddingVertical: 10,
		borderRadius: 20,
		minWidth: 100,
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'center',
		marginTop: 8,
		elevation: 2,
	},
	disabledButton: { backgroundColor: '#FFCC80' },
	actionButtonText: { color: '#FFF', fontWeight: '700', fontSize: 14 },
	emptyContainer: {
		marginTop: 60,
		alignItems: 'center',
		paddingHorizontal: 40,
	},
	emptyText: {
		color: '#666',
		fontSize: 16,
		fontWeight: '600',
		marginTop: 12,
	},
	emptySubtext: {
		color: '#999',
		fontSize: 14,
		textAlign: 'center',
		marginTop: 4,
	},
	infoBox: { marginTop: 8 },
	infoLineBold: { color: '#444', fontWeight: '700', fontSize: 13, marginBottom: 4 },
	infoLine: { color: '#666', fontSize: 13, marginBottom: 2 },
	infoLineDate: { color: '#777', fontSize: 12, marginTop: 6 },
	analyticsRow: {
		flexDirection: 'row',
		justifyContent: 'space-between',
		marginBottom: 16,
	},
	analyticsCard: {
		flex: 1,
		backgroundColor: '#FFF',
		padding: 16,
		borderRadius: 16,
		marginHorizontal: 4,
		alignItems: 'center',
		elevation: 2,
		shadowColor: '#000',
		shadowOffset: { width: 0, height: 2 },
		shadowOpacity: 0.05,
		shadowRadius: 8,
	},
	analyticsLabel: { fontSize: 12, color: '#666', marginTop: 4 },
	analyticsValue: { fontSize: 24, fontWeight: '800', color: '#333', marginTop: 4 },
	analyticsMeta: { fontSize: 11, color: '#999', marginTop: 4 },
	boostCard: {
		backgroundColor: '#FFF',
		borderRadius: 16,
		padding: 16,
		marginBottom: 12,
		flexDirection: 'row',
		alignItems: 'center',
		elevation: 2,
		shadowColor: '#000',
		shadowOffset: { width: 0, height: 1 },
		shadowOpacity: 0.05,
		shadowRadius: 4,
	},
	thumb: { width: 80, height: 80, borderRadius: 12, overflow: 'hidden', marginRight: 16, backgroundColor: '#F5F5F5' },
	boostContent: { flex: 1 },
	progressRow: { flexDirection: 'row', alignItems: 'center', marginTop: 8 },
	progressBarBackground: { height: 8, backgroundColor: '#EEE', borderRadius: 4, flex: 1, overflow: 'hidden', position: 'relative' },
	progressBarFill: { height: 8, backgroundColor: '#EC7E00', borderRadius: 4, position: 'absolute', left: 0, top: 0, bottom: 0 },
	progressPercent: { marginLeft: 8, fontWeight: '700', color: '#333', fontSize: 13 },
	expireText: { marginTop: 6, color: '#777', fontSize: 11 },
	dueText: { marginTop: 8, color: '#333', fontSize: 13, fontWeight: '600' },
	sectionHeader: {
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'space-between',
		marginBottom: 12,
	},
	sectionTitle: {
		fontSize: 16,
		fontWeight: '700',
		color: '#333',
	},
	sectionCount: {
		backgroundColor: '#F0F0F0',
		paddingHorizontal: 8,
		paddingVertical: 4,
		borderRadius: 12,
		fontSize: 12,
		color: '#666',
	},
});
