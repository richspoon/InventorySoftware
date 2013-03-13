<?php
//require_once "$ROOT/phplib/swift4/swift_required.php";

// file: /Lib/Lib_MailSwift.php

class Lib_MailSwift
{
    protected $Subject       = '';
    protected $Body_Text     = '';
    protected $Body_Html     = '';
    protected $Body_Html_Raw = '';
    protected $From          = '';
    protected $To            = '';
    protected $Cc            = '';
    protected $Bcc           = '';
    protected $Attachment    = '';
    protected $Return_Path   = '';

    protected $Transport     = '';
    protected $Mailer        = '';
    protected $Smtp                  = array();
    protected $Attached_Files        = array();
    protected $Attached_Dynamic_Files= array();
    protected $Text_Headers          = array();
    protected $Attach_Images         = false;

    protected $Message;

    public    $Error          = '';
    public    $Error_Extended = '';


    // add translations as necessary for this text
    public    $Text_Messages  = array (
        'ATTACHMENT_NOT_FOUND'  => 'Attachment not found',
        'BAD_EMAIL_RETURN_PATH' => 'Bad Email Return Path',
        'BAD_EMAIL'             => 'Bad Email',
        'MESSAGE_FAILED'        => 'Message Failed',
        'TRANSPORT_FAILED'      => 'Transport Failed to Initialize'
    );


    // function to check if email has valid form
    public function CheckEmail($email, $use_quotes=false)
    {
        if (!$use_quotes) {
            if (preg_match('/["\'\\\]/', $email)) return false;
        }
        $at_index       = strrpos($email, '@');
        if ($at_index === false) return false;  // no @

        //----local check----
        $local = substr($email, 0, $at_index);
        if (empty($local) or (strlen($local) > 64)) return false;  // max local length=64

        if (preg_match('/^\..*|.*\.$/', $local)) return false; // cannot start or end with dot
        $local = preg_replace("/\\\./", '-', $local); // remove slash items
        if (substr_count($local, '"') > 2) return false;  // cannot have more than two non-slashed quotes

        $local = preg_replace('/".+"$/', '', $local); // remove double quoted content
        if (!empty($local)) {
            $local = preg_replace("/\\\./", '-', $local); // remove slash items
            if (substr($local,-1) == '\\') return false;
            if (!preg_match('/^([A-Za-z0-9!#\$%&\'*\+\/\=\?\^_`\{|\}~\.-]+)$/', $local)) return false; // filter bad characters
            if (strpos($local, '..') !== false) return false; // cannot have double dots
        }
        //----domain check----
        $domain = substr($email, $at_index + 1);
        if (strlen($domain > 255)) return false;
        if (!preg_match('/([0-9a-z\.-]+)\.([a-z]{2,6})$/', $domain)) return false;
        $domain_labels = explode('.', $domain);
        foreach ($domain_labels as $label) {
            if (empty($label) or (strlen($label) > 63)) return false;
        }
        return true;
    }

    // function to add to error string
    protected function AddError($error)
    {
        $this->Error .= "$error<br />\n";
    }

    // function to set SMTP parameters, could be called in construct of child classes
    public function SetSmtp($smtp, $username, $password, $port=25)
    {
        $this->Smtp = array(
            'smtp'     => $smtp,
            'username' => $username,
            'password' => $password,
            'port'     => $port
        );
    }

    // function to set subject
    public function SetSubject($subject)
    {
        // remove HTML special characters from subject
        $subject = htmlspecialchars_decode($subject);
        $this->Subject = $subject;
    }


    // function to remove extra spaces in HTML code
    public function CompressHtml($html)
    {
        $E = chr(27);
        //trim and replace line breaks with escape character
        $RESULT = mb_ereg_replace("[\r\n|\n]+", $E, trim($html));

        //compress spaces
        $RESULT = mb_ereg_replace("[[:space:]]+", ' ', $RESULT);

        //remove space around line breaks
        $RESULT = mb_ereg_replace("{$E}[[:space:]]+", $E, $RESULT);
        $RESULT = mb_ereg_replace("[[:space:]]+$E", $E, $RESULT);

        //restore line breaks, removing multiples
        $RESULT = mb_ereg_replace("$E+", "\n", $RESULT);

        return $RESULT;
    }


    // function to set html body
    public function SetBodyHtml($text)
    {
        // raw is uncompressed
        $this->Body_Html_Raw = $text;
        $this->Body_Html = $this->CompressHtml($text);
    }

    // function to set text body
    public function SetBodyText($text)
    {
        $this->Body_Text = $text;
    }

    // function to set text body from HTML
    public function SetBodyTextFromHtml()
    {
        // strip tags is only a start, other transforms are needed, such as links
        $text = strip_tags($this->Body_Html_Raw);
        $this->Body_Text = $text;
    }

    // function to check to see if a file attachment exists
    protected function CheckAttachmentExists($attachment)
    {
        if (file_exists($attachment)) {
            return true;
        } else {
            $this->AddError($this->Text_Messages['ATTACHMENT_NOT_FOUND'] . " ($attachment)");
            return false;
        }
    }

    // function to set attachment files
    public function SetAttachedFiles($attachments)
    {
        // assumes an array of file links or a single attachment
        if ($attachments) {
            if (is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    if ($this->CheckAttachmentExists($attachment)) {
                        $this->Attached_Files[] = $attachment;
                    }
                }
            } else {
                if ($this->CheckAttachmentExists($attachments)) {
                    $this->Attached_Files[] = $attachments;
                }
            }
        } else {
            $this->Attached_Files = array();
        }
    }


    // function to attach dynamic content
    public function SetAttachedDynamicFiles($attachments)
    {
        // requires an array  of rows: ('data', 'filename', 'type')
        $this->Attached_Dynamic_Files = array();

        if ($attachments) {
            if (is_array($attachments[0])) {
                $this->Attached_Dynamic_Files = $attachments;
            } else {
                // must be a single item
                $this->Attached_Dynamic_Files[] = $attachments;
            }
        }
    }
    
    public function SetAttachImages($value = true)
    {
        $this->Attach_Images = $value;
    }

    // function to set custom headers
    public function SetTextHeaders($headers)
    {
        // assumes an associative array of header-name => value
        if ($headers and is_array($headers)) {
            foreach ($headers as $name => $value) {
                $this->Text_Headers[$name] = $value;
            }
        } else {
            $this->Text_Headers = array();
        }
    }


    // function to get emails to set, can take arrays or comma delimited lists
    protected function GetEmails($list)
    {
        if ($list) {
            if (is_array($list)) {
                return $list;
            } else {
                //comma delimited list in: name:email,... || email1,email2..., ||  name <email>,. . .
                $emails = explode(',', $list);
                $RESULT = array();
                foreach ($emails as $email) {
                    $email = trim($email);
                    if (strpos($email, '<') !== false) {
                        $email_part = trim(preg_replace('/.+<|>/', '', $email));
                        // --- get name <email> format ---
                        $name  = trim(preg_replace('/<.+/', '', $email));
                        $email = $email_part;
                    } else {
                        $parts = explode(':', $email);
                        // --- get name:email format ---
                        if (count($parts) > 1) {
                            $name  = trim($parts[0]);
                            $email = trim($parts[1]);
                        } else {
                            $name = '';
                        }
                    }

                    if ( $this->CheckEmail($email) ) {
                        if ($name) {
                            $RESULT[$email] = $name;
                        } else {
                            $RESULT[] = $email;
                        }
                    } else {
                        $this->AddError($this->Text_Messages['BAD_EMAIL'] . ": $email");
                    }
                }
                return $RESULT;
            }
        } else {
            return '';
        }
    }

    // function to set TO
    public function SetTo($to)
    {
        $this->To = $this->GetEmails($to);
    }

    // function to set FROM
    public function SetFrom($from)
    {
        $this->From = $this->GetEmails($from);
    }

    // function to set CC
    public function SetCc($cc)
    {
        $this->Cc = $this->GetEmails($cc);
    }

    // function to set BCC
    public function SetBcc($bcc)
    {
        $this->Bcc = $this->GetEmails($bcc);
    }


    // function to set the return path
    public function SetReturnPath($return_path)
    {
        if (is_array($return_path)) {
            foreach ($return_path as $key => $value) {
                if ($this->CheckEmail($key)) {
                    $this->Return_Path = $key;
                } elseif ($this->CheckEmail($value)) {
                    $this->Return_Path = $value;
                } else {
                    $this->Return_Path = '';
                    $this->AddError($this->Text_Messages['BAD_EMAIL_RETURN_PATH']);
                }
                return;
            }
        } elseif ($this->CheckEmail($return_path)) {
            $this->Return_Path = $return_path;
        } else {
            $this->Return_Path = '';
            if (!empty($return_path)) {
                $this->AddError($this->Text_Messages['BAD_EMAIL_RETURN_PATH']);
            }
        }
    }



    // function to instantiate the transport and mailer, if not already set, check for SMTP, otherwise uses sendmail
    public function SetTransportAndMailer()
    {
        if (!$this->Transport) {
            try {

                if (!empty($this->Smtp)) {
                    $this->Transport = Swift_SmtpTransport::newInstance($this->Smtp['smtp'], $this->Smtp['port'])
                        ->setUsername($this->Smtp['username'])
                        ->setPassword($this->Smtp['password']);
                } else {
                    $this->Transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
                }
                return $this->Mailer = Swift_Mailer::newInstance($this->Transport);

            } catch(Exception $e) {
                $this->AddError($this->Text_Messages['TRANSPORT_FAILED']); // message to public
                $this->Error_Extended = $e->getMessage();  // message for admin
                return false;
            }
        }
    }


    // function to output email details for diagnosis, used in function below
    protected function GetEmailDetails($email, $title='')
    {
        $RESULT = '';
        if (is_array($email)) {
            foreach ($email as $key => $value) {
                $RESULT .= "<dt>$title:</dt><dd>$key => $value</dd>\n";
            }
        } else {
            $RESULT .= "<dt>$title:</dt><dd>$email</dd>\n";
        }
        return $RESULT;
    }

    // function to output message details for diagnosis, using a definition list
    public function GetMessageDetails()
    {
        $RESULT  = '<dl>';
        $RESULT .= $this->GetEmailDetails($this->From, 'From');
        $RESULT .= $this->GetEmailDetails($this->To, 'To');

        if ($this->Cc) {
            $RESULT .= $this->GetEmailDetails($this->Cc, 'Cc');
        }

        if ($this->Bcc) {
            $RESULT .= $this->GetEmailDetails($this->Bcc, 'Bcc');
        }

        if ($this->Return_Path) {
            $RESULT .= $this->GetEmailDetails($this->Return_Path, 'Return Path');
        }

        if ($this->Text_Headers) {
            $RESULT .= "<dt>Custom Headers:</dt><dd>\n<ul>\n";
            foreach ($this->Text_Headers as $name => $value) {
                $RESULT .= "<li>$name: $value</li>\n";
            }
            $RESULT .= "</ul>\n</dd>";
        }

        $RESULT .= "<dt>Subject:</dt><dd>{$this->Subject}</dd>\n";
        $RESULT .= "<dt>HTML:</dt><dd>{$this->Body_Html}</dd>\n";
        $RESULT .= "<dt>Text:</dt><dd><pre>{$this->Body_Text}</pre></dd>\n";

        if ($this->Attached_Files) {
            $RESULT .= "<dt>Attachments:</dt><dd>\n<ul>\n";
            foreach ($this->Attached_Files as $attachment) {
                $RESULT .= "<li>$attachment</li>\n";
            }
            $RESULT .= "</ul>\n</dd>";
        }

        if ($this->Attached_Dynamic_Files) {
            $RESULT .= "<dt>Dynamic Attachments:</dt><dd>\n<ul>\n";
            foreach ($this->Attached_Dynamic_Files as $attachment) {
                //$data     = $attachment[0];
                $filename = $attachment[1];
                $type     = $attachment[2];
                $RESULT .= "<li>$filename, $type</li>\n";
            }
            $RESULT .= "</ul>\n</dd>";
        }

        return $RESULT;
    }

    // function to send a message pulling from the class variables
    public function SendMessage()
    {
        $ROOT = $_SERVER['DOCUMENT_ROOT'];
        try {
            $message = Swift_Message::newInstance()
            ->setSubject($this->Subject)
            ->setFrom($this->From)
            ->setTo($this->To);

            if(!$this->Return_Path) {
                $this->SetReturnPath($this->From);
            }
            $message->setReturnPath($this->Return_Path);

            if ($this->Cc) {
                $message->setCc($this->Cc);
            }

            if ($this->Bcc) {
                $message->setBcc($this->Bcc);
            }

            if ($this->Text_Headers) {
                foreach ($this->Text_Headers as $name => $value) {
                    $message->getHeaders()->addTextHeader($name, $value);
                }
            }

            if ($this->Attached_Files) {
                foreach ($this->Attached_Files as $attachment) {
                    $message->attach(Swift_Attachment::fromPath($attachment));
                }
            }

            if($this->Attached_Dynamic_Files) {
                foreach ($this->Attached_Dynamic_Files as $attachment) {
                    $data     = $attachment[0];
                    $filename = $attachment[1];
                    $type     = $attachment[2];
                    $message->attach(Swift_Attachment::newInstance($data, $filename, $type));
                }
            }

            if ($this->Body_Html) {
                if ($this->Attach_Images) {
                    $image_array = TextBetweenArray('src="', '"', $this->Body_Html);
                    $image_array = array_unique($image_array);
                    if (!empty($image_array)) {
                        foreach($image_array as $image) {
                            $src = $message->embed(Swift_EmbeddedFile::fromPath($ROOT . $image));
                            $this->Body_Html = str_replace($image, $src, $this->Body_Html);
                        }
                    }
                }
                $body_html = Swift_MimePart::newInstance( $this->Body_Html, 'text/html' );

                $message->attach($body_html, 'text/html');
            }

            if (empty($this->Body_Text) and $this->Body_Html) {
                $this->SetBodyTextFromHtml();
            }

            $body_text = Swift_MimePart::newInstance( $this->Body_Text, 'text/plain' );
            $message->addPart($this->Body_Text, 'text/plain');

            $this->SetTransportAndMailer();
            return $this->Mailer->send($message);

        } catch (Exception $e) {
            $this->AddError($this->Text_Messages['MESSAGE_FAILED']); // message to public
            $this->Error_Extended = $e->getMessage();  // message for admin
            return false;
        }

    }


    // general function to send a messages, sets the basics,  will need to set attachments, special headers, and SMTP before this function is called
    public function SendMail($from, $to='', $subject='', $message_html='', $message_text='', $bounce='', $cc='', $bcc='', $attach_images=false)
    {

        /* can use:
        $OBJ->SendMail(array(
            'from'         => '',
            'to'           => '',
            'subject'      => '',
            'message_html' => '',
            'message_text' => '',
            'bounce'       => '',
            'cc'           => '',
            'bcc'          => '',
            'attach_images'=> true
            ));
        */

        if (is_array($from)) extract($from, EXTR_OVERWRITE);

        $error_point = 0;
        try {
            $this->Error = '';
            $this->Error_Extended = '';

            $this->SetFrom($from);
            $this->SetTo($to);
            $this->SetCc($cc);
            $this->SetBcc($bcc);
            $this->SetSubject($subject);
            $this->SetBodyHtml($message_html);
            $this->SetBodyText($message_text);
            $this->SetReturnPath($bounce);
            $this->Attach_Images = $attach_images;
            $error_point = 1;
            return $this->SendMessage();

        } catch (Exception $e) {
            if ($error_point == 0) {
                // this means error occurred before SendMessage(), so need to add errors, otherwise errors already added
                $this->AddError($this->Text_Messages['MESSAGE_FAILED']); // message to public
                $this->Error_Extended = $e->getMessage();  // message for admin
            }
            return false;
        }
    }
}