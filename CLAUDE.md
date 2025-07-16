# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PhisyoMarv2 is a comprehensive medical practice management web application built with Laravel 12, specifically designed for physiotherapy/physical therapy clinics. The application uses modern Laravel architecture with API-first design and supports multiple user roles including patients, therapists, receptionists, and administrators.

## Key Development Commands

### Environment Setup
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies  
npm install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database with test data
php artisan db:seed
```

### Development Workflow
```bash
# Start full development environment (server, queue, logs, vite)
composer dev

# Run individual services
php artisan serve           # Laravel development server
php artisan queue:work      # Background job processing
npm run dev                 # Vite development server with hot reload

# Build for production
npm run build
```

### Testing & Code Quality
```bash
# Run test suite
composer test
# or
php artisan test

# Run specific test
php artisan test --filter=TestName

# Code formatting with Laravel Pint
./vendor/bin/pint
```

## Architecture Overview

### Core Structure
- **Laravel 12** backend with **PHP 8.2+**
- **Vite 6.2.4** + **Tailwind CSS 4.0** for frontend builds
- **Laravel Sanctum** for API authentication
- **Repository/Service pattern** with Eloquent ORM

### Key Business Domains
The application manages five main areas:

1. **Patient Management** - Pacientes, HistorialMedico, ConsentimientoInformado
2. **Appointments & Treatments** - Citas, Tratamientos, Valoraciones, Bitacoras  
3. **Staff Management** - Terapeutas, Especialidades, user roles
4. **Clinic Operations** - Clinicas, Tarifas, scheduling
5. **Advanced Features** - Smartwatch integration, surveys, payment processing

### Database Architecture
- 25+ models with complex medical domain relationships
- Role-based access control with 4 user types
- Comprehensive audit trail through Bitacoras
- Support for treatment packages and payment processing

### API Design
- RESTful API with Laravel Resource transformations
- Token-based authentication via Sanctum
- Form Request validation classes
- JSON-first response structure

## Development Notes

### Language Conventions
- Mixed Spanish/English naming (business domain in Spanish, technical terms in English)
- Patient-facing content primarily in Spanish
- API endpoints follow RESTful conventions

### Key File Locations
- Controllers: `app/Http/Controllers/` (20+ domain controllers)
- Models: `app/Models/` (25+ Eloquent models)
- Migrations: `database/migrations/` (comprehensive medical schema)
- API Routes: `routes/api.php`
- Frontend Assets: `resources/` (Vite + Tailwind)

### Testing Setup
- PHPUnit 11.5.3 with SQLite in-memory database
- Model factories for all major entities
- Comprehensive seeders for development data

### Authentication Flow
- Multi-role user system extends Laravel's Authenticatable
- Sanctum tokens for API access
- Role-based route protection and feature access