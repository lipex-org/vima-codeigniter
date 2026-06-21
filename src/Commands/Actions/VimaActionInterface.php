<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands\Actions;

/**
 * Interface VimaActionInterface
 * 
 * Defines the contract for custom Vima actions that are executed via resource proxy commands.
 */
interface VimaActionInterface
{
    /**
     * Executes the action logic.
     * 
     * @param array $params The CLI parameters passed to the action.
     * @return void
     */
    public function execute(array $params): void;

    /**
     * Returns a brief description of the action.
     * 
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns the usage syntax for the action.
     * 
     * @return string
     */
    public function getUsage(): string;
}
