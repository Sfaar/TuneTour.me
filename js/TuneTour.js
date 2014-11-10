/**
 * Created by nafSadh on 09-Nov-14.
 */
var artistName = "";
var eventCount = -1;

function listTourEvents() {
  $("#eventsList").html("listing tours for: " + artistName);
  var request = $.ajax({
    url: "get.php?url=http://api.bandsintown.com/artists/" + nameEncode(artistName) + "/events.json?api_version=2.0%26app_id=Tail2Tune",
    type: "GET",
    dataType: "text"
  });
  $("#eventsList").show();
  $("#eventsList").html("getting event detail<br/><img src='img/loading.gif' width='128pt;'/>");
  request.done(function (msg) {
    $("#listEventsButton").hide();
    $("#eventsList").html("<div class='bigMapHolder'>"
    +"<div id='bigmapcanvas' ></div>"
    +"</div>");
    //$("#debug").html(msg);
    processTourEvents(msg);
  });
  request.fail(function (jqXHR, textStatus) {
    alert("Request failed: " + textStatus);
  });
}

function processTourEvents(json) {
  var events = jQuery.parseJSON(json);
  if (events === undefined) {
    $("#eventsList").html("couldn't list events");
  } else {
    var locations= [];
    for (var i = 0; i < events.length; i++) {
      //$("#debug").html(events[i]);
      //alert(events[i]);
      try {
        var event = events[i];
        var eventText = tourEventHtml(event, i);
        var eventTextOnMap = tourEventText(event,i);
        locations.push([event.venue.city, event.venue.latitude, event.venue.longitude,i, eventTextOnMap]);
        $("#eventsList").append(eventText);
        seekForNearbyEvents(event.venue.latitude, event.venue.longitude, event.datetime, i);
      }catch (err){
        //$("#eventsList").append(err);
      }
    }

    $("#bigmapcanvas").delay(17890).height(500);
    setMap("bigmapcanvas",locations,locations[0][1], locations[0][2],3);
  }
}

function setMap(mapCanvasId, locations, clatt, clgtt, zoomSize){
  var mapOptions = {
    zoom: zoomSize,
    center: new google.maps.LatLng(clatt, clgtt)
  }
  var map = new google.maps.Map(document.getElementById(mapCanvasId), mapOptions);
  setMapMarkers(map, locations);

}

/* Google Map JavaScript */
function setMapMarkers(map, locations) {
  for (var i = 0; i < locations.length; i++) {
    var loc = locations[i];
    var myLatLng = new google.maps.LatLng(loc[1], loc[2]);
    var content = loc[4];
    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      title: loc[0],
      zIndex: loc[3]
    });
    var infowindow = new google.maps.InfoWindow();
    google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){
      return function() {
        infowindow.setContent(content);
        infowindow.open(map,marker);
      };
    })(marker,content,infowindow));
  }
}


function seekForNearbyEvents(latt, lgtt, date, id){
  var d = moment(date).format('YYYYMMDD');
  var nd = moment(date).add(7, 'd').format('YYYYMMDD');
  var request = $.ajax({
    url: "getevents.php?latt="+latt+"&long="+lgtt+"&d="+d+"&nd="+nd,
    type: "GET",
    dataType: "text"
  });
  request.done(function (msg) {
    var elemId = '#nearEvents'+id;
    $(elemId).html(msg);
  });
  request.fail(function (jqXHR, textStatus) {
   // alert("Request failed: " + textStatus);
  });
}

function tourEventText(event, id){
  return ""
  + "<div class='eventText' id='eventOM"+id+"'>"
  + " <a href='#event"+id+"'>"
  + "   <span class='etDate'>"+dateTimeString(event.datetime,event.formatted_datetime)+"</span>"
  + " </a>"
  + " <span class='etVenuePlace'>"
  + "   <span class='etVenueCity'>"+event.venue.city+"</span>"
  +     regionCountryString(event.venue.region,event.venue.country)
  +"  </span>"
  + "<span class='etVenueName'>"+event.venue.name+"</span>"
  + "</div>";
}

function tourEventHtml(event, id){
  return   ""
    + "<div class='event' id='event"+id+"'>"
    + " <span class='date'>"+dateTimeString(event.datetime,event.formatted_datetime)+"</span>"
    + " <span class='city'>"+event.venue.city+"</span>"
    + " <span class='placeDetail'>"
    +     regionCountryString(event.venue.region,event.venue.country)
    + "  </span>"
    + " <div class='venueDiv'>"
    + "   <span class='venue'>"+event.venue.name+"</span>"
    + " </div>"
    +  eventDescription(event.description)
    +  ticketStatus(event)
    + " <div class='hotels'>"
    + " </div>"
    + " <div class='nearEvents' id='nearEvents"+id+"'>"
    + "   nearby events: <img src='img/loading16.gif' width='12pt'/>"
    + " </div>"
    + "</div>";
}

function regionCountryString(region, country){
  if(region==country || region==null || hasNumbers(region)){
    return "<span class='country'>"+country+"</span>";
  }else{
    return "<span class='region'>"+region+",</span> "
          +"<span class='country'>"+country+"</span>";
  }
}

function eventDescription(description){
  if (description && description!=null && description!="" )
    return " <span class='description'>"+event.description+"</span>";
  else return "";
}

function ticketStatus(event){
  if(event.ticket_url) {
    return "<span class='ticketInfo'>tickets <a href='" + event.ticket_url + "'>" + event.ticket_status + "</a></span>";
  }
  return "";
}

function dateTimeString(datetime, formattedDt){
  mmnt = moment(datetime);
  return "<time datetime='"+datetime+"'>"
      + mmnt.format("ddd MMM D, YYYY")
      +" <span class='time'>"+mmnt.format('hh:mma')+"</span>"
      +"</time>";
}

function hasNumbers(t)
{
  return /\d/.test(t);
}