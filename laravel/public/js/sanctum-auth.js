/**
 * Laravel Sanctum Session Authentication Helper
 * This file provides utilities for working with Laravel Sanctum session-based authentication
 */

class SanctumAuth {
    constructor() {
        this.baseURL = window.location.origin;
        this.apiURL = `${this.baseURL}/api`;
        this.csrfToken = this.getCSRFToken();
        this.authenticated = false;
        this.user = null;
        
        // Initialize axios defaults
        this.setupAxiosDefaults();
        
        // Check initial auth status
        this.checkAuthStatus();
    }

    /**
     * Get CSRF token from meta tag
     */
    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : null;
    }

    /**
     * Setup axios defaults for Sanctum
     */
    setupAxiosDefaults() {
        // If axios is available, configure it
        if (typeof axios !== 'undefined') {
            axios.defaults.withCredentials = true;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            
            if (this.csrfToken) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = this.csrfToken;
            }

            // Add response interceptor to handle 401 errors
            axios.interceptors.response.use(
                response => response,
                error => {
                    if (error.response && error.response.status === 401) {
                        this.handleUnauthenticated();
                    }
                    return Promise.reject(error);
                }
            );
        }
    }

    /**
     * Initialize Sanctum session (call this before making authenticated API requests)
     */
    async initializeSanctum() {
        try {
            const response = await this.makeRequest('GET', '/auth/sanctum');
            if (response.csrf_token) {
                this.updateCSRFToken(response.csrf_token);
            }
            return response;
        } catch (error) {
            console.error('Failed to initialize Sanctum:', error);
            throw error;
        }
    }

    /**
     * Login with email and password
     */
    async login(email, password) {
        try {
            // First initialize sanctum session
            await this.initializeSanctum();
            
            const response = await this.makeRequest('POST', '/auth/login', {
                email: email,
                password: password
            });

            if (response.user) {
                this.authenticated = true;
                this.user = response.user;
                
                if (response.csrf_token) {
                    this.updateCSRFToken(response.csrf_token);
                }
                
                // Trigger custom event
                this.dispatchAuthEvent('login', { user: this.user });
            }

            return response;
        } catch (error) {
            console.error('Login failed:', error);
            throw error;
        }
    }

    /**
     * Logout
     */
    async logout() {
        try {
            const response = await this.makeRequest('POST', '/auth/logout');
            
            this.authenticated = false;
            this.user = null;
            
            // Trigger custom event
            this.dispatchAuthEvent('logout');
            
            return response;
        } catch (error) {
            console.error('Logout failed:', error);
            // Even if logout fails on server, clear local state
            this.authenticated = false;
            this.user = null;
            throw error;
        }
    }

    /**
     * Check authentication status
     */
    async checkAuthStatus() {
        try {
            const response = await this.makeRequest('GET', '/auth/check');
            
            this.authenticated = response.authenticated || false;
            this.user = response.user || null;
            
            if (response.csrf_token) {
                this.updateCSRFToken(response.csrf_token);
            }
            
            return response;
        } catch (error) {
            this.authenticated = false;
            this.user = null;
            return { authenticated: false, user: null };
        }
    }

    /**
     * Get current user
     */
    async getCurrentUser() {
        try {
            const response = await this.makeRequest('GET', '/auth/user');
            
            if (response.user) {
                this.user = response.user;
                this.authenticated = true;
            }
            
            return response;
        } catch (error) {
            this.authenticated = false;
            this.user = null;
            throw error;
        }
    }

    /**
     * Make authenticated API request
     */
    async makeAuthenticatedRequest(method, endpoint, data = null) {
        if (!this.authenticated) {
            throw new Error('Not authenticated. Please login first.');
        }

        return this.makeRequest(method, endpoint, data);
    }

    /**
     * Generic API request method
     */
    async makeRequest(method, endpoint, data = null) {
        const url = endpoint.startsWith('/api/') ? `${this.baseURL}${endpoint}` : `${this.apiURL}${endpoint}`;
        
        const config = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        };

        // Add CSRF token to headers
        if (this.csrfToken) {
            config.headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        // Add data for POST/PUT/PATCH requests
        if (data && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, config);
            
            // Handle non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Expected JSON response, got ${contentType}`);
            }
            
            const responseData = await response.json();
            
            if (!response.ok) {
                throw new Error(responseData.message || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            return responseData;
        } catch (error) {
            console.error(`API request failed: ${method} ${url}`, error);
            throw error;
        }
    }

    /**
     * Update CSRF token
     */
    updateCSRFToken(token) {
        this.csrfToken = token;
        
        // Update meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', token);
        }
        
        // Update jQuery AJAX setup if available
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
        
        // Update axios defaults if available
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
    }

    /**
     * Handle unauthenticated response
     */
    handleUnauthenticated() {
        this.authenticated = false;
        this.user = null;
        
        // Trigger custom event
        this.dispatchAuthEvent('unauthenticated');
        
        // Optionally redirect to login page
        if (window.location.pathname !== '/login') {
            window.location.href = '/login';
        }
    }

    /**
     * Dispatch custom authentication events
     */
    dispatchAuthEvent(type, detail = {}) {
        const event = new CustomEvent(`sanctum:${type}`, {
            detail: detail,
            bubbles: true
        });
        document.dispatchEvent(event);
    }

    /**
     * Get dashboard stats (authenticated endpoint example)
     */
    async getDashboardStats() {
        return this.makeAuthenticatedRequest('GET', '/dashboard/stats');
    }

    /**
     * Get escort data (authenticated endpoint example)
     */
    async getEscorts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = queryString ? `/escort?${queryString}` : '/escort';
        return this.makeAuthenticatedRequest('GET', endpoint);
    }

    /**
     * Create escort record (authenticated endpoint example)
     */
    async createEscort(data) {
        return this.makeAuthenticatedRequest('POST', '/escort', data);
    }

    /**
     * Update escort record (authenticated endpoint example)
     */
    async updateEscort(id, data) {
        return this.makeAuthenticatedRequest('PUT', `/escort/${id}`, data);
    }

    /**
     * Delete escort record (authenticated endpoint example)
     */
    async deleteEscort(id) {
        return this.makeAuthenticatedRequest('DELETE', `/escort/${id}`);
    }
}

// Create global instance
window.sanctumAuth = new SanctumAuth();

// Example usage with event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Listen for authentication events
    document.addEventListener('sanctum:login', function(event) {
        console.log('User logged in:', event.detail.user);
        // Update UI for authenticated state
    });

    document.addEventListener('sanctum:logout', function(event) {
        console.log('User logged out');
        // Update UI for unauthenticated state
    });

    document.addEventListener('sanctum:unauthenticated', function(event) {
        console.log('User is not authenticated');
        // Handle unauthenticated state
    });
});

/**
 * Example functions showing how to use the SanctumAuth class
 */

// Example: Login form submission
function handleLoginForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const email = form.email.value;
    const password = form.password.value;
    
    sanctumAuth.login(email, password)
        .then(response => {
            console.log('Login successful:', response);
            // Redirect or update UI
            window.location.href = '/dashboard';
        })
        .catch(error => {
            console.error('Login failed:', error);
            // Show error message
            alert('Login gagal: ' + error.message);
        });
}

// Example: Load dashboard data
async function loadDashboardData() {
    try {
        const stats = await sanctumAuth.getDashboardStats();
        console.log('Dashboard stats:', stats);
        
        // Update dashboard UI with stats
        if (stats.data) {
            updateDashboardUI(stats.data);
        }
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
}

// Example: Load escorts with filters
async function loadEscorts(filters = {}) {
    try {
        const response = await sanctumAuth.getEscorts(filters);
        console.log('Escorts loaded:', response);
        
        // Update escorts table or list
        if (response.data) {
            updateEscortsTable(response.data);
        }
    } catch (error) {
        console.error('Failed to load escorts:', error);
    }
}

// Utility function to update dashboard UI (example)
function updateDashboardUI(stats) {
    const totalElement = document.getElementById('total-count');
    const todayElement = document.getElementById('today-count');
    
    if (totalElement && stats.total_escorts !== undefined) {
        totalElement.textContent = stats.total_escorts;
    }
    
    if (todayElement && stats.today_submissions !== undefined) {
        todayElement.textContent = stats.today_submissions;
    }
}

// Utility function to update escorts table (example)
function updateEscortsTable(escorts) {
    // Implementation depends on your table structure
    console.log('Updating escorts table with:', escorts);
}