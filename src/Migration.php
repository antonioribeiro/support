<?php

namespace PragmaRX\Support;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;
use Illuminate\Database\Schema\Blueprint;
use Exception;
use Schema;

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
	 * The Laravel Migrator up() method.
	 *
	 */
	public function up()
	{
		$this->checkConnection();

		$this->executeInTransaction('migrateUp');
	}

	/**
	 * The Laravel Migrator down() method.
	 *
	 */
	public function down()
	{
		$this->checkConnection();

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
		foreach ($this->tables as $table)
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
					: $exception;

		if ($exception instanceof \Illuminate\Database\QueryException)
		{
			throw new $exception($exception->getMessage(), $exception->getBindings(), $previous);
		}
		else
		{
            throw new $exception($exception->getMessage(), $exception->getCode(), $previous);
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

	/**
	 * Drop a column from a table.
	 *
	 * @param $tableName
	 * @param $column
	 */
	public function dropColumn($tableName, $column)
	{
		// Check for its existence before dropping

		if (Schema::hasColumn($tableName, $column))
		{
			Schema::table($tableName, function(Blueprint $table) use ($column)
			{
				$table->dropColumn($column);
			});
		}
	}

	protected function checkConnection()
	{
		if ($this->isLaravel())
		{
            if ( ! $this->connection)
            {
                $this->manager = app()->make('db');

                $this->connection = $this->manager->connection();
            }

			$this->builder = $this->connection->getSchemaBuilder();
		}
		else
		{
			throw new Exception('This migrator must be ran from inside a Laravel application.');
		}
	}

	public function drop($table)
	{
		if ($this->tableExists($table))
		{
			$this->builder->drop($table);
		}
	}

}
