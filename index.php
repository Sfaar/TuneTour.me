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
    var artistName = "";
    var eventCount = -1;
    function artistLookup() {
      artistName = $("#artistNameInput").val();
      var artistNameEnc = (encodeURIComponent(encodeURIComponent(artistName)));
      $("#artistLookupResult").html("fetching tour data for: " + artistName);
      var request = $.ajax({
        url: "get.php?url=http://api.bandsintown.com/artists/" + artistNameEnc + ".json?app_id=Tail2Tune",
        type: "GET",
        dataType: "text"
      });
      request.done(function (msg) {
        processLookupResult(msg);
      });
      request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
      });
    }

    function processLookupResult(json) {
      var artist = jQuery.parseJSON(json);
      if(artist.mbid===undefined || artist.mbid===null){
        $("#artistLookupResult").html("we don't know about this artist; check spelling may be?");
      }else{
        var text = "";
        artistName = artist.name;
        eventCount = artist.upcoming_events_count;
        if(eventCount==0) text = "looks like "+artistName+" is staying out of stage for a while";
        else if(eventCount==1) text = artistName+" has one upcoming event";
        else text = artistName+" has "+eventCount+" upcoming events";
        $("#artistLookupResult").html(text);
        $("#listEventsButton").show();
      }
    }

    function listEvents() {
      $("#eventsList").html("listing tours for: " + artistName);
      var artistNameEnc = (encodeURIComponent(encodeURIComponent(artistName)));
      var request = $.ajax({
        url: "get.php?url=http://api.bandsintown.com/artists/" + artistNameEnc + "/events.json?app_id=LoneTigers",
        type: "GET",
        dataType: "text"
      });
      request.done(function (msg) {
        processEvents(msg);
      });
      request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
      });
    }

    function processEvents(json){
      var events = jQuery.parseJSON(json);
      if(events===undefined){
        $("#eventsList").html("couldn't list events");
      }else{
        $("#eventsList").html(json);
      }
    }

    $(document).ready(function () {
      $(".loadLater").hide();
    });
  </script>
</head>
<body>

<div>
  Artist: <input id="artistNameInput"/>
  <button id="artistButton" onclick="artistLookup()">hit it</button>
  <div id="artistLookupResult"></div>
  <button class="loadLater" id="listEventsButton" onclick="listEvents()">list upcoming events</button>
  <div id="eventsList"></div>
</div>

</body>

</html>