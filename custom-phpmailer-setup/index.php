<?php
/*
Plugin Name: Custom Email SMTP
Description: Setup de phpmailer pour site LPO AuRA
Version: 1.1.0
Author: LPO AuRA
Author URI: https://auvergne-rhone-alpes.lpo.fr
*/

add_action( 'phpmailer_init', 'setup_phpmailer_init' );
function setup_phpmailer_init( $phpmailer ) {
    $phpmailer->Host = getenv('WORDPRESS_SMTP_HOST'); // for example, smtp.mailtrap.io
    $phpmailer->Port = getenv('WORDPRESS_SMTP_PORT'); // set the appropriate port: 465, 2525, etc.
    $phpmailer->Username = getenv('WORDPRESS_SMTP_USERNAME'); // your SMTP username
    $phpmailer->Password = getenv('WORDPRESS_SMTP_PASSWORD'); // your SMTP password
    $phpmailer->SetFrom(getenv('WORDPRESS_SMTP_USERNAME'),getenv('WORDPRESS_SMTP_FULL_NAME'));
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = 'tls'; // preferable but optional
    //$phpmailer->IsSMTP();
    $phpmailer->isSMTP();

//    if (!$phpmailer->send()) {
//    	echo 'Mailer Error: ' . $mail->ErrorInfo;
//    } else {
//    }

}

