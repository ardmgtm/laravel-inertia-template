# Panduan Pengembangan Backend

Dokumentasi ini menjelaskan konvensi, struktur, dan best practices untuk pengembangan backend di proyek Laravel ini.

## Tech Stack Backend

- **PHP 8.4** - Programming language
- **Laravel 13** - Web application framework
- **Inertia.js Laravel v2** - Server-side adapter untuk Inertia
- **Spatie Laravel Permission** - Role & permission management
- **Spatie Laravel Media Library** - File upload & media handling
- **Pest v4 / PHPUnit v12** - Testing framework
- **Laravel Pint v1** - Code style fixer
- **Ziggy v2** - Route name generator untuk JavaScript

## Struktur Folder Backend

```
app/
├── Console/
│   └── Commands/           # Artisan commands
├── Http/
│   ├── Controllers/        # Request handlers
│   ├── Middleware/         # HTTP middleware
│   ├── Requests/          # Form request validation
│   └── Responses/         # Response classes
├── Models/                # Eloquent models
├── Services/              # Business logic layer
├── Traits/                # Reusable traits
└── Providers/             # Service providers

database/
├── factories/             # Model factories untuk testing
├── migrations/            # Database migrations
└── seeders/              # Database seeders

tests/
├── Feature/              # Feature tests (HTTP, integration)
└── Unit/                 # Unit tests (isolated logic)
```

## Architecture Pattern: Service Layer

> **PENTING:** Controller TIDAK boleh mengakses Model secara langsung. Semua business logic harus melalui Service.

### Struktur Service Pattern

```
Controller → Service → Model
         ↓          ↓
    HTTP Logic   Business Logic
```

### Contoh Service Class

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserService
{
    public function getUserQuery(): Builder
    {
        return User::query()->with(['roles']);
    }

    public function createUser(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole($data['roles']);
            DB::commit();

            return ['success' => true, 'message' => 'Success to create user'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }

    public function updateUser(User $user, array $data): array
    {
        DB::beginTransaction();
        try {
            $user->update($data);
            $user->syncRoles($data['roles']);
            DB::commit();

            return ['success' => true, 'message' => 'Success to update user'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }
}
```

### Contoh Controller dengan Service

```php
<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Responses\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function create(CreateUserRequest $request)
    {
        $this->logActivity('Create new user');
        $data = $request->validated();
        $result = $this->userService->createUser($data);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }
}
```

### Kapan Membuat Service Baru?

✅ **Buat service baru jika:**
- Ada business logic yang kompleks
- Perlu reusability di beberapa controller
- Operasi yang melibatkan multiple models
- Ada transaction handling
- Perlu unit testing yang isolated

❌ **Tidak perlu service jika:**
- Hanya simple query tanpa logic (gunakan query builder di controller)
- Single-line operation yang tidak akan direuse

### Prinsip Service yang Baik

1. **Single Responsibility** - Satu service untuk satu domain/entitas
2. **Dependency Injection** - Inject service dependencies via constructor
3. **Return Consistent Format** - Return array dengan `['success' => bool, 'message' => string, 'data' => mixed]`
4. **No Duplicate Logic** - Jika ada fungsi yang sama, gunakan satu service sebagai single source of truth
5. **Transaction Handling** - Gunakan DB transaction untuk operasi multi-step

## Konvensi Penamaan

### File & Class

- **Controllers**: PascalCase dengan suffix `Controller` → `UserController`, `RoleAndPermissionController`
- **Services**: PascalCase dengan suffix `Service` → `UserService`, `AuthenticationService`
- **Models**: PascalCase singular → `User`, `UserActivity`, `Media`
- **Requests**: PascalCase dengan suffix `Request` → `CreateUserRequest`, `LoginRequest`
- **Middleware**: PascalCase tanpa suffix → `Authenticate`, `VerifyCsrfToken`
- **Traits**: PascalCase dengan suffix `Trait` → `UserActivityTrait`, `HasRolesTrait`

### Metode & Variabel

- **Controller methods**: camelCase sesuai action → `index()`, `create()`, `update()`, `delete()`
- **Service methods**: camelCase deskriptif → `createUser()`, `getUserQuery()`, `switchStatus()`
- **Variables**: camelCase → `$userData`, `$isActive`, `$roleId`
- **Constants**: UPPER_SNAKE_CASE → `SUPERADMIN_ROLE_ID`, `MAX_UPLOAD_SIZE`

### Database

- **Tables**: snake_case plural → `users`, `user_activities`, `role_permissions`
- **Pivot tables**: singular_singular alphabetically → `permission_role`, `model_has_roles`
- **Columns**: snake_case → `user_id`, `created_at`, `is_active`, `profile_picture`
- **Foreign keys**: `{table_singular}_id` → `user_id`, `role_id`, `permission_id`

## Form Request Validation

Gunakan Form Request untuk semua validasi input:

```php
<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atau logic authorization
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar',
            'username.unique' => 'Username sudah digunakan',
        ];
    }
}
```

## Database Migrations

### Best Practices

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // jika perlu soft delete
            
            // Indexes
            $table->index('email');
            $table->index('username');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### Konvensi Migration

1. **Nama file**: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`
2. **Foreign keys**: Definisikan dengan `constrained()` dan cascade
3. **Indexes**: Tambahkan index untuk kolom yang sering di-query
4. **Timestamps**: Selalu gunakan `$table->timestamps()` kecuali pivot table
5. **Soft deletes**: Gunakan jika data perlu recoverable

## Eloquent Models

### Model Structure

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    // Accessors
    public function getProfilePictureAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile_picture');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### Model Conventions

1. **Fillable vs Guarded**: Gunakan `$fillable` untuk explicit white-listing
2. **Casts**: Cast tipe data (boolean, datetime, array, json)
3. **Hidden**: Hide sensitive fields dari JSON response
4. **Relationships**: Define semua relationships sebagai methods
5. **Accessors/Mutators**: Gunakan untuk computed attributes
6. **Scopes**: Buat query scopes untuk query yang sering dipakai

## Response Classes

Gunakan custom Response classes untuk konsistensi:

### JsonResponse

```php
<?php

namespace App\Http\Responses;

class JsonResponse
{
    public static function success(string $message, array $data = [], int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function failed(string $message, array $errors = [], int $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
```

### DataTableResponse

```php
// Untuk PrimeVue DataTable pagination
DataTableResponse::load($query);
```

## Testing dengan Pest

### Feature Test Example

```php
<?php

use App\Models\User;

test('user can be created with roles', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post(route('users.create'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'roles' => [1],
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'username' => 'johndoe',
    ]);
});

test('user creation fails with invalid email', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post(route('users.create'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'username' => 'johndoe',
            'password' => 'password123',
            'roles' => [1],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
});
```

### Unit Test Example

```php
<?php

use App\Services\UserService;
use App\Models\User;

it('creates user successfully', function () {
    $service = new UserService();
    
    $data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testuser',
        'password' => bcrypt('password'),
        'roles' => [1],
    ];
    
    $result = $service->createUser($data);
    
    expect($result['success'])->toBeTrue();
    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});
```

### Testing Best Practices

1. **Factories**: Gunakan factories untuk semua model creation
2. **Database Transactions**: Pest otomatis rollback, gunakan `RefreshDatabase` trait
3. **Naming**: Test descriptions harus jelas dan descriptive
4. **Arrange-Act-Assert**: Struktur test dengan pola AAA
5. **Mock External Services**: Jangan hit external APIs saat testing

## Middleware

### Custom Middleware Example

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['message' => 'Account is inactive']);
        }

        return $next($request);
    }
}
```

Register di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'active' => \App\Http\Middleware\CheckUserActive::class,
    ]);
})
```

## Inertia.js Server-Side

### Returning Inertia Pages

```php
use Inertia\Inertia;

public function index(Request $request)
{
    return Inertia::render('User/UserManageView', [
        'users' => User::with('roles')->get(),
        'filters' => $request->only('search', 'status'),
    ]);
}
```

### Lazy Data Loading

```php
return Inertia::render('Dashboard', [
    'stats' => [
        'users' => User::count(),
        'active' => User::where('is_active', true)->count(),
    ],
    // Lazy load - hanya dimuat saat dibutuhkan
    'recentActivities' => fn () => UserActivity::latest()->take(10)->get(),
]);
```

### Sharing Data Globally

Di `HandleInertiaRequests` middleware:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user()?->only('id', 'name', 'email', 'username', 'profile_picture'),
            'roles' => $request->user()?->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]),
            'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
        ],
    ]);
}
```

## Security Best Practices

### 1. Mass Assignment Protection

```php
// ✅ Good - use fillable
protected $fillable = ['name', 'email'];

// ❌ Bad - avoid guarded = []
protected $guarded = [];
```

### 2. SQL Injection Prevention

```php
// ✅ Good - query builder with bindings
User::where('email', $email)->first();

// ❌ Bad - raw queries without bindings
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### 3. XSS Prevention

```php
// Blade/Vue otomatis escape
// Manual escape jika perlu:
{{ htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') }}
```

### 4. CSRF Protection

```php
// Otomatis teraktivasi untuk POST/PUT/DELETE
// Jangan disable VerifyCsrfToken untuk route yang tidak perlu
```

### 5. Authorization

```php
// Gunakan Gates atau Policies
Gate::define('update-user', function ($user, $targetUser) {
    return $user->id === $targetUser->id || $user->hasRole('admin');
});

// Di controller
$this->authorize('update-user', $targetUser);
```

## File Upload dengan Spatie Media Library

```php
// Di controller
public function uploadProfilePicture(Request $request)
{
    $user = auth()->user();
    
    if ($request->hasFile('profile_picture')) {
        $user->addMedia($request->file('profile_picture'))
            ->toMediaCollection('profile_picture');
    }
    
    return back();
}

// Di model
public function getProfilePictureAttribute(): ?string
{
    return $this->getFirstMediaUrl('profile_picture');
}
```

## Activity Logging

Gunakan `UserActivityTrait` untuk automatic logging:

```php
<?php

namespace App\Http\Controllers;

use App\Traits\UserActivityTrait;

class UserController extends Controller
{
    use UserActivityTrait;

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity('Update user (id: '.$user->id.')');
        
        // ... update logic
    }
}
```

## Artisan Commands

### Custom Command Example

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DeactivateInactiveUsers extends Command
{
    protected $signature = 'users:deactivate-inactive {--days=90}';
    protected $description = 'Deactivate users who have not logged in for specified days';

    public function handle()
    {
        $days = $this->option('days');
        
        $count = User::where('last_login_at', '<', now()->subDays($days))
            ->update(['is_active' => false]);
            
        $this->info("Deactivated {$count} inactive users");
    }
}
```

## Code Quality

### Laravel Pint

Jalankan sebelum commit:

```bash
vendor/bin/pint --dirty --format agent
```

### Static Analysis

```bash
# PHPStan (jika diinstall)
./vendor/bin/phpstan analyse

# Larastan (wrapper PHPStan untuk Laravel)
php artisan code:analyse
```

## Performance Tips

1. **Eager Loading**: Gunakan `with()` untuk avoid N+1 queries
2. **Chunk Large Queries**: Gunakan `chunk()` untuk process large datasets
3. **Cache**: Cache query results yang sering diakses
4. **Queue**: Offload heavy tasks ke queue (email, exports, dll)
5. **Database Indexes**: Index kolom yang sering di-query atau di-join

```php
// ✅ Good - eager loading
$users = User::with('roles', 'permissions')->get();

// ❌ Bad - N+1 query problem
$users = User::all();
foreach ($users as $user) {
    $roles = $user->roles; // Query per user!
}
```

## Environment Configuration

`.env` file structure:

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Media Library
FILESYSTEM_DISK=public

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Broadcasting (Reverb)
BROADCAST_CONNECTION=reverb
```

## Useful Artisan Commands

```bash
# Membuat berbagai files
php artisan make:controller UserController
php artisan make:model User -mfs  # model + migration + factory + seeder
php artisan make:service UserService  # custom command (gunakan make:class)
php artisan make:request CreateUserRequest
php artisan make:middleware CheckUserActive

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Testing
php artisan test --compact
php artisan test --filter=testName

# Routes & Config
php artisan route:list
php artisan config:show app.name

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
php artisan optimize:clear
```

## Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate `APP_KEY` baru di production
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set proper file permissions (storage/ & bootstrap/cache/)
- [ ] Configure queue workers
- [ ] Setup scheduled tasks (cron)
- [ ] Enable HTTPS/SSL
- [ ] Configure backup strategy

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Pest Documentation](https://pestphp.com/docs)
- [Inertia.js Laravel Adapter](https://inertiajs.com/server-side-setup)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
