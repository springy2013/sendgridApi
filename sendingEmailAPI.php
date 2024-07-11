<?php
/**
     * SendGridMailer Class
     * 
     * This class provides functionality to send emails using the SendGrid API.
     * It supports sending emails with attachments and handling file uploads.
     * 
     * @Author: Ahmed Rabiea [Springy]
     * @since: 2024-07-08, 
 */
    class SendGridMailer
    {
        private $sendgridApiKey;
        private $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
    
        public function __construct()
        {
            $this->sendgridApiKey = 'YOUR_SENDGRID_API_KEY'; // Replace with your SendGrid API key
        }
    
        public function sendEmail($from, $to, $subject, $content, $attachments = [], $cc = null, $bcc = null, $replyTo = null)
        {
            $curl = curl_init();
            $postFields = [
                'personalizations' => [
                    [
                        'to' => $to,
                        'cc' => $cc,
                        'bcc' => $bcc,
                    ],
                ],
                'from' => $from,
                'reply_to' => $replyTo,
                'subject' => $subject,
                'content' => $content
            ];
            if(!empty($attachments))
            {
                $postFields['attachments'] = $attachments;
            }
            $headers = [
                "Accept: application/json",
                "Authorization: Bearer {$this->sendgridApiKey}",
                "Content-Type: application/json",
            ];
    
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postFields),
            ]);
    
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
            if (curl_errno($curl)) 
            {
                echo 'cURL error: ' . curl_error($curl);
            } 
            else 
            {
                if ($httpCode >= 200 && $httpCode < 300) 
                {
                    echo "Email sent successfully!";
                } 
                else 
                {
                    echo "Failed to send email. HTTP Status Code: $httpCode\n";
                    echo "Response: $response";
                }
            }
    
            curl_close($curl);
        }

        public function addAttachmentFromUpload($file)
        {
            if ($file['error'] === UPLOAD_ERR_OK) 
            {
                $fileContent = file_get_contents($file['tmp_name']);
                $encodedContent = base64_encode($fileContent);
                return [
                    'content' => $encodedContent,
                    'filename' => $file['name'],
                    'type' => $file['type'],
                    'disposition' => 'attachment',
                ];
            } 
            else 
            {
                throw new Exception("File upload error: " . $file['error']);
            }
        }
    }
    
    // Usage example
    $mailer = new SendGridMailer();
    $from = [
        'email' => $_POST['from_mail'], //'springy@branddomain.com',
        'name' => $_POST['from_name'] //'Every Thing example@branddomain.com',
    ];
    $to = [
        [
            'email' => $_POST['to_email'], //'your-email@example.com',
            'name' => $_POST['to_name'],
        ],
    ];
    $subject = $_POST['subject']; // "Mail Subject"
    $content = [
        [
            'type' => 'text/html',
            'value' => $_POST['body'],
        ],
    ];
    $attachments = [];
    if (isset($_FILES['files']) && $_FILES['files']['error'] === UPLOAD_ERR_OK) 
    {
        $attachments[] = $mailer->addAttachmentFromUpload($_FILES['files']);
    }
 
    $mailer->sendEmail($from, $to, $subject, $content, $attachments);    