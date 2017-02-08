<?php

namespace OfxParserTest;

use OfxParser\Fix;

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

        $class->fixStartDate($startDate, '20000101100000');
    }
}
