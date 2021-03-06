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

namespace Dse;

/**
 * @requires extension dse
 */
class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException         InvalidArgumentException
     * @expectedExceptionMessage  type must be a string or an instance of Dse\Type, an instance of stdClass given
     */
    public function testInvalidType()
    {
        new Set(new \stdClass());
    }

    /**
     * @expectedException         InvalidArgumentException
     * @expectedExceptionMessage  Unsupported type 'custom type'
     */
    public function testUnsupportedStringType()
    {
        new Set('custom type');
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage type must be a valid Dse\Type,
     *                           an instance of Dse\Type\UnsupportedType given
     */
    public function testUnsupportedType()
    {
        new Set(new Type\UnsupportedType());
    }

    public function testContainsUniqueValues()
    {
        $set = new Set(\Dse::TYPE_VARINT);
        $this->assertEquals(0, count($set));
        $set->add(new Varint('123'));
        $this->assertEquals(1, count($set));
        $set->add(new Varint('123'));
        $this->assertEquals(1, count($set));
    }

    /**
     * @dataProvider scalarTypes
     */
    public function testScalarKeys($type, $value, $valueCopy)
    {
        $set = Type::set($type)->create();
        $set->add($value);
        $this->assertEquals(1, count($set));
        $this->assertTrue($set->has($value));
        $this->assertTrue($set->has($valueCopy));
        $set->remove($value);
        $this->assertEquals(0, count($set));
    }

    public function scalarTypes()
    {
        return array(
            array(Type::ascii(), "ascii", "ascii"),
            array(Type::bigint(), new Bigint("9223372036854775807"), new Bigint("9223372036854775807")),
            array(Type::blob(), new Blob("blob"), new Blob("blob")),
            array(Type::boolean(), true, true),
            array(Type::counter(), new Bigint(123), new Bigint(123)),
            array(Type::decimal(), new Decimal("3.14159265359"), new Decimal("3.14159265359")),
            array(Type::double(), 3.14159, 3.14159),
            array(Type::float(), new Float(3.14159), new Float(3.14159)),
            array(Type::inet(), new Inet("127.0.0.1"), new Inet("127.0.0.1")),
            array(Type::int(), 123, 123),
            array(Type::text(), "text", "text"),
            array(Type::timestamp(), new Timestamp(123), new Timestamp(123)),
            array(Type::timeuuid(), new Timeuuid(0), new Timeuuid(0)),
            array(Type::uuid(), new Uuid("03398c99-c635-4fad-b30a-3b2c49f785c2"), new Uuid("03398c99-c635-4fad-b30a-3b2c49f785c2")),
            array(Type::varchar(), "varchar", "varchar"),
            array(Type::varint(), new Varint("9223372036854775808"), new Varint("9223372036854775808")),
            array(Type::duration(), new Duration(1, 2, 3), new Duration(1, 2, 3)),
            array(Type::point(), new Point(3.14159, 3.14159), new Point(3.14159, 3.14159)),
            array(Type::lineString(), new LineString(new Point(3.14159, 3.14159), new Point(1.0, 1.0)),
                                      new LineString(new Point(3.14159, 3.14159), new Point(1.0, 1.0))),
            array(Type::polygon(), new Polygon(new LineString(new Point(3.14159, 3.14159),
                                                              new Point(1.0, 1.0),
                                                              new Point(3.14159, 2.0),
                                                              new Point(3.14159, 3.14159))),
                                   new Polygon(new LineString(new Point(3.14159, 3.14159),
                                                              new Point(1.0, 1.0),
                                                              new Point(3.14159, 2.0),
                                                              new Point(3.14159, 3.14159)))),
            array(Type::dateRange(), new DateRange(DateRange\Precision::YEAR, 1234, DateRange\Precision::DAY, 9876),
                                     new DateRange(DateRange\Precision::YEAR, 1234, DateRange\Precision::DAY, 9876))
        );
    }

    /**
     * @dataProvider compositeTypes
     */
    public function testCompositeKeys($type)
    {
        $set = Type::set($type)->create();

        $set->add($type->create("a", "1", "b", "2"));
        $this->assertTrue($set->has($type->create("a", "1", "b", "2")));
        $this->assertEquals(1, count($set));

        $set->add($type->create("c", "3", "d", "4", "e", "5"));
        $this->assertTrue($set->has($type->create("c", "3", "d", "4", "e", "5")));
        $this->assertEquals(2, count($set));

        $set->remove($type->create("a", "1", "b", "2"));
        $this->assertFalse($set->has($type->create("a", "1", "b", "2")));
        $this->assertEquals(1, count($set));

        $set->remove($type->create("c", "3", "d", "4", "e", "5"));
        $this->assertFalse($set->has($type->create("c", "3", "d", "4", "e", "5")));
        $this->assertEquals(0, count($set));
    }

    public function compositeTypes()
    {
        return array(
            array(Type::map(Type::varchar(), Type::varchar())),
            array(Type::set(Type::varchar())),
            array(Type::collection(Type::varchar()))
        );
    }

    /**
     * @expectedException         InvalidArgumentException
     * @expectedExceptionMessage  Unsupported type 'some custom type'
     */
    public function testSupportsOnlyDseTypes()
    {
        new Set('some custom type');
    }

    /**
     * @dataProvider dseTypes
     */
    public function testSupportsAllDseTypes($type)
    {
        new Set($type);
    }

    /**
     * @dataProvider dseTypes
     */
    public function testReturnsItsType($type)
    {
        $set = new Set($type);
        $this->assertEquals($type, $set->type()->valueType());
    }

    public function dseTypes()
    {
        return array(
            array(\Dse::TYPE_ASCII),
            array(\Dse::TYPE_BIGINT),
            array(\Dse::TYPE_BLOB),
            array(\Dse::TYPE_BOOLEAN),
            array(\Dse::TYPE_COUNTER),
            array(\Dse::TYPE_DECIMAL),
            array(\Dse::TYPE_DOUBLE),
            array(\Dse::TYPE_FLOAT),
            array(\Dse::TYPE_INT),
            array(\Dse::TYPE_TEXT),
            array(\Dse::TYPE_TIMESTAMP),
            array(\Dse::TYPE_UUID),
            array(\Dse::TYPE_VARCHAR),
            array(\Dse::TYPE_VARINT),
            array(\Dse::TYPE_TIMEUUID),
            array(\Dse::TYPE_INET),
        );
    }

    /**
     * @expectedException         InvalidArgumentException
     * @expectedExceptionMessage  argument must be an instance of Dse\Varint, an instance of Dse\Decimal given
     */
    public function testValidatesTypesOfElements()
    {
        $set = new Set(\Dse::TYPE_VARINT);
        $set->add(new Decimal('123'));
    }

    /**
     * @expectedException         InvalidArgumentException
     * @expectedExceptionMessage  Invalid value: null is not supported inside sets
     */
    public function testSupportsNullValues()
    {
        $set = new Set(\Dse::TYPE_VARINT);
        $set->add(null);
    }

    /**
     * @dataProvider sampleNumbers
     */
    public function testSupportsIteration($numbers)
    {
        $set = new Set(\Dse::TYPE_INT);

        foreach ($numbers as $number) {
            $set->add($number);
        }

        $this->assertEquals(count($numbers), count($set));

        foreach ($set as $i => $number) {
            $this->assertEquals($numbers[$i], $number);
        }
    }

    /**
     * @dataProvider sampleNumbers
     */
    public function testSupportsConversionToArray($numbers)
    {
        $set = new Set(\Dse::TYPE_INT);

        foreach ($numbers as $number) {
            $set->add($number);
        }

        $this->assertEquals($numbers, $set->values());
    }

    /**
     * @dataProvider sampleNumbers
     */
    public function testResumesIterationAfterConvertingToArray($numbers)
    {
        $set = new Set(\Dse::TYPE_INT);

        foreach ($numbers as $number) {
            $set->add($number);
        }

        $set->rewind();

        $this->assertEquals(1, $set->current());
        $set->next();
        $this->assertEquals(2, $set->current());
        $set->next();
        $this->assertEquals(3, $set->current());

        $set->values();

        $set->next();
        $this->assertEquals(4, $set->current());
        $set->next();
        $this->assertEquals(5, $set->current());
        $set->next();
        $this->assertEquals(6, $set->current());
    }

    public function sampleNumbers()
    {
        return array(
            array(array(1, 2, 3, 4, 5, 6, 7, 8, 9))
        );
    }

    public function testSupportsRetrievingValues()
    {
        $values = array(new Varint('1'), new Varint('2'), new Varint('3'),
                        new Varint('4'), new Varint('5'), new Varint('6'),
                        new Varint('7'), new Varint('8'));
        $set = new Set(\Dse::TYPE_VARINT);

        for ($i = 0; $i < count($values); $i++) {
            $set->add($values[$i]);
        }

        $this->assertEquals($values, $set->values());

    }

    /**
     * @dataProvider equalTypes
     */
    public function testCompareEquals($value1, $value2)
    {
        $this->assertEquals($value1, $value2);
        $this->assertTrue($value1 == $value2);
    }

    public function equalTypes()
    {
        $setType = Type::set(Type::int());
        return array(
            array(Type::set(Type::int())->create(),
                  Type::set(Type::int())->create()),
            array(Type::set(Type::int())->create(1, 2, 3),
                  Type::set(Type::int())->create(1, 2, 3)),
            array(Type::set(Type::varchar())->create('a', 'b', 'c'),
                  Type::set(Type::varchar())->create('a', 'b', 'c')),
            array(Type::set($setType)->create($setType->create(1, 2, 3)),
                  Type::set($setType)->create($setType->create(1, 2, 3))),
        );
    }

    /**
     * @dataProvider notEqualTypes
     */
    public function testCompareNotEquals($value1, $value2)
    {
        $this->assertNotEquals($value1, $value2);
        $this->assertFalse($value1 == $value2);
    }

    public function notEqualTypes()
    {
        $setType = Type::set(Type::int());
        return array(
            array(Type::set(Type::int())->create(),
                  Type::set(Type::varchar())->create()),
            array(Type::set(Type::int())->create(1, 2, 3),
                  Type::set(Type::int())->create(4, 5, 6)),
            array(Type::set(Type::int())->create(1, 2, 3),
                  Type::set(Type::int())->create(1)),
            array(Type::set(Type::varchar())->create('a', 'b', 'c'),
                  Type::set(Type::varchar())->create('a', 'b', 'd')),
            array(Type::set($setType)->create($setType->create(1, 2, 3)),
                  Type::set($setType)->create($setType->create(4, 5, 6))),
        );
    }
}
