# Vima CodeIgniter 4 Integration

The CodeIgniter 4 bridge provides a native integration of the Vima Core engine into CodeIgniter 4 applications using Spark commands, filters, and standard services.

---

## 1. Setup & Installation

To set up the Vima package in a CodeIgniter 4 application:

1. **Install Dependencies**: Run composer installation.
2. **Publish Configuration**: Run the setup command to publish tables and settings:
   ```bash
   php spark vima:setup
   ```
3. **Run Migrations**: Create the access control schema tables:
   ```bash
   php spark migrate
   ```

---

## 2. Configuration (`app/Config/Vima.php`)

The configuration file allows customizing the following:
- **`$policies`**: Toggle policy class auto-discovery and specify directory structures.
- **`$user`**: Set user resolutions and method key lookups (e.g. `$user['current'] = fn() => auth()->user()`).
- **`$superAdmin`**: Set Super Admin role and toggle automatic authorization bypass.
- **`$cache`**: Configure caching TTLs and prefixes.
- **`$audit`**: Grouped settings for auditing (e.g. `$audit['enabled']` and `$audit['level']`).
- **`$view403`**: Define the template view rendered when access is denied.

---

## 3. Route Protection Filters

Vima includes three filters to secure application routes. Secure configurations can be generated using the type-safe `VimaFilterBuilder`:

### VimaAuthorizeFilter (`vima_authorize`)
Checks if the resolved user has permission.
- **Usage**: `vima_authorize:permission_name`
- **Builder**: `VimaFilterBuilder::authorize('posts.create')`

### VimaResourceFilter (`vima_resource`)
Loads a database model resource based on URL route segments.
- **Usage**: `vima_resource:ModelName,segmentIndex`
- **Builder**: `VimaFilterBuilder::resource('PostModel', 1)`

### VimaPolicyFilter (`vima_policy`)
Registers dynamic policies on target routes.
- **Usage**: `vima_policy:action:PolicyClass::method`
- **Builder**: `VimaFilterBuilder::policy('edit', 'App\Policies\PostPolicy::canEdit')`

---

## 4. Commands and Permission Syncer

- **Sync Definitions**: Sync permissions and roles defined in your configuration files with the database:
  ```bash
  php spark vima:sync
  ```
- **Type-safe Mappers**: Auto-generate PHP constants for permissions and roles to eliminate magic strings:
  ```bash
  php spark vima:generate maps
  ```
- **Policy Generators**: Generate a new Policy boilerplate file:
  ```bash
  php spark vima:policy create [Name] --resource [ResourceName]
  ```

---

## 5. Handling Vima Events in CodeIgniter 4

The CI4 bridge maps all core Vima events synchronously to CodeIgniter 4's native `CodeIgniter\Events\Events` manager.

### Binding Event Listeners
You can register event listeners in your `app/Config/Events.php` file:

```php
use CodeIgniter\Events\Events;

// 1. Listen to access check results
Events::on('vima.access.authorization_checked', static function ($event) {
    $data = $event->getData();
    log_message('info', "User {$data['user']->id} checked permission {$data['permission']} -> " . ($data['result'] ? 'ALLOW' : 'DENY'));
});

// 2. Listen to custom database changes
Events::on('vima.role.created', static function ($event) {
    $role = $event->getData()['role'];
    log_message('notice', "New role registered in database: {$role->name}");
});

// 3. Catch-all hook for any Vima action
Events::on('vima.event', static function ($event) {
    // Fired for every event dispatched by the Vima Core engine
});
```

