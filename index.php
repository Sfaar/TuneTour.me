<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 5//EN">
<html>
<head>
<meta charset="utf-8"/>
<meta name="google-site-verification" content="xbuRNTGkdLHrkm1w367xiWE_yI6HJV3KZAl_BzQwiSY"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href='http://fonts.googleapis.com/css?family=Raleway|Lato|Exo|Lobster|Lemon' rel='stylesheet' type='text/css'>
<link href="css/style.css" rel="stylesheet"/>
<title>TuneTour.me</title>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
<script src="http://code.jquery.com/jquery-1.11.0.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.fullPage.css"/>
<script src="js/jquery.easings.min.js"></script>
<script type="text/javascript" src="js/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="js/jquery.fullPage.min.js"></script>
<script src="js/jquery.watermarkinput.js" type="text/javascript"></script>
<script type="application/javascript">
var artistName = "";
var eventCount = -1;

function nameEncode(name) {
  name = name.replace("/", "%2F");
  return encodeURIComponent(encodeURIComponent(name));
}

function artistLookup() {
  $(".loadLater").hide();
  artistName = $("#artistNameInput").val();

  if (artistName === "enter artist's name" || artistName === "" || artistName === null) {
    $("#artistLookupResult").html("enter an artist's name");
    return;
  }
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
  if (artist.mbid === undefined || artist.mbid === null) {
    $("#artistLookupResult").html("we don't know about this artist; check spelling may be?");
  } else {
    var text = "";
    artistName = artist.name;
    $("#artistNameInput").val(artistName);
    eventCount = artist.upcoming_events_count;
    if (eventCount == 0) text = "looks like " + artistName + " is staying out of stage for a while";
    else if (eventCount == 1) text = artistName + " has one upcoming event";
    else text = artistName + " has " + eventCount + " upcoming events";
    $("#artistLookupResult").html(text);
    if (eventCount > 0) $("#listEventsButton").show();
  }
}

function listEvents() {
  $("#eventsList").html("listing tours for: " + artistName);
  var request = $.ajax({
    url: "get_hotels.php?artist_name=" + nameEncode(artistName),
    type: "GET",
    dataType: "text"
  });
  $("#eventsList").show();
  $("#eventsList").html("getting event detail<br/><img src='img/loading.gif' width='128pt;'/>");
  request.done(function (msg) {
    //$("#bigmapcanvas").show();
    $("#listEventsButton").hide();
    $("#eventsList").html(msg);
    //processEvents(msg);
  });
  request.fail(function (jqXHR, textStatus) {
    alert("Request failed: " + textStatus);
  });
}

function artistsArray2Text(artists) {
  var len = (artists.length - 1);
  var str = "";
  for (var i = 0; i < len; i++) {
    str += artists[i].name + ", ";
  }
  str += artists[i].name + "";
  return str;
}

function event2Html(event) {
  return "";

}

function processEvents(json) {
  var events = jQuery.parseJSON(json);
  if (events === undefined) {
    $("#eventsList").html("couldn't list events");
  } else {
    $("#eventsList").html("");
    for (var i = 0; i < events.length; i++) {
      $("#eventsList").append(event2Html(events[i]));
    }
  }
}

$.fn.pressEnter = function (fn) {
  return this.each(function () {
    $(this).bind('enterPress', fn);
    $(this).keyup(function (e) {
      if (e.keyCode == 13) {
        $(this).trigger("enterPress");
      }
    })
  });
};

/* Google Map JavaScript */
function setMarkers(map, locations) {
  for (var i = 0; i < locations.length; i++) {
    var beach = locations[i];
    var myLatLng = new google.maps.LatLng(beach[1], beach[2]);
    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      title: beach[0],
      zIndex: beach[3]
    });
  }
}

/* Google Map JavaScript */
function setMarkers2(map, locations) {
  for (var i = 0; i < locations.length; i++) {
    var beach = locations[i];
    var myLatLng = new google.maps.LatLng(beach[1], beach[2]);
    var contentString = beach[4];
    contentString = replaceAll('%2q%', '"', contentString);
    var infowindow = new google.maps.InfoWindow({
      content: contentString
    });
    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      title: beach[0],
      zIndex: beach[3]
    });
    google.maps.event.addListener(marker, 'click', function () {
      infowindow.open(map, marker);
    });
  }
}

function loadBigMap() {
  $("#bigmapShow").hide();
  $("#bigmapcanvas").height(500);
  var bigMapDataText = $("#bigMapData").text();
  var locations = JSON.parse(bigMapDataText);
  clatt = locations[0][1];
  clang = locations[0][2];
  var mapOptions = {
    zoom: 2,
    center: new google.maps.LatLng(clatt, clang)
  }
  var map = new google.maps.Map(document.getElementById('bigmapcanvas'), mapOptions);
  setMarkers(map, locations);
}

function replaceAll(find, replace, str) {
  return str.replace(new RegExp(find, 'g'), replace);
}

function loadHotelMap(elemName) {
  dtName = "#" + elemName + "dt";
  elemSelector = "#" + elemName;
  metaSelector = "#" + elemName + "meta";
  $(elemSelector).height(250);
  var mapData = $(dtName).text();
  var locations = JSON.parse(mapData);
  $(metaSelector).hide();
  clatt = locations[0][1];
  clang = locations[0][2];
  var mapOptions = {
    zoom: 12,
    center: new google.maps.LatLng(clatt, clang)
  }
  var map = new google.maps.Map(document.getElementById(elemName), mapOptions);
  setMarkers2(map, locations);
}

//use it:
$(document).ready(function () {
  $("#artistNameInput").val("").Watermark("enter artist's name", "#ccc");
  $('#artistNameInput').pressEnter(function () {
    artistLookup();
  });
  $(".loadLater").hide();
  $("#res").hide();
  $('#container').fullpage({
    anchors: [],
    autoScrolling: false,
    loopBottom: false,
    normalScrollElements: '#eventsList',
    navigation: false,
    scrollOverflow: true
  });
});
</script>
</head>
<body>
<div id="container">
  <div class="section" id="t2t" align="center">
    <div id="siteName"><img src="img/tt.png" height="160pt"><br/><h1>TuneTour.me</h1></div>

    <div>
      <input id="artistNameInput"/>
      <button id="artistButton" onclick="artistLookup()">&crarr;</button>
      <div id="artistLookupResult"></div>
      <button class="loadLater" id="listEventsButton" onclick="listEvents()">tell more about it</button>
    </div>
    <div id="eventsContainer" align="center">
      <div class="loadLater" id="eventsList"></div>
    </div>

    <footer>
      <div align="center">
        <div id="footerdiv">
          &copy; <strong><a href="http://tunetour.me">TuneTour.me</a></strong>; 2014<br/>
          brought to you by <a href="http://nafSadh.com">nafSadh-khan</a>, Amitav Paul and M Ruhul Amin | built in <a href="http://www.yhack.org">#YHack 2014</a><br/>
          powered with <a href="http://www.priceline.com">priceline.com API</a>,
          <a href="http://www.bandsintown.com/api/1.0/overview">Bandsintown Concert API</a>,
          <a href="http://api.eventful.com">eventful API</a> and
          <a href="https://developers.google.com/maps/">Google Maps JS API v3</a>
        </div>
      </div>
    </footer>
  </div>
</div>
<div id="res"></div>

</body>

</html>