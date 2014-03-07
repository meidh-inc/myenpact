<?php
    $max_file_size = 1048576;
    $target_path = "";
    if (isset($_POST['edit'])) { // The email information update button was clicked

    // start array to hold edit window errors
	$errors_edit = array();

    // Grab values for variables
        $logo_edit = trim($_FILES['logo_edit']['name']);

        $upload_errors = array(
            // http://php.net/manual/en/features.file-upload.errors.php
            UPLOAD_ERR_OK           => "No errors.",
            UPLOAD_ERR_INI_SIZE     => "Larger than upload_max_filesize.",
            UPLOAD_ERR_FORM_SIZE    => "Larger than form MAX_FILE_SIZE.",
            UPLOAD_ERR_PARTIAL      => "Partial upload.",
            UPLOAD_ERR_NO_FILE      => "No file.",
            UPLOAD_ERR_NO_TMP_DIR   => "No temporary directory.",
            UPLOAD_ERR_CANT_WRITE   => "Can't write to disk.",
            UPLOAD_ERR_EXTENSION    => "File upload stopped by extension.",
        );
        
        if(!$_FILES['logo_edit'] || empty($_FILES['logo_edit']) || !is_array($_FILES['logo_edit'])) {
            // error: nothing uploaded or wrong argument usage
            $message_edit = "No file was uploaded.";
        } elseif ($_FILES['logo_edit']['error'] != 0) {
            // error: report what PHP says went wrong
            $message_edit = $upload_errors[$_FILES['logo_edit']['error']];
        } else {
            print_r($_FILES['logo_edit']);
            $temp_path    = $_FILES['logo_edit']['tmp_name'];
            $filename     = basename($_FILES['logo_edit']['name']);
            $type         = $_FILES['logo_edit']['type'];
            $size         = $_FILES['logo_edit']['size'];
            echo "<br />" . $temp_path . "<br />";
            echo $filename . "<br />";
            echo $type . "<br />";
            echo $size . "<br />";
        }

        if(empty($filename) || empty($temp_path)) {
            $message_edit = "The file location was not available.";
        } else {
            $target_path = "images/logos/" . $filename;
            echo $target_path . "<br />";
        }
        
        if (file_exists($target_path)) {
            $message_edit = "The file {$filename} already exists.";
        }
        
        if(move_uploaded_file($temp_path, $target_path)) {
            // Success
        } else {
            // File was not moved.
            $message_edit = "The file upload failed, possibly due to incorrect permissions on the upload folder.";
        }
    }
?>
<form id="editform" action="test.php" method="post" enctype="multipart/form-data">
<table border='0' style="width:750px;">
    <tr>
	<td colspan="3" align="center">
            <?php if (!empty($message_edit)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_edit . "</p>";} ?>
	    <?php if (!empty($errors_edit)) { display_errors($errors_edit); } ?>
	</td>
    </tr>
    <tr>
	<td>FROM:
	</td>
	<td style="height:50px;">
	    Logo:
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>" />
            <input type="file" name ="logo_edit" maxlength="100" style="width:250px;" value="" />
	</td>
    </tr>
    <tr>
        <td colspan='3' style="padding-top:20px;text-align:center;">
	    <input type="submit" alt="edit" name="edit" value="Save" />
	    <br>
	    <font style="color:gray;font-size:10px;">(click x in upper-right corner to cancel)</font>
        </td>
    </tr>
</table>
<img src="<?php echo $target_path; ?>" />
</form>
