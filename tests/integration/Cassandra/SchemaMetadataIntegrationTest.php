<?php

/**
 * Copyright 2015-2017 DataStax, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Create an alias for DSE extension to share core test framework
use \Dse as Cassandra;

/**
 * Schema metadata integration tests.
 */
class SchemaMetadataIntegrationTest extends IntegrationTest {
    /**
     * Schema snapshot associated with the $this->session connection.
     *
     * @var \Cassandra\Schema
     */
    private $schema;

    /**
     * Setup the schema metadata for the schema metadata tests.
     */
    public function setUp() {
        // Process parent setup steps
        parent::setUp();

        // Initialize the session schema metadata snapshot
        $this->schema = $this->session->schema();
    }

    /**
     * @inheritdoc
     */
    protected function default_cluster($all = true) {
        return $this->default_cluster_builder($all)
            ->withSchemaMetadata(true)
            ->build();
    }

    protected static function generateKeyspaceName($prefix) {
        return substr(uniqid($prefix), 0, 48);
    }

    protected function createKeyspace($keyspaceName, $replicationFactor = 1) {
        $statement = new Cassandra\SimpleStatement(
            "CREATE KEYSPACE $keyspaceName " .
            "WITH REPLICATION = { 'class' : 'SimpleStrategy', 'replication_factor' : $replicationFactor }"
        );
        $this->session->execute($statement);
    }

    protected function createKeyspaceWithSchema($keyspaceName, $tableSchemas) {
        $this->createKeyspace($keyspaceName);
        foreach ($tableSchemas as $tableName => $tableSchema) {
            $query = sprintf("CREATE TABLE {$keyspaceName}.{$tableName} (%s, PRIMARY KEY(%s))",
                implode(", ",
                    array_map(function ($key, $value) { return "$key $value"; },
                    array_keys($tableSchema), array_values($tableSchema))),
                implode(", ",
                    array_filter(array_keys($tableSchema),
                    function ($columnName) { return strpos($columnName, "key") === 0; }))
            );
            $this->session->execute(new Cassandra\SimpleStatement($query));
        }
    }

    /**
     * Create the table for the secondary indexes
     */
    protected function createTableForSecondaryIndexes() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table} (key1 text, value1 int, value2 map<text, text>, PRIMARY KEY(key1))"
        );
        $this->session->execute($statement);
    }

    /**
     * Create the simple secondary index using the table
     */
    protected function createSimpleSecondaryIndex() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE INDEX simple ON {$this->keyspace}.{$this->table} (value1)"
        );
        $this->session->execute($statement);
    }

    /**
     * Create the collections secondary index using the table
     */
    protected function createCollectionSecondaryIndex() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE INDEX collection ON {$this->keyspace}.{$this->table} (KEYS(value2))"
        );
        $this->session->execute($statement);
    }

    /**
     * Assert the index instance
     *
     * @param $index Index to assert against
     * @param $kind Kind to assert
     * @param $target Target (and index->option('target')) to assert for index->target()
     * @param $className Boolean or string value to assert for index->className
     * @param $isCustom Value of index->isCustom() to assert
     */
    protected function assertIndex($index, $kind, $target, $className, $isCustom) {
        // Get the Cassandra version
        $version = $this->server_version;
        if ($version instanceof Dse\Version) {
            $version = $version->cassandra_version;
        }

        $this->assertEquals($kind, $index->kind());
        $this->assertEquals($target, $index->target());
        $this->assertEquals($isCustom, $index->isCustom());
        if ($isCustom) {
            $this->assertEquals($className, $index->className());
        } else {
            $this->assertFalse($index->className());
        }

        if ($version->compare("3.0.0") >= 0) {
            $this->assertCount(1, $index->options());
            $this->assertEquals($target, $index->option("target"));
        }
    }

    /**
     * Create the tables for the materialized views
     */
    protected function createTablesForMaterializedViews() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_1 (key1 text, value1 int, PRIMARY KEY(key1))"
        );
        $this->session->execute($statement);
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_2 (key1 text, key2 int, value1 int, PRIMARY KEY(key1, key2))"
        );
        $this->session->execute($statement);
    }

    /**
     * Create the simple materialized view using the first table
     */
    protected function createSimpleMaterializedView() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE MATERIALIZED VIEW {$this->keyspace}.simple AS " .
            "SELECT key1 FROM {$this->keyspace}.{$this->table}_1 WHERE value1 IS NOT NULL " .
            "PRIMARY KEY(value1, key1)"
        );
        $this->session->execute($statement);
    }

    /**
     * Create the primary key materialized view using the second table
     */
    protected function createPrimaryKeyMaterializedView() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE MATERIALIZED VIEW {$this->keyspace}.primary_key AS " .
            "SELECT key1 FROM {$this->keyspace}.{$this->table}_2 WHERE key2 IS NOT NULL AND value1 IS NOT NULL " .
            "PRIMARY KEY((value1, key2), key1)"
        );
        $this->session->execute($statement);
    }

    /**
     * Create the primary key materialized view using the second table
     */
    protected function createClusteringKeyMaterializedView() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE MATERIALIZED VIEW {$this->keyspace}.clustering_key AS " .
            "SELECT key1 FROM {$this->keyspace}.{$this->table}_2 WHERE key2 IS NOT NULL AND value1 IS NOT NULL " .
            "PRIMARY KEY(value1, key2, key1) " .
            "WITH CLUSTERING ORDER BY (key2 DESC)"
        );
        $this->session->execute($statement);
    }

    /**
     * Assert the materialized views are equal
     *
     * @param $materializedViewOne First materialized view
     * @param $materializedViewTwo Second materialized view
     */
    protected function assertMaterializedViewsEqual($materializedViewOne, $materializedViewTwo) {
        $this->assertEquals($materializedViewOne->name(), $materializedViewTwo->name());
        $this->assertEquals($materializedViewOne->options(), $materializedViewTwo->options());
        $this->assertEquals($materializedViewOne->comment(), $materializedViewTwo->comment());
        $this->assertEquals($materializedViewOne->readRepairChance(), $materializedViewTwo->readRepairChance());
        $this->assertEquals($materializedViewOne->localReadRepairChance(), $materializedViewTwo->localReadRepairChance());
        $this->assertEquals($materializedViewOne->gcGraceSeconds(), $materializedViewTwo->gcGraceSeconds());
        $this->assertEquals($materializedViewOne->caching(), $materializedViewTwo->caching());
        $this->assertEquals($materializedViewOne->bloomFilterFPChance(), $materializedViewTwo->bloomFilterFPChance());
        $this->assertEquals($materializedViewOne->memtableFlushPeriodMs(), $materializedViewTwo->memtableFlushPeriodMs());
        $this->assertEquals($materializedViewOne->defaultTTL(), $materializedViewTwo->defaultTTL());
        $this->assertEquals($materializedViewOne->speculativeRetry(), $materializedViewTwo->speculativeRetry());
        $this->assertEquals($materializedViewOne->indexInterval(), $materializedViewTwo->indexInterval());
        $this->assertEquals($materializedViewOne->compactionStrategyClassName(), $materializedViewTwo->compactionStrategyClassName());
        $this->assertEquals($materializedViewOne->compactionStrategyOptions(), $materializedViewTwo->compactionStrategyOptions());
        $this->assertEquals($materializedViewOne->compressionParameters(), $materializedViewTwo->compressionParameters());
        $this->assertEquals($materializedViewOne->populateIOCacheOnFlush(), $materializedViewTwo->populateIOCacheOnFlush());
        $this->assertEquals($materializedViewOne->replicateOnWrite(), $materializedViewTwo->replicateOnWrite());
        $this->assertEquals($materializedViewOne->maxIndexInterval(), $materializedViewTwo->maxIndexInterval());
        $this->assertEquals($materializedViewOne->minIndexInterval(), $materializedViewTwo->minIndexInterval());
        $this->assertEquals($materializedViewOne->columns(), $materializedViewTwo->columns());
        $this->assertEquals($materializedViewOne->partitionKey(), $materializedViewTwo->partitionKey());
        $this->assertEquals($materializedViewOne->primaryKey(), $materializedViewTwo->primaryKey());
        $this->assertEquals($materializedViewOne->clusteringKey(), $materializedViewTwo->clusteringKey());
        $this->assertEquals($materializedViewOne->clusteringOrder(), $materializedViewTwo->clusteringOrder());
        $this->assertEquals($materializedViewOne->baseTable(), $materializedViewTwo->baseTable());
    }

    /**
     * Assert the materialized view (not all elements)
     *
     * @param $materializedView Materialized view to validate
     * @param $name Name to assert
     * @param $tableName Table name to assert
     * @param $columns Columns names to assert
     * @param $partitionKeyColumns Primary key column names to assert
     * @param $clusteringKeyColumns Cluster key column names to assert
     */
    protected function assertMaterializedView($materializedView, $name, $tableName, $columns, $partitionKeyColumns, $clusteringKeyColumns) {
        $this->assertEquals($materializedView->name(), $name);
        $this->assertEquals($materializedView->baseTable()->name(), $tableName);
        $i = 0;
        foreach ($materializedView->columns() as $column) {
            $this->assertEquals($columns[$i++], $column->name());
        }
        $i = 0;
        foreach ($materializedView->partitionKey() as $column) {
            $this->assertEquals($partitionKeyColumns[$i++], $column->name());
        }
        $i = 0;
        foreach ($materializedView->clusteringKey() as $column) {
            $this->assertEquals($clusteringKeyColumns[$i++], $column->name());
        }
        $primaryKeyColumns = array_merge($partitionKeyColumns, $clusteringKeyColumns);
        $i = 0;
        foreach ($materializedView->primaryKey() as $column) {
            $this->assertEquals($primaryKeyColumns[$i++], $column->name());
        }
    }

    /**
     * Wait for the function to exist in the current keyspace schema
     *
     * @param string $name Function name to look for
     * @param int $retries (Optional) Number of retries (default: 100)
     * @return bool True if function was found; false otherwise
     */
    private function waitForFunction($name, $retries = 100) {
        // Determine if the function should be searched for
        if ($retries > 0) {
            // Get the list of functions from the current keyspace
            $functions = $this->session->schema()->keyspace($this->keyspace)->functions();

            // Iterate over each function and determine if expected function exists
            foreach ($functions as $function) {
                if ($name == $function->simpleName()) {
                    return true;
                }
            }

            // Take a nap and attempt to retry the search
            usleep(100000); // 100ms
            $this->waitForFunction($name, --$retries);
        }

        // Unable to find function
        return false;
    }

    /**
     * Create the user defined function
     */
    protected function createUserDefinedFunction() {
        $statement = new Cassandra\SimpleStatement(
          "CREATE OR REPLACE FUNCTION {$this->keyspace}.user_defined_function(rhs int, lhs int) " .
          "RETURNS NULL ON NULL INPUT " .
          "RETURNS int LANGUAGE java AS 'return lhs + rhs;'"
        );
        $this->session->execute($statement);
        $this->waitForFunction("user_defined_function");
    }

    /**
     * Assert the `user_defined_function` function
     */
    protected function assertUserDefinedFunction() {
        // Get the UDF from the current keyspace
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $function = $keyspace->function("user_defined_function",
            Cassandra\Type::int(), Cassandra\Type::int());
        $expectedArguments = array(
            "rhs" => "int",
            "lhs" => "int"
        );

        // Assert the UDF
        $this->assertEquals("user_defined_function", $function->simpleName());
        $arguments = array();
        foreach ($function->arguments() as $name => $argument) {
            $arguments[$name] = "{$argument}";
        }
        $this->assertEquals($expectedArguments, $arguments);
        $this->assertEquals("int", $function->returnType());
        $this->assertEquals("user_defined_function(int,int)", $function->signature());
        $this->assertEquals("java", $function->language());
        $this->assertEquals("return lhs + rhs;", $function->body());
        $this->assertEquals(false, $function->isCalledOnNullInput());
    }

    /**
     * Assert the UDFs are equal
     *
     * @param $name Name of user defined function to look up and compare
     * @param $function User defined function to compare
     */
    protected function assertUserDefinedFunctionsEqual($name, $function) {
        // Get the UDF from the current keyspace
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $expectedFunction = null;
        foreach ($keyspace->functions() as $compareFunction) {
            if ($compareFunction->simpleName() == $name) {
                $expectedFunction = $compareFunction;
                continue;
            }
        }
        if (is_null($expectedFunction)) {
            $this->fail("Unable to Locate Function: ${name}");
        }

        $this->assertEquals($expectedFunction->simpleName(),
            $function->simpleName());
        $this->assertEquals($expectedFunction->arguments(),
            $function->arguments());
        $this->assertEquals($expectedFunction->returnType(),
            $function->returnType());
        $this->assertEquals($expectedFunction->signature(),
            $function->signature());
        $this->assertEquals($expectedFunction->language(),
            $function->language());
        $this->assertEquals($expectedFunction->body(),
            $function->body());
        $this->assertEquals($expectedFunction->isCalledOnNullInput(),
            $function->isCalledOnNullInput());
    }

    /**
     * Wait for the aggregate to exist in the current keyspace schema
     *
     * @param string $name Aggregate name to look for
     * @param int $retries (Optional) Number of retries (default: 100)
     * @return bool True if aggregate was found; false otherwise
     */
    private function waitForAggregate($name, $retries = 100) {
        // Determine if the aggregate should be searched for
        if ($retries > 0) {
            // Get the list of aggregates from the current keyspace
            $aggregates = $this->session->schema()->keyspace($this->keyspace)->aggregates();

            // Iterate over each function and determine if expected function exists
            foreach ($aggregates as $aggregate) {
                if ($name == $aggregate->simpleName()) {
                    return true;
                }
            }

            // Take a nap and attempt to retry the search
            usleep(100000); // 100ms
            $this->waitForFunction($name, --$retries);
        }

        // Unable to find aggregate
        return false;
    }

    /**
     * Create the user defined aggregate and two user defined functions for the
     * associated aggregate
     */
    protected function createUserDefinedAggregate() {
        // Ensure the UDF has been created
        $this->createUserDefinedFunction();

        // Create the UDA
        $statement = new Cassandra\SimpleStatement(
            "CREATE OR REPLACE FUNCTION {$this->keyspace}.uda_udf_final(val int) " .
            "RETURNS NULL ON NULL INPUT " .
            "RETURNS int LANGUAGE java AS 'return val * val;'"
        );
        $this->session->execute($statement);
        $this->waitForFunction("uda_udf_final");
        $statement = new Cassandra\SimpleStatement(
            "CREATE OR REPLACE AGGREGATE {$this->keyspace}.user_defined_aggregate(int) " .
            "SFUNC user_defined_function " .
            "STYPE int " .
            "FINALFUNC uda_udf_final " .
            "INITCOND 0"
        );
        $this->session->execute($statement);
        $this->waitForAggregate("user_defined_aggregate");
    }

    /**
     * Assert the `uda_udf_final` function
     */
    protected function assertAggregateUserDefinedFunction() {
        // Get the UDF from the current keyspace
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $function = $keyspace->function("uda_udf_final", Cassandra\Type::int());
        $expectedArguments = array(
            "val" => "int"
        );

        // Assert the UDF
        $this->assertEquals("uda_udf_final", $function->simpleName());
        $arguments = array();
        foreach ($function->arguments() as $name => $argument) {
            $arguments[$name] = "{$argument}";
        }
        $this->assertEquals($expectedArguments, $arguments);
        $this->assertEquals("int", $function->returnType());
        $this->assertEquals("uda_udf_final(int)", $function->signature());
        $this->assertEquals("java", $function->language());
        $this->assertEquals("return val * val;", $function->body());
        $this->assertEquals(false, $function->isCalledOnNullInput());
    }

    /**
     * Assert the `user_defined_aggregate` aggregate
     */
    protected function assertUserDefinedAggregate() {
        // Get the UDA from the current keyspace
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $function = $keyspace->aggregate("user_defined_aggregate", Cassandra\Type::int());
        $expectedArgumentTypes = array("int");

        // Assert the UDA
        $this->assertEquals("user_defined_aggregate", $function->simpleName());
        $argumentTypes = array();
        foreach ($function->argumentTypes() as $argumentType) {
            $argumentTypes[] = "{$argumentType}";
        }
        $this->assertEquals($expectedArgumentTypes, $argumentTypes);
        $this->assertUserDefinedFunctionsEqual("user_defined_function",
            $function->stateFunction());
        $this->assertUserDefinedFunctionsEqual("uda_udf_final",
            $function->finalFunction());
        $this->assertEquals(0, $function->initialCondition());
        $this->assertEquals("int", $function->stateType());
        $this->assertEquals("int", $function->returnType());
        $this->assertEquals("user_defined_aggregate(int)", $function->signature());

        // Assert the UDFs
        $this->assertUserDefinedFunction();
        $this->assertAggregateUserDefinedFunction();
    }

    /**
     * Schema metadata support is available; basic test.
     *
     * This test will ensure that the PHP driver supports schema metadata.
     */
    public function testBasicSchemaMetadata() {
        // Ensure the test class session connection has schema metadata
        $this->assertGreaterThan(0, count($this->schema));

        // Ensure the test class session contains the test keyspace
        $this->assertArrayHasKey($this->keyspace, $this->schema->keyspaces());
    }

    /**
     * Schema metadata support can be disabled.
     *
     * This test will ensure that the PHP driver supports the ability to enable
     * and disable the schema metadata when creating a session object.
     *
     * @test
     * @ticket PHP-61
     */
    public function testDisableSchemaMetadata() {
        // Create a new session with schema metadata disabled
        $cluster = $this->default_cluster_builder()
            ->withSchemaMetadata(false)
            ->build();
        $session = $cluster->connect();

        // Get the schema from the new session
        $schema = $session->schema();

        // Ensure the new session has no schema metadata
        $this->assertCount(0, $schema->keyspaces());
        $this->assertNotEquals($this->schema->keyspaces(), $schema->keyspaces());
    }

    /**
     * Enumerate over keyspaces in schema metadata.
     *
     * This test ensures that driver correctly enumerates over keyspace
     * metadata.
     *
     * @test
     */
    public function testEnumerateKeyspaces() {
        $keyspaceNames = array(
            self::generateKeyspaceName("enumerate_ks0"),
            self::generateKeyspaceName("enumerate_ks1"),
            self::generateKeyspaceName("enumerate_ks2"),
            "system",
        );

        foreach ($keyspaceNames as $keyspaceName) {
            if (strpos($keyspaceName, "system") === 0) continue;
            $this->createKeyspace($keyspaceName);
        }

        $count = 0;
        foreach ($this->session->schema()->keyspaces() as $keyspace) {
            if (in_array($keyspace->name(), $keyspaceNames)) {
                $count++;
            }
        }

        $this->assertEquals($count, count($keyspaceNames));
    }

    /**
     * Get keyspace from schema metadata using keyspace name.
     *
     * This test ensures that the driver is able to access keyspace metadata by
     * name.
     *
     * @test
     */
    public function testGetKeyspaceByName() {
        $keyspaceNames = array(
            self::generateKeyspaceName("by_name_ks0"),
            self::generateKeyspaceName("by_name_ks1"),
            self::generateKeyspaceName("by_name_ks2"),
            "system",
        );

        foreach ($keyspaceNames as $keyspaceName) {
            if (strpos($keyspaceName, "system") === 0) continue;
            $this->createKeyspace($keyspaceName);
        }

        $count = 0;
        foreach ($keyspaceNames as $keyspaceName) {
            $keyspace = $this->session->schema()->keyspace($keyspaceName);
            if (isset($keyspace)) {
                $count++;
            }
        }

        $this->assertEquals($count, count($keyspaceNames));
    }

    /**
     * Enumerate over tables and columns in schema metadata.
     *
     * This test ensures that driver correctly enumerates over table and column
     * metadata.
     *
     * @todo: Add "varchar" and "text" tests
     *
     * @test
     */
    public function testEnumerateTablesAndColumns() {
        $keyspaceName = self::generateKeyspaceName("enumerate");

        $tableSchemas = array(
            "table_int_int" => array("key" => "int", "value" => "int"),
            "table_int_bigint" => array("key" => "int", "value" => "bigint"),
            "table_decimal_map" => array("key" => "decimal", "value" => "map<bigint, uuid>")
        );

        $this->createKeyspaceWithSchema($keyspaceName, $tableSchemas);

        $keyspace = $this->session->schema()->keyspace($keyspaceName);

        $this->assertEquals(count($tableSchemas), count($keyspace->tables()));
        foreach ($keyspace->tables() as $table) {
            $tableSchema = $tableSchemas[$table->name()];
            $this->assertEquals(count($tableSchema), count($table->columns()));
            foreach ($table->columns() as $column) {
                $columnType = $tableSchema[$column->name()];
                $this->assertEquals($columnType, (string)$column->type());
            }
        }
    }

    /**
     * Get tables and columns from schema metadata using their names.
     *
     * This test ensures that the driver is able to access table and column
     * metadata by name.
     *
     * @todo: Add "varchar" and "text" tests
     *
     * @test
     */
    public function testGetTableAndColumnByName() {
        $keyspaceName = self::generateKeyspaceName("by_name");

        $tableSchemas = array(
            "table_int_int" => array("key" => "int", "value" => "int"),
            "table_int_bigint" => array("key" => "int", "value" => "bigint"),
            "table_decimal_map" => array("key" => "decimal", "value" => "map<bigint, uuid>")
        );

        $this->createKeyspaceWithSchema($keyspaceName, $tableSchemas);

        $keyspace = $this->session->schema()->keyspace($keyspaceName);

        $this->assertEquals(count($tableSchemas), count($keyspace->tables()));

        foreach ($tableSchemas as $tableName => $tableSchema) {
            $table = $keyspace->table($tableName);
            $this->assertEquals(count($tableSchema), count($table->columns()));
            foreach ($tableSchema as $columnName => $columnType) {
                $column = $table->column($columnName);
                $this->assertEquals($columnType, (string)$column->type());
            }
        }
    }

    /**
     * Get index options metadata from a column
     *
     * This test ensures that index options metadata are properly returned from
     * an indexed column.
     *
     * @test
     *
     * @requires Cassandra < 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating index
     */
    public function testGetColumnIndexOptions() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_with_index (key int PRIMARY KEY, value map<text, frozen<map<int, int>>>)"
        );
        $this->session->execute($statement);

        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertNotNull($keyspace);

        $table = $keyspace->table("{$this->table}_with_index");
        $this->assertNotNull($table);

        $indexOptions = $table->column("value")->indexOptions();
        $this->assertNull($indexOptions);

        $statement = new Cassandra\SimpleStatement(
            "CREATE INDEX ON {$this->keyspace}.{$this->table}_with_index (value)"
        );
        $this->session->execute($statement);
        sleep(10);

        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertNotNull($keyspace);

        $table = $keyspace->table("{$this->table}_with_index");
        $this->assertNotNull($table);

        $indexOptions = $table->column("value")->indexOptions();
        $this->assertNotNull($indexOptions);
        $this->assertInstanceOf('Dse\Map', $indexOptions);
    }

    /**
     * Schema metadata data with null fields.
     *
     * This test ensures that table and column metadata with null fields
     * are returned correctly.
     *
     * @test
     */
    public function testSchemaMetadataWithNullFields() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_null_comment (key int PRIMARY KEY, value int)"
        );
        $this->session->execute($statement);

        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $table = $keyspace->table("{$this->table}_null_comment");
        $this->assertFalse($table->comment());

        $column = $table->column("value");
        $this->assertNull($column->indexName());
    }

    /**
     * Schema metadata data with deeply nested collection.
     *
     * This test ensures that the validator parser correctly parses and builds
     * columns with deeply nested collection types.
     *
     * @todo: Add "varchar" and "text" tests
     *
     * @test
     * @ticket PHP-62
     */
    public function testSchemaMetadataWithNestedColumnTypes() {
        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_nested1 (key int PRIMARY KEY, value map<frozen<list<int>>, int>)"
        );
        $this->session->execute($statement);

        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_nested2 (key int PRIMARY KEY, value map<int, frozen<list<int>>>)"
        );
        $this->session->execute($statement);

        $statement = new Cassandra\SimpleStatement(
            "CREATE TABLE {$this->keyspace}.{$this->table}_nested3 (key int PRIMARY KEY, value list<frozen<map<int, frozen<set<int>>>>>)"
        );
        $this->session->execute($statement);

        $keyspace = $this->session->schema()->keyspace($this->keyspace);

        $table1 = $keyspace->table("{$this->table}_nested1");
        $this->assertEquals((string)$table1->column("value")->type(), "map<list<int>, int>");

        $table2 = $keyspace->table("{$this->table}_nested2");
        $this->assertEquals((string)$table2->column("value")->type(), "map<int, list<int>>");

        $table3 = $keyspace->table("{$this->table}_nested3");
        $this->assertEquals((string)$table3->column("value")->type(), "list<map<int, set<int>>>");
    }

    /**
     * Schema metadata to validate no secondary indexes exist
     *
     * This test ensures that secondary indexes are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-80
     *
     * @unstable Sleep added as schema is slow to propagate after creating secondary index
     */
    public function testNoSecondaryIndexes() {
        // Create the table and validate no indexes exist
        $this->createTableForSecondaryIndexes();
        sleep(10);
        $table = $this->session->schema()->keyspace($this->keyspace)->table($this->table);
        $this->assertCount(0, $table->indexes());
        $this->assertFalse($table->index("invalid"));
    }

    /**
     * Schema metadata to validate secondary indexes exist
     *
     * This test ensures that secondary indexes are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-80
     *
     * @unstable Sleep added as schema is slow to propagate after creating secondary index
     */
    public function testSecondaryIndexes() {
        // Create the table and secondary index
        $this->createTableForSecondaryIndexes();
        $this->createSimpleSecondaryIndex();
        sleep(10);

        // Validate the schema metadata (no indexes exist)
        $table = $this->session->schema()->keyspace($this->keyspace)->table($this->table);
        $this->assertCount(1, $table->indexes());
        $index = $table->index("simple");
        $this->assertIndex($index, "composites", "value1", false, false);
    }

    /**
     * Schema metadata to validate collection secondary indexes exist
     *
     * This test ensures that secondary indexes are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-80
     *
     * @requires Cassandra >= 2.1.0
     * @unstable Sleep added as schema is slow to propagate after creating secondary index
     */
    public function testCollectionSecondaryIndexes() {
        // Create the table and secondary index
        $this->createTableForSecondaryIndexes();
        $this->createCollectionSecondaryIndex();
        sleep(10);

        // Validate the schema metadata
        $table = $this->session->schema()->keyspace($this->keyspace)->table($this->table);
        $this->assertCount(1, $table->indexes());
        $index = $table->index("collection");
        $this->assertIndex($index, "composites", "keys(value2)", false, false);
    }

    /**
     * Schema metadata to validate secondary indexes exist using iterator
     *
     * This test ensures that secondary indexes are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-80
     *
     * @requires Cassandra >= 2.1.
     * @unstable Sleep added as schema is slow to propagate after creating secondary index
     */
    public function testIteratorSecondaryIndexes() {
        // Create the table and secondary indexes
        $this->createTableForSecondaryIndexes();
        $this->createSimpleSecondaryIndex();
        $this->createCollectionSecondaryIndex();
        sleep(10);

        // Validate the schema metadata
        $table = $this->session->schema()->keyspace($this->keyspace)->table($this->table);
        $indexes = $table->indexes();
        $this->assertCount(2, $indexes);
        foreach ($indexes as $index) {
            $this->assertTrue($index->name() == "simple" || $index->name() == "collection");
        }
    }

    /**
     * Schema metadata to validate no materialized views exist
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testNoMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();
        sleep(10);

        // Validate the schema metadata (no materialized views exist)
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->materializedViews());
        $this->assertFalse($keyspace->materializedView("invalid"));
        $table = $this->session->schema()->keyspace($this->keyspace)->table("{$this->table}_1");
        $this->assertCount(0, $table->materializedViews());
        $this->assertFalse($table->materializedView("invalid"));
        $table = $this->session->schema()->keyspace($this->keyspace)->table("{$this->table}_2");
        $this->assertCount(0, $table->materializedViews());
        $this->assertFalse($table->materializedView("invalid"));
    }

    /**
     * Schema metadata to validate a simple materialized view exist
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();

        // Create the materialized view
        $this->createSimpleMaterializedView();
        sleep(10);

        // Validate the schema metadata (a single materialized views exist)
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->materializedViews());
        $table = $keyspace->table("{$this->table}_1");
        $this->assertCount(1, $table->materializedViews());
        $materializedView = $keyspace->materializedView("simple");
        $this->assertMaterializedViewsEqual($materializedView, $table->materializedView("simple"));
        $this->assertMaterializedView($materializedView, "simple", "{$this->table}_1",
            array("value1", "key1"), array("value1", "key1"), array("key1"));
    }

    /**
     * Schema metadata to validate a materialized view exist when using a
     * primary key
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testPrimaryKeyMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();

        // Create the materialized view
        $this->createPrimaryKeyMaterializedView();
        sleep(10);

        // Validate the schema metadata (two materialized views should exist now)
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->materializedViews());
        $table = $keyspace->table("{$this->table}_2");
        $this->assertCount(1, $table->materializedViews());
        $materializedView = $keyspace->materializedView("primary_key");
        $this->assertMaterializedViewsEqual($materializedView, $table->materializedView("primary_key"));
        $this->assertMaterializedView($materializedView, "primary_key", "{$this->table}_2",
            array("value1", "key2", "key1"), array("value1", "key2"), array("key1"));
    }

    /**
     * Schema metadata to validate a materialized view exist when using a
     * clustering key
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testClusteringKeyMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();

        // Create the materialized view
        $this->createClusteringKeyMaterializedView();
        sleep(10);

        // Validate the schema metadata (three materialized views should exist now as well as two in table 2)
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->materializedViews());
        $table = $keyspace->table("{$this->table}_2");
        $this->assertCount(1, $table->materializedViews());
        $materializedView = $keyspace->materializedView("clustering_key");
        $this->assertMaterializedViewsEqual($materializedView, $table->materializedView("clustering_key"));
        $this->assertMaterializedView($materializedView, "clustering_key", "{$this->table}_2",
            array("value1", "key2", "key1"), array("value1"), array("key2", "key1"));
    }

    /**
     * Schema metadata to validate materialized views exist using iterator
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testIteratorMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();

        // Create the materialized views
        $this->createSimpleMaterializedView();
        $this->createPrimaryKeyMaterializedView();
        $this->createClusteringKeyMaterializedView();
        sleep(10);

        // Validate the materialized views
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(3, $keyspace->materializedViews());
        $table = $keyspace->table("{$this->table}_1");
        $materializedViews = $table->materializedViews();
        foreach ($materializedViews as $materializedView) {
            if ($materializedView->name() == "simple") {
                $this->assertMaterializedView($materializedView, "simple",
                    "{$this->table}_1", array("value1", "key1"),
                    array("value1"), array("key1"));
            } else {
                $this->fail("Invalid Materialized View Name: {$materializedView->name()}");
            }
        }
        $this->assertCount(1, $materializedViews);
        $table = $keyspace->table("{$this->table}_2");
        $materializedViews = $table->materializedViews();
        $this->assertCount(2, $materializedViews);
        foreach ($materializedViews as $materializedView) {
            if ($materializedView->name() == "primary_key") {
                $this->assertMaterializedView($materializedView, "primary_key",
                    "{$this->table}_2", array("value1", "key2", "key1"),
                    array("value1", "key2"), array("key1"));
            } else if ($materializedView->name() == "clustering_key") {
                $this->assertMaterializedView($materializedView, "clustering_key",
                    "{$this->table}_2", array("value1", "key2", "key1"),
                    array("value1"), array("key2", "key1"));
            } else {
                $this->fail("Invalid Materialized View Name: {$materializedView->name()}");
            }
        }
    }

    /**
     * Schema metadata to validate materialized views are dropped correctly
     *
     * This test ensures that materialized views are properly handled by the
     * driver.
     *
     * @test
     * @ticket PHP-79
     *
     * @requires Cassandra >= 3.0.0
     * @unstable Sleep added as schema is slow to propagate after creating materialized views
     */
    public function testDropMaterializedViews() {
        // Create the tables
        $this->createTablesForMaterializedViews();

        // Create the materialized views
        $this->createSimpleMaterializedView();
        sleep(10);

        // Validate the materialized view exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->materializedViews());
        $this->assertNotEmpty($keyspace->materializedView("simple"));

        // Drop the materialized view and validate it no longer exists
        $statement = new Cassandra\SimpleStatement("DROP MATERIALIZED VIEW {$this->keyspace}.simple");
        $this->session->execute($statement);
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->materializedViews());
        $this->assertEmpty($keyspace->materializedView("simple"));
    }

    /**
     * Schema metadata to validate no UDFs exist
     *
     * This test ensures that UDFs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testNoUserDefinedFunctions() {
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->functions());
        $this->assertFalse($keyspace->function("invalid"));
    }

    /**
     * Schema metadata to validate a UDF exist
     *
     * This test ensures that UDFs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testUserDefinedFunctions() {
        // Create the UDF
        $this->createUserDefinedFunction();

        // Validate the UDF exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->functions());
        $this->assertUserDefinedFunction();
    }

    /**
     * Schema metadata to validate UDFs exist using iterator
     *
     * This test ensures that UDFs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testIteratorUserDefinedFunctions() {
        // Create the UDFs (createUserDefinedAggregate creates both functions)
        $this->createUserDefinedAggregate();

        // Validate the UDFs exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(2, $keyspace->functions());
        foreach ($keyspace->functions() as $function) {
            if ($function->simpleName() == "user_defined_function") {
                $this->assertUserDefinedFunction();
            } else if ($function->simpleName() == "uda_udf_final") {
                $this->assertAggregateUserDefinedFunction();
            } else {
                $this->fail("Invalid Function Name: {$function->simpleName()}");
            }
        }
    }

    /**
     * Schema metadata to validate UDFs are dropped correctly
     *
     * This test ensures that UDFs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testDropUserDefinedFunctions() {
        // Create the UDF
        $this->createUserDefinedFunction();

        // Validate the UDF exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->functions());
        $this->assertUserDefinedFunction();

        // Drop the UDF and validate it no longer exists
        $statement = new Cassandra\SimpleStatement("DROP FUNCTION {$this->keyspace}.user_defined_function");
        $this->session->execute($statement);
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->functions());
        $this->assertEmpty($keyspace->function("user_defined_function"));
    }

    /**
     * Schema metadata to validate no UDAs exist
     *
     * This test ensures that UDAs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testNoUserDefinedAggregates() {
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->aggregates());
        $this->assertFalse($keyspace->aggregate("invalid"));
    }

    /**
     * Schema metadata to validate a UDA exist
     *
     * This test ensures that UDAs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testUserDefinedAggregates() {
        // Create the UDA
        $this->createUserDefinedAggregate();

        // Validate the UDA exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->aggregates());
        $this->assertCount(2, $keyspace->functions());
        $this->assertUserDefinedAggregate();
    }

    /**
     * Schema metadata to validate UDAs exist using iterator
     *
     * This test ensures that UDAs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testIteratorUserDefinedAggregates() {
        // Create the UDAs (the same UDA with a different name)
        $this->createUserDefinedAggregate();
        $statement = new Cassandra\SimpleStatement(
            "CREATE OR REPLACE AGGREGATE {$this->keyspace}.user_defined_aggregate_repeat(int) " .
            "SFUNC user_defined_function " .
            "STYPE int " .
            "FINALFUNC uda_udf_final " .
            "INITCOND 0"
        );
        $this->session->execute($statement);
        $this->waitForAggregate("user_defined_aggregate_repeat");

        // Validate the UDAs exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(2, $keyspace->aggregates());
        $count = 0;
        foreach ($keyspace->aggregates() as $aggregate) {
            if ($aggregate->simpleName() == "user_defined_aggregate") {
                $this->assertUserDefinedFunction();
                $count++;
            } else if ($aggregate->simpleName() == "user_defined_aggregate_repeat") {
                $count++;
            } else {
                $this->fail("Invalid Aggregate Name: {$aggregate->simpleName()}");
            }
        }
        $this->assertEquals(2, $count);
    }

    /**
     * Schema metadata to validate UDAs are dropped correctly
     *
     * This test ensures that UDAs are properly handled by the driver.
     *
     * @test
     * @ticket PHP-66
     *
     * @requires Cassandra >= 2.2.0
     */
    public function testDropUserDefinedAggregates() {
        // Create the UDA
        $this->createUserDefinedAggregate();

        // Validate the UDA exists
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(1, $keyspace->aggregates());
        $this->assertCount(2, $keyspace->functions());
        $this->assertUserDefinedAggregate();

        // Drop the UDA and validate it no longer exists
        $statement = new Cassandra\SimpleStatement("DROP AGGREGATE {$this->keyspace}.user_defined_aggregate");
        $this->session->execute($statement);
        $keyspace = $this->session->schema()->keyspace($this->keyspace);
        $this->assertCount(0, $keyspace->aggregates());
        $this->assertEmpty($keyspace->function("user_defined_aggregate"));
        $this->assertCount(2, $keyspace->functions());
    }

    /**
     * Schema metadata versioning
     *
     * This test ensures that schema metadata has a version identifier to
     * quickly determine if one schema is different than another.
     *
     * @test
     *
     * @unstable Sleep added as schema is slow to propagate
     */
    public function testVersion() {
        // Ensure the version information is available
        $version = $this->session->schema()->version();
        $this->assertGreaterThan(0, $version);

        // Ensure the version is incremented by forcing a keyspace created event
        $this->createKeyspace("{$this->keyspace}_new");
        sleep(10);
        $this->assertEquals($version + 1, $this->session->schema()->version());

        // Ensure the version is not incremented (no changes occurred)
        $this->assertEquals($version + 1, $this->session->schema()->version());
    }
}
