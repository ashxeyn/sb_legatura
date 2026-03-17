// @ts-nocheck
import React, { useEffect, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView, TextInput, ActivityIndicator, Alert, Modal, FlatList, SafeAreaView, Image } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { role_service } from '../../services/role_service';
import { auth_service } from '../../services/auth_service';
import { api_config } from '../../config/api';
import * as ImagePicker from 'expo-image-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';

const LEGATURA_TOS = `LEGATURA TERMS OF SERVICE
Last Updated: March 2026

1. Acceptance of Terms and Platform Definition

By accessing or using the Legatura platform, you agree to be bound by these Terms of Service. Legatura operates strictly as an information and matching intermediary and a digital workflow manager. Legatura is an Information Technology (IT) software provider; it is not a construction firm, an engineering firm, a bank, or an e-wallet.

2. Limitation of Liability for Construction and Post-Project Defects

2.1. No Construction Guarantee: Legatura facilitates the connection between Property Owners and Contractors. We do not oversee physical construction, supply materials, or manage on-site labor.

2.2. Article 1723 Compliance: In strict accordance with Article 1723 of the Civil Code of the Philippines, the fifteen-year (15-year) legal liability for the collapse of a building or structure due to defects in construction, the use of inferior materials, or violations of the terms of the contract rests entirely and exclusively upon the licensed Engineer, Architect, or Contractor who executed the project.

2.3. Indemnification: The Property Owner agrees that Legatura, its administrators, and its developers shall bear zero legal or financial liability for any structural failure, property damage, personal injury, or subsequent loss resulting from the physical construction activities of the hired Contractor.

3. Verification and Account Accountability

3.1. Document Authenticity: Users are required to submit valid documentation, including but not limited to, Philippine Contractors Accreditation Board (PCAB) licenses, DTI/SEC registrations, Local Government Business Permits, and Valid Government IDs.

3.2. Platform Disclaimer: While Legatura's administration cross-references these documents against respective national and local government portals, the platform does not guarantee the real-world operational competence of the Contractor.

4. Financial Transactions and Tax Liabilities

4.1. Non-Financial Institution: Legatura acts as a milestone ledger. The platform does not hold, escrow, or transmit fiat currency. All financial transfers occur externally via the users' respective banking institutions.

4.2. Tax Obligations: Users are solely responsible for compliance with the National Internal Revenue Code (R.A. 8424). Legatura is not a Withholding Agent. The issuance of Official Receipts (OR) and the deduction of applicable withholding taxes and Value-Added Tax (VAT) remain the direct responsibility of the transacting Property Owner and Contractor.

5. Subscription Plans and Bidding Allocation

5.1. Plan-Based Bid Allocation: Contractors may subscribe to a subscription plan (e.g., Bronze, Silver, Gold) to receive a designated number of project bids. Each plan grants a fixed starting number of bids as defined at the time of subscription.

5.2. Non-Cumulative Bid Balance on Plan Change: When a Contractor upgrades or changes from one subscription plan to another (e.g., from Bronze to Silver), the bid count resets to the starting allocation of the newly subscribed plan. Remaining unused bids from the previous plan do not carry over or accumulate with the new plan's allocation.

5.3. Plan Cancellation and No-Refund Policy: A Contractor may cancel their active subscription plan at any time during the plan's duration. However, all subscription payments are final and non-refundable. Upon cancellation, the Contractor will retain access to the benefits of the current plan, including any remaining bids, until the plan's original expiration date. No pro-rated refunds or credits will be issued for the unused portion of the subscription period.

5.4. Plan Continuation Until Expiry: A cancelled plan remains active and fully functional until its scheduled end date. The Contractor may continue to use their remaining bids and plan features during this period. Once the plan expires, the account will revert to its default non-subscribed state unless a new plan is purchased.

6. Dispute Resolution and Arbitration

6.1. System Audit Trails: In the event of a dispute, Legatura will provide an immutable export of the user activity logs and milestone items to serve as electronic evidence under the Electronic Commerce Act of 2000 (R.A. 8792).

6.2. Escalation: If a project is placed on "Halt" and parties cannot reach a settlement through the platform, the deadlock must be escalated to the Construction Industry Arbitration Commission (CIAC) pursuant to Executive Order No. 1008. Legatura administrators will not act as technical judges or arbitrators for structural disputes.

7. Intellectual Property (Blueprints and Designs)

As per the Intellectual Property Code of the Philippines (R.A. 8293), all architectural designs, blueprints, and proprietary documents uploaded to the platform's project files module remain the exclusive intellectual property of the original creator.

8. Artificial Intelligence (AI) Decision Support Disclaimer

The Legatura platform utilizes an AI-driven delay prediction module. This feature is architected strictly as a Decision Support System (DSS) utilizing historical analytics and weather patterns. It generates a probability, not a deterministic guarantee. Legatura assumes no legal liability for project delays or financial losses incurred if a user relies solely on the system's delay probability metrics.

9. Data Retention and Privacy

In compliance with the Data Privacy Act of 2012 (R.A. 10173), user data is processed to establish contractual necessities. In the event of account deletion, Legatura reserves the right to retain specific contractual audit trails, system logs, and verified IDs for a legally mandated period to protect users against post-project liabilities and to establish legal claims.`;

interface RoleReapplyScreenProps {
  targetRole: 'contractor' | 'owner';
  existingData: any;
  onBack: () => void;
  onComplete: () => void;
  navigation?: any;
}

export default function RoleReapplyScreen(props: RoleReapplyScreenProps) {
  const { targetRole, existingData, onBack, onComplete, navigation } = props;

  const [loading, setLoading] = useState(true);
  const [formStep, setFormStep] = useState(1);
  const [formData, setFormData] = useState<any>({});
  const [submitting, setSubmitting] = useState(false);
  const [ownerName, setOwnerName] = useState<{ first_name: string; middle_name: string; last_name: string }>({ first_name: '', middle_name: '', last_name: '' });

  // Dropdown data states
  const [dropdowns, setDropdowns] = useState<any>({
    contractor_types: [],
    occupations: [],
    valid_ids: [],
    provinces: [],
    picab_categories: [],
  });

  // Location states
  const [provinces, setProvinces] = useState<any[]>([]);
  const [cities, setCities] = useState<any[]>([]);
  const [barangays, setBarangays] = useState<any[]>([]);
  const [ownerCities, setOwnerCities] = useState<any[]>([]);
  const [ownerBarangays, setOwnerBarangays] = useState<any[]>([]);

  // UI states
  const [prefilledFields, setPrefilledFields] = useState<Record<string, boolean>>({});
  const [agreedConfirm, setAgreedConfirm] = useState(false);
  const [showSubmitConfirm, setShowSubmitConfirm] = useState(false);
  const [showTermsModal, setShowTermsModal] = useState(false);
  const [hasReadTerms, setHasReadTerms] = useState(false);
  const [termsAgreed, setTermsAgreed] = useState(false);

  // Modal states
  const [showContractorTypeModal, setShowContractorTypeModal] = useState(false);
  const [showPicabCategoryModal, setShowPicabCategoryModal] = useState(false);
  const [showProvinceModal, setShowProvinceModal] = useState(false);
  const [showCityModal, setShowCityModal] = useState(false);
  const [showBarangayModal, setShowBarangayModal] = useState(false);
  const [showValidIdModal, setShowValidIdModal] = useState(false);
  const [showExperienceDateModal, setShowExperienceDateModal] = useState(false);
  const [showOwnerProvinceModal, setShowOwnerProvinceModal] = useState(false);
  const [showOwnerCityModal, setShowOwnerCityModal] = useState(false);
  const [showOwnerBarangayModal, setShowOwnerBarangayModal] = useState(false);
  const [showOccupationModal, setShowOccupationModal] = useState(false);
  const [showDobModal, setShowDobModal] = useState(false);
  const [showPicabDatePicker, setShowPicabDatePicker] = useState(false);
  const [showPermitDatePicker, setShowPermitDatePicker] = useState(false);
  const [showPermitCityModal, setShowPermitCityModal] = useState(false);

  // Date states
  const [picabDate, setPicabDate] = useState<Date>(new Date());
  const [permitDate, setPermitDate] = useState<Date>(new Date());
  const [permitCities, setPermitCities] = useState<any[]>([]);
  const [permitCityQuery, setPermitCityQuery] = useState('');

  // ============== HELPER FUNCTIONS ==============

  const formatDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const formatDateForDisplay = (dateString?: string): string => {
    if (!dateString) return '';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    } catch {
      return dateString || '';
    }
  };

  const computeYears = (start?: string): number => {
    if (!start) return 0;
    const sel = new Date(start);
    const now = new Date();
    let years = now.getFullYear() - sel.getFullYear();
    if (now.getMonth() < sel.getMonth() || (now.getMonth() === sel.getMonth() && now.getDate() < sel.getDate())) {
      years -= 1;
    }
    return Math.max(0, years);
  };

  const formatExperience = (start?: string): string => {
    if (!start) return '';
    const sel = new Date(start);
    const now = new Date();
    let years = now.getFullYear() - sel.getFullYear();
    let months = (now.getMonth() - sel.getMonth()) + (years * 12);
    if (now.getDate() < sel.getDate()) months -= 1;
    if (months < 0) months = 0;
    years = Math.floor(months / 12);
    const remMonths = months % 12;
    if (years >= 1) {
      return `${years} ${years === 1 ? 'year' : 'years'}${remMonths ? ` ${remMonths} ${remMonths === 1 ? 'month' : 'months'}` : ''}`;
    }
    return `${remMonths} ${remMonths === 1 ? 'month' : 'months'}`;
  };

  // ============== ADDRESS PARSING ==============

  const parseAddressWithCodes = (address: string): {
    street: string;
    barangayCode: string;
    cityCode: string;
    provinceCode: string;
    postal: string;
  } => {
    if (!address) {
      return { street: '', barangayCode: '', cityCode: '', provinceCode: '', postal: '' };
    }

    const parts = address.split(',').map(s => s.trim());

    // Expected format from backend: "Street, BarangayCode, CityCode, ProvinceCode Postal"
    // Example: "Anywhere, 148105004, 148105000, 148100000 7000"

    const street = parts[0] || '';
    const barangayCode = parts.length > 1 ? parts[1] : '';
    const cityCode = parts.length > 2 ? parts[2] : '';

    let provinceCode = '';
    let postal = '';

    if (parts.length > 3) {
      const lastPart = parts[3];
      const lastParts = lastPart.split(' ');
      provinceCode = lastParts[0] || '';
      postal = lastParts.length > 1 ? lastParts[1] : '';
    }

    return { street, barangayCode, cityCode, provinceCode, postal };
  };

  // ============== LOADING FUNCTIONS ==============

  const loadCities = async (provinceCode: string) => {
    console.log('[Reapply] loadCities called for province', provinceCode);
    try {
      const res = await auth_service.get_cities_by_province(provinceCode);
      if (res?.success && res.data) {
        const citiesWithStringCodes = res.data.map((city: any) => ({
          ...city,
          code: String(city.code)
        }));
        setCities(citiesWithStringCodes);
        setBarangays([]);
      }
    } catch (err) {
      console.warn('[Reapply] loadCities error', err);
    }
  };

  const loadBarangays = async (cityCode: string) => {
    console.log('[Reapply] loadBarangays called for city', cityCode);
    try {
      const res = await auth_service.get_barangays_by_city(cityCode);
      if (res?.success && res.data) {
        const barangaysWithStringCodes = res.data.map((brgy: any) => ({
          ...brgy,
          code: String(brgy.code)
        }));
        setBarangays(barangaysWithStringCodes);
      }
    } catch (err) {
      console.warn('[Reapply] loadBarangays error', err);
    }
  };

  const loadOwnerCities = async (provinceCode: string) => {
    console.log('[Reapply] loadOwnerCities called for province', provinceCode);
    try {
      const res = await auth_service.get_cities_by_province(provinceCode);
      if (res?.success && res.data) {
        const citiesWithStringCodes = res.data.map((city: any) => ({
          ...city,
          code: String(city.code)
        }));
        setOwnerCities(citiesWithStringCodes);
        setOwnerBarangays([]);
      }
    } catch (err) {
      console.warn('[Reapply] loadOwnerCities error', err);
    }
  };

  const loadOwnerBarangays = async (cityCode: string) => {
    console.log('[Reapply] loadOwnerBarangays called for city', cityCode);
    try {
      const res = await auth_service.get_barangays_by_city(cityCode);
      if (res?.success && res.data) {
        const barangaysWithStringCodes = res.data.map((brgy: any) => ({
          ...brgy,
          code: String(brgy.code)
        }));
        setOwnerBarangays(barangaysWithStringCodes);
      }
    } catch (err) {
      console.warn('[Reapply] loadOwnerBarangays error', err);
    }
  };

  // ============== UPDATE FUNCTIONS ==============

  const updateForm = (patch: Record<string, any>) => {
    setFormData((prev: any) => ({ ...prev, ...patch }));
    setPrefilledFields((prev) => {
      const next = { ...prev };
      Object.keys(patch).forEach((k) => { next[k] = false; });
      return next;
    });
  };

  const updatePrefilled = async (newData: any) => {
    console.log('[Reapply] updatePrefilled called with keys:', Object.keys(newData));

    setFormData(prev => ({ ...prev, ...newData }));
    setPrefilledFields(prev => {
      const next: Record<string, boolean> = { ...prev };
      Object.keys(newData).forEach(key => {
        if (newData[key]) next[key] = true;
      });
      return next;
    });

    // Load dependent data for address fields
    try {
      if (newData.business_address_province) {
        await loadCities(String(newData.business_address_province));
        if (newData.business_address_city) {
          await loadBarangays(String(newData.business_address_city));
        }
      }
      if (newData.owner_address_province) {
        await loadOwnerCities(String(newData.owner_address_province));
        if (newData.owner_address_city) {
          await loadOwnerBarangays(String(newData.owner_address_city));
        }
      }
    } catch (err) {
      console.warn('[Reapply] address load error', err);
    }
  };

  // ============== PRE-FILL PROCESSING ==============

  const processExistingData = async () => {
    console.log('[Reapply] Processing existing data:', existingData);

    if (!existingData) return;

    const prefill: any = {};

    // Extract data based on structure
    const contractorData = existingData.contractor || (existingData.user_type === 'contractor' ? existingData : null);
    const ownerData = existingData.property_owner || existingData.owner || (existingData.user_type === 'property_owner' ? existingData : null);
    const contractorUserData = existingData.contractor_user || {};

    // Populate owner name for read-only display (from users table via join)
    if (targetRole === 'contractor') {
      const u = existingData.user || contractorUserData;
      setOwnerName({
        first_name: u.first_name || u.authorized_rep_fname || '',
        middle_name: u.middle_name || u.authorized_rep_mname || '',
        last_name: u.last_name || u.authorized_rep_lname || '',
      });
    }

    console.log('[Reapply] Extracted data:', {
      hasContractor: !!contractorData,
      hasOwner: !!ownerData,
      hasContractorUser: !!contractorUserData
    });

    // === CONTRACTOR FIELDS ===
    if (targetRole === 'contractor' && contractorData) {
      // Company Information
      prefill.company_name = contractorData.company_name || '';
      prefill.experience_start_date = contractorData.company_start_date || '';
      prefill.years_of_experience = contractorData.years_of_experience?.toString() || '';

      // Contractor Type
      prefill.contractor_type_id = contractorData.type_id?.toString() ||
                                 contractorData.contractor_type_id?.toString() || '';
      prefill.contractor_type_other_text = contractorData.contractor_type_other || '';

      // Services
      prefill.services_offered = contractorData.services_offered || '';

      // Business Address - Parse the concatenated string
      const businessAddr = contractorData.business_address || '';
      console.log('[Reapply] Business address:', businessAddr);

      if (businessAddr) {
        const { street, barangayCode, cityCode, provinceCode, postal } = parseAddressWithCodes(businessAddr);

        prefill.business_address_street = street;
        prefill.business_address_postal = postal;

        if (barangayCode) prefill.business_address_barangay = barangayCode;
        if (cityCode) prefill.business_address_city = cityCode;
        if (provinceCode) prefill.business_address_province = provinceCode;
      }

      // Documents
      prefill.picab_number = contractorData.picab_number || '';
      prefill.picab_category = contractorData.picab_category || '';
      prefill.picab_expiration_date = contractorData.picab_expiration_date || '';
      prefill.business_permit_number = contractorData.business_permit_number || '';
      prefill.business_permit_city = contractorData.business_permit_city || '';
      prefill.business_permit_expiration = contractorData.business_permit_expiration || '';
      prefill.tin_business_reg_number = contractorData.tin_business_reg_number || '';
      prefill.dti_sec_registration_photo_server = contractorData.dti_sec_registration_photo || '';

      // Optional info
      prefill.company_website = contractorData.company_website || '';
      prefill.company_social_media = contractorData.company_social_media || '';
      prefill.company_description = contractorData.company_description || '';
      prefill.company_logo_server = contractorData.company_logo || '';
      prefill.company_banner_server = contractorData.company_banner || '';
    }

    // === OWNER FIELDS ===
    if (targetRole === 'owner' && ownerData) {
      // Personal Information
      prefill.first_name = ownerData.first_name || '';
      prefill.middle_name = ownerData.middle_name || '';
      prefill.last_name = ownerData.last_name || '';
      prefill.date_of_birth = ownerData.date_of_birth || '';

      // Occupation
      prefill.occupation_id = ownerData.occupation_id?.toString() || '';
      prefill.occupation_other_text = ownerData.occupation_other || '';

      // Owner Address - Parse the concatenated string
      const ownerAddr = ownerData.address || '';
      console.log('[Reapply] Owner address:', ownerAddr);

      if (ownerAddr) {
        const { street, barangayCode, cityCode, provinceCode, postal } = parseAddressWithCodes(ownerAddr);

        prefill.owner_address_street = street;
        prefill.owner_address_postal = postal;

        if (barangayCode) prefill.owner_address_barangay = barangayCode;
        if (cityCode) prefill.owner_address_city = cityCode;
        if (provinceCode) prefill.owner_address_province = provinceCode;
      }

      // Documents
      prefill.valid_id_id = ownerData.valid_id_id?.toString() || '';
      prefill.owner_valid_id_photo_server = ownerData.valid_id_photo || '';
      prefill.owner_valid_id_back_photo_server = ownerData.valid_id_back_photo || '';
      prefill.owner_police_clearance_server = ownerData.police_clearance || '';
    }

    // If no data was extracted, try using incoming directly as fallback
    if (Object.keys(prefill).length === 0) {
      console.log('[Reapply] No structured data found, using incoming directly');
      if (targetRole === 'contractor') {
        prefill.company_name = existingData.company_name || '';
      } else if (targetRole === 'owner') {
        prefill.first_name = existingData.first_name || '';
        prefill.middle_name = existingData.middle_name || '';
        prefill.last_name = existingData.last_name || '';
      }
    }

    console.log('[Reapply] Final prefill object:', prefill);

    // Apply all prefill data at once
    await updatePrefilled(prefill);
  };

  // ============== INITIALIZATION ==============

  useEffect(() => {
    (async () => {
      try {
        console.log('[Reapply] Initializing with targetRole:', targetRole);

        // Load dropdown data
        const res = await role_service.get_switch_form_data();
        if (res?.success) {
          const root = res?.data || {};
          const d = root?.form_data || root || {};

          setDropdowns({
            contractor_types: d.contractor_types || [],
            occupations: d.occupations || [],
            valid_ids: d.valid_ids || [],
            provinces: d.provinces || [],
            picab_categories: d.picab_categories || [],
          });
        }

        // Load PSGC provinces
        const provRes = await auth_service.get_provinces();
        if (provRes.success && provRes.data) {
          setProvinces(provRes.data);
        }

        // Load permit cities if needed
        if (targetRole === 'contractor') {
          const citiesRes = await auth_service.get_all_cities();
          if (citiesRes.success && Array.isArray(citiesRes.data)) {
            setPermitCities(citiesRes.data);
          }
        }

        // Process existing data
        await processExistingData();

      } catch (error) {
        console.error('[Reapply] Initialization error:', error);
        Alert.alert('Error', 'Failed to load re-application data. Please try again.');
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  // ============== IMAGE PICKER ==============

  const pickImage = async (field: string, aspect?: [number, number]) => {
    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert('Permission required', 'Please allow photo library access.');
        return;
      }
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: 'Images',
        quality: 0.8,
        allowsEditing: true,
        aspect: aspect ?? [4, 3]
      });

      if (!result.canceled && result.assets?.length) {
        const asset = result.assets[0];
        const uri = asset.uri;
        const fileName = (asset.fileName || asset.filename || uri?.split('/')?.pop()) ?? 'Image selected';
        updateForm({ [field]: uri, [`${field}_name`]: fileName });
      }
    } catch (err) {
      console.log('ImagePicker error:', err);
      Alert.alert('Image Picker Error', 'Failed to open gallery. Please try again or check permissions.');
    }
  };

  const getStorageUrl = (path?: string) => (path ? `${api_config.base_url}/storage/${path}` : '');

  const getDocImageUrl = (val?: any) => {
    if (!val) return '';
    const src = typeof val === 'string' ? val : val?.uri;
    if (!src) return '';
    if (src.startsWith('http') || src.startsWith('file:') || src.startsWith('content:') || src.startsWith('data:')) return src;
    return getStorageUrl(src);
  };

  // ============== DATE PICKER ==============

  const getYears = (): number[] => {
    const currentYear = new Date().getFullYear();
    const years: number[] = [];
    for (let y = currentYear; y <= currentYear + 20; y++) years.push(y);
    return years;
  };

  const getMonths = (): Array<{ value: number; label: string }> => ([
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
  ]);

  const getDays = (year: number, month: number): number[] => {
    const dim = new Date(year, month, 0).getDate();
    return Array.from({ length: dim }, (_, i) => i + 1);
  };

  function CustomDatePicker({ currentDate, onDateChange, minimumDate }: { currentDate: Date; onDateChange: (y: number, m: number, d: number) => void; minimumDate?: Date }) {
    const [selectedYear, setSelectedYear] = useState(currentDate.getFullYear());
    const [selectedMonth, setSelectedMonth] = useState(currentDate.getMonth() + 1);
    const [selectedDay, setSelectedDay] = useState(currentDate.getDate());

    const years = getYears();
    const months = getMonths();
    const min = minimumDate || new Date();
    const minYear = min.getFullYear();
    const minMonth = min.getMonth() + 1;
    const minDay = min.getDate();

    useEffect(() => {
      if (
        selectedYear < minYear ||
        (selectedYear === minYear && selectedMonth < minMonth) ||
        (selectedYear === minYear && selectedMonth === minMonth && selectedDay < minDay)
      ) {
        setSelectedYear(minYear);
        setSelectedMonth(minMonth);
        setSelectedDay(minDay);
      }
    }, []);

    useEffect(() => {
      const maxDays = getDays(selectedYear, selectedMonth).length;
      if (selectedDay > maxDays) setSelectedDay(maxDays);
    }, [selectedYear, selectedMonth]);

    const confirm = () => onDateChange(selectedYear, selectedMonth, selectedDay);

    return (
      <View style={styles.customDatePickerContainer}>
        <View style={styles.datePickerRow}>
          <View style={styles.datePickerColumn}>
            <Text style={styles.datePickerLabel}>Year</Text>
            <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
              {years.map((y) => (
                <TouchableOpacity
                  key={y}
                  style={[styles.datePickerItem, selectedYear === y && styles.datePickerItemSelected]}
                  onPress={() => setSelectedYear(y)}
                >
                  <Text style={[styles.datePickerItemText, selectedYear === y && styles.datePickerItemTextSelected]}>{y}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
          <View style={styles.datePickerColumn}>
            <Text style={styles.datePickerLabel}>Month</Text>
            <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
              {months.map((m) => (
                <TouchableOpacity
                  key={m.value}
                  style={[styles.datePickerItem, selectedMonth === m.value && styles.datePickerItemSelected]}
                  onPress={() => setSelectedMonth(m.value)}
                >
                  <Text style={[styles.datePickerItemText, selectedMonth === m.value && styles.datePickerItemTextSelected]}>{m.label}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
          <View style={styles.datePickerColumn}>
            <Text style={styles.datePickerLabel}>Day</Text>
            <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
              {getDays(selectedYear, selectedMonth).map((d) => {
                const isDisabled = selectedYear === minYear && selectedMonth === minMonth && d < minDay;
                return (
                  <TouchableOpacity
                    key={d}
                    style={[styles.datePickerItem, selectedDay === d && styles.datePickerItemSelected, isDisabled && styles.datePickerItemDisabled]}
                    onPress={() => !isDisabled && setSelectedDay(d)}
                    disabled={isDisabled}
                  >
                    <Text style={[styles.datePickerItemText, selectedDay === d && styles.datePickerItemTextSelected, isDisabled && styles.datePickerItemTextDisabled]}>{d}</Text>
                  </TouchableOpacity>
                );
              })}
            </ScrollView>
          </View>
        </View>
        <TouchableOpacity style={styles.datePickerConfirmButton} onPress={confirm}>
          <Text style={styles.datePickerConfirmText}>Confirm</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // ============== SUBMIT HANDLING ==============

  const submitStep = async () => {
    try {
      setSubmitting(true);

      if (targetRole === 'contractor') {
        if (formStep === 1) {
          // Validate contractor step 1
          const errors: string[] = [];

          if (!formData.company_name?.trim()) errors.push('Company name is required');
          if (!formData.experience_start_date) errors.push('Years of experience (start date) is required');
          if (!formData.contractor_type_id) errors.push('Contractor type is required');

          const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`);
          const isOther = (sel?.name || '').toLowerCase().includes('other');
          if (isOther && !formData.contractor_type_other_text?.trim()) errors.push('Please specify other contractor type');

          if (!formData.services_offered?.trim()) errors.push('Services offered is required');
          if (!formData.business_address_street?.trim()) errors.push('Business address street is required');
          if (!formData.business_address_barangay) errors.push('Business address barangay is required');
          if (!formData.business_address_city) errors.push('Business address city is required');
          if (!formData.business_address_province) errors.push('Business address province is required');
          if (!formData.business_address_postal?.trim()) errors.push('Business address postal code is required');

          if (errors.length) {
            Alert.alert('Please fix the following', errors.join('\n'));
            setSubmitting(false);
            return;
          }

          // Build business address for backend (concatenated format)
          const provinceName = provinces.find(p => String(p.code) === String(formData.business_address_province))?.name || '';
          const cityName = cities.find(c => String(c.code) === String(formData.business_address_city))?.name || '';
          const barangayName = barangays.find(b => String(b.code) === String(formData.business_address_barangay))?.name || '';

          const { buildContractorStep1Payload, validateContractorStep1 } = await import('../../utils/roleFormBuilders');
          const errors = validateContractorStep1(formData, dropdowns);
          if (errors.length) {
            Alert.alert('Please fix the following', errors.join('\n'));
            setSubmitting(false);
            return;
          }
          const payload = buildContractorStep1Payload(formData);
          const res = await role_service.add_contractor_step1(payload);
          if (res?.success) {
            setFormStep(2);
          } else {
            Alert.alert('Error', res?.message || 'Please check required fields or login again.');
          }
        } else if (formStep === 2) {
          // Handle contractor step 2 (documents)
          const { buildContractorStep2FormData } = await import('../../utils/roleFormBuilders');
          const fd = buildContractorStep2FormData(formData);
          const res = await role_service.add_contractor_step2(fd);
          if (res?.success) {
            const saved = res?.data?.saved;
            if (saved?.dti_sec_registration_photo) updateForm({ dti_sec_registration_photo_server: saved.dti_sec_registration_photo });
            if (saved?.company_logo) updateForm({ company_logo_server: saved.company_logo });
            if (saved?.company_banner) updateForm({ company_banner_server: saved.company_banner });
            setFormStep(3);
          } else {
            Alert.alert('Error', res?.message || 'Upload failed');
          }
        } else if (formStep === 3) {
          // Final step
          const provinceName = provinces.find(p => String(p.code) === String(formData.business_address_province))?.name || '';
          const cityName = cities.find(c => String(c.code) === String(formData.business_address_city))?.name || '';
          const barangayName = barangays.find(b => String(b.code) === String(formData.business_address_barangay))?.name || '';

          const business_address = [
            formData.business_address_street || '',
            barangayName || '',
            cityName || '',
            provinceName || ''
          ].filter(Boolean).join(', ') + (formData.business_address_postal ? ` ${formData.business_address_postal}` : '');

          const { buildContractorFinalBody } = await import('../../utils/roleFormBuilders');
          const body = buildContractorFinalBody(formData, provinces, cities, barangays);
          const res = await role_service.add_contractor_final(body);

          if (res?.success) {
            Alert.alert('Application Submitted', 'Your re-application has been received and is pending administrative review and approval.', [
              { text: 'OK', onPress: onComplete }
            ]);
          } else {
            Alert.alert('Error', res?.message || 'Finalization failed');
          }
        }
      } else if (targetRole === 'owner') {
        if (formStep === 1) {
          // Validate owner step 1
          const errors: string[] = [];

          if (!formData.first_name?.trim()) errors.push('First name is required');
          if (!formData.last_name?.trim()) errors.push('Last name is required');
          if (!formData.date_of_birth) errors.push('Date of birth is required');
          if (!formData.occupation_id) errors.push('Occupation is required');
          if (!formData.owner_address_street?.trim()) errors.push('Address street is required');
          if (!formData.owner_address_barangay) errors.push('Barangay is required');
          if (!formData.owner_address_city) errors.push('City is required');
          if (!formData.owner_address_province) errors.push('Province is required');
          if (!formData.owner_address_postal?.trim()) errors.push('Postal code is required');

          const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`);
          const isOther = (occ?.name || '').toLowerCase().includes('other');
          if (isOther && !formData.occupation_other_text?.trim()) errors.push('Please specify other occupation');

          if (errors.length) {
            Alert.alert('Please fix the following', errors.join('\n'));
            setSubmitting(false);
            return;
          }

          // Build owner address
          const provinceName = provinces.find(p => String(p.code) === String(formData.owner_address_province))?.name || '';
          const cityName = ownerCities.find(c => String(c.code) === String(formData.owner_address_city))?.name || '';
          const barangayName = ownerBarangays.find(b => String(b.code) === String(formData.owner_address_barangay))?.name || '';

          const address = [
            formData.owner_address_street || '',
            barangayName || '',
            cityName || '',
            provinceName || ''
          ].filter(Boolean).join(', ') + (formData.owner_address_postal ? ` ${formData.owner_address_postal}` : '');

          const payload = {
            first_name: formData.first_name,
            middle_name: formData.middle_name,
            last_name: formData.last_name,
            occupation_id: formData.occupation_id,
            occupation_other_text: formData.occupation_other_text,
            date_of_birth: formData.date_of_birth,
            owner_address_street: formData.owner_address_street,
            owner_address_barangay: formData.owner_address_barangay,
            owner_address_city: formData.owner_address_city,
            owner_address_province: formData.owner_address_province,
            owner_address_postal: formData.owner_address_postal,
            address: address,
          };

          const res = await role_service.add_owner_step1(payload);
          if (res?.success) {
            setFormStep(2);
          } else {
            Alert.alert('Error', res?.message || 'Please check required fields or login again.');
            setSubmitting(false);
            return;
          }
        } else if (formStep === 2) {
          // Owner step 2 (documents)
          setFormStep(3);
        } else if (formStep === 3) {
          // Final step
          const provinceName = provinces.find(p => String(p.code) === String(formData.owner_address_province))?.name || '';
          const cityName = ownerCities.find(c => String(c.code) === String(formData.owner_address_city))?.name || '';
          const barangayName = ownerBarangays.find(b => String(b.code) === String(formData.owner_address_barangay))?.name || '';

          const address = [
            formData.owner_address_street || '',
            barangayName || '',
            cityName || '',
            provinceName || ''
          ].filter(Boolean).join(', ') + (formData.owner_address_postal ? ` ${formData.owner_address_postal}` : '');

          const savedDocs: any = {};
          if (formData.owner_valid_id_id) savedDocs.valid_id_id = formData.owner_valid_id_id;
          if (formData.owner_valid_id_photo_server) savedDocs.valid_id_photo = formData.owner_valid_id_photo_server;
          if (formData.owner_valid_id_back_photo_server) savedDocs.valid_id_back_photo = formData.owner_valid_id_back_photo_server;
          if (formData.owner_police_clearance_server) savedDocs.police_clearance = formData.owner_police_clearance_server;

          const body: any = {
            owner_step1_data: {
              first_name: formData.first_name,
              middle_name: formData.middle_name,
              last_name: formData.last_name,
              occupation_id: formData.occupation_id,
              occupation_other: formData.occupation_other_text,
              date_of_birth: formData.date_of_birth,
              address: address,
              age: computeYears(formData.date_of_birth),
            },
            switch_step2_data: Object.keys(savedDocs).length ? { saved: savedDocs } : undefined,
          };

          const res = await role_service.add_owner_final(body);
          if (res?.success) {
            Alert.alert('Application Submitted', 'Your re-application has been received and is pending administrative review and approval.', [
              { text: 'OK', onPress: onComplete }
            ]);
          } else {
            Alert.alert('Error', res?.message || 'Finalization failed');
          }
        }
      }
    } finally {
      setSubmitting(false);
    }
  };

  const handlePrimaryPress = () => {
    if (formStep < 3) return submitStep();
    if (!termsAgreed) { Alert.alert('Terms of Service', 'Please read and acknowledge the Terms of Service before submitting.'); return; }
    if (!agreedConfirm) {
      Alert.alert('Confirm', 'Please confirm the information is correct.');
      return;
    }
    setShowSubmitConfirm(true);
  };

  // ============== RENDER ==============

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <ScrollView contentContainerStyle={styles.scrollContent}>
          <View style={styles.logoContainer}>
            <Image
              source={require('../../../assets/images/logos/legatura-logo.png')}
              style={styles.logo}
              resizeMode="contain"
            />
          </View>
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#EC7E00" />
            <Text style={styles.loadingText}>Loading re-application form...</Text>
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAwareScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.logoContainer}>
          <Image
            source={require('../../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        {/* Progress Steps */}
        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, formStep >= 1 && styles.progressBarActive]} />
            <Text style={[styles.progressText, formStep >= 1 && styles.progressTextActive]}>Step 1</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, formStep >= 2 && styles.progressBarActive]} />
            <Text style={[styles.progressText, formStep >= 2 && styles.progressTextActive]}>Step 2</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, formStep >= 3 && styles.progressBarActive]} />
            <Text style={[styles.progressText, formStep >= 3 && styles.progressTextActive]}>Finalize</Text>
          </View>
        </View>

        <View style={styles.formContainer}>
          <Text style={styles.sectionTitle}>
            Re-apply for {targetRole === 'contractor' ? 'Contractor' : 'Property Owner'} Role
          </Text>

          {/* Info Notice */}
          <View style={styles.infoNotice}>
            <Ionicons name="sparkles" size={18} color="#EC7E00" />
            <Text style={styles.infoNoticeText}>
              Details restored from your previous application.
              <Text style={{ fontWeight: '700' }}> Review all fields and make necessary changes.</Text>
            </Text>
          </View>

          {/* CONTRACTOR STEP 1 */}
          {targetRole === 'contractor' && formStep === 1 && (
            <View>
              <Text style={styles.sectionTitle}>Company Information</Text>

              <Text style={styles.inputLabel}>Company Logo (Optional)</Text>
              <View style={{ alignSelf: 'flex-start' }}>
                <TouchableOpacity style={[styles.uploadButton, { width: 100, height: 100 }]} onPress={() => pickImage('company_logo', [1, 1])}>
                  {(formData.company_logo || formData.company_logo_server) ? (
                    <Image source={{ uri: getDocImageUrl(formData.company_logo || formData.company_logo_server) }} style={{ width: '100%', height: '100%', borderRadius: 6 }} resizeMode="cover" />
                  ) : (
                    <View style={styles.uploadPlaceholder}>
                      <Ionicons name="cloud-upload" size={28} color="#EC7E00" />
                      <Text style={[styles.uploadHint, { textAlign: 'center' }]}>Logo{`\n`}1:1</Text>
                    </View>
                  )}
                </TouchableOpacity>
                {(formData.company_logo || formData.company_logo_server) ? (
                  <TouchableOpacity
                    onPress={() => updateForm({ company_logo: null, company_logo_server: null, company_logo_name: null })}
                    style={{ position: 'absolute', top: -8, right: -8 }}
                  >
                    <Ionicons name="close-circle" size={22} color="#E74C3C" />
                  </TouchableOpacity>
                ) : null}
              </View>

              <Text style={styles.inputLabel}>Company Banner (Optional)</Text>
              <View>
                <TouchableOpacity style={[styles.uploadButton, { aspectRatio: 16 / 9, height: undefined }]} onPress={() => pickImage('company_banner', [16, 9])}>
                  {(formData.company_banner || formData.company_banner_server) ? (
                    <Image source={{ uri: getDocImageUrl(formData.company_banner || formData.company_banner_server) }} style={{ width: '100%', height: '100%', borderRadius: 6 }} resizeMode="cover" />
                  ) : (
                    <View style={styles.uploadPlaceholder}>
                      <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                      <Text style={styles.uploadText}>Tap to upload company banner</Text>
                      <Text style={styles.uploadHint}>JPG, JPEG, PNG — 16:9</Text>
                    </View>
                  )}
                </TouchableOpacity>
                {(formData.company_banner || formData.company_banner_server) ? (
                  <TouchableOpacity
                    onPress={() => updateForm({ company_banner: null, company_banner_server: null, company_banner_name: null })}
                    style={{ position: 'absolute', top: 6, right: 6 }}
                  >
                    <Ionicons name="close-circle" size={26} color="#E74C3C" />
                  </TouchableOpacity>
                ) : null}
              </View>

              <Text style={styles.inputLabel}>Company Name *</Text>
              <TextInput
                style={[styles.input, prefilledFields.company_name && styles.prefilledInput]}
                value={formData.company_name || ''}
                onChangeText={(t) => updateForm({ company_name: t })}
                placeholder="Company Name *"
                placeholderTextColor="#999"
              />

              <Text style={styles.inputLabel}>Years of Experience *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowExperienceDateModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, !formData.experience_start_date && styles.placeholderText, prefilledFields.experience_start_date && styles.prefilledDropdownText]}>
                    {formData.experience_start_date
                      ? `${formatExperience(formData.experience_start_date)} (started ${formData.experience_start_date})`
                      : 'Select company start date *'}
                  </Text>
                  <Ionicons name="calendar" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>Contractor Type *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowContractorTypeModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, !formData.contractor_type_id && styles.placeholderText, prefilledFields.contractor_type_id && styles.prefilledDropdownText]}>
                    {(() => {
                      const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`);
                      return sel?.name || 'Select Contractor Type *';
                    })()}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              {(() => {
                const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`);
                const selName = (sel?.name || '').toLowerCase();
                const isOther = selName.includes('other');
                if (!isOther) return null;
                return (
                  <>
                    <Text style={styles.inputLabel}>Specify Other Contractor Type</Text>
                    <TextInput
                      style={[styles.input, prefilledFields.contractor_type_other_text && styles.prefilledInput]}
                      value={formData.contractor_type_other_text || ''}
                      onChangeText={(t) => updateForm({ contractor_type_other_text: t })}
                      placeholder="e.g., Solar Installer"
                      placeholderTextColor="#999"
                    />
                  </>
                );
              })()}

              <Text style={styles.inputLabel}>Services Offered *</Text>
              <TextInput
                style={[styles.input, prefilledFields.services_offered && styles.prefilledInput]}
                value={formData.services_offered || ''}
                onChangeText={(t) => updateForm({ services_offered: t })}
                placeholder="Services Offered"
                placeholderTextColor="#999"
              />

              <Text style={styles.sectionTitle}>Business Address</Text>

              <Text style={styles.inputLabel}>Business Address Street *</Text>
              <TextInput
                style={[styles.input, prefilledFields.business_address_street && styles.prefilledInput]}
                value={formData.business_address_street || ''}
                onChangeText={(t) => updateForm({ business_address_street: t })}
                placeholder="Street"
                placeholderTextColor="#999"
              />

              <Text style={styles.inputLabel}>Province *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowProvinceModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.business_address_province && styles.prefilledDropdownText,
                    !formData.business_address_province && styles.placeholderText
                  ]}>
                    {provinces.find(p => String(p.code) === String(formData.business_address_province))?.name || 'Select Province'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>City/Municipality *</Text>
              <TouchableOpacity
                style={[styles.input, !formData.business_address_province && styles.inputDisabled]}
                onPress={() => formData.business_address_province && setShowCityModal(true)}
                disabled={!formData.business_address_province}
              >
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.business_address_city && styles.prefilledDropdownText,
                    !formData.business_address_city && styles.placeholderText
                  ]}>
                    {cities.length > 0
                      ? (cities.find(c => String(c.code) === String(formData.business_address_city))?.name || 'Select City/Municipality')
                      : (formData.business_address_province ? 'Loading City...' : 'Select Province First')}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>Barangay *</Text>
              <TouchableOpacity
                style={[styles.input, !formData.business_address_city && styles.inputDisabled]}
                onPress={() => formData.business_address_city && setShowBarangayModal(true)}
                disabled={!formData.business_address_city}
              >
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.business_address_barangay && styles.prefilledDropdownText,
                    !formData.business_address_barangay && styles.placeholderText
                  ]}>
                    {barangays.length > 0
                      ? (barangays.find(b => String(b.code) === String(formData.business_address_barangay))?.name || 'Select Barangay')
                      : (formData.business_address_city ? 'Loading Barangay...' : 'Select City First')}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>Postal Code *</Text>
              <TextInput
                style={[styles.input, prefilledFields.business_address_postal && styles.prefilledInput]}
                value={formData.business_address_postal || ''}
                onChangeText={(t) => updateForm({ business_address_postal: t })}
                keyboardType="number-pad"
                placeholder="Postal Code"
                placeholderTextColor="#999"
              />

              <Text style={styles.sectionTitle}>Owner Information</Text>
              <Text style={styles.inputLabel}>First Name</Text>
              <TextInput style={[styles.input, styles.readOnlyInput]} value={ownerName.first_name || 'Not set'} editable={false} />
              <Text style={styles.inputLabel}>Middle Name</Text>
              <TextInput style={[styles.input, styles.readOnlyInput]} value={ownerName.middle_name || '\u2014'} editable={false} />
              <Text style={styles.inputLabel}>Last Name</Text>
              <TextInput style={[styles.input, styles.readOnlyInput]} value={ownerName.last_name || 'Not set'} editable={false} />
              <Text style={[styles.inputLabel, { fontSize: 11, color: '#999', marginTop: -4 }]}>This information is linked from your account and cannot be edited here.</Text>

              <Text style={styles.sectionTitle}>Optional Information</Text>
              <Text style={styles.inputLabel}>Website</Text>
              <TextInput
                style={[styles.input, prefilledFields.company_website && styles.prefilledInput]}
                value={formData.company_website || ''}
                onChangeText={(t) => updateForm({ company_website: t })}
                placeholder="Website (Optional)"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Social Media</Text>
              <TextInput
                style={[styles.input, prefilledFields.company_social_media && styles.prefilledInput]}
                value={formData.company_social_media || ''}
                onChangeText={(t) => updateForm({ company_social_media: t })}
                placeholder="Social Media (Optional)"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Company Description</Text>
              <TextInput
                style={[styles.input, { height: 100, textAlignVertical: 'top' }]}
                value={formData.company_description || ''}
                onChangeText={(t) => updateForm({ company_description: t })}
                placeholder="Brief description of your company (Optional)"
                placeholderTextColor="#999"
                multiline
              />
            </View>
          )}

          {/* CONTRACTOR STEP 2 */}
          {targetRole === 'contractor' && formStep === 2 && (
            <View>
              <Text style={styles.inputLabel}>PICAB Number *</Text>
              <TextInput
                style={[styles.input, prefilledFields.picab_number && styles.prefilledInput]}
                value={formData.picab_number || ''}
                onChangeText={(t) => updateForm({ picab_number: t })}
                placeholder="PICAB Number"
                placeholderTextColor="#999"
              />

              <Text style={styles.inputLabel}>PICAB Category *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowPicabCategoryModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.picab_category && styles.prefilledDropdownText, !formData.picab_category && styles.placeholderText]}>
                    {formData.picab_category || 'Select PICAB Category *'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>PICAB Expiration Date *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowPicabDatePicker(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.picab_expiration_date && styles.prefilledDropdownText, !formData.picab_expiration_date && styles.placeholderText]}>
                    {formData.picab_expiration_date ? formatDateForDisplay(formData.picab_expiration_date) : 'PICAB Expiration Date *'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>Business Permit Number *</Text>
              <TextInput
                style={[styles.input, prefilledFields.business_permit_number && styles.prefilledInput]}
                value={formData.business_permit_number || ''}
                onChangeText={(t) => updateForm({ business_permit_number: t })}
                placeholder="Business Permit Number"
                placeholderTextColor="#999"
              />

              <Text style={styles.inputLabel}>Business Permit City *</Text>
              <TouchableOpacity style={styles.inputSelector} onPress={() => setShowPermitCityModal(true)}>
                <Text style={[styles.selectorText, prefilledFields.business_permit_city && styles.prefilledDropdownText, !formData.business_permit_city && styles.selectorPlaceholder]}>
                  {formData.business_permit_city || 'Select City/Municipality'}
                </Text>
                <Ionicons name="chevron-down" size={18} color="#666" />
              </TouchableOpacity>

              <Text style={styles.inputLabel}>Business Permit Expiration *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowPermitDatePicker(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.business_permit_expiration && styles.prefilledDropdownText, !formData.business_permit_expiration && styles.placeholderText]}>
                    {formData.business_permit_expiration ? formatDateForDisplay(formData.business_permit_expiration) : 'Business Permit Expiration *'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>

              <Text style={styles.inputLabel}>TIN Business Reg Number *</Text>
              <TextInput
                style={[styles.input, prefilledFields.tin_business_reg_number && styles.prefilledInput]}
                value={formData.tin_business_reg_number || ''}
                onChangeText={(t) => updateForm({ tin_business_reg_number: t })}
                placeholder="TIN Business Reg Number"
                placeholderTextColor="#999"
              />

              <Text style={styles.inputLabel}>DTI/SEC Registration Photo *</Text>
              <TouchableOpacity style={styles.uploadButton} onPress={() => pickImage('dti_sec_registration_photo')}>
                {(formData.dti_sec_registration_photo || formData.dti_sec_registration_photo_server) ? (
                  <View style={styles.uploadedFile}>
                    <Image source={{ uri: getDocImageUrl(formData.dti_sec_registration_photo || formData.dti_sec_registration_photo_server) }} style={styles.thumbnailImage} />
                    <Text style={styles.fileName} numberOfLines={1}>{formData.dti_sec_registration_photo_name || 'Image selected'}</Text>
                    <TouchableOpacity onPress={() => updateForm({ dti_sec_registration_photo: null, dti_sec_registration_photo_server: null, dti_sec_registration_photo_name: null })}>
                      <Ionicons name="close-circle" size={24} color="#E74C3C" />
                    </TouchableOpacity>
                  </View>
                ) : (
                  <View style={styles.uploadPlaceholder}>
                    <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                    <Text style={styles.uploadText}>Tap to upload image</Text>
                    <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                  </View>
                )}
              </TouchableOpacity>
            </View>
          )}

          {/* FINAL STEP */}
          {formStep === 3 && (
            <View>
              <Text style={styles.sectionTitle}>Review & Confirm</Text>
              {targetRole === 'contractor' ? (
                <>
                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Company Information</Text>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Company Name</Text><Text style={styles.previewValue}>{formData.company_name || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Experience</Text><Text style={styles.previewValue}>{formData.experience_start_date ? formatExperience(formData.experience_start_date) : '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Contractor Type</Text><Text style={styles.previewValue}>{(() => { const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`); return sel?.name || '—'; })()}</Text></View>
                    {(() => { const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`); const isOther = (sel?.name || '').toLowerCase().includes('other'); return isOther ? (<View style={styles.previewRow}><Text style={styles.previewLabel}>Other Type</Text><Text style={styles.previewValue}>{formData.contractor_type_other_text || '—'}</Text></View>) : null; })()}
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Services Offered</Text><Text style={styles.previewValue}>{formData.services_offered || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Owner Name</Text><Text style={styles.previewValue}>{[ownerName.first_name, ownerName.middle_name, ownerName.last_name].filter(Boolean).join(' ') || '—'}</Text></View>
                  </View>

                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Business Address</Text>
                    {(() => {
                      const provinceName = provinces.find(p => String(p.code) === String(formData.business_address_province))?.name || '';
                      const cityName = cities.find(c => String(c.code) === String(formData.business_address_city))?.name || '';
                      const barangayName = barangays.find(b => String(b.code) === String(formData.business_address_barangay))?.name || '';
                      const address = [formData.business_address_street || '', barangayName || '', cityName || '', provinceName || ''].filter(Boolean).join(', ');
                      return (
                        <>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Street</Text><Text style={styles.previewValue}>{formData.business_address_street || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Barangay</Text><Text style={styles.previewValue}>{barangayName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>City/Municipality</Text><Text style={styles.previewValue}>{cityName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Province</Text><Text style={styles.previewValue}>{provinceName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Postal Code</Text><Text style={styles.previewValue}>{formData.business_address_postal || '—'}</Text></View>
                          <View style={[styles.previewRow, { marginTop: 6 }]}><Text style={styles.previewLabel}>Full Address</Text><Text style={styles.previewValue}>{address || '—'}</Text></View>
                        </>
                      );
                    })()}
                  </View>

                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Regulatory Documents</Text>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>PICAB Number</Text><Text style={styles.previewValue}>{formData.picab_number || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>PICAB Category</Text><Text style={styles.previewValue}>{formData.picab_category || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>PICAB Expiration</Text><Text style={styles.previewValue}>{formData.picab_expiration_date ? formatDateForDisplay(formData.picab_expiration_date) : '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Business Permit No.</Text><Text style={styles.previewValue}>{formData.business_permit_number || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Permit City</Text><Text style={styles.previewValue}>{formData.business_permit_city || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Permit Expiration</Text><Text style={styles.previewValue}>{formData.business_permit_expiration ? formatDateForDisplay(formData.business_permit_expiration) : '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>TIN Reg Number</Text><Text style={styles.previewValue}>{formData.tin_business_reg_number || '—'}</Text></View>
                    {(formData.dti_sec_registration_photo || formData.dti_sec_registration_photo_server) && (
                      <View style={[styles.previewRow, { alignItems: 'center' }]}>
                        <Text style={styles.previewLabel}>DTI/SEC Photo</Text>
                        <Image source={{ uri: getDocImageUrl(formData.dti_sec_registration_photo || formData.dti_sec_registration_photo_server) }} style={styles.previewImage} />
                      </View>
                    )}
                  </View>
                </>
              ) : (
                <>
                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Personal Details</Text>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>First Name</Text><Text style={styles.previewValue}>{formData.first_name || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Middle Name</Text><Text style={styles.previewValue}>{formData.middle_name || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Last Name</Text><Text style={styles.previewValue}>{formData.last_name || '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Date of Birth</Text><Text style={styles.previewValue}>{formData.date_of_birth ? formatDateForDisplay(formData.date_of_birth) : '—'}</Text></View>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Occupation</Text><Text style={styles.previewValue}>{(() => { const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`); return occ?.name || '—'; })()}</Text></View>
                    {(() => { const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`); const isOther = ((occ?.name || '').toLowerCase()).includes('other'); return isOther ? (<View style={styles.previewRow}><Text style={styles.previewLabel}>Occupation Other</Text><Text style={styles.previewValue}>{formData.occupation_other_text || '—'}</Text></View>) : null; })()}
                  </View>
                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Address</Text>
                    {(() => {
                      const provinceName = provinces.find(p => String(p.code) === String(formData.owner_address_province))?.name || '';
                      const cityName = ownerCities.find(c => String(c.code) === String(formData.owner_address_city))?.name || '';
                      const barangayName = ownerBarangays.find(b => String(b.code) === String(formData.owner_address_barangay))?.name || '';
                      const address = [formData.owner_address_street || '', barangayName || '', cityName || '', provinceName || ''].filter(Boolean).join(', ');
                      return (
                        <>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Street</Text><Text style={styles.previewValue}>{formData.owner_address_street || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Barangay</Text><Text style={styles.previewValue}>{barangayName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>City/Municipality</Text><Text style={styles.previewValue}>{cityName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Province</Text><Text style={styles.previewValue}>{provinceName || '—'}</Text></View>
                          <View style={styles.previewRow}><Text style={styles.previewLabel}>Postal Code</Text><Text style={styles.previewValue}>{formData.owner_address_postal || '—'}</Text></View>
                          <View style={[styles.previewRow, { marginTop: 6 }]}><Text style={styles.previewLabel}>Full Address</Text><Text style={styles.previewValue}>{address || '—'}</Text></View>
                        </>
                      );
                    })()}
                  </View>
                  <View style={styles.previewCard}>
                    <Text style={styles.previewHeader}>Documents</Text>
                    <View style={styles.previewRow}><Text style={styles.previewLabel}>Valid ID</Text><Text style={styles.previewValue}>{(() => { const v = (dropdowns.valid_ids || []).find((vi: any) => `${vi.id}` === `${formData.valid_id_id}`); return v?.name || '—'; })()}</Text></View>
                    {(formData.valid_id_photo || formData.owner_valid_id_photo_server) && (
                      <View style={[styles.previewRow, { alignItems: 'center' }]}>
                        <Text style={styles.previewLabel}>ID Front</Text>
                        <Image source={{ uri: getDocImageUrl(formData.valid_id_photo || formData.owner_valid_id_photo_server) }} style={styles.previewImage} />
                      </View>
                    )}
                    {(formData.valid_id_back_photo || formData.owner_valid_id_back_photo_server) && (
                      <View style={[styles.previewRow, { alignItems: 'center' }]}>
                        <Text style={styles.previewLabel}>ID Back</Text>
                        <Image source={{ uri: getDocImageUrl(formData.valid_id_back_photo || formData.owner_valid_id_back_photo_server) }} style={styles.previewImage} />
                      </View>
                    )}
                    {formData.police_clearance && (
                      <View style={[styles.previewRow, { alignItems: 'center' }]}>
                        <Text style={styles.previewLabel}>Police Clearance</Text>
                        <Image source={{ uri: getDocImageUrl(formData.police_clearance) }} style={styles.previewImage} />
                      </View>
                    )}
                  </View>
                </>
              )}
              {/* Terms of Service */}
              <View style={styles.tosCard}>
                <View style={styles.tosCardHeader}>
                  <Ionicons name="document-text-outline" size={18} color="#1E3A5F" />
                  <Text style={styles.tosCardTitle}>Terms of Service</Text>
                </View>
                <View style={styles.tosDivider} />
                <Text style={styles.tosCardBody}>
                  Before submitting your application, you must read and acknowledge the Legatura Terms of Service.
                </Text>
                <TouchableOpacity
                  style={[styles.tosReadButton, termsAgreed && styles.tosReadButtonAgreed]}
                  onPress={() => setShowTermsModal(true)}
                >
                  <Ionicons name={termsAgreed ? 'checkmark-circle-outline' : 'document-text-outline'} size={18} color="#FFFFFF" />
                  <Text style={styles.tosReadButtonText}>
                    {termsAgreed ? 'Terms Read & Acknowledged ✓' : 'Read Terms of Service'}
                  </Text>
                </TouchableOpacity>
              </View>

              <View style={styles.confirmRow}>
                <TouchableOpacity
                  onPress={() => {
                    if (!termsAgreed) {
                      Alert.alert('Terms of Service', 'Please read and acknowledge the Terms of Service first.');
                      return;
                    }
                    setAgreedConfirm(!agreedConfirm);
                  }}
                  style={styles.confirmCheckbox}
                >
                  <Ionicons name={agreedConfirm ? 'checkmark-circle' : 'checkmark-circle-outline'} size={24} color={agreedConfirm ? '#2ECC71' : '#666'} />
                </TouchableOpacity>
                <Text style={styles.confirmText}>I confirm the details above are accurate and agree to submit.</Text>
              </View>
            </View>
          )}

          {/* OWNER STEP 1 */}
          {targetRole === 'owner' && formStep === 1 && (
            <View>
              <Text style={[styles.inputLabel, { marginTop: 12 }]}>First Name *</Text>
              <TextInput
                style={[styles.input, prefilledFields.first_name && styles.prefilledInput]}
                value={formData.first_name || ''}
                onChangeText={(t) => updateForm({ first_name: t })}
                placeholder="First Name *"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Middle Name</Text>
              <TextInput
                style={[styles.input, prefilledFields.middle_name && styles.prefilledInput]}
                value={formData.middle_name || ''}
                onChangeText={(t) => updateForm({ middle_name: t })}
                placeholder="Middle Name (Optional)"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Last Name *</Text>
              <TextInput
                style={[styles.input, prefilledFields.last_name && styles.prefilledInput]}
                value={formData.last_name || ''}
                onChangeText={(t) => updateForm({ last_name: t })}
                placeholder="Last Name *"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Date of Birth *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowDobModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.date_of_birth && styles.prefilledDropdownText, !formData.date_of_birth && styles.placeholderText]}>
                    {formData.date_of_birth || 'Select Date of Birth *'}
                  </Text>
                  <Ionicons name="calendar" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Occupation *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowOccupationModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.occupation_id && styles.prefilledDropdownText, !formData.occupation_id && styles.placeholderText]}>
                    {(() => {
                      const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`);
                      return occ?.name || 'Select Occupation *';
                    })()}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              {(() => {
                const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`);
                const occName = (occ?.name || '').toLowerCase();
                const isOther = occName.includes('other');
                if (!isOther) return null;
                return (
                  <>
                    <Text style={styles.inputLabel}>Occupation Other</Text>
                    <TextInput
                      style={styles.input}
                      value={formData.occupation_other_text || ''}
                      onChangeText={(t) => updateForm({ occupation_other_text: t })}
                      placeholder="Occupation (Other)"
                      placeholderTextColor="#999"
                    />
                  </>
                );
              })()}
              <Text style={styles.inputLabel}>Address Street *</Text>
              <TextInput
                style={[styles.input, prefilledFields.owner_address_street && styles.prefilledInput]}
                value={formData.owner_address_street || ''}
                onChangeText={(t) => updateForm({ owner_address_street: t })}
                placeholder="Street"
                placeholderTextColor="#999"
              />
              <Text style={styles.inputLabel}>Province *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowOwnerProvinceModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.owner_address_province && styles.prefilledDropdownText,
                    !formData.owner_address_province && styles.placeholderText
                  ]}>
                    {provinces.find(p => String(p.code) === String(formData.owner_address_province))?.name || 'Select Province'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              <Text style={styles.inputLabel}>City/Municipality *</Text>
              <TouchableOpacity
                style={[styles.input, !formData.owner_address_province && styles.inputDisabled]}
                onPress={() => formData.owner_address_province && setShowOwnerCityModal(true)}
                disabled={!formData.owner_address_province}
              >
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.owner_address_city && styles.prefilledDropdownText,
                    !formData.owner_address_city && styles.placeholderText
                  ]}>
                    {ownerCities.length > 0
                      ? (ownerCities.find(c => String(c.code) === String(formData.owner_address_city))?.name || 'Select City/Municipality')
                      : (formData.owner_address_province ? 'Loading City...' : 'Select Province First')
                    }
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Barangay *</Text>
              <TouchableOpacity
                style={[styles.input, !formData.owner_address_city && styles.inputDisabled]}
                onPress={() => formData.owner_address_city && setShowOwnerBarangayModal(true)}
                disabled={!formData.owner_address_city}
              >
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[
                    styles.dropdownInputText,
                    prefilledFields.owner_address_barangay && styles.prefilledDropdownText,
                    !formData.owner_address_barangay && styles.placeholderText
                  ]}>
                    {ownerBarangays.length > 0
                      ? (ownerBarangays.find(b => String(b.code) === String(formData.owner_address_barangay))?.name || 'Select Barangay')
                      : (formData.owner_address_city ? 'Loading Barangay...' : 'Select City First')
                    }
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Postal Code *</Text>
              <TextInput
                style={[styles.input, prefilledFields.owner_address_postal && styles.prefilledInput]}
                value={formData.owner_address_postal || ''}
                onChangeText={(t) => updateForm({ owner_address_postal: t })}
                keyboardType="number-pad"
                placeholder="Postal Code"
                placeholderTextColor="#999"
              />
            </View>
          )}

          {/* OWNER STEP 2 */}
          {targetRole === 'owner' && formStep === 2 && (
            <View>
              <Text style={styles.inputLabel}>Valid ID *</Text>
              <TouchableOpacity style={styles.input} onPress={() => setShowValidIdModal(true)}>
                <View style={styles.dropdownInputWrapper}>
                  <Text style={[styles.dropdownInputText, prefilledFields.valid_id_id && styles.prefilledDropdownText, !formData.valid_id_id && styles.placeholderText]}>
                    {(() => {
                      const v = (dropdowns.valid_ids || []).find((vi: any) => `${vi.id}` === `${formData.valid_id_id}`);
                      return v?.name || 'Select Valid ID *';
                    })()}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#666" />
                </View>
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Valid ID Front *</Text>
              <TouchableOpacity style={styles.uploadButton} onPress={() => pickImage('valid_id_photo')}>
                {(formData.valid_id_photo || formData.owner_valid_id_photo_server) ? (
                  <View style={styles.uploadedFile}>
                    <Image source={{ uri: getDocImageUrl(formData.valid_id_photo || formData.owner_valid_id_photo_server) }} style={styles.thumbnailImage} />
                    <Text style={styles.fileName} numberOfLines={1}>{formData.valid_id_photo_name || 'Image selected'}</Text>
                    <TouchableOpacity onPress={() => updateForm({ valid_id_photo: null, owner_valid_id_photo_server: null, valid_id_photo_name: null })}>
                      <Ionicons name="close-circle" size={24} color="#E74C3C" />
                    </TouchableOpacity>
                  </View>
                ) : (
                  <View style={styles.uploadPlaceholder}>
                    <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                    <Text style={styles.uploadText}>Tap to upload image</Text>
                    <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                  </View>
                )}
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Valid ID Back *</Text>
              <TouchableOpacity style={styles.uploadButton} onPress={() => pickImage('valid_id_back_photo')}>
                {(formData.valid_id_back_photo || formData.owner_valid_id_back_photo_server) ? (
                  <View style={styles.uploadedFile}>
                    <Image source={{ uri: getDocImageUrl(formData.valid_id_back_photo || formData.owner_valid_id_back_photo_server) }} style={styles.thumbnailImage} />
                    <Text style={styles.fileName} numberOfLines={1}>{formData.valid_id_back_photo_name || 'Image selected'}</Text>
                    <TouchableOpacity onPress={() => updateForm({ valid_id_back_photo: null, owner_valid_id_back_photo_server: null, valid_id_back_photo_name: null })}>
                      <Ionicons name="close-circle" size={24} color="#E74C3C" />
                    </TouchableOpacity>
                  </View>
                ) : (
                  <View style={styles.uploadPlaceholder}>
                    <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                    <Text style={styles.uploadText}>Tap to upload image</Text>
                    <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                  </View>
                )}
              </TouchableOpacity>
              <Text style={styles.inputLabel}>Police Clearance *</Text>
              <TouchableOpacity style={styles.uploadButton} onPress={() => pickImage('police_clearance')}>
                {(formData.police_clearance || formData.owner_police_clearance_server) ? (
                  <View style={styles.uploadedFile}>
                    <Image source={{ uri: getDocImageUrl(formData.police_clearance || formData.owner_police_clearance_server) }} style={styles.thumbnailImage} />
                    <Text style={styles.fileName} numberOfLines={1}>{formData.police_clearance_name || 'Image selected'}</Text>
                    <TouchableOpacity onPress={() => updateForm({ police_clearance: null, owner_police_clearance_server: null, police_clearance_name: null })}>
                      <Ionicons name="close-circle" size={24} color="#E74C3C" />
                    </TouchableOpacity>
                  </View>
                ) : (
                  <View style={styles.uploadPlaceholder}>
                    <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                    <Text style={styles.uploadText}>Tap to upload image</Text>
                    <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                  </View>
                )}
              </TouchableOpacity>
            </View>
          )}

          {/* Buttons */}
          <View style={styles.buttonContainer}>
            <TouchableOpacity style={styles.backButton} onPress={onBack}>
              <Text style={styles.backButtonText}>Back</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.nextButton, submitting && styles.nextButtonDisabled]}
              onPress={handlePrimaryPress}
              disabled={submitting}
            >
              {submitting ? (
                <ActivityIndicator color="#FFFFFF" />
              ) : (
                <Text style={styles.nextButtonText}>
                  {formStep < 3 ? 'Next' : 'Submit'}
                </Text>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </KeyboardAwareScrollView>

      {/* Modals */}
      <Modal visible={showContractorTypeModal} animationType="slide" transparent onRequestClose={() => setShowContractorTypeModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Contractor Type</Text>
              <TouchableOpacity onPress={() => setShowContractorTypeModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={[...dropdowns.contractor_types].sort((a, b) => {
                const an = (a.name || '').toLowerCase();
                const bn = (b.name || '').toLowerCase();
                if (an === 'others') return 1;
                if (bn === 'others') return -1;
                return an.localeCompare(bn);
              })}
              keyExtractor={(item) => `${item.id}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ contractor_type_id: `${item.id}` });
                    setShowContractorTypeModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showPicabCategoryModal} animationType="slide" transparent onRequestClose={() => setShowPicabCategoryModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select PICAB Category</Text>
              <TouchableOpacity onPress={() => setShowPicabCategoryModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={dropdowns.picab_categories}
              keyExtractor={(item, index) => `${item}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ picab_category: item });
                    setShowPicabCategoryModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showProvinceModal} animationType="slide" transparent onRequestClose={() => setShowProvinceModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Province</Text>
              <TouchableOpacity onPress={() => setShowProvinceModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={provinces}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ business_address_province: item.code });
                    setShowProvinceModal(false);
                    loadCities(item.code);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showCityModal} animationType="slide" transparent onRequestClose={() => setShowCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select City/Municipality</Text>
              <TouchableOpacity onPress={() => setShowCityModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={cities}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ business_address_city: item.code });
                    setShowCityModal(false);
                    loadBarangays(item.code);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showBarangayModal} animationType="slide" transparent onRequestClose={() => setShowBarangayModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity onPress={() => setShowBarangayModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={barangays}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ business_address_barangay: item.code });
                    setShowBarangayModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showOwnerProvinceModal} animationType="slide" transparent onRequestClose={() => setShowOwnerProvinceModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Province</Text>
              <TouchableOpacity onPress={() => setShowOwnerProvinceModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={provinces}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ owner_address_province: item.code });
                    setShowOwnerProvinceModal(false);
                    loadOwnerCities(item.code);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showOwnerCityModal} animationType="slide" transparent onRequestClose={() => setShowOwnerCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select City/Municipality</Text>
              <TouchableOpacity onPress={() => setShowOwnerCityModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={ownerCities}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ owner_address_city: item.code });
                    setShowOwnerCityModal(false);
                    loadOwnerBarangays(item.code);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showOwnerBarangayModal} animationType="slide" transparent onRequestClose={() => setShowOwnerBarangayModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity onPress={() => setShowOwnerBarangayModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={ownerBarangays}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ owner_address_barangay: item.code });
                    setShowOwnerBarangayModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showValidIdModal} animationType="slide" transparent onRequestClose={() => setShowValidIdModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Valid ID</Text>
              <TouchableOpacity onPress={() => setShowValidIdModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={dropdowns.valid_ids}
              keyExtractor={(item) => `${item.id}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ valid_id_id: `${item.id}` });
                    setShowValidIdModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showOccupationModal} animationType="slide" transparent onRequestClose={() => setShowOccupationModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Occupation</Text>
              <TouchableOpacity onPress={() => setShowOccupationModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={dropdowns.occupations || []}
              keyExtractor={(item, index) => `${item.id ?? item.occupation_id ?? index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    const name = (item.occupation_name || item.name || item.title || '').toLowerCase();
                    const isOther = name.includes('other');
                    updateForm({
                      occupation_id: `${item.id ?? item.occupation_id}`,
                      occupation_other_text: isOther ? (formData.occupation_other_text || '') : ''
                    });
                    setShowOccupationModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.occupation_name || item.name || item.title || `${item.id}`}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      <Modal visible={showDobModal} animationType="slide" transparent onRequestClose={() => setShowDobModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Date of Birth</Text>
              <TouchableOpacity onPress={() => setShowDobModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <View style={{ paddingHorizontal: 20 }}>
              <DateTimePicker
                value={formData.date_of_birth ? new Date(formData.date_of_birth) : new Date()}
                mode="date"
                display="default"
                maximumDate={new Date()}
                onChange={(event, date) => {
                  if (date) {
                    const today = new Date();
                    const picked = date > today ? today : date;
                    updateForm({ date_of_birth: formatDate(picked) });
                  }
                  setShowDobModal(false);
                }}
              />
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showExperienceDateModal} animationType="slide" transparent onRequestClose={() => setShowExperienceDateModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Company Start Date</Text>
              <TouchableOpacity onPress={() => setShowExperienceDateModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <View style={{ paddingHorizontal: 20 }}>
              <DateTimePicker
                value={formData.experience_start_date ? new Date(formData.experience_start_date) : new Date()}
                mode="date"
                display="spinner"
                minimumDate={new Date(1900, 0, 1)}
                maximumDate={new Date()}
                onChange={(event, date) => {
                  if (date) {
                    const today = new Date();
                    const picked = date > today ? today : date;
                    updateForm({ experience_start_date: formatDate(picked) });
                  }
                  setShowExperienceDateModal(false);
                }}
              />
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showPermitCityModal} animationType="slide" transparent onRequestClose={() => setShowPermitCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Business Permit City</Text>
              <TouchableOpacity onPress={() => setShowPermitCityModal(false)} style={styles.closeButton}>
                <Ionicons name="close" size={22} color="#333" />
              </TouchableOpacity>
            </View>
            <View style={{ paddingHorizontal: 16, paddingBottom: 8 }}>
              <TextInput
                style={styles.modalSearchInput}
                placeholder="Search city/municipality"
                placeholderTextColor="#999"
                value={permitCityQuery}
                onChangeText={setPermitCityQuery}
              />
            </View>
            <FlatList
              data={permitCities.filter((c: any) => {
                const q = (permitCityQuery || '').toLowerCase();
                if (!q) return true;
                return (c.name || '').toLowerCase().includes(q);
              })}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    updateForm({ business_permit_city: item.name });
                    setShowPermitCityModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* PICAB Expiration Date Picker */}
      <Modal
        visible={showPicabDatePicker}
        transparent
        animationType="slide"
        onRequestClose={() => setShowPicabDatePicker(false)}
      >
        <View style={styles.datePickerModalOverlay}>
          <View style={styles.datePickerModalContainer}>
            <View style={styles.datePickerModalHeader}>
              <Text style={styles.datePickerModalTitle}>Select PICAB Expiration Date</Text>
              <TouchableOpacity onPress={() => setShowPicabDatePicker(false)} style={styles.datePickerModalCloseButton}>
                <Text style={styles.datePickerModalCloseText}>Done</Text>
              </TouchableOpacity>
            </View>
            <CustomDatePicker
              currentDate={picabDate}
              minimumDate={new Date()}
              onDateChange={(y, m, d) => {
                const newDate = new Date(y, m - 1, d);
                setPicabDate(newDate);
                updateForm({ picab_expiration_date: formatDate(newDate) });
                setShowPicabDatePicker(false);
              }}
            />
          </View>
        </View>
      </Modal>

      {/* Business Permit Expiration Date Picker */}
      <Modal
        visible={showPermitDatePicker}
        transparent
        animationType="slide"
        onRequestClose={() => setShowPermitDatePicker(false)}
      >
        <View style={styles.datePickerModalOverlay}>
          <View style={styles.datePickerModalContainer}>
            <View style={styles.datePickerModalHeader}>
              <Text style={styles.datePickerModalTitle}>Select Business Permit Expiration Date</Text>
              <TouchableOpacity onPress={() => setShowPermitDatePicker(false)} style={styles.datePickerModalCloseButton}>
                <Text style={styles.datePickerModalCloseText}>Done</Text>
              </TouchableOpacity>
            </View>
            <CustomDatePicker
              currentDate={permitDate}
              minimumDate={new Date()}
              onDateChange={(y, m, d) => {
                const newDate = new Date(y, m - 1, d);
                setPermitDate(newDate);
                updateForm({ business_permit_expiration: formatDate(newDate) });
                setShowPermitDatePicker(false);
              }}
            />
          </View>
        </View>
      </Modal>

      {/* Terms of Service Modal */}
      <Modal visible={showTermsModal} animationType="slide" transparent onRequestClose={() => setShowTermsModal(false)}>
        <View style={styles.tosModalOverlay}>
          <View style={styles.tosModalContainer}>
            <View style={styles.tosModalHeader}>
              <Text style={styles.tosModalTitle}>Terms of Service</Text>
              <TouchableOpacity onPress={() => setShowTermsModal(false)} style={styles.tosModalClose}>
                <Ionicons name="close" size={22} color="#1E3A5F" />
              </TouchableOpacity>
            </View>
            <ScrollView
              style={styles.tosModalScroll}
              contentContainerStyle={styles.tosScrollContent}
              showsVerticalScrollIndicator={true}
              scrollIndicatorInsets={{ right: 1 }}
              onScroll={({ nativeEvent }) => {
                const { contentOffset, contentSize, layoutMeasurement } = nativeEvent;
                if (contentOffset.y + layoutMeasurement.height >= contentSize.height - 30) {
                  setHasReadTerms(true);
                }
              }}
              scrollEventThrottle={16}
            >
              <Text style={styles.tosText}>{LEGATURA_TOS}</Text>
            </ScrollView>
            {!hasReadTerms ? (
              <View style={styles.tosScrollHint}>
                <Ionicons name="arrow-down" size={14} color="#92400E" />
                <Text style={styles.tosScrollHintText}>  Scroll down to read the full Terms of Service</Text>
              </View>
            ) : null}
            <TouchableOpacity
              style={[styles.tosAcknowledgeButton, !hasReadTerms && styles.tosAcknowledgeButtonDisabled]}
              disabled={!hasReadTerms}
              onPress={() => { setTermsAgreed(true); setShowTermsModal(false); }}
            >
              <Text style={styles.tosAcknowledgeButtonText}>
                {hasReadTerms ? 'I Acknowledge & Agree' : 'Read to Continue'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      {/* Submit Confirmation Modal */}
      <Modal visible={showSubmitConfirm} animationType="fade" transparent onRequestClose={() => setShowSubmitConfirm(false)}>
        <View style={styles.confirmModalOverlay}>
          <View style={styles.confirmModalContainer}>
            <Text style={styles.confirmModalTitle}>Confirm Submission</Text>
            <Text style={styles.confirmModalText}>
              Please confirm that all details are correct. This will finalize your re-application for the {targetRole === 'contractor' ? 'Contractor' : 'Owner'} role.
            </Text>
            <View style={styles.confirmActions}>
              <TouchableOpacity style={styles.confirmCancelButton} onPress={() => setShowSubmitConfirm(false)}>
                <Text style={styles.confirmCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.confirmSubmitButton} onPress={() => { setShowSubmitConfirm(false); submitStep(); }}>
                <Text style={styles.confirmSubmitText}>Confirm & Submit</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FEFEFE' },
  scrollContent: { flexGrow: 1, paddingHorizontal: 30, paddingBottom: 40 },
  logoContainer: { alignItems: 'center', marginTop: 40, marginBottom: 30 },
  logo: { width: 200, height: 120 },
  loadingContainer: { justifyContent: 'center', alignItems: 'center', paddingVertical: 20 },
  loadingText: { marginTop: 16, fontSize: 16, color: '#666666' },
  progressContainer: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 40, paddingHorizontal: 10 },
  progressStep: { flex: 1, alignItems: 'center' },
  progressBar: { height: 4, backgroundColor: '#E5E5E5', borderRadius: 2, width: '100%', marginBottom: 8 },
  progressBarActive: { backgroundColor: '#EC7E00' },
  progressText: { fontSize: 12, color: '#999999', textAlign: 'center' },
  progressTextActive: { color: '#333333', fontWeight: '600' },
  formContainer: { flex: 1, gap: 20 },
  sectionTitle: { fontSize: 18, fontWeight: 'bold', color: '#333333', marginBottom: 20, marginTop: 10 },
  inputLabel: { fontSize: 13, color: '#666666', marginBottom: 6, marginTop: 8 },
  input: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 16, fontSize: 16, color: '#333333', marginBottom: 8 },
  inputDisabled: { backgroundColor: '#F5F5F5', borderColor: '#DDDDDD' },
  readOnlyInput: { backgroundColor: '#F5F5F5', borderColor: '#E0E0E0', color: '#666666' },
  inputSelector: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 },
  selectorText: { fontSize: 16, color: '#333333', flex: 1 },
  selectorPlaceholder: { color: '#999999' },
  dropdownInputWrapper: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  dropdownInputText: { fontSize: 16, color: '#333333', flex: 1 },
  placeholderText: { color: '#999999' },
  prefilledInput: { color: '#EC7E00' },
  prefilledDropdownText: { color: '#EC7E00' },
  infoNotice: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF5EB', padding: 10, borderRadius: 10, marginBottom: 12 },
  infoNoticeText: { marginLeft: 8, color: '#EC7E00', flex: 1 },
  uploadButton: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#DDDDDD', borderRadius: 8, borderStyle: 'dashed', padding: 20, alignItems: 'center' },
  uploadPlaceholder: { alignItems: 'center' },
  uploadText: { fontSize: 14, color: '#333333', marginTop: 8 },
  uploadHint: { fontSize: 11, color: '#999999', marginTop: 4 },
  uploadedFile: { flexDirection: 'row', alignItems: 'center', width: '100%' },
  thumbnailImage: { width: 50, height: 50, borderRadius: 6, marginRight: 12 },
  fileName: { flex: 1, fontSize: 14, color: '#333333' },
  buttonContainer: { flexDirection: 'row', gap: 15, marginTop: 24, paddingHorizontal: 5, paddingBottom: 20 },
  backButton: { flex: 1, backgroundColor: '#E8E8E8', borderRadius: 12, paddingVertical: 18, alignItems: 'center', marginRight: 8 },
  backButtonText: { color: '#333333', fontSize: 18, fontWeight: '600' },
  nextButton: { flex: 1, backgroundColor: '#EC7E00', borderRadius: 12, paddingVertical: 18, alignItems: 'center', marginLeft: 8 },
  nextButtonText: { color: '#FFFFFF', fontSize: 18, fontWeight: '600' },
  nextButtonDisabled: { backgroundColor: '#CCCCCC' },
  confirmRow: { flexDirection: 'row', alignItems: 'center', marginTop: 12 },
  confirmCheckbox: { marginRight: 8 },
  confirmText: { flex: 1, fontSize: 13, color: '#333333' },
  tosCard: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 4, padding: 14, marginTop: 20, marginBottom: 4, elevation: 1 },
  tosCardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  tosCardTitle: { fontSize: 13, fontWeight: '700', color: '#1E3A5F', marginLeft: 8, letterSpacing: 0.3 },
  tosDivider: { height: 1, backgroundColor: '#E2E8F0', marginBottom: 10 },
  tosCardBody: { fontSize: 13, color: '#64748B', marginBottom: 12, lineHeight: 19 },
  tosReadButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: '#1E3A5F', borderRadius: 8, paddingVertical: 10, paddingHorizontal: 12 },
  tosReadButtonAgreed: { backgroundColor: '#10B981' },
  tosReadButtonText: { fontSize: 14, fontWeight: '600', color: '#FFFFFF', marginLeft: 8 },
  tosModalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'flex-end' },
  tosModalContainer: { backgroundColor: '#FFFFFF', borderTopLeftRadius: 16, borderTopRightRadius: 16, height: '85%' },
  tosModalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 14, borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
  tosModalTitle: { fontSize: 16, fontWeight: '700', color: '#1E3A5F' },
  tosModalClose: { padding: 4 },
  tosModalScroll: { flex: 1 },
  tosScrollContent: { padding: 16, paddingBottom: 32 },
  tosText: { fontSize: 13, color: '#334155', lineHeight: 22 },
  tosScrollHint: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 8, backgroundColor: '#FEF3C7' },
  tosScrollHintText: { fontSize: 12, color: '#92400E', fontWeight: '500' },
  tosAcknowledgeButton: { marginHorizontal: 16, marginTop: 12, marginBottom: 20, backgroundColor: '#EC7E00', borderRadius: 8, paddingVertical: 14, alignItems: 'center' },
  tosAcknowledgeButtonDisabled: { backgroundColor: '#CBD5E1' },
  tosAcknowledgeButtonText: { fontSize: 15, fontWeight: '700', color: '#FFFFFF' },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center' },
  modalContainer: { backgroundColor: '#FFFFFF', borderRadius: 16, width: '90%', maxHeight: '80%', paddingVertical: 20 },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, marginBottom: 15 },
  modalTitle: { fontSize: 20, fontWeight: 'bold', color: '#333333' },
  closeButton: { padding: 4 },
  modalItem: { paddingVertical: 15, paddingHorizontal: 20, borderBottomWidth: 1, borderBottomColor: '#F0F0F0' },
  modalItemText: { fontSize: 16, color: '#333333' },
  modalSearchInput: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 12, fontSize: 16, color: '#333333' },
  previewCard: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, padding: 16, marginBottom: 14 },
  previewHeader: { fontSize: 16, fontWeight: '700', color: '#333333', marginBottom: 10 },
  previewRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 6 },
  previewLabel: { fontSize: 13, color: '#666666', flex: 1 },
  previewValue: { fontSize: 13, color: '#1A1A1A', flex: 1, textAlign: 'right' },
  previewImage: { width: 70, height: 70, borderRadius: 8, borderWidth: 1, borderColor: '#E5E5E5', marginLeft: 12 },
  // Date picker modal styles
  datePickerModalOverlay: { flex: 1, backgroundColor: 'rgba(0, 0, 0, 0.5)', justifyContent: 'flex-end' },
  datePickerModalContainer: { backgroundColor: '#FFFFFF', borderTopLeftRadius: 20, borderTopRightRadius: 20, paddingBottom: 20 },
  datePickerModalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, paddingVertical: 15, borderBottomWidth: 1, borderBottomColor: '#E5E5E5' },
  datePickerModalTitle: { fontSize: 18, fontWeight: '600', color: '#333333' },
  datePickerModalCloseButton: { paddingVertical: 5, paddingHorizontal: 10 },
  datePickerModalCloseText: { fontSize: 16, color: '#EC7E00', fontWeight: '600' },
  customDatePickerContainer: { padding: 20 },
  datePickerRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 20 },
  datePickerColumn: { flex: 1, marginHorizontal: 5 },
  datePickerLabel: { fontSize: 14, fontWeight: '600', color: '#666666', marginBottom: 10, textAlign: 'center' },
  datePickerScroll: { maxHeight: 200, backgroundColor: '#F5F5F5', borderRadius: 8 },
  datePickerItem: { paddingVertical: 12, paddingHorizontal: 8, alignItems: 'center', borderBottomWidth: 1, borderBottomColor: '#E5E5E5' },
  datePickerItemSelected: { backgroundColor: '#EC7E00' },
  datePickerItemDisabled: { opacity: 0.3 },
  datePickerItemText: { fontSize: 16, color: '#333333' },
  datePickerItemTextSelected: { color: '#FFFFFF', fontWeight: '600' },
  datePickerItemTextDisabled: { color: '#999999' },
  datePickerConfirmButton: { backgroundColor: '#EC7E00', borderRadius: 12, paddingVertical: 16, alignItems: 'center', marginTop: 10 },
  datePickerConfirmText: { color: '#FFFFFF', fontSize: 16, fontWeight: '600' },
  // Submit confirmation modal
  confirmModalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center' },
  confirmModalContainer: { backgroundColor: '#FFFFFF', borderRadius: 16, width: '88%', paddingVertical: 22, paddingHorizontal: 20 },
  confirmModalTitle: { fontSize: 18, fontWeight: '700', color: '#333333', marginBottom: 8 },
  confirmModalText: { fontSize: 14, color: '#666666', marginBottom: 18 },
  confirmActions: { flexDirection: 'row', justifyContent: 'flex-end', gap: 12 },
  confirmCancelButton: { backgroundColor: '#E8E8E8', borderRadius: 10, paddingVertical: 12, paddingHorizontal: 16 },
  confirmCancelText: { color: '#333333', fontSize: 14, fontWeight: '600' },
  confirmSubmitButton: { backgroundColor: '#EC7E00', borderRadius: 10, paddingVertical: 12, paddingHorizontal: 16 },
  confirmSubmitText: { color: '#FFFFFF', fontSize: 14, fontWeight: '700' },
});
