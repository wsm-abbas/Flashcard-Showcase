import { ref, computed } from 'vue'
import { useApi } from './useApi'

export function useUser() {
    const { apiCall } = useApi()

    // State
    const currentUser = ref(null)
    const isInitialized = ref(false)
    const loading = ref(false)
    const error = ref(null)

    // Computed
    const hasUser = computed(() => currentUser.value !== null)
    const userId = computed(() => currentUser.value?.id || null)

    // Constants
    const USER_STORAGE_KEY = 'flashcard_user_id'
    const USER_NAME_KEY = 'flashcard_user_name'

    // Methods
    const generateUserId = () => {
        return 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
    }

    const getUserFromStorage = () => {
        try {
            const storedUserId = localStorage.getItem(USER_STORAGE_KEY)
            const storedName = localStorage.getItem(USER_NAME_KEY)

            if (storedUserId && storedName) {
                return {
                    id: storedUserId, // This is the LOCAL ID, not backend ID
                    name: storedName
                }
            }
            return null
        } catch (error) {
            console.error('Error reading from localStorage:', error)
            return null
        }
    }

    const saveUserToStorage = (user) => {
        try {
            localStorage.setItem(USER_STORAGE_KEY, user.id) // Store LOCAL ID
            localStorage.setItem(USER_NAME_KEY, user.name)
        } catch (error) {
            console.error('Error saving to localStorage:', error)
        }
    }

    const createUser = async (name) => {
        loading.value = true
        error.value = null

        try {
            // Generate a LOCAL user ID (never sent to backend)
            const localId = generateUserId()
            const userEmail = `${localId}@local.user`

            // Initialize study (this will create user if needed)
            const response = await apiCall('POST', '/study/initialize', {
                user_email: userEmail
            })

            // Store LOCAL ID and name in localStorage
            const user = {
                id: localId, // Store LOCAL ID, not backend ID
                name: name
            }
            saveUserToStorage(user)
            currentUser.value = user
            isInitialized.value = true

            return user
        } catch (err) {
            error.value = 'Failed to create user. Please try again.'
            console.error('Error creating user:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const initializeUser = async () => {
        if (isInitialized.value) return currentUser.value

        // Prevent race conditions by checking if initialization is already in progress
        if (loading.value) {
            // Wait for the current initialization to complete
            while (loading.value && !isInitialized.value) {
                await new Promise(resolve => setTimeout(resolve, 100))
            }
            return currentUser.value
        }

        loading.value = true
        error.value = null

        try {
            // Try to get user from localStorage first
            let user = getUserFromStorage()

            if (!user) {
                // Create a new user with a default name
                const defaultName = `Student ${Math.floor(Math.random() * 1000)}`
                user = await createUser(defaultName)
            } else {
                // User exists locally, just set as current
                currentUser.value = user
                isInitialized.value = true
            }

            return user
        } catch (err) {
            error.value = 'Failed to initialize user. Please refresh the page.'
            console.error('Error initializing user:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const initializeStudy = async () => {
        if (!currentUser.value) {
            throw new Error('User not initialized')
        }

        try {
            const response = await apiCall('POST', '/study/initialize', {
                user_email: `${currentUser.value.id}@local.user` // Send LOCAL ID as email
            })
            return response.data
        } catch (err) {
            console.error('Error initializing study:', err)
            throw err
        }
    }

    const clearUser = () => {
        try {
            localStorage.removeItem(USER_STORAGE_KEY)
            localStorage.removeItem(USER_NAME_KEY)
            currentUser.value = null
            isInitialized.value = false
        } catch (error) {
            console.error('Error clearing user from localStorage:', error)
        }
    }

    const updateUserName = async (newName) => {
        if (!currentUser.value) return

        try {
            // Update locally
            currentUser.value.name = newName
            saveUserToStorage(currentUser.value)
        } catch (error) {
            console.error('Error updating user name:', error)
        }
    }

    return {
        // State
        currentUser,
        isInitialized,
        loading,
        error,

        // Computed
        hasUser,
        userId,

        // Methods
        initializeUser,
        createUser,
        initializeStudy,
        clearUser,
        updateUserName,
        getUserFromStorage
    }
}
