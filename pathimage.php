<?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
// ... (validation code here) ...
if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    // Connect to database
    // Insert $target_file path into the 'images' table
    echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded and path saved to the database.";
} else {
    echo "Sorry, there was an error uploading your file.";
}
?>
