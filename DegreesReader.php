<?php
require('ExcelReader.php');

class DegreesReader extends ExcelReader {
	protected $tableHeaders = ['name','degree'];
	protected $DBtable = 'degrees';

}