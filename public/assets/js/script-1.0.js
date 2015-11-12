/*
$(document).ready(function() { 

    google.maps.event.addDomListener(window, 'load', initialize);

    function initialize() {

        var latlng = new google.maps.LatLng(52.3731, 4.8922);

        var mapOptions = {
            center: latlng,
            scrollWheel: false,
            zoom: 13
        };

        var marker = new google.maps.Marker({
            position: latlng,
            url: '/',
            animation: google.maps.Animation.DROP
        });

        var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
        marker.setMap(map);

    };
});
*/

(function (window, $) {
    var $mapster = $('#map-canvas').mapster(Mapster.MAP_OPTIONS);
    
    $mapster.mapster('addMarker', {
       lat: 37.791350,
       lng: -122.435883,
       content: 'I am here'
    });
    

}(window, jQuery));