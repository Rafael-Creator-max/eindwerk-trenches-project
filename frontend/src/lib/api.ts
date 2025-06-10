import axios, { AxiosError } from 'axios';

// Debug log environment variables
console.log('Environment:', {
  NODE_ENV: process.env.NODE_ENV,
  NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL,
  NEXT_PUBLIC_APP_ENV: process.env.NEXT_PUBLIC_APP_ENV
});

// Determine the base URL
const getBaseUrl = () => {
  // If NEXT_PUBLIC_API_URL is set, use it
  if (process.env.NEXT_PUBLIC_API_URL) {
    return process.env.NEXT_PUBLIC_API_URL;
  }
  
  // Otherwise, use default based on environment
  return process.env.NODE_ENV === 'production' 
    ? 'https://backend.ddev.site' 
    : 'http://localhost:8000';
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

// Request interceptor to add auth token if it exists
api.interceptors.request.use(
  async (config) => {
    console.log('Making request to:', config.url);
    
    // Get the token from the cookie
    const token = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];
      
    if (token) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
      console.log('Added XSRF-TOKEN to request headers');
    } else {
      console.log('No XSRF-TOKEN found in cookies');
    }
    
    return config;
  },
  (error) => {
    console.error('Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Helper function to safely extract error details without causing circular references
const getErrorDetails = (error: any): Record<string, unknown> => {
  // Handle non-error values
  if (!error || typeof error !== 'object') {
    return { value: error };
  }

  // Handle circular references by tracking seen objects
  const seen = new WeakSet();
  
  const safeStringify = (obj: any, space = 2): string => {
    const replacer = (key: string, value: any) => {
      // Skip certain keys that might cause issues
      if (key === 'config' && value?.headers) {
        const { headers, ...rest } = value;
        return {
          ...rest,
          headers: '[REDACTED]',
        };
      }
      
      // Handle circular references
      if (typeof value === 'object' && value !== null) {
        if (seen.has(value)) return '[Circular]';
        seen.add(value);
      }
      
      // Handle special cases
      if (value instanceof Error) {
        const errorObj: Record<string, any> = {};
        Object.getOwnPropertyNames(value).forEach(key => {
          errorObj[key] = (value as any)[key];
        });
        return errorObj;
      }
      
      return value;
    };
    
    try {
      return JSON.stringify(obj, replacer, space);
    } catch (e) {
      return `[Error stringifying: ${String(e)}]`;
    }
  };
  
  // Special handling for Axios errors
  if (error.isAxiosError) {
    const { config, request, response, ...rest } = error;
    
    // Safely extract config details
    let configDetails = {};
    if (config) {
      const { headers, ...restConfig } = config;
      configDetails = {
        ...restConfig,
        headers: '[REDACTED]',
      };
    }
    
    // Safely extract response details
    let responseDetails = {};
    if (response) {
      const { config: resConfig, request: resRequest, ...restResponse } = response;
      responseDetails = {
        ...restResponse,
        config: resConfig ? '[Response Config]' : undefined,
        request: resRequest ? '[Response Request]' : undefined,
      };
    }
    
    return {
      isAxiosError: true,
      message: error.message,
      code: error.code,
      config: configDetails,
      response: responseDetails,
      request: request ? '[Request object]' : undefined,
      ...rest,
    };
  }
  
  // Handle regular errors
  if (error instanceof Error) {
    const { name, message, stack } = error;
    return { name, message, stack };
  }
  
  // Handle other objects
  return { ...error };
};

// Response interceptor for better error handling
api.interceptors.response.use(
  (response) => {
    try {
      console.log('[API] Response:', {
        method: response.config.method?.toUpperCase(),
        url: response.config.url,
        status: response.status,
        statusText: response.statusText,
        data: response.data
      });
    } catch (e) {
      console.error('[API] Error logging response:', e);
    }
    return response;
  },
  (error) => {
    try {
      // Get safe error details first
      const errorDetails = getErrorDetails(error);
      
      // Log basic error info
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        const { status, statusText } = error.response;
        
        // Handle specific status codes
        if (status === 401) {
          console.warn('[API] Unauthorized - User might need to log in');
        } else if (status === 403) {
          console.warn('[API] Forbidden - Missing or invalid permissions');
        } else if (status === 404) {
          console.warn('[API] Not Found - The requested resource was not found');
        } else if (status === 419) {
          console.warn('[API] CSRF Token Mismatch - Page has expired, please refresh');
        } else if (status >= 500) {
          console.error('[API] Server Error - Something went wrong on the server');
        } else {
          console.warn(`[API] Received status ${status} ${statusText}`);
        }
        
        // Log response data if available
        if (error.response.data) {
          console.warn('[API] Response data:', error.response.data);
        }
      } else if (error.request) {
        // The request was made but no response was received
        console.error('[API] No Response Received - The request was made but no response was received');
        console.error('[API] Request details:', {
          method: error.config?.method?.toUpperCase(),
          url: error.config?.url,
          baseURL: error.config?.baseURL,
          timeout: error.config?.timeout,
        });
      } else {
        // Something happened in setting up the request that triggered an Error
        console.error('[API] Request Setup Error - Error setting up the request');
      }
      
      // Log the full error details in a separate console group
      console.groupCollapsed('[API] Error Details');
      try {
        console.error('Error message:', error.message);
        console.error('Error code:', error.code);
        console.error('Error details:', JSON.stringify(errorDetails, null, 2));
        if (error.stack) {
          console.error('Stack trace:', error.stack);
        }
      } catch (e) {
        console.error('Error while logging error details:', e);
      }
      console.groupEnd();
      
    } catch (loggingError) {
      // If we get here, something went very wrong with error handling
      console.error('[API] Critical error in error handler:', loggingError);
      console.error('Original error message:', error?.message || 'No error message');
    }
    
    return Promise.reject(error);
  }
);

// Response interceptor to handle 401 Unauthorized
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    
    // If the error is 401 and we haven't tried to refresh yet
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        // Try to refresh the token
        await getCSRFToken();
        const refreshResponse = await axios.post(
          'http://localhost:8000/api/refresh-token',
          {},
          { withCredentials: true }
        );
        
        const { token } = refreshResponse.data;
        localStorage.setItem('auth_token', token);
        
        // Retry the original request with the new token
        originalRequest.headers.Authorization = `Bearer ${token}`;
        return api(originalRequest);
      } catch (error) {
        // If refresh fails, redirect to login
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
        return Promise.reject(error);
      }
    }
    
    return Promise.reject(error);
  }
);

// Ensures CSRF token is set for Sanctum
export const getCSRFToken = async () => {
  await axios.get('http://localhost:8000/sanctum/csrf-cookie', { 
    withCredentials: true 
  });
};

export default api;
