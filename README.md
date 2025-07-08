# Laravel CRUD Generator

[![Latest Version](https://img.shields.io/github/v/release/ringkubd/anwarcrud)](https://github.com/ringkubd/anwarcrud/releases)
[![License](https://img.shields.io/github/license/ringkubd/anwarcrud)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/anwar/crud-generator)](https://packagist.org/packages/anwar/crud-generator)
[![Tests](https://github.com/ringkubd/anwarcrud/workflows/Tests/badge.svg)](https://github.com/ringkubd/anwarcrud/actions)

A powerful Laravel package that automates the creation of CRUD operations with advanced features including API generation, comprehensive validation support, and modern UI interface.

## 🚨 Important Safety Notice

**⚠️ ALWAYS BACKUP YOUR DATABASE BEFORE INSTALLATION**

This package includes migrations. For your safety:
- Migrations are NOT automatically run
- You must manually publish and review them
- See [SAFETY-NOTICE.md](SAFETY-NOTICE.md) for complete safety guidelines

## ✨ Features

- 🚀 **Complete CRUD Generation** - Models, Controllers, Views, Migrations, Requests, Resources
- 🎨 **Modern UI Interface** - Bootstrap 4 with live preview and advanced field configuration
- ✅ **Full Validation Support** - All Laravel validation rules with visual builder
- 🔗 **API Generation** - RESTful controllers and JSON resources
- 📚 **Auto Documentation** - Generate API docs in Markdown and HTML
- ⚡ **CLI Integration** - Artisan commands for automation
- 🧪 **Test Generation** - Feature and unit tests included
- 🎯 **Customizable Templates** - Stub-based system for full customization
- 🛡️ **Database Safe** - No automatic migrations, full user control

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL 5.7+ or PostgreSQL 10+

## 🚀 Quick Start

### Safe Installation

```bash
# 1. BACKUP YOUR DATABASE FIRST!
mysqldump -u user -p database_name > backup.sql

# 2. Install the package
composer require anwar/crud-generator

# 3. Publish assets (migrations will NOT auto-run)
php artisan vendor:publish --tag=crudgenerator-config
php artisan vendor:publish --tag=crudgenerator-migrations
php artisan vendor:publish --tag=crudgenerator-assets

# 4. Review published migrations first!
ls database/migrations/*anwar*

# 5. Only run if safe
php artisan migrate
```

### Access the Generator

Visit: `http://your-app.com/admin/anwar-crud-generator`

## 🎯 Usage Examples

### Web Interface

1. **Access the Generator Interface**
   ```
   http://your-app.com/admin/anwar-crud-generator
   ```

2. **Configure Your Module**
   - **Module Name**: `Post`
   - **Fields**: 
     - `title` (string, required|max:255)
     - `content` (text, required)
     - `status` (boolean)
     - `published_at` (datetime, nullable)
   - **Relationships**: 
     - `user` (belongsTo User)
     - `comments` (hasMany Comment)
   - **Options**: ✓ API, ✓ Soft Deletes

3. **Preview and Generate**
   - Use live preview to review generated code
   - Click "Generate" to create all files

### CLI Commands

```bash
# Generate via Artisan
php artisan anwar-crud:generate Post \
  --fields="title:string,content:text,status:boolean" \
  --relationships="user:belongsTo" \
  --api \
  --soft-deletes

# List generated modules
php artisan anwar-crud:list

# Delete module
php artisan anwar-crud:delete Post --force
```

### API Usage

```bash
# Generate via API
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

# List modules
curl -X GET http://your-app.com/api/crud-modules \
  -H "Authorization: Bearer your-token"
```

## 📁 Generated Files

When you generate a `Post` module, you'll get:

```
app/
├── Models/Post.php                    # Eloquent model with relationships
├── Http/
│   ├── Controllers/
│   │   ├── PostController.php         # Web controller
│   │   └── Api/PostController.php     # API controller
│   ├── Requests/PostRequest.php       # Form validation
│   └── Resources/PostResource.php     # API resource
resources/views/posts/                 # Bootstrap 4 views
├── index.blade.php                    # List view with DataTables
├── create.blade.php                   # Create form
├── edit.blade.php                     # Edit form
└── show.blade.php                     # Detail view
database/migrations/
└── create_posts_table.php             # Database migration
tests/
├── Feature/PostTest.php               # Feature tests
└── Unit/PostTest.php                  # Unit tests
```

## 🎨 Advanced Features

### Field Types & Validation

Support for all Laravel field types and validation rules:

```php
'fields' => [
    ['name' => 'email', 'type' => 'string', 'validation' => 'required|email|unique:users'],
    ['name' => 'age', 'type' => 'integer', 'validation' => 'required|integer|min:18|max:65'],
    ['name' => 'bio', 'type' => 'text', 'validation' => 'nullable|string|max:1000'],
    ['name' => 'avatar', 'type' => 'string', 'validation' => 'nullable|image|max:2048'],
    ['name' => 'settings', 'type' => 'json', 'validation' => 'nullable|array'],
    ['name' => 'salary', 'type' => 'decimal', 'validation' => 'required|numeric|min:0'],
]
```

### Complex Relationships

```php
'relationships' => [
    [
        'name' => 'user',
        'type' => 'belongsTo',
        'model' => 'User',
        'foreign_key' => 'user_id'
    ],
    [
        'name' => 'tags',
        'type' => 'belongsToMany',
        'model' => 'Tag',
        'pivot_table' => 'post_tags'
    ]
]
```

### API Features

Generated API controllers include:

- ✅ RESTful endpoints (GET, POST, PUT, DELETE)
- ✅ JSON API resources with data transformation
- ✅ Proper HTTP status codes
- ✅ Error handling and validation
- ✅ Pagination support
- ✅ Rate limiting ready

Example API endpoints:
```
GET    /api/posts           # List all posts
POST   /api/posts           # Create new post
GET    /api/posts/{id}      # Get specific post
PUT    /api/posts/{id}      # Update post
DELETE /api/posts/{id}      # Delete post
```

## 🔧 Configuration

Customize the package behavior in `config/anwarcrud.php`:

```php
return [
    'model_namespace' => 'App\\Models',
    'controller_namespace' => 'App\\Http\\Controllers',
    'view_path' => 'resources/views',
    
    'features' => [
        'api_generation' => true,
        'test_generation' => true,
        'documentation_generation' => true,
        'soft_deletes' => true,
    ],
    
    'ui' => [
        'theme' => 'bootstrap4',
        'show_preview' => true,
        'enable_live_preview' => true,
    ],
];
```

## 🎯 Customization

### Custom Templates

1. Publish stubs:
   ```bash
   php artisan vendor:publish --tag=anwar-crud-stubs
   ```

2. Modify templates in `resources/crud-stubs/`

3. Use custom placeholders:
   - `@modelName` - Model class name
   - `@modelVar` - Model variable name
   - `@fields` - Generated fields
   - `@relationships` - Generated relationships

### Custom Field Types

Add custom field types:

```php
'custom_fields' => [
    'phone' => [
        'migration_type' => 'string',
        'validation' => 'required|string|regex:/^[0-9\-\+\s\(\)]+$/',
        'input_type' => 'tel'
    ]
]
```

## 🧪 Testing

Run package tests:

```bash
vendor/bin/phpunit packages/CrudGenerator/tests/
```

Generated modules include comprehensive tests:

```php
// Feature Test Example
public function test_can_create_post()
{
    $data = ['title' => 'Test Post', 'content' => 'Test Content'];
    
    $response = $this->post(route('posts.store'), $data);
    
    $response->assertRedirect(route('posts.index'));
    $this->assertDatabaseHas('posts', $data);
}

// API Test Example  
public function test_api_can_list_posts()
{
    Post::factory(3)->create();
    
    $response = $this->getJson('/api/posts');
    
    $response->assertOk()
             ->assertJsonCount(3, 'data');
}
```

## 📖 Documentation

- **Full Documentation**: [DOCUMENTATION.md](DOCUMENTATION.md)
- **API Reference**: Complete API documentation with examples
- **Video Tutorials**: [Coming Soon]
- **Wiki**: [GitHub Wiki](https://github.com/ringkubd/anwarcrud/wiki)

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/ringkubd/crud-generator.git
cd crud-generator
composer install
cp .env.example .env.testing
php artisan key:generate --env=testing
vendor/bin/phpunit
```

## 🐛 Bug Reports & Feature Requests

Please use [GitHub Issues](https://github.com/ringkubd/anwarcrud/issues) for:
- 🐛 Bug reports
- 💡 Feature requests  
- 📝 Documentation improvements
- ❓ Questions and support

## 📄 License

This package is open-source software licensed under the [MIT License](LICENSE).

## 🙏 Credits

- **Author**: [Anwar Jahid](https://anwarjahid.com) | ajr.jahid@gmail.com
- **Contributors**: [All Contributors](https://github.com/ringkubd/anwarcrud/contributors)
- **Inspired by**: Laravel community and best practices

## 🔗 Links

- **Packagist**: [anwar/crud-generator](https://packagist.org/packages/anwar/crud-generator)
- **GitHub**: [ringkubd/anwarcrud](https://github.com/ringkubd/anwarcrud)
- **Issues**: [Report Issues](https://github.com/ringkubd/anwarcrud/issues)
- **Discussions**: [GitHub Discussions](https://github.com/ringkubd/anwarcrud/discussions)

---

⭐ **Star this repo** if you find it useful!

*Made with ❤️ for the Laravel community*
