import { ref, computed } from 'vue'
import { useApi } from './useApi'
import { useUser } from './useUser'

export function useStudy() {
    const { apiCall } = useApi()
    const { initializeUser, initializeStudy, hasUser, userId, isInitialized } = useUser()

    // State
    const currentCard = ref(null)
    const showDefinition = ref(false)
    const loading = ref(false)
    const error = ref(null)
    const statusMessage = ref(null)
    const isPermanentlyDone = ref(false)
    const statistics = ref(null)

    // Computed
    const hasCard = computed(() => currentCard.value !== null)
    const canShowDefinition = computed(() => hasCard.value && showDefinition.value)
    const isLoading = computed(() => loading.value)
    const hasError = computed(() => error.value !== null)
    const hasStatusMessage = computed(() => statusMessage.value !== null)

    // Methods
    const clearState = () => {
        error.value = null
        statusMessage.value = null
        showDefinition.value = false
    }

    const ensureUserInitialized = async () => {
        if (!isInitialized.value) {
            try {
                await initializeUser() // This now handles both user creation and study initialization
            } catch (err) {
                error.value = 'Failed to initialize study session. Please refresh the page.'
                throw err
            }
        }
    }

    const loadNextCard = async (showStatusMessage = true) => {
        loading.value = true

        // Only clear state for user-initiated calls
        if (showStatusMessage) {
            clearState()
        }

        try {
            await ensureUserInitialized()

            const response = await apiCall('GET', '/study/next-card')
            const data = response.data

            if (data.card) {
                currentCard.value = data.card
                // Clear any existing status message when we have a card
                statusMessage.value = null
            } else {
                // Only show status message if explicitly requested (user-initiated)
                // This ensures we only show "no cards available" messages when user manually requests a card
                if (showStatusMessage) {
                    statusMessage.value = data.message
                }
                isPermanentlyDone.value = data.is_permanently_done
                currentCard.value = null
            }
        } catch (err) {
            error.value = 'Failed to load next card. Please try again.'
            console.error('Error loading next card:', err)
        } finally {
            loading.value = false
        }
    }

    const submitAnswer = async (isCorrect) => {
        if (!currentCard.value) return

        loading.value = true
        clearState()

        try {
            await ensureUserInitialized()

            await apiCall('POST', '/study/submit-review', {
                user_flashcard_id: currentCard.value.id,
                is_correct: isCorrect
            })

            // Show feedback briefly
            const feedback = isCorrect ? 'Correct! ✅' : 'Incorrect. Keep studying! ❌'
            statusMessage.value = feedback

            // Load next card after delay without showing intermediate status messages
            setTimeout(async () => {
                statusMessage.value = null
                await loadNextCard(false) // Don't show status message for automatic loading
                await loadStatistics()
            }, 1500)

        } catch (err) {
            error.value = 'Failed to submit answer. Please try again.'
            console.error('Error submitting answer:', err)
            loading.value = false
        }
    }

    const loadStatistics = async () => {
        try {
            await ensureUserInitialized()

            const response = await apiCall('GET', '/study/statistics')
            statistics.value = response.data.statistics

            // Only show study status if there are no cards available for study
            // and we're not in the middle of a card transition
            if (response.data.study_status && !currentCard.value) {
                statusMessage.value = response.data.study_status
                isPermanentlyDone.value = response.data.is_permanently_done
            }
        } catch (err) {
            console.error('Error loading statistics:', err)
        }
    }

    const getBinDescription = (bin) => {
        const descriptions = {
            0: 'New Card',
            1: '5 seconds',
            2: '25 seconds',
            3: '2 minutes',
            4: '10 minutes',
            5: '1 hour',
            6: '5 hours',
            7: '1 day',
            8: '5 days',
            9: '25 days',
            10: '4 months',
            11: 'Mastered'
        }
        return descriptions[bin] || 'Unknown'
    }

    const showCardDefinition = () => {
        showDefinition.value = true
    }

    return {
        // State
        currentCard,
        showDefinition,
        loading,
        error,
        statusMessage,
        isPermanentlyDone,
        statistics,

        // Computed
        hasCard,
        canShowDefinition,
        isLoading,
        hasError,
        hasStatusMessage,

        // Methods
        loadNextCard,
        submitAnswer,
        loadStatistics,
        getBinDescription,
        showCardDefinition,
        clearState,
        ensureUserInitialized
    }
}
