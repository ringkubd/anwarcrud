# Laravel CRUD Generator Package Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Features](#features)
5. [Basic Usage](#basic-usage)
6. [Advanced Usage](#advanced-usage)
7. [API Reference](#api-reference)
8. [CLI Commands](#cli-commands)
9. [Customization](#customization)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)
12. [Contributing](#contributing)

## Introduction

The Laravel CRUD Generator is a powerful package that automates the creation of CRUD (Create, Read, Update, Delete) operations for Laravel applications. It generates models, controllers, views, migrations, requests, resources, and tests with comprehensive validation support.

### Key Features

- **Complete CRUD Generation**: Models, Controllers, Views, Migrations, Requests, Resources
- **Advanced UI**: Bootstrap 4 interface with live preview and field configuration
- **Validation Support**: Full Laravel validation rule support with visual builder
- **API Generation**: REST API controllers and resources
- **Documentation Generation**: Automatic API documentation (Markdown & HTML)
- **CLI Integration**: Artisan commands for automated generation
- **Testing Support**: Automated test generation
- **Customizable Templates**: Stub-based template system

## Installation

### Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL 5.7+ or PostgreSQL 10+

### Step 1: Install via Composer

```bash
composer require anwar/crud-generator
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Anwar\CrudGenerator\AnwarCrudGeneratorProvider"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Install Assets (Optional)

```bash
php artisan anwar-crud:install
```

## Configuration

### Config File: `config/anwarcrud.php`

```php
<?php

return [
    // Default namespace for generated models
    'model_namespace' => 'App\\Models',
    
    // Default namespace for generated controllers
    'controller_namespace' => 'App\\Http\\Controllers',
    
    // Default view path
    'view_path' => 'resources/views',
    
    // Enable/disable features
    'features' => [
        'api_generation' => true,
        'test_generation' => true,
        'documentation_generation' => true,
        'soft_deletes' => true,
        'timestamps' => true,
    ],
    
    // Validation rules
    'validation' => [
        'default_rules' => [
            'string' => 'required|string|max:255',
            'text' => 'nullable|string',
            'integer' => 'required|integer',
            'boolean' => 'boolean',
            'date' => 'required|date',
            'email' => 'required|email',
        ],
    ],
    
    // UI Configuration
    'ui' => [
        'theme' => 'bootstrap4',
        'show_preview' => true,
        'enable_live_preview' => true,
    ],
];
```

## Features

### 1. Model Generation

Generates Eloquent models with:
- **Fillable attributes**
- **Relationships** (belongsTo, hasMany, hasOne, belongsToMany)
- **Soft deletes** (optional)
- **Accessors and mutators**
- **PHPDoc properties**

### 2. Controller Generation

Creates controllers with:
- **Standard CRUD methods** (index, create, store, show, edit, update, destroy)
- **API controllers** (RESTful endpoints)
- **Form validation** using Request classes
- **Resource responses** for APIs
- **Proper error handling**

### 3. View Generation

Bootstrap 4 responsive views:
- **Index view** with data tables and pagination
- **Create/Edit forms** with validation display
- **Show/Detail pages** with formatted data display
- **Modal support** for quick actions

### 4. Migration Generation

Database migrations with:
- **All field types** (string, text, integer, boolean, date, etc.)
- **Foreign key constraints**
- **Indexes** and unique constraints
- **Soft deletes** support

### 5. Request Classes

Form Request validation with:
- **Laravel validation rules**
- **Custom error messages**
- **Authorization logic**
- **Field attribute labels**

### 6. API Resources

JSON API resources with:
- **Data transformation**
- **Relationship inclusion**
- **Conditional fields**
- **Metadata support**

### 7. Test Generation

Comprehensive test suites:
- **Feature tests** for HTTP endpoints
- **Unit tests** for models and services
- **API tests** for RESTful endpoints
- **Database testing** with factories

## Basic Usage

### Web Interface

1. **Access the Generator**
   ```
   http://your-app.com/admin/anwar-crud-generator
   ```

2. **Fill the Form**
   - Module name (e.g., "Post")
   - Fields with types and validation
   - Relationships
   - Options (API, soft deletes)

3. **Preview & Generate**
   - Use live preview to see generated code
   - Click "Generate" to create files

### Example: Creating a Blog Post Module

```php
// Form Input
Module: Post
Fields:
- title: string (required|string|max:255)
- content: text (required|string)
- status: boolean (boolean)
- published_at: datetime (nullable|date)

Relationships:
- user: belongsTo (User model)
- comments: hasMany (Comment model)

Options:
- ✓ Generate API
- ✓ Soft Deletes
```

This generates:
- `app/Models/Post.php`
- `app/Http/Controllers/PostController.php`
- `app/Http/Controllers/Api/PostController.php`
- `app/Http/Requests/PostRequest.php`
- `app/Http/Resources/PostResource.php`
- `resources/views/posts/` (index, create, edit, show)
- `database/migrations/create_posts_table.php`
- `tests/Feature/PostTest.php`
- `tests/Unit/PostTest.php`

## Advanced Usage

### Custom Field Types

The package supports all Laravel migration field types:

```php
'fields' => [
    ['name' => 'title', 'type' => 'string', 'validation' => 'required|string|max:255'],
    ['name' => 'content', 'type' => 'text', 'validation' => 'required|string'],
    ['name' => 'price', 'type' => 'decimal', 'validation' => 'required|numeric|min:0'],
    ['name' => 'tags', 'type' => 'json', 'validation' => 'nullable|array'],
    ['name' => 'image', 'type' => 'string', 'validation' => 'nullable|image|max:2048'],
]
```

### Complex Validation Rules

Support for all Laravel validation rules:

```php
'validation' => 'required|string|min:3|max:255|unique:posts,title|regex:/^[a-zA-Z\s]+$/'
```

### Relationship Configuration

```php
'relationships' => [
    [
        'name' => 'category',
        'type' => 'belongsTo',
        'model' => 'Category',
        'foreign_key' => 'category_id',
        'local_key' => 'id'
    ],
    [
        'name' => 'tags',
        'type' => 'belongsToMany',
        'model' => 'Tag',
        'pivot_table' => 'post_tags',
        'foreign_key' => 'post_id',
        'related_key' => 'tag_id'
    ]
]
```

### API Configuration

```php
'api' => [
    'enabled' => true,
    'version' => 'v1',
    'middleware' => ['auth:sanctum'],
    'rate_limiting' => '60,1', // 60 requests per minute
    'pagination' => 15
]
```

## API Reference

### ModuleGeneratorController

#### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/anwar-crud-generator` | Show generator interface |
| POST | `/admin/anwar-crud-generator/preview` | Preview generated code |
| POST | `/admin/anwar-crud-generator/generate` | Generate CRUD files |
| GET | `/api/crud-modules` | List all modules |
| POST | `/api/crud-modules` | Generate module via API |
| DELETE | `/api/crud-modules/{module}` | Delete module |
| GET | `/admin/anwar-crud-generator/docs/{module}` | Generate documentation |

#### API Usage Examples

**List Modules**
```bash
curl -X GET http://your-app.com/api/crud-modules \
  -H "Accept: application/json" \
  -H "Authorization: Bearer your-token"
```

**Generate Module**
```bash
curl -X POST http://your-app.com/api/crud-modules \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "module": "Product",
    "fields": "name:string,price:decimal,description:text",
    "relationships": "category:belongsTo",
    "api": true,
    "softdeletes": false
  }'
```

**Delete Module**
```bash
curl -X DELETE http://your-app.com/api/crud-modules/Product \
  -H "Authorization: Bearer your-token"
```

### Response Formats

**Success Response**
```json
{
  "success": true,
  "message": "Module generated successfully",
  "data": {
    "module": "Product",
    "files_created": [
      "app/Models/Product.php",
      "app/Http/Controllers/ProductController.php",
      "..."
    ],
    "routes_added": [
      "Route::resource('products', 'ProductController')"
    ]
  }
}
```

**Error Response**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "module": ["The module field is required."],
    "fields": ["The fields field must be a valid format."]
  }
}
```

## CLI Commands

### Generate CRUD via Artisan

```bash
# Basic generation
php artisan anwar-crud:generate Product name:string,price:decimal

# With options
php artisan anwar-crud:generate Product \
  --fields="name:string,price:decimal,description:text" \
  --relationships="category:belongsTo" \
  --api \
  --soft-deletes \
  --force
```

### Command Options

| Option | Description |
|--------|-------------|
| `--fields` | Field definitions (name:type,name:type) |
| `--relationships` | Relationship definitions |
| `--api` | Generate API controllers and resources |
| `--soft-deletes` | Add soft deletes to model |
| `--force` | Overwrite existing files |
| `--no-migration` | Skip migration generation |
| `--no-views` | Skip view generation |
| `--no-tests` | Skip test generation |

### List Generated Modules

```bash
php artisan anwar-crud:list
```

### Delete Generated Module

```bash
php artisan anwar-crud:delete Product --force
```

## Customization

### Custom Stubs

1. **Publish Stubs**
   ```bash
   php artisan vendor:publish --tag=anwar-crud-stubs
   ```

2. **Modify Templates**
   Edit files in `resources/crud-stubs/`:
   - `model.stub` - Model template
   - `controller.stub` - Controller template
   - `request.stub` - Request template
   - `view_index.stub` - Index view template
   - And more...

3. **Custom Placeholders**
   ```php
   // In your stub files
   @modelName - Model class name
   @modelVar - Model variable name
   @modelTable - Database table name
   @fields - Generated fields
   @relationships - Generated relationships
   ```

### Custom Field Types

Add custom field types in config:

```php
'custom_fields' => [
    'phone' => [
        'migration_type' => 'string',
        'validation' => 'required|string|regex:/^[0-9\-\+\s\(\)]+$/',
        'input_type' => 'tel'
    ],
    'slug' => [
        'migration_type' => 'string',
        'validation' => 'required|string|unique:@table,slug',
        'input_type' => 'text'
    ]
]
```

### Custom Validation Rules

Extend validation in your service provider:

```php
use Anwar\CrudGenerator\Services\ValidationRuleBuilder;

public function boot()
{
    ValidationRuleBuilder::extend('custom_rule', function($field) {
        return "required|custom_validation:{$field['parameter']}";
    });
}
```

## Testing

### Running Package Tests

```bash
# Run all tests
vendor/bin/phpunit packages/CrudGenerator/tests/

# Run specific test suite
vendor/bin/phpunit packages/CrudGenerator/tests/Feature/
vendor/bin/phpunit packages/CrudGenerator/tests/Unit/

# Run with coverage
vendor/bin/phpunit packages/CrudGenerator/tests/ --coverage-html coverage/
```

### Test Structure

```
tests/
├── Feature/
│   ├── CrudGeneratorTest.php      # Integration tests
│   └── ApiEndpointsTest.php       # API testing
├── Unit/
│   ├── ModuleGeneratorControllerTest.php
│   ├── ScaffoldGeneratorTest.php
│   └── ValidationRuleBuilderTest.php
└── TestCase.php                   # Base test class
```

### Writing Tests for Generated Code

Generated modules include test files:

```php
// tests/Feature/PostTest.php
class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_post()
    {
        $data = Post::factory()->make()->toArray();
        
        $response = $this->post(route('posts.store'), $data);
        
        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', $data);
    }
    
    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(route('posts.store'), []);
        
        $response->assertSessionHasErrors(['title', 'content']);
    }
}
```

## Troubleshooting

### Common Issues

**1. Permission Errors**
```bash
# Fix directory permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Fix file permissions
chmod 644 storage/logs/laravel.log
```

**2. Namespace Issues**
```php
// Ensure correct namespace in config/anwarcrud.php
'model_namespace' => 'App\\Models',
'controller_namespace' => 'App\\Http\\Controllers',
```

**3. Migration Errors**
```bash
# Reset migrations if needed
php artisan migrate:reset
php artisan migrate
```

**4. Asset Publishing Issues**
```bash
# Re-publish assets
php artisan vendor:publish --provider="Anwar\CrudGenerator\AnwarCrudGeneratorProvider" --force
```

### Debug Mode

Enable debug mode in config:

```php
'debug' => true,
'log_level' => 'debug',
'log_queries' => true,
```

### Performance Optimization

**1. Cache Configuration**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**2. Optimize Autoloader**
```bash
composer dump-autoload --optimize
```

**3. Database Optimization**
```php
// Add indexes to generated migrations
$table->index(['status', 'created_at']);
$table->index('user_id');
```

## Contributing

### Development Setup

1. **Clone Repository**
   ```bash
   git clone https://github.com/ringkubd/anwarcrud.git
   cd crud-generator
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Testing Environment**
   ```bash
   cp .env.example .env.testing
   php artisan key:generate --env=testing
   ```

### Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for all public methods
- Write tests for new features

### Pull Request Process

1. Create feature branch from `develop`
2. Write tests for new functionality
3. Update documentation
4. Submit pull request with detailed description
5. Wait for code review and feedback

### Bug Reports

When reporting bugs, include:
- Laravel version
- PHP version
- Package version
- Steps to reproduce
- Error messages/logs
- Expected vs actual behavior

---

## License

This package is open-source software licensed under the [MIT License](LICENSE).

## Support

- **Documentation**: [GitHub Wiki](https://github.com/ringkubd/anwarcrud/wiki)
- **Issues**: [GitHub Issues](https://github.com/ringkubd/anwarcrud/issues)
- **Discussions**: [GitHub Discussions](https://github.com/ringkubd/anwarcrud/discussions)
- **Email**: support@anwarjahid.com

---

*Last updated: {{ date('Y-m-d') }}*
*Version: 2.0.0*
