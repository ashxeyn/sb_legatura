// API configuration for connecting to Laravel backend
const API_BASE_URL = 'http://192.168.254.111:8086'; //'https://legaturaph.com'

import { storage_service } from '../utils/storage';

export const api_config = {
    base_url: API_BASE_URL,
    endpoints: {
        auth: {
            login: '/api/login',
            signup_form: '/api/signup-form',
            force_change_password: '/api/force-change-password',
        },
        contractor: {
            step1: '/api/signup/contractor/step1',
            step2: '/api/signup/contractor/step2',
            verify_otp: '/api/signup/contractor/step3/verify-otp',
            step4: '/api/signup/contractor/step4',
            final: '/api/signup/contractor/final',
        },
        property_owner: {
            step1: '/api/signup/owner/step1',
            step2: '/api/signup/owner/step2',
            verify_otp: '/api/signup/property-owner/step3/verify-otp',
            step4: '/api/signup/owner/step4',
            final: '/api/signup/owner/final',
        },
        address: {
            provinces: '/api/psgc/provinces',
            cities: (province_code: string) => `/api/psgc/provinces/${province_code}/cities`,
            barangays: (city_code: string) => `/api/psgc/cities/${city_code}/barangays`,
        },
        contractors: {
            list: '/api/contractors',
        }
        ,
        contractor_members: {
            list: '/api/contractor/members',
            create: '/api/contractor/members',
            update: (id: string) => `/api/contractor/members/${id}`,
            delete: (id: string) => `/api/contractor/members/${id}`,
            toggle_active: (id: string) => `/api/contractor/members/${id}/toggle-active`
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
            // In browser environments the cookie `XSRF-TOKEN` will be set by Laravel
            // but `Set-Cookie` is not exposed via fetch() response headers. Attempt
            // to read it from `document.cookie` when available (web). In React
            // Native (Expo) there is no `document.cookie`, so we avoid relying on
            // JS access to cookies and simply return null — the cookie will still
            // be stored by the fetch call if `credentials: 'include'` is honored.
            try {
                if (typeof document !== 'undefined' && document && document.cookie) {
                    const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                    if (cookieMatch) {
                        csrfToken = decodeURIComponent(cookieMatch[1]);
                        console.log('CSRF token extracted from document.cookie:', csrfToken);
                        return csrfToken;
                    }
                }
            } catch (e) {
                console.log('Could not read document.cookie:', e);
            }

            // Some server setups may expose the token header; try common header names.
            const headerToken = response.headers.get('x-xsrf-token') || response.headers.get('X-XSRF-TOKEN');
            if (headerToken) {
                csrfToken = headerToken;
                console.log('CSRF token extracted from response header:', csrfToken);
                return csrfToken;
            }

            console.log('CSRF cookie not accessible from JS environment; proceeding without CSRF token.');
        }
    } catch (error) {
        console.log('CSRF token fetch failed:', error);
    }
    return null;
};

export const api_request = async (endpoint: string, options: RequestInit = {}) => {
    const url = get_api_url(endpoint);
    console.log(`Making API request to: ${url}`);

    let hasAuthToken = false;
    try {
        const savedToken = await storage_service.get_auth_token();
        if (savedToken) {
            hasAuthToken = true;
            options.headers = {
                ...(options.headers || {}),
                'Authorization': `Bearer ${savedToken}`,
            } as any;
            console.log('Auth token present (masked):', `${savedToken.substring(0, 8)}...`);
        }
        // Include stored user id for backend routing if available
        try {
            const savedUser = await storage_service.get_user_data();
            if (savedUser) {
                const uid = savedUser.user_id || (savedUser.id as any) || (savedUser.user && savedUser.user.id) || savedUser.username || null;
                if (uid) {
                    options.headers = {
                        ...(options.headers || {}),
                        'X-User-Id': String(uid),
                    } as any;
                    console.log('Added X-User-Id header');
                }
            }
        } catch (e) {
            console.warn('Could not read user data from storage:', e);
        }
    } catch (e) {
        console.warn('Could not read auth token from storage:', e);
    }

    const reqMethod = ((options.method || 'GET') as string).toString().toUpperCase();
    // If we already have a Bearer token, we don't need to fetch Sanctum CSRF cookie
    if (reqMethod !== 'GET') {
        if (!csrfToken && !hasAuthToken) {
            console.log('Getting CSRF token for non-GET request to:', endpoint);
            await getCsrfToken();
        }
        console.log('Using CSRF token for non-GET:', csrfToken ? 'Present' : (hasAuthToken ? 'Skipped (Bearer token present)' : 'Missing'));
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

    const credentialsMode = hasAuthToken ? 'omit' : 'include';
    // If using Bearer token, don't send cookies (prevents CSRF middleware from triggering)
    const config: RequestInit = {
        ...options,
        credentials: credentialsMode as RequestCredentials,
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
