(function (window, $) {
    var $mapster = $('#map-canvas').mapster(Mapster.MAP_OPTIONS);
    
    $('#docSearch').submit(function(e) {
        e.preventDefault();
        var self = $(this);
        $('#left').html('<br><br><p><img src="http://searchforatopdoc.com/public/assets/images/loading.gif" alt="loader"></p>');
        $.ajax({
            url: 'doc/fetch.json',
            type: 'GET',
            data: self.serialize(),
        })
        .done(function(d) {
            //var d = JSON.parse(data);
            if(d.doctors) {
                //$('#left').html('<h2 class="text-center page-header">Top Doctors in '+ $('#location').val() +' for '+ $('#speciality').val() +'</h2>');
                $('#left').html('<h2 class="text-center page-header">Top Doctors </h2>');
                $.each(d.doctors, function(i, val) {
                    $mapster.mapster('addMarker', {
                       lat: val.location.lat,
                       lng: val.location.long,
                       content: val.doctor.name + ', '+ val.doctor.suffix + '<br>' + val.doctor.practice + '<br>' + val.location.address + ', ' + val.location.city
                    });
                    $('#left').append('<div class="media"><div class="media-body"><h4 class="media-heading">'+ val.doctor.name + ', '+ val.doctor.suffix +'</h4> '+ val.doctor.practice + '<br>' + val.location.address + ', ' + val.location.city +' </div></div>');
                });
            } else{
                $('#left').html('No Results Found, Please try some other location');
            };
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