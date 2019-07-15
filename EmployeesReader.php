<?php
require('ExcelReader.php');

class EmployeesReader extends ExcelReader {
	protected $tableHeaders = ['name','position'];
	protected $DBtable = 'employees';
}