<!DOCTYPE html>
<HTML lang="en-GB">
<head>
    
  <title>Spelling Test</title>
  <meta charset="utf-8">
  <link href="jquery-ui/jquery-ui.css" rel="stylesheet">
  <script src="jquery-ui/external/jquery/jquery.js"></script>
  <script src="jquery-ui/jquery-ui.js"></script>

 <script>

<?php

include 'getclip.php';
include 'wordfilename.php';

	// load list of words and prepare audio clips
		
	$wordList = file($wordfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	//$wordList = array("Aeroplane","Zebra Crossing","Watermelon");
	
	//shuffle
	shuffle($wordList);
   
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
			
			echo "<TD><input type=\"text\" size=\"60\" name=\"answer" . $i . "\" id = \"answer" . $i . "\"></TD>";
			
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
	
	<p>BUILD 2017-04-16</p>
</div>
</div>
</body>
</HTML>
