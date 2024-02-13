<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    function email_simulador($email,$link) {
    
          //------ send email
    
          $pdf_data = base64_decode($link);
    
            // Define o cabeçalho do e-mail para incluir o anexo
            $boundary = md5(uniqid(time()));
            $headers ="From: " . mb_encode_mimeheader('Fénix Pensões', "UTF-8") . "<".env('API_EMAIL').">" . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
            // Corpo do e-mail
            $message = "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset='utf-8'\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= '
              <html lang="pt-AO">
              <head><meta http-equiv="Content-Type" content="text/html charset=UTF-8" /></head>
              <body style="background-color:#f6f9fc;padding:10px 0;color:#000;">
                <table align="center" role="presentation" cellSpacing="0" cellPadding="0" border="0" width="100%"
                  style="max-width:37.5em;background-color:#ffffff;border:1px solid #f0f0f0;padding:45px">
                  <tr style="width:100%">
                    <td><img alt="Fénix Pensões" src="' . asset('prince.png') . '" width="50" height="50" style="display:block;outline:none;border:none;text-decoration:none" />
                      <table align="center" border="0" cellPadding="0" cellSpacing="0" role="presentation" width="100%">
                        <tbody>
                          <tr>
                            <td>
                              <p style="font-size:16px;line-height:26px;margin:24px 0 0;">
                                Apresentamos o resultado da sua simulação de pensão. Como solicitado, anexamos o arquivo em formato PDF.
                              </p>
                              <p style="font-size:16px;line-height:26px;margin:8px 0 32px;">
                              Por favor, revise o arquivo anexado e entre em contacto connosco se tiver alguma dúvida ou visite <a target="_blank" style="color: #0f25ff;text-decoration:underline" href="https://fenixpensoes.ao/">nosso site para obter mais informações.</a>
                            </p>
                            
                            <a style="color:#ffffff;background:#ff6406;border:none;width:100%;font-size:20px;font-family:sans-serif;font-weight:semibold;border-radius:8px;text-decoration:none;padding:18px 80px;" href="https://fenixpensoes.ao/subscrever-reembolsar/">Subscrever a um Fundo</a>
                            
                              <p style="font-size:16px;line-height:26px;margin:16px 0;">
                                &copy; Todos os direitos Reservados - Fénix Pensões
                              </p>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </table>
              </body>
              </html>
            ';
    
            $message .= "\r\n\r\n";
           
    
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: application/pdf; name=\"Simulação-Fénix.pdf\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"Simulação-Fénix.pdf\"\r\n\r\n";
            $message .= chunk_split(base64_encode($pdf_data)) . "\r\n";
    
            $message .= "--$boundary--";
    
            // Envie o e-mail
            $to = $email;
            $subject = "Resultado da simulação - Fénix Pensões";
            
            if (mail($to, $subject, $message, $headers)) {
              return 1;
            } else {
              encode(0);
            }
    
    }

    function email_simulador_tt($email,$link) {
    
      //------ send email

      $pdf_data = base64_decode($link);

        // Define o cabeçalho do e-mail para incluir o anexo
        $boundary = md5(uniqid(time()));
        $headers = 'From: Fénix Pensões <noreply@fenixpensoes.ao>' . "\r\n" .
        'Reply-To: noreply@fenixpensoes.ao' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
        // Corpo do e-mail
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset='utf-8'\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= '
          <html lang="pt-AO">
          <head><meta http-equiv="Content-Type" content="text/html charset=UTF-8" /></head>
          <body style="background-color:#f6f9fc;padding:10px 0;color:#000;">
            <table align="center" role="presentation" cellSpacing="0" cellPadding="0" border="0" width="100%"
              style="max-width:37.5em;background-color:#ffffff;border:1px solid #f0f0f0;padding:45px">
              <tr style="width:100%">
                <td><img alt="Fénix Pensões" src="' . asset('prince.png') . '" width="50" height="50" style="display:block;outline:none;border:none;text-decoration:none" />
                  <table align="center" border="0" cellPadding="0" cellSpacing="0" role="presentation" width="100%">
                    <tbody>
                      <tr>
                        <td>
                          <p style="font-size:16px;line-height:26px;margin:24px 0 0;">
                            Apresentamos o resultado da sua simulação de pensão. Como solicitado, anexamos o arquivo em formato PDF.
                          </p>
                          <p style="font-size:16px;line-height:26px;margin:8px 0 32px;">
                          Por favor, revise o arquivo anexado e entre em contacto connosco se tiver alguma dúvida ou visite <a target="_blank" style="color: #0f25ff;text-decoration:underline" href="https://fenixpensoes.ao/">nosso site para obter mais informações.</a>
                        </p>
                        
                        <a style="color:#ffffff;background:#ff6406;border:none;width:100%;font-size:20px;font-family:sans-serif;font-weight:semibold;border-radius:8px;text-decoration:none;padding:18px 80px;" href="https://fenixpensoes.ao/subscrever-reembolsar/">Subscrever a um Fundo</a>
                        
                          <p style="font-size:16px;line-height:26px;margin:16px 0;">
                            &copy; Todos os direitos Reservados - Fénix Pensões
                          </p>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </table>
          </body>
          </html>
        ';

        $message .= "\r\n\r\n";
       

        $message .= "--$boundary\r\n";
        $message .= "Content-Type: application/pdf; name=\"Simulação-Fénix.pdf\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"Simulação-Fénix.pdf\"\r\n\r\n";
        $message .= chunk_split(base64_encode($pdf_data)) . "\r\n";

        $message .= "--$boundary--";

        // Envie o e-mail
        $to = $email;
        $subject = "Resultado da simulação - Fénix Pensões";
        
        if (mail($to, $subject, $message, $headers)) {
          return 1;
        } else {
          encode(0);
        }

  }

    function email_portal($email,$cod) {
    
      //------ send email


        // Define o cabeçalho do e-mail para incluir o anexo
        $boundary = md5(uniqid(time()));
        $headers ="From: " . mb_encode_mimeheader('Fénix Pensões', "UTF-8") . "<".env('API_EMAIL').">" . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        // Corpo do e-mail
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset='utf-8'\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= '
        <html lang="pt-AO">
        <head><meta http-equiv="Content-Type" content="text/html charset=UTF-8" /></head>
        <body style="background-color:#f6f9fc;padding:10px 0;color:#000;">
          <table align="center" role="presentation" cellSpacing="0" cellPadding="0" border="0" width="100%"
            style="max-width:37.5em;background-color:#ffffff;border:1px solid #f0f0f0;padding:45px">
            <tr style="width:100%">
              <td><img alt="Fénix Pensões" src="' . asset('prince.png') . '" width="50" height="50" style="display:block;outline:none;border:none;text-decoration:none" />
                <table align="center" border="0" cellPadding="0" cellSpacing="0" role="presentation" width="100%">
                  <tbody>
                    <tr>
                      <td>
                        <p style="font-size:16px;line-height:26px;margin:16px 0;">
                          Olá,</p>
                        <p style="font-size:16px;line-height:26px;margin:16px 0;">
                          Este é o código de verificação para aceder a sua
                          conta:
                        <p style="color:#000;font-size:18px;font-weight:bold;margin-top:12px;text-align:center;display:inline-block;padding:14px 4px;max-width:100%;">
                          ' . $cod . '
                        </p>
                        <p style="font-size:16px;line-height:26px;margin:16px 0;">
                          Se você não está tentando entrar na sua conta ou não pediu isso, apenas ignore ou apague este e-mail.
                        <p style="font-size:16px;line-height:26px;margin:16px 0;">
                          Para manter sua conta segura, por favor não enviei este email para ninguém. Se precisar de ajuda
                          visite <a target="_blank" style="color: #0f25ff;text-decoration:underline"
                            href="https://fenixpensoes.ao/">nosso site para obter mais informações.</a></p>
                        <p style="font-size:16px;line-height:26px;margin:16px 0;">
                          &copy; Todos os direitos Reservados - Fénix Pensões
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </table>
        </body>
        </html>
        ';

        $message .= "\r\n\r\n";
       

        
        //$message .= "--$boundary--";

        // Envie o e-mail
        $to = $email;
        $subject = "Seu código de verificação - Fénix Pensões Portal";
        
        if (mail($to, $subject, $message, $headers)) {
          return 1;
        } else {
          encode(0);
        }

    }
}
