<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Plan\Solver;

use Phinx\Db\Plan\AlterTable;

/**
 * A Plan takes an Intent and transforms it into a sequence of
 * instructions that can be correctly executed by an AdapterInterface.
 *
 * The main focus of Plan is to arrange the actions in the most efficient
 * way possible for the database.
 */
class ActionSplitter
{
    /**
     * The fully qualified class name of the Action class to match for conflicts
     *
     * @var string
     */
    protected $conflictClass;

    /**
     * The fully qualified class name of the Action class to match for conflicts, which
     * is the dual of $conflictClass. For example `AddColumn` and `DropColumn` are duals.
     *
     * @var string
     */
    protected $conflictClassDual;

    /**
     * A callback used to signal the actual presence of a conflict, that will be used to
     * partition the AlterTable into non-conflicting parts.
     *
     * The callback receives as first argument amn instance of $conflictClass and as second
     * argument an instance of $conflictClassDual
     *
     * @var callable
     */
    protected $conflictFilter;

    /**
     * Comstructor
     *
     * @param string $conflictClass The fully qualified class name of the Action class to match for conflicts
     * @param string $conflictClassDual The fully qualified class name of the Action class to match for conflicts,
     * which is the dual of $conflictClass. For example `AddColumn` and `DropColumn` are duals.
     * @param callable $conflictFilter The collection of actions to inspect
     */
    public function __construct(string $conflictClass, string $conflictClassDual, callable $conflictFilter)
    {
        $this->conflictClass = $conflictClass;
        $this->conflictClassDual = $conflictClassDual;
        $this->conflictFilter = $conflictFilter;
    }

    /**
     * Returs a sequence of AlterTable instructions that are non conflicting
     * based on the constructor parameters.
     *
     * @param \Phinx\Db\Plan\AlterTable $alter The collection of actions to inspect
     * @return \Phinx\Db\Plan\AlterTable[] A list of AlterTable that can be executed without
     * this type of conflict
     */
    public function __invoke(AlterTable $alter): array
    {
        $conflictActions = array_filter($alter->getActions(), function ($action) {
            return $action instanceof $this->conflictClass;
        });

        $originalAlter = new AlterTable($alter->getTable());
        $newAlter = new AlterTable($alter->getTable());

        foreach ($alter->getActions() as $action) {
            if (!$action instanceof $this->conflictClassDual) {
                $originalAlter->addAction($action);
                continue;
            }

            $found = false;
            $matches = $this->conflictFilter;
            foreach ($conflictActions as $ca) {
                if ($matches($ca, $action)) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $newAlter->addAction($action);
            } else {
                $originalAlter->addAction($action);
            }
        }

        return [$originalAlter, $newAlter];
    }
}
