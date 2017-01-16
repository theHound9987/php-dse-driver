<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse;

/**
 * A future returned from Dse\Session::closeAsync().
 *
 * @see Session::closeAsync()
 */
final class FutureClose implements Future {

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\TimeoutException
     *
     * @param float|null $timeout
     * @return mixed a value that the future has been resolved with
     */
    public function get($timeout) { }

}