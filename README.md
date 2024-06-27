# php-mongo
Class Mongo - Represents a MongoDB connection and operations class.

```php
/**
 * Checks the connection to the MongoDB server.
 * 
 * @return bool True if connection is successful, false otherwise
 */
public function checkConnection()
{
    // ... (implementation)
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
public function findOne(array $filter = [], array $opts = [], bool $assoc = false)
{
    // ... (implementation)
}

/**
 * Finds a document by its ID.
 * 
 * @param string $_id The ID of the document to find
 * @param bool $assoc Whether to return the result as an associative array
 * @return object|null The found document or null if not found
 * @throws \MongoDB\Driver\Exception\Exception
 */
public function findById($_id, bool $assoc = false)
{
    // ... (implementation)
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
public function find(array $filter = [], array $opts = [], $returnCursor = false, bool $assoc = false)
{
    // ... (implementation)
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
    // ... (implementation)
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
    // ... (implementation)
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
    // ... (implementation)
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
    // ... (implementation)
}

/**
 * Updates a document or inserts it if it doesn't exist.
 * 
 * @param mixed ...$args Arguments for updateOne
 * @return array|\MongoDB\Driver\WriteResult|null
 */
public function updateInsert(...$args): null|array|\MongoDB\Driver\WriteResult
{
    // ... (implementation)
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
    // ... (implementation)
}

/**
 * Deletes a single document from the collection.
 * 
 * @param array $filter The query filter
 * @return array|\MongoDB\Driver\WriteResult
 */
public function deleteOne($filter = [])
{
    // ... (implementation)
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
    // ... (implementation)
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
    // ... (implementation)
}

/**
 * Creates a projection array for the given fields.
 * 
 * @param array $arr The fields to project
 * @return array The projection array
 */
public function project(array $arr): array
{
    // ... (implementation)
}

/**
 * Starts a new transaction.
 */
public function transactionStart(): void
{
    // ... (implementation)
}

/**
 * Commits the current transaction.
 */
public function transactionCommit(): void
{
    // ... (implementation)
}

/**
 * Aborts the current transaction.
 */
public function transactionAbort(): void
{
    // ... (implementation)
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
    // ... (implementation)
}

/**
 * Gets the next AID (Auto Increment ID) for the specified collection.
 * 
 * @param string|null $document_name The name of the document/collection
 * @return int|float The next AID
 */
public function aid(?string $document_name): int|float
{
    // ... (implementation)
}

/**
 * Gets the next AID from the last document in the current collection.
 * 
 * @return int The next AID
 * @throws \MongoDB\Driver\Exception\Exception
 */
function getNextAIDfromLast(): int
{
    // ... (implementation)
}

/**
 * Formats a date to UTCDateTime.
 * 
 * @param string|null $datetime The date/time string to format
 * @return \MongoDB\BSON\UTCDateTime
 */
public function date(?string $datetime = null): \MongoDB\BSON\UTCDateTime
{
    // ... (implementation)
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
    // ... (implementation)
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
    // ... (implementation)
}
```

