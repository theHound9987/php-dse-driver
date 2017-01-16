<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse;

/**
 * A PHP representation of a table
 */
interface Table {

    /**
     * Returns the name of this table
     * @return string Name of the table
     */
    public function name();

    /**
     * Return a table's option by name
     * @param mixed $name
     * @return Dse\Value Value of an option by name
     */
    public function option($name);

    /**
     * Returns all the table's options
     *               view's options.
     * @return array A dictionary of `string` and `Value` pairs of the
     */
    public function options();

    /**
     * Description of the table, if any
     * @return string Table description or null
     */
    public function comment();

    /**
     * Returns read repair chance
     * @return float Read repair chance
     */
    public function readRepairChance();

    /**
     * Returns local read repair chance
     * @return float Local read repair chance
     */
    public function localReadRepairChance();

    /**
     * Returns GC grace seconds
     * @return int GC grace seconds
     */
    public function gcGraceSeconds();

    /**
     * Returns caching options
     * @return string Caching options
     */
    public function caching();

    /**
     * Returns bloom filter FP chance
     * @return float Bloom filter FP chance
     */
    public function bloomFilterFPChance();

    /**
     * Returns memtable flush period in milliseconds
     * @return int Memtable flush period in milliseconds
     */
    public function memtableFlushPeriodMs();

    /**
     * Returns default TTL.
     * @return int Default TTL.
     */
    public function defaultTTL();

    /**
     * Returns speculative retry.
     * @return string Speculative retry.
     */
    public function speculativeRetry();

    /**
     * Returns index interval
     * @return int Index interval
     */
    public function indexInterval();

    /**
     * Returns compaction strategy class name
     * @return string Compaction strategy class name
     */
    public function compactionStrategyClassName();

    /**
     * Returns compaction strategy options
     * @return Dse\Map Compaction strategy options
     */
    public function compactionStrategyOptions();

    /**
     * Returns compression parameters
     * @return Dse\Map Compression parameters
     */
    public function compressionParameters();

    /**
     * Returns whether or not the `populate_io_cache_on_flush` is true
     * @return bool Value of `populate_io_cache_on_flush` or null
     */
    public function populateIOCacheOnFlush();

    /**
     * Returns whether or not the `replicate_on_write` is true
     * @return bool Value of `replicate_on_write` or null
     */
    public function replicateOnWrite();

    /**
     * Returns the value of `max_index_interval`
     * @return int Value of `max_index_interval` or null
     */
    public function maxIndexInterval();

    /**
     * Returns the value of `min_index_interval`
     * @return int Value of `min_index_interval` or null
     */
    public function minIndexInterval();

    /**
     * Returns column by name
     * @param string $name Name of the column
     * @return Dse\Column Column instance
     */
    public function column($name);

    /**
     * Returns all columns in this table
     * @return array A list of `Dse\Column` instances
     */
    public function columns();

    /**
     * Returns the partition key columns of the table
     * @return array A list of of `Dse\Column` instances
     */
    public function partitionKey();

    /**
     * Returns both the partition and clustering key columns of the table
     * @return array A list of of `Dse\Column` instances
     */
    public function primaryKey();

    /**
     * Returns the clustering key columns of the table
     * @return array A list of of `Dse\Column` instances
     */
    public function clusteringKey();

    /**
     * @return array A list of cluster column orders ('asc' and 'desc')
     */
    public function clusteringOrder();

}