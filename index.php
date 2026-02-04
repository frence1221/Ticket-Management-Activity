<?php

session_start();

$errors = [
    'login' => $_SESSION["login_error"] ?? '',
    'register' => $_SESSION["registration_errors"] ?? ''
];

$activeForm = $_SESSION["active_form"] ?? 'login';

session_unset();

function showError($error)
{
    return !empty($error) ? "<div class=\"error-message\">$error</div>" : '';
}

function isActive($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <div class="container">
        <div class="form-box active" id="login-form">
            <form action="process_register.php" method="post">
                <h2>Login</h2>
                <?php if (!empty($errors['login'])): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($errors['login']); ?>
                    </div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an Account?<a href="#" onclick="showForm('registration-form')"> Register here</a></p>
            </form>
        </div>
    </div>

    <div class="form-box" id="registration-form">
        <?php if (!empty($_SESSION['registration_errors'])): ?>
            <div class="error-message" style="color: red; margin-bottom: 15px;">
                <ul>
                    <?php foreach ($_SESSION['registration_errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['registration_errors']); ?>
        <?php endif; ?>
        <form action="process_register.php" method="post">
            <h2>Register</h2>
            <input type="text" name="LastName" placeholder="Last Name" required>
            <input type="text" name="FirstName" placeholder="First Name" required>
            <input type="text" name="MiddleName" placeholder="Middle Name" required>
            <input type="date" id="birthdate" name="birthdate">
        </form>

        <div class="form-group gender-group">
            <p class="label">Please select your gender:</p>
            <div class="radio-group">
                <label class="radio-item"><input type="radio" name="gender" value="male" required> Male</label>
                <label class="radio-item"><input type="radio" name="gender" value="female" required> Female</label>
            </div>
        </div>

        <div class="form-group phone-group">
            <label class="label">Phone</label>
            <div class="phone-input">
                <span class="country-code">+63</span>
                <input type="tel" name="phone" pattern="[0-9]{10}" placeholder="9171234567" required>
            </div>
        </div>

        <input type="email" name="email" placeholder="Email" required>
        <button type="button" id="nextBtn">Next</button>
        <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login here</a></p>
        </form>
    </div>

    <div id="passwordModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:#fff; padding:30px; border-radius:8px; min-width:300px; position:relative;">
            <h3 style="margin-bottom: 15px;">Set Your Password</h3>
            <?php if (!empty($_SESSION['registration_errors'])): ?>
                <div style="color: red; margin-bottom: 15px;">
                    <ul>
                        <?php foreach ($_SESSION['registration_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['registration_errors']); ?>
            <?php endif; ?>
            <form id="passwordForm" action="process_register.php" method="post">

                <input type="hidden" name="LastName">
                <input type="hidden" name="FirstName">
                <input type="hidden" name="MiddleName">
                <input type="hidden" name="birthdate">
                <input type="hidden" name="gender">
                <input type="hidden" name="phone">
                <input type="hidden" name="email">
                <input type="password" id="password" name="EnterPassword" placeholder="Enter Password" required>
                <input type="password" id="confirmPassword" name="ConfirmPassword" placeholder="Confirm Password" required>
                <button type="submit" name="register">Register</button>
                <button type="button" id="closeModal">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('nextBtn').onclick = function(e) {
            e.preventDefault();

            var form = document.querySelector('#registration-form form');
            var modal = document.getElementById('passwordModal');

            ['LastName', 'FirstName', 'MiddleName', 'birthdate', 'gender', 'phone', 'email'].forEach(function(name) {
                var value = '';
                if (name === 'gender') {
                    var genderInput = form.querySelector('input[name="gender"]:checked');
                    if (genderInput) value = genderInput.value;
                } else {
                    var input = form.querySelector('[name="' + name + '"]');
                    if (input) value = input.value;
                }
                var hidden = modal.querySelector('input[name="' + name + '"]');
                if (hidden) hidden.value = value;
            });
            modal.style.display = 'flex';
        };

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('passwordModal').style.display = 'none';
        };

        document.getElementById('passwordModal').onclick = function(e) {
            if (e.target === this) this.style.display = 'none';
        };
    </script>

    <script>
        function showForm(formId) {
            document.querySelectorAll('.form-box').forEach(form => form.classList.remove('active'));
            document.getElementById(formId).classList.add('active');
        }
    </script>

    <script>
        document.getElementById('passwordForm').onsubmit = function() {
            alert('Modal registration form is being submitted!');
        }
    </script>
</body>

</html>