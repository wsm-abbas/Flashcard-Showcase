<template>
    <div class="flashcard-card" :class="{ 'flipped': showDefinition }">
        <div class="card-inner">
            <!-- Front of card -->
            <div class="card-front">
                <div class="bin-info" :class="binClass">
                    <span class="bin-number">Bin {{ card.bin }}</span>
                    <span class="bin-description">{{ binDescription }}</span>
                </div>

                <div class="word-display">
                    <h2 class="word">{{ card.word }}</h2>
                </div>

                <div class="card-actions">
                    <button
                        class="btn btn-primary show-definition-btn"
                        @click="$emit('show-definition')"
                        :disabled="loading"
                        aria-label="Show definition"
                    >
                        <span class="btn-icon">👁️</span>
                        Show Definition
                    </button>
                </div>
            </div>

            <!-- Back of card -->
            <div class="card-back">
                <div class="bin-info" :class="binClass">
                    <span class="bin-number">Bin {{ card.bin }}</span>
                    <span class="bin-description">{{ binDescription }}</span>
                </div>

                <div class="word-display">
                    <h2 class="word">{{ card.word }}</h2>
                </div>

                <div class="definition-display">
                    <p class="definition">{{ card.definition }}</p>
                </div>

                <div class="card-actions">
                    <button
                        class="btn btn-success"
                        @click="$emit('answer', true)"
                        :disabled="loading"
                        aria-label="Mark as correct"
                    >
                        <span class="btn-icon">✅</span>
                        I got it
                    </button>
                    <button
                        class="btn btn-danger"
                        @click="$emit('answer', false)"
                        :disabled="loading"
                        aria-label="Mark as incorrect"
                    >
                        <span class="btn-icon">❌</span>
                        I didn't get it
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue'

export default {
    name: 'FlashcardCard',
    props: {
        card: {
            type: Object,
            required: true
        },
        showDefinition: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        getBinDescription: {
            type: Function,
            required: true
        }
    },
    emits: ['show-definition', 'answer'],
    setup(props) {
        const binDescription = computed(() => props.getBinDescription(props.card.bin))

        const binClass = computed(() => {
            const bin = props.card.bin
            if (bin === 0) return 'bin-new'
            if (bin === 11) return 'bin-mastered'
            if (bin <= 3) return 'bin-early'
            if (bin <= 7) return 'bin-mid'
            return 'bin-advanced'
        })

        return {
            binDescription,
            binClass
        }
    }
}
</script>

<style scoped>
.flashcard-card {
    perspective: 1000px;
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.card-inner {
    position: relative;
    width: 100%;
    height: 400px;
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
    border-radius: 20px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #e9ecef;
}

.card-back {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bin-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.bin-new {
    background: #e3f2fd;
    color: #1976d2;
}

.bin-early {
    background: #fff3e0;
    color: #f57c00;
}

.bin-mid {
    background: #f3e5f5;
    color: #7b1fa2;
}

.bin-advanced {
    background: #e8f5e8;
    color: #2e7d32;
}

.bin-mastered {
    background: #e8f5e8;
    color: #1b5e20;
}

.bin-number {
    font-size: 1rem;
    font-weight: 700;
}

.bin-description {
    font-size: 0.8rem;
    opacity: 0.8;
}

.word-display {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.word {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    line-height: 1.2;
}

.definition-display {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px 0;
}

.definition {
    font-size: 1.3rem;
    color: #34495e;
    line-height: 1.6;
    margin: 0;
    text-align: center;
}

.card-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 140px;
    justify-content: center;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.btn-success:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    color: white;
}

.btn-danger:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
}

.btn-icon {
    font-size: 1.1rem;
}

.show-definition-btn {
    min-width: 180px;
}

@media (max-width: 600px) {
    .card-front,
    .card-back {
        padding: 20px;
    }

    .word {
        font-size: 2rem;
    }

    .definition {
        font-size: 1.1rem;
    }

    .card-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        min-width: auto;
    }
}
</style>
