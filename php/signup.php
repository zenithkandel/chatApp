<?php
    session_start();
    include_once "config.php";
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    if(!empty($fname) && !empty($lname) && !empty($email) && !empty($password)){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
            if(mysqli_num_rows($sql) > 0){
                echo "$email - This email already exist!";
            }else{
                if(isset($_FILES['image'])){
                    $img_name = $_FILES['image']['name'];
                    $img_type = $_FILES['image']['type'];
                    $tmp_name = $_FILES['image']['tmp_name'];

                    // Normalize and validate extension (case-insensitive)
                    $base_name = basename($img_name);
                    $ext = strtolower(pathinfo($base_name, PATHINFO_EXTENSION));
                    $extensions = ["jpeg", "png", "jpg"];
                    if(in_array($ext, $extensions) === true){
                        $types = ["image/jpeg", "image/jpg", "image/png"];
                        if(in_array($img_type, $types) === true){
                            // Ensure upload directory exists (php/images relative to this file)
                            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
                            if(!is_dir($uploadDir)){
                                @mkdir($uploadDir, 0777, true);
                            }

                            $time = time();
                            // Sanitize filename and prefix with timestamp
                            $nameOnly = pathinfo($base_name, PATHINFO_FILENAME);
                            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $nameOnly);
                            $new_img_name = $time . $safeName . '.' . $ext;

                            if(move_uploaded_file($tmp_name, $uploadDir . $new_img_name)){
                                // Use a safe random id range for unique_id
                                $ran_id = mt_rand(100000000, 999999999);
                                $status = "Active now";
                                $encrypt_pass = md5($password);
                                $insert_query = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, email, password, img, status)
                                VALUES ({$ran_id}, '{$fname}','{$lname}', '{$email}', '{$encrypt_pass}', '{$new_img_name}', '{$status}')");
                                if($insert_query){
                                    $select_sql2 = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
                                    if(mysqli_num_rows($select_sql2) > 0){
                                        $result = mysqli_fetch_assoc($select_sql2);
                                        $_SESSION['unique_id'] = $result['unique_id'];
                                        echo "success";
                                    }else{
                                        echo "This email address not Exist!";
                                    }
                                }else{
                                    echo "Something went wrong. Please try again!";
                                }
                            } else {
                                echo "Failed to save uploaded image. Please try again.";
                            }
                        }else{
                            echo "Please upload an image file - jpeg, png, jpg";
                        }
                    }else{
                        echo "Please upload an image file - jpeg, png, jpg";
                    }
                }
            }
        }else{
            echo "$email is not a valid email!";
        }
    }else{
        echo "All input fields are required!";
    }
?>