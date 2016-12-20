<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse;

/**
 * A PHP representation of the CQL `float` datatype
 */
final class Float_ implements Value, Numeric {

    /**
     * Creates a new float.
     * @param mixed $value float value as a string, number or Cassandra\Float
     */
    public function __construct($value) { }

    /**
     * Returns string representation of the float value.
     * @return string float value
     */
    public function __toString() { }

    /**
     * The type of this float.
     * @return Type
     */
    public function type() { }

    /**
     * Returns the float value.
     * @return float float value
     */
    public function value() { }

    /**
     * @return mixed
     */
    public function isInfinite() { }

    /**
     * @return mixed
     */
    public function isFinite() { }

    /**
     * @return mixed
     */
    public function isNaN() { }

    /**
     * @param Cassandra\Numeric $num a number to add to this one
     * @return Numeric sum
     */
    public function add($num) { }

    /**
     * @param Cassandra\Numeric $num a number to subtract from this one
     * @return Numeric difference
     */
    public function sub($num) { }

    /**
     * @param Cassandra\Numeric $num a number to multiply this one by
     * @return Numeric product
     */
    public function mul($num) { }

    /**
     * @param Cassandra\Numeric $num a number to divide this one by
     * @return Numeric quotient
     */
    public function div($num) { }

    /**
     * @param Cassandra\Numeric $num a number to divide this one by
     * @return Numeric remainder
     */
    public function mod($num) { }

    /**
     * @return Numeric absolute value
     */
    public function abs() { }

    /**
     * @return Numeric negative value
     */
    public function neg() { }

    /**
     * @return Numeric square root
     */
    public function sqrt() { }

    /**
     * @return int this number as int
     */
    public function toInt() { }

    /**
     * @return float this number as float
     */
    public function toDouble() { }

    /**
     * Minimum possible Float value
     * @return Float minimum value
     */
    public static function min() { }

    /**
     * Maximum possible Float value
     * @return Float maximum value
     */
    public static function max() { }

}
