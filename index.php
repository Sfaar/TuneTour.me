<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5//EN">
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="google-site-verification" content="xbuRNTGkdLHrkm1w367xiWE_yI6HJV3KZAl_BzQwiSY"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Tail2Tune</title>

  <script src="http://code.jquery.com/jquery-1.11.0.js"></script>
  <script type="application/javascript">
    function artistClick(){

      var artistName = $("#artistInput").val();
      var artistNameEnc = (encodeURIComponent(encodeURIComponent(artistName)));
      $("#result").html("fetching tour data for: "+artistName);
      var request = $.ajax({
        url: "rest.php?url=http://api.bandsintown.com/artists/"+artistNameEnc+"/events.json?app_id=LoneTigers",
        type: "GET",
        dataType: "html"
      });

      request.done(function(msg) {
        $("#result").html(msg);
      });

      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });
    }

  </script>
</head>
<body>

<div>
    <input id="artistInput"/>
    <button id="artistButton" onclick="artistClick()">hit it</button>
  <div id="result">

  </div>
</div>

</body>

</html>