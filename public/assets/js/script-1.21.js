(function(window, $) {

    $('.docSearch').submit(function(e) {
        e.preventDefault();
        $('#map-canvas').html('');

        if (!$mapster) {
            var $mapster = $('#map-canvas').mapster({
                center: {
                    lat: 37.79135,
                    lng: -122.435
                },
                zoom: 10,
                cluster: true,
                geocode: true
            });
        };

        var self = $(this);
        self.addClass('main-blog');
        $('#docSearch').html('<br><br><p><img src="http://searchforatopdoc.com/public/assets/images/loading.gif" alt="loader"></p>');
        $.ajax({
                url: 'doc/fetch.json',
                type: 'GET',
                data: self.serialize(),
            })
            .done(function(d) {
                if (d.doctors[0]) {
                    $mapster.mapster('reCenter', {
                        lat: Number(d.doctors[0].location.lat),
                        lng: Number(d.doctors[0].location.long)
                    });
                    $('#docSearch').html('<h4>Search Results for Top Doctors</h2>');
                    $('#docSearch').append('<br><br>');
                    $.each(d.doctors, function(i, val) {
                        $mapster.mapster('addMarker', {
                            lat: Number(val.location.lat),
                            lng: Number(val.location.long),
                            content: val.doctor.name + ', ' + val.doctor.suffix + '<br>' + val.doctor.practice + '<br>' + val.location.address + ', ' + val.location.city
                        });
                        $('#docSearch').append('<div class="blog-box"><div class="blog-text"><h4>' + val.doctor.name + '</h4><button class="btn-sm contact" data-input="' + val.doctor.name + ', ' + val.doctor.suffix + ', ' + val.doctor.practice + ',' + val.location.address + ', ' + val.location.city +'">Contact Number</button><div class="post-meta">' + val.doctor.suffix + ', ' + val.doctor.practice + '<div class="clear"></div></div><div class="post-text">' + val.location.address + ', ' + val.location.city + '</div><div class="post-text">' + val.doctor.bio + '</div></div></div>');
                    });
                } else {
                    $('#docSearch').html('<br><br><p>No Results Found, Please try some other location, <a href="/">go back</a></p>');
                };
            })
            .fail(function() {
                console.log("error");
                alert("Something went wrong, Please try again later");
            })
            .always(function(d) {
                //console.log(d.doctors);
            });

    });

    $(document).on('click', '.contact', function () {
        var self = $(this);
        self.html('Fetching...');
        $.ajax({
            url: '/doc/google',
            data: {input: self.data('input')},
        })
        .done(function(data) {
            var d = $.parseJSON(data);
            console.log(data);
            if(d) {
                if(d.hasOwnProperty("place_id")) {
                    $.ajax({
                        url: '/doc/googlePlace',
                        data: {placeid: d.place_id},
                    })
                    .done(function(p) {
                        var place = $.parseJSON(p);
                        self.html(place.international_phone_number);
                        alert(place.international_phone_number);
                    });
                } else{
                    self.html('No Number Found');
                }
            } else{
                self.html('No Number Found');
            }
            
        });
        
    });

}(window, jQuery));

var $ = jQuery.noConflict();
$(document).ready(function($) {

    /*-------------------------------------------------*/
    /* =  Dropdown Menu - Superfish
    /*-------------------------------------------------*/
    try {
        $('ul.sf-menu').superfish({
            delay: 400,
            autoArrows: false,
            speed: 'fast',
            animation: {
                opacity: 'show',
                height: 'show'
            }
        });
    } catch (err) {

    }

    /*-------------------------------------------------*/
    /* =  Mobile Menu
    /*-------------------------------------------------*/
    // Create the dropdown base
    $("<select />").appendTo("#nav");

    // Create default option "Go to..."
    $("<option />", {
        "selected": "selected",
        "value": "",
        "text": "Go to..."
    }).appendTo("#nav select");

    // Populate dropdown with menu items
    $(".sf-menu a").each(function() {
        var el = $(this);
        $("<option />", {
            "value": el.attr("href"),
            "text": el.text()
        }).appendTo("#nav select");
    });

    $("#nav select").change(function() {
        window.location = $(this).find("option:selected").val();
    });

    /*-------------------------------------------------*/
    /* =  Input & Textarea Placeholder
    /*-------------------------------------------------*/
    $('input[type="text"], textarea').each(function() {
        $(this).attr({
            'data-value': $(this).attr('placeholder')
        });
        $(this).removeAttr('placeholder');
        $(this).attr({
            'value': $(this).attr('data-value')
        });
    });

    $('input[type="text"], textarea').focus(function() {
        $(this).removeClass('error');
        if ($(this).val().toLowerCase() === $(this).attr('data-value').toLowerCase()) {
            $(this).val('');
        }
    }).blur(function() {
        if ($(this).val() === '') {
            $(this).val($(this).attr('data-value'));
        }
    });

    /*-------------------------------------------------*/
    /* =  Testimonials
    /*-------------------------------------------------*/

    try { //fade effect
        $('.bxslider.fade').bxSlider({
            adaptiveHeight: true,
            mode: 'fade',
            pager: false
        });
    } catch (err) {

    }

});
