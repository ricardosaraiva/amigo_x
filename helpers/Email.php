<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use Slim\Container;

class Email extends PHPMailer{
    
    public function __construct(Container $container) {
        $this->IsSMTP();
        $this->Host = $container->get('settings')['mail']['host']; 
        $this->From = $container->get('settings')['mail']['email']; 
        $this->FromName = $container->get('settings')['mail']['nome'];    
        $this->SMTPDebug = 0;		
        $this->SMTPAuth = true;		
        $this->CharSet="UTF-8";	
        $this->SMTPSecure = $container->get('settings')['mail']['secure'];  			
        $this->Port = $container->get('settings')['mail']['porta'];  		
        $this->Username = $container->get('settings')['mail']['usuario'];
        $this->Password = $container->get('settings')['mail']['senha'];
        $this->SetFrom($this->From, $this->FromName);
        $this->IsHTML(true);   
    }
    
}