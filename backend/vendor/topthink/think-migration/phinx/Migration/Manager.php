<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Migration;

use DateTime;
use InvalidArgumentException;
use Phinx\Config\Config;
use Phinx\Config\ConfigInterface;
use Phinx\Config\NamespaceAwareInterface;
use Phinx\Console\Command\AbstractCommand;
use Phinx\Migration\Manager\Environment;
use Phinx\Seed\AbstractSeed;
use Phinx\Seed\SeedInterface;
use Phinx\Util\Util;
use Psr\Container\ContainerInterface;
use RuntimeException;
use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

class Manager
{
    public const BREAKPOINT_TOGGLE = 1;
    public const BREAKPOINT_SET = 2;
    public const BREAKPOINT_UNSET = 3;

    /**
     * @var \Phinx\Config\ConfigInterface
     */
    protected $config;

    /**
     * @var \think\console\Input
     */
    protected $input;

    /**
     * @var \think\console\Output
     */
    protected $output;

    /**
     * @var \Phinx\Migration\Manager\Environment[]
     */
    protected $environments = [];

    /**
     * @var \Phinx\Migration\MigrationInterface[]|null
     */
    protected $migrations;

    /**
     * @var \Phinx\Seed\SeedInterface[]|null
     */
    protected $seeds;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    private $verbosityLevel = OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_NORMAL;

    /**
     * @param \Phinx\Config\ConfigInterface $config Configuration Object
     * @param \think\console\Input $input Console Input
     * @param \think\console\Output $output Console Output
     */
    public function __construct(ConfigInterface $config, InputInterface $input, OutputInterface $output)
    {
        $this->setConfig($config);
        $this->setInput($input);
        $this->setOutput($output);
    }

    /**
     * Prints the specified environment's migration status.
     *
     * @param string $environment environment to print status of
     * @param string|null $format format to print status in (either text, json, or null)
     * @throws \RuntimeException
     * @return array array indicating if there are any missing or down migrations
     */
    public function printStatus(string $environment, ?string $format = null): array
    {
        $output = $this->getOutput();
        $hasDownMigration = false;
        $hasMissingMigration = false;
        $migrations = $this->getMigrations($environment);
        $migrationCount = 0;
        $missingCount = 0;
        $pendingMigrationCount = 0;
        $finalMigrations = [];
        $verbosity = $output->getVerbosity();
        if ($format === 'json') {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        if (count($migrations)) {
            // rewrite using Symfony Table Helper as we already have this library
            // included and it will fix formatting issues (e.g drawing the lines)
            $output->writeln('', $this->verbosityLevel);

            switch ($this->getConfig()->getVersionOrder()) {
                case Config::VERSION_ORDER_CREATION_TIME:
                    $migrationIdAndStartedHeader = '<info>[Migration ID]</info>  Started            ';
                    break;
                case Config::VERSION_ORDER_EXECUTION_TIME:
                    $migrationIdAndStartedHeader = 'Migration ID    <info>[Started          ]</info>';
                    break;
                default:
                    throw new RuntimeException('Invalid version_order configuration option');
            }

            $output->writeln(" Status  $migrationIdAndStartedHeader  Finished             Migration Name ", $this->verbosityLevel);
            $output->writeln('----------------------------------------------------------------------------------', $this->verbosityLevel);

            $env = $this->getEnvironment($environment);
            $versions = $env->getVersionLog();

            $maxNameLength = $versions ? max(array_map(function ($version) {
                return strlen($version['migration_name']);
            }, $versions)) : 0;

            $missingVersions = array_diff_key($versions, $migrations);
            $missingCount = count($missingVersions);

            $hasMissingMigration = !empty($missingVersions);

            // get the migrations sorted in the same way as the versions
            /** @var \Phinx\Migration\AbstractMigration[] $sortedMigrations */
            $sortedMigrations = [];

            foreach ($versions as $versionCreationTime => $version) {
                if (isset($migrations[$versionCreationTime])) {
                    array_push($sortedMigrations, $migrations[$versionCreationTime]);
                    unset($migrations[$versionCreationTime]);
                }
            }

            if (empty($sortedMigrations) && !empty($missingVersions)) {
                // this means we have no up migrations, so we write all the missing versions already so they show up
                // before any possible down migration
                foreach ($missingVersions as $missingVersionCreationTime => $missingVersion) {
                    $this->printMissingVersion($missingVersion, $maxNameLength);

                    unset($missingVersions[$missingVersionCreationTime]);
                }
            }

            // any migration left in the migrations (ie. not unset when sorting the migrations by the version order) is
            // a migration that is down, so we add them to the end of the sorted migrations list
            if (!empty($migrations)) {
                $sortedMigrations = array_merge($sortedMigrations, $migrations);
            }

            $migrationCount = count($sortedMigrations);
            foreach ($sortedMigrations as $migration) {
                $version = array_key_exists($migration->getVersion(), $versions) ? $versions[$migration->getVersion()] : false;
                if ($version) {
                    // check if there are missing versions before this version
                    foreach ($missingVersions as $missingVersionCreationTime => $missingVersion) {
                        if ($this->getConfig()->isVersionOrderCreationTime()) {
                            if ($missingVersion['version'] > $version['version']) {
                                break;
                            }
                        } else {
                            if ($missingVersion['start_time'] > $version['start_time']) {
                                break;
                            } elseif (
                                $missingVersion['start_time'] == $version['start_time'] &&
                                $missingVersion['version'] > $version['version']
                            ) {
                                break;
                            }
                        }

                        $this->printMissingVersion($missingVersion, $maxNameLength);

                        unset($missingVersions[$missingVersionCreationTime]);
                    }

                    $status = '     <info>up</info> ';
                } else {
                    $pendingMigrationCount++;
                    $hasDownMigration = true;
                    $status = '   <error>down</error> ';
                }
                $maxNameLength = max($maxNameLength, strlen($migration->getName()));

                $output->writeln(
                    sprintf(
                        '%s %14.0f  %19s  %19s  <comment>%s</comment>',
                        $status,
                        $migration->getVersion(),
                        ($version ? $version['start_time'] : ''),
                        ($version ? $version['end_time'] : ''),
                        $migration->getName()
                    ),
                    $this->verbosityLevel
                );

                if ($version && $version['breakpoint']) {
                    $output->writeln('         <error>BREAKPOINT SET</error>', $this->verbosityLevel);
                }

                $finalMigrations[] = ['migration_status' => trim(strip_tags($status)), 'migration_id' => sprintf('%14.0f', $migration->getVersion()), 'migration_name' => $migration->getName()];
                unset($versions[$migration->getVersion()]);
            }

            // and finally add any possibly-remaining missing migrations
            foreach ($missingVersions as $missingVersionCreationTime => $missingVersion) {
                $this->printMissingVersion($missingVersion, $maxNameLength);

                unset($missingVersions[$missingVersionCreationTime]);
            }
        } else {
            // there are no migrations
            $output->writeln('', $this->verbosityLevel);
            $output->writeln('There are no available migrations. Try creating one using the <info>create</info> command.', $this->verbosityLevel);
        }

        // write an empty line
        $output->writeln('', $this->verbosityLevel);

        if ($format !== null) {
            switch ($format) {
                case AbstractCommand::FORMAT_JSON:
                    $output->setVerbosity($verbosity);
                    $output->writeln(json_encode(
                        [
                            'pending_count' => $pendingMigrationCount,
                            'missing_count' => $missingCount,
                            'total_count' => $migrationCount + $missingCount,
                            'migrations' => $finalMigrations,
                        ]
                    ));
                    break;
                default:
                    $output->writeln('<info>Unsupported format: ' . $format . '</info>');
            }
        }

        return [
            'hasMissingMigration' => $hasMissingMigration,
            'hasDownMigration' => $hasDownMigration,
        ];
    }

    /**
     * Print Missing Version
     *
     * @param array $version The missing version to print (in the format returned by Environment.getVersionLog).
     * @param int $maxNameLength The maximum migration name length.
     * @return void
     */
    protected function printMissingVersion(array $version, int $maxNameLength): void
    {
        $this->getOutput()->writeln(sprintf(
            '     <error>up</error>  %14.0f  %19s  %19s  <comment>%s</comment>  <error>** MISSING MIGRATION FILE **</error>',
            $version['version'],
            $version['start_time'],
            $version['end_time'],
            str_pad($version['migration_name'], $maxNameLength, ' ')
        ));

        if ($version && $version['breakpoint']) {
            $this->getOutput()->writeln('         <error>BREAKPOINT SET</error>');
        }
    }

    /**
     * Migrate to the version of the database on a given date.
     *
     * @param string $environment Environment
     * @param \DateTime $dateTime Date to migrate to
     * @param bool $fake flag that if true, we just record running the migration, but not actually do the
     *                               migration
     * @return void
     */
    public function migrateToDateTime(string $environment, DateTime $dateTime, bool $fake = false): void
    {
        $versions = array_keys($this->getMigrations($environment));
        $dateString = $dateTime->format('YmdHis');

        $outstandingMigrations = array_filter($versions, function ($version) use ($dateString) {
            return $version <= $dateString;
        });

        if (count($outstandingMigrations) > 0) {
            $migration = max($outstandingMigrations);
            $this->getOutput()->writeln('Migrating to version ' . $migration, $this->verbosityLevel);
            $this->migrate($environment, $migration, $fake);
        }
    }

    /**
     * Migrate an environment to the specified version.
     *
     * @param string $environment Environment
     * @param int|null $version version to migrate to
     * @param bool $fake flag that if true, we just record running the migration, but not actually do the migration
     * @return void
     */
    public function migrate(string $environment, ?int $version = null, bool $fake = false): void
    {
        $migrations = $this->getMigrations($environment);
        $env = $this->getEnvironment($environment);
        $versions = $env->getVersions();
        $current = $env->getCurrentVersion();

        if (empty($versions) && empty($migrations)) {
            return;
        }

        if ($version === null) {
            $version = max(array_merge($versions, array_keys($migrations)));
        } else {
            if ($version != 0 && !isset($migrations[$version])) {
                $this->output->writeln(sprintf(
                    '<comment>warning</comment> %s is not a valid version',
                    $version
                ));

                return;
            }
        }

        // are we migrating up or down?
        $direction = $version > $current ? MigrationInterface::UP : MigrationInterface::DOWN;

        if ($direction === MigrationInterface::DOWN) {
            // run downs first
            krsort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getVersion() <= $version) {
                    break;
                }

                if (in_array($migration->getVersion(), $versions)) {
                    $this->executeMigration($environment, $migration, MigrationInterface::DOWN, $fake);
                }
            }
        }

        ksort($migrations);
        foreach ($migrations as $migration) {
            if ($migration->getVersion() > $version) {
                break;
            }

            if (!in_array($migration->getVersion(), $versions)) {
                $this->executeMigration($environment, $migration, MigrationInterface::UP, $fake);
            }
        }
    }

    /**
     * Execute a migration against the specified environment.
     *
     * @param string $name Environment Name
     * @param \Phinx\Migration\MigrationInterface $migration Migration
     * @param string $direction Direction
     * @param bool $fake flag that if true, we just record running the migration, but not actually do the migration
     * @return void
     */
    public function executeMigration(string $name, MigrationInterface $migration, string $direction = MigrationInterface::UP, bool $fake = false): void
    {
        $this->getOutput()->writeln('', $this->verbosityLevel);

        // Skip the migration if it should not be executed
        if (!$migration->shouldExecute()) {
            $this->printMigrationStatus($migration, 'skipped');

            return;
        }

        $this->printMigrationStatus($migration, ($direction === MigrationInterface::UP ? 'migrating' : 'reverting'));

        // Execute the migration and log the time elapsed.
        $start = microtime(true);
        $this->getEnvironment($name)->executeMigration($migration, $direction, $fake);
        $end = microtime(true);

        $this->printMigrationStatus(
            $migration,
            ($direction === MigrationInterface::UP ? 'migrated' : 'reverted'),
            sprintf('%.4fs', $end - $start)
        );
    }

    /**
     * Execute a seeder against the specified environment.
     *
     * @param string $name Environment Name
     * @param \Phinx\Seed\SeedInterface $seed Seed
     * @return void
     */
    public function executeSeed(string $name, SeedInterface $seed): void
    {
        $this->getOutput()->writeln('', $this->verbosityLevel);

        // Skip the seed if it should not be executed
        if (!$seed->shouldExecute()) {
            $this->printSeedStatus($seed, 'skipped');

            return;
        }

        $this->printSeedStatus($seed, 'seeding');

        // Execute the seeder and log the time elapsed.
        $start = microtime(true);
        $this->getEnvironment($name)->executeSeed($seed);
        $end = microtime(true);

        $this->printSeedStatus(
            $seed,
            'seeded',
            sprintf('%.4fs', $end - $start)
        );
    }

    /**
     * Print Migration Status
     *
     * @param \Phinx\Migration\MigrationInterface $migration Migration
     * @param string $status Status of the migration
     * @param string|null $duration Duration the migration took the be executed
     * @return void
     */
    protected function printMigrationStatus(MigrationInterface $migration, string $status, ?string $duration = null): void
    {
        $this->printStatusOutput(
            $migration->getVersion() . ' ' . $migration->getName(),
            $status,
            $duration
        );
    }

    /**
     * Print Seed Status
     *
     * @param \Phinx\Seed\SeedInterface $seed Seed
     * @param string $status Status of the seed
     * @param string|null $duration Duration the seed took the be executed
     * @return void
     */
    protected function printSeedStatus(SeedInterface $seed, string $status, ?string $duration = null): void
    {
        $this->printStatusOutput(
            $seed->getName(),
            $status,
            $duration
        );
    }

    /**
     * Print Status in Output
     *
     * @param string $name Name of the migration or seed
     * @param string $status Status of the migration or seed
     * @param string|null $duration Duration the migration or seed took the be executed
     * @return void
     */
    protected function printStatusOutput(string $name, string $status, ?string $duration = null): void
    {
        $this->getOutput()->writeln(
            ' ==' .
            ' <info>' . $name . ':</info>' .
            ' <comment>' . $status . ' ' . $duration . '</comment>',
            $this->verbosityLevel
        );
    }

    /**
     * Rollback an environment to the specified version.
     *
     * @param string $environment Environment
     * @param int|string|null $target Target
     * @param bool $force Force
     * @param bool $targetMustMatchVersion Target must match version
     * @param bool $fake Flag that if true, we just record running the migration, but not actually do the migration
     * @return void
     */
    public function rollback(string $environment, $target = null, bool $force = false, bool $targetMustMatchVersion = true, bool $fake = false): void
    {
        // note that the migrations are indexed by name (aka creation time) in ascending order
        $migrations = $this->getMigrations($environment);

        // note that the version log are also indexed by name with the proper ascending order according to the version order
        $executedVersions = $this->getEnvironment($environment)->getVersionLog();

        // get a list of migrations sorted in the opposite way of the executed versions
        $sortedMigrations = [];

        foreach ($executedVersions as $versionCreationTime => &$executedVersion) {
            // if we have a date (ie. the target must not match a version) and we are sorting by execution time, we
            // convert the version start time so we can compare directly with the target date
            if (!$this->getConfig()->isVersionOrderCreationTime() && !$targetMustMatchVersion) {
                /** @var \DateTime $dateTime */
                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $executedVersion['start_time']);
                $executedVersion['start_time'] = $dateTime->format('YmdHis');
            }

            if (isset($migrations[$versionCreationTime])) {
                array_unshift($sortedMigrations, $migrations[$versionCreationTime]);
            } else {
                // this means the version is missing so we unset it so that we don't consider it when rolling back
                // migrations (or choosing the last up version as target)
                unset($executedVersions[$versionCreationTime]);
            }
        }

        if ($target === 'all' || $target === '0') {
            $target = 0;
        } elseif (!is_numeric($target) && $target !== null) { // try to find a target version based on name
            // search through the migrations using the name
            $migrationNames = array_map(function ($item) {
                return $item['migration_name'];
            }, $executedVersions);
            $found = array_search($target, $migrationNames, true);

            // check on was found
            if ($found !== false) {
                $target = (string)$found;
            } else {
                $this->getOutput()->writeln("<error>No migration found with name ($target)</error>");

                return;
            }
        }

        // Check we have at least 1 migration to revert
        $executedVersionCreationTimes = array_keys($executedVersions);
        if (empty($executedVersionCreationTimes) || $target == end($executedVersionCreationTimes)) {
            $this->getOutput()->writeln('<error>No migrations to rollback</error>');

            return;
        }

        // If no target was supplied, revert the last migration
        if ($target === null) {
            // Get the migration before the last run migration
            $prev = count($executedVersionCreationTimes) - 2;
            $target = $prev >= 0 ? $executedVersionCreationTimes[$prev] : 0;
        }

        // If the target must match a version, check the target version exists
        if ($targetMustMatchVersion && $target !== 0 && !isset($migrations[$target])) {
            $this->getOutput()->writeln("<error>Target version ($target) not found</error>");

            return;
        }

        // Rollback all versions until we find the wanted rollback target
        $rollbacked = false;

        foreach ($sortedMigrations as $migration) {
            if ($targetMustMatchVersion && $migration->getVersion() == $target) {
                break;
            }

            if (in_array($migration->getVersion(), $executedVersionCreationTimes)) {
                $executedVersion = $executedVersions[$migration->getVersion()];

                if (!$targetMustMatchVersion) {
                    if (
                        ($this->getConfig()->isVersionOrderCreationTime() && $executedVersion['version'] <= $target) ||
                        (!$this->getConfig()->isVersionOrderCreationTime() && $executedVersion['start_time'] <= $target)
                    ) {
                        break;
                    }
                }

                if ($executedVersion['breakpoint'] != 0 && !$force) {
                    $this->getOutput()->writeln('<error>Breakpoint reached. Further rollbacks inhibited.</error>');
                    break;
                }
                $this->executeMigration($environment, $migration, MigrationInterface::DOWN, $fake);
                $rollbacked = true;
            }
        }

        if (!$rollbacked) {
            $this->getOutput()->writeln('<error>No migrations to rollback</error>');
        }
    }

    /**
     * Run database seeders against an environment.
     *
     * @param string $environment Environment
     * @param string|null $seed Seeder
     * @throws \InvalidArgumentException
     * @return void
     */
    public function seed(string $environment, ?string $seed = null): void
    {
        $seeds = $this->getSeeds($environment);

        if ($seed === null) {
            // run all seeders
            foreach ($seeds as $seeder) {
                if (array_key_exists($seeder->getName(), $seeds)) {
                    $this->executeSeed($environment, $seeder);
                }
            }
        } else {
            // run only one seeder
            if (array_key_exists($seed, $seeds)) {
                $this->executeSeed($environment, $seeds[$seed]);
            } else {
                throw new InvalidArgumentException(sprintf('The seed class "%s" does not exist', $seed));
            }
        }
    }

    /**
     * Sets the environments.
     *
     * @param \Phinx\Migration\Manager\Environment[] $environments Environments
     * @return $this
     */
    public function setEnvironments(array $environments = [])
    {
        $this->environments = $environments;

        return $this;
    }

    /**
     * Gets the manager class for the given environment.
     *
     * @param string $name Environment Name
     * @throws \InvalidArgumentException
     * @return \Phinx\Migration\Manager\Environment
     */
    public function getEnvironment(string $name): Environment
    {
        if (isset($this->environments[$name])) {
            return $this->environments[$name];
        }

        // check the environment exists
        if (!$this->getConfig()->hasEnvironment($name)) {
            throw new InvalidArgumentException(sprintf(
                'The environment "%s" does not exist',
                $name
            ));
        }

        // create an environment instance and cache it
        $envOptions = $this->getConfig()->getEnvironment($name);
        $envOptions['version_order'] = $this->getConfig()->getVersionOrder();
        $envOptions['data_domain'] = $this->getConfig()->getDataDomain();

        $environment = new Environment($name, $envOptions);
        $this->environments[$name] = $environment;
        $environment->setInput($this->getInput());
        $environment->setOutput($this->getOutput());

        return $environment;
    }

    /**
     * Sets the user defined PSR-11 container
     *
     * @param \Psr\Container\ContainerInterface $container Container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Sets the console input.
     *
     * @param \think\console\Input $input Input
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Gets the console input.
     *
     * @return \think\console\Input
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * Sets the console output.
     *
     * @param \think\console\Output $output Output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Gets the console output.
     *
     * @return \think\console\Output
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Sets the database migrations.
     *
     * @param \Phinx\Migration\AbstractMigration[] $migrations Migrations
     * @return $this
     */
    public function setMigrations(array $migrations)
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Gets an array of the database migrations, indexed by migration name (aka creation time) and sorted in ascending
     * order
     *
     * @param string $environment Environment
     * @throws \InvalidArgumentException
     * @return \Phinx\Migration\MigrationInterface[]
     */
    public function getMigrations(string $environment): array
    {
        if ($this->migrations === null) {
            $phpFiles = $this->getMigrationFiles();

            if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                $this->getOutput()->writeln('Migration file');
                $this->getOutput()->writeln(
                    array_map(
                        function ($phpFile) {
                            return "    <info>{$phpFile}</info>";
                        },
                        $phpFiles
                    )
                );
            }

            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var \Phinx\Migration\AbstractMigration[] $versions */
            $versions = [];

            foreach ($phpFiles as $filePath) {
                if (Util::isValidMigrationFileName(basename($filePath))) {
                    if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                        $this->getOutput()->writeln("Valid migration file <info>{$filePath}</info>.");
                    }

                    $version = Util::getVersionFromFileName(basename($filePath));

                    if (isset($versions[$version])) {
                        throw new InvalidArgumentException(sprintf('Duplicate migration - "%s" has the same version as "%s"', $filePath, $versions[$version]->getVersion()));
                    }

                    $config = $this->getConfig();
                    $namespace = $config instanceof NamespaceAwareInterface ? $config->getMigrationNamespaceByPath(dirname($filePath)) : null;

                    // convert the filename to a class name
                    $class = ($namespace === null ? '' : $namespace . '\\') . Util::mapFileNameToClassName(basename($filePath));

                    if (isset($fileNames[$class])) {
                        throw new InvalidArgumentException(sprintf(
                            'Migration "%s" has the same name as "%s"',
                            basename($filePath),
                            $fileNames[$class]
                        ));
                    }

                    $fileNames[$class] = basename($filePath);

                    if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                        $this->getOutput()->writeln("Loading class <info>$class</info> from <info>$filePath</info>.");
                    }

                    // load the migration file
                    $orig_display_errors_setting = ini_get('display_errors');
                    ini_set('display_errors', 'On');
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    ini_set('display_errors', $orig_display_errors_setting);
                    if (!class_exists($class)) {
                        throw new InvalidArgumentException(sprintf(
                            'Could not find class "%s" in file "%s"',
                            $class,
                            $filePath
                        ));
                    }

                    if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                        $this->getOutput()->writeln("Running <info>$class</info>.");
                    }

                    // instantiate it
                    $migration = new $class($environment, $version, $this->getInput(), $this->getOutput());

                    if (!($migration instanceof AbstractMigration)) {
                        throw new InvalidArgumentException(sprintf(
                            'The class "%s" in file "%s" must extend \Phinx\Migration\AbstractMigration',
                            $class,
                            $filePath
                        ));
                    }

                    $versions[$version] = $migration;
                } else {
                    if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                        $this->getOutput()->writeln("Invalid migration file <error>{$filePath}</error>.");
                    }
                }
            }

            ksort($versions);
            $this->setMigrations($versions);
        }

        return $this->migrations;
    }

    /**
     * Returns a list of migration files found in the provided migration paths.
     *
     * @return string[]
     */
    protected function getMigrationFiles(): array
    {
        return Util::getFiles($this->getConfig()->getMigrationPaths());
    }

    /**
     * Sets the database seeders.
     *
     * @param \Phinx\Seed\SeedInterface[] $seeds Seeders
     * @return $this
     */
    public function setSeeds(array $seeds)
    {
        $this->seeds = $seeds;

        return $this;
    }

    /**
     * Get seed dependencies instances from seed dependency array
     *
     * @param \Phinx\Seed\SeedInterface $seed Seed
     * @return \Phinx\Seed\SeedInterface[]
     */
    protected function getSeedDependenciesInstances(SeedInterface $seed): array
    {
        $dependenciesInstances = [];
        $dependencies = $seed->getDependencies();
        if (!empty($dependencies)) {
            foreach ($dependencies as $dependency) {
                foreach ($this->seeds as $seed) {
                    if (get_class($seed) === $dependency) {
                        $dependenciesInstances[get_class($seed)] = $seed;
                    }
                }
            }
        }

        return $dependenciesInstances;
    }

    /**
     * Order seeds by dependencies
     *
     * @param \Phinx\Seed\SeedInterface[] $seeds Seeds
     * @return \Phinx\Seed\SeedInterface[]
     */
    protected function orderSeedsByDependencies(array $seeds): array
    {
        $orderedSeeds = [];
        foreach ($seeds as $seed) {
            $orderedSeeds[get_class($seed)] = $seed;
            $dependencies = $this->getSeedDependenciesInstances($seed);
            if (!empty($dependencies)) {
                $orderedSeeds = array_merge($this->orderSeedsByDependencies($dependencies), $orderedSeeds);
            }
        }

        return $orderedSeeds;
    }

    /**
     * Gets an array of database seeders.
     *
     * @param string $environment Environment
     * @throws \InvalidArgumentException
     * @return \Phinx\Seed\SeedInterface[]
     */
    public function getSeeds(string $environment): array
    {
        if ($this->seeds === null) {
            $phpFiles = $this->getSeedFiles();

            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var \Phinx\Seed\SeedInterface[] $seeds */
            $seeds = [];

            foreach ($phpFiles as $filePath) {
                if (Util::isValidSeedFileName(basename($filePath))) {
                    $config = $this->getConfig();
                    $namespace = $config instanceof NamespaceAwareInterface ? $config->getSeedNamespaceByPath(dirname($filePath)) : null;

                    // convert the filename to a class name
                    $class = ($namespace === null ? '' : $namespace . '\\') . pathinfo($filePath, PATHINFO_FILENAME);
                    $fileNames[$class] = basename($filePath);

                    // load the seed file
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    if (!class_exists($class)) {
                        throw new InvalidArgumentException(sprintf(
                            'Could not find class "%s" in file "%s"',
                            $class,
                            $filePath
                        ));
                    }

                    // instantiate it
                    /** @var \Phinx\Seed\AbstractSeed $seed */
                    if ($this->container !== null) {
                        $seed = $this->container->get($class);
                    } else {
                        $seed = new $class();
                    }
                    $seed->setEnvironment($environment);
                    $input = $this->getInput();
                    if ($input !== null) {
                        $seed->setInput($input);
                    }
                    $output = $this->getOutput();
                    if ($output !== null) {
                        $seed->setOutput($output);
                    }

                    if (!($seed instanceof AbstractSeed)) {
                        throw new InvalidArgumentException(sprintf(
                            'The class "%s" in file "%s" must extend \Phinx\Seed\AbstractSeed',
                            $class,
                            $filePath
                        ));
                    }

                    $seeds[$class] = $seed;
                }
            }

            ksort($seeds);
            $this->setSeeds($seeds);
        }

        $this->seeds = $this->orderSeedsByDependencies($this->seeds);

        return $this->seeds;
    }

    /**
     * Returns a list of seed files found in the provided seed paths.
     *
     * @return string[]
     */
    protected function getSeedFiles(): array
    {
        return Util::getFiles($this->getConfig()->getSeedPaths());
    }

    /**
     * Sets the config.
     *
     * @param \Phinx\Config\ConfigInterface $config Configuration Object
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets the config.
     *
     * @return \Phinx\Config\ConfigInterface
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * Toggles the breakpoint for a specific version.
     *
     * @param string $environment Environment name
     * @param int|null $version Version
     * @return void
     */
    public function toggleBreakpoint(string $environment, ?int $version): void
    {
        $this->markBreakpoint($environment, $version, self::BREAKPOINT_TOGGLE);
    }

    /**
     * Updates the breakpoint for a specific version.
     *
     * @param string $environment The required environment
     * @param int|null $version The version of the target migration
     * @param int $mark The state of the breakpoint as defined by self::BREAKPOINT_xxxx constants.
     * @return void
     */
    protected function markBreakpoint(string $environment, ?int $version, int $mark): void
    {
        $migrations = $this->getMigrations($environment);
        $env = $this->getEnvironment($environment);
        $versions = $env->getVersionLog();

        if (empty($versions) || empty($migrations)) {
            return;
        }

        if ($version === null) {
            $lastVersion = end($versions);
            $version = $lastVersion['version'];
        }

        if ($version != 0 && (!isset($versions[$version]) || !isset($migrations[$version]))) {
            $this->output->writeln(sprintf(
                '<comment>warning</comment> %s is not a valid version',
                $version
            ));

            return;
        }

        switch ($mark) {
            case self::BREAKPOINT_TOGGLE:
                $env->getAdapter()->toggleBreakpoint($migrations[$version]);
                break;
            case self::BREAKPOINT_SET:
                if ($versions[$version]['breakpoint'] == 0) {
                    $env->getAdapter()->setBreakpoint($migrations[$version]);
                }
                break;
            case self::BREAKPOINT_UNSET:
                if ($versions[$version]['breakpoint'] == 1) {
                    $env->getAdapter()->unsetBreakpoint($migrations[$version]);
                }
                break;
        }

        $versions = $env->getVersionLog();

        $this->getOutput()->writeln(
            ' Breakpoint ' . ($versions[$version]['breakpoint'] ? 'set' : 'cleared') .
            ' for <info>' . $version . '</info>' .
            ' <comment>' . $migrations[$version]->getName() . '</comment>'
        );
    }

    /**
     * Remove all breakpoints
     *
     * @param string $environment The required environment
     * @return void
     */
    public function removeBreakpoints(string $environment): void
    {
        $this->getOutput()->writeln(sprintf(
            ' %d breakpoints cleared.',
            $this->getEnvironment($environment)->getAdapter()->resetAllBreakpoints()
        ));
    }

    /**
     * Set the breakpoint for a specific version.
     *
     * @param string $environment The required environment
     * @param int|null $version The version of the target migration
     * @return void
     */
    public function setBreakpoint(string $environment, ?int $version): void
    {
        $this->markBreakpoint($environment, $version, self::BREAKPOINT_SET);
    }

    /**
     * Unset the breakpoint for a specific version.
     *
     * @param string $environment The required environment
     * @param int|null $version The version of the target migration
     * @return void
     */
    public function unsetBreakpoint(string $environment, ?int $version): void
    {
        $this->markBreakpoint($environment, $version, self::BREAKPOINT_UNSET);
    }

    /**
     * @param int $verbosityLevel Verbosity level for info messages
     * @return $this
     */
    public function setVerbosityLevel(int $verbosityLevel)
    {
        $this->verbosityLevel = $verbosityLevel;

        return $this;
    }
}
