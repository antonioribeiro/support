<?php

/**
 * Part of the Tracker package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Tracker
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Support;

use Illuminate\Database\DatabaseManager;

abstract class Migrator
{
	protected $manager;

	protected $connection;

	protected $schemaBuilder;

	protected $tables = array();

	public function __construct(DatabaseManager $manager, $connection)
	{
		$this->manager = $manager;

		$this->connection = $this->manager->connection($connection);

		$this->schemaBuilder = $this->connection->getSchemaBuilder();
	}

	public function up()
	{
		$this->executeInTransaction('migrateUp');
	}

	public function down()
	{
		$this->executeInTransaction('migrateDown');
	}

	abstract protected function migrateUp();

	abstract protected function migrateDown();

	protected function executeInTransaction($method)
	{
		$this->connection->beginTransaction();

		try 
		{
			$this->{$method}();
		} 
		catch (\Exception $exception)
		{
			$this->connection->rollback();

			$this->handleException($exception);
		}

		$this->connection->commit();
	}

	protected function dropAllTables()
	{
		foreach($this->tables as $table)
		{
			if ($this->tableExists($table))
			{
				$this->schemaBuilder->drop($table);
			}
		}
	}

	protected function tableExists($table)
	{
		return $this->schemaBuilder->hasTable($table);
	}

	/**
	 * @param $exception
	 */
	protected function handleException($exception)
	{
		if ($exception instanceof \Illuminate\Database\QueryException)
		{
			throw new $exception($exception->getMessage(), $exception->getBindings(), $exception->previous);
		}
		else
		{
			throw new $exception($exception->getMessage(), $exception->previous);
		}
	}

}
