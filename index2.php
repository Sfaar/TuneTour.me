<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>My jQuery Ajax test</title>
  <style type="text/css">
    #mybox {
      width: 300px;
      height: 250px;
      border: 1px solid #999;
    }
  </style>
  <script src="http://code.jquery.com/jquery-1.11.0.js"></script>
  <script>
    function myCall() {
      var request = $.ajax({
        url: "rest.php?url=sadh",
        type: "GET",
        dataType: "html"
      });

      request.done(function(msg) {
        $("#mybox").html(msg);
      });

      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });
    }

  </script>
</head>
<body>
The following div will be updated after the call:<br />
<div id="mybox">

</div>
<input type="button" value="Update" onclick="myCall()" />

</body>
</html>