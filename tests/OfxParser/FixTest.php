<?php

namespace OfxParserTest;

/**
 * @covers OfxParser\Fix
 */
class FixTest extends \PHPUnit_Framework_TestCase
{
    public function testItMustCorrectInvalidDates()
    {
        $class = $this->getMockBuilder('OfxParser\Fix')
            ->disableOriginalConstructor()
            ->setMethods(['getFileContent', 'saveFileContent'])
            ->getMock();

        $startDate = '10000';

        $class
            ->expects($this->once())
            ->method('getFileContent')
            ->willReturn("<DTSTART>$startDate");

        $this->assertTrue(
            method_exists($class, 'replaceStartDate'),
            'Method "replaceStartDate()" must exist'
        );

        $testReturn = $class->replaceStartDate($startDate, '20000101100000');

        $expectedInstance = 'OfxParser\Fix';

        $this->assertInstanceOf(
            $expectedInstance,
            $testReturn,
            'It must return an instance of ' . $expectedInstance
        );
    }
}
