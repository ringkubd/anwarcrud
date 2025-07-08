# Installation & Setup Guide

## Laravel CRUD Generator Package

This guide will help you install and configure the Laravel CRUD Generator package in any Laravel project.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL 5.7+ or PostgreSQL 10+

## Installation

### Step 1: Install via Composer

```bash
composer require anwar/crud-generator
```

### Step 2: Publish Package Assets

The package will auto-register via Laravel's package discovery. Publish the required assets:

```bash
# Publish configuration file
php artisan vendor:publish --tag=crudgenerator-config

# Publish migrations
php artisan vendor:publish --tag=crudgenerator-migrations

# Publish assets (CSS, JS)
php artisan vendor:publish --tag=crudgenerator-assets

# Optional: Publish views for customization
php artisan vendor:publish --tag=crudgenerator-views

# Optional: Publish stubs for customization
php artisan vendor:publish --tag=crudgenerator-stubs
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Configure (Optional)

Edit `config/anwarcrud.php` to customize settings:

```php
<?php

return [
    // Middleware for admin routes
    'admin_middleware' => ['web', 'auth'],
    
    // Namespaces for generated files
    'model_namespace' => 'App\\Models',
    'controller_namespace' => 'App\\Http\\Controllers',
    
    // Features
    'features' => [
        'api_generation' => true,
        'test_generation' => true,
        'documentation_generation' => true,
    ],
];
```

## Quick Start

### Web Interface

1. **Access the Generator:**
   ```
   http://your-app.com/admin/anwar-crud-generator
   ```

2. **Create a CRUD Module:**
   - Enter module name (e.g., "Post")
   - Configure fields and validation
   - Set relationships
   - Choose options (API, soft deletes)
   - Preview and generate

### CLI Usage

```bash
# Generate via Artisan command
php artisan anwar-crud:generate Post \
  --fields="title:string,content:text,status:boolean" \
  --relationships="user:belongsTo" \
  --api \
  --soft-deletes
```

### API Usage

```bash
# Generate via API
curl -X POST http://your-app.com/api/crud-modules \
  -H "Content-Type: application/json" \
  -d '{
    "module": "Product",
    "fields": "name:string,price:decimal,description:text",
    "api": true
  }'
```

## Generated Files

For a `Post` module, the package generates:

```
app/
├── Models/Post.php
├── Http/
│   ├── Controllers/PostController.php
│   ├── Controllers/Api/PostController.php
│   ├── Requests/PostRequest.php
│   └── Resources/PostResource.php
resources/views/posts/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
database/migrations/
└── create_posts_table.php
tests/
├── Feature/PostTest.php
└── Unit/PostTest.php
```

## Features

### ✨ Advanced UI
- Bootstrap 4 responsive interface
- Live preview functionality
- Multi-step wizard
- Field configuration with validation builder

### 🔧 Code Generation
- Models with relationships
- Controllers (Web + API)
- Form validation requests
- API resources
- Blade views
- Database migrations
- PHPUnit tests

### 📚 Documentation
- Automatic API documentation
- Markdown and HTML formats
- Usage examples

### 🧪 Testing
- Feature tests for endpoints
- Unit tests for components
- API testing support

## Configuration Options

### Middleware Configuration

```php
// config/anwarcrud.php
'admin_middleware' => ['web', 'auth', 'admin'],
```

### Custom Namespaces

```php
'model_namespace' => 'App\\Models',
'controller_namespace' => 'App\\Http\\Controllers',
```

### Feature Toggles

```php
'features' => [
    'api_generation' => true,
    'test_generation' => true,
    'documentation_generation' => true,
    'soft_deletes' => true,
],
```

## Customization

### Custom Templates

1. **Publish stubs:**
   ```bash
   php artisan vendor:publish --tag=crudgenerator-stubs
   ```

2. **Edit templates in:** `resources/crud-stubs/`

3. **Available placeholders:**
   - `@modelName` - Model class name
   - `@modelVar` - Model variable name
   - `@fields` - Generated fields
   - `@relationships` - Generated relationships

### Custom Validation Rules

```php
// In your service provider
use Anwar\CrudGenerator\Services\ValidationRuleBuilder;

ValidationRuleBuilder::extend('custom_rule', function($field) {
    return "required|custom_validation:{$field['parameter']}";
});
```

## Troubleshooting

### Common Issues

1. **Permission Errors:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   ```

2. **Migration Issues:**
   ```bash
   php artisan migrate:status
   php artisan migrate
   ```

3. **Asset Issues:**
   ```bash
   php artisan vendor:publish --tag=crudgenerator-assets --force
   ```

4. **Cache Issues:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Debug Mode

Enable debug logging:

```php
// config/anwarcrud.php
'debug' => true,
'log_level' => 'debug',
```

## Support

- **Documentation:** [Full Documentation](DOCUMENTATION.md)
- **Issues:** [GitHub Issues](https://github.com/ringkubd/anwarcrud/issues)
- **Wiki:** [GitHub Wiki](https://github.com/ringkubd/anwarcrud/wiki)

## License

This package is open-source software licensed under the MIT License.
