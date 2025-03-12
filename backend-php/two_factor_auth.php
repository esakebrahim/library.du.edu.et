<?php
require_once 'database.php';
require_once 'vendor/autoload.php'; // Make sure you have installed phpgangsta/googleauthenticator via composer

use PHPGangsta\GoogleAuthenticator;

class TwoFactorAuth {
    private $ga;
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->ga = new GoogleAuthenticator();
    }
    
    // Enable 2FA for a user
    public function enable2FA($userId) {
        // Generate secret key
        $secret = $this->ga->createSecret();
        
        // Store in database
        $query = "INSERT INTO two_factor_auth (user_id, secret_key, is_enabled, created_at)
                  VALUES (?, ?, 1, NOW())
                  ON DUPLICATE KEY UPDATE 
                  secret_key = VALUES(secret_key),
                  is_enabled = VALUES(is_enabled),
                  created_at = VALUES(created_at)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $userId, $secret);
        
        if ($stmt->execute()) {
            // Get user email for QR code
            $query = "SELECT email FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Generate QR code URL
            $qrCodeUrl = $this->ga->getQRCodeGoogleUrl(
                'Library System - ' . $user['email'],
                $secret
            );
            
            return [
                'success' => true,
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to enable 2FA'];
    }
    
    // Verify 2FA code
    public function verifyCode($userId, $code) {
        // Get user's secret key
        $query = "SELECT secret_key FROM two_factor_auth 
                  WHERE user_id = ? AND is_enabled = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Verify the code
            return [
                'success' => true,
                'valid' => $this->ga->verifyCode($row['secret_key'], $code, 2)
            ];
        }
        
        return ['success' => false, 'error' => '2FA not enabled for this user'];
    }
    
    // Disable 2FA
    public function disable2FA($userId) {
        $query = "UPDATE two_factor_auth 
                  SET is_enabled = 0 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        
        return [
            'success' => $stmt->execute(),
            'error' => $stmt->error
        ];
    }
    
    // Check if 2FA is enabled
    public function is2FAEnabled($userId) {
        $query = "SELECT is_enabled 
                  FROM two_factor_auth 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return ['success' => true, 'enabled' => (bool)$row['is_enabled']];
        }
        
        return ['success' => true, 'enabled' => false];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $tfa = new TwoFactorAuth();
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'enable':
                if (isset($data['user_id'])) {
                    echo json_encode($tfa->enable2FA($data['user_id']));
                }
                break;
                
            case 'verify':
                if (isset($data['user_id'], $data['code'])) {
                    echo json_encode($tfa->verifyCode($data['user_id'], $data['code']));
                }
                break;
                
            case 'disable':
                if (isset($data['user_id'])) {
                    echo json_encode($tfa->disable2FA($data['user_id']));
                }
                break;
                
            case 'status':
                if (isset($data['user_id'])) {
                    echo json_encode($tfa->is2FAEnabled($data['user_id']));
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 