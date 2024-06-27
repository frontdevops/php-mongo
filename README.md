# php-mongo
Class Mongo - Represents a MongoDB connection and operations class.

# Usage

## Basic Example: Inserting and Retrieving a Document
```php
$db = GeekJOB\Mongo();
$db('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

$user = $db('users')->findOne(['email' => 'john@example.com']);
print_r($user);
```
Explanation: This example demonstrates the basic usage of the library. We first create a MongoDB connection using GeekJOB\Mongo(). Then, we insert a new document into the 'users' collection using the insert() method. Finally, we retrieve the inserted document using findOne() with a filter for the email address.

## Updating a Document
```php
$db = GeekJOB\Mongo();
$db('users')->updateOne(
    ['email' => 'john@example.com'],
    ['$set' => ['age' => 31]]
);
```
Explanation: This example shows how to update a single document. We use the updateOne() method, providing a filter to match the document and an update operation to set the new age value. The $set operator is used to update specific fields without affecting others.

## Deleting a Document
```php
$db = GeekJOB\Mongo();
$db('users')->deleteOne(['email' => 'john@example.com']);
```
Explanation: Here, we demonstrate how to delete a single document from the 'users' collection. The deleteOne() method is used with a filter to specify which document to remove.

## Performing a Query with Multiple Conditions
```php
$db = GeekJOB\Mongo();
$users = $db('users')->find([
    'age' => ['$gte' => 25, '$lte' => 50],
    'status' => 'active'
]);

foreach ($users as $user) {
    echo $user->name . "\n";
}
```

**or**

```php
$db = GeekJOB\Mongo();
$users = $db('users')->find([
    'age' => ['$gte' => 25, '$lte' => 50],
    'status' => 'active'
], assoc: true);

foreach ($users as $user) {
    echo $user['name'] . "\n";
}
```

Explanation: This example showcases a more complex query using the find() method. We're searching for users aged between 25 and 50 (inclusive) with an 'active' status. The $gte and $lte operators are used for the age range. The results are converted to an array and then iterated over.

## Using Aggregation
```php
$db = GeekJOB\Mongo();
$result = $db('orders')->aggregate([
    ['$group' => [
        '_id' => '$status',
        'totalAmount' => ['$sum' => '$amount']
    ]],
    ['$sort' => ['totalAmount' => -1]]
]);

foreach ($result as $group) {
    echo "Status: " . $group->_id . ", Total: $" . $group->totalAmount . "\n";
}
```
Explanation: This example demonstrates the use of MongoDB's aggregation framework. We're grouping orders by their status and calculating the total amount for each status. The results are then sorted in descending order of the total amount. This showcases how to perform complex data analysis operations using the library.

## Using Transactions
```php
$db = GeekJOB\Mongo();
try {
    $db->transactionStart();
    
    $db('accounts')->updateOne(
        ['userId' => 123],
        ['$inc' => ['balance' => -100]]
    );
    
    $db('transactions')->insert([
        'userId' => 123,
        'amount' => 100,
        'type' => 'withdrawal',
        'date' => $db->date()
    ]);
    
    $db->transactionCommit();
    echo "Transaction completed successfully.\n";
} catch (Exception $e) {
    $db->transactionAbort();
    echo "Transaction failed: " . $e->getMessage() . "\n";
}
```
Explanation: This example illustrates how to use transactions for ensuring data consistency across multiple operations. We start a transaction, perform two operations (updating an account balance and inserting a transaction record), and then commit the transaction. If any error occurs, the transaction is aborted, rolling back any changes. This is crucial for maintaining data integrity in financial operations or other scenarios where multiple related updates must succeed or fail together.
These examples demonstrate the versatility and power of the GeekJOB MongoDB Library, showcasing various operations from basic CRUD to complex aggregations and transactions. The library's consistent interface `($db($table)->...)` makes it intuitive to use across different types of operations.


# Methods
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
 * @param object|string $_id The ID of the document to find
 * @param bool $assoc Whether to return the result as an associative array
 * @return object|null The found document or null if not found
 * @throws \MongoDB\Driver\Exception\Exception
 */
public function findById(object|string $_id, bool $assoc = false)
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

# License
MIT


