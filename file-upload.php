<?php
header('Content-Type: application/json'); // Indicate that you're returning JSON


// Directory where uploaded files will be saved
$targetDirectory = "uploads/";
// Ensure upload directory exists
if (!file_exists($targetDirectory)) {
    mkdir($targetDirectory, 0777, true);
}

if ($_FILES['abstractFile']['error'] === UPLOAD_ERR_INI_SIZE) {
    die('Error: Uploaded file exceeds the configured limit.');
} elseif ($_FILES['abstractFile']['error'] !== UPLOAD_ERR_OK) {
    die('Error: File upload error code - ' . $_FILES['abstractFile']['error']);
}


// Path of the file to be uploaded
$targetFile = $targetDirectory . basename($_FILES["abstractFile"]["name"]);

$idSubmission = uniqid();

// Sanitizing and creating a safe file name
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_POST['name']);
$safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $_FILES["abstractFile"]["name"]);
$idFile = $idSubmission . '_' . $safeName . '_' . $safeFileName;

// Path of the file to be uploaded
$targetFile = $targetDirectory . $idFile;

$isUploadSuccessful = false;

// Check if the file is a PDF
$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
if ($fileType != "pdf") {
    echo "Sorry, only PDF files are allowed.";
} else {
    // Try to upload the file
    if (move_uploaded_file($_FILES["abstractFile"]["tmp_name"], $targetFile)) {
        echo "The file " . htmlspecialchars(basename($_FILES["abstractFile"]["name"])) . " has been uploaded.";
        $isUploadSuccessful = true;
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Handle form data here (e.g., save to database) if $isUploadSuccessful is true
// Assuming $isUploadSuccessful is true and file upload is done
if ($isUploadSuccessful) {
    $csvFilePath = "submissions.csv";
    if (!file_exists($csvFilePath)) {
        // Create a new CSV file if it doesn't exist
        $fileHandle = fopen($csvFilePath, 'w');
        // Write the CSV headers
        fputcsv($fileHandle, ['id', 'name', 'email', 'phone', 'affiliation', 'title', 'filename', 'uploaded_at',]);
        fclose($fileHandle);
    }
    $formData = [
        'id' => $idSubmission, // Generates a unique ID for the submission
        'name' => $_POST['name'], // User's name from the form
        'email' => $_POST['email'], // User's email from the form
        'phone' => $_POST['phone'], // User's phone from the form
        'affiliation' => $_POST['affiliation'], // User's affiliation from the form
        'title' => $_POST['title'], // User's title from the form
        'filename' => $idFile, // Uploaded file name
        // Current date and time and timezone of the server
        'uploaded_at' => date('Y-m-d H:i:s')
    ];

    // Open the file in append mode, or create it if it doesn't exist
    $fileHandle = fopen($csvFilePath, 'a');
    // Write the form data to the CSV file
    fputcsv($fileHandle, $formData);
    // Close the file
    fclose($fileHandle);

    // Return a JSON response with the form data
    echo json_encode(['success' => true, 'message' => 'Submission recorded successfully.']);
} else {
    // On failure, you might want to return a structured error message
    http_response_code(400); // Optional: Set an HTTP response code indicating failure
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
}
