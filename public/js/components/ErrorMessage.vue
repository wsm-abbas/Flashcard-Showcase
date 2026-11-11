<template>
    <div class="error-message" role="alert" aria-live="polite">
        <div class="error-icon">⚠️</div>
        <div class="error-content">
            <h4 class="error-title">Something went wrong</h4>
            <p class="error-text">{{ message }}</p>
            <button
                class="btn btn-secondary retry-btn"
                @click="$emit('retry')"
                aria-label="Try again"
            >
                <span class="btn-icon">🔄</span>
                Try Again
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ErrorMessage',
    props: {
        message: {
            type: String,
            required: true
        }
    },
    emits: ['retry']
}
</script>

<style scoped>
.error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.error-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.error-content {
    flex: 1;
}

.error-title {
    color: #721c24;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.error-text {
    color: #721c24;
    margin: 0 0 16px 0;
    line-height: 1.5;
}

.retry-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.retry-btn:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-icon {
    font-size: 0.9rem;
}
</style>
