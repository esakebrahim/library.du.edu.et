<?php
session_start();
require_once 'database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    $email = $_SESSION['pending_email'] ?? '';
    $verification_code = trim($_POST['code']);

    if (empty($email)) {
        $response['message'] = 'Session expired. Please try logging in again.';
        $response['redirect'] = '../frontend/login.html';
        echo json_encode($response);
        exit();
    }

    // Verify the code
    $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['verification_code'] != $verification_code) {
        $response['message'] = 'Invalid verification code';
        echo json_encode($response);
        exit();
    }

    // Update user verification status
    $stmt = $conn->prepare("UPDATE users SET is_confirmed = 1, verification_code = NULL WHERE email = ?");
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        unset($_SESSION['pending_email']);
        $response['success'] = true;
        $response['message'] = 'Email verified successfully!';
        $response['redirect'] = '../frontend/login.html';
        echo json_encode($response);
        exit();
    } else {
        $response['message'] = 'Verification failed. Please try again.';
        echo json_encode($response);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6366f1, #818cf8);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 380px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        h2 {
            color: #1f2937;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .message {
            padding: 12px;
            border-radius: 1rem;
            margin-bottom: 20px;
            display: none;
            animation: fadeIn 0.3s ease;
            backdrop-filter: blur(8px);
            border: 1px solid;
            transition: all 0.3s ease;
        }

        .error-message {
            background: rgba(254, 226, 226, 0.9);
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.3);
        }

        .success-message {
            background: rgba(220, 252, 231, 0.9);
            color: #059669;
            border-color: rgba(5, 150, 105, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            color: #374151;
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1rem;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        input:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 1rem;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(99, 102, 241, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .description {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
        }

        #email-display {
            color: #6366f1;
            font-weight: 600;
        }

        .back-to-login {
            margin-top: 20px;
            font-size: 14px;
        }

        .back-to-login a {
            color: #6366f1;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #818cf8;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Your Email</h2>
        
        <div id="error-message" class="message error-message"></div>
        <div id="success-message" class="message success-message"></div>
        
        <p class="description">
            We've sent a verification code to<br>
            <span id="email-display"><?php echo isset($_SESSION['pending_email']) ? htmlspecialchars($_SESSION['pending_email']) : ''; ?></span>
        </p>

        <form id="verificationForm">
            <div class="input-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" required maxlength="6" placeholder="Enter 6-digit code">
            </div>

            <button type="submit" class="btn">Verify Email</button>
        </form>

        <div class="back-to-login">
            <a href="../frontend/login.html">Back to Login</a>
        </div>
    </div>

    <script>
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('code').value;
            
            fetch('verify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'code=' + encodeURIComponent(code)
            })
            .then(response => response.json())
            .then(data => {
                const errorDiv = document.getElementById('error-message');
                const successDiv = document.getElementById('success-message');
                
                // Hide both messages
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                }
            })
            .catch(error => {
                const errorDiv = document.getElementById('error-message');
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>