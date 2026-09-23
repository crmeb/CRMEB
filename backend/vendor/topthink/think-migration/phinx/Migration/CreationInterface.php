<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Migration;

use think\console\Input as InputInterface;
use think\console\Output as OutputInterface;

/**
 * Migration interface
 *
 * @author Richard Quadling <RQuadling@GMail.com>
 */
interface CreationInterface
{
    /**
     * @param \think\console\Input|null $input Input
     * @param \think\console\Output|null $output Output
     */
    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null);

    /**
     * @param \think\console\Input $input Input
     * @return $this
     */
    public function setInput(InputInterface $input);

    /**
     * @param \think\console\Output $output Output
     * @return $this
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return \think\console\Input
     */
    public function getInput(): InputInterface;

    /**
     * @return \think\console\Output
     */
    public function getOutput(): OutputInterface;

    /**
     * Get the migration template.
     *
     * This will be the content that Phinx will amend to generate the migration file.
     *
     * @return string The content of the template for Phinx to amend.
     */
    public function getMigrationTemplate(): string;

    /**
     * Post Migration Creation.
     *
     * Once the migration file has been created, this method will be called, allowing any additional
     * processing, specific to the template to be performed.
     *
     * @param string $migrationFilename The name of the newly created migration.
     * @param string $className The class name.
     * @param string $baseClassName The name of the base class.
     * @return void
     */
    public function postMigrationCreation(string $migrationFilename, string $className, string $baseClassName): void;
}
