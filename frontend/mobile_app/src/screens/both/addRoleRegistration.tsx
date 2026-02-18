// @ts-nocheck
import React, { useEffect, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ScrollView, TextInput, ActivityIndicator, Alert, Modal, FlatList, SafeAreaView, Image } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { StatusBar } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { role_service } from '../../services/role_service';
import { api_request, api_config } from '../../config/api';
import { auth_service } from '../../services/auth_service';
import * as ImagePicker from 'expo-image-picker';
import PlatformDatePicker from '../../components/PlatformDatePicker';
import { Platform } from 'react-native';
import { computeYears, formatDate, formatDateForDisplay, formatExperience } from '../../utils/roleFormUtils';

interface RoleAddScreenProps {
  targetRole: 'contractor' | 'owner';
  onBack: () => void;
  onComplete: () => void;
}

export default function RoleAddScreen(props: RoleAddScreenProps & { route?: any; navigation?: any }) {
  // SAFETY CHECK: fallback for userData
  const userData = (props as any)?.userData;
  const user = userData || {};
  // hasBoth state initialized from user roles when available

  const { targetRole: targetRoleProp, onBack, onComplete, route, navigation } = (props as any) || {};
  const insets = useSafeAreaInsets();
  const targetRole = (targetRoleProp ?? route?.params?.targetRole ?? 'contractor') as 'contractor' | 'owner';
  const handleBack = () => {
    console.log('[addRoleRegistration] Back pressed, formStep:', formStep);
    if (formStep > 1) {
      console.log('[addRoleRegistration] Decrementing formStep:', formStep - 1);
      setFormStep(formStep - 1);
    } else {
      console.log('[addRoleRegistration] Calling onBack or navigation.goBack');
      if (onBack) {
        onBack();
      } else if (navigation?.goBack) {
        navigation.goBack();
      }
    }
  };
  const handleComplete = onComplete ?? (() => navigation?.goBack?.());
  const [loading, setLoading] = useState(true);
  const [formStep, setFormStep] = useState(1);
  const [formData, setFormData] = useState<any>({});
  const [submitting, setSubmitting] = useState(false);
  const [currentRoleInfo, setCurrentRoleInfo] = useState<any>(null);
  const [hasBoth, setHasBoth] = useState<boolean>(() => !!(user?.is_contractor && user?.is_owner));
  const [blockedDueToPending, setBlockedDueToPending] = useState<boolean>(false);
  const [pendingInfo, setPendingInfo] = useState<any>(null);
  const [dropdowns, setDropdowns] = useState<any>({
    contractor_types: [],
    occupations: [],
    valid_ids: [],
    provinces: [],
    picab_categories: [],
  });
  const [existingData, setExistingData] = useState<any>(null);
  const [provinces, setProvinces] = useState<any[]>([]);
  const [cities, setCities] = useState<any[]>([]);
  const [barangays, setBarangays] = useState<any[]>([]);
  const [addressPrefilled, setAddressPrefilled] = useState(false);
  const [prefilledFields, setPrefilledFields] = useState<Record<string, boolean>>({});
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
  const [picabDate, setPicabDate] = useState<Date>(() => {
    return formData.picab_expiration_date ? new Date(formData.picab_expiration_date) : new Date();
  });
  const [permitDate, setPermitDate] = useState<Date>(() => {
    return formData.business_permit_expiration ? new Date(formData.business_permit_expiration) : new Date();
  });
  // Business Permit City (searchable all-cities list)
  const [showPermitCityModal, setShowPermitCityModal] = useState(false);
  const [permitCities, setPermitCities] = useState<any[]>([]);
  const [permitCityQuery, setPermitCityQuery] = useState('');
  const [agreedConfirm, setAgreedConfirm] = useState(false);
  const [showSubmitConfirm, setShowSubmitConfirm] = useState(false);

  useEffect(() => {
    (async () => {
      try {
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

          // Prefill fields from existing_data similar to authController
          const existing = root?.existing_data || {};
          setExistingData(existing);
          if (targetRole === 'contractor') {
            const phone = existing?.property_owner?.phone_number || existing?.user?.phone_number;
            if (phone) updatePrefilled({ company_phone: phone });
            // Prefill authorized representative fields if available from existing contractor_user
            const cu = existing?.contractor_user || existing?.contractor_user_for_this_user || existing?.contractor_user_for_current;
            const repPrefill: any = {};
            if (cu?.authorized_rep_fname) repPrefill.authorized_rep_fname = cu.authorized_rep_fname;
            if (cu?.authorized_rep_mname) repPrefill.authorized_rep_mname = cu.authorized_rep_mname;
            if (cu?.authorized_rep_lname) repPrefill.authorized_rep_lname = cu.authorized_rep_lname;
            // Fallback: if no contractor_user authorized rep info, use property_owner name (owner becomes authorized rep)
            if (!repPrefill.authorized_rep_fname && existing?.property_owner?.first_name) {
              repPrefill.authorized_rep_fname = existing.property_owner.first_name;
            }
            if (!repPrefill.authorized_rep_mname && existing?.property_owner?.middle_name) {
              repPrefill.authorized_rep_mname = existing.property_owner.middle_name;
            }
            if (!repPrefill.authorized_rep_lname && existing?.property_owner?.last_name) {
              repPrefill.authorized_rep_lname = existing.property_owner.last_name;
            }
            if (Object.keys(repPrefill).length) updatePrefilled(repPrefill);
          } else if (targetRole === 'owner') {
            const user = existing?.user || {};
            const cu = existing?.contractor_user || {};
            const prefill: any = {};
            // Username is not required for switch/role reapplication form; omit prefilling username.
            // Email removed from owner switch form; do not prefill
            if (cu?.authorized_rep_fname) prefill.first_name = cu.authorized_rep_fname;
            if (cu?.authorized_rep_mname) prefill.middle_name = cu.authorized_rep_mname;
            if (cu?.authorized_rep_lname) prefill.last_name = cu.authorized_rep_lname;
            if (cu?.phone_number) prefill.phone_number = cu.phone_number;
            if (Object.keys(prefill).length) updatePrefilled(prefill);
          }

          // Also load PSGC provinces for consistent codes used elsewhere
          const provRes = await auth_service.get_provinces();
          if (provRes.success && provRes.data) setProvinces(provRes.data);

          // Fetch current role info to decide if user already has both roles or has a pending submission
          const curRes = await role_service.get_current_role();
          if (curRes?.success) {
            setCurrentRoleInfo(curRes);
            const data = curRes.data || curRes;
            const ut = curRes.user_type || data?.user_type;
            setHasBoth(ut === 'both');

            // Determine if there is an existing pending application for the target role.
            // Policy: allow re-registration only after a rejection; block while pending.
            const ownerStatus = data?.owner?.verification_status;
            const contractorStatus = data?.contractor?.verification_status;
            const isOwnerPending = ownerStatus === 'pending';
            const isContractorPending = contractorStatus === 'pending';

            const isPendingForTarget = (targetRole === 'owner' && isOwnerPending) || (targetRole === 'contractor' && isContractorPending);
            setBlockedDueToPending(!!isPendingForTarget);
            setPendingInfo(isPendingForTarget ? (targetRole === 'owner' ? data.owner : data.contractor) : null);
          }
        }
      } finally {
        setLoading(false);
      }
    })();
  }, [targetRole]);

  // Load all cities list when contractor step 2 is active
  useEffect(() => {
    (async () => {
      if (targetRole !== 'contractor') return;
      if (formStep !== 2) return;
      if (permitCities.length > 0) return;
      try {
        const res = await auth_service.get_all_cities();
        if (res.success && Array.isArray(res.data)) {
          setPermitCities(res.data);
        }
      } catch {}
    })();
  }, [targetRole, formStep]);

  // Once provinces and existing data are available, prefill address
  useEffect(() => {
    (async () => {
      if (addressPrefilled) return;
      if (!existingData || !provinces?.length) return;
      try {
        if (targetRole === 'contractor') {
          await prefillContractorFromOwner(existingData);
        } else {
          await prefillOwnerFromContractor(existingData);
          const opc = formData.owner_address_province;
          const occ = formData.owner_address_city;
          if (opc) await loadOwnerCities(opc);
          if (occ) await loadOwnerBarangays(occ);
        }
        setAddressPrefilled(true);
      } catch {}
    })();
  }, [existingData, provinces, targetRole]);

  const updateForm = (patch: Record<string, any>) => {
    setFormData((prev: any) => ({ ...prev, ...patch }));
    // Any direct user edit clears the prefilled indicator for those fields
    setPrefilledFields((prev) => {
      const next = { ...prev };
      Object.keys(patch).forEach((k) => { next[k] = false; });
      return next;
    });
  };

  const updatePrefilled = (patch: Record<string, any>) => {
    setFormData((prev: any) => ({ ...prev, ...patch }));
    setPrefilledFields((prev) => {
      const next = { ...prev };
      Object.keys(patch).forEach((k) => { next[k] = true; });
      return next;
    });
  };

  // Years of experience computation (same logic as accountSetup/companyInfo)
  // Use shared helpers from utils/roleFormUtils

  // Custom Date Picker (matches Business Documents screen)
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
                <TouchableOpacity key={y} style={[styles.datePickerItem, selectedYear === y && styles.datePickerItemSelected]} onPress={() => setSelectedYear(y)}>
                  <Text style={[styles.datePickerItemText, selectedYear === y && styles.datePickerItemTextSelected]}>{y}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
          <View style={styles.datePickerColumn}>
            <Text style={styles.datePickerLabel}>Month</Text>
            <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
              {months.map((m) => (
                <TouchableOpacity key={m.value} style={[styles.datePickerItem, selectedMonth === m.value && styles.datePickerItemSelected]} onPress={() => setSelectedMonth(m.value)}>
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
                  <TouchableOpacity key={d} style={[styles.datePickerItem, selectedDay === d && styles.datePickerItemSelected, isDisabled && styles.datePickerItemDisabled]} onPress={() => !isDisabled && setSelectedDay(d)} disabled={isDisabled}>
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

  const loadCities = async (provinceCode: string) => {
    try {
      const res = await auth_service.get_cities_by_province(provinceCode);
      if (res?.success && res.data) {
        setCities(res.data);
        setBarangays([]);
        updateForm({ business_address_city: '', business_address_barangay: '' });
      }
    } catch {}
  };
  const loadBarangays = async (cityCode: string) => {
    try {
      const res = await auth_service.get_barangays_by_city(cityCode);
      if (res?.success && res.data) setBarangays(res.data);
    } catch {}
  };

  const loadOwnerCities = async (provinceCode: string) => {
    try {
      const res = await auth_service.get_cities_by_province(provinceCode);
      if (res?.success && res.data) {
        setCities(res.data);
        setBarangays([]);
        updateForm({ owner_address_city: '', owner_address_barangay: '' });
      }
    } catch {}
  };
  const loadOwnerBarangays = async (cityCode: string) => {
    try {
      const res = await auth_service.get_barangays_by_city(cityCode);
      if (res?.success && res.data) setBarangays(res.data);
    } catch {}
  };

  // Helpers to map address names to PSGC codes and prefill
  const normalizeName = (s: string) => {
    return (s || '')
      .toLowerCase()
      .replace(/\(.*?\)/g, '')
      .replace(/city of\s+/g, '')
      .replace(/province of\s+/g, '')
      .replace(/municipality of\s+/g, '')
      .replace(/\b(city|municipality|province)\b/g, '')
      .replace(/[^a-z0-9\s]/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  };
  const mapProvinceCodeByName = (nameOrCode: string): string | null => {
    // Try direct code match first
    const byCode = provinces.find((pr) => `${pr.code}` === `${nameOrCode}`);
    if (byCode) return byCode.code;

    const target = normalizeName(nameOrCode);
    let found = provinces.find((pr) => normalizeName(pr.name || '') === target);
    if (!found) {
      // Fuzzy contain match
      found = provinces.find((pr) => {
        const n = normalizeName(pr.name || '');
        return n.includes(target) || target.includes(n);
      });
    }
    if (!found) {
      // Handle common synonyms
      if (target.includes('metro manila') || target.includes('ncr') || target.includes('manila')) {
        const ncr = provinces.find((pr) => /ncr|national capital region|metro manila|city of manila/i.test(pr.name || ''));
        return ncr ? ncr.code : null;
      }
    }
    return found ? found.code : null;
  };
  const fetchCityCodeByName = async (provinceCode: string, cityNameOrCode: string): Promise<string | null> => {
    const res = await auth_service.get_cities_by_province(provinceCode);
    if (res?.success && res.data) {
      // Direct code match
      let c = (res.data as any[]).find((ci) => `${ci.code}` === `${cityNameOrCode}`);
      if (!c) {
        const target = normalizeName(cityNameOrCode);
        c = (res.data as any[]).find((ci) => normalizeName(ci.name || '') === target);
        if (!c) {
          c = (res.data as any[]).find((ci) => {
            const n = normalizeName(ci.name || '');
            return n.includes(target) || target.includes(n);
          });
        }
      }
      return c ? c.code : null;
    }
    return null;
  };
  const fetchBarangayCodeByName = async (cityCode: string, barangayNameOrCode: string): Promise<string | null> => {
    const res = await auth_service.get_barangays_by_city(cityCode);
    if (res?.success && res.data) {
      // Direct code match
      let b = (res.data as any[]).find((br) => `${br.code}` === `${barangayNameOrCode}`);
      if (!b) {
        const target = normalizeName(barangayNameOrCode);
        b = (res.data as any[]).find((br) => normalizeName(br.name || '') === target);
        if (!b) {
          b = (res.data as any[]).find((br) => {
            const n = normalizeName(br.name || '');
            return n.includes(target) || target.includes(n);
          });
        }
      }
      return b ? b.code : null;
    }
    return null;
  };

  const prefillContractorFromOwner = async (existing: any) => {
    // Prefer structured address fields when available; otherwise fall back to parsing a single address string.
    const po = existing?.property_owner || {};
    const userAddr = existing?.user || {};

    // Check for explicit structured fields on property_owner or user
    const streetField = po.address_street || po.owner_address_street || po.street || userAddr.address_street || userAddr.street;
    const provinceCodeField = po.address_province_code || po.owner_address_province || po.owner_address_province_code || po.province_code || userAddr.address_province_code || userAddr.province_code;
    const cityCodeField = po.address_city_code || po.owner_address_city || po.owner_address_city_code || po.city_code || userAddr.address_city_code || userAddr.city_code;
    const barangayCodeField = po.address_barangay_code || po.owner_address_barangay || po.owner_address_barangay_code || po.barangay_code || userAddr.address_barangay_code || userAddr.barangay_code;
    const postalField = po.postal_code || po.owner_address_postal || po.postal || userAddr.postal_code || userAddr.postal;

    const updates: any = {};
    if (streetField) updates.business_address_street = streetField;
    if (postalField) updates.business_address_postal = postalField;

    if (provinceCodeField) {
      // If we already have a code-like value, map directly; otherwise try to resolve by name
      const provCode = String(provinceCodeField).match(/^\d+$/) ? String(provinceCodeField) : mapProvinceCodeByName(String(provinceCodeField));
      if (provCode) {
        updates.business_address_province = provCode;
        await loadCities(provCode);
        // If we have a city code or name, resolve it
        if (cityCodeField) {
          const cityCode = String(cityCodeField).match(/^\d+$/) ? String(cityCodeField) : await fetchCityCodeByName(provCode, String(cityCodeField));
          if (cityCode) {
            updates.business_address_city = cityCode;
            await loadBarangays(cityCode);
            if (barangayCodeField) {
              const barangayCode = String(barangayCodeField).match(/^\d+$/) ? String(barangayCodeField) : await fetchBarangayCodeByName(cityCode, String(barangayCodeField));
              if (barangayCode) updates.business_address_barangay = barangayCode;
            }
          }
        }
      }
    }

    // If we didn't find structured fields, fall back to parsing an address string
    if (!updates.business_address_street || !updates.business_address_province) {
      const addr = po.address || po.address_full || po.address_string || userAddr.address || userAddr.address_full;
      if (addr && typeof addr === 'string') {
        const parts = addr.split(',').map((s) => s.trim());
        if (parts.length >= 4) {
          const street = parts[0];
          const barangayName = parts[1] || '';
          const cityName = parts[2] || '';
          const provinceName = parts[3] || '';
          const postal = parts[4] || '';
          const provinceCode = mapProvinceCodeByName(provinceName);
          updates.business_address_street = updates.business_address_street || street;
          updates.business_address_postal = updates.business_address_postal || postal;
          if (provinceCode) {
            updates.business_address_province = updates.business_address_province || provinceCode;
            await loadCities(provinceCode);
            const cityCode = await fetchCityCodeByName(provinceCode, cityName);
            if (cityCode) {
              updates.business_address_city = updates.business_address_city || cityCode;
              await loadBarangays(cityCode);
              const barangayCode = await fetchBarangayCodeByName(cityCode, barangayName);
              if (barangayCode) updates.business_address_barangay = updates.business_address_barangay || barangayCode;
            }
          }
        }
      }
    }

    if (Object.keys(updates).length) updatePrefilled(updates);
  };

  const prefillOwnerFromContractor = async (existing: any) => {
    // Expect contractor business_address: street, barangay, city, province [space] postal OR comma before postal
    const addr = existing?.contractor?.business_address;
    if (!addr || typeof addr !== 'string') return;
    const parts = addr.split(',').map((s) => s.trim());
    if (parts.length < 4) {
      // Fallback: try to extract postal code from the whole address string
      const mAll = addr.match(/(\b\d{4,}\b)/);
      if (mAll && mAll[1]) {
        const postalFallback = mAll[1].trim();
        updatePrefilled({ owner_address_postal: postalFallback });
      }
      return;
    }
    // province and postal may be in one part or separated
    let street = parts[0];
    let barangayName = parts[1];
    let cityName = parts[2];
    let provinceName = '';
    let postal = '';
    if (parts.length >= 5) {
      provinceName = parts[3];
      postal = parts[4];
    } else {
      const last = parts[3];
      const m = last.match(/^(.*)\s+(\d{4,})$/);
      if (m) { provinceName = m[1].trim(); postal = m[2].trim(); } else { provinceName = last; }
    }

    const provinceCode = mapProvinceCodeByName(provinceName);
    const updates: any = { owner_address_street: street };
    if (postal) updates.owner_address_postal = postal;
    if (provinceCode) {
      updates.owner_address_province = provinceCode;
      const citiesRes = await auth_service.get_cities_by_province(provinceCode);
      if (citiesRes?.success && citiesRes.data) setCities(citiesRes.data);
      const cityCode = await fetchCityCodeByName(provinceCode, cityName);
      if (cityCode) {
        updates.owner_address_city = cityCode;
        const bRes = await auth_service.get_barangays_by_city(cityCode);
        if (bRes?.success && bRes.data) setBarangays(bRes.data);
        const barangayCode = await fetchBarangayCodeByName(cityCode, barangayName);
        if (barangayCode) updates.owner_address_barangay = barangayCode;
      }
    }
    updatePrefilled(updates);
  };

  const pickImage = async (field: string) => {
    try {
      // Web fallback: prompt for an image URL if running on web
      if (Platform.OS === 'web') {
        const url = (window as any)?.prompt?.('Enter image URL (or data URI)');
        if (url) {
          const fileName = url.split('/').pop() || 'uploaded-image';
          updateForm({ [field]: url, [`${field}_name`]: fileName });
        }
        return;
      }

      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert('Permission required', 'Please allow photo library access.');
        return;
      }
      const result = await ImagePicker.launchImageLibraryAsync({ mediaTypes: 'Images', quality: 0.8 });
      console.log('ImagePicker result:', result);
      if (!result.canceled && result.assets?.length) {
        const asset = result.assets[0];
        const uri = asset.uri;
        const fileName = (asset.fileName || asset.filename || uri?.split('/')?.pop()) ?? 'Image selected';
        updateForm({ [field]: uri, [`${field}_name`]: fileName });
      } else {
        console.log('ImagePicker canceled or no assets');
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

  const submitStep = async () => {
    try {
      setSubmitting(true);
      if (targetRole === 'contractor') {
        if (formStep === 1) {
          // ...existing contractor step 1 logic...
          const { validateContractorStep1, buildContractorStep1Payload } = await import('../../utils/roleFormBuilders');
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
            const backendErrors = Array.isArray(res?.data?.errors) ? res.data.errors.join('\n') : '';
            const msg = backendErrors || res?.message || 'Please check required fields or login again.';
            Alert.alert('Error', msg);
          }
        } else if (formStep === 2) {
          // ...existing contractor step 2 logic...
          const { buildContractorStep2FormData } = await import('../../utils/roleFormBuilders');
          const fd = buildContractorStep2FormData(formData);
          const res = await role_service.add_contractor_step2(fd);
          if (res?.success) {
            const saved = res?.data?.saved;
            if (saved && saved.dti_sec_registration_photo) {
              updateForm({ dti_sec_registration_photo_server: saved.dti_sec_registration_photo });
            }
            setFormStep(3); // Only advance to step 3, do not submit finalization here
          } else {
            Alert.alert('Error', res?.message || 'Upload failed');
          }
        } else if (formStep === 3) {
          // Build business address string required by backend final step
          const { buildContractorFinalBody } = await import('../../utils/roleFormBuilders');
          const body = buildContractorFinalBody(formData, provinces, cities, barangays);
          const res = await role_service.add_contractor_final(body);
          if (res?.success) {
            Alert.alert('Application Submitted', 'Your application has been received and is pending administrative review and approval.', [
              { text: 'OK', onPress: handleComplete }
            ]);
          } else {
            Alert.alert('Error', res?.message || 'Finalization failed');
          }
        }
      } else if (targetRole === 'owner') {
        if (formStep === 1) {
          // Owner step 1 validation
          const errors: string[] = [];
          // Username removed from form; no longer required
          // Email removed from form validation
          if (!formData.first_name?.trim()) errors.push('First name is required');
          if (!formData.last_name?.trim()) errors.push('Last name is required');
          if (!formData.date_of_birth) errors.push('Date of birth is required');
          if (!formData.phone_number?.trim()) errors.push('Phone number is required');
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
          const res = await role_service.add_owner_step1({});
          if (res?.success) {
            setFormStep(2);
          } else {
            // Show backend errors immediately
            let backendErrors = '';
            if (Array.isArray(res?.data?.errors)) backendErrors = res.data.errors.join('\n');
            else if (Array.isArray(res?.errors)) backendErrors = res.errors.join('\n');
            else if (typeof res?.message === 'string') backendErrors = res.message;
            else backendErrors = 'Please check required fields or login again.';
            Alert.alert('Error', backendErrors);
            setSubmitting(false);
            return;
          }
        } else if (formStep === 2) {
          // Owner step 2 validation and submit
          // Build owner address as a single string for backend
          const provinceName = provinces.find(p => `${p.code}` === `${formData.owner_address_province}`)?.name || '';
          const cityName = cities.find(c => `${c.code}` === `${formData.owner_address_city}`)?.name || '';
          const barangayName = barangays.find(b => `${b.code}` === `${formData.owner_address_barangay}`)?.name || '';
          const address = [
            formData.owner_address_street || '',
            barangayName || '',
            cityName || '',
            provinceName || ''
          ].filter(Boolean).join(', ') + (formData.owner_address_postal ? ` ${formData.owner_address_postal}` : '');
          // Compute age
          const age = formData.date_of_birth ? computeYears(formData.date_of_birth) : undefined;
           // Collect saved document paths from step 2
          const savedDocs: any = {};
          if (formData.owner_valid_id_id) savedDocs.valid_id_id = formData.owner_valid_id_id;
          // Fallback to local file path if server path is missing
          if (formData.owner_valid_id_photo_server) {
            savedDocs.valid_id_photo = formData.owner_valid_id_photo_server;
          } else if (formData.valid_id_photo) {
            savedDocs.valid_id_photo = formData.valid_id_photo;
          }
          if (formData.owner_valid_id_back_photo_server) {
            savedDocs.valid_id_back_photo = formData.owner_valid_id_back_photo_server;
          } else if (formData.valid_id_back_photo) {
            savedDocs.valid_id_back_photo = formData.valid_id_back_photo;
          }
          if (formData.owner_police_clearance_server) {
            savedDocs.police_clearance = formData.owner_police_clearance_server;
          } else if (formData.police_clearance) {
            savedDocs.police_clearance = formData.police_clearance;
          }
          // Only advance to step 3, do not submit finalization here
          setFormStep(3);
        } else if (formStep === 3) {
          // ...existing owner step 3 logic (finalize)...
          const provinceName = provinces.find(p => `${p.code}` === `${formData.owner_address_province}`)?.name || '';
          const cityName = cities.find(c => `${c.code}` === `${formData.owner_address_city}`)?.name || '';
          const barangayName = barangays.find(b => `${b.code}` === `${formData.owner_address_barangay}`)?.name || '';
          const address = [
            formData.owner_address_street || '',
            barangayName || '',
            cityName || '',
            provinceName || ''
          ].filter(Boolean).join(', ') + (formData.owner_address_postal ? ` ${formData.owner_address_postal}` : '');
          const age = formData.date_of_birth ? computeYears(formData.date_of_birth) : undefined;
          const savedDocs: any = {};
          if (formData.owner_valid_id_id) savedDocs.valid_id_id = formData.owner_valid_id_id;
          if (formData.owner_valid_id_photo_server) {
            savedDocs.valid_id_photo = formData.owner_valid_id_photo_server;
          } else if (formData.valid_id_photo) {
            savedDocs.valid_id_photo = formData.valid_id_photo;
          }
          if (formData.owner_valid_id_back_photo_server) {
            savedDocs.valid_id_back_photo = formData.owner_valid_id_back_photo_server;
          } else if (formData.valid_id_back_photo) {
            savedDocs.valid_id_back_photo = formData.valid_id_back_photo;
          }
          if (formData.owner_police_clearance_server) {
            savedDocs.police_clearance = formData.owner_police_clearance_server;
          } else if (formData.police_clearance) {
            savedDocs.police_clearance = formData.police_clearance;
          }
          const body: any = {
            owner_step1_data: {
              first_name: formData.first_name,
              middle_name: formData.middle_name,
              last_name: formData.last_name,
              occupation_id: formData.occupation_id,
              occupation_other: formData.occupation_other_text,
              date_of_birth: formData.date_of_birth,
              phone_number: formData.phone_number,
              address,
              age,
            },
            // switch_step1_data omitted (email removed)
            switch_step2_data: Object.keys(savedDocs).length ? { saved: savedDocs } : undefined,
            first_name: formData.first_name,
            middle_name: formData.middle_name,
            last_name: formData.last_name,
            date_of_birth: formData.date_of_birth,
            phone_number: formData.phone_number,
            occupation_id: formData.occupation_id,
            occupation_other: formData.occupation_other_text,
            address,
          };
          const res = await role_service.add_owner_final(body);
          if (res?.success) {
            Alert.alert(
              'Application Submitted',
              'Your application has been received and is pending administrative review and approval.',
              [
                {
                  text: 'OK',
                  onPress: handleComplete,
                },
              ]
            );
          } else {
            Alert.alert('Error', res?.message || 'Finalization failed');
          }
        }
      }
    } finally {
      setSubmitting(false);
    }
  };

  const switchToTargetRole = async () => {
    // Switching role is not allowed until admin approval
    Alert.alert('Not Allowed', 'You cannot switch to this role until your application is approved by an administrator.');
    setSubmitting(false);
  };

  const handlePrimaryPress = () => {
    if (hasBoth) return switchToTargetRole();
    if (blockedDueToPending) {
      Alert.alert('Application Pending', 'You have already submitted an application for this role. Please wait for administrative review or resubmit after a rejection.');
      return;
    }
    if (formStep < 3) return submitStep();
    if (!agreedConfirm) { Alert.alert('Confirm', 'Please confirm the information is correct.'); return; }
    setShowSubmitConfirm(true);
  };

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
            <Text style={styles.loadingText}>Loading form...</Text>
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
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
          {/* Section Title */}
          <Text style={styles.sectionTitle}>Add {targetRole === 'contractor' ? 'Contractor' : 'Property Owner'} Role</Text>
            {hasBoth ? (
              <View>
                <Text style={styles.inputLabel}>You already have both roles.</Text>
                <Text style={styles.inputLabel}>Current role: {currentRoleInfo?.current_role || 'Unknown'}</Text>
                <Text style={[styles.inputLabel, { marginTop: 12 }]}>Use the button below to switch.</Text>
              </View>
            ) : null}

            {blockedDueToPending ? (
              <View>
                <Text style={styles.inputLabel}>You have an application pending for this role.</Text>
                <Text style={styles.inputLabel}>Status: {pendingInfo?.verification_status || 'pending'}</Text>
                {pendingInfo?.rejection_reason ? <Text style={[styles.inputLabel, { marginTop: 8 }]}>Reason: {pendingInfo.rejection_reason}</Text> : null}
                <Text style={[styles.inputLabel, { marginTop: 12 }]}>You cannot submit a new application until this is reviewed.</Text>
              </View>
            ) : null}

            {!hasBoth && targetRole === 'contractor' && formStep === 1 && (
              <View>
                <Text style={styles.sectionTitle}>Company Information</Text>
                <Text style={styles.inputLabel}>Company Name *</Text>
                <TextInput style={styles.input} value={formData.company_name || ''} onChangeText={(t) => updateForm({ company_name: t })} placeholder="Company Name *" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Company Phone *</Text>
                <TextInput style={[styles.input, prefilledFields.company_phone && styles.prefilledInput]} value={formData.company_phone || ''} onChangeText={(t) => updateForm({ company_phone: t })} keyboardType="phone-pad" placeholder="Company Phone *" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Years of Experience *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowExperienceDateModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.experience_start_date && styles.placeholderText]}>
                      {formData.experience_start_date
                        ? `${formatExperience(formData.experience_start_date)} (selected ${formData.experience_start_date})`
                        : 'Years of Experience *'}
                    </Text>
                    <Ionicons name="calendar" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Contractor Type *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowContractorTypeModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.contractor_type_id && styles.placeholderText]}>
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
                        style={styles.input}
                        value={formData.contractor_type_other_text || ''}
                        onChangeText={(t) => updateForm({ contractor_type_other_text: t })}
                        placeholder="e.g., Solar Installer"
                        placeholderTextColor="#999"
                      />
                    </>
                  );
                })()}
                <Text style={styles.inputLabel}>Services Offered *</Text>
                <TextInput style={styles.input} value={formData.services_offered || ''} onChangeText={(t) => updateForm({ services_offered: t })} placeholder="Services Offered" placeholderTextColor="#999" />
                <Text style={styles.sectionTitle}>Business Address</Text>
                <Text style={styles.inputLabel}>Business Address Street *</Text>
                <TextInput style={[styles.input, prefilledFields.business_address_street && styles.prefilledInput]} value={formData.business_address_street || ''} onChangeText={(t) => updateForm({ business_address_street: t })} placeholder="Street" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Province *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowProvinceModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.business_address_province && styles.prefilledDropdownText, !formData.business_address_province && styles.placeholderText]}>
                      {provinces.find(p => p.code === formData.business_address_province)?.name || 'Select Province *'}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>City/Municipality *</Text>
                <TouchableOpacity style={[styles.input, !formData.business_address_province && styles.inputDisabled]} onPress={() => formData.business_address_province && setShowCityModal(true)} disabled={!formData.business_address_province}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.business_address_city && styles.prefilledDropdownText, !formData.business_address_city && styles.placeholderText]}>
                      {cities.find(c => c.code === formData.business_address_city)?.name || (formData.business_address_province ? 'Select City/Municipality *' : 'Select Province First')}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Barangay *</Text>
                <TouchableOpacity style={[styles.input, !formData.business_address_city && styles.inputDisabled]} onPress={() => formData.business_address_city && setShowBarangayModal(true)} disabled={!formData.business_address_city}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.business_address_barangay && styles.prefilledDropdownText, !formData.business_address_barangay && styles.placeholderText]}>
                      {barangays.find(b => b.code === formData.business_address_barangay)?.name || (formData.business_address_city ? 'Select Barangay *' : 'Select City First')}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Postal Code *</Text>
                <TextInput style={[styles.input, prefilledFields.business_address_postal && styles.prefilledInput]} value={formData.business_address_postal || ''} onChangeText={(t) => updateForm({ business_address_postal: t })} keyboardType="number-pad" placeholder="Postal Code" placeholderTextColor="#999" />
                <Text style={styles.sectionTitle}>Authorized Representative</Text>
                <Text style={styles.inputLabel}>First Name *</Text>
                <TextInput style={[styles.input, prefilledFields.authorized_rep_fname ? styles.prefilledInput : null]} value={formData.authorized_rep_fname || ''} onChangeText={(t) => updateForm({ authorized_rep_fname: t })} placeholder="First name" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Middle Name (Optional)</Text>
                <TextInput style={[styles.input, prefilledFields.authorized_rep_mname ? styles.prefilledInput : null]} value={formData.authorized_rep_mname || ''} onChangeText={(t) => updateForm({ authorized_rep_mname: t })} placeholder="Middle name (Optional)" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Last Name *</Text>
                <TextInput style={[styles.input, prefilledFields.authorized_rep_lname ? styles.prefilledInput : null]} value={formData.authorized_rep_lname || ''} onChangeText={(t) => updateForm({ authorized_rep_lname: t })} placeholder="Last name" placeholderTextColor="#999" />
                <Text style={styles.sectionTitle}>Optional Information</Text>
                <Text style={styles.inputLabel}>Website</Text>
                <TextInput style={styles.input} value={formData.company_website || ''} onChangeText={(t) => updateForm({ company_website: t })} placeholder="Website (Optional)" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Social Media</Text>
                <TextInput style={styles.input} value={formData.company_social_media || ''} onChangeText={(t) => updateForm({ company_social_media: t })} placeholder="Social Media (Optional)" placeholderTextColor="#999" />
              </View>
            )}

            {!hasBoth && targetRole === 'contractor' && formStep === 2 && (
              <View>
                <Text style={styles.inputLabel}>PICAB Number *</Text>
                <TextInput style={styles.input} value={formData.picab_number || ''} onChangeText={(t) => updateForm({ picab_number: t })} placeholder="PICAB Number" placeholderTextColor="#999" />
                    <Text style={styles.inputLabel}>PICAB Category *</Text>
                    <TouchableOpacity style={styles.input} onPress={() => setShowPicabCategoryModal(true)}>
                      <View style={styles.dropdownInputWrapper}>
                        <Text style={[styles.dropdownInputText, !formData.picab_category && styles.placeholderText]}>
                          {formData.picab_category || 'Select PICAB Category *'}
                        </Text>
                        <Ionicons name="chevron-down" size={20} color="#666" />
                      </View>
                    </TouchableOpacity>
                <Text style={styles.inputLabel}>PICAB Expiration Date *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowPicabDatePicker(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.picab_expiration_date && styles.placeholderText]}>
                      {formData.picab_expiration_date ? formatDateForDisplay(formData.picab_expiration_date) : 'PICAB Expiration Date *'}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Business Permit Number *</Text>
                <TextInput style={styles.input} value={formData.business_permit_number || ''} onChangeText={(t) => updateForm({ business_permit_number: t })} placeholder="Business Permit Number" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Business Permit City *</Text>
                <TouchableOpacity style={styles.inputSelector} onPress={() => setShowPermitCityModal(true)}>
                  <Text style={[styles.selectorText, !formData.business_permit_city && styles.selectorPlaceholder]}>
                    {formData.business_permit_city || 'Select City/Municipality'}
                  </Text>
                  <Ionicons name="chevron-down" size={18} color="#666" />
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Business Permit Expiration *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowPermitDatePicker(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.business_permit_expiration && styles.placeholderText]}>
                      {formData.business_permit_expiration ? formatDateForDisplay(formData.business_permit_expiration) : 'Business Permit Expiration *'}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>TIN Business Reg Number *</Text>
                <TextInput style={styles.input} value={formData.tin_business_reg_number || ''} onChangeText={(t) => updateForm({ tin_business_reg_number: t })} placeholder="TIN Business Reg Number" placeholderTextColor="#999" />
                    <Text style={styles.inputLabel}>DTI/SEC Registration Photo *</Text>
                    <TouchableOpacity style={styles.uploadButton} onPress={() => pickImage('dti_sec_registration_photo')}>
                      {formData.dti_sec_registration_photo || formData.dti_sec_registration_photo_server ? (
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

            {/* Always show step 3 finalization for both roles, but only render the correct preview for the current targetRole */}
            {!hasBoth && formStep === 3 && (
              <View>
                <Text style={styles.sectionTitle}>Review & Confirm</Text>
                {targetRole === 'contractor' ? (
                  <>
                    <View style={styles.previewCard}>
                      <Text style={styles.previewHeader}>Company Information</Text>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Company Name</Text><Text style={styles.previewValue}>{formData.company_name || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Company Phone</Text><Text style={styles.previewValue}>{formData.company_phone || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Experience</Text><Text style={styles.previewValue}>{formData.experience_start_date ? formatExperience(formData.experience_start_date) : '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Contractor Type</Text><Text style={styles.previewValue}>{(() => { const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`); return sel?.name || '—'; })()}</Text></View>
                      {(() => { const sel = (dropdowns.contractor_types || []).find((t: any) => `${t.id}` === `${formData.contractor_type_id}`); const isOther = (sel?.name || '').toLowerCase().includes('other'); return isOther ? (<View style={styles.previewRow}><Text style={styles.previewLabel}>Other Type</Text><Text style={styles.previewValue}>{formData.contractor_type_other_text || '—'}</Text></View>) : null; })()}
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Services Offered</Text><Text style={styles.previewValue}>{formData.services_offered || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Authorized Representative</Text><Text style={styles.previewValue}>{(formData.authorized_rep_fname || '') + (formData.authorized_rep_mname ? ' ' + formData.authorized_rep_mname : '') + (formData.authorized_rep_lname ? ' ' + formData.authorized_rep_lname : '') || '—'}</Text></View>
                    </View>

                    <View style={styles.previewCard}>
                      <Text style={styles.previewHeader}>Business Address</Text>
                      {(() => {
                        const provinceName = provinces.find(p => `${p.code}` === `${formData.business_address_province}`)?.name || '';
                        const cityName = cities.find(c => `${c.code}` === `${formData.business_address_city}`)?.name || '';
                        const barangayName = barangays.find(b => `${b.code}` === `${formData.business_address_barangay}`)?.name || '';
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
                    {/* Account Info preview: email removed */}
                    <View style={styles.previewCard}>
                      <Text style={styles.previewHeader}>Personal Details</Text>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>First Name</Text><Text style={styles.previewValue}>{formData.first_name || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Middle Name</Text><Text style={styles.previewValue}>{formData.middle_name || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Last Name</Text><Text style={styles.previewValue}>{formData.last_name || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Date of Birth</Text><Text style={styles.previewValue}>{formData.date_of_birth ? formatDateForDisplay(formData.date_of_birth) : '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Phone Number</Text><Text style={styles.previewValue}>{formData.phone_number || '—'}</Text></View>
                      <View style={styles.previewRow}><Text style={styles.previewLabel}>Occupation</Text><Text style={styles.previewValue}>{(() => { const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`); return occ?.name || '—'; })()}</Text></View>
                      {(() => { const occ = (dropdowns.occupations || []).find((o: any) => `${o.id}` === `${formData.occupation_id}`); const isOther = ((occ?.name || '').toLowerCase()).includes('other'); return isOther ? (<View style={styles.previewRow}><Text style={styles.previewLabel}>Occupation Other</Text><Text style={styles.previewValue}>{formData.occupation_other_text || '—'}</Text></View>) : null; })()}
                    </View>
                    <View style={styles.previewCard}>
                      <Text style={styles.previewHeader}>Address</Text>
                      {(() => {
                        const provinceName = provinces.find(p => `${p.code}` === `${formData.owner_address_province}`)?.name || '';
                        const cityName = cities.find(c => `${c.code}` === `${formData.owner_address_city}`)?.name || '';
                        const barangayName = barangays.find(b => `${b.code}` === `${formData.owner_address_barangay}`)?.name || '';
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
                <View style={styles.confirmRow}>
                  <TouchableOpacity onPress={() => setAgreedConfirm(!agreedConfirm)} style={styles.confirmCheckbox}>
                    <Ionicons name={agreedConfirm ? 'checkmark-circle' : 'checkmark-circle-outline'} size={24} color={agreedConfirm ? '#2ECC71' : '#666'} />
                  </TouchableOpacity>
                  <Text style={styles.confirmText}>I confirm the details above are accurate and agree to submit.</Text>
                </View>
              </View>
            )}

            {!hasBoth && targetRole === 'owner' && formStep === 1 && (
              <View>
                <Text style={[styles.inputLabel, { marginTop: 12 }]}>First Name *</Text>
                <TextInput style={[styles.input, prefilledFields.first_name && styles.prefilledInput]} value={formData.first_name || ''} onChangeText={(t) => updateForm({ first_name: t })} placeholder="First Name *" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Middle Name</Text>
                <TextInput style={[styles.input, prefilledFields.middle_name && styles.prefilledInput]} value={formData.middle_name || ''} onChangeText={(t) => updateForm({ middle_name: t })} placeholder="Middle Name (Optional)" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Last Name *</Text>
                <TextInput style={[styles.input, prefilledFields.last_name && styles.prefilledInput]} value={formData.last_name || ''} onChangeText={(t) => updateForm({ last_name: t })} placeholder="Last Name *" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Date of Birth *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowDobModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.date_of_birth && styles.prefilledDropdownText, !formData.date_of_birth && styles.placeholderText]}>
                      {formData.date_of_birth || 'Select Date of Birth *'}
                    </Text>
                    <Ionicons name="calendar" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Phone Number *</Text>
                <TextInput style={[styles.input, prefilledFields.phone_number && styles.prefilledInput]} value={formData.phone_number || ''} onChangeText={(t) => updateForm({ phone_number: t })} keyboardType="phone-pad" placeholder="Phone Number *" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Occupation *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowOccupationModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.occupation_id && styles.placeholderText]}>
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
                <TextInput style={[styles.input, prefilledFields.owner_address_street && styles.prefilledInput]} value={formData.owner_address_street || ''} onChangeText={(t) => updateForm({ owner_address_street: t })} placeholder="Street" placeholderTextColor="#999" />
                <Text style={styles.inputLabel}>Province *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowOwnerProvinceModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.owner_address_province && styles.prefilledDropdownText, !formData.owner_address_province && styles.placeholderText]}>
                      {provinces.find(p => p.code === formData.owner_address_province)?.name || 'Select Province *'}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>City/Municipality *</Text>
                <TouchableOpacity style={[styles.input, !formData.owner_address_province && styles.inputDisabled]} onPress={() => formData.owner_address_province && setShowOwnerCityModal(true)} disabled={!formData.owner_address_province}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.owner_address_city && styles.prefilledDropdownText, !formData.owner_address_city && styles.placeholderText]}>
                      {cities.find(c => c.code === formData.owner_address_city)?.name || (formData.owner_address_province ? 'Select City/Municipality *' : 'Select Province First')}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Barangay *</Text>
                <TouchableOpacity style={[styles.input, !formData.owner_address_city && styles.inputDisabled]} onPress={() => formData.owner_address_city && setShowOwnerBarangayModal(true)} disabled={!formData.owner_address_city}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, prefilledFields.owner_address_barangay && styles.prefilledDropdownText, !formData.owner_address_barangay && styles.placeholderText]}>
                      {barangays.find(b => b.code === formData.owner_address_barangay)?.name || (formData.owner_address_city ? 'Select Barangay *' : 'Select City First')}
                    </Text>
                    <Ionicons name="chevron-down" size={20} color="#666" />
                  </View>
                </TouchableOpacity>
                <Text style={styles.inputLabel}>Postal Code *</Text>
                <TextInput style={[styles.input, prefilledFields.owner_address_postal && styles.prefilledInput]} value={formData.owner_address_postal || ''} onChangeText={(t) => updateForm({ owner_address_postal: t })} keyboardType="number-pad" placeholder="Postal Code" placeholderTextColor="#999" />
              </View>
            )}

            {!hasBoth && targetRole === 'owner' && formStep === 2 && (
              <View>
                <Text style={styles.inputLabel}>Valid ID *</Text>
                <TouchableOpacity style={styles.input} onPress={() => setShowValidIdModal(true)}>
                  <View style={styles.dropdownInputWrapper}>
                    <Text style={[styles.dropdownInputText, !formData.valid_id_id && styles.placeholderText]}>
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
                  {formData.police_clearance ? (
                    <View style={styles.uploadedFile}>
                      <Image source={{ uri: getDocImageUrl(formData.police_clearance) }} style={styles.thumbnailImage} />
                      <Text style={styles.fileName} numberOfLines={1}>{formData.police_clearance_name || 'Image selected'}</Text>
                      <TouchableOpacity onPress={() => updateForm({ police_clearance: null, police_clearance_name: null })}>
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
            <TouchableOpacity style={styles.backButton} onPress={handleBack}>
              <Text style={styles.backButtonText}>Back</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.nextButton, (submitting || blockedDueToPending) && styles.nextButtonDisabled]}
              onPress={handlePrimaryPress}
              disabled={submitting || blockedDueToPending}
            >
              {submitting ? (
                <ActivityIndicator color="#FFFFFF" />
              ) : (
                <Text style={[styles.nextButtonText, (submitting || blockedDueToPending) && styles.nextButtonTextDisabled]}>
                  {blockedDueToPending ? 'Pending' : (hasBoth ? `Switch to ${targetRole === 'contractor' ? 'Contractor' : 'Owner'}` : (formStep < 3 ? 'Next' : 'Submit'))}
                </Text>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>

      {/* Contractor Type Selector */}
      <Modal visible={showContractorTypeModal} animationType="slide" transparent onRequestClose={() => setShowContractorTypeModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Contractor Type</Text>
              <TouchableOpacity onPress={() => setShowContractorTypeModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={[...dropdowns.contractor_types].sort((a, b) => {
                const an = (a.name || '').toLowerCase();
                const bn = (b.name || '').toLowerCase();
                if (an === 'others') return 1; if (bn === 'others') return -1; return an.localeCompare(bn);
              })}
              keyExtractor={(item) => `${item.id}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ contractor_type_id: `${item.id}` }); setShowContractorTypeModal(false); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* PICAB Category Selector */}
      <Modal visible={showPicabCategoryModal} animationType="slide" transparent onRequestClose={() => setShowPicabCategoryModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select PICAB Category</Text>
              <TouchableOpacity onPress={() => setShowPicabCategoryModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={dropdowns.picab_categories}
              keyExtractor={(item, index) => `${item}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ picab_category: item }); setShowPicabCategoryModal(false); }}>
                  <Text style={styles.modalItemText}>{item}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Province Selector */}
      <Modal visible={showProvinceModal} animationType="slide" transparent onRequestClose={() => setShowProvinceModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Province</Text>
              <TouchableOpacity onPress={() => setShowProvinceModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={provinces}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ business_address_province: item.code }); setShowProvinceModal(false); loadCities(item.code); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* City Selector */}
      <Modal visible={showCityModal} animationType="slide" transparent onRequestClose={() => setShowCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select City/Municipality</Text>
              <TouchableOpacity onPress={() => setShowCityModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={cities}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ business_address_city: item.code }); setShowCityModal(false); loadBarangays(item.code); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Business Permit City Selector (global, searchable) */}
      <Modal visible={showPermitCityModal} animationType="slide" transparent onRequestClose={() => setShowPermitCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Business Permit City</Text>
              <TouchableOpacity onPress={() => setShowPermitCityModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
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
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ business_permit_city: item.name }); setShowPermitCityModal(false); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Barangay Selector */}
      <Modal visible={showBarangayModal} animationType="slide" transparent onRequestClose={() => setShowBarangayModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity onPress={() => setShowBarangayModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={barangays}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ business_address_barangay: item.code }); setShowBarangayModal(false); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Valid ID Selector */}
      <Modal visible={showValidIdModal} animationType="slide" transparent onRequestClose={() => setShowValidIdModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Valid ID</Text>
              <TouchableOpacity onPress={() => setShowValidIdModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={dropdowns.valid_ids}
              keyExtractor={(item) => `${item.id}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ valid_id_id: `${item.id}` }); setShowValidIdModal(false); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Date of Birth Picker */}
      <Modal visible={showDobModal} animationType="slide" transparent onRequestClose={() => setShowDobModal(false)}>
        <View style={styles.datePickerModalOverlay}>
          <View style={styles.datePickerModalContainer}>
            <View style={styles.datePickerModalHeader}>
              <Text style={styles.datePickerModalTitle}>Select Date of Birth</Text>
              <TouchableOpacity onPress={() => setShowDobModal(false)} style={styles.datePickerModalCloseButton}>
                <Text style={styles.datePickerModalCloseText}>Done</Text>
              </TouchableOpacity>
            </View>
            <View style={{ paddingHorizontal: 20 }}>
              <PlatformDatePicker
                value={formData.date_of_birth}
                maximumDate={new Date()}
                onChange={(val) => {
                  updateForm({ date_of_birth: val });
                  setShowDobModal(false);
                }}
              />
            </View>
          </View>
        </View>
      </Modal>

      {/* Occupation Selector */}
      <Modal visible={showOccupationModal} animationType="slide" transparent onRequestClose={() => setShowOccupationModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Occupation</Text>
              <TouchableOpacity onPress={() => setShowOccupationModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
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

      {/* Experience Start Date Picker */}
      <Modal visible={showExperienceDateModal} animationType="slide" transparent onRequestClose={() => setShowExperienceDateModal(false)}>
        <View style={styles.datePickerModalOverlay}>
          <View style={styles.datePickerModalContainer}>
            <View style={styles.datePickerModalHeader}>
              <Text style={styles.datePickerModalTitle}>Select Company Start Date</Text>
              <TouchableOpacity onPress={() => setShowExperienceDateModal(false)} style={styles.datePickerModalCloseButton}>
                <Text style={styles.datePickerModalCloseText}>Done</Text>
              </TouchableOpacity>
            </View>

            <View style={{ paddingHorizontal: 20 }}>
              <PlatformDatePicker
                value={formData.experience_start_date}
                maximumDate={new Date()}
                onChange={(val) => {
                  updateForm({ experience_start_date: val });
                  setShowExperienceDateModal(false);
                }}
              />
            </View>
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

      {/* Owner Province Selector */}
      <Modal visible={showOwnerProvinceModal} animationType="slide" transparent onRequestClose={() => setShowOwnerProvinceModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Province</Text>
              <TouchableOpacity onPress={() => setShowOwnerProvinceModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={provinces}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ owner_address_province: item.code, owner_address_city: '', owner_address_barangay: '' }); setShowOwnerProvinceModal(false); loadOwnerCities(item.code); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Owner City Selector */}
      <Modal visible={showOwnerCityModal} animationType="slide" transparent onRequestClose={() => setShowOwnerCityModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select City/Municipality</Text>
              <TouchableOpacity onPress={() => setShowOwnerCityModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={cities}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ owner_address_city: item.code, owner_address_barangay: '' }); setShowOwnerCityModal(false); loadOwnerBarangays(item.code); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Owner Barangay Selector */}
      <Modal visible={showOwnerBarangayModal} animationType="slide" transparent onRequestClose={() => setShowOwnerBarangayModal(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity onPress={() => setShowOwnerBarangayModal(false)} style={styles.closeButton}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
            </View>
            <FlatList
              data={barangays}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.modalItem} onPress={() => { updateForm({ owner_address_barangay: item.code }); setShowOwnerBarangayModal(false); }}>
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Submit Confirmation */}
      <Modal visible={showSubmitConfirm} animationType="fade" transparent onRequestClose={() => setShowSubmitConfirm(false)}>
        <View style={styles.confirmModalOverlay}>
          <View style={styles.confirmModalContainer}>
            <Text style={styles.confirmModalTitle}>Confirm Submission</Text>
            <Text style={styles.confirmModalText}>Please confirm that all details are correct. This will finalize adding your {targetRole === 'contractor' ? 'Contractor' : 'Owner'} role.</Text>
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
  inputSelector: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 },
  selectorText: { fontSize: 16, color: '#333333', flex: 1 },
  selectorPlaceholder: { color: '#999999' },
  dropdownInputWrapper: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  dropdownInputText: { fontSize: 16, color: '#333333', flex: 1 },
  placeholderText: { color: '#999999' },
  prefilledInput: { color: '#EC7E00' },
  prefilledDropdownText: { color: '#EC7E00' },
  fileButton: { backgroundColor: '#EC7E00', borderRadius: 12, paddingVertical: 12, alignItems: 'center', marginTop: 6 },
  fileButtonText: { color: '#FFFFFF', fontSize: 16, fontWeight: '600' },
  uploadButton: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#DDDDDD', borderRadius: 8, borderStyle: 'dashed', padding: 20, alignItems: 'center' },
  uploadPlaceholder: { alignItems: 'center' },
  uploadText: { fontSize: 14, color: '#333333', marginTop: 8 },
  uploadHint: { fontSize: 11, color: '#999999', marginTop: 4 },
  uploadedFile: { flexDirection: 'row', alignItems: 'center', width: '100%' },
  thumbnailImage: { width: 50, height: 50, borderRadius: 6, marginRight: 12 },
  fileName: { flex: 1, fontSize: 14, color: '#333333' },
  imagePreviewWrap: { alignItems: 'center', marginTop: 10, marginBottom: 6 },
  imagePreview: { width: 200, height: 120, borderRadius: 12, borderWidth: 2, borderColor: '#D1D5DB', backgroundColor: '#F5F5F5' },
  buttonContainer: { flexDirection: 'row', gap: 15, marginTop: 24, paddingHorizontal: 5, paddingBottom: 20 },
  backButton: { flex: 1, backgroundColor: '#E8E8E8', borderRadius: 12, paddingVertical: 18, alignItems: 'center', marginRight: 8 },
  backButtonText: { color: '#333333', fontSize: 18, fontWeight: '600' },
  nextButton: { flex: 1, backgroundColor: '#EC7E00', borderRadius: 12, paddingVertical: 18, alignItems: 'center', marginLeft: 8, shadowColor: '#EC7E00', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 8 },
  nextButtonText: { color: '#FFFFFF', fontSize: 18, fontWeight: '600' },
  nextButtonDisabled: { backgroundColor: '#CCCCCC', shadowColor: '#CCCCCC', shadowOpacity: 0, elevation: 0 },
  nextButtonTextDisabled: { color: '#999999' },
  // Review cards
  previewCard: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, padding: 16, marginBottom: 14 },
  previewHeader: { fontSize: 16, fontWeight: '700', color: '#333333', marginBottom: 10 },
  previewRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 6 },
  previewLabel: { fontSize: 13, color: '#666666', flex: 1 },
  previewValue: { fontSize: 13, color: '#1A1A1A', flex: 1, textAlign: 'right' },
  previewImage: { width: 70, height: 70, borderRadius: 8, borderWidth: 1, borderColor: '#E5E5E5', marginLeft: 12 },
  // Confirmation row
  confirmRow: { flexDirection: 'row', alignItems: 'center', marginTop: 12 },
  confirmCheckbox: { marginRight: 8 },
  confirmText: { flex: 1, fontSize: 13, color: '#333333' },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', alignItems: 'center' },
  modalContainer: { backgroundColor: '#FFFFFF', borderRadius: 16, width: '90%', maxHeight: '80%', paddingVertical: 20 },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, marginBottom: 15 },
  modalTitle: { fontSize: 20, fontWeight: 'bold', color: '#333333' },
  closeButton: { padding: 4 },
  modalItem: { paddingVertical: 15, paddingHorizontal: 20, borderBottomWidth: 1, borderBottomColor: '#F0F0F0' },
  modalItemText: { fontSize: 16, color: '#333333' },
  modalSearchInput: { backgroundColor: '#FFFFFF', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 12, fontSize: 16, color: '#333333' },
  // Date picker modal + custom picker styles (mirrors businessDocuments.tsx)
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
