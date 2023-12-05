<?php

class Kuveyt
{   

    private $boa_customer_id;
    private $boa_merchant_id;
    private $boa_classic_name;
    private $boa_classic_password;

    public function __construct($customer_id, $merchant_id, $classic_name, $classic_password)
    {
        $this->boa_customer_id      = $customer_id;
        $this->boa_merchant_id      = $merchant_id;
        $this->boa_classic_name     = $classic_name;
        $this->boa_classic_password = $classic_password;
    }

    public static function createHash($Password,$MerchantId, $MerchantOrderId, $Amount, $OkUrl, $FailUrl, $UserName)
    {
        $HashedPassword = base64_encode(sha1($Password,"ISO-8859-9")); //md5($Password);    
        $HashData       = base64_encode(sha1($MerchantId.$MerchantOrderId.$Amount.$OkUrl.$FailUrl.$UserName.$HashedPassword , "ISO-8859-9"));
        return $HashData;
    }

    public static function pay($card_name, $card_number, $card_month, $card_year, $card_cvv, $amount,$installment) 
    {
        try {
            
            $order_no            = time();

            $okUrl               = '';
            $failUrl             = '';
            
            $Name                = $card_name; 
            $CardNumber          = str_replace(' ', '', $card_number); 
            $CardExpireDateMonth = str_pad($card_month, 2, "0", STR_PAD_LEFT); 
            $CardExpireDateYear  = str_replace('20','',$card_year);
            $CardCVV2            = $card_cvv;
            $MerchantOrderId     = $order_no;
            $Amount              = (float) str_replace([','], [''], $amount) * 100; 
            $card_installment    = $installment;

            $CustomerId          = $boa_customer_id;
            $MerchantId          = $boa_merchant_id;
            $UserName            = $boa_classic_name;
            $Password            = $boa_classic_password;

            $HashData            = self::createHash($Password,$MerchantId,$MerchantOrderId,$Amount,$okUrl,$failUrl,$UserName);
            
            $xml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
                    .'<APIVersion>1.0.0</APIVersion>'
                    .'<OkUrl>'.$okUrl.'</OkUrl>'
                    .'<FailUrl>'.$failUrl.'</FailUrl>'
                    .'<HashData>'.$HashData.'</HashData>'
                    .'<MerchantId>'.$MerchantId.'</MerchantId>'
                    .'<CustomerId>'.$CustomerId.'</CustomerId>'
                    .'<UserName>'.$UserName.'</UserName>'
                    .'<CardNumber>'.$CardNumber.'</CardNumber>'
                    .'<CardExpireDateYear>'.$CardExpireDateYear.'</CardExpireDateYear>'
                    .'<CardExpireDateMonth>'.$CardExpireDateMonth.'</CardExpireDateMonth>'
                    .'<CardCVV2>'.$CardCVV2.'</CardCVV2>'
                    .'<CardHolderName>'.$Name.'</CardHolderName>'
                    .'<CardType>MasterCard</CardType>'
                    .'<BatchID>0</BatchID>'
                    .'<TransactionType>Sale</TransactionType>'
                    .'<InstallmentCount>'.$card_installment.'</InstallmentCount>'
                    .'<Amount>'.$Amount.'</Amount>'
                    .'<DisplayAmount>'.$Amount.'</DisplayAmount>'
                    .'<CurrencyCode>0949</CurrencyCode>'
                    .'<MerchantOrderId>'.$MerchantOrderId.'</MerchantOrderId>'
                    .'<TransactionSecurity>3</TransactionSecurity>'
                    .'</KuveytTurkVPosMessage>';
            
            $url = 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate';

            
            $ch = curl_init();  
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)) );
            curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri g�nder  
            curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini �nemseme.  
            curl_setopt($ch, CURLOPT_URL,$url); //Baglanacagi URL  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonu�larini al.
            $data = curl_exec($ch);  
            curl_close($ch);

        } catch (ConnectionException $exception) 
        {            
            return $exception->getMessage();

        } catch (ClientException $exception) 
        {
            return $exception->getErrorMessage();
        }
    }

    public static function confirm($_POST) 
    {
        if(!array_key_exists('AuthenticationResponse',$_POST))
        {
            return "İşlem Başarısız";
        }
        
        $AuthenticationResponse = $_POST["AuthenticationResponse"];
        $RequestContent         = urldecode($AuthenticationResponse);
        $xml                    = simplexml_load_string($RequestContent) or die("Error: Cannot create object");
        
        if($xml->ResponseCode == "00")
        {
            $MerchantOrderId = $xml->MerchantOrderId;
            $Amount          = $xml->VPosMessage->Amount; //Islem Tutari
            $MD              = $xml->MD; //Islem Tutari
            
            $MerchantOrderId = $this->boa_merchant_id;
            $CustomerId      = $this->boa_customer_id;
            $UserName        = $this->boa_classic_name;
            $Password        = $this->boa_classic_password;

            $HashedPassword  = base64_encode(sha1($Password,"ISO-8859-9")); //md5($Password);
            $HashData        = base64_encode(sha1($MerchantId.$MerchantOrderId.$Amount.$UserName.$HashedPassword , "ISO-8859-9"));

            $xml='<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
            <APIVersion>1.0.0</APIVersion>
            <HashData>'.$HashData.'</HashData>
            <MerchantId>'.$MerchantId.'</MerchantId>
            <CustomerId>'.$CustomerId.'</CustomerId>
            <UserName>'.$UserName.'</UserName>
            <TransactionType>Sale</TransactionType>
            <InstallmentCount>0</InstallmentCount>
            <CurrencyCode>0949</CurrencyCode>
            <Amount>'.$Amount.'</Amount>
            <MerchantOrderId>'.$MerchantOrderId.'</MerchantOrderId>
            <TransactionSecurity>3</TransactionSecurity>
            <KuveytTurkVPosAdditionalData>
            <AdditionalData>
                <Key>MD</Key>
                <Data>'.$MD.'</Data>
            </AdditionalData>
            </KuveytTurkVPosAdditionalData>
            </KuveytTurkVPosMessage>';
            
            $url = 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelProvisionGate';
            
            try {
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSLVERSION, 6);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)) );
                curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri g�nder  
                curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini �nemseme.  
                curl_setopt($ch, CURLOPT_URL,$url); //Baglanacagi URL  
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonu�larini al.
                $xml_response = curl_exec($ch);  
                curl_close($ch);
                
                $data = simplexml_load_string($xml_response);
                
                if($data->ResponseCode == "00")
                {   
                    // Siparişi Onayla 

                } else 
                {
                    $ErrMsg = (string)$data->ResponseMessage;

                    return "Ödeme Islemi Basarisiz. Hata = ".$ErrMsg;
                }
            }
            catch (Exception $e) 
            {   
                echo "3D İşlemi başarısız";
            }
        } else 
        {
            $ErrMsg = (string)$xml->ResponseMessage;

            return "Ödeme Islemi Basarisiz. Hata = ".$ErrMsg
        }
    }
}
