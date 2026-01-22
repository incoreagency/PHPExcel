<?php

use PHPUnit\Framework\TestCase;

class RowCellIteratorTest extends TestCase
{
    public $mockWorksheet;
    public $mockRowCell;

    protected function setUp(): void
    {
        if (!defined('PHPEXCEL_ROOT')) {
            define('PHPEXCEL_ROOT', APPLICATION_PATH . '/');
        }
        require_once(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
        
        $this->mockCell = $this->getMockBuilder('PHPExcel_Cell')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockWorksheet = $this->getMockBuilder('PHPExcel_Worksheet')
            ->disableOriginalConstructor()
            ->onlyMethods(['getHighestColumn', 'getCellByColumnAndRow', 'disconnectCells'])
            ->getMock();
        $this->mockWorksheet->method('getHighestColumn')
                 ->will($this->returnValue('E'));
        $this->mockWorksheet->method('getCellByColumnAndRow')
                 ->will($this->returnValue($this->mockCell));
        $this->mockWorksheet->method('disconnectCells')->willReturn(null);
    }


    public function testIteratorFullRange()
    {
        $iterator = new PHPExcel_Worksheet_RowCellIterator($this->mockWorksheet);
        $RowCellIndexResult = 'A';
        $this->assertEquals($RowCellIndexResult, $iterator->key());
        
        foreach ($iterator as $key => $RowCell) {
            $this->assertEquals($RowCellIndexResult++, $key);
            $this->assertInstanceOf('PHPExcel_Cell', $RowCell);
        }
    }

    public function testIteratorStartEndRange()
    {
        $iterator = new PHPExcel_Worksheet_RowCellIterator($this->mockWorksheet, 2, 'B', 'D');
        $RowCellIndexResult = 'B';
        $this->assertEquals($RowCellIndexResult, $iterator->key());
        
        foreach ($iterator as $key => $RowCell) {
            $this->assertEquals($RowCellIndexResult++, $key);
            $this->assertInstanceOf('PHPExcel_Cell', $RowCell);
        }
    }

    public function testIteratorSeekAndPrev()
    {
        $ranges = range('A', 'E');
        $iterator = new PHPExcel_Worksheet_RowCellIterator($this->mockWorksheet, 2, 'B', 'D');
        $RowCellIndexResult = 'D';
        $iterator->seek('D');
        $this->assertEquals($RowCellIndexResult, $iterator->key());

        for ($i = 1; $i < array_search($RowCellIndexResult, $ranges); $i++) {
            $iterator->prev();
            $expectedResult = $ranges[array_search($RowCellIndexResult, $ranges) - $i];
            $this->assertEquals($expectedResult, $iterator->key());
        }
    }

    public function testSeekOutOfRange()
    {
        $this->expectException(PHPExcel_Exception::class);

        $iterator = new PHPExcel_Worksheet_RowCellIterator($this->mockWorksheet, 2, 'B', 'D');
        $iterator->seek(1);
    }

    public function testPrevOutOfRange()
    {
        $this->expectException(PHPExcel_Exception::class);
        
        $iterator = new PHPExcel_Worksheet_RowCellIterator($this->mockWorksheet, 2, 'B', 'D');
        $iterator->prev();
    }
}
