var blub;
$(window).load(function () {

    var activeRequest = 0;


    var map = new google.maps.Map(document.getElementById("axovis-map-canvas"), {
        mapTypeControl: false,
        // center: new google.maps.LatLng(parseInt($("#base_address_1 .lat").text()),parseInt($("#base_address_1 .long").text())),
        center: new google.maps.LatLng(35.6894, 139.692),
        navigationControl: false,
        scrollwheel: false,
        streetViewControl: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        infoWindow: new google.maps.InfoWindow({content: "."}),
        bounds: false
    });


    var mapDiv = $("#axovis-map-canvas");
    mapDiv.addClass("activated").css("height", Math.floor(mapDiv.width() / 16 * 9));

    var updateElements = {
        header: $(".emergencyService h2.host_container_head"),
        hiddenDay: $("#emergencyDay"),
        zip: $("#emergencyZip"),
        dateLine1: $("#emergencyService div.date .line1"),
        dateLine2: $("#emergencyService div.date .line2"),
        table: $('#emergencyService table.emergencyService'),
        loading: $('<div class="loading"><img src="' + ImgBasePath + 'ajax-loader.gif" /></div>').css({
            position: "absolute"
        })
    }

    var InitPharmacy = {
        lat: $("#base_address_1 .lat"),
        lon: $("#base_address_1 .lon"),
        name: $("#base_address_1 .name"),
        street: $("#base_address_1 .street"),
        zip: $("#base_address_1 .zip"),
        city: $("#base_address_1 .city"),
        minResult: $("#base_address_1 .minResult")
    }
    var markerImageNormal = ImgEmergencyPath + "default_field_pic_45.png";
    var markers = [];
    var monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    var weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];


    function loadService(servicePharmacies) {

        updateElements.loading.appendTo(mapDiv);

        var day = parseInt(updateElements.hiddenDay.val());
        var requestedDate = new Date();
        requestedDate.setTime(requestedDate.getTime() + (day * 86400000));

        // update nav

        switch (day) {
            case 0:
            case 1:
                updateElements.dateLine1.html(day == 0 ? "heute" : "morgen");
                updateElements.dateLine2.html(requestedDate.getDate() + '. ' + monthNames[requestedDate.getMonth()]);
                break;
            default:
                updateElements.dateLine1.html(weekdayNames[requestedDate.getDay()]);
                updateElements.dateLine2.html(requestedDate.getDate() + '. ' + monthNames[requestedDate.getMonth()]);
        }

        // var ajaxUrl = window.location.href + '?id=17006&dataReq=true&day=' + day;
        var zip = $.trim(updateElements.zip.val())
        if (zip.match(/^\d+$/))
        {
            updateElements.zip.val(zip);
            $('div.error').html('');
        }
        else if (zip != '' && zip != 'PLZ')
            $('div.error').html('Ungültige Postleitzahl!');
        
        map.bounds = new google.maps.LatLngBounds()

        // delete old markers
        var m;
        while (m = markers.pop())
            m.setMap(null);
    
	   $i_center_lat = InitPharmacy.lat.text();
	   $i_center_lon = InitPharmacy.lon.text();
	   

        // base/self marker
        markers = [
            new google.maps.Marker({
                position: new google.maps.LatLng(InitPharmacy.lat.text(), InitPharmacy.lon.text()),
            //   position: new google.maps.LatLng(35.6894,139.692),
                map: map,
                title: InitPharmacy.name.text(),
                infoData: '<b>' + InitPharmacy.name.text() + "</b><br >" + InitPharmacy.street.text() + "<br >" + InitPharmacy.zip.text() + " " + InitPharmacy.city.text()
            })
        ];
        if (updateElements.zip.val().match(/[0-9]{5}/g))
        {
            markers[0].infoData = '<b>' + InitPharmacy.city.text() + '</b>';
            markers[0].title = InitPharmacy.city.text();
        }

        map.radius = map.radius || new google.maps.Circle({
            fillOpacity: .1,
            strokeOpacity: .25,
            fillColor: "#FF0000",
            strokeColor: "#FF0000",
            map: map
        });

        map.radius.setCenter(markers[0].getPosition());
        map.radius.setRadius(servicePharmacies.data[0].radius * 1000);

        // remove old data from table
         updateElements.table.find("tr.eP").remove();        

        for (var i = 0; i < servicePharmacies.data.length; i++) {
            var p = servicePharmacies.data[i];
	    
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(p.lat, p.lon),
                icon: markerImageNormal,
                map: map,
                title: p.name,
                daddr: p.street + ' ' + p.zip + ' ' + p.city,
                infoData: '<div class="infoWindow"><div class="address"><b>' + p.name + "</b><br >" + p.street + "<br >" + p.zip + " " + p.city
            });
            if (p.district)
                marker.infoData += ' (' + p.district + ')';

            if (p.phone1)
                marker.infoData += "<br >Telefon: " + p.phone1;
	
	
	if(isNaN(p.start.substr(0, 1)) === false)
		{
		p.start = (weekdayNames[p.start.substr(0, 1)] + " " + p.start.substr(2));
		}
	if(isNaN(p.end.substr(0, 1)) === false)
		{
		p.end = (weekdayNames[p.end.substr(0, 1)] + " " + p.end.substr(2));
		}
	

            marker.infoData += '</div>';
            marker.infoData += '<div class="service" xstyle="margin: .5em 0;">von ' + p.start + ' bis ' + p.end + '</div>';
            marker.infoData += '<div class="icons">';
            marker.infoData += '<img class="zoomIn"     style="cursor: pointer;" src="' + ImgBasePath + 'magnifier_zoom_in.png" alt="näher" />';
            marker.infoData += '<img class="zoomOut"    style="cursor: pointer;" src="' + ImgBasePath + 'magnifier_zoom_out.png" alt="weiter" />';
            marker.infoData += '<img class="directions" style="cursor: pointer;" src="' + ImgBasePath + 'car.png" alt="weiter" />';
            marker.infoData += '</div>';
            marker.infoData += '</div>';
            // convert string to dom
            marker.infoData = $(marker.infoData)[0];

            markers.push(marker);


            var row = $('<tr class="eP" />').appendTo(updateElements.table);
            row.append('<td class="distance">' + p.distance.toString().replace(/\./, ',') + '</td>');
            row.append('<td class="names">' + p.name + '</td>');
            row.append('<td class="address">' + p.street + '<br />' + p.zip + ' ' + p.city + (p.phone1 ? '<br >Telefon: ' + p.phone1 : '') + '</td>');
            row.append('<td class="time">' + p.start + '<br />' + p.end + '</td>');
            var td = $('<td class="tools" />')
                    .append(
                            $('<img class="map" src="' + ImgBasePath + 'map.png" alt="in Karte" />')
                            .data('markerIndex', markers.length - 1)
                            .click(function () {
                                var i = $(this).data('markerIndex');
                                google.maps.event.trigger(markers[i], 'click');
				
				dc7__scroller.f_scroll({'jqo_target':jQuery('.__s_segment_type__emergency'), 'i_y_offset':80});
                            })
                            .css({cursor: 'pointer'})
                            )
                    .append(
                            $(' <img class="directions" src="' + ImgBasePath + 'car.png" alt="Route planen" />')
                            .data('markerIndex', markers.length - 1)
                            .click(function () {
                                var i = $(this).data('markerIndex');
                               window.open('http://maps.google.com/maps?daddr=' + markers[i].daddr, '_blank');
                            })
                            .css({cursor: 'pointer'})
                            )
                    .appendTo(row);
        }
        
        for (var i = 0; i < markers.length; i++) {

            google.maps.event.addListener(markers[i], 'click', function () {
                var marker = this;
                map.infoWindow.setContent(marker.infoData);
                map.infoWindow.open(map, marker);
                $("div.infoWindow .zoomIn").click(function () {
                    map.infoWindow.close();
                    map.setZoom(15);
                    map.setCenter(marker.getPosition());
                    return false;
                });
                $("div.infoWindow .zoomOut").click(function () {
                    map.infoWindow.close();
                    map.fitBounds(map.bounds)
                    return false;
                });
                $("div.infoWindow .directions").click(function () {
                    window.location.href = 'http://maps.google.com/maps?daddr=' + marker.daddr;
                    return false;
                });
            });

            map.bounds.extend(markers[i].getPosition());
        }

        map.fitBounds(map.bounds);

        updateElements.header.html('Notdienstplan für ' + updateElements.dateLine1.html() + ', ' + updateElements.dateLine2.html());

        updateElements.loading.remove();
	
	
	//map.setZoom(3);
	
	map.setCenter(new google.maps.LatLng($i_center_lat, $i_center_lon));
	map.setZoom(9);
	
	$(window).trigger('resize');
	
    }

    function moveDate(add)
    {
        var day = parseInt(updateElements.hiddenDay.val());
        var newDay = day += add;

        if (newDay == -1 || newDay == 8)
            return false;

        updateElements.hiddenDay.val(Math.min(Math.max(newDay, 0), 7));
        updateContent();

        return false;
    }

    $('#emergencyService .dateNext').click(function () {
        return moveDate(+1);
    });

    $('#emergencyService .datePrev').click(function () {
        return moveDate(-1);
    });

    $('#emergencyService input.submit').click(function () {
       getZipContent();
        return false;
    });

    updateElements.zip.keydown(function (event) {
        if (event.keyCode == '13') {
            getZipContent();
            event.preventDefault();
        }
    });

    $("div.zip label").css({display: 'none'});

    if ($("div.zip input.zip").val() == '')
    {
        $("div.zip input.zip")
                .val('PLZ')
                .css({color: 'grey'});
    }

    $("div.zip input.zip")
            .focus(function () {
                if ($(this).val() == 'PLZ')
                {
                    $(this)
                            .val('')
                            .css({color: 'black'});
                }
            })
            .blur(function () {
                if ($(this).val() == '')
                {
                    $(this)
                            .val('PLZ')
                            .css({color: 'grey'});
                }
            });

    $('<img class="reset" src=" ' + ImgBasePath + 'map_reset.png" alt="zur Ausgangsposition" />')
            .click(function () {
                map.infoWindow.close();
                map.fitBounds(map.bounds);
            })
            .appendTo("div.zip")
            .css({cursor: 'pointer'});


    function updateContent() {
        
        var ajaxUrl = 'Ajax/GetPharmacies';

        $.ajax({
           // data: '&datetoget=' + $("#emergencyDay").val() + '&lat=' + $('#base_address_1 .lat').text() + '&lon=' + $('#base_address_1 .lon').text() + '&radius=' + $('#base_address_1 .radius').text() +'&zip=' + updateElements.zip.val(),
            data: '&datetoget=' + $("#emergencyDay").val() + '&lat=' + InitPharmacy.lat.text() + '&lon=' + InitPharmacy.lon.text() + '&radius=' + $('#base_address_1 .radius').text() +'&zip=' + updateElements.zip.val() + '&minResult='+ InitPharmacy.minResult.text(),
            type: 'POST',
            url: ajaxUrl,
            dataType: "json",
            success: function (data) {
                if (data['status'] == "OK")
                {
                    loadService(data);
                }
                else
                {

                    $('#axovis-map-canvas').remove();
                    $('#emergencyService').html(data['message']);
                }
            },
            error: function (data) {

            }
        });        
    }
    
    function getZipContent() {
        
        var ajaxUrl = 'Ajax/zip/getZipContent';

        $.ajax({
            data: '&zip=' + updateElements.zip.val(),
            type: 'POST',
            url: ajaxUrl,
            dataType: "json",
            success: function (data) {
                if (data['status'] == "OK")
                {
                    InitPharmacy.lat.html(data.data.lat);
                    InitPharmacy.lon.html(data.data.lon);
                    InitPharmacy.city.html(data.data.ort);
                    updateContent();
                }
                else
                {

                    $('#axovis-map-canvas').remove();
                    $('#emergencyService').html(data['message']);
                }
            },
            error: function (data) {
                console.log("da geht es nicht rein!");
            }
        });
    }

    /*wird hier initial aufgerufen und in den einzelnen buttons */
    updateContent();
});