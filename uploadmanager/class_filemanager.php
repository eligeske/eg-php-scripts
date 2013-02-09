<?php

/**
 * 
 * Requirements:  This class assumes there is an open MySQL connection.
 * 
 */
class filemanager {
	
	private $_storagepath; // path to storage directory
	private $_tablename; // name of table in database
	
	public function __construct($storagepath,$tablename){
		
		// set storage location
		$this->_storagepath = $storagepath;
		
		// set table name
		$this->_tablename = $tablename;

	}

 /*******************/
 /** ADDING A FILE **/
 /*******************/
 	
	public function addFile($tmp_name,$name, $mimetype){
		
		$name = mysql_real_escape_string($name);
		$mimetype = mysql_real_escape_string($mimetype);
		
		// validate
		if(!$this->_validate($tmp_name,$name,$mimetype)){
			throw new Exception("Missing Parameters", 1);
		}
		// get file extension
		$extension = $this->_getFileExtension($name);
		
		// create guid/storage name
		$storagename = $this->_createStorageName($extension);
		
		// move file
		if(!$this->_movefile($tmp_name, $storagename,$extension)){
			throw new Exception("Error In Moving File to Location: ".$this->_storagepath."/".$storagename.'.'.$extension, 1);
		}
		
		// on move file success insert new name
		if(!$this->_insertNewFile($storagename, $name, $extension, $mimetype)){
			if(!$this->_deleteFile($storagename,$extension)){
				 $this->_exception("Error Deleting File from Location: ".$this->_storagepath."/".$storagename.'.'.$extension); 
			}
			$this->_exception("Error In Moving File to Location: ".$this->_storagepath."/".$storagename);
		}
		
	}
	
	private function _validate($tmp_name,$name,$mimetype){
		return ($tmp_name == "" || $name == "" || $mimetype == "")?false:true;		
	}
	
	private function _insertNewFile($storagename,$name,$extension,$mimetype){
		$query = "INSERT INTO $this->_tablename (storagename,nicename,extension,mimetype) VALUES ('$storagename', '$name', '$extension', '$mimetype')";
		 return (!mysql_query($query)) ? false : true;
	}
	
	private function _movefile($tmp_name,$storagename,$extension){
		$destination = $this->_storagepath."/".$storagename.'.'.$extension;
		return move_uploaded_file($tmp_name,$destination);
	}
	
	private function _getFileExtension($name){
		return pathinfo($name, PATHINFO_EXTENSION);
	}
	
	private function _createStorageName($extension){
		return trim(com_create_guid(), '{}');
	}
	
	private function _deleteFile($storagename,$extension){
		return unlink($destination = $this->_storagepath."/".$storagename.'.'.$extension);
	}
	
	private function _exception($message){
		throw new Exception($message, 1);
	}
	

 /***********************/
 /** RETRIEVING A FILE **/
 /***********************/	
	
	
	public function viewFile($storagename){
		if(!$details = $this->_getFileDetailsFromStorageName($storagename)){
			$this->_exception("Error Getting File Details from Database: ".$storagename); 
		}
		$file = $this->_storagepath.'/'.$details['storagename'].'.'.$details['extension'];
		$header = "filename=".$details['nicename']."; Content-type: ".$details['mimetype']."; ";
		// echo $header; exit();
		header($header);
		readfile($file);
		exit();
	}
	public function downloadFile($storagename){		
		if(!$details = $this->_getFileDetailsFromStorageName($storagename)){
			$this->_exception("Error Getting File Details from Database: ".$storagename); 
		}
		$file = $this->_storagepath.'/'.$details['storagename'].'.'.$details['extension'];
		$header = "Content-disposition: attachment; filename=".$details['nicename']."; Content-type: ".$details['mimetype']."; ";
		header($header);
		readfile($file);
		exit();
	}
	public function deleteFile($storagename){		
		if(!$extension = $this->_getFileExtensionFromStorageName($storagename)){
			 $this->_exception("Error Getting Type from Database"); 
		}	
		if(!$this->_deleteFileFromDatabase($storagename)){
			 $this->_exception("Error Deleting File from Database: ".$storagename); 
		}		
		if(!$this->_deleteFile($storagename,$extension)){
			 $this->_exception("Error Deleting File from Location: ".$this->_storagepath."/".$storagename.'.'.$extension); 
		}		
	}
	
	private function _deleteFileFromDatabase($storagename){
		$storagename = mysql_real_escape_string($storagename);
		$query = "DELETE FROM $this->_tablename WHERE storagename ='$storagename' LIMIT 1";
		return (mysql_query($query));
	}
	private function _getFileExtensionFromStorageName($storagename){
		return ($details = $this->_getFileDetailsFromStorageName($storagename))?$details['extension']:false; 
	}
	private function _getFileDetailsFromStorageName($storagename){
		$storagename = mysql_real_escape_string($storagename);
		$query = "SELECT * FROM $this->_tablename WHERE storagename = '$storagename'";
		$result = mysql_query($query) or $this->_exception('Error Retrieving Details from Database');
		$row = mysql_fetch_assoc($result);
		return (isset($row))?$row:false;
	}
	

	
 /****************************/
 /** RETRIEVING A FILE LIST **/
 /****************************/
 	
	public function getFileListAll(){
		return $this->_retrieveAllFiles();
	}
	private function _retrieveAllFiles(){
		$query = "SELECT * FROM $this->_tablename";
		$result = mysql_query($query) or $this->_exception('Error Retrieving All Files from Database');
		$rows = array();
		while($row = mysql_fetch_assoc($result)){
			$rows[$row['id']] = $row;
		}
		return $rows;
	}
	public function getFileListByMIMEType(){
		
	}
	public function getFileListByDate(){
		
	}
	public function getFileListByDateRange(){
		
	}
}
