# 📚 Flashcards application

A modern flashcard study application implementing spaced repetition algorithms for optimal learning retention. Built with Vue.js 3 and Laravel.

## 🎯 Overview

This application features a 12-bin spaced repetition system that automatically schedules card reviews based on performance, maximizing long-term retention through scientifically-proven intervals.

**Key Features:**
- Spaced repetition with 12 difficulty bins
- Modern Vue.js 3 + Laravel architecture
- Responsive design with accessibility support
- Real-time statistics and progress tracking
- Filament Admin panel for content management

## 🚀 Quick Start

### Prerequisites
- Node.js 18+
- PHP 8.2+
- Composer

### Installation

```bash
# Clone and setup
git clone <repository-url>
cd flashcard-app

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed
```

### Development

```bash
# Start both servers
npm run dev:full
```

Access the application at `http://localhost:3000`

### Production Build

```bash
npm run build
```

## 🏗️ Architecture

**Frontend:** Vue.js 3 with Composition API, Vite build tooling
**Backend:** Laravel 12 with Filament admin panel
**Database:** MySQL with Eloquent ORM

## 🧩 Components

- `FlashcardCard.vue` - Main card display with flip animation
- `StatisticsPanel.vue` - Study progress dashboard
- `useStudy.js` - Study logic and state management
- `useApi.js` - API communication layer

## 🧪 Testing

```bash
# Run tests
php artisan test

# Code quality
npm run lint
```

## 📱 Browser Support

Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

## 🔧 Troubleshooting

**Database issues:**
```bash
php artisan migrate:fresh --seed
```

**Node modules:**
```bash
rm -rf node_modules package-lock.json && npm install
```

**Port conflicts:**
```bash
php artisan serve --port=8001
npm run dev -- --port 3001
```

## 📄 License

MIT License
