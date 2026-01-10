// API configuration for connecting to Laravel backend
const API_BASE_URL = 'http://10.236.216.46:8000';

import { storage_service } from '../utils/storage';

export const api_config = {
    base_url: API_BASE_URL,
    endpoints: {
        auth: {
            login: '/api/login',
            signup_form: '/api/signup-form',
        },
        contractor: {
            step1: '/accounts/signup/contractor/step1',
            step2: '/accounts/signup/contractor/step2',
            verify_otp: '/accounts/signup/contractor/step3/verify-otp',
            step4: '/accounts/signup/contractor/step4',
            final: '/accounts/signup/contractor/final',
        },
        property_owner: {
            step1: '/accounts/signup/owner/step1',
            step2: '/accounts/signup/owner/step2',
            verify_otp: '/accounts/signup/owner/step3/verify-otp',
            step4: '/accounts/signup/owner/step4',
            final: '/accounts/signup/owner/final',
        },
        address: {
            provinces: '/api/psgc/provinces',
            cities: (province_code: string) => `/api/psgc/provinces/${province_code}/cities`,
            barangays: (city_code: string) => `/api/psgc/cities/${city_code}/barangays`,
        },
        contractors: {
            list: '/api/contractors',
        }
    }
};

export const get_api_url = (endpoint: string) => `${API_BASE_URL}${endpoint}`;

let csrfToken: string | null = null;

export const getCsrfToken = async (): Promise<string | null> => {
    try {
        console.log('Fetching CSRF token from:', `${API_BASE_URL}/sanctum/csrf-cookie`);
        const response = await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
            method: 'GET',
            credentials: 'include',
        });

        console.log('CSRF response status:', response.status);

        if (response.ok) {
            const setCookieHeader = response.headers.get('set-cookie');
            if (setCookieHeader) {
                const tokenMatch = setCookieHeader.match(/XSRF-TOKEN=([^;]+)/);
                if (tokenMatch) {
                    csrfToken = decodeURIComponent(tokenMatch[1]);
                    console.log('CSRF token extracted from header:', csrfToken);
                    return csrfToken;
                }
            }
        }
    } catch (error) {
        console.log('CSRF token fetch failed:', error);
    }
    return null;
};

export const api_request = async (endpoint: string, options: RequestInit = {}) => {
    const url = get_api_url(endpoint);
    console.log(`Making API request to: ${url}`);

    try {
        const savedToken = await storage_service.get_auth_token();
        if (savedToken) {
            options.headers = {
                ...(options.headers || {}),
                'Authorization': `Bearer ${savedToken}`,
            } as any;
            console.log('Auth token present (masked):', `${savedToken.substring(0, 8)}...`);
        }
    } catch (e) {
        console.warn('Could not read auth token from storage:', e);
    }

    const reqMethod = ((options.method || 'GET') as string).toString().toUpperCase();
    if (reqMethod !== 'GET') {
        if (!csrfToken) {
            console.log('Getting CSRF token for non-GET request to:', endpoint);
            await getCsrfToken();
        }
        console.log('Using CSRF token for non-GET:', csrfToken ? 'Present' : 'Missing');
    }

    const default_headers: any = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0',
    };

    if (!(options.body instanceof FormData)) {
        default_headers['Content-Type'] = 'application/json';
    }

    if (csrfToken) {
        default_headers['X-XSRF-TOKEN'] = csrfToken;
    }

    const config: RequestInit = {
        ...options,
        credentials: 'include',
        headers: {
            ...default_headers,
            ...options.headers,
        },
    };

    try {
        const method = (config.method || 'GET').toString().toUpperCase();
        let fetchUrl = url;
        if (method === 'GET') {
            const sep = fetchUrl.includes('?') ? '&' : '?';
            fetchUrl = `${fetchUrl}${sep}_cb=${Date.now()}`;
        }

        const response = await fetch(fetchUrl, config);
        console.log(`Response status: ${response.status}`);

        const response_text = await response.text();
        console.log('Raw response:', response_text.substring(0, 500));

        if (!response_text.trim()) throw new Error('Empty response from server');

        let data;
        try {
            data = JSON.parse(response_text);
        } catch (json_error) {
            console.error('JSON parse error:', json_error);
            if (response_text.trim().startsWith('<')) {
                throw new Error('Server returned HTML instead of JSON. Check if Laravel backend is running correctly.');
            } else {
                throw new Error(`Invalid JSON response: ${json_error instanceof Error ? json_error.message : 'Unknown parsing error'}`);
            }
        }

        return { success: response.ok, data, status: response.status, message: data?.message };
    } catch (error) {
        console.error('API Request Error:', error);
        return { success: false, data: null, status: 0, message: error instanceof Error ? error.message : 'Network error occurred' };
    }
};