<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pure Serenity Spa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

</head>
<style>
.upload-circle {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px dashed #ccc;
  position: relative;
  cursor: pointer;
  background-color: #f8f8f8;
  margin: auto;
}

.upload-circle img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: none;
}

.upload-circle::before {

  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #888;
  font-size: 14px;
  text-align: center;
}

#imgprofile {
  display: none;
}
</style>
<body>
    <!-- Include Navigation -->
    <!-- <div id="navbar-placeholder"></div> -->

    <!-- Authentication Section -->
    <section class="auth-section">

            <div class="auth-container">
                <div class="row g-0">

                    <div class="col-lg-12">
                        <div class="auth-right">
                            <!-- Tab Navigation -->
                            <div class="auth-tabs">
                                <div class="auth-tab active" onclick="switchTab('login')">Login</div>
                                <div class="auth-tab" onclick="switchTab('signup')">Sign Up</div>
                            </div>

                           <!-- Login Form -->
<form id="loginForm" class="auth-form active" action="check_login.php" method="post">
    <div id="loginAlert"></div>

    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="username" placeholder="Enter your email" required>

    </div>

    <div class="form-group" style="position: relative;">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
        <span class="password-toggle" onclick="togglePassword(this.previousElementSibling.id)">
            <i class="fas fa-eye"></i>
        </span>
    </div>

    <div class="form-check">
        <input type="checkbox" class="form-check-input" name="remember" id="rememberMe">
        <label class="form-check-label" for="rememberMe">
            Remember me
        </label>
    </div>

    <button type="submit" class="btn-auth">
        Sign In
    </button>

    <!-- <div class="forgot-password">
        <a href="#" onclick="showForgotPassword()">Forgot your password?</a>
    </div> -->
</form>


                            <!-- Signup Form -->
                            <form id="signupForm" class="auth-form" action="insert_customer.php" method="POST" enctype="multipart/form-data" >
                                <div id="signupAlert"></div>
                                

<div class="col-md-12 text-center mb-3">
  <label for="imgprofile" class="form-label d-block">Profile Image</label>
  <div class="upload-circle" id="uploadCircle">
    <img id="previewImage" alt="Profile Preview" />
  </div>
  <input type="file" id="imgprofile" name="imgprofile" accept="image/*">
</div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="form-label">Name</label>
                                            <input type="text" class="form-control" id="floatingName" name="floatingName" placeholder="Full name">
                                        </div>
                                    </div>

                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Gmail</label>
                                    <input type="email" class="form-control" id="floatingEmail" name="floatingEmail" placeholder="Your email">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Phone number</label>
                                    <input type="text" class="form-control" id="floatingPhone" name="floatingPhone" placeholder="Phone number">
                                </div>

<div class="form-group">
  <label class="form-label" for="floatinggender">Gender</label>
  <select class="form-control" id="floatinggender" name="floatinggender">
    <option value="">-- Select Gender --</option>
    <option value="male">Male</option>
    <option value="female">Female</option>
    <option value="other">Other</option>
  </select>
</div>

                                
                                <div class="form-group">
                                    <label class="form-label">Birthday</label>
                                    <input type="date" class="form-control" id="floatingDate" name="floatingDate" placeholder="Birthday">
                                </div>                                
                                
                                <div class="form-group" style="position: relative;">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" id="floatingPassword" name="floatingPassword" placeholder="Password">
                                    <span class="password-toggle" onclick="togglePassword('signupPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                
                                <!-- <div class="form-group" style="position: relative;">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="floatingPassword" name="floatingPassword" placeholder="Confirm Password">
                                    <span class="password-toggle" onclick="togglePassword('confirmPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div> -->
                                
                                <!-- <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                    <label class="form-check-label" for="agreeTerms">
                                        I agree to the <a href="#" style="color: var(--luxury-gold);">Terms of Service</a> and <a href="#" style="color: var(--luxury-gold);">Privacy Policy</a>.
                                    </label>
                                </div> -->
                                
                                <button type="submit" class="btn-auth">
                                    Sign Up
                                </button>
                            </form>

                            <!-- Social Login -->
                            <!-- <div class="divider">
                                <span>Or sign in with</span>
                            </div>
                             -->
                            <!-- <div class="social-login">
                                <a href="#" class="btn-social" onclick="socialLogin('google')">
                                    <i class="fab fa-google"></i>
                                    Google
                                </a>
                                <a href="#" class="btn-social" onclick="socialLogin('facebook')">
                                    <i class="fab fa-facebook-f"></i>
                                    Facebook
                                </a>
                                <a href="#" class="btn-social" onclick="socialLogin('line')">
                                    <i class="fab fa-line"></i>
                                    LINE
                                </a>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <!-- <div id="footer-placeholder"></div> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>
    <script>
        // Tab Switching
        function switchTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`.auth-tab:nth-child(${tab === 'login' ? '1' : '2'})`).classList.add('active');
            
            // Update forms
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.getElementById(tab === 'login' ? 'loginForm' : 'signupForm').classList.add('active');
            
            // Clear alerts
            document.getElementById('loginAlert').innerHTML = '';
            document.getElementById('signupAlert').innerHTML = '';
        }

        // Password Toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Login Handler
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const alertDiv = document.getElementById('loginAlert');
    const submitBtn = event.target.querySelector('button[type="submit"]');

    if (!email || !password) {
        showAlert(alertDiv, 'error', 'Please complete all required fields.');
        return;
    }

    submitBtn.classList.add('btn-loading');

    fetch('check_login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `username=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.classList.remove('btn-loading');
        if (data.success) {
            showAlert(alertDiv, 'success', data.message);
            setTimeout(() => {
                if (data.level == 1) {
                    window.location.href = 'index-user.php';
                } else if (data.level == 9) {
                    window.location.href = 'index-admin.php';
                } else {
                    showAlert(alertDiv, 'error', 'You do not have permission to access this system.');
                }
            }, 1000);
        } else {
            showAlert(alertDiv, 'error', data.message);
        }
    })
    .catch(error => {
        submitBtn.classList.remove('btn-loading');
        showAlert(alertDiv, 'error', 'A server connection error occurred.');
        console.error(error);
    });
}


        // Signup Handler
        function handleSignup(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('signupEmail').value.trim();
            const phone = document.getElementById('signupPhone').value.trim();
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const agreeTerms = document.getElementById('agreeTerms').checked;
            const alertDiv = document.getElementById('signupAlert');
            const submitBtn = event.target.querySelector('button[type="submit"]');
            
            // Validation
            if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
                showAlert(alertDiv, 'error', 'Please complete all required fields.');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert(alertDiv, 'error', 'Passwords do not match.');
                return;
            }
            
            if (password.length < 8) {
                showAlert(alertDiv, 'error', 'Password must be at least 8 characters long.');
                return;
            }
            
            const phoneRegex = /^[0-9]{9,10}$/;
            if (!phoneRegex.test(phone)) {
                showAlert(alertDiv, 'error', 'Invalid phone number.');
                return;
            }
            
            if (!agreeTerms) {
                showAlert(alertDiv, 'error', 'Please accept the terms of service.');
                return;
            }
            
            // Show loading
            submitBtn.classList.add('btn-loading');
            
            // Simulate API call
            setTimeout(() => {
                showAlert(alertDiv, 'success', 'Registration successful! Redirecting you to the homepage...');
                
                // Store user session
                const userData = {
                    email: email,
                    name: `${firstName} ${lastName}`,
                    phone: phone,
                    registerTime: new Date().toISOString()
                };
                
                window.currentUser = userData;
                
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
                
                submitBtn.classList.remove('btn-loading');
            }, 2000);
        }

        // Social Login
        function socialLogin(provider) {
            alert(`Sign in with ${provider.charAt(0).toUpperCase() + provider.slice(1)} (Demo Mode)`);
            
            // For demo purposes
            const userData = {
                email: `user@${provider}.com`,
                name: `${provider} User`,
                loginTime: new Date().toISOString(),
                provider: provider
            };
            
            window.currentUser = userData;
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        }

        // Show Alert
        function showAlert(container, type, message) {
            container.innerHTML = `
                <div class="alert-custom alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
            `;
        }

        // Forgot Password
        function showForgotPassword() {
            const email = prompt('Please enter the email used to register:');
            if (email) {
                alert('A password reset link has been sent to your email.');
            }
        }

        // Load navbar and footer
        document.addEventListener('DOMContentLoaded', function() {
            fetch('navbar.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('navbar-placeholder').innerHTML = data;
                })
                .catch(error => console.error('Error loading navbar:', error));
            
            fetch('footer.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('footer-placeholder').innerHTML = data;
                })
                .catch(error => console.error('Error loading footer:', error));
        });
    </script>
    <script>
const uploadCircle = document.getElementById('uploadCircle');
const imgInput = document.getElementById('imgprofile');
const previewImage = document.getElementById('previewImage');

uploadCircle.addEventListener('click', () => {
  imgInput.click();
});

imgInput.addEventListener('change', function () {
  const file = this.files[0];
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImage.src = e.target.result;
      previewImage.style.display = 'block';
      uploadCircle.classList.add('has-image');
      uploadCircle.style.border = 'none';
      uploadCircle.style.backgroundColor = 'transparent';
      uploadCircle.style.justifyContent = 'center';
      uploadCircle.style.alignItems = 'center';
      uploadCircle.style.textAlign = 'center';
      uploadCircle.style.color = 'transparent';
      uploadCircle.style.fontSize = '0';
      uploadCircle.style.lineHeight = '0';
    };
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>