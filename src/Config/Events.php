<?php

use CodeIgniter\Events\Events;
use Vima\CodeIgniter\Config\Services;
use Vima\CodeIgniter\Support\VimaRegistrar;
use Vima\Core\Audit\Services\AuditService;
use Vima\Core\Events\Access\AuthorizationChecked;
use Vima\Core\Events\Sync\SyncFinished;
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

    if (str_starts_with($name, 'vima.user.')) {
        $cache = Services::vima_cache(true);
        $config = Services::vima_config(true);

        $data = [];
        if (method_exists($event, 'getData')) {
            $data = $event->getData();
        } elseif (method_exists($event, 'getParams')) {
            $data = $event->getParams();
        }

        $userId = $data['userId'] ?? null;
        if ($userId !== null) {
            $prefix = rtrim($config->cachePrefix, '_:') ?: 'vima';
            $cache->delete($prefix . '_user_' . $userId . '_roles');
            $cache->delete($prefix . '_user_' . $userId . '_permissions');
        }
    } elseif ($name === SyncFinished::class || str_contains($name, 'SyncFinished')) {
        $cache = Services::vima_cache(true);
        $cache->clear();
    }
});