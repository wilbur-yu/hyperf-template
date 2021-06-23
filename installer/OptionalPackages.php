<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Installer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\BasePackage;
use Composer\Package\Link;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class OptionalPackages
{
    /**
     * @const string Regular expression for matching package name and version
     */
    public const PACKAGE_REGEX = '/^(?P<name>[^:]+\/[^:]+)([:]*)(?P<version>.*)$/';

    /**
     * @var IOInterface
     */
    public $io;

    /**
     * Assets to remove during cleanup.
     *
     * @var string[]
     */
    private $assetsToRemove = [
        '.travis.yml',
    ];

    /**
     * @var array
     */
    private $config;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var array
     */
    private $composerDefinition;

    /**
     * @var JsonFile
     */
    private $composerJson;

    /**
     * @var Link[]
     */
    private $composerRequires;

    /**
     * @var Link[]
     */
    private $composerDevRequires;

    /**
     * @var string[] Dev dependencies to remove after install is complete
     */
    private $devDependencies = [
        'composer/composer',
    ];

    /**
     * @var string path to this file
     */
    private $installerSource;

    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var RootPackageInterface
     */
    private $rootPackage;

    /**
     * @var int[]
     */
    private $stabilityFlags;

    public function __construct(IOInterface $io, Composer $composer, string $projectRoot = null)
    {
        $this->io       = $io;
        $this->composer = $composer;
        // Get composer.json location
        $composerFile = Factory::getComposerFile();
        // Calculate project root from composer.json, if necessary
        $this->projectRoot = $projectRoot ?: realpath(dirname($composerFile));
        $this->projectRoot = rtrim($this->projectRoot, '/\\') . '/';

        // Parse the composer.json
        $this->parseComposerDefinition($composer, $composerFile);
        // Get optional packages configuration
        $this->config = require __DIR__ . '/config.php';
        // Source path for this file
        $this->installerSource = realpath(__DIR__) . '/';
    }

    public function installHyperfScript()
    {
        $ask[]  = "\n  <question>What time zone do you want to setup ?</question>\n";
        $ask[]  = "  [<comment>n</comment>] Default time zone for php.ini\n";
        $ask[]  = "Make your selection or type a time zone name, like Asia/Shanghai (y):\n";
        $answer = $this->io->ask(implode('', $ask), 'y');

        $content = file_get_contents($this->installerSource . '/resources/bin/hyperf.stub');
        if ($answer != 'n') {
            $content = str_replace('%TIME_ZONE%', $answer, $content);
            file_put_contents($this->projectRoot . '/bin/hyperf.php', $content);
        }
    }

    /**
     * Create data and cache directories, if not present.
     *
     * Also sets up appropriate permissions.
     */
    public function setupRuntimeDir(): void
    {
        $this->io->write('<info>Setup data and cache dir</info>');
        $runtimeDir = $this->projectRoot . '/runtime';

        if (! is_dir($runtimeDir)) {
            if (! mkdir($runtimeDir, 0775, true) && ! is_dir($runtimeDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $runtimeDir));
            }
            chmod($runtimeDir, 0775);
        }
    }

    /**
     * Cleanup development dependencies.
     *
     * The dev dependencies should be removed from the stability flags,
     * require-dev and the composer file.
     */
    public function removeDevDependencies(): void
    {
        $this->io->write('<info>Removing installer development dependencies</info>');
        foreach ($this->devDependencies as $devDependency) {
            unset($this->stabilityFlags[$devDependency], $this->composerDevRequires[$devDependency], $this->composerDefinition['require-dev'][$devDependency]);
        }
    }

    /**
     * Prompt for each optional installation package.
     *
     * @codeCoverageIgnore
     */
    public function promptForOptionalPackages(): void
    {
        foreach ($this->config['questions'] as $questionName => $question) {
            $this->promptForOptionalPackage($questionName, $question);
        }
    }

    /**
     * Prompt for a single optional installation package.
     *
     * @param string $questionName Name of question
     * @param array  $question Question details from configuration
     */
    public function promptForOptionalPackage(string $questionName, array $question): void
    {
        $defaultOption = $question['default'] ?? 1;
        if (isset($this->composerDefinition['extra']['optional-packages'][$questionName])) {
            // Skip question, it's already answered
            return;
        }
        // Get answer
        $answer = $this->askQuestion($question, $defaultOption);
        // Process answer
        $this->processAnswer($question, $answer);
        // Save user selected option
        $this->composerDefinition['extra']['optional-packages'][$questionName] = $answer;
        // Update composer definition
        $this->composerJson->write($this->composerDefinition);
    }

    /**
     * Update the root package based on current state.
     */
    public function updateRootPackage(): void
    {
        $this->rootPackage->setRequires($this->composerRequires);
        $this->rootPackage->setDevRequires($this->composerDevRequires);
        $this->rootPackage->setStabilityFlags($this->stabilityFlags);
        $this->rootPackage->setAutoload($this->composerDefinition['autoload']);
        $this->rootPackage->setDevAutoload($this->composerDefinition['autoload-dev']);
        $this->rootPackage->setExtra($this->composerDefinition['extra'] ?? []);
    }

    /**
     * Remove the installer from the composer definition.
     */
    public function removeInstallerFromDefinition(): void
    {
        $this->io->write('<info>Remove installer</info>');
        // Remove installer script autoloading rules
        unset(
            $this->composerDefinition['autoload']['psr-4']['Installer\\'],
            $this->composerDefinition['autoload-dev']['psr-4']['InstallerTest\\'],
            $this->composerDefinition['extra']['branch-alias'],
            $this->composerDefinition['extra']['optional-packages'],
            $this->composerDefinition['scripts']['pre-update-cmd'],
            $this->composerDefinition['scripts']['pre-install-cmd']
        );
    }

    /**
     * Finalize the package.
     *
     * Writes the current JSON state to composer.json, clears the
     * composer.lock file, and cleans up all files specific to the
     * installer.
     *
     * @codeCoverageIgnore
     */
    public function finalizePackage(): void
    {
        // Update composer definition
        $this->composerJson->write($this->composerDefinition);
        $this->clearComposerLockFile();
        $this->cleanUp();
    }

    /**
     * Process the answer of a question.
     *
     * @param bool|int|string $answer
     */
    public function processAnswer(array $question, $answer): bool
    {
        if (isset($question['options'][$answer])) {
            // Add packages to install
            if (isset($question['options'][$answer]['packages'])) {
                foreach ($question['options'][$answer]['packages'] as $packageName) {
                    $packageData = $this->config['packages'][$packageName];
                    $this->addPackage($packageName, $packageData['version'], $packageData['whitelist'] ?? []);
                }
            }
            // Copy files
            if (isset($question['options'][$answer])) {
                $force = ! empty($question['force']);
                foreach ($question['options'][$answer]['resources'] as $resource => $target) {
                    $this->copyResource($resource, $target, $force);
                }
            }

            return true;
        }
        if ($question['custom-package'] === true && preg_match(self::PACKAGE_REGEX, (string) $answer, $match)) {
            $this->addPackage($match['name'], $match['version'], []);
            if (isset($question['custom-package-warning'])) {
                $this->io->write(sprintf('  <warning>%s</warning>', $question['custom-package-warning']));
            }

            return true;
        }

        return false;
    }

    /**
     * Add a package.
     */
    public function addPackage(string $packageName, string $packageVersion, array $whitelist = []): void
    {
        $this->io->write(sprintf(
            '  - Adding package <info>%s</info> (<comment>%s</comment>)',
            $packageName,
            $packageVersion
        ));
        // Get the version constraint
        $versionParser = new VersionParser();
        $constraint    = $versionParser->parseConstraints($packageVersion);
        // Create package link
        $link = new Link('__root__', $packageName, $constraint, 'requires', $packageVersion);
        // Add package to the root package and composer.json requirements
        if (in_array($packageName, $this->config['require-dev'], true)) {
            unset($this->composerDefinition['require'][$packageName], $this->composerRequires[$packageName]);

            $this->composerDefinition['require-dev'][$packageName] = $packageVersion;
            $this->composerDevRequires[$packageName]               = $link;
        } else {
            unset($this->composerDefinition['require-dev'][$packageName], $this->composerDevRequires[$packageName]);

            $this->composerDefinition['require'][$packageName] = $packageVersion;
            $this->composerRequires[$packageName]              = $link;
        }
        // Set package stability if needed
        switch (VersionParser::parseStability($packageVersion)) {
            case 'dev':
                $this->stabilityFlags[$packageName] = BasePackage::STABILITY_DEV;

                break;
            case 'alpha':
                $this->stabilityFlags[$packageName] = BasePackage::STABILITY_ALPHA;

                break;
            case 'beta':
                $this->stabilityFlags[$packageName] = BasePackage::STABILITY_BETA;

                break;
            case 'RC':
                $this->stabilityFlags[$packageName] = BasePackage::STABILITY_RC;

                break;
        }
        // Whitelist packages for the component installer
        foreach ($whitelist as $package) {
            if (! in_array($package, $this->composerDefinition['extra']['zf']['component-whitelist'], true)) {
                $this->composerDefinition['extra']['zf']['component-whitelist'][] = $package;
                $this->io->write(sprintf('  - Whitelist package <info>%s</info>', $package));
            }
        }
    }

    /**
     * Copy a file to its final destination in the skeleton.
     *
     * @param string $resource resource file
     * @param string $target destination
     * @param bool   $force whether or not to copy over an existing file
     */
    public function copyResource(string $resource, string $target, bool $force = false): void
    {
        // Copy file
        if ($force === false && is_file($this->projectRoot . $target)) {
            return;
        }
        $sourceIsDir     = is_dir($this->installerSource . $resource);
        $destinationPath = dirname($this->projectRoot . $target);
        if (! is_dir($this->projectRoot . $target)
            && ! mkdir($concurrentDirectory = $this->projectRoot . $target, 0755, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $destinationPath . $target));
        }
        if (! is_dir($destinationPath) && ! mkdir($destinationPath, 0775, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $destinationPath));
        }

        $this->io->write(sprintf('  - Copying <info>%s</info>', $target));
        if (! $sourceIsDir) {
            copy($this->installerSource . $resource, $this->projectRoot . $target);
        }
    }

    /**
     * Remove lines from string content containing words in array.
     */
    public function removeLinesContainingStrings(array $entries, string $content): string
    {
        $entries = implode('|', array_map(function ($word) {
            return preg_quote($word, '/');
        }, $entries));

        return preg_replace('/^.*(?:' . $entries . ").*$(?:\r?\n)?/m", '', $content);
    }

    /**
     * Clean up/remove installer classes and assets.
     *
     * On completion of install/update, removes the installer classes (including
     * this one) and assets (including configuration and templates).
     *
     * @codeCoverageIgnore
     */
    private function cleanUp(): void
    {
        $this->io->write('<info>Removing Expressive installer classes, configuration, tests and docs</info>');
        foreach ($this->assetsToRemove as $target) {
            $target = $this->projectRoot . $target;
            if (file_exists($target)) {
                unlink($target);
            }
        }
        $this->recursiveRmdir($this->installerSource);
    }

    /**
     * Prepare and ask questions and return the answer.
     *
     * @param int|string $defaultOption
     *
     * @return bool|int|string
     * @codeCoverageIgnore
     */
    private function askQuestion(array $question, $defaultOption)
    {
        // Construct question
        $ask         = [
            sprintf("\n  <question>%s</question>\n", $question['question']),
        ];
        $defaultText = $defaultOption;
        foreach ($question['options'] as $key => $option) {
            $defaultText = ($key === $defaultOption) ? $option['name'] : $defaultText;
            $ask[]       = sprintf("  [<comment>%s</comment>] %s\n", $key, $option['name']);
        }
        if ($question['required'] !== true) {
            $ask[] = "  [<comment>n</comment>] None of the above\n";
        }
        $ask[] = ($question['custom-package'] === true)
            ? sprintf(
                '  Make your selection or type a composer package name and version <comment>(%s)</comment>: ',
                $defaultText
            )
            : sprintf('  Make your selection <comment>(%s)</comment>: ', $defaultText);
        while (true) {
            // Ask for user input
            $answer = $this->io->ask(implode($ask), (string) $defaultOption);
            // Handle none of the options
            if ($answer === 'n' && $question['required'] !== true) {
                return 'n';
            }
            // Handle numeric options
            if (is_numeric($answer) && isset($question['options'][(int) $answer])) {
                return (int) $answer;
            }
            // Handle string options
            if (isset($question['options'][$answer])) {
                return $answer;
            }
            // Search for package
            if ($question['custom-package'] === true && preg_match(self::PACKAGE_REGEX, $answer, $match)) {
                $packageName    = $match['name'];
                $packageVersion = $match['version'];
                if (! $packageVersion) {
                    $this->io->write('<error>No package version specified</error>');

                    continue;
                }
                $this->io->write(sprintf('  - Searching for <info>%s:%s</info>', $packageName, $packageVersion));
                $optionalPackage = $this->composer->getRepositoryManager()->findPackage($packageName, $packageVersion);
                if ($optionalPackage === null) {
                    $this->io->write(sprintf('<error>Package not found %s:%s</error>', $packageName, $packageVersion));

                    continue;
                }

                return sprintf('%s:%s', $packageName, $packageVersion);
            }
            $this->io->write('<error>Invalid answer</error>');
        }

        return false;
    }

    /**
     * Recursively remove a directory.
     *
     * @codeCoverageIgnore
     */
    private function recursiveRmdir(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }
        $rdi = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($rii as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);

                continue;
            }
            unlink($filename);
        }
        rmdir($directory);
    }

    /**
     * Removes composer.lock file from gitignore.
     *
     * @codeCoverageIgnore
     */
    private function clearComposerLockFile(): void
    {
        $this->io->write('<info>Removing composer.lock from .gitignore</info>');
        $ignoreFile = sprintf('%s/.gitignore', $this->projectRoot);
        $content    = $this->removeLinesContainingStrings(['composer.lock'], file_get_contents($ignoreFile));
        file_put_contents($ignoreFile, $content);
    }

    /**
     * Parses the composer file and populates internal data.
     */
    private function parseComposerDefinition(Composer $composer, string $composerFile): void
    {
        $this->composerJson       = new JsonFile($composerFile);
        $this->composerDefinition = $this->composerJson->read();
        // Get root package or root alias package
        $this->rootPackage = $composer->getPackage();
        // Get required packages
        $this->composerRequires    = $this->rootPackage->getRequires();
        $this->composerDevRequires = $this->rootPackage->getDevRequires();
        // Get stability flags
        $this->stabilityFlags = $this->rootPackage->getStabilityFlags();
    }
}
