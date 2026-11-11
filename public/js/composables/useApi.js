import axios from 'axios'

export function useApi() {
    const apiBaseUrl = window.location.origin + '/api'

    // Create axios instance with default config
    const apiClient = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })

    // Helper function to get user email from localStorage
    const getUserEmail = () => {
        try {
            const userId = localStorage.getItem('flashcard_user_id')
            if (userId) {
                return `${userId}@local.user`
            }
            return null
        } catch (error) {
            console.error('Error getting user email from localStorage:', error)
            return null
        }
    }

    // Request interceptor
    apiClient.interceptors.request.use(
        (config) => {
            // Add user_email to study requests (except initialize which provides its own)
            if (config.url.startsWith('/study/') && !config.url.includes('/initialize')) {
                const userEmail = getUserEmail()
                if (userEmail) {
                    // Add user_email to query params for GET requests
                    if (config.method === 'get') {
                        config.params = { ...config.params, user_email: userEmail }
                    }
                    // Add user_email to data for POST requests
                    else if (config.method === 'post') {
                        config.data = { ...config.data, user_email: userEmail }
                    }
                }
            }
            return config
        },
        (error) => {
            return Promise.reject(error)
        }
    )

    // Response interceptor
    apiClient.interceptors.response.use(
        (response) => {
            return response
        },
        (error) => {
            // Handle common errors
            if (error.response?.status === 429) {
                throw new Error('Too many requests. Please wait a moment and try again.')
            } else if (error.response?.status === 500) {
                throw new Error('Server error. Please try again later.')
            } else if (error.code === 'ECONNABORTED') {
                throw new Error('Request timeout. Please check your connection.')
            } else if (!error.response) {
                throw new Error('Network error. Please check your connection.')
            }

            throw error
        }
    )

    const apiCall = async (method, endpoint, data = null) => {
        try {
            const config = {
                method,
                url: endpoint,
                ...(data && { data })
            }

            return await apiClient(config)
        } catch (error) {
            console.error(`API Error [${method} ${endpoint}]:`, error)
            throw error
        }
    }

    return {
        apiClient,
        apiCall
    }
}
