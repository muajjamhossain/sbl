<?php

		$response_xml = $_REQUEST['Request'];
        $xml_object = simplexml_load_string($response_xml);

        
        // responde xml to array
        $response_array = objectsIntoArray($xml_object);
        
        // echo "<pre>";
        // print_r($response_array);
        // exit();
        /* having arrays to debug*/
        echo '<pre>----------------Result ------------------</pre><br>';
        // exit();
        /* end of debug*/
         //var_dump($response_array);
         //var_dump("tsjhdsjfhdsjkhg");
        if ($response_array["TransactionId"])
        {
            
            /* TransactionStatus is 200: means ok */
            if ($response_array["TransactionStatus"] == '200')
            {
                $vsatus = postTranActionforSuccess($response_array);
                
               // var_dump("tsjhdsjfhdsjkhg".$vsatus);
                if($vsatus=="200"){
                    
                     $notify = updateTransactionasSuccess($response_array);
                    
                 $notification = 'Transaction Made Successfully.';
                }
                else{
                    $notification = 'Data is not valid .';
                }
                
                echo $notification.$response_array["TransactionId"] ; //exit();
            }
            else if ($response_array["TransactionStatus"] == '5017')
            {
                $notification = updateTransactionasPending($response_array);
                
                echo $notification; exit();
            }
            /* Fail transaction if Cancel Btn is hit on SPG */
            else
            {
                $notification = 'Transaction Cancelled.';
                echo $notification; exit();
            }
        }
        /* Fail transaction */
        else 
        {
        	$notification = 'Transaction Failed.';
             echo $notification; exit();
        }

 function updateTransactionasPending($response_array)
    {
        $notification ="Data status upadte print your voucher";// updateTransactionasPending($response_array);// need only database pending status update
        
    
        return $notification;
    }
    
    function updateTransactionasSuccess($response_array)
    {
        $notification ="Your Payment Successfull";// updateTransactionasPending($response_array);// need only database pending status update
        
        return $notification;
    }
    
  function objectsIntoArray($arrObjData, $arrSkipIndices = array())
    {
        $arrData = array();
        // if input is object, convert into array
        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }
        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
                }
                if (in_array($index, $arrSkipIndices)) {
                    continue;
                }
                $arrData[$index] = $value;
            }
        }
        return $arrData;
    }


 function postTranActionforSuccess($response_array)
    {
        // $paymentRefId is like 190529 017 0007003
        $paymentRefId = $response_array["TransactionId"];
         
        // Response Transaction Array Verification through SPG API
        $verifiedTransaction = verifyTransaction($response_array);
        
       // var_dump("tsjhdsjfhdsjkhg".$verifiedTransaction);
       $verifiedTransaction = json_decode($verifiedTransaction, true);
       
       //var_dump( "Hasan".$verifiedTransaction->StatusCode);
       
        if($response_array["TransactionId"] == $verifiedTransaction['TransactionId'] && $response_array["TranAmount"] == $verifiedTransaction['TranAmount'] && $verifiedTransaction['StatusCode'] == '200') {
            
            
            return $notification="200";
        
        } else {

            return $notification = '500'; 
        }
    }

    /*
    * api for transaction verification
    * it sends 10 digits form 16 digits txnid
    */
    function verifyTransaction($response_array)
    {
        
        //-------------Change -------------------
        $verifyurl = 'https://spg.sblesheba.com:6314/api/SpgService/TransactionVerification';
    
        $userName = "bdtaxUser2014";
        $password = "duUserPayment2014";
        $stCode = "bdtax";
        
        //-------------Change -------------------
        
    
         $credentials = array(
	            'userName'=> $userName,
	            'password'=> $password
	     );

         $header=array(
                    'Content-Type: application/json'
                );

         $data = array('AccessUser' =>$credentials, 
                   "OwnerCode"        => $stCode,
                   "ReferenceDate"    => substr($response_array["RefTranDateTime"],0,10),
                   "RequiestNo"       => substr($response_array["TransactionId"],6,10),
                   "isEncPwd"         => true             
         );
         
        //var_dump( $data);
        
        try{
            $url = curl_init($verifyurl);    
            $createpaybody= json_encode($data);
        
            curl_setopt($url,CURLOPT_HTTPHEADER, $header);
            curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_POST, 1);
            curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url,CURLOPT_SSLVERSION, 6);
            curl_setopt($url,CURLOPT_POSTFIELDS, $createpaybody);
            curl_setopt($url,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($url,CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);

            $result = curl_exec($url);
            //$content = curl_exec($url);
            $errmsg  = curl_error($url) ;
            $header  = curl_getinfo($url);
            curl_close($url);

            } catch (Error $e) {
            // Handle error
            //echo $e->getMessage(); // Call to a member function method() on string
            // die(curl_error($url));
            }
            
        
            $verifiedTransaction = json_decode($result, true);
           
       return $verifiedTransaction;
    }

?>