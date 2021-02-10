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

        $class->setFileContent("<DTSTART>$startDate");

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

    public function testItMustCorrectInvalidMemo()
    {
        $class = $this->getMockBuilder('OfxParser\Fix')
            ->disableOriginalConstructor()
            ->setMethods(['getFileContent', 'saveFileContent'])
            ->getMock();

        $ofxFile = dirname(__DIR__) . '/fixtures/wrong/ofxdata-inter.ofx';

        if (!file_exists($ofxFile)) {
            self::markTestSkipped('Could not find data file, cannot test Ofx Class');
        }

        $class->setFileContent(file_get_contents($ofxFile));

        $this->assertTrue(
            method_exists($class, 'replaceStartDate'),
            'Method "replaceUsingRegexCallback()" must exist'
        );

        $testReturn = $class->replaceUsingRegexCallback(
            '/(?<=MEMO\>)(.*\n.*)(?=\<\/MEMO)/',
            function ($matches) {
                return preg_replace('/\n|\r/', ' ', $matches[0]);
            }
        );

        $expectedInstance = 'OfxParser\Fix';

        $this->assertInstanceOf(
            $expectedInstance,
            $testReturn,
            'It must return an instance of ' . $expectedInstance
        );

        $return = $class->getFileContentFromMemory();

        $ofxRightFile = dirname(__DIR__) . '/fixtures/right/ofxdata-inter.ofx';
        $this->assertStringEqualsFile($ofxRightFile, $return);
    }
}
