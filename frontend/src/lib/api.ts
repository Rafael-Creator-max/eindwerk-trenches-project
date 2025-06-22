import axios, { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios';

// Debug log environment variables
console.log('Environment:', {
  NODE_ENV: process.env.NODE_ENV,
  NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL,
  NEXT_PUBLIC_APP_ENV: process.env.NEXT_PUBLIC_APP_ENV
});

// Determine the base URL
const getBaseUrl = () => {
  // Always use the full URL in development to avoid CORS issues with DDEV
  if (process.env.NEXT_PUBLIC_APP_ENV === 'development') {
    return process.env.NEXT_PUBLIC_API_URL || 'https://backend.ddev.site';
  }
  
  // In production, use relative URLs
  return '';
};

const baseURL = getBaseUrl();
console.log('Using API baseURL:', baseURL);

const api = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  timeout: 10000, // 10 seconds timeout
});

// Add request interceptor for logging
api.interceptors.request.use(
  (config) => {
    console.log(`[${config.method?.toUpperCase()}]`, config.url);
    return config;
  },
  (error) => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Request interceptor to add CSRF token if it exists
api.interceptors.request.use(
  async (config) => {
    // Skip for these endpoints to avoid infinite loops
    if (config.url?.includes('/sanctum/csrf-cookie')) {
      return config;
    }

    // Get the token from cookies
    const cookies = document.cookie.split('; ');
    const xsrfToken = cookies.find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1];
    
    if (xsrfToken) {
      const decodedToken = decodeURIComponent(xsrfToken);
      config.headers['X-XSRF-TOKEN'] = decodedToken;
      config.headers['X-Requested-With'] = 'XMLHttpRequest';
      console.log(`[API] Added X-XSRF-TOKEN to ${config.method?.toUpperCase()} ${config.url}`);
    } else {
      console.log(`[API] No XSRF-TOKEN found for ${config.method?.toUpperCase()} ${config.url}`);
    }
    
    return config;
  },
  (error) => {
    console.error('[API] Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Helper function to safely extract error details
const getErrorDetails = (error: any): Record<string, unknown> => {
  if (!error || typeof error !== 'object') {
    return { value: error };
  }

  if (error.isAxiosError) {
    const { config, request, response, ...rest } = error;
    return {
      isAxiosError: true,
      message: error.message,
      code: error.code,
      config: config ? { url: config.url, method: config.method } : undefined,
      status: response?.status,
      statusText: response?.statusText,
      data: response?.data,
      ...rest,
    };
  }
  
  return { message: error.message, stack: error.stack };
};

// Response interceptor for better error handling
api.interceptors.response.use(
  (response) => {
    console.log('[API] Response:', {
      method: response.config.method?.toUpperCase(),
      url: response.config.url,
      status: response.status,
      statusText: response.statusText,
    });
    return response;
  },
  async (error) => {
    const errorDetails = getErrorDetails(error);
    console.error('[API] Request failed:', errorDetails);
    
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      console.log('[API] Unauthorized - User might need to log in');
      
      // If this is a retry after a token refresh, don't try again
      if (error.config._retry) {
        console.log('[API] Already retried request, redirecting to login');
        // Clear any existing tokens
        document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        // Redirect to login
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
      
      // Try to refresh the token
      error.config._retry = true;
      try {
        console.log('[API] Attempting to refresh token...');
        const token = await getCSRFToken();
        
        if (token) {
          console.log('[API] Token refreshed, retrying original request');
          // Update the token in the headers
          error.config.headers['X-XSRF-TOKEN'] = token;
          return api(error.config);
        } else {
          throw new Error('Failed to refresh CSRF token');
        }
      } catch (refreshError) {
        console.error('[API] Token refresh failed:', refreshError);
        // Clear any existing tokens
        document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        // Redirect to login
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
        return Promise.reject(refreshError);
      }
    }
    
    // Handle other errors
    return Promise.reject(error);
  }
);

// Ensures CSRF token is set for Sanctum
export const getCSRFToken = async (): Promise<string | null> => {
  try {
    const response = await axios.get('/sanctum/csrf-cookie', {
      withCredentials: true,
      baseURL: process.env.NEXT_PUBLIC_API_URL || 'https://backend.ddev.site'
    });
    
    // Get the token from cookies
    const cookies = document.cookie.split('; ');
    const xsrfCookie = cookies.find(row => row.startsWith('XSRF-TOKEN='));
    
    if (xsrfCookie) {
      const token = decodeURIComponent(xsrfCookie.split('=')[1]);
      console.log('[API] Retrieved CSRF token');
      return token;
    }
    
    console.warn('[API] No XSRF-TOKEN found in cookies after request');
    return null;
  } catch (error) {
    console.error('[API] Failed to get CSRF token:', error);
    return null;
  }
};

export default api;
