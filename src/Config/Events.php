<?php

use CodeIgniter\Events\Events;
use Vima\CodeIgniter\Config\Services;
use Vima\CodeIgniter\Support\VimaRegistrar;
use Vima\Core\Audit\Services\AuditService;
use Vima\Core\Events\Access\AuthorizationChecked;
use Vima\Core\Events\Sync\SyncFinished;
use Vima\Core\Cache\Services\CacheVersionManager;
use function Vima\Core\resolve;

Events::on('pre_system', static function () {
    // initialzes the dependecy container
    VimaRegistrar::init();
});

Events::on('pre_command', static function () {
    // initialzes the dependecy container
    VimaRegistrar::init();
});

Events::on(AuthorizationChecked::NAME, static function ($event) {
    /** @var \Vima\CodeIgniter\Config\Vima $config */
    $config = config('Vima');
    if ($config->isAuditEnabled()) {
        /** @var AuditService $auditService */
        $auditService = resolve(AuditService::class);
        $auditService->handleAuthorizationChecked($event);
    }
});

Events::on('vima.event', static function ($event) {
    $name = method_exists($event, 'getName') ? $event->getName() : get_class($event);

    /** @var CacheVersionManager $versionManager */
    $versionManager = resolve(CacheVersionManager::class);

    $data = [];
    if (method_exists($event, 'getData')) {
        $data = $event->getData();
    } elseif (method_exists($event, 'getParams')) {
        $data = $event->getParams();
    }

    if (str_starts_with($name, 'vima.user.')) {
        $userId = $data['userId'] ?? null;
        if ($userId !== null) {
            $versionManager->bumpUserEpoch($userId);
        }
    } elseif (str_starts_with($name, 'vima.role.')) {
        $roleId = $data['roleId'] ?? ($data['role']->id ?? null);
        if ($roleId !== null) {
            $versionManager->bumpRoleEpoch($roleId);
        } else {
            $versionManager->bumpGlobalEpoch();
        }
    } elseif (str_starts_with($name, 'vima.permission.')) {
        $versionManager->bumpGlobalEpoch();
    } elseif ($name === SyncFinished::class || str_contains($name, 'SyncFinished')) {
        $versionManager->bumpGlobalEpoch();
    }
});