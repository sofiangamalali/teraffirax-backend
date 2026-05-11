# Dynamic Settings System Implementation

A scalable, production-ready Dynamic Settings System for Laravel, built according to the provided enterprise architecture specification.

## 📂 Folder Structure

The implementation spans the following files:

```text
app/
├── DTOs/
│   └── SettingDTO.php
├── Enums/
│   └── SettingKeyEnum.php

├── Http/
│   ├── Controllers/Api/
│   │   └── SettingController.php
│   ├── Requests/
│   │   ├── StoreSettingRequest.php
│   │   └── UpdateSettingRequest.php
│   └── Resources/
│       └── SettingResource.php

├── Models/
│   └── Setting.php
├── Policies/
│   └── SettingPolicy.php
├── Repositories/
│   ├── Eloquent/
│   │   └── SettingRepository.php
│   └── Interfaces/
│       └── SettingRepositoryInterface.php
├── Services/
│   └── SettingService.php
└── Support/
    └── helpers.php

database/migrations/
└── 2026_05_11_100000_create_settings_table.php

tests/
├── Feature/Api/
│   └── SettingControllerTest.php
└── Unit/Services/
    └── SettingServiceTest.php
```

## 🚀 Usage Examples

### 1. Using the Helper Function
The global `setting()` function is available anywhere in the application.

```php
// Get a setting with a default value
$minAmount = setting('withdrawal.min_amount', 50.0);

// Get a boolean setting
$isMaintenance = setting('app.maintenance_mode', false);

// Get a JSON setting (automatically casted to array)
$config = setting('custom.config', []);
```

### 2. Using the Service Layer (Dependency Injection)
For more advanced operations or inside other services.

```php
use App\Services\SettingService;
use App\DTOs\SettingDTO;

class WithdrawalService
{
    public function __construct(protected SettingService $settingService) {}

    public function process()
    {
        $min = $this->settingService->get('withdrawal.min_amount');
        // ...
    }
    
    public function updateLimit($newLimit)
    {
        $dto = new SettingDTO(
            key: 'withdrawal.min_amount',
            value: (string) $newLimit,
            type: 'float',
            group: 'financial'
        );
        
        $this->settingService->set($dto);
    }
}
```

### 3. API Endpoints (Admin Dashboard Ready)
The system includes standard REST endpoints for managing settings.

- `GET /api/settings?group=financial` - List settings by group.
- `POST /api/settings` - Create or update a setting.
- `GET /api/settings/{key}` - Get a specific setting.
- `PUT /api/settings/{key}` - Update a setting value.
- `DELETE /api/settings/{key}` - Delete a setting and clear cache.

## 💡 Best Practices Explained

### 1. High Performance Caching
- **Forever Cache**: Settings are cached indefinitely using `rememberForever`.
- **Active Invalidation**: The cache is only cleared when a setting is updated or deleted, ensuring maximum read performance.
- **Cache Keying**: Uses the pattern `setting:{key}` for targeted invalidation.

### 2. Type Safety and Casting
- Supported types: `integer`, `float`, `boolean`, `json`, `string`.
- Values are stored as strings in the database but are automatically cast to their proper PHP types when retrieved via the Service or Helper.



### 3. Enum-Driven Keys
- Centralizes valid setting keys in `SettingKeyEnum`.
- Helps prevent typos and provides a single source of truth for available configurations.
