import { api_config, api_request, getCsrfToken } from '../config/api';

// Type definitions based on Laravel backend forms
export interface login_data {
  username: string; // Username or Email from backend
  password: string;
}

export interface signup_form_data {
  contractor_types: contractor_type[];
  occupations: occupation[];
  valid_ids: valid_id[];
  provinces: province[];
  picab_categories: string[];
}

export interface contractor_type {
  type_id: number;
  type_name: string;
}

export interface occupation {
  id: number;
  occupation_name: string;
}

export interface valid_id {
  id: number;
  valid_id_name?: string;
  name?: string; // API returns 'name' but we also support 'valid_id_name'
}

export interface province {
  code: string;
  name: string;
}

export interface city {
  code: string;
  name: string;
}

export interface barangay {
  code: string;
  name: string;
}

export interface api_response<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

export class auth_service {
  // Get signup form data (contractor types, occupations, etc.)
  static async get_signup_form_data(): Promise<api_response<signup_form_data>> {
    const response = await api_request(api_config.endpoints.auth.signup_form, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    // Handle nested data structure: response.data.data or response.data
    if (response.success && response.data) {
      // If data is nested (response.data.data), extract it
      if (response.data.data) {
        return {
          ...response,
          data: response.data.data
        };
      }
    }

    return response;
  }

  /**
   * Update current user's profile (username, email, profile_pic, cover_photo)
   * Expects a FormData object when uploading files
   */
  static async updateProfile(formData: FormData): Promise<api_response> {
    try {
      const response = await api_request('/api/user/profile', {
        method: 'POST',
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error updating profile:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update profile',
        status: 0,
      };
    }
  }

  // Login functionality
  static async login(credentials: login_data): Promise<api_response> {
    return await api_request(api_config.endpoints.auth.login, {
      method: 'POST',
      body: JSON.stringify(credentials),
    });
  }

  // Role selection for signup
  static async select_role(user_type: 'contractor' | 'property_owner'): Promise<api_response> {
    // On mobile we don't need to perform a server-side role switch at this step —
    // the signup flow will continue client-side and submit the chosen user_type
    // during the actual registration requests. Return a successful response
    // so the UI can proceed like the web flow.
    return { success: true, status: 200, data: { user_type }, message: 'Role selected' };
  }

  // Get provinces directly from PSGC API (bypassing backend for consistent codes)
  static async get_provinces(): Promise<api_response<province[]>> {
    try {
      console.log('Fetching provinces from PSGC API...');
      const response = await fetch('https://psgc.gitlab.io/api/provinces/');
      if (response.ok) {
        const data = await response.json();
        // Sort by name and map to simpler format
        const provinces = data
          .map((p: any) => ({ code: p.code, name: p.name }))
          .sort((a: province, b: province) => a.name.localeCompare(b.name));
        console.log('Loaded', provinces.length, 'provinces from PSGC API');
        return { success: true, data: provinces, status: 200 };
      }
      return { success: false, data: [], status: response.status, message: 'Failed to fetch provinces' };
    } catch (error) {
      console.error('PSGC provinces fetch error:', error);
      return { success: false, data: [], status: 0, message: 'Network error' };
    }
  }

  // Get cities by province directly from PSGC API
  static async get_cities_by_province(province_code: string): Promise<api_response<city[]>> {
    try {
      console.log('Fetching cities for province:', province_code);
      const response = await fetch(`https://psgc.gitlab.io/api/provinces/${province_code}/cities-municipalities/`);
      if (response.ok) {
        const data = await response.json();
        // Sort by name and map to simpler format
        const cities = data
          .map((c: any) => ({ code: c.code, name: c.name }))
          .sort((a: city, b: city) => a.name.localeCompare(b.name));
        console.log('Loaded', cities.length, 'cities from PSGC API');
        return { success: true, data: cities, status: 200 };
      }
      return { success: false, data: [], status: response.status, message: 'Failed to fetch cities' };
    } catch (error) {
      console.error('PSGC cities fetch error:', error);
      return { success: false, data: [], status: 0, message: 'Network error' };
    }
  }

  // Get all cities/municipalities in the Philippines directly from PSGC API
  static async get_all_cities(): Promise<api_response<city[]>> {
    try {
      console.log('Fetching all cities/municipalities from PSGC API...');
      const response = await fetch('https://psgc.gitlab.io/api/cities-municipalities/');
      if (response.ok) {
        const data = await response.json();
        const cities = data
          .map((c: any) => ({ code: c.code, name: c.name }))
          .sort((a: city, b: city) => a.name.localeCompare(b.name));
        console.log('Loaded', cities.length, 'cities/municipalities from PSGC API');
        return { success: true, data: cities, status: 200 };
      }
      return { success: false, data: [], status: response.status, message: 'Failed to fetch cities' };
    } catch (error) {
      console.error('PSGC all cities fetch error:', error);
      return { success: false, data: [], status: 0, message: 'Network error' };
    }
  }

  // Get barangays by city from backend
  static async get_barangays_by_city(city_code: string): Promise<api_response<barangay[]>> {
    try {
      console.log('Fetching barangays for city:', city_code);
      const api_url = `${api_config.base_url}${api_config.endpoints.address.barangays(city_code)}`;
      const response = await fetch(api_url, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      if (response.ok) {
        const data = await response.json();
        // Sort by name and map to simpler format
        const barangaysData = Array.isArray(data) ? data : (data.data || data);
        const barangays = barangaysData
          .map((b: any) => ({ code: b.code, name: b.name }))
          .sort((a: barangay, b: barangay) => a.name.localeCompare(b.name));
        console.log('Loaded', barangays.length, 'barangays from backend');
        return { success: true, data: barangays, status: 200 };
      }
      return { success: false, data: [], status: response.status, message: 'Failed to fetch barangays' };
    } catch (error) {
      console.error('Barangays fetch error:', error);
      return { success: false, data: [], status: 0, message: 'Network error' };
    }
  }

  // Property Owner Registration Steps (using proper backend flow)
  static async property_owner_step1(personalInfo: any): Promise<api_response> {
    console.log('🔥 STEP 1 CALLED - Personal Info:', personalInfo);
    console.log('🔥 STEP 1 ENDPOINT:', api_config.endpoints.property_owner.step1);

    const result = await api_request(api_config.endpoints.property_owner.step1, {
      method: 'POST',
      body: JSON.stringify(personalInfo),
    });

    console.log('🔥 STEP 1 RESULT:', result);
    return result;
  }

  static async property_owner_step2(accountInfo: any): Promise<api_response> {
    // Transform confirmPassword to password_confirmation for Laravel validation
    const requestData = {
      ...accountInfo,
      password_confirmation: accountInfo.confirmPassword,
    };
    // Remove the confirmPassword field since Laravel expects password_confirmation
    delete requestData.confirmPassword;

    return await api_request(api_config.endpoints.property_owner.step2, {
      method: 'POST',
      body: JSON.stringify(requestData),
    });
  }

  /**
   * Verify Property Owner OTP
   *
   * This method fetches the CSRF token first, then sends the OTP verification request
   * with credentials (cookies) enabled.
   *
   * @param otp - The OTP code entered by the user
   * @param otpToken - Optional OTP token returned from step2 for stateless clients
   * @param email - Optional email address for fallback lookup
   * @returns Success response if OTP is valid, or descriptive error if OTP is missing/expired
   */
  static async property_owner_verify_otp(otp: string, otpToken?: string, email?: string): Promise<api_response> {
    try {
      // Validate OTP is provided
      if (!otp || otp.trim() === '') {
        return {
          success: false,
          data: null,
          status: 422,
          message: 'OTP is required. Please enter the verification code sent to your email.',
        };
      }

      // Fetch CSRF token before making the OTP verification request
      console.log('Fetching CSRF token for OTP verification...');
      await getCsrfToken();

      const body: any = { otp: otp.trim() };
      if (otpToken) body.otp_token = otpToken;
      if (email) body.email = email;

      console.log('Sending OTP verification request to:', api_config.endpoints.property_owner.verify_otp);
      const response = await api_request(api_config.endpoints.property_owner.verify_otp, {
        method: 'POST',
        body: JSON.stringify(body),
      });

      // Provide descriptive error messages based on error codes
      if (!response.success && response.data) {
        const errorCode = response.data.error_code;
        if (errorCode === 'otp_not_found') {
          return {
            ...response,
            message: 'OTP not found or expired. Please request a new verification code.',
          };
        } else if (errorCode === 'otp_expired') {
          return {
            ...response,
            message: 'Your OTP has expired. Please request a new verification code.',
          };
        } else if (errorCode === 'invalid_otp') {
          return {
            ...response,
            message: 'Invalid OTP. Please check the code and try again.',
          };
        } else if (errorCode === 'otp_identifier_missing') {
          return {
            ...response,
            message: 'Session expired. Please go back and re-enter your account information.',
          };
        }
      }

      return response;
    } catch (error) {
      console.error('OTP verification error:', error);
      return {
        success: false,
        data: null,
        status: 0,
        message: error instanceof Error ? error.message : 'Network error occurred during OTP verification.',
      };
    }
  }

  static async property_owner_step4(verificationInfo: any): Promise<api_response> {
    // Create FormData for file uploads
    const formData = new FormData();

    // Use valid_id_id directly from verificationInfo (must exist in database)
    if (!verificationInfo.valid_id_id) {
      throw new Error('valid_id_id is required and must exist in the database');
    }
    formData.append('valid_id_id', verificationInfo.valid_id_id.toString());

    // Add image files with proper React Native format
    if (verificationInfo.idFrontImage) {
      formData.append('valid_id_photo', {
        uri: verificationInfo.idFrontImage,
        type: 'image/jpeg',
        name: 'id_front.jpg',
      } as any);
    }

    if (verificationInfo.idBackImage) {
      formData.append('valid_id_back_photo', {
        uri: verificationInfo.idBackImage,
        type: 'image/jpeg',
        name: 'id_back.jpg',
      } as any);
    }

    if (verificationInfo.policeClearanceImage) {
      formData.append('police_clearance', {
        uri: verificationInfo.policeClearanceImage,
        type: 'image/jpeg',
        name: 'police_clearance.jpg',
      } as any);
    }

    return await api_request(api_config.endpoints.property_owner.step4, {
      method: 'POST',
      body: formData,
    });
  }

  static async property_owner_final(profileInfo: any = {}): Promise<api_response> {
    console.log('🔥 PROPERTY OWNER FINAL - Profile Info:', profileInfo);

    try {
      // Don't refresh CSRF token here - we should already have it from previous steps
      // Refreshing might create a new session and lose the session data
      console.log('🔥 Using existing CSRF token for final step');

      // Create FormData for file upload if profile picture exists
      const formData = new FormData();

      if (profileInfo.profileImageUri) {
        console.log('🔥 Adding profile picture to FormData');
        formData.append('profile_pic', {
          uri: profileInfo.profileImageUri,
          type: 'image/jpeg',
          name: 'profile.jpg',
        } as any);
      } else {
        console.log('🔥 No profile picture - sending empty FormData');
        // Add a dummy field to ensure FormData is not completely empty
        formData.append('skip_profile', 'true');
      }

      // Include step data for stateless/mobile clients if provided
      try {
        if (profileInfo.step1_data) {
          formData.append('step1_data', JSON.stringify(profileInfo.step1_data));
        }
        if (profileInfo.step2_data) {
          formData.append('step2_data', JSON.stringify(profileInfo.step2_data));
        }
        if (profileInfo.step4_data) {
          formData.append('step4_data', JSON.stringify({
            valid_id_id: profileInfo.step4_data.valid_id_id,
            // Don't include file URIs in JSON - they will be sent as separate files
          }));

          // Include the image files from step4_data for mobile stateless flow
          // These need to be uploaded in the final step since mobile doesn't have sessions
          if (profileInfo.step4_data.idFrontImage) {
            console.log('🔥 Adding valid_id_photo from step4_data');
            formData.append('valid_id_photo', {
              uri: profileInfo.step4_data.idFrontImage,
              type: 'image/jpeg',
              name: 'id_front.jpg',
            } as any);
          }
          if (profileInfo.step4_data.idBackImage) {
            console.log('🔥 Adding valid_id_back_photo from step4_data');
            formData.append('valid_id_back_photo', {
              uri: profileInfo.step4_data.idBackImage,
              type: 'image/jpeg',
              name: 'id_back.jpg',
            } as any);
          }
          if (profileInfo.step4_data.policeClearanceImage) {
            console.log('🔥 Adding police_clearance from step4_data');
            formData.append('police_clearance', {
              uri: profileInfo.step4_data.policeClearanceImage,
              type: 'image/jpeg',
              name: 'police_clearance.jpg',
            } as any);
          }
        }
        // Also include otp_token if present (useful when owner used token flow)
        if (profileInfo.otp_token) {
          formData.append('otp_token', profileInfo.otp_token);
        }
      } catch (err) {
        console.warn('Failed to append step data to FormData:', err);
      }

      console.log('🔥 Calling endpoint:', api_config.endpoints.property_owner.final);

      // If no profile picture, just send empty form data
      const result = await api_request(api_config.endpoints.property_owner.final, {
        method: 'POST',
        body: formData,
        // Don't set Content-Type for FormData - let the browser set it with boundary
      });

      console.log('🔥 PROPERTY OWNER FINAL RESULT:', result);
      return result;
    } catch (error) {
      console.error('🔥 PROPERTY OWNER FINAL ERROR:', error);
      return {
        success: false,
        data: null,
        status: 0,
        message: error instanceof Error ? error.message : 'Network error occurred',
      };
    }
  }

  // Contractor Registration Steps
  static async contractor_step1(companyInfo: any): Promise<api_response> {
    // Transform camelCase to snake_case to match Laravel backend expectations
    // Clean phone number: remove spaces, dashes, and ensure it's exactly 11 digits starting with 09
    let cleanedPhone = companyInfo.companyPhone.replace(/\s+/g, '').replace(/-/g, '');

    const requestData: any = {
      company_name: companyInfo.companyName,
      company_phone: cleanedPhone,
      founded_date: companyInfo.foundedDate || '', // ISO date (YYYY-MM-DD)
      contractor_type_id: parseInt(companyInfo.contractorTypeId) || 0, // Convert to integer as required by backend
      contractor_type_other_text: companyInfo.contractorTypeOtherText || null,
      services_offered: companyInfo.servicesOffered,
      business_address_street: companyInfo.businessAddressStreet,
      business_address_province: companyInfo.businessAddressProvince,
      business_address_city: companyInfo.businessAddressCity,
      business_address_barangay: companyInfo.businessAddressBarangay,
      business_address_postal: companyInfo.businessAddressPostal,
    };

    // Only include company_website if it's a valid URL or empty (backend expects nullable|url)
    if (companyInfo.companyWebsite && companyInfo.companyWebsite.trim() !== '') {
      // Ensure URL has protocol
      let websiteUrl = companyInfo.companyWebsite.trim();
      if (!websiteUrl.startsWith('http://') && !websiteUrl.startsWith('https://')) {
        websiteUrl = 'https://' + websiteUrl;
      }
      requestData.company_website = websiteUrl;
    }

    // Only include company_social_media if it has a value
    if (companyInfo.companySocialMedia && companyInfo.companySocialMedia.trim() !== '') {
      requestData.company_social_media = companyInfo.companySocialMedia.trim();
    }

    return await api_request(api_config.endpoints.contractor.step1, {
      method: 'POST',
      body: JSON.stringify(requestData),
    });
  }

  static async contractor_step2(accountInfo: any): Promise<api_response> {
    // Transform camelCase to snake_case to match Laravel backend expectations
    const requestData = {
      first_name: accountInfo.firstName,
      middle_name: accountInfo.middleName || null,
      last_name: accountInfo.lastName,
      username: accountInfo.username,
      company_email: accountInfo.companyEmail,
      password: accountInfo.password,
      password_confirmation: accountInfo.confirmPassword, // Laravel expects password_confirmation for 'confirmed' validation
    };

    return await api_request(api_config.endpoints.contractor.step2, {
      method: 'POST',
      body: JSON.stringify(requestData),
    });
  }

  static async contractor_verify_otp(otp: string, companyEmail?: string): Promise<api_response> {
    const body: any = { otp };
    if (companyEmail) body.company_email = companyEmail;
    return await api_request(api_config.endpoints.contractor.verify_otp, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }

  static async contractor_step4(documentsInfo: any): Promise<api_response> {
    // Create FormData for file upload
    const formData = new FormData();

    // Add all the business document fields based on backend requirements
    formData.append('picab_number', documentsInfo.picabNumber || '');
    formData.append('picab_category', documentsInfo.picabCategory || '');
    formData.append('picab_expiration_date', documentsInfo.picabExpirationDate || '');
    formData.append('business_permit_number', documentsInfo.businessPermitNumber || '');
    formData.append('business_permit_city', documentsInfo.businessPermitCity || '');
    formData.append('business_permit_expiration', documentsInfo.businessPermitExpiration || '');
    formData.append('tin_business_reg_number', documentsInfo.tinBusinessRegNumber || '');

    // Add DTI/SEC registration photo (required file upload)
    if (documentsInfo.dtiSecRegistrationPhoto) {
      formData.append('dti_sec_registration_photo', {
        uri: documentsInfo.dtiSecRegistrationPhoto,
        type: 'image/jpeg',
        name: 'dti_sec_registration.jpg',
      } as any);
    }

    return await api_request(api_config.endpoints.contractor.step4, {
      method: 'POST',
      body: formData,
    });
  }

  static async contractor_final(payload: any = {}): Promise<api_response> {
    // payload may include: companyInfo, accountInfo, documentsInfo, profileInfo
    console.log('🔥 CONTRACTOR FINAL - Payload:', payload);

    try {
      const { companyInfo, accountInfo, documentsInfo, profileInfo } = payload;

      // Build FormData including step data so stateless/mobile clients don't rely on server session
      const formData = new FormData();

      // Attach JSON-encoded step data
      if (companyInfo) {
        formData.append('step1_data', JSON.stringify({
          company_name: companyInfo.companyName,
          company_phone: companyInfo.companyPhone,
          founded_date: companyInfo.foundedDate || '',
          contractor_type_id: companyInfo.contractorTypeId || 0,
          contractor_type_other: companyInfo.contractorTypeOtherText || null,
          services_offered: companyInfo.servicesOffered || '',
          business_address_street: companyInfo.businessAddressStreet || '',
          business_address_province: companyInfo.businessAddressProvince || '',
          business_address_city: companyInfo.businessAddressCity || '',
          business_address_barangay: companyInfo.businessAddressBarangay || '',
          business_address_postal: companyInfo.businessAddressPostal || '',
          company_website: companyInfo.companyWebsite || null,
          company_social_media: companyInfo.companySocialMedia || null,
        }));
      }

      if (accountInfo) {
        formData.append('step2_data', JSON.stringify({
          first_name: accountInfo.firstName,
          middle_name: accountInfo.middleName || null,
          last_name: accountInfo.lastName,
          username: accountInfo.username,
          company_email: accountInfo.companyEmail,
          password: accountInfo.password,
          password_confirmation: accountInfo.confirmPassword
        }));
        // If OTP token was returned earlier, include it so server can lookup OTP hash
        if (accountInfo.otpToken) {
          formData.append('otp_token', accountInfo.otpToken);
        }
      }

      if (documentsInfo) {
        formData.append('step4_data', JSON.stringify({
          picab_number: documentsInfo.picabNumber || '',
          picab_category: documentsInfo.picabCategory || '',
          picab_expiration_date: documentsInfo.picabExpirationDate || '',
          business_permit_number: documentsInfo.businessPermitNumber || '',
          business_permit_city: documentsInfo.businessPermitCity || '',
          business_permit_expiration: documentsInfo.businessPermitExpiration || '',
          tin_business_reg_number: documentsInfo.tinBusinessRegNumber || ''
        }));

        // Attach DTI/SEC photo if present
        if (documentsInfo.dtiSecRegistrationPhoto) {
          formData.append('dti_sec_registration_photo', {
            uri: documentsInfo.dtiSecRegistrationPhoto,
            type: 'image/jpeg',
            name: 'dti_sec_registration.jpg',
          } as any);
        }
      }

      // Profile picture (optional)
      if (profileInfo && profileInfo.profileImageUri) {
        formData.append('profile_pic', {
          uri: profileInfo.profileImageUri,
          type: 'image/jpeg',
          name: 'profile.jpg',
        } as any);
      } else {
        // Ensure we send at least one field to avoid empty body issues
        formData.append('skip_profile', 'true');
      }

      console.log('🔥 Calling endpoint:', api_config.endpoints.contractor.final);

      const result = await api_request(api_config.endpoints.contractor.final, {
        method: 'POST',
        body: formData,
      });

      console.log('🔥 CONTRACTOR FINAL RESULT:', result);
      return result;
    } catch (error) {
      console.error('🔥 CONTRACTOR FINAL ERROR:', error);
      return {
        success: false,
        data: null,
        status: 0,
        message: error instanceof Error ? error.message : 'Network error occurred',
      };
    }
  }
}
