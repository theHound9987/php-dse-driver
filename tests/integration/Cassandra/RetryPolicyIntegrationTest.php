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
 * Retry policy integration tests.
 *
 * @requires Cassandra >= 2.0.0
 */
class RetryPolicyIntegrationTest extends IntegrationTest {
    /**
     * The number of inserts and asserts to perform during testing.
     */
    const NUMBER_OF_INSERTS = 25;
    /**
     * Maximum number of TimeoutExceptions to bound the insert/assert functions
     * recursive calls/
     */
    const NUMBER_OF_TIMEOUT_EXCEPTIONS = 5;
    /**
     * Batch statement type
     */
    const BATCH_STATEMENT = 1;
    /**
     * Prepared statement type
     */
    const PREPARED_STATEMENT = 2;
    /**
     * Simple statement type
     */
    const SIMPLE_STATEMENT = 3;

    /**
     * Insert query generated for a retry policy test.
     *
     * @var string
     */
    private $insertQuery;

    /**
     * Setup the retry policy for multiple nodes.
     */
    public function setUp() {
        // Ensure RF = 3 (anything greater than 1 is good)
        $this->replication_factor = 3;

        // Process parent setup steps
        parent::setUp();

        // Create the table
        $query = "CREATE TABLE {$this->keyspace}.{$this->table} (key int, value_int int, PRIMARY KEY(key, value_int))";
        $this->session->execute($query);

        // Create the insert query
        $this->insertQuery = "INSERT INTO {$this->keyspace}.{$this->table} (key, value_int) VALUES (?, ?)";
    }

    /**
     * Insert n values into the table for a given key.
     *
     * @param $statementType Type of statement to create for inserts
     * @param Cassandra\RetryPolicy $policy RetryPolicy to use when executing statements
     * @param $key Key value
     * @param $numberOfInserts Number of inserts to perform
     * @param $consistency Consistency level to execute statement
     * @param int $retries Number of TimeoutException retries
     *                     (DEFAULT: self::NUMBER_OF_TIMEOUT_EXCEPTIONS)
     * @throws Cassandra\Exception\TimeoutException If the number of retries is <= 0
     */
    private function insert($statementType, Cassandra\RetryPolicy $policy, $key, $numberOfInserts, $consistency, $retries = self::NUMBER_OF_TIMEOUT_EXCEPTIONS) {
        try {
            // Create all statement types
            $batch = new Cassandra\BatchStatement(Cassandra::BATCH_UNLOGGED);
            $prepare = $this->session->prepare($this->insertQuery);

            // Create the default execution options
            $options = array(
                "consistency" => $consistency,
                "retry_policy" => $policy
            );

            // Create the inserts
            foreach (range(1, $numberOfInserts) as $i) {
                $values = array(
                    $key,
                    $i
                );
                if ($statementType == self::BATCH_STATEMENT) {
                    if ($i % 2 == 0) {
                        $batch->add($prepare, $values);
                    } else {
                        $batch->add($this->insertQuery, $values);
                    }
                } else {
                    // Execute either the prepare or simple statment
                    $statement = $prepare;
                    if ($statementType == self::SIMPLE_STATEMENT) {
                        $statement = $this->insertQuery;
                    }
                    $options["arguments"] = $values;
                    $this->session->execute($statement, $options);
                }
            }

            // Execute the batched insert
            if ($statementType == self::BATCH_STATEMENT) {
                $this->session->execute($batch, $options);
            }
        } catch (Cassandra\Exception\TimeoutException $te) {
            if (self::$configuration->verbose) {
                fprintf(STDOUT, "Insert TimeoutException: %s (%s:%d)" . PHP_EOL,
                    $te->getMessage(), $te->getFile(), $te->getLine());
            }
            if ($retries > 0) {
                $this->insert($policy, $key, $numberOfInserts, $consistency, ($retries - 1));
            } else {
                throw $te;
            }
        }
    }

    /**
     * Assert n values in the table for a given key.
     *
     * @param Cassandra\RetryPolicy $policy RetryPolicy to use when executing statements
     * @param $key Key value
     * @param $numberOfAsserts Number of inserts to perform
     * @param $consistency Consistency level to execute statement
     * @param int $retries Number of TimeoutException retries
     *                     (DEFAULT: self::NUMBER_OF_TIMEOUT_EXCEPTIONS
     * @throws Cassandra\Exception\TimeoutException If the number of retries is <= 0
     */
    private function assert(Cassandra\RetryPolicy $policy, $key, $numberOfAsserts, $consistency, $retries = self::NUMBER_OF_TIMEOUT_EXCEPTIONS) {
        try {
            // Select the values
            $options = array(
                "consistency" => $consistency,
                "retry_policy" => $policy
            );
            $rows = $this->session->execute(
                "SELECT value_int FROM {$this->keyspace}.{$this->table} WHERE key = {$key}",
                $options
            );

            // Assert the values
            $this->assertCount($numberOfAsserts, $rows);
            foreach ($rows as $i => $row) {
                $this->assertEquals(($i + 1), $row["value_int"]);
            }
        } catch (Cassandra\Exception\TimeoutException $te) {
            if (self::$configuration->verbose) {
                fprintf(STDOUT, "Assert TimeoutException: %s (%s:%d)" . PHP_EOL,
                    $te->getMessage(), $te->getFile(), $te->getLine());
            }
            if ($retries > 0) {
                $this->assert($policy, $key, $numberOfAsserts, $consistency, ($retries - 1));
            } else {
                throw $te;
            }
        }
    }

    /**
     * Statement execution supports downgrading consistency retry policy.
     *
     * This test will ensure that the PHP driver supports the downgrading
     * retry policy when executing statements.
     *
     * @test
     * @ticket PHP-60
     */
    public function testDowngradingPolicy() {
        // Create the retry policy (RF = 3 with 1 node)
        $policy = new Cassandra\RetryPolicy\DowngradingConsistency();

        // Iterate over each statement type
        foreach (range(1, 3) as $statementType) {
            // Determine if the statement type should be skipped
            if ($statementType == self::BATCH_STATEMENT
                && version_compare(Cassandra::CPP_DRIVER_VERSION, "2.2.3") < 0) {
                if (self::$configuration->verbose) {
                    fprintf(STDOUT, "Skipping Batch Statements in %s: Issue fixed in DataStax C/C++ v2.2.3" . PHP_EOL,
                        $this->getName());
                }
            } else {
                // Insert and assert values with CONSISTENCY_ALL
                $this->insert($statementType, $policy, 0, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_ALL);
                $this->assert($policy, 0, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_ALL);

                // Insert and assert values with CONSISTENCY_QUORUM
                $this->insert($statementType, $policy, 1, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_QUORUM);
                $this->assert($policy, 1, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_QUORUM);

                // Insert and assert values with CONSISTENCY_TWO
                $this->insert($statementType, $policy, 2, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_TWO);
                $this->assert($policy, 2, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_TWO);
            }
        }
    }

    /**
     * Statement execution supports fallthrough retry policy (write exception).
     *
     * This test will ensure that the PHP driver supports the ability to
     * provide any exception that occurs  when executing statements. This test
     * will ensure that a WriteTimeoutException occurs when a consistency level
     * cannot be achieved.
     *
     * @test
     * @ticket PHP-60
     *
     * @expectedException Dse\Exception\UnavailableException
     * @expectedExceptionMessageRegExp |Cannot achieve consistency level .*|
     */
    public function testFallThroughPolicyWrite() {
        // Create the retry policy (RF = 3 with 1 node)
        $policy = new Cassandra\RetryPolicy\Fallthrough();

        // Iterate over each statement type
        foreach (range(1, 3) as $statementType) {
            // Determine if the statement type should be skipped
            if ($statementType == self::BATCH_STATEMENT
                && version_compare(Cassandra::CPP_DRIVER_VERSION, "2.2.3") < 0) {
                if (self::$configuration->verbose) {
                    fprintf(STDOUT, "Skipping Batch Statements in %s: Issue fixed in DataStax C/C++ v2.2.3" . PHP_EOL,
                        $this->getName());
                }
            } else {
                // Create an exception during write
                $this->insert($statementType, $policy, 0, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_ALL);
            }
        }
    }

    /**
     * Statement execution supports fallthrough retry policy (read exception).
     *
     * This test will ensure that the PHP driver supports the ability to
     * provide any exception that occurs  when executing statements. This test
     * will ensure that a ReadTimeoutException occurs when a consistency level
     * cannot be achieved.
     *
     * @test
     * @ticket PHP-60
     *
     * @expectedException Dse\Exception\UnavailableException
     * @expectedExceptionMessageRegExp |Cannot achieve consistency level .*|
     */
    public function testFallThroughPolicyRead() {
        // Create the retry policy (RF = 3 with 1 node)
        $policy = new Cassandra\RetryPolicy\Fallthrough();

        // Iterate over each statement type
        foreach (range(1, 3) as $statementType) {
            // Determine if the statement type should be skipped
            if ($statementType == self::BATCH_STATEMENT
                && version_compare(Cassandra::CPP_DRIVER_VERSION, "2.2.3") < 0) {
                if (self::$configuration->verbose) {
                    fprintf(STDOUT, "Skipping Batch Statements in %s: Issue fixed in DataStax C/C++ v2.2.3" . PHP_EOL,
                        $this->getName());
                }
            } else {
                // Create an exception during read
                $this->insert($statementType, $policy, 0, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_ONE);
                $this->assert($policy, 0, self::NUMBER_OF_INSERTS, Cassandra::CONSISTENCY_ALL);
            }
        }
    }
}
