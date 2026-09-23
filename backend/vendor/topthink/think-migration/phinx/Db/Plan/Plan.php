<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Plan;

use ArrayObject;
use Phinx\Db\Action\AddColumn;
use Phinx\Db\Action\AddForeignKey;
use Phinx\Db\Action\AddIndex;
use Phinx\Db\Action\ChangeColumn;
use Phinx\Db\Action\ChangeComment;
use Phinx\Db\Action\ChangePrimaryKey;
use Phinx\Db\Action\CreateTable;
use Phinx\Db\Action\DropForeignKey;
use Phinx\Db\Action\DropIndex;
use Phinx\Db\Action\DropTable;
use Phinx\Db\Action\RemoveColumn;
use Phinx\Db\Action\RenameColumn;
use Phinx\Db\Action\RenameTable;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Plan\Solver\ActionSplitter;
use Phinx\Db\Table\Table;

/**
 * A Plan takes an Intent and transforms int into a sequence of
 * instructions that can be correctly executed by an AdapterInterface.
 *
 * The main focus of Plan is to arrange the actions in the most efficient
 * way possible for the database.
 */
class Plan
{
    /**
     * List of tables to be created
     *
     * @var \Phinx\Db\Plan\NewTable[]
     */
    protected $tableCreates = [];

    /**
     * List of table updates
     *
     * @var \Phinx\Db\Plan\AlterTable[]
     */
    protected $tableUpdates = [];

    /**
     * List of table removals or renames
     *
     * @var \Phinx\Db\Plan\AlterTable[]
     */
    protected $tableMoves = [];

    /**
     * List of index additions or removals
     *
     * @var \Phinx\Db\Plan\AlterTable[]
     */
    protected $indexes = [];

    /**
     * List of constraint additions or removals
     *
     * @var \Phinx\Db\Plan\AlterTable[]
     */
    protected $constraints = [];

    /**
     * List of dropped columns
     *
     * @var \Phinx\Db\Plan\AlterTable[]
     */
    protected $columnRemoves = [];

    /**
     * Constructor
     *
     * @param \Phinx\Db\Plan\Intent $intent All the actions that should be executed
     */
    public function __construct(Intent $intent)
    {
        $this->createPlan($intent->getActions());
    }

    /**
     * Parses the given Intent and creates the separate steps to execute
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to use for the plan
     * @return void
     */
    protected function createPlan(array $actions): void
    {
        $this->gatherCreates($actions);
        $this->gatherUpdates($actions);
        $this->gatherTableMoves($actions);
        $this->gatherIndexes($actions);
        $this->gatherConstraints($actions);
        $this->resolveConflicts();
    }

    /**
     * Returns a nested list of all the steps to execute
     *
     * @return \Phinx\Db\Plan\AlterTable[][]
     */
    protected function updatesSequence(): array
    {
        return [
            $this->tableUpdates,
            $this->constraints,
            $this->indexes,
            $this->columnRemoves,
            $this->tableMoves,
        ];
    }

    /**
     * Returns a nested list of all the steps to execute in inverse order
     *
     * @return \Phinx\Db\Plan\AlterTable[][]
     */
    protected function inverseUpdatesSequence(): array
    {
        return [
            $this->constraints,
            $this->tableMoves,
            $this->indexes,
            $this->columnRemoves,
            $this->tableUpdates,
        ];
    }

    /**
     * Executes this plan using the given AdapterInterface
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $executor The executor object for the plan
     * @return void
     */
    public function execute(AdapterInterface $executor): void
    {
        foreach ($this->tableCreates as $newTable) {
            $executor->createTable($newTable->getTable(), $newTable->getColumns(), $newTable->getIndexes());
        }

        foreach ($this->updatesSequence() as $updates) {
            foreach ($updates as $update) {
                $executor->executeActions($update->getTable(), $update->getActions());
            }
        }
    }

    /**
     * Executes the inverse plan (rollback the actions) with the given AdapterInterface:w
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $executor The executor object for the plan
     * @return void
     */
    public function executeInverse(AdapterInterface $executor): void
    {
        foreach ($this->inverseUpdatesSequence() as $updates) {
            foreach ($updates as $update) {
                $executor->executeActions($update->getTable(), $update->getActions());
            }
        }

        foreach ($this->tableCreates as $newTable) {
            $executor->createTable($newTable->getTable(), $newTable->getColumns(), $newTable->getIndexes());
        }
    }

    /**
     * Deletes certain actions from the plan if they are found to be conflicting or redundant.
     *
     * @return void
     */
    protected function resolveConflicts(): void
    {
        foreach ($this->tableMoves as $alterTable) {
            foreach ($alterTable->getActions() as $action) {
                if ($action instanceof DropTable) {
                    $this->tableUpdates = $this->forgetTable($action->getTable(), $this->tableUpdates);
                    $this->constraints = $this->forgetTable($action->getTable(), $this->constraints);
                    $this->indexes = $this->forgetTable($action->getTable(), $this->indexes);
                    $this->columnRemoves = $this->forgetTable($action->getTable(), $this->columnRemoves);
                }
            }
        }

        // Renaming a column and then changing the renamed column is something people do,
        // but it is a conflicting action. Luckily solving the conflict can be done by moving
        // the ChangeColumn action to another AlterTable.
        $splitter = new ActionSplitter(
            RenameColumn::class,
            ChangeColumn::class,
            function (RenameColumn $a, ChangeColumn $b) {
                return $a->getNewName() === $b->getColumnName();
            }
        );
        $tableUpdates = [];
        foreach ($this->tableUpdates as $update) {
            $tableUpdates = array_merge($tableUpdates, $splitter($update));
        }
        $this->tableUpdates = $tableUpdates;

        // Dropping indexes used by foreign keys is a conflict, but one we can resolve
        // if the foreign key is also scheduled to be dropped. If we can find such a a case,
        // we force the execution of the index drop after the foreign key is dropped.
        // Changing constraint properties sometimes require dropping it and then
        // creating it again with the new stuff. Unfortunately, we have already bundled
        // everything together in as few AlterTable statements as we could, so we need to
        // resolve this conflict manually.
        $splitter = new ActionSplitter(
            DropForeignKey::class,
            AddForeignKey::class,
            function (DropForeignKey $a, AddForeignKey $b) {
                return $a->getForeignKey()->getColumns() === $b->getForeignKey()->getColumns();
            }
        );
        $constraints = [];
        foreach ($this->constraints as $constraint) {
            $constraints = array_merge(
                $constraints,
                $splitter($this->remapContraintAndIndexConflicts($constraint))
            );
        }
        $this->constraints = $constraints;
    }

    /**
     * Deletes all actions related to the given table and keeps the
     * rest
     *
     * @param \Phinx\Db\Table\Table $table The table to find in the list of actions
     * @param \Phinx\Db\Plan\AlterTable[] $actions The actions to transform
     * @return \Phinx\Db\Plan\AlterTable[] The list of actions without actions for the given table
     */
    protected function forgetTable(Table $table, array $actions): array
    {
        $result = [];
        foreach ($actions as $action) {
            if ($action->getTable()->getName() === $table->getName()) {
                continue;
            }
            $result[] = $action;
        }

        return $result;
    }

    /**
     * Finds all DropForeignKey actions in an AlterTable and moves
     * all conflicting DropIndex action in `$this->indexes` into the
     * given AlterTable.
     *
     * @param \Phinx\Db\Plan\AlterTable $alter The collection of actions to inspect
     * @return \Phinx\Db\Plan\AlterTable The updated AlterTable object. This function
     * has the side effect of changing the `$this->indexes` property.
     */
    protected function remapContraintAndIndexConflicts(AlterTable $alter): AlterTable
    {
        $newAlter = new AlterTable($alter->getTable());

        foreach ($alter->getActions() as $action) {
            $newAlter->addAction($action);
            if ($action instanceof DropForeignKey) {
                [$this->indexes, $dropIndexActions] = $this->forgetDropIndex(
                    $action->getTable(),
                    $action->getForeignKey()->getColumns(),
                    $this->indexes
                );
                foreach ($dropIndexActions as $dropIndexAction) {
                    $newAlter->addAction($dropIndexAction);
                }
            }
        }

        return $newAlter;
    }

    /**
     * Deletes any DropIndex actions for the given table and exact columns
     *
     * @param \Phinx\Db\Table\Table $table The table to find in the list of actions
     * @param string[] $columns The column names to match
     * @param \Phinx\Db\Plan\AlterTable[] $actions The actions to transform
     * @return array A tuple containing the list of actions without actions for dropping the index
     * and a list of drop index actions that were removed.
     */
    protected function forgetDropIndex(Table $table, array $columns, array $actions): array
    {
        $dropIndexActions = new ArrayObject();
        $indexes = array_map(function ($alter) use ($table, $columns, $dropIndexActions) {
            if ($alter->getTable()->getName() !== $table->getName()) {
                return $alter;
            }

            $newAlter = new AlterTable($table);
            foreach ($alter->getActions() as $action) {
                if ($action instanceof DropIndex && $action->getIndex()->getColumns() === $columns) {
                    $dropIndexActions->append($action);
                } else {
                    $newAlter->addAction($action);
                }
            }

            return $newAlter;
        }, $actions);

        return [$indexes, $dropIndexActions->getArrayCopy()];
    }

    /**
     * Deletes any RemoveColumn actions for the given table and exact columns
     *
     * @param \Phinx\Db\Table\Table $table The table to find in the list of actions
     * @param string[] $columns The column names to match
     * @param \Phinx\Db\Plan\AlterTable[] $actions The actions to transform
     * @return array A tuple containing the list of actions without actions for removing the column
     * and a list of remove column actions that were removed.
     */
    protected function forgetRemoveColumn(Table $table, array $columns, array $actions): array
    {
        $removeColumnActions = new ArrayObject();
        $indexes = array_map(function ($alter) use ($table, $columns, $removeColumnActions) {
            if ($alter->getTable()->getName() !== $table->getName()) {
                return $alter;
            }

            $newAlter = new AlterTable($table);
            foreach ($alter->getActions() as $action) {
                if ($action instanceof RemoveColumn && in_array($action->getColumn()->getName(), $columns, true)) {
                    $removeColumnActions->append($action);
                } else {
                    $newAlter->addAction($action);
                }
            }

            return $newAlter;
        }, $actions);

        return [$indexes, $removeColumnActions->getArrayCopy()];
    }

    /**
     * Collects all table creation actions from the given intent
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to parse
     * @return void
     */
    protected function gatherCreates(array $actions): void
    {
        foreach ($actions as $action) {
            if ($action instanceof CreateTable) {
                $this->tableCreates[$action->getTable()->getName()] = new NewTable($action->getTable());
            }
        }

        foreach ($actions as $action) {
            if (
                ($action instanceof AddColumn || $action instanceof AddIndex)
                && isset($this->tableCreates[$action->getTable()->getName()])
            ) {
                $table = $action->getTable();

                if ($action instanceof AddColumn) {
                    $this->tableCreates[$table->getName()]->addColumn($action->getColumn());
                }

                if ($action instanceof AddIndex) {
                    $this->tableCreates[$table->getName()]->addIndex($action->getIndex());
                }
            }
        }
    }

    /**
     * Collects all alter table actions from the given intent
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to parse
     * @return void
     */
    protected function gatherUpdates(array $actions): void
    {
        foreach ($actions as $action) {
            if (
                !($action instanceof AddColumn)
                && !($action instanceof ChangeColumn)
                && !($action instanceof RemoveColumn)
                && !($action instanceof RenameColumn)
            ) {
                 continue;
            } elseif (isset($this->tableCreates[$action->getTable()->getName()])) {
                continue;
            }
            $table = $action->getTable();
            $name = $table->getName();

            if ($action instanceof RemoveColumn) {
                if (!isset($this->columnRemoves[$name])) {
                    $this->columnRemoves[$name] = new AlterTable($table);
                }
                $this->columnRemoves[$name]->addAction($action);
            } else {
                if (!isset($this->tableUpdates[$name])) {
                    $this->tableUpdates[$name] = new AlterTable($table);
                }
                $this->tableUpdates[$name]->addAction($action);
            }
        }
    }

    /**
     * Collects all alter table drop and renames from the given intent
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to parse
     * @return void
     */
    protected function gatherTableMoves(array $actions): void
    {
        foreach ($actions as $action) {
            if (
                !($action instanceof DropTable)
                && !($action instanceof RenameTable)
                && !($action instanceof ChangePrimaryKey)
                && !($action instanceof ChangeComment)
            ) {
                continue;
            }
            $table = $action->getTable();
            $name = $table->getName();

            if (!isset($this->tableMoves[$name])) {
                $this->tableMoves[$name] = new AlterTable($table);
            }

            $this->tableMoves[$name]->addAction($action);
        }
    }

    /**
     * Collects all index creation and drops from the given intent
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to parse
     * @return void
     */
    protected function gatherIndexes(array $actions): void
    {
        foreach ($actions as $action) {
            if (!($action instanceof AddIndex) && !($action instanceof DropIndex)) {
                continue;
            } elseif (isset($this->tableCreates[$action->getTable()->getName()])) {
                continue;
            }

            $table = $action->getTable();
            $name = $table->getName();

            if (!isset($this->indexes[$name])) {
                $this->indexes[$name] = new AlterTable($table);
            }

            $this->indexes[$name]->addAction($action);
        }
    }

    /**
     * Collects all foreign key creation and drops from the given intent
     *
     * @param \Phinx\Db\Action\Action[] $actions The actions to parse
     * @return void
     */
    protected function gatherConstraints(array $actions): void
    {
        foreach ($actions as $action) {
            if (!($action instanceof AddForeignKey || $action instanceof DropForeignKey)) {
                continue;
            }
            $table = $action->getTable();
            $name = $table->getName();

            if (!isset($this->constraints[$name])) {
                $this->constraints[$name] = new AlterTable($table);
            }

            $this->constraints[$name]->addAction($action);
        }
    }
}
