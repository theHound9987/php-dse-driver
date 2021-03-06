<?php

/*
  Copyright (c) 2017 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse\Graph;

/**
 * A future result set of an asynchronously running graph query.
 *
 * @see Session::executeGraphAsync()
 */
final class FutureResultSet implements \Dse\Future {

    /**
     * Get the result set of an asynchronously executed graph query.
     *
     * @param double $timeout The amount of time to wait for the graph query to return in seconds.
     *
     * @throws Exception
     *
     * @return Graph\ResultSet A result set of arbitrary graph results.
     */
    public function get($timeout) { }

}
