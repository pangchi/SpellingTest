<?php

function prepareClips($w) {
// Convert Words (text) to Speech (OGG)
// ------------------------------------

   $words = $w;

// Name of the MP3 file generated using the MD5 hash
    $file  = md5($words);
  
// Define destination file
	//$filetype = 'mp3';
	$filetype = 'wav';
    $file = 'audio/' . $file . '.' . $filetype;

	
	// If file exists, do not create a new request
   if (!file_exists($file) || filesize($file) < 2048) {
		//$wav = getWordfromWatson($words);		//IBM Watson
		$wav = getWordfromCognitive($words);	//Microsoft Cognitive

		if ($filetype == 'wav') {
			file_put_contents($file, $wav);
		}
	 
		if ($filetype == 'mp3') {
		//convert to mp3 
			file_put_contents('int.wav', $wav);
			exec('bin\lame int.wav '. $file);
			exec('del int.wav');
		}
		
   }
   return $file;
}
   
   function getWordfromWatson($w) {
		$ch = curl_init();
		$username = '5f361d6c-8379-4f6e-9d51-2ae1ac6b720a';
		$password = '6uMGfeRvaUCz';
		
		// HTTP GET, non-alphanumeric must be formatted
		// Replace the non-alphanumeric characters 	
		// The spaces in the sentence are replaced with the Plus symbol
		$w = urlencode($w);
		
		//get wave .wav file
		$url = 'https://stream.watsonplatform.net/text-to-speech/api/v1/synthesize?accept=audio/wav&text='. $w .'&voice=en-GB_KateVoice';
		curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		
		// $output contains the output string 
        $output = curl_exec($ch); 
		
		// close curl resource to free up system resources 
        curl_close($ch);   

		Return $output;
   }
   
   function getWordfromCognitive($w) {
		$result = '';
		$AccessTokenUri = "https://api.cognitive.microsoft.com/sts/v1.0/issueToken";
	
		//Key 1:7d7dea02dfd5426db23dcba72439ce61 Regenerate | Hide | Copy	
		//Key 2:90ad2b03b32d42ab87cf7f053cfcc5e2 Regenerate | Hide | Copy		
		// Note: The way to get api key:
		// Free: https://www.microsoft.com/cognitive-services/en-us/subscriptions?productId=/products/Bing.Speech.Preview
		// Paid: https://portal.azure.com/#create/Microsoft.CognitiveServices/apitype/Bing.Speech/pricingtier/S0
		$apiKey = '7d7dea02dfd5426db23dcba72439ce61'; //"Your api key goes here";
		$ttsHost = 'https://speech.platform.bing.com/synthesize';
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Ocp-Apim-Subscription-Key: ".$apiKey."\r\n" .
				"content-length: 0\r\n",
				'method'  => 'POST',
			),
		);
		$context  = stream_context_create($options);
		
		//get the Access Token
		$access_token = file_get_contents($AccessTokenUri, false, $context);
		if (!$access_token) {
			throw new Exception("Problem with $AccessTokenUri, $php_errormsg");
		}
		
		else{
			echo "Access Token: ". $access_token. "<br>";
			$ttsServiceUri = "https://speech.platform.bing.com:443/synthesize";
			//$SsmlTemplate = "<speak version='1.0' xml:lang='en-us'><voice xml:lang='%s' xml:gender='%s' name='%s'>%s</voice></speak>";
			$doc = new DOMDocument();
			$root = $doc->createElement( "speak" );
			$root->setAttribute( "version" , "1.0" );
			$root->setAttribute( "xml:lang" , "en-GB" );
			$voice = $doc->createElement( "voice" );
			$voice->setAttribute( "xml:lang" , "en-GB" );
			$voice->setAttribute( "xml:gender" , "Female" );
			$voice->setAttribute( "name" , "Microsoft Server Speech Text to Speech Voice (en-GB, Susan, Apollo)" );
			$text = $doc->createTextNode( $w );
			//$text = $doc->createTextNode( "This is text" );
			$voice->appendChild( $text );
			$root->appendChild( $voice );
			$doc->appendChild( $root );
			$data = $doc->saveXML();
			echo "tts post data: ". $data . "<br>";
			$options = array(
				'http' => array(
				'header'  => "Content-type: application/ssml+xml\r\n" .
						"X-Microsoft-OutputFormat: riff-16khz-16bit-mono-pcm\r\n" .
						"Authorization: "."Bearer ".$access_token."\r\n" .
						"X-Search-AppId: 07D3234E49CE426DAA29772419F436CA\r\n" .
						"X-Search-ClientID: 1ECFAE91408841A480F00935DC390960\r\n" .
						"User-Agent: TTSPHP\r\n" .
						"content-length: ".strlen($data)."\r\n",
				'method'  => 'POST',
				'content' => $data,
				),
			);
			$context  = stream_context_create($options);
			// get the wave data
			$result = file_get_contents($ttsServiceUri, false, $context);
			if (!$result) {
				throw new Exception("Problem with $ttsServiceUri, $php_errormsg");
			}
			else{
			echo "Wave data length: ". strlen($result);
			}
		}
	Return $result;
   }

   ?>
   