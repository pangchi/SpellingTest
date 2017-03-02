<!DOCTYPE html>
<HTML>
<head>
    
  <title>Spelling Test</title>
  <meta charset="utf-8">
  <link href="jquery-ui/jquery-ui.css" rel="stylesheet">
  <script src="jquery-ui/external/jquery/jquery.js"></script>
  <script src="jquery-ui/jquery-ui.js"></script>

 <script>

<?php
	// load list of words and prepare audio clips
	
	$handle = fopen("word.txt", "r");
	$header = false;

	while (($line = fgetcsv($handle)) !== false)
        list($wordList[]) = $line;

	fclose($handle);
	
	//$wordList = array("Aeroplane","Zebra Crossing","Watermelon");
	
	//shuffle
	shuffle($wordList);

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
	
		//Key 1:8d6ddbbcbab84d23bbd7b1d5233aee05 Regenerate | Hide | Copy	
		//Key 2:fd179f4964c84ddca84914e3c0fc4f1d Regenerate | Hide | Copy		
		// Note: The way to get api key:
		// Free: https://www.microsoft.com/cognitive-services/en-us/subscriptions?productId=/products/Bing.Speech.Preview
		// Paid: https://portal.azure.com/#create/Microsoft.CognitiveServices/apitype/Bing.Speech/pricingtier/S0
		$apiKey = '8d6ddbbcbab84d23bbd7b1d5233aee05'; //"Your api key goes here";
		$ttsHost = 'https://speech.platform.bing.com';
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

//javascript

	var wordList = JSON.parse(<?php 	echo "'" . json_encode($wordList) . "'"; ?>);

//load mask
function overlay() {
	
	elem = document.getElementById("overlay");
	elem.style.visibility="hidden";
	elem = document.getElementById("bodydiv");
	elem.style.visibility="visible"; 
 }
	
//check function
function check() {

	var score = 0;
	
	for (i = 0; i < wordList.length; i++) {
		var youranswer = document.getElementById('answer' + i).value.toLowerCase().trim();
		var teacheranswer = wordList[i].toLowerCase();
		if (youranswer == "") {
			document.getElementById('check' + i).innerHTML = '';
		} 
		else if ( youranswer == teacheranswer) {
			document.getElementById('check' + i).innerHTML = '<strong style="color:green">&#x2714;</strong>';
			score++;
		} else { 
		document.getElementById('check' + i).innerHTML = '<strong style="color:red">&#x2718;</strong>';
		}
	}
	
	if (score ==  wordList.length) {
		document.getElementById('startover').style.display = 'inline';
		document.getElementById('check').style.display = 'none';
		document.getElementById('showanswer').style.display = 'none';
	}
	else {
		document.getElementById('showanswer').style.display = 'inline';
	}
}

function showanswer() {
	
	for (i = 0; i < wordList.length; i++) {
		document.getElementById('answer' + i).disabled = true;

		var youranswer = document.getElementById('answer' + i).value.toLowerCase();
		var teacheranswer = wordList[i].toLowerCase();
		
		if ( youranswer == teacheranswer) {
		}
		else {
			document.getElementById('correctanswer' + i).innerHTML = wordList[i];
		}
	
	}
		
		document.getElementById('startover').style.display = 'inline';
		document.getElementById('check').style.display = 'none';
		document.getElementById('showanswer').style.display = 'none';
}

function startover() {
	window.location.reload(true); 
}

</Script>

<style>
<!-- check link colour -->
A.check {text-decoration: none; color: green;}
A.check:link {text-decoration: none; color: green;}
A.check:visited {text-decoration: none; color: green;}
A.check:hover {text-decoration: underline; color: green;}
A.check:active {text-decoration: none; background-color: green; color: white;}

A.showanswer {text-decoration: none; color: red;}
A.showanswer:link {text-decoration: none; color: red;}
A.showanswer:visited {text-decoration: none; color: red;}
A.showanswer:hover {text-decoration: underline; color: red;}
A.showanswer:active {text-decoration: none; background-color: red; color: white;}

A.startover {text-decoration: none; color: gray;}
A.startover:link {text-decoration: none; color: gray;}
A.startover:visited {text-decoration: none; color: gray;}
A.startover:hover {text-decoration: underline; color: gray;}
A.startover:active {text-decoration: none; background-color: gray; color: white;}

#check {
    display: inline;
}

#startover {
    display: none;
}

#showanswer {
    display: none;
}

</style>

</head>

<body onLoad="overlay()">
<div class="ui-widget">
<div id="overlay" style="width:100%; height:100%; position: absolute;">
    <table><tr><td valign="center" height="100%" width="100%">
         <h1>Loading...</h1>
    </td></tr></table>
</div>

<div id="bodydiv" style="visibility:hidden;">


	
	<H1>Spelling Test</H1>
	
	<P>Note: Words in random sequence.</P>
	
	<FORM>

	<TABLE>
	<TH>No.</TH><TH>Voice</TH><TH>Answer</TH><TH></TH>
	
	<?php // prepare all audio files	
		for ($i = 0; $i < count($wordList); $i++) {
			echo "<TR>";
			
			$j = $i + 1;
			echo "<TD>". $j ."<input type=\"hidden\" name=\"word" . $i . "\" id=\"word" . $i . "\" value=\"" . $wordList[$i] . "\"></TD>";
			
			echo "<TD><audio controls=\"controls\">";
			$src = prepareClips($wordList[$i]);
			$srctype = substr($src, -3, 3);
			echo "<source src=\"" . $src . "\" type=\"audio/" . $srctype . "\">";
			echo "</audio></TD>";
			
			echo "<TD><input type=\"text\" name=\"answer" . $i . "\" id = \"answer" . $i . "\"></TD>";
			
			echo "<TD><SPAN id=\"check" . $i . "\"></SPAN></TD>";
			
			echo "<TD><SPAN id=\"correctanswer" . $i . "\"></SPAN></TD>";
						
			echo "</TR>";
			
		}
	?>
	</TABLE>
		
	</FORM>

	
	<h2>
	<a href="javascript:startover();" class="startover"><STRONG id="startover">START OVER</STRONG></a>
	<a href="javascript:check();" class="check"><STRONG id="check">CHECK<STRONG></a>&nbsp;&nbsp;
	<a href="javascript:showanswer();" class="showanswer"><STRONG id="showanswer">SHOW ANSWER</STRONG></a>&nbsp;&nbsp;
	</h2>
	
	<p>
	Build 2017-03-02
	</p>
</div>
</div>
</body>
</HTML>
