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

class VimaSetup extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Vima';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'vima:setup';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sets up Vima in the current project';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:setup {options}';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $options = [
        'overwrite' => 'Overwrite existing files',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $overwrite = !!CLI::getOption('overwrite');

        $this->publishSetup($overwrite);
        $this->publishConfig($overwrite);
        $this->registerHelper();

        CLI::write('Vima setup completed!', 'green');
    }

    /**
     * Publishes the default Vima config file to the application.
     */
    protected function publishConfig(bool $overwrite = false)
    {
        $source = __DIR__ . '/../Config/Vima.php';
        $dest = APPPATH . 'Config/Vima.php';

        if (file_exists($dest)) {
            if (!$overwrite) {
                CLI::write('File already exists: ' . $dest, 'yellow');
                return;
            }

            unlink($dest);
        }

        $content = file_get_contents($source);
        if ($content === false) {
            CLI::error('Failed to read source config file.');
            return;
        }

        // Adjust namespace and imports
        $search = [
            "namespace Vima\CodeIgniter\Config;",
            "use CodeIgniter\Config\BaseConfig;",
            "class Vima extends BaseConfig",
            "use Vima\CodeIgniter\Libraries\Setup as SetupLibrary;",
        ];
        $replace = [
            "namespace Config;",
            "use Vima\CodeIgniter\Config\Vima as BaseVima;",
            "class Vima extends BaseVima",
            "use App\Libraries\Vima\Setup as SetupLibrary;",
        ];

        $content = str_replace($search, $replace, $content);

        // Remove the resolveSetup method definition from the published file
        // Strip the constructor and the resolveSetup method definition
        $content = preg_replace('/    public function __construct\(.*?\n    \}/s', '', $content);
        $content = preg_replace('/    protected function resolveSetup\(.*?\n    \}/s', '', $content);
        $content = preg_replace('/    private function registerPolicies\(.*?\n    \}/s', '', $content);

        // Remove unused class imports
        $unusedNodes = [
            'Vima\Core\Config\RoleColumns',
            'Vima\Core\Config\PermissionColumns',
            'Vima\Core\Config\RolePermissionColumns',
            'Vima\Core\Config\UserRoleColumns',
            'Vima\Core\Config\UserPermissionColumns',
            'Vima\Core\Config\RoleParentColumns',
        ];

        foreach ($unusedNodes as $node) {
            $content = preg_replace('/use ' . preg_quote($node, '/') . ";\n/", '', $content);
        }

        // Clean up any double blank lines at the end of the class
        $content = preg_replace('/\n\s*\n\}/', "\n}", $content);

        if (file_put_contents($dest, $content)) {
            CLI::write('Published: ' . $dest, 'green');
        } else {
            CLI::error('Failed to publish config file.');
        }
    }

    /**
     * Publishes the default Vima config file to the application.
     */
    protected function publishSetup(bool $overwrite = false)
    {
        $source = __DIR__ . '/../Libraries/Setup.php';
        $dest = APPPATH . 'Libraries/Vima/Setup.php';

        $desDir = dirname($dest);

        if (!is_dir($desDir)) {
            mkdir($desDir, 0777, true);
        }

        if (file_exists($dest)) {
            if (!$overwrite) {
                CLI::write('File already exists: ' . $dest, 'yellow');
                return;
            }

            unlink($dest);
        }

        if (copy($source, $dest)) {
            // Adjust namespace in the copied file
            $content = file_get_contents($dest);

            $search[] = "namespace Vima\CodeIgniter\Libraries;";
            $replace[] = "namespace App\Libraries\Vima;";

            $content = str_replace($search, $replace, $content);

            file_put_contents($dest, $content);
        } else {
            CLI::error('Failed to publish setup file.');
        }
    }

    /**
     * Helps with registering the helper in Autoload.php
     */
    protected function registerHelper()
    {
        $path = APPPATH . 'Config/Autoload.php';
        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        $helperName = 'Vima\CodeIgniter\Helpers\vima';

        // Search for $helpers array (matching public/protected, optional array type hint)
        $pattern = '/((?:public|protected)\s+(?:array\s+)?\$helpers\s*=\s*\[)(.*?)(\];)/s';
        if (preg_match($pattern, $content, $matches)) {
            $prefix = $matches[1];
            $inner = $matches[2];
            $suffix = $matches[3];

            // Clean up any existing double commas or leading commas
            $inner = preg_replace('/,\s*,+/', ',', $inner);
            $inner = preg_replace('/^\s*,\s*/', '', $inner);

            $normalizedHelper = str_replace('\\\\', '\\', $helperName);
            $escapedHelper = str_replace('\\', '\\\\', $helperName);

            $alreadyPresent = str_contains($inner, $normalizedHelper) || str_contains($inner, $escapedHelper);

            if (!$alreadyPresent) {
                $trimmed = rtrim($inner);
                if ($trimmed === '') {
                    $inner = "\n        '{$helperName}',\n    ";
                } else {
                    // Check if last code token ends with comma (excluding trailing comments)
                    $codeOnly = preg_replace('/(?:\/\/|#)[^\r\n]*$/', '', $trimmed);
                    $codeOnly = preg_replace('/\/\*.*?\*\/\s*$/s', '', $codeOnly);
                    $codeOnly = rtrim($codeOnly);

                    if (str_ends_with($codeOnly, ',')) {
                        $inner = $trimmed . "\n        '{$helperName}',\n    ";
                    } else {
                        // If line ends with a comment, place comma before the comment
                        if (preg_match('/^(.*?)(\s*(?:\/\/|#)[^\r\n]*)$/', $trimmed, $m)) {
                            $inner = $m[1] . ",\n        '{$helperName}',\n    " . $m[2];
                        } else {
                            $inner = $trimmed . ",\n        '{$helperName}',\n    ";
                        }
                    }
                }
            }

            // Final safety cleanup of any duplicate commas in helpers block
            $inner = preg_replace('/,\s*,+/', ',', $inner);

            $newContent = str_replace($matches[0], $prefix . $inner . $suffix, $content);
            if ($newContent !== $content) {
                file_put_contents($path, $newContent);
            }
        } else {
            CLI::error('Failed to register helper.');
        }
    }
}
