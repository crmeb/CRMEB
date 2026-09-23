<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Adapter;

/**
 * Wrapper Interface.
 *
 * @author Woody Gilk <woody.gilk@gmail.com>
 */
interface WrapperInterface
{
    /**
     * Class constructor, must always wrap another adapter.
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter Adapter
     */
    public function __construct(AdapterInterface $adapter);

    /**
     * Sets the database adapter to proxy commands to.
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter Adapter
     * @return \Phinx\Db\Adapter\AdapterInterface
     */
    public function setAdapter(AdapterInterface $adapter): AdapterInterface;

    /**
     * Gets the database adapter.
     *
     * @throws \RuntimeException if the adapter has not been set
     * @return \Phinx\Db\Adapter\AdapterInterface
     */
    public function getAdapter(): AdapterInterface;
}
