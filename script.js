document.addEventListener("DOMContentLoaded", function () {

    let themeBtn = document.getElementById("themeBtn");

    themeBtn.addEventListener("click", function () {

        document.body.classList.toggle("dark");

        if (document.body.classList.contains("dark")) {
            themeBtn.textContent = "☀️ Light Mode";
            localStorage.setItem("theme", "dark");
        } 
        else {
            themeBtn.textContent = "🌙 Dark Mode";
            localStorage.setItem("theme", "light");
        }

    });


    // Load saved theme
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark");
        themeBtn.textContent = "☀️ Light Mode";
    }

});
// Registration Form Validation

let form = document.getElementById("registrationForm");

if (form) {

    form.addEventListener("submit", function(event) {

        event.preventDefault();

        let name = document.getElementById("name").value.trim();
        let email = document.getElementById("email").value.trim();
        let mobile = document.getElementById("mobile").value.trim();
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirmPassword").value;
        let course = document.getElementById("course").value;
        let year = document.getElementById("year").value;
        let terms = document.getElementById("terms").checked;

        let gender = document.querySelector('input[name="gender"]:checked');

        let valid = true;


        // Name validation
        if (!/^[A-Za-z ]{2,}$/.test(name)) {
            document.getElementById("nameError").textContent =
                "Please enter a valid name.";
            valid = false;
        } else {
            document.getElementById("nameError").textContent = "";
        }


        // Email validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById("emailError").textContent =
                "Please enter a valid email.";
            valid = false;
        } else {
            document.getElementById("emailError").textContent = "";
        }


        // Mobile validation
        if (!/^[0-9]{10}$/.test(mobile)) {
            document.getElementById("mobileError").textContent =
                "Mobile number must contain 10 digits.";
            valid = false;
        } else {
            document.getElementById("mobileError").textContent = "";
        }


        // Password validation
        if (!/^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(password)) {
            document.getElementById("passwordError").textContent =
                "Password must be at least 8 characters and contain a letter and number.";
            valid = false;
        } else {
            document.getElementById("passwordError").textContent = "";
        }


        // Confirm password
        if (password !== confirmPassword || confirmPassword === "") {
            document.getElementById("confirmPasswordError").textContent =
                "Passwords do not match.";
            valid = false;
        } else {
            document.getElementById("confirmPasswordError").textContent = "";
        }


        // Course
        if (course === "") {
            document.getElementById("courseError").textContent =
                "Please select a course.";
            valid = false;
        } else {
            document.getElementById("courseError").textContent = "";
        }


        // Year
        if (year === "") {
            document.getElementById("yearError").textContent =
                "Please select your year.";
            valid = false;
        } else {
            document.getElementById("yearError").textContent = "";
        }


        // Gender
        if (!gender) {
            document.getElementById("genderError").textContent =
                "Please select your gender.";
            valid = false;
        } else {
            document.getElementById("genderError").textContent = "";
        }


        // Terms
        if (!terms) {
            document.getElementById("termsError").textContent =
                "Please accept the terms and conditions.";
            valid = false;
        } else {
            document.getElementById("termsError").textContent = "";
        }


        // Final result
        if (valid) {
            alert("Registration successful!");
            form.reset();
            document.getElementById("strength").textContent = "";
        }

    });


    // Password Strength
    document.getElementById("password").addEventListener("input", function() {

        let password = this.value;
        let strength = document.getElementById("strength");

        if (password.length < 6) {
            strength.textContent = "Weak Password";
        } else if (password.length < 8) {
            strength.textContent = "Medium Password";
        } else {
            strength.textContent = "Strong Password";
        }

    });

}