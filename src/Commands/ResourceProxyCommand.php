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
use Vima\CodeIgniter\Commands\Actions\BaseAction;

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

    protected array $params = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->params = $params;
        $action = array_shift($params);

        $showHelp = false;
        foreach ($params as $key => $val) {
            if ($key === 'help' && $val === null) {
                $showHelp = true;
            }
        }

        if (empty($action) || $action === 'help' || $showHelp) {
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
        helper('filesystem');
        $params = $this->params ?? CLI::getSegments();
        $action = $params[0] ?? null;

        if (is_file($action)) {
            $action = $params[1] ?? null;
        }

        if ($action && $action !== 'help') {
            /**
             * @var BaseAction
             */
            $class = $this->actions[$action] ?? null;
            $instance = $class ? new $class() : null;

            if ($instance) {
                $this->showActionHelp($action, $instance);
                return;
            } else {
                CLI::error("Unknown action '{$action}' for resource '{$this->resource}'");
                CLI::write("");

                $this->showAvailableActions();
                return;
            }
        }

        CLI::write("Vima Management - " . ucfirst($this->resource), 'yellow');
        CLI::write("Usage:", 'yellow');
        CLI::write("  php spark vima:{$this->resource} [action] [arguments]");
        CLI::write("");

        $this->showAvailableActions();
    }

    protected function showAvailableActions(): void
    {
        CLI::write("Available Actions:", 'yellow');

        foreach ($this->actions as $action => $class) {
            /** @var \Vima\CodeIgniter\Commands\Actions\VimaActionInterface $instance */
            $instance = new $class();
            CLI::write(sprintf("  %-15s %s", CLI::color($action, 'green'), $instance->getDescription()));
            CLI::write(sprintf("  %-15s Usage: %s", "", $instance->getUsage()), 'light_gray');
        }
    }

    /**
     * Displays help for a specific action.
     *
     * @param string $action
     * @param \Vima\CodeIgniter\Commands\Actions\VimaActionInterface $instance
     */
    protected function showActionHelp(string $action, \Vima\CodeIgniter\Commands\Actions\VimaActionInterface $instance)
    {
        CLI::write("Vima Action Help - " . ucfirst($this->resource) . " " . ucfirst($action), 'yellow');
        CLI::write("");
        CLI::write("Description:", 'yellow');
        CLI::write("  " . $instance->getDescription());
        CLI::write("");
        CLI::write("Usage:", 'yellow');
        CLI::write("  php spark " . $instance->getUsage());
        CLI::write("");

        $options = $instance->getOptions();
        if (!empty($options)) {
            CLI::write("Options:", 'yellow');
            foreach ($options as $opt => $desc) {
                CLI::write(sprintf("  %-20s %s", CLI::color($opt, 'green'), $desc));
            }
            CLI::write("");
        }
    }
}
