<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <title>Shyam Future Tech</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
        }
       
        .table-container {
            max-height: 400px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <h3 style ="text-align: center;">Web Application For Shyam Future Tech</h3>
    <br>
    </br>

    <?php


    // Function to save uploaded file
    function saveUploadedFile($file) {
        $targetDirectory = 'uploads/';
        $targetFile = $targetDirectory . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $targetFile;
        }
        return false;
    }


    // Function to generate a unique ID
    function generateID() {
        $filename = 'form_data.txt';
        if (file_exists($filename)) {
            $fileData = file($filename);
            $formData = [];
            $lastID=0;
            foreach ($fileData as $line) {
                $entry = unserialize(trim($line));
                $lastID = $entry['id'];
            }
            return $lastID+1;
        }
    }

    // Function to save form data to a file
    function saveFormData($data) {
        $filename = 'form_data.txt';
        $serializedData = serialize($data);
        file_put_contents($filename, $serializedData . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    // Function to read form data from the file
    function readFormData() {
        $filename = 'form_data.txt';
        if (file_exists($filename)) {
            $fileData = file($filename);
            $formData = [];
            foreach ($fileData as $line) {
                $entry = unserialize(trim($line));
                $entry['file'] = htmlspecialchars($entry['file']); // Prevent HTML injection
                $formData[] = $entry;
            }
            return $formData;
        }
        return [];
    }


    // Function to update form data in the file
    function updateFormData($formData) {
        $filename = 'form_data.txt';
        $serializedData = '';
        foreach ($formData as $entry) {
            $serializedData .= serialize($entry) . PHP_EOL;
        }
        file_put_contents($filename, $serializedData);
    }


    // Function to delete a form entry
    function deleteFormEntry($id) {
        $formData = readFormData();
        foreach ($formData as $index => $entry) {
            if ($entry['id'] === (int)$id) {
                unset($formData[$index]);
                updateFormData($formData);
                break;
            }
        }
    }

    // Add / Edit activity
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['id']) && $_POST['id']!=''){
            $id=base64_decode($_GET['id']);
            $formData = readFormData();
            $dataSet = [];
            foreach ($formData as $index => $entry) {
                deleteFormEntry($entry['id']);
                if ($entry['id'] === (int)$id) {
                    if(isset($_FILES) && $_FILES['file']['name']!=''){
                         $uploadedFile = saveUploadedFile($_FILES['file']);
                    }else{
                        $uploadedFile = $entry['file'];
                    }
                    $entry = [
                        'id' => (int)$id,
                        'name' => $_POST['name'],
                        'file' => $uploadedFile,
                        'address' => $_POST['address'],
                        'gender' => $_POST['gender']
                    ];
                    saveFormData($entry);
                    
                }else{
                    saveFormData($entry);
                }
            }
            echo "<b>Form data updated successfully!</b>";
        }else{

            // Get form values
            $name = $_POST['name'];
            $address = $_POST['address'];
            $gender = $_POST['gender'];
            $id = generateID();
            // Save the uploaded file
            if(isset($_FILES) && $_FILES['file']['name']!='')
            $uploadedFile = saveUploadedFile($_FILES['file']);
            else
            $uploadedFile = "";

            // Create an entry with the submitted data
            $entry = [
                'id' => (int)$id,
                'name' => $name,
                'file' => $uploadedFile,
                'address' => $address,
                'gender' => $gender
            ];
            // Save the form data
            saveFormData($entry);
            echo "<b>Form data submitted successfully!</b>";
        } 
    }else{
        // Handle edit and delete actions
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            $id = base64_decode($_GET['id']);
            if ($action === 'edit') {
                $formData = readFormData();
                foreach ($formData as $entry) {
                    if ($entry['id'] === (int)$id) {
                        $editEntry = $entry;
                        break;
                    }
                }
            } elseif ($action === 'delete') {
                deleteFormEntry($id);
                echo "<b>Data deleted successfully!</b>";
            }
        }

    }

    




    // Read form  after all the actions
    $formData = readFormData();
    // Sort the form data by Name or ID
    $sortBy = isset($_GET['sort']) ? $_GET['sort'] : '';
    if ($sortBy === 'name') {
        usort($formData, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    } else {
        usort($formData, function ($a, $b) {
            return $a['id'] - $b['id'];
        });
    }
    ?>

    <!-- Add From and edit from -->
    <?php if (isset($editEntry)){ ?>
        <h4>Edit Form Data</h4>
        <div id="editImagePreview">
                <img class="preview-image" src="<?php echo $editEntry['file']; ?>">
            </div>
        <form  class="form-inline" id="editForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo base64_encode($editEntry['id']); ?>">
            <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" name="name" value="<?php echo $editEntry['name']; ?>" required>
            </div>
            <div class="form-group">
            <label for="name">Address:</label>
            <textarea name="address" class="form-control"  required><?php echo $editEntry['address']; ?></textarea>
            </div>
            <div class="form-group">
            <label for="gender">Gender:</label>
            <select name="gender" class="form-control" id="gender">
                <option  value="Male" <?php if($editEntry['gender'] == "Male"){ echo "selected";}?>>Male</option>
                <option value="Female" <?php if($editEntry['gender'] == "Female"){ echo "selected";}?>>Female</option>
            </select>
           </div>
            <div class="form-group">
            <label for="file">Upload Image:</label>
            <input type="file"  class="form-control" name="file" accept="image/*" onchange="handleImagePreview(event)">
             </div>
            <button type="submit" class="form-control">Save</button>
        </form>
    <?php }else{ ?>
        <div id="imagePreview"></div>
        <form class="form-inline" id="myForm" method="POST" enctype="multipart/form-data">
        <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" name="name" placeholder="Enter Name" required>
        </div>
        <div class="form-group">
        <label for="name">Address:</label>
        <textarea name="address" class="form-control" placeholder="Enter Address" required></textarea>
        </div>
        <div class="form-group">
        <label for="gender">Gender:</label>
        <select name="gender" class="form-control"  id="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        </div>
        <div class="form-group">
        <label for="file">Upload Image:</label>
        <input type="file" class="form-control" name="file" accept="image/*" onchange="handleImagePreview(event)">
        </div>
        <button type="submit" class="form-control">Submit</button>
    </form>
   <?php } ?>


   <br>
    <!-- Listing Data -->
    <h5>All Submitted Data:</h5>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a  href="?sort=id">ID</a></th>
                    <th><a href="?sort=name">Name</a></th>
                    <th>Image</th>
                    <th>Address</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formData as $entry) : ?>
                    <tr>
                        <td><?php echo $entry['id']; ?></td>
                        <td><?php echo $entry['name']; ?></td>
                        <td><?php if(isset($entry['file']) && $entry['file']!=''){ ?><a href="<?php echo $entry['file']; ?>" download><img class="preview-image"  src="<?php echo $entry['file']; ?>" height="50" width="50"></a><?php } ?></td>
                        <td><?php echo $entry['address']; ?></td>
                        <td><?php echo $entry['gender']; ?></td>
                        <td class="action-buttons">
                            <a href="?action=edit&id=<?php echo base64_encode($entry['id']); ?>">Edit</a>
                            <?php  if(isset($entry['file']) && $entry['file']!=''){ ?><a href="<?php echo $entry['file']; ?>" target="_blank">View</a><?php } ?>
                            <a href="?action=delete&id=<?php echo base64_encode($entry['id']); ?>" style="color:red">Delete</a>
                           
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    
    <!-- script Start -->
    <script>
        function handleImagePreview(event) {
            var fileInput = event.target;
            var imagePreview = document.getElementById('imagePreview');
            var editImagePreview = document.getElementById('editImagePreview');

            if (fileInput.files.length > 0) {
                for (var i = 0; i < fileInput.files.length; i++) {
                    var file = fileInput.files[i];
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var image = document.createElement('img');
                        image.classList.add('preview-image');
                        image.src = event.target.result;
                        imagePreview.appendChild(image);
                        if(editImagePreview!=null)
                        editImagePreview.innerHTML = ''; // Clear the edit preview
                        
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    </script>
</body>
</html>
