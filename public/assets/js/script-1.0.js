(function (window, $) {
    var $mapster = $('#map-canvas').mapster(Mapster.MAP_OPTIONS);
    
    

    $mapster.mapster('addMarker', {
       lat: 37.791350,
       lng: -122.435883,
       content: 'I am here'
    });
    
    $('#docSearch').submit(function() {
        var self = $(this);

        $.ajax({
            url: 'doc/search',
            type: 'GET',
            data: self.serialize(),
        })
        .done(function(data) {
            console.log(data);
        })
        .fail(function() {
            console.log("error");
            alert("Something went wrong, Please try again later")
        })
        .always(function() {
            console.log("complete");
        });
        
    });

}(window, jQuery));