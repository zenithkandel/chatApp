<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include_once "config.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';

        // Handle optional image upload
        $imageFile = null;
        if(isset($_FILES['attachment']) && is_uploaded_file($_FILES['attachment']['tmp_name'])){
            $img_name = $_FILES['attachment']['name'];
            $img_type = $_FILES['attachment']['type'];
            $tmp_name = $_FILES['attachment']['tmp_name'];

            $allowedExt = ["jpeg","jpg","png","gif","webp"];
            $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
            $allowedTypes = ["image/jpeg","image/jpg","image/png","image/gif","image/webp"];
            if(in_array($ext, $allowedExt) && in_array($img_type, $allowedTypes)){
                $dir = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR;
                if(!is_dir($dir)){
                    @mkdir($dir, 0777, true);
                }
                $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($img_name, PATHINFO_FILENAME));
                $newName = time() . '_' . $safeBase . '.' . $ext;
                if(move_uploaded_file($tmp_name, $dir . $newName)){
                    $imageFile = $newName;
                }
            }
        }

        // Insert if we have either text or an image
        if($message !== '' || $imageFile !== null){
            $columns = "incoming_msg_id, outgoing_msg_id, msg, image";
            $values = "{$incoming_id}, {$outgoing_id}, '" . mysqli_real_escape_string($conn, $message) . "', " . ($imageFile ? "'{$imageFile}'" : "NULL");
            $sql = mysqli_query($conn, "INSERT INTO messages ($columns) VALUES ($values)") or die();
        }
    }else{
        header("location: ../login.php");
    }
?>