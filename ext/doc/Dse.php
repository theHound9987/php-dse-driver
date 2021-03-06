<?php

/*
  Copyright (c) 2017 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

/**
 * The main entry point to the DataStax Enterprise PHP Driver.
 *
 * Use Dse::cluster() to build a cluster instance.
 * Use Dse::ssl() to build SSL options instance.
 * Use Dse::graphOptions() to build a graph options instance.
 */
final class Dse {

    /**
     * Consistency level ANY means the request is fulfilled as soon as the data
     * has been written on the Coordinator. Requests with this consistency level
     * are not guranteed to make it to Replica nodes.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_ANY = 0;

    /**
     * Consistency level ONE guarantees that data has been written to at least
     * one Replica node.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_ONE = 1;

    /**
     * Consistency level TWO guarantees that data has been written to at least
     * two Replica nodes.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_TWO = 2;

    /**
     * Consistency level THREE guarantees that data has been written to at least
     * three Replica nodes.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_THREE = 3;

    /**
     * Consistency level QUORUM guarantees that data has been written to at least
     * the majority of Replica nodes. How many nodes exactly are a majority
     * depends on the replication factor of a given keyspace and is calculated
     * using the formula `ceil(RF / 2 + 1)`, where `ceil` is a mathematical
     * ceiling function and `RF` is the replication factor used. For example,
     * for a replication factor of `5`, the majority is `ceil(5 / 2 + 1) = 3`.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_QUORUM = 4;

    /**
     * Consistency level ALL guarantees that data has been written to all
     * Replica nodes.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_ALL = 5;

    /**
     * Same as `CONSISTENCY_QUORUM`, but confined to the local data center. This
     * consistency level works only with `NetworkTopologyStrategy` replication.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_LOCAL_QUORUM = 6;

    /**
     * Consistency level EACH_QUORUM guarantees that data has been written to at
     * least a majority Replica nodes in all datacenters. This consistency level
     * works only with `NetworkTopologyStrategy` replication.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_EACH_QUORUM = 7;

    /**
     * This is a serial consistency level, it is used in conditional updates,
     * e.g. (`CREATE|INSERT ... IF NOT EXISTS`), and should be specified as the
     * `serial_consistency` execution option when invoking `session.execute`
     * or `session.execute_async`.
     *
     * Consistency level SERIAL, when set, ensures that a Paxos commit fails if
     * any of the replicas is down.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_SERIAL = 8;

    /**
     * Same as `CONSISTENCY_SERIAL`, but confined to the local data center. This
     * consistency level works only with `NetworkTopologyStrategy` replication.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_LOCAL_SERIAL = 9;

    /**
     * Same as `CONSISTENCY_ONE`, but confined to the local data center. This
     * consistency level works only with `NetworkTopologyStrategy` replication.
     *
     * @see Session::execute()
     */
    const CONSISTENCY_LOCAL_ONE = 10;

    /**
     * Perform no verification of nodes when using SSL encryption.
     *
     * @see SSLOptions\Builder::withVerifyFlags()
     */
    const VERIFY_NONE = 0;

    /**
     * Verify presence and validity of SSL certificates.
     *
     * @see SSLOptions\Builder::withVerifyFlags()
     */
    const VERIFY_PEER_CERT = 1;

    /**
     * Verify that the IP address matches the SSL certificate’s common name or
     * one of its subject alternative names. This implies the certificate is
     * also present.
     *
     * @see SSLOptions\Builder::withVerifyFlags()
     */
    const VERIFY_PEER_IDENTITY = 2;

    /**
     * @see BatchStatement::__construct()
     */
    const BATCH_LOGGED = 0;

    /**
     * @see BatchStatement::__construct()
     */
    const BATCH_UNLOGGED = 1;

    /**
     * @see BatchStatement::__construct()
     */
    const BATCH_COUNTER = 2;

    /**
     * Used to disable logging.
     */
    const LOG_DISABLED = 0;

    /**
     * Allow critical level logging.
     */
    const LOG_CRITICAL = 1;

    /**
     * Allow error level logging.
     */
    const LOG_ERROR = 2;

    /**
     * Allow warning level logging.
     */
    const LOG_WARN = 3;

    /**
     * Allow info level logging.
     */
    const LOG_INFO = 4;

    /**
     * Allow debug level logging.
     */
    const LOG_DEBUG = 5;

    /**
     * Allow trace level logging.
     */
    const LOG_TRACE = 6;

    /**
     * When using a map, collection or set of type text, all of its elements
     * must be strings.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_TEXT = 'text';

    /**
     * When using a map, collection or set of type ascii, all of its elements
     * must be strings.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_ASCII = 'ascii';

    /**
     * When using a map, collection or set of type varchar, all of its elements
     * must be strings.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_VARCHAR = 'varchar';

    /**
     * When using a map, collection or set of type bigint, all of its elements
     * must be instances of Bigint.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_BIGINT = 'bigint';

    /**
     * When using a map, collection or set of type smallint, all of its elements
     * must be instances of Inet.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_SMALLINT = 'smallint';

    /**
     * When using a map, collection or set of type tinyint, all of its elements
     * must be instances of Inet.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_TINYINT = 'tinyint';

    /**
     * When using a map, collection or set of type blob, all of its elements
     * must be instances of Blob.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_BLOB = 'blob';

    /**
     * When using a map, collection or set of type bool, all of its elements
     * must be boolean.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * When using a map, collection or set of type counter, all of its elements
     * must be instances of Bigint.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_COUNTER = 'counter';

    /**
     * When using a map, collection or set of type decimal, all of its elements
     * must be instances of Decimal.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_DECIMAL = 'decimal';

    /**
     * When using a map, collection or set of type double, all of its elements
     * must be doubles.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_DOUBLE = 'double';

    /**
     * When using a map, collection or set of type float, all of its elements
     * must be instances of Float.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_FLOAT = 'float';

    /**
     * When using a map, collection or set of type int, all of its elements
     * must be ints.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_INT = 'int';

    /**
     * When using a map, collection or set of type timestamp, all of its elements
     * must be instances of Timestamp.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * When using a map, collection or set of type uuid, all of its elements
     * must be instances of Uuid.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_UUID = 'uuid';

    /**
     * When using a map, collection or set of type varint, all of its elements
     * must be instances of Varint.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_VARINT = 'varint';

    /**
     * When using a map, collection or set of type timeuuid, all of its elements
     * must be instances of Timeuuid.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_TIMEUUID = 'timeuuid';

    /**
     * When using a map, collection or set of type inet, all of its elements
     * must be instances of Inet.
     *
     * @see Set::__construct()
     * @see Collection::__construct()
     * @see Map::__construct()
     */
    const TYPE_INET = 'inet';

    /**
     * The current version of the extension.
     */
    const VERSION = '1.0.0';

    /**
     * The version of the cpp-driver library used to build the cpp-driver-dse
     * driver library.
     */
    const CPP_DRIVER_VERSION = '2.6.0-dev';

    /**
     * The version of the cpp-driver-dse library used to build the php-driver-dse
     * extension.
     */
    const CPP_DRIVER_DSE_VERSION = '1.2.0';

    /**
     * Creates a new cluster builder for constructing a Cluster object.
     *
     * @return Cluster\Builder A cluster builder object with default settings
     */
    public static function cluster() { }

    /**
     * Creates a new ssl builder for constructing a SSLOptions object.
     *
     * @return SSLOptions\Builder A SSL options builder with default settings
     */
    public static function ssl() { }

    /**
     * Creates a new graph options builder for constructing a Dse\Graph\Options object.
     *
     * @return Dse\Graph\Options A graph options builder with default settings
     */
    public static function graphOptions() { }

}
