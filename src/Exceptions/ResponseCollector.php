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

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Collector to gather custom responses during access denied events.
 */
class ResponseCollector
{
    private ?ResponseInterface $response = null;

    /**
     * Provide a custom response to override the default access denied behavior.
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Retrieve the collected custom response, if any.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
