<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse;

/**
 * Rows represent a result of statement execution.
 */
final class Rows implements \Iterator, \ArrayAccess {

    /**
     */
    public function __construct() { }

    /**
     * Returns the number of rows.
     *
     * @see \Countable::count()
     * @return int number of rows
     */
    public function count() { }

    /**
     * Resets the rows iterator.
     *
     * @see \Iterator::rewind()
     * @return void
     */
    public function rewind() { }

    /**
     * Returns current row.
     *
     * @see \Iterator::current()
     * @return array current row
     */
    public function current() { }

    /**
     * Returns current index.
     *
     * @see \Iterator::key()
     * @return int index
     */
    public function key() { }

    /**
     * Advances the rows iterator by one.
     *
     * @see \Iterator::next()
     * @return void
     */
    public function next() { }

    /**
     * Returns existence of more rows being available.
     *
     * @see \Iterator::valid()
     * @return bool whether there are more rows available for iteration
     */
    public function valid() { }

    /**
     * Returns existence of a given row.
     *
     *
     * @see \ArrayAccess::offsetExists()
     * @param int $offset row index
     * @return bool whether a row at a given index exists
     */
    public function offsetExists($offset) { }

    /**
     * Returns a row at given index.
     *
     *
     * @see \ArrayAccess::offsetGet()
     * @param int $offset row index
     * @return array|null row at a given index
     */
    public function offsetGet($offset) { }

    /**
     * Sets a row at given index.
     *
     * @throws Exception\DomainException
     *
     *
     * @see \ArrayAccess::offsetSet()
     * @param int $offset row index
     * @param array $value row value
     * @return void
     */
    public function offsetSet($offset, $value) { }

    /**
     * Removes a row at given index.
     *
     * @throws Exception\DomainException
     *
     *
     * @see \ArrayAccess::offsetUnset()
     * @param int $offset row index
     * @return void
     */
    public function offsetUnset($offset) { }

    /**
     * Check for the last page when paging.
     * @return bool whether this is the last page or not
     */
    public function isLastPage() { }

    /**
     * Get the next page of results.
     *
     * @param float|null $timeout
     * @return Rows|null loads and returns next result page
     */
    public function nextPage($timeout) { }

    /**
     * Get the next page of results asynchronously.
     * @return Future returns future of the next result page
     */
    public function nextPageAsync() { }

    /**
     * Returns the raw paging state token.
     * @return string
     */
    public function pagingStateToken() { }

    /**
     * Get the first row.
     * @return array|null returns first row if any
     */
    public function first() { }

}