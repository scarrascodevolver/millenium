<?php
// Simulación básica de PHPMailer para desarrollo local
class PHPMailer {
    public $Host;
    public $SMTPAuth;
    public $Username;
    public $Password;
    public $SMTPSecure;
    public $Port;
    public $CharSet;
    public $Subject;
    public $Body;
    public $AltBody;
    public $ErrorInfo;
    
    const ENCRYPTION_STARTTLS = 'tls';
    
    public function __construct($exceptions = null) {}
    
    public function isSMTP() {}
    
    public function setFrom($email, $name = '') {}
    
    public function addAddress($email, $name = '') {}
    
    public function isHTML($bool) {}
    
    public function send() {
        // Simulación de envío exitoso para desarrollo
        return true;
    }
}
?>