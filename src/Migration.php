<?php

/**
 * Part of the Support package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Support
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Support;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use Exception;

abstract class Migration extends IlluminateMigration
{

	/**
	 * Database Manager
	 *
	 * @var
	 */
	protected $manager;

	/**
	 * Database Connection
	 *
	 * @var
	 */
	protected $connection;

	/**
	 * Schema Builder
	 *
	 * @var
	 */
	protected $builder;

	/**
	 * List of all tables related to this migration
	 *
	 * You can add them here and use the dropAll() method in down().
	 *
	 * Why? Because it's easier and safer, because dropAll() will check
	 * if the table exists before trying to delete it.
	 *
	 * @var array
	 */
	protected $tables = array();

	/**
	 * Create a Migrator
	 *
	 * @throws \Exception
	 */
	public function __construct()
	{
		if ($this->isLaravel())
		{
			$this->manager = app()->make('db');

			$this->connection = $this->manager->connection();

			$this->builder = $this->connection->getSchemaBuilder();
		}
		else
		{
			throw new Exception('This migrator must be ran from inside a Laravel application.');
		}
	}

	/**
	 * The Laravel Migrator up() method.
	 *
	 */
	public function up()
	{
		$this->executeInTransaction('migrateUp');
	}

	/**
	 * The Laravel Migrator down() method.
	 *
	 */
	public function down()
	{
		$this->executeInTransaction('migrateDown');
	}

	/**
	 * The abstracted up() method.
	 *
	 * Do not use up(), use this one instead.
	 *
	 */
	abstract protected function migrateUp();

	/**
	 * The abstracted down() method.
	 *
	 * Do not use down(), use this one instead.
	 *
	 */
	abstract protected function migrateDown();

	/**
	 * Execute the migrationm command inside a transaction layer.
	 *
	 * @param $method
	 */
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

	/**
	 * Drop all tables.
	 *
	 */
	protected function dropAllTables()
	{
		foreach($this->tables as $table)
		{
			if ($this->tableExists($table))
			{
				$this->builder->drop($table);
			}
		}
	}

	/**
	 * Check if a table exists.
	 *
	 * @param $table
	 * @return mixed
	 */
	protected function tableExists($table)
	{
		return $this->builder->hasTable($table);
	}

	/**
	 * Handle an exception.
	 *
	 * @param $exception
	 */
	protected function handleException($exception)
	{
		$previous = property_exists($exception, 'previous')
					? $exception->previous
					: null;

		if ($exception instanceof \Illuminate\Database\QueryException)
		{
			throw new $exception($exception->getMessage(), $exception->getBindings(), $previous);
		}
		else
		{
			throw new $exception($exception->getMessage(), $previous);
		}
	}

	/**
	 * Check if this is a Laravel application
	 */
	private function isLaravel()
	{
		return
			function_exists('app') &&
			app() instanceof \Illuminate\Foundation\Application;
	}

}
