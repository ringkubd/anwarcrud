
# Laravel CRUD Generator (anwar/crud-generator)

A powerful, interactive Laravel package to generate complete CRUD (Create, Read, Update, Delete) scaffolding for your Laravel 8/9/10/11+ projects. This package helps you rapidly scaffold models, migrations, controllers (API or web), form requests, resources, and Blade views, with support for relationships and soft deletes.

---

## Features

- **Interactive Artisan Command**: Step-by-step prompts for module name, fields, relationships, and options.
- **Model Generation**: With fillable fields, relationships, PHPDoc, and optional soft deletes.
- **Migration Generation**: With all specified fields and relationships.
- **Controller Generation**: API or web, with full CRUD methods.
- **Form Request Generation**: For validation rules.
- **Resource Generation**: For API responses.
- **Blade View Generation**: index, create, edit, show (Bootstrap styled).
- **Automatic Route Registration**: Adds routes to `web.php` or `api.php`.
- **Supports Laravel 8, 9, 10, 11+**

---

## Installation

1. **Require the package** (if using as a Composer dependency):

```bash
composer require anwar/crud-generator
```

2. **(If developing locally)**: Add to your `composer.json` repositories and require as `path` type, or symlink into `vendor/`.

3. **Publish assets/config (optional):**

```bash
php artisan vendor:publish --provider="Anwar\\CrudGenerator\\AnwarCrudGeneratorProvider"
```

4. **Register the Service Provider** (if not auto-discovered):

Add to `config/app.php`:
```php
'providers' => [
    // ...
    Anwar\\CrudGenerator\\AnwarCrudGeneratorProvider::class,
],
```

---

## Usage

### Interactive Command

Run the following Artisan command:

```bash
php artisan anwar:crudgenerator
```

Or, for local development/testing (see `TestCrudCommand.php`):

```bash
php artisan test:crud {ModuleName} --fields="field:type,field2:type" [--api] [--softdeletes] [--relationships="rel:type,..."]
```

#### Example:

```bash
php artisan test:crud Product --fields="name:string,price:decimal,description:text" --softdeletes --relationships="user:belongsTo,comments:hasMany"
```

#### Options:
- `module` (argument): Name of the module (e.g. `Post`)
- `--fields`: Comma-separated list of fields (e.g. `title:string,body:text`)
- `--api`: Generate API controller and routes
- `--softdeletes`: Add soft deletes to the model
- `--relationships`: Comma-separated relationships (e.g. `user:belongsTo,comments:hasMany`)

---

## What Gets Generated?

- **Model**: `app/Models/{Module}.php`
- **Migration**: `database/migrations/{timestamp}_create_{modules}_table.php`
- **Controller**: `app/Http/Controllers/{Module}Controller.php` or `app/Http/Controllers/Api/{Module}Controller.php`
- **Form Request**: `app/Http/Requests/{Module}Request.php`
- **Resource**: `app/Http/Resources/{Module}Resource.php`
- **Views**: `resources/views/{module}/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
- **Routes**: Added to `routes/web.php` or `routes/api.php`

---

## Example Workflow

1. **Generate a CRUD for a Blog Post:**

```bash
php artisan anwar:crudgenerator
# or
php artisan test:crud Post --fields="title:string,body:text,published:boolean" --relationships="user:belongsTo,comments:hasMany"
```

2. **Check generated files:**
   - Model: `app/Models/Post.php`
   - Migration: `database/migrations/xxxx_xx_xx_create_posts_table.php`
   - Controller: `app/Http/Controllers/PostController.php`
   - Views: `resources/views/post/`
   - Routes: `routes/web.php`

3. **Run migrations:**

```bash
php artisan migrate
```

4. **Visit your app and use the generated CRUD!**

---

## Advanced

- **API Mode**: Use `--api` to generate API controllers/resources/routes only.
- **Soft Deletes**: Use `--softdeletes` to add soft delete support to your model and migration.
- **Relationships**: Use `--relationships` to define Eloquent relationships (e.g. `user:belongsTo,comments:hasMany`).

---

## Customization

- **Stubs**: You can customize the stub files in `packages/CrudGenerator/src/stubs/` to change the generated code style.
- **Views**: Edit the generated Blade files for your own UI/UX needs.

---

## Contributing

Pull requests and issues are welcome! Please fork the repo and submit your improvements.

---

## Credits

- Developed by Anwar
- Inspired by Laravel community best practices

---

## License

MIT
