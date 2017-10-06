<?php
namespace App\Utilities;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendEmail
{
    protected $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer();

        $this->mail->IsSMTP(); // Define que a mensagem será SMTP
        $this->mail->Host = "smtp.polihouse.com.br"; # Endereço do servidor SMTP
        $this->mail->Port = 587; // Porta TCP para a conexão
        $this->mail->SMTPAutoTLS = false; // Utiliza TLS Automaticamente se disponível
        $this->mail->SMTPAuth = true; # Usar autenticação SMTP - Sim
        $this->mail->Username = 'naoresponder@polihouse.com.br'; # Usuário de e-mail
        $this->mail->Password = '2491041247%x'; // # Senha do usuário de e-mail
    }

    public function send($from, $to, $acc, $subject, $message){

        $this->mail->From = $from;
        $this->mail->FromName = "Cancelamento Automático Walmart";

        $this->mail->AddAddress($to);
        if(!empty($acc)){
            foreach ($acc as $email) {
                $this->mail->AddCC($email);
            }
        }
        $this->mail->IsHTML(true);
        $this->mail->CharSet = 'utf-8';

        $this->mail->Subject  = $subject;
        $this->mail->Body = $message;

        try{
            $this->mail->Send();
        }catch (\Exception $e){
            throw new Exception('Não foi possivel enviar email', 500, null);
        }

        $this->mail->ClearAllRecipients();
        $this->mail->ClearAttachments();
   }
}