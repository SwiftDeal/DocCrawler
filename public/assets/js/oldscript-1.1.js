(function (window, $) {
    var $mapster = $('#map-canvas').mapster({
        center: {
            lat: 37.79135,
            lng: -122.435
        },
        zoom: 10,
        cluster: true,
        geocode: true
    });
    
    $('.docSearch').submit(function(e) {
        e.preventDefault();
        var self = $(this);
        $('#left').html('<br><br><p><img src="http://searchforatopdoc.com/public/assets/images/loading.gif" alt="loader"></p>');
        $.ajax({
            url: 'doc/fetch.json',
            type: 'GET',
            data: self.serialize(),
        })
        .done(function(d) {
            if(d.doctors[0]) {
                $mapster.mapster('reCenter', {
                    lat: Number(d.doctors[0].location.lat),
                    lng: Number(d.doctors[0].location.long)
                });
                $('#left').html('<h2 class="text-center page-header">Search Results for Top Doctors</h2>');
                $.each(d.doctors, function(i, val) {
                    $mapster.mapster('addMarker', {
                       lat: Number(val.location.lat),
                       lng: Number(val.location.long),
                       content: val.doctor.name + ', '+ val.doctor.suffix + '<br>' + val.doctor.practice + '<br>' + val.location.address + ', ' + val.location.city
                    });
                    $('#left').append('<div class="media"><div class="media-body"><h4 class="media-heading">'+ val.doctor.name + ', '+ val.doctor.suffix +'</h4> '+ val.doctor.practice + '<br>' + val.location.address + ', ' + val.location.city +' </div></div>');
                });
            } else{
                $('#left').html('<br><br><p>Sorry No Results Found, Please try some nearest zip location, <a href="/">go back</a></p>');
            };
        })
        .fail(function() {
            console.log("error");
            alert("Something went wrong, Please try again later");
        })
        .always(function(d) {
            console.log(d.doctors.length);
        });
        
    });

}(window, jQuery));