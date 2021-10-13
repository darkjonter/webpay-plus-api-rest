<?php
/**
 * @author     Cristian Cisternas.
 * @copyright  2021 Brouter SpA (https://www.brouter.cl)
 * @date       Agoust 2021
 * @license    GNU LGPL
 * @version    1.0.1
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function get_ws($data,$method,$type,$endpoint){
    $curl = curl_init();
    if($type=='live'){
		$TbkApiKeyId='597055555532';
		$TbkApiKeySecret='579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C';
        $url="https://webpay3g.transbank.cl".$endpoint;//Live
    }else{
		$TbkApiKeyId='597055555532';
		$TbkApiKeySecret='579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C';
        $url="https://webpay3gint.transbank.cl".$endpoint;//Testing
    }
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        'Tbk-Api-Key-Id: '.$TbkApiKeyId.'',
        'Tbk-Api-Key-Secret: '.$TbkApiKeySecret.'',
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    //echo $response;
    return json_decode($response);
}

$baseurl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$url="https://webpay3g.transbank.cl/";//Live
$url="https://webpay3gint.transbank.cl/";//Testing

$action = isset($_GET["action"]) ? $_GET["action"] : 'init';
$message=null;
$post_array = false;

switch ($action) {
    
    case "init":
        $message.= 'init';
        $buy_order=rand();
        $session_id=rand();
        $amount=15000;
        $return_url = $baseurl."?action=getResult";
		$type="sandbox";
            $data='{
                    "buy_order": "'.$buy_order.'",
                    "session_id": "'.$session_id.'",
                    "amount": '.$amount.',
                    "return_url": "'.$return_url.'"
                    }';
            $method='POST';
            $endpoint='/rswebpaytransaction/api/webpay/v1.0/transactions';
            
            $response = get_ws($data,$method,$type,$endpoint);
            $message.= "<pre>";
            $message.= print_r($response,TRUE);
            $message.= "</pre>";
            $url_tbk = $response->url;
            $token = $response->token;
            $submit='Continuar!';

    break;

    case "getResult":
        
        $message.= "<pre>".print_r($_POST,TRUE)."</pre>";
        if (!isset($_POST["token_ws"]))
            break;

        /** Token de la transacción */
        $token = filter_input(INPUT_POST, 'token_ws');
        
        $request = array(
            "token" => filter_input(INPUT_POST, 'token_ws')
        );
        
        $data='';
		$method='PUT';
		$type='sandbox';
		$endpoint='/rswebpaytransaction/api/webpay/v1.0/transactions/'.$token;
		
        $response = get_ws($data,$method,$type,$endpoint);
       
        $message.= "<pre>";
        $message.= print_r($response,TRUE);
        $message.= "</pre>";
        
        $url_tbk = $baseurl."?action=getStatus";
        $submit='Ver Status!';
        
        break;
        
    case "getStatus":
        
        if (!isset($_POST["token_ws"]))
            break;

        /** Token de la transacción */
        $token = filter_input(INPUT_POST, 'token_ws');
        
        $request = array(
            "token" => filter_input(INPUT_POST, 'token_ws')
        );
        
        $data='';
		$method='GET';
		$type='sandbox';
		$endpoint='/rswebpaytransaction/api/webpay/v1.0/transactions/'.$token;
		
        $response = get_ws($data,$method,$type,$endpoint);
       
        $message.= "<pre>";
        $message.= print_r($response,TRUE);
        $message.= "</pre>";
        
        $url_tbk = $baseurl."?action=refund";
        $submit='Refund!';
        break;
        
    case "refund":
        
        if (!isset($_POST["token_ws"]))
            break;

        /** Token de la transacción */
        $token = filter_input(INPUT_POST, 'token_ws');
        
        $request = array(
            "token" => filter_input(INPUT_POST, 'token_ws')
        );
        $amount=15000;
        $data='{
                  "amount": '.$amount.'
                }';
		$method='POST';
		$type='sandbox';
		$endpoint='/rswebpaytransaction/api/webpay/v1.0/transactions/'.$token.'/refunds';
		
        $response = get_ws($data,$method,$type,$endpoint);
       
        $message.= "<pre>";
        $message.= print_r($response,TRUE);
        $message.= "</pre>";
        $submit='Crear nueva!';
        $url_tbk = $baseurl;
        break;        
}        
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Webpay Plus Mall">
        <meta name="author" content="VendoOnline.cl">
    
        <title>Pagos</title>
        <style>
            .container {
              height: 200px;
              position: relative;
              text-align: center;
              
            }
            
            .vertical-center {
                margin-top: 20%;
              /*margin: 0;
              position: absolute;
              top: 50%;
              -ms-transform: translateY(-50%);
              transform: translateY(-50%);*/
            }
            .lds-hourglass {
              display: inline-block;
              position: relative;
              width: 80px;
              height: 80px;
            }
            .lds-hourglass:after {
              content: " ";
              display: block;
              border-radius: 50%;
              width: 0;
              height: 0;
              margin: 8px;
              box-sizing: border-box;
              border: 32px solid purple;
              border-color: purple transparent purple transparent;
              animation: lds-hourglass 1.2s infinite;
            }
            @keyframes lds-hourglass {
              0% {
                transform: rotate(0);
                animation-timing-function: cubic-bezier(0.55, 0.055, 0.675, 0.19);
              }
              50% {
                transform: rotate(900deg);
                animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
              }
              100% {
                transform: rotate(1800deg);
              }
            }
        </style>
    </head>
    <body>
        <div class="container">
          <div class="vertical-center">
              <div class="lds-hourglass"></div>
              <img src="WebpayPlus_FB_300px.png">
              <p><?php echo $message; ?></p>
                <?php if (strlen($url_tbk)) { ?>
                <form name="brouterForm" id="brouterForm"  method="POST" action="<?=$url_tbk?>" style="display:block;">
                  <input type="hidden" name="token_ws" value="<?=$token?>" />
                  <input type="submit" value="<?=(($submit)? $submit : 'Cargando...')?>" style="border: 1px solid #6b196b;
    border-radius: 4px;
    background-color: #6b196b;
    color: #fff;
    font-family: Roboto,Arial,Helvetica,sans-serif;
    font-size: 1.14rem;
    font-weight: 500;
    margin: auto 0 0;
    padding: 12px;
    position: relative;
    text-align: center;
    -webkit-transition: .2s ease-in-out;
    transition: .2s ease-in-out;
    max-width: 200px;" />
                </form>
                <script>
            
                var auto_refresh = setInterval(
                function()
                {
                    //submitform();
                }, 15000);
            //}, 5000);
                function submitform()
                {
                  //alert('test');
                  document.brouterForm.submit();
                }
                </script>
            <?php } ?>
            </div>
        </div>
    </body>
</html>