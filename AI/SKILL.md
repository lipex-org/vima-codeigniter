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

### Key Service: `AccessManager`
The heart of the system. Access via `vima()` helper.
- `can(user, permission, namespace, ...args)`: Evaluates permissions.
- `enforce(...)`: Same as `can`, but throws `AccessDeniedException`.
- `assignRole(user, role)`: Database-level assignment.

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

    public function canEdit(object $user, Post $post): bool
    {
        // Owner or Admin can edit
        return $user->id === $post->user_id || can('admin.posts');
    }
}
```

### 2. Registration Logic
| Method | Usage | Best For |
| :--- | :--- | :--- |
| **Generator** | `php spark vima:make:policy Post` | Creating new policy files. |
| **Manual** | `vima_policy('action', $callback)` | Ad-hoc or closure-based logic. |
| **Route Filter** | `filter => 'vima_policy:action:Class::method'` | Scoped registration (lazy loading). |

---

## 🛠️ CI4 Command Pipeline

Follow this sequence to maintain a robust authorization layer.

### 1. Setup & Config
- `php spark vima:setup`: Publishes `Config/Vima.php`. **Only run once.**
- **Config**: Define your roles and permissions in the `$setup` array in `Config/Vima.php`.

### 2. Synchronization (Push to DB)
- `php spark vima:sync`: Synchronizes `Config/Vima.php` with the database.
- > [!WARNING]
  > Using `vima:sync --refresh` will **WIPE** all permission/role data from the database before re-syncing. Use with extreme caution in production.

### 3. Mapping (Type Safety)
- `php spark vima:generate-maps`: Generates `App\Mappers\Vima\Roles` and `App\Mappers\Vima\Permissions`.
- `php spark vima:generate-maps --ts`: Generates TypeScript equivalents in `resources/js/vima` (configurable via `--ts-dir`).
- > [!IMPORTANT]
  > **NEVER** use magic strings in code. Always use the generated mappers.
  > PHP: `can(Permissions::POSTS_EDIT, $post)`
  > TS: `Permissions.POSTS_EDIT`

### 4. Generation
- `php spark vima:make:policy <Name> --resource <Class>`: Generates a policy template.

---

## 🚦 Integration Workflow

When an agent is asked to implement a feature with authorization, follow this exact workflow:

1.  **Define**: Add the role/permission to `app/Config/Vima.php`. You can also use `php spark vima:make:role` and `php spark vima:make:permission` to generate role and permission classes.
2.  **Sync**: Run `php spark vima:sync`.
3.  **Map**: Run `php spark vima:generate-maps`.
4.  **Extend**: If ABAC is needed, run `php spark vima:make:policy`.
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
`vima()->isPermitted($user, 'project.delete', ['project_id' => 10])`

---

## 🚫 Critical Warnings (The "Don'ts")

- **DO NOT** check roles directly (e.g. `if ($user->role === 'admin')`). Use `can('permission')`.
- **DO NOT** skip `vima:generate-maps`. String-based permissions are prone to typos and make refactoring impossible.
- **DO NOT** define policies without implementing `PolicyInterface`. The system will fail to resolve them.
- **DO NOT** bypass `authorize()` in controllers. It ensures standardized `AccessDeniedException` handling.

---

## 💡 Best Practices
- **Mapper First**: Always run `vima:generate-maps` after any config change.
- **Lean Policies**: Keep policies focused on authorization. Business logic belongs in Services.
- **Fail Closed**: If no user is resolved, the `can()` helper throws an exception. Ensure authentication middleware runs *before* Vima filters.
