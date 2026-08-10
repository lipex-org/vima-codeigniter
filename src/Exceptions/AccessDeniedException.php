<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Exceptions;

use RuntimeException;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use Vima\Core\Exceptions\AccessDeniedExceptionInterface;

/**
 * CodeIgniter 4 specific Access Denied Exception.
 */
class AccessDeniedException extends RuntimeException implements AccessDeniedExceptionInterface, HTTPExceptionInterface
{
    public function __construct(
        public readonly string $permission,
        public readonly mixed $user = null,
        public readonly ?string $userId = null,
        string $message = ""
    ) {
        if ($message === "") {
            $userPart = $userId !== null ? "user [{$userId}]" : "user";
            $message = "Access denied for {$userPart} on permission '{$permission}'";
        }
        parent::__construct($message, 403);
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public static function forPermission(string $permission, mixed $user = null, mixed $userResolver = null): self
    {
        $userId = null;
        if ($user !== null) {
            if ($userResolver !== null && method_exists($userResolver, 'resolveId')) {
                try {
                    $userId = (string) $userResolver->resolveId($user);
                } catch (\Throwable $e) {
                }
            } elseif (method_exists($user, 'vimaGetId')) {
                $userId = (string) $user->vimaGetId();
            } elseif (method_exists($user, 'getId')) {
                $userId = (string) $user->getId();
            } elseif (isset($user->id)) {
                $userId = (string) $user->id;
            }
        }

        return new self($permission, $user, $userId);
    }
}
