<template>
    <div class="statistics-panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <span class="title-icon">📊</span>
                Study Statistics
            </h3>
            <button
                class="btn btn-secondary btn-sm"
                @click="$emit('refresh')"
                :disabled="loading"
                aria-label="Refresh statistics"
            >
                <span class="btn-icon">🔄</span>
                Refresh
            </button>
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
</template>

<script>
import { computed } from 'vue'

export default {
    name: 'StatisticsPanel',
    props: {
        statistics: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['refresh'],
    setup(props) {
        const statItems = computed(() => {
            if (!props.statistics) return []

            return [
                {
                    key: 'total',
                    icon: '📚',
                    label: 'Total Cards',
                    value: props.statistics.total_cards || 0
                },
                {
                    key: 'new',
                    icon: '🆕',
                    label: 'New Cards',
                    value: props.statistics.new_cards || 0
                },
                {
                    key: 'review',
                    icon: '⏰',
                    label: 'Ready for Review',
                    value: props.statistics.ready_for_review || 0
                },
                {
                    key: 'mastered',
                    icon: '🎯',
                    label: 'Mastered',
                    value: props.statistics.mastered_cards || 0
                }
            ]
        })

        return {
            statItems
        }
    }
}
</script>

<style scoped>
.statistics-panel {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.panel-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

.title-icon {
    font-size: 1.3rem;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
    min-width: auto;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #5a6268;
    transform: translateY(-1px);
}

.loading-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 16px;
}

.skeleton-item {
    height: 80px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 12px;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #667eea;
}

.stat-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: #667eea;
    line-height: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 4px;
    line-height: 1.2;
}

.no-stats {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

@media (max-width: 600px) {
    .statistics-panel {
        padding: 16px;
    }

    .panel-header {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stat-item {
        flex-direction: column;
        text-align: center;
        gap: 8px;
        padding: 12px;
    }

    .stat-icon {
        font-size: 1.2rem;
    }

    .stat-value {
        font-size: 1.2rem;
    }
}
</style>
