<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5//EN">
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="google-site-verification" content="xbuRNTGkdLHrkm1w367xiWE_yI6HJV3KZAl_BzQwiSY"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Tail2Tune</title>

  <style>
    .event span {padding: 4px 8px;}
  </style>
  <script src="http://code.jquery.com/jquery-1.11.0.js"></script>
  <script type="application/javascript">
    var artistName = "";
    var eventCount = -1;

    function nameEncode(name){
      name = name.replace("/","%2F");
      return encodeURIComponent(encodeURIComponent(name));
    }

    function artistLookup() {
      $(".loadLater").hide();
      artistName = $("#artistNameInput").val();
      $("#artistLookupResult").html("fetching tour data for: " + artistName);
      var request = $.ajax({
        url: "get.php?url=http://api.bandsintown.com/artists/" + nameEncode(artistName) + ".json?app_id=Tail2Tune",
        type: "GET",
        dataType: "text"
      });
      request.done(function (msg) {
        $("#res").html(msg);
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
      var request = $.ajax({
        url: "get.php?url=http://api.bandsintown.com/artists/" + nameEncode(artistName) + "/events.json?app_id=LoneTigers",
        type: "GET",
        dataType: "text"
      });
      request.done(function (msg) {
        $("#res").html(msg);
        processEvents(msg);
      });
      request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
      });
    }

    function artistsArray2Text(artists){
      var len = (artists.length-1);
      var str = "";
      for(var i=0; i<len; i++){
        str+= artists[i].name+", ";
      }str+= artists[i].name+"";
      return str;
    }

    function event2Html(event){
      var str = "<div class='event'>"
              +"<span class='venue'>"+event.datetime+"</span>"
              +"<span class='venue'>"+event.venue.name+"</span>"
              +"<span class='city'>"+event.venue.city+"</span>"
              +"<span class='city'>"+event.venue.region+"</span>"
              +"<span class='city'>"+event.venue.country+"</span>"
              +"<span class='ticket'>tickets "+event.ticket_status+"</span>"
              +"<span class='eventartists'>"+artistsArray2Text(event.artists)+"</span>"
          +"</div>";
      return str;

    }

    function processEvents(json){
      $("#eventsList").show();
      var events = jQuery.parseJSON(json);
      if(events===undefined){
        $("#eventsList").html("couldn't list events");
      }else{
        $("#eventsList").html("");
        for(var i = 0; i<events.length;i++) {
          $("#eventsList").append(event2Html(events[i]));
        }
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
  <div class="loadLater" id="eventsList"></div>
</div>
<div id="res"></div>

</body>

</html>