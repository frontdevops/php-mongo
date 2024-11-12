<?php declare(strict_types=1);

namespace
{
	if (!function_exists('captureError')) {
		/**
		 * @param $e
		 * @return void
		 */
		function captureError($e): void
		{
			if (defined('DEBUG') && true === DEBUG) {
				echo $e->getMessage(), "\n";
			}
		}
	}
}



/**
 * GeekJOB namespace.
 * Contains the Mongo class and related functions for MongoDB operations.
 * @package GeekJOB
 * @version 1.0.2
 * @since 1.0.2
 * @link
 * @license MIT
 * @see
 * @uses
 * @deprecated
 * @todo
 * @example
 * @category
 * @subpackage
 * @filesource
 * @author
 * @credits
 */
namespace GeekJOB
{
	if (!function_exists('DTZ')) {
		/**
		 * @return \DateTimeZone
		 */
		function DTZ(): \DateTimeZone
		{
			static $dtz;
			if (empty($dtz)) $dtz = new \DateTimeZone(date_default_timezone_get());
			return $dtz;
		}
	}

	/**
	 * Class Mongo.
	 * Represents a MongoDB connection and operations class.
	 *
	 */
	class Mongo
	{
		/**
		 * @var object|null
		 */
		private static ?Mongo $instance = null;

		/**
		 * @var \MongoDB\Driver\Manager
		 */
		public \MongoDB\Driver\Manager $manager;

		/**
		 * @var \MongoDB\Driver\BulkWrite
		 */
		public \MongoDB\Driver\BulkWrite $bulk;

		/**
		 * @var object|null
		 */
		public ?object $insertedId = null;

		/**
		 * @var \MongoDB\Driver\Command
		 */
		public \MongoDB\Driver\Command $command;

		/**
		 * @var array
		 */
		public \MongoDB\Driver\Query $query;

		/**
		 * @var string
		 */
		public string $collection;

		/**
		 * @var string
		 */
		public static ?string $dbnamespace = CONFIG['storage']['data']['base'];

		/**
		 * @var int
		 */
		public int $count = 0;

		/**
		 * @var \MongoDB\Driver\Session
		 */
		public \MongoDB\Driver\Session $transaction;


		/**
		 * Gets the singleton instance of the Mongo class.
		 *
		 * @param string|null $storage_data_uri The MongoDB connection URI
		 * @return self
		 * @throws \MongoConnectionException
		 */
		public static function getInstance(string $storage_data_uri = null): self
		{
			if (!self::$instance)
				self::$instance = new self($storage_data_uri);
			return self::$instance;
		}

		/**
		 * Private constructor for Mongo class.
		 *
		 * @param string|null $storage_data_uri The MongoDB connection URI
		 * @throws \Exception If database name is empty
		 */
		private function __construct(string $storage_data_uri = null)
		{
			if (empty($storage_data_uri))
				$storage_data_uri = CONFIG['storage']['data']['uri'];

			try {
				$this->manager = new \MongoDB\Driver\Manager($storage_data_uri);
				//$this->manager->executeCommand('test', new \MongoDB\Driver\Command(['ping' => 1]));
			}
			catch (\Exception $e) {
				\captureError($e);
			}

			if (!$this->checkConnection()) {
				http_response_code(503);
			}

			if (empty(self::$dbnamespace))
				throw new \Exception("Empty database name");
			// self::$dbnamespace = CONFIG['storage']['data']['base'];
		}


		/**
		 * Checks the connection to the MongoDB server.
		 *
		 * @return bool True if connection is successful, false otherwise
		 */
		public function checkConnection()
		{
			try {
				$this->manager->executeCommand('admin', new \MongoDB\Driver\Command(['ping' => 1]));
				return true;
			}
			catch (\Exception $e) {
				\captureError($e);
				return false;
			}
		}


		/**
		 * Sets the collection for the current operation.
		 *
		 * @param string $collection The name of the collection
		 * @return self
		 */
		public function __invoke(string $collection): self
		{
			$this->count = 0;
			$this->insertedId = null;
			$this->collection = $collection;
			return $this;
		}


		/**
		 * Sets up a query for the current operation.
		 *
		 * @param array $filter The query filter
		 * @param array $opts Query options
		 * @return self
		 */
		public function query(array $filter = [], array $opts = []): self
		{
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->query = new \MongoDB\Driver\Query($filter, $opts);
			return $this;
		}


		/**
		 * Finds a single document in the collection.
		 *
		 * @param array $filter The query filter
		 * @param array $opts Query options
		 * @param bool $assoc Whether to return the result as an associative array
		 * @return mixed|null The found document or null if not found
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function findOne(array $filter = [], array $opts = [], bool $assoc = false): mixed
		{
			$opts['limit'] = 1;
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->query = new \MongoDB\Driver\Query($filter, $opts);
			$cursor = $this->execQuery($this->collection);
			if ($assoc) $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
			$a = $cursor->toArray();
			return empty($a) ? null : $a[0];
		}


		/**
		 * Finds a document by its ID.
		 *
		 * @param object|string $_id The ID of the document to find
		 * @param bool $assoc Whether to return the result as an associative array
		 * @return object|null The found document or null if not found
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function findById(object|string $_id, bool $assoc = false): ?object
		{
			if (!($_id instanceof \MongoDB\BSON\ObjectId)) {
				try {
					$_id = new \MongoDB\BSON\ObjectId($_id);
				}
				catch (\Exception $e) {
					\captureError($e);
					return null;
				}
			}
			try {
				return $this->findOne(['_id' => $_id], [], $assoc);
			}
			catch (\Exception $e) {
				\captureError($e);
				return null;
			}
		}


		/**
		 * Finds multiple documents in the collection.
		 *
		 * @param array $filter The query filter
		 * @param array $opts Query options
		 * @param bool $returnCursor Whether to return the cursor instead of an array
		 * @param bool $assoc Whether to return the results as associative arrays
		 * @return mixed The found documents or cursor
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function find(array $filter = [], array $opts = [], $returnCursor = false, bool $assoc = false): mixed
		{
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->query = new \MongoDB\Driver\Query($filter, $opts);

			$cursor = $this->execQuery($this->collection);
			if ($assoc) $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

			return $returnCursor ? $cursor : $cursor->toArray();
		}

		/**
		 * @return \MongoDB\Driver\Cursor
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function exec()
		{
			return $this->execQuery($this->collection);
		}


		/**
		 * Executes a query on the specified collection.
		 *
		 * @param string $collection The name of the collection
		 * @return \MongoDB\Driver\Cursor|null
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function execQuery(string $collection)
		{
			$res = new EmptyCursor;
			try {
				$res = $this->manager->executeQuery(
					self::$dbnamespace . '.' . $collection,
					$this->query
				);
			}
			catch (\Exception $e) {
				\captureError($e);
			}
			finally {
				return $res;
			}
		}


		/**
		 * Counts the number of documents matching the filter.
		 *
		 * @param array $filter The query filter
		 * @return int The count of matching documents
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function count(array $filter = []): int
		{
			try {
				if (!empty($filter)) {
					$pipe = [
						['$match' => $filter],
						[
							'$group' => [
								'_id'   => null,
								'count' => ['$sum' => 1],
							],
						],
					];
				}
				else {
					$pipe = [['$count' => 'count']];
				}

				$r = $this->aggregate($pipe);
				if (empty($r)) return 0;
				return $r[0]->count;
			}
			catch (\Exception $e) {
				\captureError($e);
				return 0;
			}
		}


		/**
		 * Inserts a document into the collection.
		 *
		 * @param mixed $data The document to insert
		 * @param array $opts Insert options
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function insert($data, array $opts = [])
		{
			$opts['ordered'] = true;

			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->insertedId = $this
				->newBulkWrite($opts['ordered'])
				->bulk
				->insert($data);
			return $this->executeBulkWrite();
		}


		/**
		 * Updates documents in the collection.
		 *
		 * @param array $filter The query filter
		 * @param array $newObj The update operations
		 * @param array $opts Update options
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function update($filter = [], $newObj = [], $opts = ['multi' => true])
		{
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this
				->newBulkWrite()
				->bulk
				->update($filter, $newObj, $opts);
			return $this->executeBulkWrite();
		}


		/**
		 * Updates a single document in the collection.
		 *
		 * @param array $filter The query filter
		 * @param array $newObj The update operations
		 * @param array $opts Update options
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function updateOne($filter = [], array $newObj = [], array $opts = [])
		{
			$opts['limit'] = 1;
			$opts['multi'] = false;

			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this
				->newBulkWrite()
				->bulk
				->update($filter, $newObj, $opts);
			return $this->executeBulkWrite();
		}


		/**
		 * Updates a document or inserts it if it doesn't exist.
		 *
		 * @param mixed ...$args Arguments for updateOne
		 * @return array|\MongoDB\Driver\WriteResult|null
		 */
		public function updateInsert(...$args): null|array|\MongoDB\Driver\WriteResult
		{
			if (empty($args['opts'])) $args['opts'] = [];
			$args['opts']['upsert'] = true;
			return $this->updateOne(...$args);
		}


		/**
		 * Deletes documents from the collection.
		 *
		 * @param array $filter The query filter
		 * @param array $opts Delete options
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function delete($filter = [], array $opts = ['limit' => 0])
		{
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this
				->newBulkWrite()
				->bulk
				->delete($filter, $opts);
			return $this->executeBulkWrite();
		}


		/**
		 * Deletes a single document from the collection.
		 *
		 * @param array $filter The query filter
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function deleteOne($filter = [])
		{
			$opts = ['limit' => 1];
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this
				->newBulkWrite()
				->bulk
				->delete($filter, $opts);
			return $this->executeBulkWrite();
		}


		/**
		 * Executes a bulk write operation.
		 *
		 * @return array|\MongoDB\Driver\WriteResult
		 */
		public function executeBulkWrite()
		{
			$opts = [];
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			try {
				return $this->manager
					->executeBulkWrite(
						self::$dbnamespace . '.' . $this->collection,
						$this->bulk,
						$opts
					);
			}
			catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
				\captureError($e);
				$result = $e->getWriteResult();
				// Убедиться, что гарантия записи не может быть выполнена
				if ($writeConcernError = $result->getWriteConcernError()) {
					$errs = sprintf("%s (%d): %s\n",
						$writeConcernError->getMessage(),
						$writeConcernError->getCode(),
						var_export($writeConcernError->getInfo(), true)
					);
					error_log("$errs\n\n");
					#if (DEBUG) var_dump(['error' => true, 'message' => $errs]);
					return null;
				}

				// Проверить, не выполнялись ли какие-либо операции записи
				foreach ($result->getWriteErrors() as $writeError) {
					$errs = sprintf("Operation#%d: %s (%d)\n",
						$writeError->getIndex(),
						$writeError->getMessage(),
						$writeError->getCode()
					);
					error_log("$errs\n\n");
					#if (DEBUG) var_dump(['error' => true, 'message' => $errs]);
				}

				$errs = $e->getMessage();
				error_log("$errs\n\n");
				#if (DEBUG) var_dump(['error' => true, 'message' => $errs, 'code' => $e->getCode()]);
				return null;
			} //catch (\MongoDB\Driver\Exception\Exception $e) {
			catch (\Exception $e) {
				\captureError($e);
				return null;
			}
		}


		/**
		 * Creates a new bulk write operation.
		 *
		 * @param bool $ordered Whether the bulk operation should be ordered
		 * @return self
		 */
		public function newBulkWrite($ordered = true): self
		{
			//$writeConcern = new \MongoDB\Driver\WriteConcern();
			$opts = ['ordered' => $ordered];
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->bulk = new \MongoDB\Driver\BulkWrite($opts);
			return $this;
		}


		/**
		 * Creates a new command.
		 *
		 * @param array $command The command to create
		 * @param array $opts Command options
		 * @return self
		 */
		public function newCommand(array $command, array $opts = []): self
		{
//        if (!isset($command['cursor']))
//            $command['cursor'] = ['batchSize' => 4000];
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			$this->command = new \MongoDB\Driver\Command($command, $opts);
			return $this;
		}


		/**
		 * Executes the current command.
		 *
		 * @param array $opts Execution options
		 * @return \MongoDB\Driver\Cursor
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function execCommand(array $opts = []): \MongoDB\Driver\Cursor
		{
			if (!empty($this->transaction))
				$opts['session'] = $this->transaction;

			return $this
				->manager
				->executeCommand(self::$dbnamespace, $this->command, $opts);
		}


		/**
		 * Performs an aggregation operation.
		 *
		 * @param array $pipeline The aggregation pipeline
		 * @param bool $return_cursor Whether to return the cursor instead of an array
		 * @param bool $assoc Whether to return the results as associative arrays
		 * @return mixed The aggregation results
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function aggregate(array $pipeline, bool $return_cursor = false, bool $assoc = false)
		{
			$r = $this
				->newCommand(
					[
						'aggregate' => $this->collection,
						'pipeline'  => $pipeline,
						'cursor'    => (object)[],
					]
				)
				->execCommand();
			if ($assoc) $r->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
			if ($return_cursor) return $r;
			return $r->toArray();
		}


		/**
		 * Performs an aggregation operation and returns the first result.
		 *
		 * @param array $pipeline The aggregation pipeline
		 * @param bool $return_cursor Whether to return the cursor instead of the result
		 * @return mixed|\MongoDB\Driver\Cursor
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function aggregatex(array $pipeline, bool $return_cursor = false)
		{
			$r = $this
				->newCommand(
					[
						'aggregate' => $this->collection,
						'pipeline'  => $pipeline,
						'cursor'    => (object)[],
					]
				)
				->execCommand();

			if ($return_cursor) return $r;
			return $r->toArray()[0];
		}


		/**
		 * Creates a projection array for the given fields.
		 *
		 * @param array $arr The fields to project
		 * @return array The projection array
		 */
		public function project(array $arr): array
		{
			$project = [];
			foreach ($arr as $item) $project[$item] = 1;
			return $project;
		}


		/**
		 * Starts a new transaction.
		 */
		public function transactionStart(): void
		{
			$this->transaction = $this->manager->startSession();
			$this->transaction->startTransaction([]);
		}


		/**
		 * Commits the current transaction.
		 */
		public function transactionCommit(): void
		{
			$this->transaction->commitTransaction();
		}


		/**
		 * Aborts the current transaction.
		 */
		public function transactionAbort(): void
		{
			$this->transaction->abortTransaction();
		}


		/**
		 * Finds a single document and updates it.
		 *
		 * @param array $filter The query filter
		 * @param array $update The update operations
		 * @return null|object The updated document or null
		 */
		public function findOneAndUpdate(array $filter, array $update)
		{
			$res = $this
				->newCommand([
					'findandmodify' => $this->collection,
					'query'         => $filter,
					'update'        => $update,
					//'limit' => 1,
					'upsert'        => true,
					//'cursor' => ['batchSize' => 4000]
				])
				->execCommand()
				->toArray();
			if (!$res || !count($res)) return null;
			return $res[0]->value;
		}


		/**
		 * Gets the next AID (Auto Increment ID) for the specified collection.
		 *
		 * @param string|null $document_name The name of the document/collection
		 * @return int|float The next AID
		 */
		public function aid(?string $document_name): int|float
		{
			$document_name = $document_name ?: $this->collection;
			$aid = $this
				->__invoke('sys_aid_sequences')
				->findOneAndUpdate(
					['_id' => $document_name],
					['$inc' => ['seq' => 1]],
				);
			if (!$aid) return 1;
			return $aid->seq;
		}


		/**
		 * Gets the next AID from the last document in the current collection.
		 *
		 * @return int The next AID
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		function getNextAIDfromLast(): int
		{
			$lastDocument = $this->findOne([], [
				'sort'  => ['aid' => -1],
				'limit' => 1,
			]);

			if (!empty($lastDocument->aid)) {
				return $lastDocument->aid + 1;
			}
			else {
				return 1;
			}
		}


		/**
		 * Formats a date to UTCDateTime.
		 *
		 * @param string|null $datetime The date/time string to format
		 * @return \MongoDB\BSON\UTCDateTime
		 */
		public function date(?string $datetime = null): \MongoDB\BSON\UTCDateTime
		{
			return self::_date_static($datetime);
		}


		/**
		 * Static method to format a date to UTCDateTime.
		 *
		 * @param string|null $datetime The date/time string to format
		 * @return \MongoDB\BSON\UTCDateTime
		 */
		public static function _date_static(?string $datetime = null): \MongoDB\BSON\UTCDateTime
		{
			return new \MongoDB\BSON\UTCDateTime($datetime ? strtotime($datetime) * 1000 : null);
		}


		/**
		 * Magic method to call private methods.
		 *
		 * @param string $name The method name
		 * @param array $arguments The method arguments
		 * @return mixed
		 */
		public function __call(string $name, array $arguments)
		{
			if (method_exists($this, "_$name")) {
				return call_user_func([$this, "_$name"], ...$arguments);
			}
			throw new \BadMethodCallException("Method $name not found");
		}


		/**
		 * Magic method to call private static methods.
		 *
		 * @param string $name The method name
		 * @param array $arguments The method arguments
		 * @return mixed
		 */
		public static function __callStatic(string $name, array $arguments)
		{
			return call_user_func([__CLASS__, "_{$name}_static"], ...$arguments);
		}


		/**
		 * Formats a UTCDateTime to a formatted string.
		 *
		 * @param \MongoDB\BSON\UTCDateTime $dt The UTCDateTime to format
		 * @param string $format The desired format
		 * @return string The formatted date/time string
		 */
		public function toDateTime(\MongoDB\BSON\UTCDateTime $dt, string $format): string
		{
			return self::todtime($dt, $format);
		}


		/**
		 * Static method to format a UTCDateTime to a formatted string.
		 *
		 * @param \MongoDB\BSON\UTCDateTime $dt The UTCDateTime to format
		 * @param string $format The desired format
		 * @return string The formatted date/time string
		 */
		public static function todtime(\MongoDB\BSON\UTCDateTime $dt, string $format): string
		{
			return $dt->toDateTime()->setTimeZone(DTZ())->format($format);
		}

		/**
		 * Lists all collections in the current database.
		 *
		 * @param array $opts Additional options for the listCollections command
		 * @param bool $nameOnly If true, returns only collection names instead of full collection info
		 * @return array Array of collection information or collection names
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function listCollections(array $opts = [], bool $nameOnly = false): array
		{
			try {
				// Create command to list collections
				$command = [
					'listCollections' => 1,
					'cursor'          => new \stdClass(),
				];

				// Add any additional options
				if (!empty($opts)) {
					$command = array_merge($command, $opts);
				}

				// Execute the command
				$cursor = $this
					->newCommand($command)
					->execCommand();

				// Convert cursor to array
				$collections = $cursor->toArray();

				// If nameOnly is true, extract only the collection names
				if ($nameOnly) {
					return array_map(function ($collection) {
						return $collection->name;
					}, $collections);
				}

				return $collections;
			}
			catch (\Exception $e) {
				\captureError($e);
				return [];
			}
		}


		/**
		 * @param bool $assoc
		 * @return array|object|null
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function getMetaData(?string $collection, bool $assoc = false): array|object|null
		{
			$collection = $collection ?: $this->collection;
			$meta = $this
				->__invoke('system.js')
				->findOne(['_id' => $collection], assoc: $assoc);
			return $meta;
		}


		/**
		 * @param string|null $collection
		 * @return string|null
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		function getDescription(?string $collection): ?string
		{
			$collection = $collection ?: $this->collection;
			$meta = $this->getMetaData($collection);
			if (empty($meta)) return null;
			return $meta->value->description ?? null;
		}


		/**
		 * @param string|null $collection
		 * @param string $description
		 * @return void
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function setDescription(?string $collection, string $description): void
		{
			$collection = $collection ?: $this->collection;
			$this->setMetaData($collection, ['description' => $description]);
		}


		/**
		 * @param array|object $set
		 * @return void
		 * @throws \MongoDB\Driver\Exception\Exception
		 */
		public function setMetaData(?string $collection, array|object $set): void
		{
			$collection = $collection ?: $this->collection;
			$meta = $this->getMetaData();
			if (empty($meta)) {
				$meta = [
					'_id' => $collection,
					'created' => $this->date(),
					'updated' => $this->date(),
				];
				$meta = array_merge($meta, $set);
				$this->insert($meta);
			}
			else {
				$meta['updated'] = $this->date();
				$meta = array_merge($meta, $set);
				$this->update(['_id' => $collection], $meta);
			}
		}
	}


	/**
	 * Factory function to get a Mongo instance.
	 *
	 * @param string|null $storage_data_uri The MongoDB connection URI
	 * @return Mongo
	 */
	function Mongo(string $storage_data_uri = null): Mongo
	{
		return Mongo::getInstance($storage_data_uri);
	}


	/**
	 * Class EmptyCursor
	 * Represents an empty cursor for MongoDB operations.
	 */
	class EmptyCursor extends
		\stdClass
	{
		/**
		 * Returns an empty array, simulating an empty cursor result.
		 *
		 * @return array An empty array
		 */
		function toArray(): array
		{
			return [];
		}
	}
}


//EOF//
