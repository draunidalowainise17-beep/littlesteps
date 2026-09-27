<?php
    include_once "../config/dbconnect.php";
    
    if(isset($_POST['upload']))
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password
        $qualification = $_POST['qualification'];
        $experience = $_POST['experience'];
        $special_training = $_POST['special_training'] ?? null; // Handle null value
        $profile_image = $_POST['profile_image'] ?? null; // Handle null value

        $insert = mysqli_query($conn, "INSERT INTO caregivers
            (name, email, password, qualification, experience, special_training, profile_image)
            VALUES ('$name', '$email', '$password', '$qualification', '$experience', '$special_training', '$profile_image')");

        if (!$insert) {
            echo mysqli_error($conn);
            header("Location: ../dashboard.php?caregiver=error");
        } else {
            echo "Caregiver added successfully.";
            header("Location: ../dashboard.php?caregiver=success");
        }
    }
?>
