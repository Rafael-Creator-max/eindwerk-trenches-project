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

// Re-export getCSRFToken from api
import { getCSRFToken } from './api';

export const login = async (email: string, password: string): Promise<User> => {
  try {
    console.log('[Auth] Starting login process...');
    
    // First, ensure we have a CSRF token
    console.log('[Auth] Getting CSRF token...');
    const token = await getCSRFToken();
    
    if (!token) {
      throw new Error('Could not retrieve CSRF token. Please try again.');
    }
    
    // Perform the login
    console.log('[Auth] Sending login request...');
    const response = await api.post<AuthResponse>(
      '/login',
      { email, password, remember: true },
      {
        withCredentials: true,
        validateStatus: (status) => status < 500, // Don't throw for 4xx errors
      }
    );
    
    console.log('[Auth] Login response:', {
      status: response.status,
      statusText: response.statusText,
      headers: response.headers,
    });
    
    // Handle non-2xx responses
    if (response.status >= 400) {
      const errorData = response.data as any;
      let errorMessage = 'Login failed';
      
      if (response.status === 422 && errorData.errors) {
        // Validation errors
        errorMessage = Object.entries(errorData.errors)
          .map(([field, messages]) => `${field}: ${(messages as string[]).join(', ')}`)
          .join('\n');
      } else if (errorData.message) {
        errorMessage = errorData.message;
      }
      
      throw new Error(errorMessage);
    }
    
    // Get the user data
    console.log('[Auth] Fetching user data...');
    const userResponse = await api.get<User>('/api/user', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      withCredentials: true,
    });
    
    if (!userResponse.data) {
      throw new Error('Failed to fetch user data after login');
    }
    
    console.log('[Auth] Login successful, user data received');
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
    
    // Rethrow the error with a user-friendly message
    if (error.message) {
      throw error; // Already has a good message
    } else if (error.response) {
      throw new Error('Login failed. Please check your credentials and try again.');
    } else if (error.request) {
      throw new Error('No response from server. Please check your internet connection.');
    } else {
      throw new Error('An unexpected error occurred during login.');
    }
  }
};

export const register = async (name: string, email: string, password: string, password_confirmation: string): Promise<User> => {
  // Ensure we have a CSRF token first
  const token = await getCSRFToken();
  if (!token) {
    throw new Error('Could not retrieve CSRF token. Please try again.');
  }
  
  const response = await api.post<AuthResponse>(
    '/register',
    { name, email, password, password_confirmation },
    {
      withCredentials: true,
      validateStatus: (status) => status < 500,
    }
  );
  
  if (response.status >= 400) {
    const errorData = response.data as any;
    let errorMessage = 'Registration failed';
    
    if (errorData?.errors) {
      const errorMessages = Object.values(errorData.errors).flat();
      errorMessage = errorMessages.join('\n');
    } else if (errorData?.message) {
      errorMessage = errorData.message;
    }
    
    throw new Error(errorMessage);
  }
  
  return response.data.user || response.data;
};

export const logout = async (): Promise<void> => {
  try {
    // Ensure we have a CSRF token first
    const token = await getCSRFToken();
    if (!token) {
      console.warn('No CSRF token found for logout');
    }
    
    // Perform the logout request
    await api.post('/logout', {}, { 
      withCredentials: true,
      validateStatus: (status) => status < 500,
    });
    
    // Clear any cached user data
    if (typeof window !== 'undefined') {
      localStorage.removeItem('user');
      sessionStorage.removeItem('user');
      localStorage.removeItem('auth_token');
    }
    
    // Clear the CSRF token
    document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    
  } catch (error) {
    console.error('Logout error:', error);
    // Even if logout fails, clear local state
    if (typeof window !== 'undefined') {
      localStorage.removeItem('user');
      sessionStorage.removeItem('user');
      localStorage.removeItem('auth_token');
    }
    throw error;
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
