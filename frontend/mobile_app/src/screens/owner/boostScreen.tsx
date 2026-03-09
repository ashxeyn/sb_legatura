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
	RefreshControl,
	SafeAreaView,
	TextInput,
	StatusBar,
} from 'react-native';
import { Ionicons, Feather } from '@expo/vector-icons';
import * as Linking from 'expo-linking';
import * as WebBrowser from 'expo-web-browser';

// Color palette (milestoneDetail style)
const COLORS = {
	primary: '#1E3A5F',
	primaryLight: '#E8EEF4',
	accent: '#EC7E00',
	accentLight: '#FFF3E6',
	success: '#10B981',
	successLight: '#D1FAE5',
	warning: '#F59E0B',
	warningLight: '#FEF3C7',
	error: '#EF4444',
	errorLight: '#FEE2E2',
	info: '#3B82F6',
	infoLight: '#DBEAFE',
	background: '#F8FAFC',
	surface: '#FFFFFF',
	text: '#1E3A5F',
	textSecondary: '#64748B',
	textMuted: '#94A3B8',
	border: '#E2E8F0',
	borderLight: '#F1F5F9',
};
import { api_request, api_config } from '../../config/api';

export default function BoostScreen({ navigation }: any) {
	const [loading, setLoading] = useState(false);
	const [refreshing, setRefreshing] = useState(false);
	const [projects, setProjects] = useState<any[]>([]);
	const [boostedPosts, setBoostedPosts] = useState<any[]>([]);
	const [selectedTab, setSelectedTab] = useState<'active' | 'dashboard'>('active');
	const [searchQuery, setSearchQuery] = useState<string>('');
	const [processingId, setProcessingId] = useState<number | null>(null);
	const [failedImages, setFailedImages] = useState<Record<string, boolean>>({});

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
			const returnUrl = Linking.createURL('payment-callback', { queryParams: { project_id: String(projectId) } });
			console.log('Boost return URL:', returnUrl);
			const res = await api_request('/api/boost/checkout', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ project_id: projectId, return_url: returnUrl }),
			});
			if (res.success && res.data?.checkout_url) {
				// openAuthSessionAsync watches for the returnUrl redirect and auto-closes the browser
				const result = await WebBrowser.openAuthSessionAsync(
					res.data.checkout_url,
					returnUrl
				);
				console.log('WebBrowser result:', result);

				if (result.type === 'cancel' || result.type === 'dismiss') {
					// User closed the browser without completing payment
					Alert.alert('Payment Pending', 'Payment was not completed. You can try again anytime.');
					setProcessingId(null);
					return;
				}

				// Browser returned with a URL — start verifying & polling
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
			if (attempts > 15) {
				clearInterval(interval);
				return;
			}

			try {
				// First, verify the payment with PayMongo to set is_approved = 1
				const verifyRes = await api_request('/api/boost/verify', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ project_id: projectId }),
				});

				// If payment was approved, refresh and show success
				if (verifyRes.success && verifyRes.data?.approved) {
					clearInterval(interval);
					Alert.alert('Success!', 'Your post is now boosted.');
					fetchModalData(true);
					return;
				}

				// Fallback: check if boost already appears in boosted posts
				const res = await api_request('/subs/modal-data', { method: 'GET' });
				const found = res.data?.boostedPosts?.find((b: any) => b.id === projectId || b.project_id === projectId);
				if (found) {
					clearInterval(interval);
					Alert.alert('Success!', 'Your post is now boosted.');
					fetchModalData(true);
				}
			} catch (e) {
				console.warn('Poll verification error:', e);
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
		// Prefer project file "desired design" first, then fall back to API image fields.
		const files = item.project_files || item.files || item.project_files_list || [];
		let imgUrl: string | null = null;

		// --- STEP 1: PRIORITIZE THE DATABASE FILE (Desired Design) ---
		if (Array.isArray(files) && files.length > 0) {
			console.log('Project files for item:', (item.id || item.project_id || item.projectId || '?'), files);
			const readPath = (f: any) => f?.file_path || f?.path || f?.file || f?.fileUrl || f?.file_url || f?.url || f?.file_path_server || null;
			const desired = files.find((f: any) => {
				const ft = String(f.file_type || f.type || '').toLowerCase();
				return ft === 'desired design' || /desired[_ ]?design/.test(ft);
			});

			const pick = desired || files[0];
			const pth = readPath(pick);
			if (pth) {
				let rawPath = String(pth).replace(/\\/g, '/').replace(/^\\+/, '');
				if (rawPath.toLowerCase().startsWith('http')) {
					imgUrl = rawPath;
				} else if (rawPath.toLowerCase().startsWith('storage/')) {
					imgUrl = `${api_config.base_url}/${rawPath}`;
				} else {
					imgUrl = `${api_config.base_url}/storage/${rawPath}`;
				}
				try { imgUrl = encodeURI(imgUrl); } catch (e) { /* ignore */ }
				console.log('Boost image URL from project file:', imgUrl, 'picked:', pick);
			}
		}

		// --- STEP 2: FALLBACK TO image_url IF NO FILE FOUND ---
		if (!imgUrl && item.image_url) {
			imgUrl = String(item.image_url);
		}

		// --- STEP 3: ADDITIONAL FALLBACKS (other API image fields) ---
		if (!imgUrl) {
			const imageCandidates = ['image_url', 'image', 'thumbnail', 'cover_image', 'photo', 'imageUrl', 'image_path', 'imagePath'];
			for (const key of imageCandidates) {
				const v = item[key] || item[key.toString().replace(/_([a-z])/g, (m, p1) => p1.toUpperCase())];
				if (v) {
					imgUrl = String(v);
					break;
				}
			}
		}

		// --- STEP 4: FINAL FALLBACK ---
		if (!imgUrl) {
			imgUrl = 'https://via.placeholder.com/400x200?text=No+Design+Found';
		}

		// Normalize and ensure absolute URL where applicable
		if (imgUrl) {
			try {
				let raw = String(imgUrl).replace(/\\/g, '/');
				raw = raw.replace(/^\\+/, '');
				if (!raw.toLowerCase().startsWith('http')) {
					if (raw.toLowerCase().startsWith('storage/')) imgUrl = `${api_config.base_url}/${raw}`;
					else imgUrl = `${api_config.base_url}/storage/${raw}`;
				} else imgUrl = raw;
				imgUrl = encodeURI(imgUrl);
			} catch (e) { /* ignore */ }
		}

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

			const loadFailed = !!(imgUrl && failedImages[imgUrl]);

		return (
			<View style={styles.fdCard}>
				{/* Project image */}
				{imgUrl && !loadFailed ? (
					<View style={styles.cardImgWrap}>
						<Image
							source={{ uri: imgUrl }}
							style={styles.cardImg}
							resizeMode="cover"
							onLoad={() => console.log('Boost image loaded:', imgUrl)}
							onError={(e) => {
								console.warn('Boost image load error:', imgUrl, e.nativeEvent || e);
								setFailedImages(prev => ({ ...prev, [imgUrl]: true }));
							}}
						/>
					</View>
				) : (
					<View style={[styles.cardImgWrap, styles.cardImgPlaceholder]}>
						<Feather name="image" size={28} color={COLORS.textMuted} />
						<Text style={styles.cardImgPlaceholderText}>No design image</Text>
					</View>
				)}

				{/* Card body */}
				<View style={styles.cardBody}>
					<Text style={styles.cardLabel}>PROJECT</Text>
					<Text style={styles.cardTitle} numberOfLines={2}>
						{item.title || item.project_title || 'Untitled Project'}
					</Text>

					{/* Meta badges */}
					<View style={styles.cardMeta}>
						{(item.address || item.location) ? (
							<View style={[styles.metaBadge, { backgroundColor: COLORS.primaryLight }]}>
								<Feather name="map-pin" size={9} color={COLORS.primary} />
								<Text style={[styles.metaBadgeText, { color: COLORS.primary }]} numberOfLines={1}>
									{item.address || item.location}
								</Text>
							</View>
						) : null}
						{(item.category || item.project_type) ? (
							<View style={[styles.metaBadge, { backgroundColor: COLORS.accentLight }]}>
								<Text style={[styles.metaBadgeText, { color: COLORS.accent }]}>
									{item.category || item.project_type}
								</Text>
							</View>
						) : null}
					</View>

					{/* Boost button — Available tab */}
					{selectedTab === 'active' && (
						<View style={styles.cardActions}>
							{processingId === Number(item.id || item.project_id || item.projectId || 0) ? (
								<View style={[styles.boostBtn, { opacity: 0.75 }]}>
									<ActivityIndicator size="small" color="#FFF" style={{ marginRight: 8 }} />
									<Text style={styles.boostBtnText}>Processing…</Text>
								</View>
							) : (
								<TouchableOpacity
									style={styles.boostBtn}
									onPress={() => handleBoost(Number(item.id || item.project_id || item.projectId || 0))}
								>
									<Feather name="zap" size={14} color="#FFF" style={{ marginRight: 6 }} />
									<Text style={styles.boostBtnText}>Boost Now  ·  ₱49</Text>
								</TouchableOpacity>
							)}
						</View>
					)}

					{/* Progress — Dashboard tab */}
					{selectedTab === 'dashboard' && (
						<View style={styles.progressSection}>
							<View style={styles.progressLabelRow}>
								<Text style={styles.progressLabel}>BOOST PROGRESS</Text>
								<Text style={styles.progressPct}>{Number.isFinite(pct) ? `${pct}%` : '0%'}</Text>
							</View>
							<View style={[styles.progressBg, { backgroundColor: getProgressBg(daysLeft) }]}>
								<View style={[styles.progressFill, { width: fillWidth, backgroundColor: getProgressColor(daysLeft) }]} />
							</View>
							<View style={styles.expireRow}>
								<Feather name="clock" size={11} color={COLORS.textMuted} />
								<Text style={styles.expireText}>Ends {endsAtText}</Text>
							</View>
						</View>
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
			<StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

			{/* ── Header ── */}
			<View style={styles.header}>
				<TouchableOpacity
					onPress={() => { try { navigation.navigate('Profile'); } catch (e) { navigation.goBack(); } }}
					style={styles.backBtn}
				>
					<Feather name="chevron-left" size={24} color={COLORS.text} />
					<Text style={styles.backText}>Back</Text>
				</TouchableOpacity>
				<View style={styles.headerCenter}>
					<Text style={styles.headerTitle}>Boost Your Project</Text>
					<Text style={styles.headerSub}>Get more contractor bids</Text>
				</View>
				<View style={{ width: 56 }} />
			</View>

			{/* ── Stats Banner ── */}
			<View style={styles.statsBanner}>
				<View style={styles.statItem}>
					<Text style={styles.statNum}>{boostedPosts.length}</Text>
					<Text style={styles.statLbl}>Active</Text>
				</View>
				<View style={styles.statDiv} />
				<View style={styles.statItem}>
					<Text style={styles.statNum}>{projects.length}</Text>
					<Text style={styles.statLbl}>Available</Text>
				</View>
				<View style={styles.statDiv} />
				<View style={styles.statItem}>
					<Text style={[styles.statNum, { color: COLORS.accent }]}>₱49</Text>
					<Text style={styles.statLbl}>Per Boost</Text>
				</View>
			</View>

			{/* ── Tab Bar ── */}
			<View style={styles.tabBar}>
				<TouchableOpacity
					style={[styles.tabBtn, selectedTab === 'active' && styles.tabBtnActive]}
					onPress={() => setSelectedTab('active')}
				>
					<Feather name="zap" size={14} color={selectedTab === 'active' ? COLORS.accent : COLORS.textMuted} style={{ marginRight: 6 }} />
					<Text style={[styles.tabText, selectedTab === 'active' && styles.tabTextActive]}>Available</Text>
				</TouchableOpacity>
				<TouchableOpacity
					style={[styles.tabBtn, selectedTab === 'dashboard' && styles.tabBtnActive]}
					onPress={() => setSelectedTab('dashboard')}
				>
					<Feather name="bar-chart-2" size={14} color={selectedTab === 'dashboard' ? COLORS.accent : COLORS.textMuted} style={{ marginRight: 6 }} />
					<Text style={[styles.tabText, selectedTab === 'dashboard' && styles.tabTextActive]}>Dashboard</Text>
				</TouchableOpacity>
			</View>

			{loading ? (
				<ActivityIndicator size="large" color={COLORS.accent} style={{ marginTop: 60 }} />
			) : selectedTab === 'active' ? (
				<>
					{/* Search */}
					<View style={styles.searchWrap}>
						<Feather name="search" size={15} color={COLORS.textMuted} style={{ marginRight: 8 }} />
						<TextInput
							style={styles.searchInput}
							placeholder="Search by title or location…"
							placeholderTextColor={COLORS.textMuted}
							value={searchQuery}
							onChangeText={setSearchQuery}
							returnKeyType="search"
						/>
						{searchQuery.length > 0 && (
							<TouchableOpacity onPress={() => setSearchQuery('')}>
								<Feather name="x" size={15} color={COLORS.textMuted} />
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
						keyExtractor={(item, index) => `boost-${item.id || item.project_id || index}`}
						renderItem={renderItem}
						contentContainerStyle={styles.listContent}
						refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => fetchModalData(true)} tintColor={COLORS.accent} />}
						ListEmptyComponent={
							<View style={styles.emptyWrap}>
								<View style={styles.emptyIcon}>
									<Feather name="zap" size={28} color={COLORS.textMuted} />
								</View>
								<Text style={styles.emptyTitle}>No projects to boost</Text>
								<Text style={styles.emptyDesc}>Create a project first, then boost it to attract more bids.</Text>
							</View>
						}
					/>
				</>
			) : (
				<ScrollView contentContainerStyle={{ padding: 16 }}>
					{/* Analytics */}
					<Text style={styles.sectionLabel}>PERFORMANCE · LAST 7 DAYS</Text>
					<View style={styles.analyticsRow}>
						<View style={styles.analyticsCard}>
							<View style={styles.analyticsIcon}>
								<Feather name="eye" size={18} color={COLORS.accent} />
							</View>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsLabel}>Reach</Text>
						</View>
						<View style={styles.analyticsCard}>
							<View style={styles.analyticsIcon}>
								<Feather name="message-square" size={18} color={COLORS.accent} />
							</View>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsLabel}>Bids</Text>
						</View>
						<View style={styles.analyticsCard}>
							<View style={styles.analyticsIcon}>
								<Feather name="mouse-pointer" size={18} color={COLORS.accent} />
							</View>
							<Text style={styles.analyticsValue}>0</Text>
							<Text style={styles.analyticsLabel}>Clicks</Text>
						</View>
					</View>

					{/* Boosted posts */}
					<View style={styles.sectionHeaderRow}>
						<Text style={styles.sectionLabel}>BOOSTED POSTS</Text>
						<View style={styles.sectionCountBadge}>
							<Text style={styles.sectionCountText}>{boostedPosts.length}</Text>
						</View>
					</View>

					{boostedPosts && boostedPosts.length > 0 ? (
						boostedPosts.map((b: any, idx: number) => (
							<View key={`boosted-${b.id || b.project_id || b._id || idx}`}>
								{renderBoostedItem(b)}
							</View>
						))
					) : (
						<View style={styles.emptyWrap}>
							<View style={styles.emptyIcon}>
								<Feather name="trending-up" size={28} color={COLORS.textMuted} />
							</View>
							<Text style={styles.emptyTitle}>No active boosts</Text>
							<Text style={styles.emptyDesc}>Switch to Available to boost your first project.</Text>
						</View>
					)}
				</ScrollView>
			)}
		</SafeAreaView>
	);
}

const styles = StyleSheet.create({
	// ── Layout ──
	container: {
		flex: 1,
		backgroundColor: COLORS.background,
	},

	// ── Header ──
	header: {
		flexDirection: 'row',
		alignItems: 'center',
		paddingHorizontal: 16,
		paddingTop: 48,
		paddingBottom: 14,
		backgroundColor: COLORS.surface,
		borderBottomWidth: 1,
		borderBottomColor: COLORS.border,
	},
	backBtn: {
		flexDirection: 'row',
		alignItems: 'center',
		width: 56,
	},
	backText: {
		fontSize: 14,
		color: COLORS.text,
		fontWeight: '600',
		marginLeft: 2,
	},
	headerCenter: {
		flex: 1,
		alignItems: 'center',
	},
	headerTitle: {
		fontSize: 16,
		fontWeight: '700',
		color: COLORS.text,
	},
	headerSub: {
		fontSize: 11,
		color: COLORS.textMuted,
		marginTop: 1,
	},

	// ── Stats Banner ──
	statsBanner: {
		flexDirection: 'row',
		alignItems: 'center',
		backgroundColor: COLORS.surface,
		borderBottomWidth: 1,
		borderBottomColor: COLORS.border,
		paddingVertical: 14,
		paddingHorizontal: 12,
	},
	statItem: {
		flex: 1,
		alignItems: 'center',
	},
	statNum: {
		fontSize: 20,
		fontWeight: '800',
		color: COLORS.text,
	},
	statLbl: {
		fontSize: 11,
		color: COLORS.textMuted,
		marginTop: 2,
		letterSpacing: 0.3,
	},
	statDiv: {
		width: 1,
		height: 28,
		backgroundColor: COLORS.border,
	},

	// ── Tab Bar ──
	tabBar: {
		flexDirection: 'row',
		backgroundColor: COLORS.surface,
		borderBottomWidth: 1,
		borderBottomColor: COLORS.border,
		paddingHorizontal: 16,
	},
	tabBtn: {
		flexDirection: 'row',
		alignItems: 'center',
		paddingVertical: 12,
		paddingHorizontal: 16,
		marginRight: 8,
		borderBottomWidth: 2,
		borderBottomColor: 'transparent',
	},
	tabBtnActive: {
		borderBottomColor: COLORS.accent,
	},
	tabText: {
		fontSize: 14,
		fontWeight: '600',
		color: COLORS.textMuted,
	},
	tabTextActive: {
		color: COLORS.accent,
	},

	// ── Search ──
	searchWrap: {
		flexDirection: 'row',
		alignItems: 'center',
		backgroundColor: COLORS.surface,
		marginHorizontal: 16,
		marginTop: 14,
		marginBottom: 6,
		paddingHorizontal: 14,
		paddingVertical: 10,
		borderRadius: 8,
		borderWidth: 1,
		borderColor: COLORS.border,
	},
	searchInput: {
		flex: 1,
		fontSize: 14,
		color: COLORS.text,
		paddingVertical: 0,
	},

	// ── List ──
	listContent: {
		padding: 16,
		paddingTop: 10,
	},

	// ── Project Card (fdInfoCard style) ──
	fdCard: {
		backgroundColor: COLORS.surface,
		borderRadius: 6,
		borderWidth: 1,
		borderColor: COLORS.border,
		marginBottom: 12,
		overflow: 'hidden',
	},
	cardImgWrap: {
		height: 160,
		backgroundColor: COLORS.borderLight,
	},
	cardImg: {
		width: '100%',
		height: '100%',
	},
	cardImgPlaceholder: {
		justifyContent: 'center',
		alignItems: 'center',
		gap: 6,
	},
	cardImgPlaceholderText: {
		fontSize: 12,
		color: COLORS.textMuted,
	},
	cardBody: {
		padding: 16,
	},
	cardLabel: {
		fontSize: 10,
		fontWeight: '700',
		color: COLORS.textMuted,
		letterSpacing: 1.2,
		textTransform: 'uppercase',
		marginBottom: 6,
	},
	cardTitle: {
		fontSize: 17,
		fontWeight: '700',
		color: COLORS.text,
		lineHeight: 23,
		marginBottom: 10,
	},
	cardMeta: {
		flexDirection: 'row',
		flexWrap: 'wrap',
		gap: 6,
		marginBottom: 4,
	},
	metaBadge: {
		flexDirection: 'row',
		alignItems: 'center',
		gap: 4,
		paddingHorizontal: 8,
		paddingVertical: 4,
		borderRadius: 4,
	},
	metaBadgeText: {
		fontSize: 11,
		fontWeight: '600',
		maxWidth: 140,
	},
	cardActions: {
		borderTopWidth: 1,
		borderTopColor: COLORS.borderLight,
		paddingTop: 12,
		marginTop: 10,
	},
	boostBtn: {
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'center',
		backgroundColor: COLORS.accent,
		paddingVertical: 11,
		borderRadius: 6,
	},
	boostBtnText: {
		color: '#FFF',
		fontWeight: '700',
		fontSize: 14,
	},

	// ── Progress Section (Dashboard) ──
	progressSection: {
		borderTopWidth: 1,
		borderTopColor: COLORS.borderLight,
		paddingTop: 12,
		marginTop: 10,
	},
	progressLabelRow: {
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'space-between',
		marginBottom: 8,
	},
	progressLabel: {
		fontSize: 10,
		fontWeight: '700',
		color: COLORS.textMuted,
		letterSpacing: 0.8,
		textTransform: 'uppercase',
	},
	progressPct: {
		fontSize: 13,
		fontWeight: '700',
		color: COLORS.text,
	},
	progressBg: {
		height: 6,
		borderRadius: 3,
		backgroundColor: COLORS.borderLight,
		overflow: 'hidden',
	},
	progressFill: {
		height: 6,
		borderRadius: 3,
	},
	expireRow: {
		flexDirection: 'row',
		alignItems: 'center',
		gap: 5,
		marginTop: 8,
	},
	expireText: {
		fontSize: 12,
		color: COLORS.textMuted,
	},

	// ── Dashboard ──
	sectionLabel: {
		fontSize: 10,
		fontWeight: '700',
		color: COLORS.textMuted,
		letterSpacing: 1.2,
		textTransform: 'uppercase',
		marginBottom: 10,
		marginTop: 4,
	},
	analyticsRow: {
		flexDirection: 'row',
		gap: 10,
		marginBottom: 20,
	},
	analyticsCard: {
		flex: 1,
		backgroundColor: COLORS.surface,
		borderRadius: 6,
		borderWidth: 1,
		borderColor: COLORS.border,
		padding: 14,
		alignItems: 'center',
	},
	analyticsIcon: {
		width: 40,
		height: 40,
		borderRadius: 20,
		backgroundColor: COLORS.accentLight,
		alignItems: 'center',
		justifyContent: 'center',
		marginBottom: 8,
	},
	analyticsValue: {
		fontSize: 22,
		fontWeight: '800',
		color: COLORS.text,
	},
	analyticsLabel: {
		fontSize: 11,
		color: COLORS.textMuted,
		marginTop: 3,
	},
	sectionHeaderRow: {
		flexDirection: 'row',
		alignItems: 'center',
		justifyContent: 'space-between',
		marginBottom: 10,
	},
	sectionCountBadge: {
		backgroundColor: COLORS.primaryLight,
		paddingHorizontal: 8,
		paddingVertical: 3,
		borderRadius: 10,
	},
	sectionCountText: {
		fontSize: 12,
		fontWeight: '700',
		color: COLORS.primary,
	},

	// ── Empty State ──
	emptyWrap: {
		marginTop: 48,
		alignItems: 'center',
		paddingHorizontal: 40,
	},
	emptyIcon: {
		width: 64,
		height: 64,
		borderRadius: 32,
		backgroundColor: COLORS.borderLight,
		alignItems: 'center',
		justifyContent: 'center',
		marginBottom: 16,
	},
	emptyTitle: {
		fontSize: 16,
		fontWeight: '700',
		color: COLORS.text,
		marginBottom: 6,
	},
	emptyDesc: {
		fontSize: 13,
		color: COLORS.textMuted,
		textAlign: 'center',
		lineHeight: 19,
	},
});
