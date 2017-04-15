<!DOCTYPE html>
<HTML lang="en-GB">
<head>

  <title>Spelling Test - Maintain</title>
  <meta charset="utf-8">
  <link href="jquery-ui/jquery-ui.css" rel="stylesheet">
  <script src="jquery-ui/external/jquery/jquery.js"></script>
  <script src="jquery-ui/jquery-ui.js"></script>


<?php
include 'wordfilename.php';

$wordList = file($wordfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$maxline = 20;

?>

<Script>

var maxline = <?php echo $maxline; ?>;

function commit() {

	var outstring = "";
		for (i = 0; i < maxline; i++) {
			document.getElementById('word' + i).disabled = true;
			outstring = outstring + document.getElementById('word' + i).value.trim() + "\r\n";
		}
		
	writefile(outstring);
}

function startover() {
	window.location.reload(true); 
}

function writefile(data) {
	
	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			document.getElementById("filestate").innerHTML = xmlhttp.responseText;
			}
	}

	var targetURI = "saveword.php";
	var content = "c=" + encodeURIComponent(data);
	
	//GET
		//xmlhttp.open("GET", targetURI + "?" + data, true);
		//xmlhttp.send();

		//test vector
		//xmlhttp.open("GET", "saveword.php", true);
		//xmlhttp.send();

	//POST
	xmlhttp.open("POST", targetURI, true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send(content);
}
</Script>

<style>
<!-- check link colour -->
A.startover {text-decoration: none; color: gray;}
A.startover:link {text-decoration: none; color: gray;}
A.startover:visited {text-decoration: none; color: gray;}
A.startover:hover {text-decoration: underline; color: gray;}
A.startover:active {text-decoration: none; background-color: gray; color: white;}

A.commit {text-decoration: none; color: green;}
A.commit:link {text-decoration: none; color: green;}
A.commit:visited {text-decoration: none; color: green;}
A.commit:hover {text-decoration: underline; color: green;}
A.commit:active {text-decoration: none; background-color: green; color: white;}

#startover {
    display: inline;
}

#commit {
    display: inline;
}

</style>

</head>

<body>
<div class="ui-widget">
	<TABLE>
	<TH>No.</TH><TH>Word</TH>
		
		<?php
		//prepare table
			for ($i = 0; $i < $maxline; $i++) {
				echo "<TR>";
				
				$j = $i + 1;
				echo "<TD>". $j ."</TD>";

				if (!isset($wordList[$i])) {
					echo "<TD><input type=\"text\" size=\"60\" name=\"word" . $i . "\" id=\"word" . $i . "\" value=\"\"></TD>";		
				}
				else
				{
					
				echo "<TD><input type=\"text\" size=\"60\" name=\"word" . $i . "\" id=\"word" . $i . "\" value=\"" . $wordList[$i] . "\"></TD>";
				}	
				
				echo "</TR>";
			}
		?>
				
	</TABLE>
	
	<H2>
		<a href="javascript:startover();" class="startover"><STRONG id="startover">START OVER</STRONG></a>&nbsp;&nbsp;
		<a href="javascript:commit();" class="commit"><STRONG id="check">COMMIT CHANGE<STRONG></a>
	</H2>

	<p><SPAN id="filestate">NOT SAVED</SPAN></p>
	
	<p>BUILD 2017-04-15</p>
</div>	
</body>
</HTML>
