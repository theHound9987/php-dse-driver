<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse\Type;

/**
 * A class that represents the set type. The set type contains the type of the
 * elements contain in the set.
 */
final class Set extends Type {

    private function __construct() { }

    /**
     * Returns "set"
     * @return string "set"
     */
    public function name() { }

    /**
     * Returns type of values
     * @return Type Type of values
     */
    public function valueType() { }

    /**
     * Returns type representation in CQL, e.g. `set<varchar>`
     * @return string Type representation in CQL
     */
    public function __toString() { }

    /**
     * Creates a new Dse\Set from the given values.
     *
     * @throws Exception\InvalidArgumentException when values given are of a
     *                                            different type than what this
     *                                            set type expects.
     * @param mixed $value ,... One or more values to be added to the set. When no values are given, creates an empty set.
     * @return Dse\Set A set with given values.
     */
    public function create($value) { }

}