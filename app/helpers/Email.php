<?php
/**
 * Email Helper Class
 * Handles email sending functionality
 */

class Email {
    
    /**
     * Send email using PHP mail() or SMTP
     */
    public static function send($to, $subject, $message, $headers = []) {
        $defaultHeaders = [
            'From' => SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            'Reply-To' => SMTP_FROM_EMAIL,
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        
        return mail($to, $subject, $message, $headerString);
    }
    
    /**
     * Send welcome email
     */
    public static function sendWelcomeEmail($userEmail, $userName) {
        $subject = 'Welcome to ' . SITE_NAME;
        $message = self::getWelcomeEmailTemplate($userName);
        
        return self::send($userEmail, $subject, $message);
    }
    
    /**
     * Send order confirmation email
     */
    public static function sendOrderConfirmation($userEmail, $orderNumber, $orderDetails) {
        $subject = 'Order Confirmation - ' . $orderNumber;
        $message = self::getOrderConfirmationTemplate($orderNumber, $orderDetails);
        
        return self::send($userEmail, $subject, $message);
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordReset($userEmail, $resetToken) {
        $subject = 'Password Reset Request';
        $resetLink = SITE_URL . '/user/reset-password?token=' . $resetToken;
        $message = self::getPasswordResetTemplate($resetLink);
        
        return self::send($userEmail, $subject, $message);
    }
    
    /**
     * Welcome email template
     */
    private static function getWelcomeEmailTemplate($userName) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Welcome</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #4CAF50;">Welcome to ' . SITE_NAME . '!</h1>
                <p>Hi ' . htmlspecialchars($userName) . ',</p>
                <p>Thank you for joining us. We\'re excited to have you as part of our community!</p>
                <p>Start shopping for the best kids\' fashion at amazing prices.</p>
                <p><a href="' . SITE_URL . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Start Shopping</a></p>
                <p>Best regards,<br>The ' . SITE_NAME . ' Team</p>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Order confirmation email template
     */
    private static function getOrderConfirmationTemplate($orderNumber, $orderDetails) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Order Confirmation</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #4CAF50;">Order Confirmed!</h1>
                <p>Thank you for your order. Your order number is: <strong>' . htmlspecialchars($orderNumber) . '</strong></p>
                <p>We\'ll send you another email when your order ships.</p>
                <p><a href="' . SITE_URL . '/user/orders" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Track Your Order</a></p>
                <p>Best regards,<br>The ' . SITE_NAME . ' Team</p>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Password reset email template
     */
    private static function getPasswordResetTemplate($resetLink) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Password Reset</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #4CAF50;">Password Reset Request</h1>
                <p>You requested to reset your password. Click the link below to reset it:</p>
                <p><a href="' . $resetLink . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                <p>If you didn\'t request this, please ignore this email.</p>
                <p>Best regards,<br>The ' . SITE_NAME . ' Team</p>
            </div>
        </body>
        </html>
        ';
    }
}

