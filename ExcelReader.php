<?php

require('library/php-excel-reader/excel_reader2.php');
require('library/SpreadsheetReader.php');

abstract class ExcelReader {
	protected $reader,$mysqlConnection;
	public $has_headers;
	protected $data = [];
	protected $tableHeaders;
	protected $DBtable;
	protected $dbInfo = ['host'=>'localhost','user'=>'root','password'=>'','db'=>'data_from_excel'];
	public function __construct($filePath, $has_headers = false) {

		$this->reader = new SpreadsheetReader($filePath);
		$this->totalSheet = count($this->reader->sheets());
		$this->has_headers = $has_headers;
	}

	public function getData() {

		$reader = $this->reader;

		for($i=0; $i < $this->totalSheet;$i++)
        {
            $reader->ChangeSheet($i);
            $counter = 0;
            foreach ($reader as $Row)
            {
            	// if table has headers it will skip first row in this case it'll be table header
            	$counter++;
            	if($this->has_headers && $counter == 1) continue;
            	// get every row data according to the arrangement of table headers variable
            	$rowData = array_combine($this->tableHeaders, $Row);

                $this->data[] = $rowData;

            }
        }

        return $this->data;
	}
	protected function ConnectToDB() {
		$this->mysqlConnection = new mysqli($this->dbInfo['host'], 
											$this->dbInfo['user'], 
											$this->dbInfo['password'], 
											$this->dbInfo['db']);
		// Check connection
		if ($this->mysqlConnection->connect_error) {
		    die("Connection failed: " . $this->mysqlConnection->connect_error);
		}
	}

	public function saveToDB() {
		$data = $this->getDataEscaped();
		$query = "INSERT INTO ".$this->DBtable."( ".implode(",",$this->tableHeaders).") values";
		$i = 1;
		foreach ($data as $row) {
			if(count($data) == $i) {
				$query .= "('".implode("','", $row)."')";
				continue;
			}
			$i++;
			$query .= "('".implode("','", $row)."'),";
		}
		return $this->mysqlConnection->query($query);
	}
	
	public function getDataEscaped() {
		if(!isset($this->mysqlConnection)) $this->ConnectToDB();

		$this->getData();

		$escapedData = [];

		foreach ($this->data as $row) {

			$escapedRow = array_map(function($item) {
				return mysqli_real_escape_string($this->mysqlConnection,$item);
			}, $row);
			$escapedData[] = $escapedRow;

		}

		return $escapedData;
	}
}