// src/screens/both/EditProfileScreen.tsx
// @ts-nocheck
import React, { useEffect, useState } from 'react';

import { DeviceEventEmitter } from 'react-native';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  FlatList,
  Alert,
  ActivityIndicator,
  Modal,
  Platform,
  RefreshControl,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { auth_service } from '../../services/auth_service';
import { role_service } from '../../services/role_service';
import { storage_service } from '../../utils/storage';
import { api_request, api_config } from '../../config/api';

export default function EditProfileScreen({ navigation, userData, onBackPress, onSaveSuccess }: any) {
  const [currentTab, setCurrentTab] = useState<'personal' | 'addresses'>('personal');
  const [isSaving, setIsSaving] = useState(false);
  // Compute active role synchronously from cached stored user (fallback to prop) so initial render knows the role
  const _storedSync = storage_service.get_user_data_sync ? storage_service.get_user_data_sync() : null;
  const _initialUser = _storedSync || userData || {};
  const _initialUserType = (_initialUser?.user_type || _initialUser?.userType || '').toString().toLowerCase();
  const _initialPreferredRole = (_initialUser?.preferred_role || _initialUser?.preferredRole || '').toString().toLowerCase();
  const initialActiveRole = _initialUserType === 'both' ? _initialPreferredRole : (_initialUserType === 'property_owner' ? 'owner' : _initialUserType === 'contractor' ? 'contractor' : '');

  const [effectiveUserType, setEffectiveUserType] = useState<string>(_initialUserType);
  const [effectivePreferredRole, setEffectivePreferredRole] = useState<string>(_initialPreferredRole);

  const activeRole = effectiveUserType === 'both' ? effectivePreferredRole : (effectiveUserType === 'property_owner' ? 'owner' : effectiveUserType);
  const viewContractor = activeRole === 'contractor';
  const viewOwner = activeRole === 'owner';

  // Ensure we refresh effective role from storage when component mounts (or when userData changes)
  useEffect(() => {
    (async () => {
      try {
        const stored = await storage_service.get_user_data();
        const sType = (stored?.user_type || stored?.userType || userData?.user_type || '').toString().toLowerCase();
        const sPref = (stored?.preferred_role || stored?.preferredRole || userData?.preferred_role || userData?.preferredRole || '').toString().toLowerCase();
        if (sType) setEffectiveUserType(sType);
        if (sPref) setEffectivePreferredRole(sPref);
      } catch (err) {
        console.warn('editProfile: failed to read stored user for role resolution', err);
      }
    })();

    const sub = DeviceEventEmitter.addListener('roleChanged', async (payload: any) => {
      try {
        const stored = await storage_service.get_user_data();
        const sType = (stored?.user_type || stored?.userType || userData?.user_type || '').toString().toLowerCase();
        const sPref = (stored?.preferred_role || stored?.preferredRole || userData?.preferred_role || userData?.preferredRole || '').toString().toLowerCase();
        if (sType) setEffectiveUserType(sType);
        if (sPref) setEffectivePreferredRole(sPref);
        // refresh profile immediately for the new role
        try { await fetchFullProfile(); } catch (e) {}
      } catch (e) {}
    });

    return () => { sub.remove(); };
  }, [userData]);

  // Personal Information Fields
  const [username, setUsername] = useState(userData?.username || '');
  const [firstName, setFirstName] = useState(userData?.first_name || '');
  const [lastName, setLastName] = useState(userData?.last_name || '');

  const [occupation, setOccupation] = useState(userData?.occupation || '');
  const [occupationId, setOccupationId] = useState<string | null>(null);
  const [occupationQuery, setOccupationQuery] = useState('');
  const [occupationOtherText, setOccupationOtherText] = useState('');
  const [occupationsList, setOccupationsList] = useState<any[]>([]);
  const [showOccupationModal, setShowOccupationModal] = useState(false);
  const [dateOfBirth, setDateOfBirth] = useState(userData?.date_of_birth || '');
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [dobDate, setDobDate] = useState<Date | null>(dateOfBirth ? new Date(dateOfBirth) : null);

  // Contractor Business Details
  const [companyName, setCompanyName] = useState(userData?.contractor?.company_name || '');
  const [companyWebsite, setCompanyWebsite] = useState(userData?.contractor?.company_website || '');
  const [companySocialMedia, setCompanySocialMedia] = useState(userData?.contractor?.company_social_media || '');
  const [companyDescription, setCompanyDescription] = useState(userData?.contractor?.company_description || '');
  const [servicesOffered, setServicesOffered] = useState(userData?.contractor?.services_offered || '');
  const [picabNumber, setPicabNumber] = useState(userData?.contractor?.picab_number || '');
  const [businessPermitNumber, setBusinessPermitNumber] = useState(userData?.contractor?.business_permit_number || '');
  const [tinNumber, setTinNumber] = useState(userData?.contractor?.tin_business_reg_number || '');

  // System Calculated Fields (Display Only)


  // Address Fields
  const [addressStreet, setAddressStreet] = useState(userData?.address_street || '');
  const [addressBarangay, setAddressBarangay] = useState(userData?.address_barangay || '');
  const [addressCity, setAddressCity] = useState(userData?.address_city || '');
  const [addressProvince, setAddressProvince] = useState(userData?.address_province || '');
  const [addressPostal, setAddressPostal] = useState(userData?.address_postal || '');
  // PSGC dropdowns
  const [provinces, setProvinces] = useState<any[]>([]);
  const [cities, setCities] = useState<any[]>([]);
  const [barangays, setBarangays] = useState<any[]>([]);
  const [showProvinceModal, setShowProvinceModal] = useState(false);
  const [showCityModal, setShowCityModal] = useState(false);
  const [showBarangayModal, setShowBarangayModal] = useState(false);

  // Verification Status Tracking
  const [pendingVerifications, setPendingVerifications] = useState<Record<string, boolean>>({});
  const [prefilledFields, setPrefilledFields] = useState<Record<string, boolean>>({});
  const [refreshing, setRefreshing] = useState(false);
  const [loadingProfile, setLoadingProfile] = useState(true);

  useEffect(() => {
    // Load startup data in sequence: provinces -> profile -> occupations
    (async () => {
      try {
        const provRes = await auth_service.get_provinces();
        if (provRes?.success && provRes.data) setProvinces(provRes.data);
      } catch (e) {
        // ignore province load failure; fetchFullProfile will still try best-effort
      }

      // Load verification status (non-blocking but keep order predictable)
      try { await loadVerificationStatus(); } catch (e) {}

      // Now fetch full profile after provinces are populated so address resolution can match
      console.log('EditProfile: forcing full profile fetch from /api/profile/fetch (after provinces)');
      try { await fetchFullProfile(); } catch (e) { console.warn('fetchFullProfile failed on startup', e); }

      // load occupations dropdown (normalize multiple possible shapes)
      (async () => {
        try {
          const formRes = await api_request('/api/signup-form', { method: 'GET' });
          console.log('EditProfile: /api/signup-form response ->', formRes);
          const rawOcc = formRes?.data?.data?.occupations || formRes?.data?.occupations || formRes?.data?.form_data?.occupations ||
            formRes?.data?.data?.form_data?.occupations || (await role_service.get_switch_form_data())?.data?.form_data?.occupations || [];
          console.log('EditProfile: raw occupations array ->', rawOcc);
          const normalized = (Array.isArray(rawOcc) ? rawOcc : []).map((item: any) => {
            const id = item?.id ?? item?.occupation_id ?? item?.ID ?? '';
            const name = item?.name ?? item?.occupation_name ?? item?.occupation ?? item?.title ?? '';
            return { id: `${id}`, name: `${name}`.trim() };
          }).filter((it: any) => it.name && it.name.length > 0);
          setOccupationsList(normalized);
          console.log('Loaded occupations for dropdown (normalized):', normalized.length, normalized[0] || null);
        } catch (e) {
          console.warn('Failed to load occupations for dropdown', e);
        }
      })();
    })();
  }, []);

  // When a province code is set, load its cities
  useEffect(() => {
    if (!addressProvince) return;
    (async () => {
      try {
        await loadCities(addressProvince);
      } catch {}
    })();
  }, [addressProvince]);

  const onRefresh = async () => {
    try {
      setRefreshing(true);
      await fetchFullProfile();
    } catch (e) {
      console.warn('Refresh failed', e);
    } finally {
      setRefreshing(false);
    }
  };

  const fetchFullProfile = async () => {
    try {
      if (!refreshing) setLoadingProfile(true);
      // Re-read stored user to avoid stale preferred_role when this screen mounts early
      let stored = null;
      try {
        stored = await storage_service.get_user_data();
      } catch (err) {
        console.warn('Failed to read stored user before fetchFullProfile', err);
      }
      const sType = (stored?.user_type || stored?.userType || userData?.user_type || '').toString().toLowerCase();
      const sPref = (stored?.preferred_role || stored?.preferredRole || userData?.preferred_role || userData?.preferredRole || '').toString().toLowerCase();
      const activeFromStored = sType === 'both' ? sPref : (sType === 'property_owner' ? 'owner' : sType);
      const roleToUse = activeFromStored || activeRole;
      const roleQuery = roleToUse ? `?role=${encodeURIComponent(roleToUse)}` : '';
      console.log('Fetching profile with role (resolved):', roleToUse);
      const resp = await api_request(`/api/profile/fetch${roleQuery}`);
      console.log('fetchFullProfile response:', resp);
      if (resp && resp.success && resp.data) {
        const payload = resp.data.data || resp.data || {};
        const u = payload.user || payload;
        const owner = payload.owner || {};
        const c = payload.contractor || {};
        if (u || owner || (c && Object.keys(c).length)) {
          console.log('fetchFullProfile user payload:', u);
          // Merge carefully: prefer base user row, then overlay only the active role's fields
          const activeIsOwner = String(roleToUse).toLowerCase().includes('owner');
          const activeIsContractor = String(roleToUse).toLowerCase().includes('contractor');
          const merged = {
            ...(u || {}),
            ...(activeIsOwner ? owner || {} : {}),
            ...(activeIsContractor ? c || {} : {}),
          };

          setUsername((merged && (merged.username || merged.user_name)) || '');
          setFirstName((merged && (merged.first_name || merged.fname)) || '');
          setLastName((merged && (merged.last_name || merged.lname)) || '');
          // Occupation fields: prefer explicit occupation_name, then occupation, then occupation_other
          const resolvedOccupationName = (payload && payload.occupation_name) || merged.occupation_name || merged.occupation || merged.occupation_other || '';
          setOccupation(resolvedOccupationName);

          // Also set occupation id and occupation_other text when available (owner row uses occupation_id/occupation_other)
          const occId = (owner && (owner.occupation_id ?? owner.occupationId)) ?? (merged && (merged.occupation_id ?? merged.occupationId)) ?? (payload && payload.occupation_id) ?? null;
          const occOther = (owner && (owner.occupation_other ?? owner.occupationOther)) ?? (merged && (merged.occupation_other ?? merged.occupationOther)) ?? (payload && payload.occupation_other) ?? '';
          setOccupationId(occId ? `${occId}` : null);
          setOccupationOtherText(occOther || '');
          setDateOfBirth(merged.date_of_birth || '');

          // Address: user row may store address_* or owner.address; parse combined address when separate fields missing
          let street = (u && (u.address_street || u.address)) || owner.address_street || '';
          let barangay = (u && u.address_barangay) || owner.address_barangay || '';
          let city = (u && u.address_city) || owner.address_city || '';
          let province = (u && u.address_province) || owner.address_province || '';
          let postal = (u && u.address_postal) || owner.address_postal || '';

          // If fields are missing but a combined address exists, or if `street` already contains a combined address
          // (e.g., DB stored full address in `address`), split it into components so resolver gets correct parts.
          const combinedRaw = owner.address || u.address || c.business_address || payload.address_display || '';
          const streetLooksCombined = street && street.includes(',') && (!barangay && !city && !province);
          if ((!(street || barangay || city || province || postal) && combinedRaw) || streetLooksCombined) {
            const raw = streetLooksCombined ? street : combinedRaw;
            const parts = raw.split(',').map((p: string) => p.trim()).filter(Boolean);
            street = parts[0] || '';
            barangay = parts[1] || '';
            city = parts[2] || '';
            province = parts[3] || '';
            postal = parts[4] || '';
          }

            // Map resolved address names to PSGC codes where possible, preserving numeric codes
          try {
            const resolved = await resolveAddressParts([street, barangay, city, province, postal]);
            setAddressStreet(resolved.street || '');
            setAddressPostal(resolved.postal || '');
            // Load dependent lists in sequence so UI shows selected names immediately
            if (resolved.provCode) {
              await loadCities(resolved.provCode);
              setAddressProvince(resolved.provCode);
              if (resolved.cityCode) {
                await loadBarangays(resolved.cityCode);
                setAddressCity(resolved.cityCode);
                if (resolved.barangayCode) setAddressBarangay(resolved.barangayCode);
              } else {
                setAddressCity(resolved.cityCode || (city || ''));
                setAddressBarangay(resolved.barangayCode || (barangay || ''));
              }
            } else {
              // Attempt best-effort name -> code resolution using loaded province list
              if (province) {
                const maybeProv = await findProvinceCodeByName(province);
                if (maybeProv) {
                  await loadCities(maybeProv);
                  setAddressProvince(maybeProv);
                  if (city) {
                    const maybeCity = await findCityCodeByName(maybeProv, city);
                    if (maybeCity) {
                      await loadBarangays(maybeCity);
                      setAddressCity(maybeCity);
                      if (barangay) {
                        const maybeB = await findBarangayCodeByName(maybeCity, barangay);
                        if (maybeB) setAddressBarangay(maybeB);
                        else setAddressBarangay(barangay);
                      }
                    } else {
                      setAddressCity(city || '');
                      setAddressBarangay(barangay || '');
                    }
                  }
                } else {
                  setAddressProvince(province || '');
                  setAddressCity(city || '');
                  setAddressBarangay(barangay || '');
                }
              } else {
                setAddressProvince(province || '');
                setAddressCity(city || '');
                setAddressBarangay(barangay || '');
              }
            }
            console.log('Address populated (owner):', {
              street: resolved.street || street,
              province: resolved.provCode || province,
              city: resolved.cityCode || city,
              barangay: resolved.barangayCode || barangay,
              postal: resolved.postal || postal,
            });
          } catch (e) {
            console.warn('Failed to map address names to PSGC codes:', e);
            setAddressStreet(street);
            setAddressBarangay(barangay);
            setAddressCity(city);
            setAddressProvince(province);
            setAddressPostal(postal);
          }

          // contractor-specific
          if (c && Object.keys(c).length) {
            console.log('fetchFullProfile contractor payload:', c);
            // Only set contractor-specific fields if viewing contractor
            if (activeIsContractor) {
              setCompanyName(c.company_name || '');
              setCompanyWebsite(c.company_website || '');
              setCompanySocialMedia(c.company_social_media || '');
              setCompanyDescription(c.company_description || '');
              setServicesOffered(c.services_offered || '');
              setPicabNumber(c.picab_number || '');
              setBusinessPermitNumber(c.business_permit_number || '');
              setTinNumber(c.picab_number || c.tin_business_reg_number || '');
            }

            // If the fetched role is contractor, prefer contractor business_address for Addresses tab
            if (activeIsContractor && c.business_address) {
              const parts = (c.business_address || '').split(',').map((p: string) => p.trim()).filter(Boolean);
              const bStreet = parts[0] || '';
              const bBarangay = parts[1] || '';
              const bCity = parts[2] || '';
              const bProvince = parts[3] || '';
              const bPostal = parts[4] || '';

              // Attempt to map textual province/city/barangay into PSGC codes so dropdowns display correctly
              try {
                const resolved = await resolveAddressParts([bStreet, bBarangay, bCity, bProvince, bPostal]);
                console.log('Contractor business_address mapping:', { bStreet, bBarangay, bCity, bProvince, bPostal, provCode: resolved.provCode, cityCode: resolved.cityCode, barangayCode: resolved.barangayCode });
                setAddressStreet(resolved.street || bStreet);
                setAddressPostal(resolved.postal || bPostal);
                if (resolved.provCode) {
                  await loadCities(resolved.provCode);
                  setAddressProvince(resolved.provCode);
                  if (resolved.cityCode) {
                    await loadBarangays(resolved.cityCode);
                    setAddressCity(resolved.cityCode);
                    if (resolved.barangayCode) setAddressBarangay(resolved.barangayCode);
                  } else {
                    setAddressCity(resolved.cityCode || (bCity || ''));
                    setAddressBarangay(resolved.barangayCode || (bBarangay || ''));
                  }
                } else {
                  if (bProvince) {
                    const maybeProv = await findProvinceCodeByName(bProvince);
                    if (maybeProv) {
                      await loadCities(maybeProv);
                      setAddressProvince(maybeProv);
                      if (bCity) {
                        const maybeCity = await findCityCodeByName(maybeProv, bCity);
                        if (maybeCity) {
                          await loadBarangays(maybeCity);
                          setAddressCity(maybeCity);
                          if (bBarangay) {
                            const maybeB = await findBarangayCodeByName(maybeCity, bBarangay);
                            if (maybeB) setAddressBarangay(maybeB);
                            else setAddressBarangay(bBarangay);
                          }
                        } else {
                          setAddressCity(bCity || '');
                          setAddressBarangay(bBarangay || '');
                        }
                      }
                    } else {
                      setAddressProvince(bProvince || '');
                      setAddressCity(bCity || '');
                      setAddressBarangay(bBarangay || '');
                    }
                  } else {
                    setAddressProvince(bProvince || '');
                    setAddressCity(bCity || '');
                    setAddressBarangay(bBarangay || '');
                  }
                }
                console.log('Address populated (contractor):', {
                  street: resolved.street || bStreet,
                  province: resolved.provCode || bProvince,
                  city: resolved.cityCode || bCity,
                  barangay: resolved.barangayCode || bBarangay,
                  postal: resolved.postal || bPostal,
                });
              } catch (err) {
                // fallback to raw values
                setAddressStreet(bStreet);
                setAddressBarangay(bBarangay);
                setAddressCity(bCity);
                setAddressProvince(bProvince);
                setAddressPostal(bPostal);
              }
            }
          }

          // No client-side 'pending verification' flags — all updates apply immediately.
          setPendingVerifications({});

          console.log('fetchFullProfile populated fields:', {
            username: u.username,
            first_name: u.first_name,
            last_name: u.last_name,
            address_street: u.address_street || u.address,
            company_name: (payload.contractor || {}).company_name,
            picab_number: (payload.contractor || {}).picab_number,
          });

          // Optionally persist enriched user data (merge owner/contractor into a single object)
          try {
            const mergedToSave = { ...(u || {}), ...(owner || {}), contractor: c || {} };
            await storage_service.save_user_data(mergedToSave);
            console.log('Saved merged user to storage');
          } catch (e) {
            // ignore storage errors
          }
        }
      }
    } catch (e) {
      console.warn('Failed to fetch full profile for edit screen', e?.message || e);
    } finally {
      try {
        setLoadingProfile(false);
      } catch (ignored) {}
    }
  };

  const formatDate = (d: Date) => {
    const yyyy = d.getFullYear();
    const mm = `${d.getMonth() + 1}`.padStart(2, '0');
    const dd = `${d.getDate()}`.padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  };

  const loadVerificationStatus = async () => {
    try {
      const response = await role_service.get_current_role();
      if (response.success) {
        const data = response.data || response;

        // Check for pending verifications
        const pending: Record<string, boolean> = {};

        if (data.contractor?.verification_status === 'pending') {
          pending.company_name = true;
          pending.business_address = true;
          pending.picab_number = true;
          pending.business_permit_number = true;
          pending.tin_business_reg_number = true;
        }

        if (data.owner?.address_verification_pending) {
          pending.address = true;
        }

        setPendingVerifications(pending);
      }
    } catch (error) {
      console.error('Error loading verification status:', error);
    }
  };

  // Phone input is editable without OTP verification in this screen

  const handleAddressChange = () => {
    // Check if address actually changed
    const originalAddress = {
      street: userData?.address_street || '',
      barangay: userData?.address_barangay || '',
      city: userData?.address_city || '',
      province: userData?.address_province || '',
      postal: userData?.address_postal || ''
    };

    const newAddress = {
      street: addressStreet,
      barangay: addressBarangay,
      city: addressCity,
      province: addressProvince,
      postal: addressPostal
    };
    // Address updates apply immediately without requiring verification.
    // No prompt will be shown and no pending verification flag will be set.
    return;
  };

  const loadCities = async (provinceCode: string) => {
    try {
      const res = await auth_service.get_cities_by_province(provinceCode);
      if (res?.success && res.data) {
        setCities(res.data);
        setBarangays([]);
        setAddressCity('');
        setAddressBarangay('');
      }
    } catch {}
  };

  const loadBarangays = async (cityCode: string) => {
    try {
      const res = await auth_service.get_barangays_by_city(cityCode);
      if (res?.success && res.data) setBarangays(res.data);
    } catch {}
  };

  // Helper: resolve arbitrary address parts (names or numeric codes) to PSGC codes and street/postal
  const resolveAddressParts = async (parts: string[]) => {
    const normalize = (s = '') => {
      const str = (s || '').toString().toLowerCase();
      try {
        // remove diacritics
        const noDiacritics = str.normalize('NFD').replace(/\p{Diacritic}/gu, '');
        return noDiacritics.replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, ' ').trim();
      } catch (e) {
        return str.replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, ' ').trim();
      }
    };
    const extractNumeric = (s = '') => {
      const m = String(s || '').match(/\d{4,}/);
      return m ? m[0] : null;
    };

    const out: any = { street: '', provCode: null, cityCode: null, barangayCode: null, postal: '' };
    const arr = (parts || []).map((p: string) => (p || '').toString().trim()).filter(Boolean);
    if (!arr.length) return out;

    // Try to extract postal if last part contains postal-like digits
    if (arr.length > 0) {
      const lastNum = extractNumeric(arr[arr.length - 1]);
      if (lastNum && (arr[arr.length - 1].length <= 12)) {
        out.postal = lastNum;
        arr.pop();
      }
    }

    // Ensure provinces loaded
    if (!provinces || provinces.length === 0) {
      try {
        const provRes = await auth_service.get_provinces();
        if (provRes?.success && provRes.data) setProvinces(provRes.data);
      } catch (_) {}
    }

    // Attempt to find province by checking any part (prefer later parts)
    let provIndex = -1;
    const placeWordsRegex = /\b(province|city|municipality|municipal|municipalidad|mun|city of|municipality of)\b/gi;
    for (let i = arr.length - 1; i >= 0; i--) {
      const p = arr[i];
      const numeric = extractNumeric(p);
      if (numeric) {
        out.provCode = numeric;
        provIndex = i; break;
      }
      const tgt = normalize(p);
      const strippedTgt = tgt.replace(placeWordsRegex, '').trim();
      const foundProv = (provinces || []).find((prov: any) => {
        const provName = normalize(prov.name || '');
        const provStripped = provName.replace(placeWordsRegex, '').trim();
        return provName === tgt || provName.includes(tgt) || tgt.includes(provName) || provStripped === strippedTgt || provStripped.includes(strippedTgt) || strippedTgt.includes(provStripped) || (prov.oldName && normalize(prov.oldName || '') === tgt);
      });
      if (foundProv) { out.provCode = String(foundProv.code); provIndex = i; break; }
    }
    if (provIndex >= 0) arr.splice(provIndex, 1);

    // Attempt to find city using provCode if available, else try global match
    let citiesList: any[] = [];
    if (out.provCode) {
      try {
        const cRes = await auth_service.get_cities_by_province(String(out.provCode));
        if (cRes?.success && cRes.data) { citiesList = cRes.data; setCities(cRes.data); }
        else citiesList = cRes?.data || [];
      } catch (_) { citiesList = []; }
    }

    let cityIndex = -1;
    for (let i = arr.length - 1; i >= 0; i--) {
      const p = arr[i];
      const numeric = extractNumeric(p);
      if (numeric) { out.cityCode = numeric; cityIndex = i; break; }
      const tgt = normalize(p);
      // check in citiesList first
      let foundCity = (citiesList || []).find((c: any) => normalize(c.name || '') === tgt || normalize(c.name || '').includes(tgt) || tgt.includes(normalize(c.name || '')));
      if (!foundCity) {
        // fallback: search all provinces' cities (best effort via API call is expensive; try quick local lookup by fetching cities for each prov)
        // skip expensive global scan here; rely on citiesList when province found or name-match won't resolve
      }
      if (foundCity) { out.cityCode = String(foundCity.code); cityIndex = i; break; }
    }
    if (cityIndex >= 0) arr.splice(cityIndex, 1);

    // Attempt to find barangay using cityCode
    if (out.cityCode) {
      try {
        const brRes = await auth_service.get_barangays_by_city(String(out.cityCode));
        const brList = brRes?.success && brRes.data ? brRes.data : (brRes?.data || []);
        if (brList && brList.length) setBarangays(brList);
        for (let i = arr.length - 1; i >= 0; i--) {
          const p = arr[i];
          const numeric = extractNumeric(p);
          if (numeric) { out.barangayCode = numeric; arr.splice(i, 1); break; }
          const tgt = normalize(p);
          const foundB = (brList || []).find((bObj: any) => normalize(bObj.name || '') === tgt || normalize(bObj.name || '').includes(tgt) || tgt.includes(normalize(bObj.name || '')));
          if (foundB) { out.barangayCode = String(foundB.code); arr.splice(i, 1); break; }
        }
      } catch (_) {}
    }

    // Remaining parts: treat first element as the street; remaining parts are likely barangay/city/province names
    if (arr.length) out.street = arr[0];
    return out;
  };

  // Helper: find PSGC codes by name (best-effort using loaded lists)
  const normalizeForMatch = (s = '') => {
    return (s || '').toString().toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, ' ').trim();
  };

  const findProvinceCodeByName = async (name: string) => {
    if (!name) return null;
    const tgt = normalizeForMatch(name);
    let list = provinces || [];
    if ((!list || !list.length)) {
      try {
        const provRes = await auth_service.get_provinces();
        list = provRes?.success && provRes.data ? provRes.data : (provRes?.data || []);
        if (list && list.length) setProvinces(list);
      } catch (_) { list = []; }
    }
    const found = (list || []).find((p: any) => {
      const pname = normalizeForMatch(p.name || '');
      return pname === tgt || pname.includes(tgt) || tgt.includes(pname) || (p.oldName && normalizeForMatch(p.oldName || '') === tgt);
    });
    return found ? String(found.code) : null;
  };

  const findCityCodeByName = async (provCode: string, name: string) => {
    if (!name) return null;
    const tgt = normalizeForMatch(name);
    let list = cities || [];
    if ((!list || !list.length) && provCode) {
      try {
        const cRes = await auth_service.get_cities_by_province(String(provCode));
        list = cRes?.success && cRes.data ? cRes.data : (cRes?.data || []);
        if (list && list.length) setCities(list);
      } catch (_) { list = []; }
    }
    const found = (list || []).find((c: any) => {
      const cname = normalizeForMatch(c.name || '');
      return cname === tgt || cname.includes(tgt) || tgt.includes(cname);
    });
    return found ? String(found.code) : null;
  };

  const findBarangayCodeByName = async (cityCode: string, name: string) => {
    if (!name) return null;
    const tgt = normalizeForMatch(name);
    let list = barangays || [];
    if ((!list || !list.length) && cityCode) {
      try {
        const bRes = await auth_service.get_barangays_by_city(String(cityCode));
        list = bRes?.success && bRes.data ? bRes.data : (bRes?.data || []);
        if (list && list.length) setBarangays(list);
      } catch (_) { list = []; }
    }
    const found = (list || []).find((b: any) => {
      const bname = normalizeForMatch(b.name || '');
      return bname === tgt || bname.includes(tgt) || tgt.includes(bname);
    });
    return found ? String(found.code) : null;
  };

  // Helper: get PSGC name by numeric code (province/city/barangay)
  const getProvinceNameByCode = async (code: string) => {
    if (!code) return null;
    const c = String(code);
    let list = provinces || [];
    if ((!list || !list.length)) {
      try { const provRes = await auth_service.get_provinces(); if (provRes?.success && provRes.data) { setProvinces(provRes.data); list = provRes.data; } } catch (_) {}
    }
    const found = (list || []).find((p: any) => String(p.code) === c);
    return found ? (found.name || null) : null;
  };

  const getCityNameByCode = async (provCode: string | null, code: string) => {
    if (!code) return null;
    const c = String(code);
    let list = cities || [];
    if ((!list || !list.length) && provCode) {
      try { const res = await auth_service.get_cities_by_province(String(provCode)); if (res?.success && res.data) { setCities(res.data); list = res.data; } } catch (_) {}
    }
    // fallback: try to find in currently loaded cities
    const found = (list || []).find((ct: any) => String(ct.code) === c);
    if (found) return found.name || null;

    // last resort: try fetching cities for all provinces (expensive but rare)
    try {
      const provs = provinces && provinces.length ? provinces : (await (await auth_service.get_provinces()).data || []);
      for (const p of provs || []) {
        try {
          const r = await auth_service.get_cities_by_province(String(p.code));
          const l = r?.success && r.data ? r.data : (r?.data || []);
          const f = (l || []).find((ct: any) => String(ct.code) === c);
          if (f) return f.name || null;
        } catch (e) {}
      }
    } catch (e) {}
    return null;
  };

  const getBarangayNameByCode = async (cityCode: string | null, code: string) => {
    if (!code) return null;
    const c = String(code);
    let list = barangays || [];
    if ((!list || !list.length) && cityCode) {
      try { const res = await auth_service.get_barangays_by_city(String(cityCode)); if (res?.success && res.data) { setBarangays(res.data); list = res.data; } } catch (_) {}
    }
    const found = (list || []).find((b: any) => String(b.code) === c);
    if (found) return found.name || null;

    // fallback: try fetching barangays across provinces/cities is expensive; skip
    return null;
  };

  const handleSave = async () => {
    // Validate required fields
    if (!username.trim()) {
      Alert.alert('Error', 'Username is required');
      return;
    }

    setIsSaving(true);
    try {
      const formData = new FormData();

      // Personal Information
      formData.append('username', username);
      formData.append('first_name', firstName);

      // Last name: allow updates
      if (lastName) formData.append('last_name', lastName);

      // Phone removed from edit form

      // Occupation: prefer occupation_id when available, otherwise send occupation text
      if (occupationId) {
        formData.append('occupation_id', occupationId);
        if (occupationId === '26' && occupationOtherText) {
          formData.append('occupation_other', occupationOtherText);
        }
      } else if (occupation) {
        formData.append('occupation', occupation);
      }

      // Contractor Business Details
      // Always editable fields
      if (companyWebsite) formData.append('company_website', companyWebsite);
      if (companySocialMedia) formData.append('company_social_media', companySocialMedia);
      if (companyDescription) formData.append('company_description', companyDescription);
      if (servicesOffered) formData.append('services_offered', servicesOffered);

      // Fields that trigger re-verification
      const reVerificationFields: Record<string, any> = {
        company_name: companyName,
        picab_number: picabNumber,
        business_permit_number: businessPermitNumber,
        tin_business_reg_number: tinNumber
      };

      Object.entries(reVerificationFields).forEach(([key, value]) => {
        if (value && value !== userData?.contractor?.[key]) {
          formData.append(key, value);
        }
      });

      // Address: ensure we send PSGC numeric codes when possible (backend expects codes)
      try {
        const extractNumeric = (s = '') => {
          const m = String(s || '').match(/\d{4,}/);
          return m ? m[0] : null;
        };

        // If user pasted a full comma-separated address into the street field,
        // split it into components and populate the individual address fields
        if ((addressStreet || '').toString().includes(',') && !addressBarangay && !addressCity && !addressProvince) {
          const parts = (addressStreet || '').toString().split(',').map((s: string) => s.trim()).filter(Boolean);
          const [streetPart, barangayPart, cityPart, provincePart] = parts;
          if (streetPart) setAddressStreet(streetPart);
          if (barangayPart) setAddressBarangay(barangayPart);
          if (cityPart) setAddressCity(cityPart);
          if (provincePart) setAddressProvince(provincePart);
        }

        let provinceToSend = addressProvince;
        let cityToSend = addressCity;
        let barangayToSend = addressBarangay;

        // Resolve province -> code
        if (provinceToSend && !/^[0-9]+$/.test(String(provinceToSend))) {
          // try numeric inside string first
          const num = extractNumeric(provinceToSend);
          if (num) provinceToSend = num;
          else {
            if (!provinces || provinces.length === 0) {
              try { const provRes = await auth_service.get_provinces(); if (provRes?.success && provRes.data) setProvinces(provRes.data); } catch (_) {}
            }
            const target = (provinceToSend || '').toString().toLowerCase().trim();
            const found = (provinces || []).find((p: any) => (p.name || '').toString().toLowerCase().trim() === target || (p.name || '').toString().toLowerCase().includes(target));
            if (found) provinceToSend = String(found.code);
          }
        }

        // Resolve city -> code
        if (cityToSend && !/^[0-9]+$/.test(String(cityToSend))) {
          const num = extractNumeric(cityToSend);
          if (num) cityToSend = num;
          else if (provinceToSend) {
            try {
              const citiesRes = await auth_service.get_cities_by_province(String(provinceToSend));
              const citiesList = citiesRes?.success && citiesRes.data ? citiesRes.data : (citiesRes?.data || citiesRes || []);
              if (citiesList && citiesList.length) setCities(citiesList);
              const target = (cityToSend || '').toString().toLowerCase().trim();
              const foundC = (citiesList || []).find((cObj: any) => (cObj.name || '').toString().toLowerCase().trim() === target || (cObj.name || '').toString().toLowerCase().includes(target));
              if (foundC) cityToSend = String(foundC.code);
            } catch (_) {}
          }
        }

        // Resolve barangay -> code
        if (barangayToSend && !/^[0-9]+$/.test(String(barangayToSend))) {
          const num = extractNumeric(barangayToSend);
          if (num) barangayToSend = num;
          else if (cityToSend) {
            try {
              const brRes = await auth_service.get_barangays_by_city(String(cityToSend));
              const brList = brRes?.success && brRes.data ? brRes.data : (brRes?.data || brRes || []);
              if (brList && brList.length) setBarangays(brList);
              const target = (barangayToSend || '').toString().toLowerCase().trim();
              const foundB = (brList || []).find((bObj: any) => (bObj.name || '').toString().toLowerCase().trim() === target || (bObj.name || '').toString().toLowerCase().includes(target));
              if (foundB) barangayToSend = String(foundB.code);
            } catch (_) {}
          }
        }

        // Resolve any numeric PSGC codes back to human-readable names for payload
        let displayProvinceName = addressProvince;
        let displayCityName = addressCity;
        let displayBarangayName = addressBarangay;
        try {
          if (provinceToSend && /^[0-9]+$/.test(String(provinceToSend))) {
            const n = await getProvinceNameByCode(String(provinceToSend));
            if (n) displayProvinceName = n;
          } else if (addressProvince && /^[0-9]+$/.test(String(addressProvince))) {
            const n = await getProvinceNameByCode(String(addressProvince));
            if (n) displayProvinceName = n;
          }

          // city
          if (cityToSend && /^[0-9]+$/.test(String(cityToSend))) {
            const n = await getCityNameByCode(provinceToSend || (addressProvince && /^[0-9]+$/.test(String(addressProvince)) ? addressProvince : null), String(cityToSend));
            if (n) displayCityName = n;
          } else if (addressCity && /^[0-9]+$/.test(String(addressCity))) {
            const n = await getCityNameByCode(provinceToSend || (addressProvince && /^[0-9]+$/.test(String(addressProvince)) ? addressProvince : null), String(addressCity));
            if (n) displayCityName = n;
          }

          // barangay
          if (barangayToSend && /^[0-9]+$/.test(String(barangayToSend))) {
            const n = await getBarangayNameByCode(cityToSend || (addressCity && /^[0-9]+$/.test(String(addressCity)) ? addressCity : null), String(barangayToSend));
            if (n) displayBarangayName = n;
          } else if (addressBarangay && /^[0-9]+$/.test(String(addressBarangay))) {
            const n = await getBarangayNameByCode(cityToSend || (addressCity && /^[0-9]+$/.test(String(addressCity)) ? addressCity : null), String(addressBarangay));
            if (n) displayBarangayName = n;
          }
        } catch (e) {
          // ignore resolution errors and fall back to raw values
        }

        // Only send owner-specific address_* fields when active role is owner.
        const isContractorSave = activeRole && String(activeRole).toLowerCase().includes('contractor');
        if (!isContractorSave) {
          if (addressStreet) formData.append('address_street', addressStreet);
          if (displayBarangayName) formData.append('address_barangay', displayBarangayName);
          if (displayCityName) formData.append('address_city', displayCityName);
          if (displayProvinceName) formData.append('address_province', displayProvinceName);
          if (addressPostal) formData.append('address_postal', addressPostal);
        }

        // For contractor role, backend expects `business_address` as a single comma-separated string.
        if (isContractorSave) {
          const bizParts: string[] = [];
          if (addressStreet) bizParts.push(addressStreet);
          if (displayBarangayName) bizParts.push(displayBarangayName);
          if (displayCityName) bizParts.push(displayCityName);
          if (displayProvinceName) bizParts.push(displayProvinceName);
          if (addressPostal) bizParts.push(addressPostal);
          if (bizParts.length) formData.append('business_address', bizParts.join(', '));
          // also include individual address_* fields for consistency
          if (addressStreet) formData.append('address_street', addressStreet);
          if (displayBarangayName) formData.append('address_barangay', displayBarangayName);
          if (displayCityName) formData.append('address_city', displayCityName);
          if (displayProvinceName) formData.append('address_province', displayProvinceName);
          if (addressPostal) formData.append('address_postal', addressPostal);
        }
      } catch (err) {
        // fallback: append raw values
        if (addressStreet) formData.append('address_street', addressStreet);
        if (addressBarangay) formData.append('address_barangay', addressBarangay);
        if (addressCity) formData.append('address_city', addressCity);
        if (addressProvince) formData.append('address_province', addressProvince);
        if (addressPostal) formData.append('address_postal', addressPostal);
      }

      // Include active role in update so backend knows which role-specific row to update
      if (activeRole) formData.append('active_role', activeRole);
      const updateRoleQuery = activeRole ? `?role=${encodeURIComponent(activeRole)}` : '';
      const response = await api_request(`${api_config.endpoints.profile.update}${updateRoleQuery}`, {
        method: 'POST',
        body: formData,
      });

      if (response.success) {
        const returnedUser = response.data?.data || response.data || null;
        let updatedUser = { ...userData };

        if (returnedUser) {
          updatedUser = { ...updatedUser, ...returnedUser };
        }

        await storage_service.save_user_data(updatedUser);

        Alert.alert(
          'Profile Updated',
          pendingVerifications.address ? 'Your address changes have been submitted for verification.' : 'Profile updated successfully!',
          [
            {
              text: 'OK',
              onPress: () => {
                // Stay on this screen; refresh the displayed profile data
                try {
                  fetchFullProfile();
                } catch (e) {}
              },
            },
          ]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to update profile.');
      }
    } catch (error) {
      console.error('Update profile error:', error);
      Alert.alert('Error', 'Network error. Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const renderVerificationBadge = (fieldName: string) => {
    if (pendingVerifications[fieldName]) {
      return (
        <View style={styles.pendingBadge}>
          <Ionicons name="time-outline" size={14} color="#F39C12" />
          <Text style={styles.pendingBadgeText}>Pending Review</Text>
        </View>
      );
    }
    return null;
  };



  return (
    <SafeAreaView style={styles.container}>
      {loadingProfile ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#EC7E00" />
        </View>
      ) : (
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => {
            try {
              if (navigation && typeof navigation.goBack === 'function') return navigation.goBack();
              if (typeof onBackPress === 'function') return onBackPress();
              console.warn('EditProfile: no navigation or onBackPress available to go back');
            } catch (e) { console.warn('Back navigation failed', e); }
          }} style={styles.backButton}>
            <Ionicons name="chevron-back" size={28} color="#333" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Edit Profile</Text>
        </View>

        {/* Tabs */}
        <View style={styles.tabContainer}>
          <TouchableOpacity
            style={[styles.tabButton, currentTab === 'personal' && styles.tabButtonActive]}
            onPress={() => setCurrentTab('personal')}
          >
            <Text style={[styles.tabButtonText, currentTab === 'personal' && styles.tabButtonTextActive]}>
              Personal Information
            </Text>
          </TouchableOpacity>
          {(viewOwner || viewContractor || (!viewOwner && !viewContractor)) && (
            <TouchableOpacity
              style={[styles.tabButton, currentTab === 'addresses' && styles.tabButtonActive]}
              onPress={() => setCurrentTab('addresses')}
            >
              <Text style={[styles.tabButtonText, currentTab === 'addresses' && styles.tabButtonTextActive]}>
                Addresses
              </Text>
            </TouchableOpacity>
          )}

        </View>

        {/* Personal Information Tab */}
        {currentTab === 'personal' && (
          <View style={styles.formContainer}>
            {/* Basic Info */}
            <Text style={styles.sectionTitle}>Basic Information</Text>

            <Text style={styles.label}>Username</Text>
            <TextInput
              style={styles.input}
              value={username}
              onChangeText={setUsername}
              placeholder="Enter username"
              placeholderTextColor="#999"
            />

            {/* Email removed from edit form */}

            {!viewContractor && (
              <>
                <Text style={styles.label}>First Name</Text>
                <TextInput
                  style={styles.input}
                  value={firstName}
                  onChangeText={setFirstName}
                  placeholder="Enter first name"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>Last Name</Text>
                <TextInput
                  style={styles.input}
                  value={lastName}
                  onChangeText={setLastName}
                  placeholder="Enter last name"
                  placeholderTextColor="#999"
                />
              </>
            )}

            {/* Phone removed from edit form */}

            {viewOwner && (
              <>
                <Text style={styles.label}>Occupation</Text>
                <TouchableOpacity
                  style={styles.input}
                  onPress={() => setShowOccupationModal(true)}
                >
                  <Text style={{ color: occupation ? '#000' : '#999' }}>{occupation || 'Select occupation'}</Text>
                </TouchableOpacity>

                <Modal
                  visible={showOccupationModal}
                  animationType="slide"
                  transparent
                  onRequestClose={() => setShowOccupationModal(false)}
                >
                  <View style={styles.modalOverlay}>
                    <View style={[styles.modalContainer, { maxHeight: '80%' }]}>
                      <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>Select Occupation</Text>
                        <TouchableOpacity onPress={() => setShowOccupationModal(false)}>
                          <Ionicons name="close" size={24} color="#333" />
                        </TouchableOpacity>
                      </View>
                      <TextInput
                        style={[styles.input, { marginBottom: 12 }]}
                        value={occupationQuery}
                        onChangeText={setOccupationQuery}
                        placeholder="Search occupations"
                        placeholderTextColor="#999"
                      />

                      {occupationsList.length === 0 ? (
                        <View style={{ padding: 12 }}>
                          <Text style={{ color: '#666' }}>No occupations available</Text>
                        </View>
                      ) : (
                        <FlatList
                          data={[...occupationsList].filter((o: any) => !occupationQuery || (o.name || '').toLowerCase().includes(occupationQuery.toLowerCase()))}
                          keyExtractor={(item, index) => `${item.id ?? index}`}
                          renderItem={({ item }) => (
                            <TouchableOpacity
                              style={styles.pickerItem}
                              onPress={() => {
                                setOccupation(item.name);
                                setOccupationId(item.id?.toString ? item.id.toString() : `${item.id}`);
                                setShowOccupationModal(false);
                              }}
                            >
                              <Text style={styles.pickerItemText}>{item.name}</Text>
                            </TouchableOpacity>
                          )}
                        />
                      )}

                      {occupationId === '26' && (
                        <View style={{ marginTop: 12 }}>
                          <Text style={styles.label}>Please specify</Text>
                          <TextInput
                            style={styles.input}
                            value={occupationOtherText}
                            onChangeText={setOccupationOtherText}
                            placeholder="Enter occupation"
                            placeholderTextColor="#999"
                          />
                        </View>
                      )}
                    </View>
                  </View>
                </Modal>

                {/* If 'Others' selected, show the specify field on the main form */}
                {(occupationId === '26' || (occupation || '').toLowerCase().includes('other')) && (
                  <View style={{ marginTop: 12 }}>
                    <Text style={styles.label}>Please specify occupation</Text>
                    <TextInput
                      style={styles.input}
                      value={occupationOtherText}
                      onChangeText={setOccupationOtherText}
                      placeholder="Enter occupation"
                      placeholderTextColor="#999"
                    />
                  </View>
                )}

                <Text style={styles.label}>Date of Birth</Text>
                <TouchableOpacity
                  style={[styles.input, { justifyContent: 'center' }]}
                  onPress={() => setShowDatePicker(true)}
                >
                  <Text style={{ color: dateOfBirth ? '#000' : '#999' }}>
                    {dateOfBirth || 'YYYY-MM-DD'}
                  </Text>
                </TouchableOpacity>
                {showDatePicker && (
                  <DateTimePicker
                    value={dobDate || new Date(1990, 0, 1)}
                    mode="date"
                    display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                    maximumDate={new Date()}
                    onChange={(_, selected) => {
                      setShowDatePicker(Platform.OS === 'ios');
                      if (selected) {
                        setDobDate(selected);
                        const formatted = formatDate(selected);
                        setDateOfBirth(formatted);
                      }
                    }}
                  />
                )}
              </>
            )}



            {/* Contractor Business Details */}
            {viewContractor && (
              <>
                <Text style={styles.sectionTitle}>Contractor Business Details</Text>

                <Text style={styles.label}>
                  Company Name {renderVerificationBadge('company_name')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.company_name && styles.pendingInput]}
                  value={companyName}
                  onChangeText={setCompanyName}
                  placeholder="Enter company name"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>Company Website</Text>
                <TextInput
                  style={styles.input}
                  value={companyWebsite}
                  onChangeText={setCompanyWebsite}
                  placeholder="https://example.com"
                  placeholderTextColor="#999"
                  keyboardType="url"
                />

                <Text style={styles.label}>Social Media</Text>
                <TextInput
                  style={styles.input}
                  value={companySocialMedia}
                  onChangeText={setCompanySocialMedia}
                  placeholder="Social media links"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>Company Description</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  value={companyDescription}
                  onChangeText={setCompanyDescription}
                  placeholder="Describe your company"
                  placeholderTextColor="#999"
                  multiline
                  numberOfLines={4}
                />

                <Text style={styles.label}>Services Offered</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  value={servicesOffered}
                  onChangeText={setServicesOffered}
                  placeholder="List your services"
                  placeholderTextColor="#999"
                  multiline
                  numberOfLines={3}
                />

                <Text style={styles.label}>
                  PICAB Number {renderVerificationBadge('picab_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.picab_number && styles.pendingInput]}
                  value={picabNumber}
                  onChangeText={setPicabNumber}
                  placeholder="Enter PICAB number"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>
                  Business Permit Number {renderVerificationBadge('business_permit_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.business_permit_number && styles.pendingInput]}
                  value={businessPermitNumber}
                  onChangeText={setBusinessPermitNumber}
                  placeholder="Enter business permit number"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>
                  TIN Number {renderVerificationBadge('tin_business_reg_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.tin_business_reg_number && styles.pendingInput]}
                  value={tinNumber}
                  onChangeText={setTinNumber}
                  placeholder="Enter TIN number"
                  placeholderTextColor="#999"
                />
              </>
            )}
          </View>
        )}

        {/* Addresses Tab */}
        {currentTab === 'addresses' && (
          <View style={styles.formContainer}>
            <Text style={styles.sectionTitle}>
              Address Information {renderVerificationBadge('address')}
            </Text>

            <Text style={styles.label}>Street Address</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressStreet}
              onChangeText={setAddressStreet}
              onBlur={handleAddressChange}
              placeholder="Enter street address"
              placeholderTextColor="#999"
            />
            <Text style={styles.label}>Province</Text>
            <TouchableOpacity
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              onPress={() => setShowProvinceModal(true)}
            >
              <Text style={{ color: addressProvince ? '#000' : '#999' }}>{provinces.find(p => `${p.code}` === `${addressProvince}`)?.name || 'Select Province'}</Text>
            </TouchableOpacity>

            <Text style={styles.label}>City/Municipality</Text>
            <TouchableOpacity
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              onPress={() => setShowCityModal(true)}
              disabled={!addressProvince}
            >
              <Text style={{ color: addressCity ? '#000' : '#999' }}>{cities.find(c => `${c.code}` === `${addressCity}`)?.name || (addressProvince ? 'Select City/Municipality' : 'Select Province First')}</Text>
            </TouchableOpacity>

            <Text style={styles.label}>Barangay</Text>
            <TouchableOpacity
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              onPress={() => setShowBarangayModal(true)}
              disabled={!addressCity}
            >
              <Text style={{ color: addressBarangay ? '#000' : '#999' }}>{barangays.find(b => `${b.code}` === `${addressBarangay}`)?.name || (addressCity ? 'Select Barangay' : 'Select City First')}</Text>
            </TouchableOpacity>

            <Text style={styles.label}>Postal Code</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressPostal}
              onChangeText={setAddressPostal}
              onBlur={handleAddressChange}
              placeholder="Enter postal code"
              placeholderTextColor="#999"
              keyboardType="number-pad"
            />

            {pendingVerifications.address && (
              <View style={styles.verificationMessage}>
                <Ionicons name="information-circle-outline" size={20} color="#F39C12" />
                <Text style={styles.verificationMessageText}>
                  Your address changes will be reviewed by our team.
                </Text>
              </View>
            )}
          </View>
        )}

        {/* Save Button */}
        {/* Province / City / Barangay Modals */}
        <Modal visible={showProvinceModal} animationType="slide" transparent onRequestClose={() => setShowProvinceModal(false)}>
          <View style={styles.modalOverlay}>
            <View style={styles.modalContainer}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Select Province</Text>
                <TouchableOpacity onPress={() => setShowProvinceModal(false)}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
              </View>
              <FlatList
                data={provinces}
                keyExtractor={(item, index) => `${item.code}-${index}`}
                renderItem={({ item }) => (
                  <TouchableOpacity style={styles.pickerItem} onPress={async () => { setAddressProvince(item.code); setShowProvinceModal(false); try { await loadCities(item.code); } catch {} }}>
                    <Text style={styles.pickerItemText}>{item.name}</Text>
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
                <TouchableOpacity onPress={() => setShowCityModal(false)}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
              </View>
              <FlatList
                data={cities}
                keyExtractor={(item, index) => `${item.code}-${index}`}
                renderItem={({ item }) => (
                  <TouchableOpacity style={styles.pickerItem} onPress={async () => { setAddressCity(item.code); setShowCityModal(false); try { await loadBarangays(item.code); } catch {} }}>
                    <Text style={styles.pickerItemText}>{item.name}</Text>
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
                <TouchableOpacity onPress={() => setShowBarangayModal(false)}><Ionicons name="close" size={22} color="#333" /></TouchableOpacity>
              </View>
              <FlatList
                data={barangays}
                keyExtractor={(item, index) => `${item.code}-${index}`}
                renderItem={({ item }) => (
                  <TouchableOpacity style={styles.pickerItem} onPress={() => { setAddressBarangay(item.code); setShowBarangayModal(false); }}>
                    <Text style={styles.pickerItemText}>{item.name}</Text>
                  </TouchableOpacity>
                )}
              />
            </View>
          </View>
        </Modal>

        {/* Save Button */}
          <TouchableOpacity
          style={[styles.saveButton, isSaving && styles.buttonDisabled]}
          onPress={handleSave}
          disabled={isSaving}
        >
          {isSaving ? (
            <ActivityIndicator color="#FFF" />
          ) : (
            <Text style={styles.saveButtonText}>Save Changes</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
      )}

      {/* Email and phone removed from this edit form */}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FAFAFA',
  },
  scrollContent: {
    padding: 20,
  },

  /** HEADER **/
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 24,
  },
  backButton: {
    width: 42,
    height: 42,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
    borderRadius: 10,
    backgroundColor: '#FFF',
    elevation: 2,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#2C2C2C',
  },

  /** TABS **/
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: '#EFEFEF',
    borderRadius: 10,
    padding: 4,
    marginBottom: 28,
  },
  tabButton: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 8,
  },
  tabButtonActive: {
    backgroundColor: '#FFFFFF',
    elevation: 3,
  },
  tabButtonText: {
    fontSize: 14,
    color: '#777',
  },
  tabButtonTextActive: {
    color: '#EC7E00',
    fontWeight: '600',
  },

  /** FORM **/
  formContainer: {
    marginBottom: 40,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#2C2C2C',
    marginBottom: 14,
    marginTop: 8,
  },
  label: {
    fontSize: 13,
    color: '#666',
    marginBottom: 6,
    marginTop: 12,
  },
  input: {
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 10,
    padding: 14,
    fontSize: 15,
    backgroundColor: '#FFF',
  },
  textArea: {
    height: 110,
    textAlignVertical: 'top',
  },
  lockedField: {
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 10,
    padding: 14,
    backgroundColor: '#F3F3F3',
  },
  lockedFieldText: {
    fontSize: 15,
    color: '#999',
  },

  /** BADGES **/
  pendingBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5E0',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    marginLeft: 8,
  },
  pendingBadgeText: {
    fontSize: 12,
    color: '#F39C12',
    marginLeft: 4,
  },

  /** BUTTON **/
  saveButton: {
    backgroundColor: '#EC7E00',
    paddingVertical: 16,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 24,
    shadowColor: '#000',
    shadowOpacity: 0.1,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 4,
    elevation: 3,
  },
  saveButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  buttonDisabled: {
    opacity: 0.6,
  },

  /** MODAL **/
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.4)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContainer: {
    backgroundColor: '#FFF',
    borderRadius: 16,
    padding: 22,
    width: '88%',
    maxHeight: '80%',
    elevation: 10,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 18,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#2C2C2C',
  },
  modalText: {
    fontSize: 14,
    color: '#555',
    lineHeight: 20,
    marginBottom: 16,
  },
  modalButton: {
    backgroundColor: '#EC7E00',
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 12,
  },
  modalButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  resendText: {
    color: '#EC7E00',
    fontSize: 14,
    textAlign: 'center',
    marginTop: 12,
  },

  /** PICKER **/
  pickerItem: {
    paddingVertical: 12,
    paddingHorizontal: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#EEE',
  },
  pickerItemText: {
    fontSize: 16,
    color: '#333',
  },

  /** LOADING **/
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
});
