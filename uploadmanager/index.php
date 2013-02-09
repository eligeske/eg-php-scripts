<?php
	
	$include_path = __DIR__;
	set_include_path($include_path);
	
// include filemanager class	
	
	require('class_filemanager.php');

	
// database
	
	$link = mysql_connect('localhost', 'root', '') or die(mysql_error()); 
	mysql_select_db('eg-php-scripts_uploadmanager') or die(mysql_error()); 


// file manager

	$filemanager = new filemanager($include_path.'\storage','filemanager');


// catch post

	// upload
	if(isset($_FILES['myfile'])){
		try{	
			$filemanager->addFile($_FILES['myfile']['tmp_name'],$_FILES['myfile']['name'], $_FILES['myfile']['type']);
			unset($_FILES,$_POST);	
		}catch(Exception $e){
			$message = $e->getMessage();
		}
	}
	// download
	if(isset($_GET['download'])){
		$filemanager->downloadFile($_GET['download']);
	}
	// view
	// if(isset($_GET['view'])){
		// $filemanager->viewFile($_GET['view']);
	// }
	// delete
	if(isset($_GET['delete'])){
		$filemanager->deleteFile($_GET['delete']);
	}
	
// show files

	$filelist = $filemanager->getFileListAll();



// just stuff for the page below this point


	$page = $_SERVER['PHP_SELF'];

?>

<html>
	<head>
		
	</head>
	<body>
		<div><?php if(isset($message)){ echo $message; } ?></div>
		<table>
			<tr>
				<td valign="top">
					<form action="<?php echo $page; ?>" method="POST" enctype="multipart/form-data">
						<label>file <input type="file" name="myfile" /></label>
						<br/>
						
						<input type="submit" name="submit" value="upload" />
					</form>						
				</td>
				<td valign="top">
					<div style="height: 200px; overflow: auto;">
						<?php foreach($filelist as $id => $row){ ?>
							<div>
								<a class="delete" href="<?php echo $page."?delete=".$row['storagename']; ?>">delete</a>
								<a class="download"  href="<?php echo $page."?download=".$row['storagename']; ?>">download</a>
								<!--<a class="view"  href="<?php echo $page."?view=".$row['storagename']; ?>">view</a>-->
								<span class="name"><?php echo $row['nicename']; ?></span>
							</div>							
						<?php } ?>	
					</div>
				</td>
			</tr>
		</table>

	</body>
</html>



