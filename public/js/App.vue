<template>
  <div id="app">
    <div class="app-container">
      <div class="main-container fade-in">
        <!-- Header -->
        <header class="app-header">
          <h1>📚 Flashcard Study</h1>
          <div class="keyboard-shortcuts" role="note" aria-label="Keyboard shortcuts">
            <small>
              <kbd>Space</kbd> or <kbd>Enter</kbd> to show definition •
              <kbd>Y</kbd> for correct • <kbd>N</kbd> for incorrect •
              <kbd>→</kbd> for next card
            </small>
          </div>
        </header>

        <!-- Main Content -->
        <main class="app-main">
          <!-- Loading State -->
          <div v-if="isLoading && !hasCard && !hasError" class="loading-container">
            <div class="spinner"></div>
            <p class="loading-text">Loading next card...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="hasError" class="error-message" role="alert">
            <div class="error-icon">⚠️</div>
            <div class="error-content">
              <h4 class="error-title">Something went wrong</h4>
              <p class="error-text">{{ error }}</p>
              <button class="btn btn-secondary" @click="loadNextCard">
                <span class="btn-icon">🔄</span>
                Try Again
              </button>
            </div>
          </div>

          <!-- Status Message -->
          <div v-else-if="hasStatusMessage" class="status-message" :class="messageClass" role="alert">
            <div class="status-icon">{{ icon }}</div>
            <div class="status-content">
              <h3 class="status-title">{{ title }}</h3>
              <p class="status-text">{{ statusMessage }}</p>
            </div>
          </div>

          <!-- Flashcard Display -->
          <div v-else-if="hasCard" class="flashcard-card" :class="{ 'flipped': showDefinition }">
            <div class="card-inner">
              <!-- Front of card -->
              <div class="card-front">
                <div class="bin-info" :class="binClass">
                  <span class="bin-number">Bin {{ currentCard.bin }}</span>
                  <span class="bin-description">{{ binDescription }}</span>
                </div>

                <div class="word-display">
                  <h2 class="word">{{ currentCard.word }}</h2>
                </div>

                <div class="card-actions">
                  <button
                    class="btn btn-primary show-definition-btn"
                    @click="showCardDefinition"
                    :disabled="loading"
                  >
                    <span class="btn-icon">👁️</span>
                    Show Definition
                  </button>
                </div>
              </div>

              <!-- Back of card -->
              <div class="card-back">
                <div class="bin-info" :class="binClass">
                  <span class="bin-number">Bin {{ currentCard.bin }}</span>
                  <span class="bin-description">{{ binDescription }}</span>
                </div>

                <div class="word-display">
                  <h2 class="word">{{ currentCard.word }}</h2>
                </div>

                <div class="definition-display">
                  <p class="definition">{{ currentCard.definition }}</p>
                </div>

                <div class="card-actions">
                  <button
                    class="btn btn-success"
                    @click="submitAnswer(true)"
                    :disabled="loading"
                  >
                    <span class="btn-icon">✅</span>
                    I got it
                  </button>
                  <button
                    class="btn btn-danger"
                    @click="submitAnswer(false)"
                    :disabled="loading"
                  >
                    <span class="btn-icon">❌</span>
                    I didn't get it
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading Overlay for Actions -->
          <div v-if="isLoading && (hasCard || hasError) && !hasStatusMessage" class="loading-overlay">
            <div class="spinner"></div>
            <p class="loading-text">Processing...</p>
          </div>
        </main>

        <!-- Statistics Panel -->
        <aside class="app-sidebar" v-if="statistics">
          <div class="statistics-panel">
            <button class="btn btn-secondary btn-sm mb-4" @click="loadStatistics" :disabled="loading">
                <span class="btn-icon">🔄</span>
                Refresh
            </button>
            <div class="panel-header">
              <h3 class="panel-title">
                <span class="title-icon">📊</span>
                Study Statistics
              </h3>
            </div>

            <div v-if="loading" class="loading-skeleton">
              <div class="skeleton-item" v-for="n in 4" :key="n"></div>
            </div>

            <div v-else-if="statistics" class="stats-grid">
              <div class="stat-item" v-for="stat in statItems" :key="stat.key">
                <div class="stat-icon">{{ stat.icon }}</div>
                <div class="stat-content">
                  <div class="stat-value">{{ stat.value }}</div>
                  <div class="stat-label">{{ stat.label }}</div>
                </div>
              </div>
            </div>

            <div v-else class="no-stats">
              <p>No statistics available</p>
            </div>
          </div>
        </aside>

        <!-- Footer Actions -->
        <footer class="app-footer">
          <div class="footer-actions">
            <button class="btn btn-secondary" @click="loadNextCard" :disabled="loading">
              <span class="btn-icon">⏭️</span>
              Next Card
            </button>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useStudy } from './composables/useStudy.js'

export default {
  name: 'App',
  setup() {
    const {
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
      clearState
    } = useStudy()

    // Initialize app
    const initializeApp = async () => {
      await Promise.all([
        loadNextCard(),
        loadStatistics()
      ])
    }

    // Handle keyboard navigation
    const handleKeydown = (event) => {
      if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        return
      }

      switch (event.key) {
        case ' ':
        case 'Enter':
          event.preventDefault()
          if (hasCard.value && !canShowDefinition.value) {
            showCardDefinition()
          }
          break
        case 'y':
        case 'Y':
          if (canShowDefinition.value) {
            submitAnswer(true)
          }
          break
        case 'n':
        case 'N':
          if (canShowDefinition.value) {
            submitAnswer(false)
          }
          break
        case 'ArrowRight':
        case 'ArrowDown':
          event.preventDefault()
          loadNextCard()
          break
        case 'Escape':
          if (hasError.value) {
            clearState()
          }
          break
      }
    }

    // Add keyboard event listener
    const addKeyboardListeners = () => {
      document.addEventListener('keydown', handleKeydown)
    }

    const removeKeyboardListeners = () => {
      document.removeEventListener('keydown', handleKeydown)
    }

    // Lifecycle
    const onMounted = () => {
      initializeApp()
      addKeyboardListeners()
    }

    const onUnmounted = () => {
      removeKeyboardListeners()
    }

    // Additional computed properties for template
    const binDescription = computed(() => {
      return currentCard.value ? getBinDescription(currentCard.value.bin) : ''
    })

    const binClass = computed(() => {
      if (!currentCard.value) return ''
      const bin = currentCard.value.bin
      if (bin === 0) return 'bin-new'
      if (bin <= 2) return 'bin-early'
      if (bin <= 5) return 'bin-mid'
      if (bin <= 9) return 'bin-advanced'
      return 'bin-mastered'
    })

    const messageClass = computed(() => {
      if (isPermanentlyDone.value) return 'status-success'
      return 'status-info'
    })

    const icon = computed(() => {
      if (isPermanentlyDone.value) return '🎉'
      return 'ℹ️'
    })

    const title = computed(() => {
      if (isPermanentlyDone.value) return 'All Done!'
      return 'Study Status'
    })

    const statItems = computed(() => {
      if (!statistics.value) return []
      return [
        { key: 'total', icon: '📚', value: statistics.value.total_cards, label: 'Total Cards' },
        { key: 'new', icon: '🆕', value: statistics.value.new_cards, label: 'New Cards' },
        { key: 'review', icon: '⏰', value: statistics.value.ready_for_review, label: 'Ready for Review' },
        { key: 'mastered', icon: '🏆', value: statistics.value.mastered_cards, label: 'Mastered' }
      ]
    })

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
      binDescription,
      binClass,
      messageClass,
      icon,
      title,
      statItems,

      // Methods
      loadNextCard,
      submitAnswer,
      loadStatistics,
      getBinDescription,
      showCardDefinition,
      clearState,
      onMounted,
      onUnmounted
    }
  },
  mounted() {
    this.onMounted()
  },
  unmounted() {
    this.onUnmounted()
  }
}
</script>

<style scoped>
/* App Container */
.app-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem;
}

.main-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 300px;
  grid-template-rows: auto 1fr auto;
  grid-template-areas:
    "header header"
    "main sidebar"
    "footer footer";
  gap: 1rem;
  min-height: calc(100vh - 2rem);
}

.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Header */
.app-header {
  grid-area: header;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.app-header h1 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-size: 2rem;
}

.keyboard-shortcuts {
  color: #666;
  font-size: 0.9rem;
}

.keyboard-shortcuts kbd {
  background: #f0f0f0;
  border: 1px solid #ccc;
  border-radius: 3px;
  padding: 2px 6px;
  font-size: 0.8rem;
}

/* Main Content */
.app-main {
  grid-area: main;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: relative;
}

/* Loading States */
.loading-container {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-text {
  margin: 0;
  font-size: 1.1rem;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

/* Error State */
.error-message {
  text-align: center;
  padding: 2rem;
  background: #fee;
  border: 1px solid #fcc;
  border-radius: 8px;
  color: #c33;
}

.error-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.error-title {
  margin: 0 0 0.5rem 0;
  color: #c33;
}

.error-text {
  margin: 0 0 1rem 0;
}

/* Status Message */
.status-message {
  text-align: center;
  padding: 2rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.status-success {
  background: #efe;
  border: 1px solid #cfc;
  color: #363;
}

.status-info {
  background: #eef;
  border: 1px solid #ccf;
  color: #336;
}

.status-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.status-title {
  margin: 0 0 0.5rem 0;
}

.status-text {
  margin: 0;
}

/* Flashcard Card */
.flashcard-card {
  perspective: 1000px;
  margin: 0 auto;
  max-width: 500px;
}

.card-inner {
  position: relative;
  width: 100%;
  height: 300px;
  text-align: center;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}

.flashcard-card.flipped .card-inner {
  transform: rotateY(180deg);
}

.card-front,
.card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-back {
  transform: rotateY(180deg);
}

.bin-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.bin-new { background: #e3f2fd; color: #1976d2; }
.bin-early { background: #fff3e0; color: #f57c00; }
.bin-mid { background: #f3e5f5; color: #7b1fa2; }
.bin-advanced { background: #e8f5e8; color: #388e3c; }
.bin-mastered { background: #fff8e1; color: #f9a825; }

.word-display {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.word {
  font-size: 2.5rem;
  margin: 0;
  color: #333;
}

.definition-display {
  margin: 1rem 0;
}

.definition {
  font-size: 1.2rem;
  line-height: 1.6;
  color: #555;
  margin: 0;
}

.card-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

/* Buttons */
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-success {
  background: #4caf50;
  color: white;
}

.btn-danger {
  background: #f44336;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-icon {
  font-size: 1.1em;
}

/* Sidebar */
.app-sidebar {
  grid-area: sidebar;
}

.statistics-panel {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  height: fit-content;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-title {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.title-icon {
  font-size: 1.3em;
}

.loading-skeleton {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-item {
  height: 40px;
  background: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat-icon {
  font-size: 1.5rem;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
}

.stat-label {
  font-size: 0.9rem;
  color: #666;
}

.no-stats {
  text-align: center;
  color: #666;
  padding: 2rem;
}

/* Footer */
.app-footer {
  grid-area: footer;
  display: flex;
  justify-content: center;
}

.footer-actions {
  display: flex;
  gap: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .main-container {
    grid-template-columns: 1fr;
    grid-template-areas:
      "header"
      "main"
      "sidebar"
      "footer";
  }

  .app-sidebar {
    order: 3;
  }

  .app-footer {
    order: 4;
  }

  .word {
    font-size: 2rem;
  }

  .card-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>
