<?php

/*
  Copyright (c) 2017 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse;

/**
 * Default cluster implementation.
 *
 * @see Cluster
 */
final class DefaultCluster implements Cluster {

    /**
     * Creates a new Session instance.
     *
     * @param string $keyspace Optional keyspace name
     * @param int $timeout Optional timeout
     *
     * @return Session Session instance
     */
    public function connect($keyspace, $timeout) { }

    /**
     * Creates a new Session instance.
     *
     * @param string $keyspace Optional keyspace name
     *
     * @return Future A Future Session instance
     */
    public function connectAsync($keyspace) { }

}
