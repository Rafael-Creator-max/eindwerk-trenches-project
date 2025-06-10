import api from './api';

export type User = {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  two_factor_confirmed_at?: string | null;
  current_team_id?: number | null;
  profile_photo_path?: string | null;
  created_at: string;
  updated_at: string;
  profile_photo_url?: string;
};

type AuthResponse = {
  user: User;
};

// Get CSRF token from the server
export const getCSRFToken = async (): Promise<void> => {
  const url = '/sanctum/csrf-cookie';
  console.log(`[CSRF] Fetching CSRF token from: ${api.defaults.baseURL}${url}`);
  
  try {
    // First, clear any existing XSRF-TOKEN
    document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    
    const response = await api.get(url, {
      withCredentials: true,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      validateStatus: (status) => status >= 200 && status < 300, // Only consider 2xx as success
    });
    
    console.log('[CSRF] Response status:', response.status);
    console.log('[CSRF] Response headers:', JSON.stringify(response.headers, null, 2));
    console.log('[CSRF] Set-Cookie header:', response.headers['set-cookie']);
    
    // Check if the XSRF-TOKEN cookie was set
    const cookies = document.cookie.split(';').map(c => c.trim());
    console.log('[CSRF] Current cookies:', cookies);
    
    const xsrfToken = cookies.find(cookie => cookie.startsWith('XSRF-TOKEN='));
    
    if (!xsrfToken) {
      console.warn('[CSRF] XSRF-TOKEN cookie not found after request');
      // Try to extract from response headers as fallback
      const setCookieHeader = response.headers['set-cookie'] || [];
      const xsrfCookie = setCookieHeader.find((c: string) => c.includes('XSRF-TOKEN='));
      
      if (xsrfCookie) {
        console.log('[CSRF] Found XSRF-TOKEN in Set-Cookie header');
        // Manually set the cookie
        document.cookie = xsrfCookie.split(';')[0] + '; Path=/';
      } else {
        console.warn('[CSRF] XSRF-TOKEN not found in Set-Cookie header');
      }
    } else {
      console.log('[CSRF] XSRF-TOKEN found in cookies:', xsrfToken.split('=')[1].substring(0, 10) + '...');
    }
    
    return;
    
  } catch (error: any) {
    const errorDetails = {
      message: error.message,
      code: error.code,
      name: error.name,
      stack: error.stack,
      config: {
        url: error.config?.url,
        baseURL: error.config?.baseURL,
        withCredentials: error.config?.withCredentials,
        headers: error.config?.headers,
      },
      response: error.response ? {
        status: error.response.status,
        statusText: error.response.statusText,
        headers: error.response.headers,
        data: error.response.data,
      } : undefined,
    };
    
    console.error('[CSRF] Failed to get CSRF token. Details:', JSON.stringify(errorDetails, null, 2));
    
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      throw new Error(`Failed to get CSRF token: ${error.response.status} ${error.response.statusText}`);
    } else if (error.code === 'ECONNABORTED') {
      throw new Error('Request to get CSRF token timed out. Is the backend server running?');
    } else if (error.request) {
      // The request was made but no response was received
      throw new Error('No response received from server while fetching CSRF token. Check: ' +
        '1. Is the backend server running? ' +
        '2. Is CORS properly configured on the backend? ' +
        '3. Are you using the correct backend URL?');
    } else {
      // Something happened in setting up the request that triggered an Error
      throw new Error(`Error setting up CSRF token request: ${error.message}`);
    }
  }
};

export const login = async (email: string, password: string): Promise<User> => {
  try {
    console.log('[Auth] Starting login process...');
    
    // First, get the CSRF token
    console.log('[Auth] Getting CSRF token...');
    await getCSRFToken();
    
    // Perform the login
    console.log('[Auth] Sending login request...');
    const response = await api.post<AuthResponse>('/login', {
      email,
      password,
      remember: true,
    }, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });
    
    console.log('[Auth] Login response:', {
      status: response.status,
      statusText: response.statusText,
      headers: response.headers,
    });
    
    // Get the user data
    console.log('[Auth] Fetching user data...');
    const userResponse = await api.get<User>('/api/user', {
      headers: {
        'Accept': 'application/json',
      },
    });
    
    console.log('[Auth] User data received:', userResponse.data);
    return userResponse.data;
    
  } catch (error: any) {
    console.error('[Auth] Login error:', {
      message: error.message,
      code: error.code,
      response: error.response ? {
        status: error.response.status,
        statusText: error.response.statusText,
        data: error.response.data,
      } : 'No response',
    });
    
    let errorMessage = 'Failed to log in';
    
    if (error.response) {
      if (error.response.status === 422) {
        // Validation error
        const errors = error.response.data.errors;
        errorMessage = Object.values(errors).flat().join('\n');
      } else if (error.response.data?.message) {
        errorMessage = error.response.data.message;
      }
    } else if (error.request) {
      errorMessage = 'No response from server. Please check your connection.';
    }
    
    throw new Error(errorMessage);
  }
};

export const register = async (name: string, email: string, password: string, password_confirmation: string): Promise<User> => {
  try {
    await getCSRFToken();
    await api.post('/register', {
      name,
      email,
      password,
      password_confirmation,
    });
    
    // After registration, log the user in
    const userResponse = await api.get<User>('/api/user');
    return userResponse.data;
  } catch (error: any) {
    console.error('Registration error:', error);
    const errorMessage = error.response?.data?.message || 'Failed to create an account';
    throw new Error(errorMessage);
  }
};

export const logout = async (): Promise<void> => {
  try {
    await getCSRFToken();
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
    throw error;
  } finally {
    localStorage.removeItem('auth_token');
  }
};

// Cache to prevent multiple simultaneous requests
let userRequest: Promise<User | null> | null = null;

export const getCurrentUser = async (): Promise<User | null> => {
  console.log('[Auth] Getting current user...');
  // Return the existing request if it's in progress to prevent duplicate requests
  if (userRequest) {
    console.log('[Auth] Using existing user request');
    return userRequest;
  }

  try {
    userRequest = (async () => {
      try {
        // First ensure we have a CSRF token
        try {
          await getCSRFToken();
        } catch (csrfError) {
          console.warn('[Auth] CSRF token fetch failed, but continuing with user check:', csrfError);
          // Continue anyway, as the user might already be authenticated
        }
        
        console.log('[Auth] Fetching current user...');
        const response = await api.get<User>('/api/user', {
          // Don't retry on 401 to prevent infinite loops
          validateStatus: (status) => status === 200 || status === 401,
        });
        
        if (response.status === 200) {
          console.log('[Auth] Current user:', response.data);
          return response.data;
        } else if (response.status === 401) {
          console.log('[Auth] No authenticated user found');
          return null;
        } else {
          console.warn('[Auth] Unexpected status code when fetching user:', response.status);
          return null;
        }
      } catch (error: any) {
        console.error('[Auth] Failed to get current user:', {
          message: error.message,
          code: error.code,
          status: error.response?.status,
          statusText: error.response?.statusText,
          url: error.config?.url,
        });
        return null;
      } finally {
        // Clear the request cache when done
        userRequest = null;
      }
    })();

    return await userRequest;
  } catch (error) {
    console.error('[Auth] Unexpected error in getCurrentUser:', error);
    userRequest = null;
    return null;
  }
};

export const isAuthenticated = async (): Promise<boolean> => {
  try {
    const user = await getCurrentUser();
    return !!user;
  } catch (error) {
    console.error('Authentication check failed:', error);
    return false;
  }
};
