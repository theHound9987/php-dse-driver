<?php

/*
  Copyright (c) 2017 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse\Type;

/**
 * A class that represents the list type. The list type contains the type of the
 * elements contain in the list.
 */
final class Collection extends Type {

    private function __construct() { }

    /**
     * Returns "list"
     *
     * @return string "list"
     */
    public function name() { }

    /**
     * Returns type of values
     *
     * @return Type Type of values
     */
    public function valueType() { }

    /**
     * Returns type representation in CQL, e.g. `list<varchar>`
     *
     * @return string Type representation in CQL
     */
    public function __toString() { }

    /**
     * Creates a new Collection from the given values.  When no values
     * given, creates an empty list.
     *
     * @param mixed $value ,...      One or more values to be added to the list.
     *
     * @throws Exception\InvalidArgumentException when values given are of a
     *                                            different type than what this
     *                                            list type expects.
     *
     * @return Collection A list with given values.
     */
    public function create($value) { }

}
