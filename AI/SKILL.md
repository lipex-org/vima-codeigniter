---
name: Vima CodeIgniter 4 Adapter (Professional Agent Edition)
description: Master RBAC and ABAC seamlessly with Vima and CI4.
---

# 🛡️ Professional Guidance: Vima Framework for AI Agents

This document is the definitive guide for AI agents working on projects using the `vima` authorization ecosystem within CodeIgniter 4. It covers core logic, class-based policies, CI4 command pipelines, and architectural best practices.

---

## 🏗️ Core Architecture (The Vima Stack)

Vima is a **Contract-First** authorization system consisting of two layers:
1.  **Vima Core**: Framework-agnostic logic for RBAC (Roles) and ABAC (Policies).
2.  **Vima CI4 Adapter**: Integration layer providing helpers (`can()`), traits (`VimaTrait`), and route filters.

### Key Facade & Service: `Vima\Core\Vima`
The primary entry point. Access core services and the fluent builder API via `Vima\Core\Vima` static facade, or evaluate permissions using the global `can()` helper.

#### The Fluent API:
- **User Resource**: `Vima\Core\Vima::user($user)`
  - `grant()->role($role)` / `grant()->permission($permission)`
  - `revoke()->role($role)` / `revoke()->permission($permission)`
  - `deny()->role($role, $reason, $expiresAt)` / `deny()->permission($permission, $reason, $expiresAt)`
  - `undeny()->role($role)` / `undeny()->permission($permission)`
  - `is()->superAdmin()` / `is()->denied()->role($role)` / `is()->denied()->permission($permission)`
  - `has()->role($role)` / `has()->permission($permission)`
  - `get()->roles($resolve)` / `get()->permissions()->direct()`
- **Role Resource**: `Vima\Core\Vima::role($role)`
  - `exists(): bool`
  - `permissions()->add($permission)` / `permissions()->remove($permission)`
  - `parents()->add($parentRole)` / `parents()->remove($parentRole)`
- **Permission Resource**: `Vima\Core\Vima::permission($permission)`
  - `exists(): bool`

#### Global Helpers:
- `can($permission, ...$arguments)`: Checks if the current user has the permission (supports namespacing and policies).
- `can_any(array $permissions, ...$arguments)`: Checks if any of the permissions are granted.
- `can_all(array $permissions, ...$arguments)`: Checks if all of the permissions are granted.

---

## 📜 Mastering Policies (ABAC)

Policies handle complex resource-specific logic. 

### 1. Class-Based Policies (Preferred)
Every class-based policy **must** implement `Vima\Core\Contracts\PolicyInterface`.

#### Syntax & Structure:
```php
namespace App\Policies;

use Vima\Core\Policy\Contracts\PolicyInterface;
use App\Entities\Post;

class PostPolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return Post::class; // Essential for auto-resolving
    }

    public function canEdit(\Vima\Core\Policy\DTOs\AccessContext $ctx, Post $post): bool
    {
        // Owner or Admin can edit
        return $ctx->user->id === $post->user_id || can('admin.posts');
    }
}
```

### 2. Registration & Listing Logic
| Method | Usage | Best For |
| :--- | :--- | :--- |
| **Generator** | `php spark vima:policy create Post` | Creating new policy files (optionally with `--namespace` or `-N`). |
| **Listing** | `php spark vima:policy list` | Viewing all registered and autodiscovered policies. |
| **Manual** | `vima_policy('action', $callback)` | Ad-hoc or closure-based logic. |
| **Route Filter** | `filter => 'vima_policy:action:Class::method'` | Scoped registration (lazy loading). |

---

## 🛠️ CI4 Command Pipeline

Follow this sequence to maintain a robust authorization layer. All subcommands/actions support `--help` or `-h` to display arguments and options details.

### 1. Setup & Config
- `php spark vima:setup`: Publishes `Config/Vima.php` and the default setup provider `Libraries/Vima/Setup.php`. **Only run once.**
- **Config**: Setup providers (implementing `Vima\Core\Config\Contracts\SetupProviderInterface`) are the canonical place to define roles and permissions. The default provider `App\Libraries\Vima\Setup` is published by `vima:setup`. Any classes implementing `SetupProviderInterface` located under `Libraries/Vima/` in any namespace are automatically discovered and aggregated across all application modules.

### 2. Synchronization (Push to DB)
- `php spark vima:sync`: Synchronizes all defined roles and permissions from all registered and discovered setup providers with the database (merging, deduplicating, and expanding wildcards seamlessly).
- > [!WARNING]
  > Using `vima:sync --refresh` will **WIPE** all permission/role data from the database before re-syncing. Use with extreme caution in production.

### 3. Mapping (Type Safety)
- `php spark vima:maps generate`: Generates `App\Mappers\Vima\Roles`, `App\Mappers\Vima\Permissions`, and `App\Mappers\Vima\Namespaces`.
- `php spark vima:maps generate --ts`: Generates TypeScript equivalents in `resources/js/vima` (configurable via `--ts-dir`).
- > [!IMPORTANT]
  > **NEVER** use magic strings in code. Always use the generated mappers.
  > PHP: `can(Permissions::POSTS_EDIT, $post)`
  > TS: `Permissions.POSTS_EDIT`

### 4. Generation
- `php spark vima:policy create <Name> --resource <Class>`: Generates a policy template (supports `--namespace` or `-N` to target autoloader paths).

---

## 🚦 Integration Workflow

When an agent is asked to implement a feature with authorization, follow this exact workflow:

1.  **Define**: Add the roles/permissions to a Setup Provider (e.g. `app/Libraries/Vima/Setup.php`), or write a custom setup provider under a module's `Libraries/Vima/` directory. You can also use `php spark vima:role create` and `php spark vima:permission create` to dynamically declare roles and permissions in the database.
2.  **Sync**: Run `php spark vima:sync`.
3.  **Map**: Run `php spark vima:maps generate`.
4.  **Extend**: If ABAC is needed, run `php spark vima:policy create`.
5.  **Inject**: 
    - Use `VimaTrait` in Controllers.
    - Use `VimaAuthorizeFilter` in `app/Config/Routes.php`.
    - Use `can()` in Views.

---

## 🏔️ Edge Cases & Advanced Usage

### 1. Multi-Tenant Namespacing
Isolate permissions by prefixing the namespace:
`can('tenant_5:reports.view')` or `can('reports.view', 'tenant_5')`.

### 2. Dynamic Resource Resolving (`VimaResourceFilter`)
Automatically load a model based on URI segments:
```php
// In Routes.php
$routes->get('api/posts/(:num)', 'Posts::show', [
    'filter' => 'vima_resource:App\Models\PostModel,1'
]);
// Access later via vima_context()
```

### 3. Contextual RBAC
Check if a user has a role *within* a specific context (e.g., project lead for project X):
`Vima\Core\Vima::auth()->isPermitted($user, 'project.delete', ['project_id' => 10])`

---

## 🚫 Critical Warnings (The "Don'ts")

- **DO NOT** check roles directly (e.g. `if ($user->role === 'admin')`). Use `can('permission')`.
- **DO NOT** skip `vima:maps generate`. String-based permissions are prone to typos and make refactoring impossible.
- **DO NOT** define policies without implementing `PolicyInterface`. The system will fail to resolve them.
- **DO NOT** bypass `authorize()` in controllers. It ensures standardized `AccessDeniedException` handling.

---

## 💡 Best Practices
- **Mapper First**: Always run `vima:maps generate` after any config change.
- **Lean Policies**: Keep policies focused on authorization. Business logic belongs in Services.
- **Fail Closed**: If no user is resolved, the `can()` helper throws an exception. Ensure authentication middleware runs *before* Vima filters.
