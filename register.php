<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $message = trim($_POST["message"] ?? "");

    $errors = [];


    // Name validation
    if (!preg_match("/^[A-Za-z ]{2,}$/", $name)) {
        $errors[] = "Enter a valid name.";
    }


    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email.";
    }


    // Mobile validation
    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Enter a valid 10 digit mobile number.";
    }


    // Course validation
    if ($course == "") {
        $errors[] = "Please select a course.";
    }


    // Year validation
    if ($year == "") {
        $errors[] = "Please select your year.";
    }


    // Gender validation
    if ($gender == "") {
        $errors[] = "Please select your gender.";
    }


    if (count($errors) > 0) {

        echo "<h2>Registration Failed</h2>";

        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }

        echo "<a href='registration.html'>Go Back</a>";

    } else {

        $file = "registrations.csv";

        $newFile = !file_exists($file);

        $handle = fopen($file, "a");

        if ($newFile) {
            fputcsv($handle, [
                "Name",
                "Email",
                "Mobile",
                "Course",
                "Year",
                "Gender",
                "Message"
            ]);
        }

        fputcsv($handle, [
            $name,
            $email,
            $mobile,
            $course,
            $year,
            $gender,
            $message
        ]);

        fclose($handle);


        echo "<h2>Registration Successful!</h2>";
        echo "<p>Your information has been saved successfully.</p>";
        echo "<a href='registration.html'>Back to Registration</a>";
    }

} else {

    echo "Invalid request.";

}

?>