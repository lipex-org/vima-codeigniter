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

use Vima\Core\Exceptions\AccessDeniedException as CoreAccessDeniedException;

/**
 * CodeIgniter 4 specific Access Denied Exception.
 */
class AccessDeniedException extends CoreAccessDeniedException
{
    // Inherits all logic and implements AccessDeniedExceptionInterface via core exception
}
