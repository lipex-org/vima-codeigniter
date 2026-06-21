<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class ResourceProxyCommand
 * 
 * Base class for resource-specific proxy commands (e.g. vima:role) that delegate to decentralized actions.
 */
abstract class ResourceProxyCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Vima';

    /**
     * The resource identifier (e.g. 'role').
     */
    protected string $resource = '';

    /**
     * Map of actions to their respective Action classes.
     */
    protected array $actions = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $action = array_shift($params);

        if (empty($action) || $action === 'help') {
            $this->showHelp();
            return;
        }

        if (!isset($this->actions[$action])) {
            CLI::error("Unknown action '{$action}' for resource '{$this->resource}'");
            $this->showHelp();
            return;
        }

        $class = $this->actions[$action];

        try {
            /** @var \Vima\CodeIgniter\Commands\Actions\VimaActionInterface $instance */
            $instance = new $class();
            $instance->execute($params);
        } catch (\Throwable $e) {
            CLI::error("Action execution failed: " . $e->getMessage());
            if (ENVIRONMENT === 'development') {
                CLI::write($e->getTraceAsString());
            }
        }
    }

    /**
     * Displays help for the resource.
     */
    public function showHelp()
    {
        CLI::write("Vima Management - " . ucfirst($this->resource), 'yellow');
        CLI::write("Usage:", 'yellow');
        CLI::write("  php spark vima:{$this->resource} [action] [arguments]");
        CLI::write("");
        CLI::write("Available Actions:", 'yellow');

        foreach ($this->actions as $action => $class) {
            /** @var \Vima\CodeIgniter\Commands\Actions\VimaActionInterface $instance */
            $instance = new $class();
            CLI::write(sprintf("  %-15s %s", CLI::color($action, 'green'), $instance->getDescription()));
            CLI::write(sprintf("  %-15s Usage: %s", "", $instance->getUsage()), 'light_gray');
        }
    }
}
