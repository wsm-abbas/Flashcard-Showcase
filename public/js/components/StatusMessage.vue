<template>
    <div class="status-message" :class="messageClass" role="alert" aria-live="polite">
        <div class="status-icon">{{ icon }}</div>
        <div class="status-content">
            <h3 class="status-title">{{ title }}</h3>
            <p class="status-text">{{ message }}</p>
            <div class="status-actions" v-if="showActions">
                <button
                    class="btn btn-secondary"
                    @click="$emit('check-again')"
                    aria-label="Check for more cards"
                >
                    <span class="btn-icon">🔍</span>
                    Check Again
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue'

export default {
    name: 'StatusMessage',
    props: {
        message: {
            type: String,
            required: true
        },
        isPermanentlyDone: {
            type: Boolean,
            default: false
        },
        showActions: {
            type: Boolean,
            default: true
        }
    },
    emits: ['check-again'],
    setup(props) {
        const messageClass = computed(() => {
            return props.isPermanentlyDone ? 'status-success' : 'status-info'
        })

        const icon = computed(() => {
            return props.isPermanentlyDone ? '🎉' : '⏰'
        })

        const title = computed(() => {
            return props.isPermanentlyDone ? 'Congratulations!' : 'No cards ready'
        })

        return {
            messageClass,
            icon,
            title
        }
    }
}
</script>

<style scoped>
.status-message {
    border-radius: 16px;
    padding: 24px;
    margin: 20px 0;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    border: 2px solid;
}

.status-info {
    background: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.status-success {
    background: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.status-icon {
    font-size: 2rem;
    flex-shrink: 0;
    margin-top: 4px;
}

.status-content {
    flex: 1;
}

.status-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.status-text {
    font-size: 1rem;
    line-height: 1.6;
    margin: 0 0 16px 0;
}

.status-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-icon {
    font-size: 1rem;
}

@media (max-width: 600px) {
    .status-message {
        padding: 20px;
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .status-icon {
        font-size: 1.5rem;
        margin-top: 0;
    }

    .status-title {
        font-size: 1.2rem;
    }

    .status-actions {
        justify-content: center;
    }
}
</style>
