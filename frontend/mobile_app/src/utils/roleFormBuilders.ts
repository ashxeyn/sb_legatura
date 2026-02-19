import { computeYears } from './roleFormUtils';

export function validateContractorStep1(formData: any, dropdowns: any): string[] {
  const errors: string[] = [];
  const phone = (formData.company_phone || '').trim();
  if (!formData.company_name?.trim()) errors.push('Company name is required');
  if (!phone) errors.push('Company phone is required');
  if (phone && !/^09\d{9}$/.test(phone)) errors.push('Company phone must be 11 digits starting with 09');
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
  if (!formData.authorized_rep_fname?.trim()) errors.push('Authorized representative first name is required');
  if (!formData.authorized_rep_lname?.trim()) errors.push('Authorized representative last name is required');
  return errors;
}

export function buildContractorStep1Payload(formData: any) {
  return {
    company_name: formData.company_name,
    company_phone: formData.company_phone,
    years_of_experience: computeYears(formData.experience_start_date),
    contractor_type_id: formData.contractor_type_id,
    contractor_type_other_text: formData.contractor_type_other_text,
    services_offered: formData.services_offered,
    business_address_street: formData.business_address_street,
    business_address_barangay: formData.business_address_barangay,
    business_address_city: formData.business_address_city,
    business_address_province: formData.business_address_province,
    business_address_postal: formData.business_address_postal,
    authorized_rep_fname: formData.authorized_rep_fname,
    authorized_rep_mname: formData.authorized_rep_mname,
    authorized_rep_lname: formData.authorized_rep_lname,
    company_website: formData.company_website,
    company_social_media: formData.company_social_media,
  };
}

export function buildContractorStep2FormData(formData: any): FormData {
  const fd = new FormData();
  const docKeys = [
    'picab_number', 'picab_category', 'picab_expiration_date',
    'business_permit_number', 'business_permit_city', 'business_permit_expiration',
    'tin_business_reg_number', 'dti_sec_registration_photo',
  ];
  docKeys.forEach((k) => {
    const v = formData[k];
    if (v === undefined || v === null) return;
    if (k === 'dti_sec_registration_photo' && typeof v === 'string' && v.startsWith('file://')) {
      fd.append(k, { uri: v, name: 'dti_sec_registration.jpg', type: 'image/jpeg' } as any);
    } else {
      fd.append(k, v as any);
    }
  });
  return fd;
}

export function buildContractorFinalBody(formData: any, provinces: any[] = [], cities: any[] = [], barangays: any[] = {}) {
  const provinceName = provinces.find(p => `${p.code}` === `${formData.business_address_province}`)?.name || '';
  const cityName = cities.find(c => `${c.code}` === `${formData.business_address_city}`)?.name || '';
  const barangayName = (barangays || []).find((b: any) => `${b.code}` === `${formData.business_address_barangay}`)?.name || '';
  const business_address = [
    formData.business_address_street || '',
    barangayName || '',
    cityName || '',
    provinceName || ''
  ].filter(Boolean).join(', ') + (formData.business_address_postal ? ` ${formData.business_address_postal}` : '');

  const step1_data = {
    company_name: formData.company_name,
    company_phone: formData.company_phone,
    years_of_experience: computeYears(formData.experience_start_date),
    type_id: formData.contractor_type_id,
    contractor_type_other: formData.contractor_type_other_text,
    services_offered: formData.services_offered,
    business_address,
    company_website: formData.company_website,
    company_social_media: formData.company_social_media,
    authorized_rep_fname: formData.authorized_rep_fname,
    authorized_rep_mname: formData.authorized_rep_mname,
    authorized_rep_lname: formData.authorized_rep_lname,
  };

  const step2_data = {
    picab_number: formData.picab_number,
    picab_category: formData.picab_category,
    picab_expiration_date: formData.picab_expiration_date,
    business_permit_number: formData.business_permit_number,
    business_permit_city: formData.business_permit_city,
    business_permit_expiration: formData.business_permit_expiration,
    tin_business_reg_number: formData.tin_business_reg_number,
    dti_sec_registration_photo: formData.dti_sec_registration_photo_server || undefined,
    first_name: formData.authorized_rep_fname,
    middle_name: formData.authorized_rep_mname,
    last_name: formData.authorized_rep_lname,
  };

  return { step1_data, step2_data };
}

export function validateOwnerStep1(formData: any, dropdowns: any): string[] {
  const errors: string[] = [];
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
  return errors;
}

export function buildOwnerStep1Payload(formData: any, provinces: any[] = [], cities: any[] = [], barangays: any[] = []) {
  const provinceName = provinces.find(p => `${p.code}` === `${formData.owner_address_province}`)?.name || '';
  const cityName = cities.find(c => `${c.code}` === `${formData.owner_address_city}`)?.name || '';
  const barangayName = barangays.find(b => `${b.code}` === `${formData.owner_address_barangay}`)?.name || '';
  const address = [
    formData.owner_address_street || '',
    barangayName || '',
    cityName || '',
    provinceName || ''
  ].filter(Boolean).join(', ') + (formData.owner_address_postal ? ` ${formData.owner_address_postal}` : '');

  return {
    first_name: formData.first_name,
    middle_name: formData.middle_name,
    last_name: formData.last_name,
    occupation_id: formData.occupation_id,
    occupation_other_text: formData.occupation_other_text,
    date_of_birth: formData.date_of_birth,
    phone_number: formData.phone_number,
    owner_address_street: formData.owner_address_street,
    owner_address_barangay: formData.owner_address_barangay,
    owner_address_city: formData.owner_address_city,
    owner_address_province: formData.owner_address_province,
    owner_address_postal: formData.owner_address_postal,
    address,
  };
}

export function buildOwnerFinalBody(formData: any, provinces: any[] = [], ownerCities: any[] = [], ownerBarangays: any[] = []) {
  const provinceName = provinces.find(p => `${p.code}` === `${formData.owner_address_province}`)?.name || '';
  const cityName = ownerCities.find(c => `${c.code}` === `${formData.owner_address_city}`)?.name || '';
  const barangayName = ownerBarangays.find(b => `${b.code}` === `${formData.owner_address_barangay}`)?.name || '';
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

  return {
    owner_step1_data: {
      first_name: formData.first_name,
      middle_name: formData.middle_name,
      last_name: formData.last_name,
      occupation_id: formData.occupation_id,
      occupation_other: formData.occupation_other_text,
      date_of_birth: formData.date_of_birth,
      phone_number: formData.phone_number,
      address: address,
      age: computeYears(formData.date_of_birth),
    },
    switch_step2_data: Object.keys(savedDocs).length ? { saved: savedDocs } : undefined,
  };
}

export default {};
