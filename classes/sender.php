<?php
/**
 * Bulk email Sender.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

class Sender
{
    private $data =[];

    public function process_data($data)
    {
        $email = rtrim($data["email-address"], ",");
        $email = explode(",", $email);
        unset($data["email-address"]);
        $data["email"] = $email;
        $this->data = $data;
        return $this;
    }

    public function send_email()
    {
        $error_email = [];
        $result = $this->data;
    
        $mail = new PHPMailer(true);

        $body = $result['body'];

        $mail->isSMTP();
        $mail->Host = $_ENV["HOST"];
        $mail->SMTPAuth = true;
        $mail->SMTPKeepAlive = true; //SMTP connection will not close after each email sent, reduces SMTP overhead
        $mail->Port = $_ENV["POST"];
        $mail->Username = $_ENV["USERNAME"];
        $mail->Password = $_ENV['EMAIL_PASSWORD'];
        $mail->setFrom($_ENV["FROM_EMAIL"], $_ENV["FROM_NAME"]);
        $mail->addReplyTo($_ENV["FROM_EMAIL"], $_ENV["FROM_NAME"]);

        $mail->Subject = $result["subject"];

        //Same body for all messages, so set this before the sending loop
        //If you generate a different body for each recipient (e.g. you're using a templating system),
        //set it inside the loop
        $mail->msgHTML($body);
        $mail->addAttachment($result["file"]);
        //msgHTML also sets AltBody, but if you want a custom one, set it afterwards
        $mail->AltBody = strip_tags($body);
        
        foreach ($result["email"] as $email) {
            try {
                $mail->addAddress($email, "");
            } catch (Exception $e) {
                $error_email[] = $email;
                continue;
            }

            try {
                $mail->send();
                echo 'Message sent to : (' .htmlspecialchars($email) . ')';
            } catch (Exception $e) {
                echo 'Mailer Error (' . htmlspecialchars($email) . ') ' . $mail->ErrorInfo . '<br>';
                //Reset the connection to abort sending this message
                //The loop will continue trying to send to the rest of the list
                $mail->getSMTPInstance()->reset();
            }
            //Clear all addresses and attachments for the next iteration
            $mail->clearAddresses();
        }

        if ($error_email) {
            echo "The following email address are invalid". implode("<br>", $error_email);
        }
        
    }
}
