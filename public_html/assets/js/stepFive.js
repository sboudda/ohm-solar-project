/**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */
// @ts-nocheck TODO remove when fixed
let map;
let markers = [];
let geocoder;
let response;
let address;
let nextbuttondiv;
let newposition;
let polygon;
let secondStepDiv;
let thirdStepDiv;
let fourStepDiv;
let area ;
let polyline;
let polylines = [];
let index ;
let marker;
let userLat;
let userLng;
function validateForm()
{
    address = document.getElementById('address').value;
    if(address)
    {
        toggle_element('hide','.invalid-feedback.address_msg')
        geocode({
            address: address
        })
        $('#roofnotfound').removeClass('hide');
        //show the button roof not found
        toggle_element('show','#roofnotfound')
    }
    else {
        toggle_element('show','.invalid-feedback.address_msg')
    }

    return false;
}

function toggle_element(flag, element) {
    if (flag == 'show') {
        $(element).removeClass('hide');
    } else {
        $(element).addClass('hide');
    }
}

function initMap11() {
    let lat = -34.397;
    let lng =  150.644;
    var mapOption =  {zoom: 8,
        center: {
            lat: lat,
            lng: lng
        },
        mapTypeControl: false,
        mapTypeId:'satellite',
        zoomControl: true,
        fullscreenControl: false,
        scaleControl: false,
        streetViewControl: false,
        tilt: 0,
        rotateControl: false


    }
    const options = {
        fields: ["formatted_address", "geometry", "name"],
        strictBounds: false,
        types: ["establishment"],
    };
    map = new google.maps.Map(document.getElementById("map"), mapOption);
    geocoder = new google.maps.Geocoder();
    const bounds = [
        { lat: lat, lng: lng + 0.0001 },
        { lat: lat, lng: lng },
        { lat: lat - 0.0001, lng: lng },
        { lat: lat-0.0001, lng: lng+ 0.0001 },
    ]
    /*  const autocomplete = new google.maps.places.Autocomplete(document.getElementById('address'), options);
      autocomplete.addListener("place_changed", () => {

          const place = autocomplete.getPlace();


      });*/

    //response = document.getElementById('response');
    //response.innerText = "";
    // Define a polygon
    var polygonOptions = {
        paths: bounds,
        visible:false,
        strokeColor: "#03fc03",
    }
    polygon = new google.maps.Polygon(polygonOptions);
    polygon.setMap(map);

    /* const clearButton = document.getElementById('clear');
     clearButton.addEventListener("click", () => {
         clear();
     });*/
    /*  nextbuttondiv = document.getElementById('nextbutton');
      const nextbotton = document.createElement("input")
      nextbotton.type = "button";
      nextbotton.value = "Suivant";
      nextbotton.classList.add("button", "button-secondary");
      nextbotton.addEventListener('click', () => {
          drawOnMap(newposition);
      });

      nextbuttondiv.appendChild(nextbotton);
      secondStepDiv = document.getElementById('secondbutton');
      thirdStepDiv = document.getElementById('thirdbutton');
      fourStepDiv =  document.getElementById('step_four');
      const secondbutton = createButton("Suivant");
      //event listener on the second button
      secondbutton.addEventListener('click', () => {
          //make the polygon unmoveable
          makerMovable(markers,false);
          // draw polyline
          drawPolyLine();
          //calculate the area of the roof
          calculateArea();
          //remove polygon now
          removePolygon();
          //hide the second step
          moveToStep("two", "three");

      })
      secondStepDiv.appendChild(secondbutton)

      const thirdbutton = createButton("Suivant");
      //event listener on the second button
      thirdbutton.addEventListener('click', () => {
          // save the selected vertices
          SaveChosenVertice();
          //moveToStep
          moveToStep("three", "four");
      });
      thirdStepDiv.appendChild(thirdbutton)*/
    // clear();
    map.addListener("click", (mapsMouseEvent) => {
        newposition = mapsMouseEvent.latLng
        var location =  document.getElementById('location');
        location.setAttribute('data-lat', newposition.lat())
        location.setAttribute('data-lng', newposition.lng())

        return newposition;
    });

}

function moveToStep(from, to)
{
    switch(from) {

        case "two" :
            //save addresse to localstorage
            localStorage.setItem("address", $('#address').val());
            //hide form
            $('#mainform').hide();
            secondStepDiv.style.display = "none";
            response.style.display = "block";
            /*response.innerText = response.innerText + "\n 4. Sélectioner le coté la plus élever de votre toiture.\n" +
                "La couleur changera en blanc une fois selecté.\n"
                + "Ensuite cliquer sur suivant.\n";*/
            response.innerText = response.innerText + "\n 4. "
                + "Cliquer sur suivant.\n";
            thirdStepDiv.style.display = "block";

            break;
        case "three":
            // code block
            thirdStepDiv.style.display = "none";
            //attach event to button  id buttonforthStep
            fourStepbutton =  document.getElementById('buttonforthStep');
            fourStepbutton.addEventListener('click', () => {
                //save orientation
                if(manageRadios())
                {
                    //move to step five
                    return moveToStep("four","five")
                }
            });
            response.style.display = "none";
            $(fourStepDiv).removeClass('hide');
            fourStepDiv.style.display = "block";
            //disable the click on the vertices now

            for (j = 0; j < polylines.length; j++) {
                google.maps.event.clearListeners(polylines[j], "click");
                // polylines[j].removeEventListener('click');
            }

            break;

        case "four":
            //step 5
            fourStepDiv.style.display = "none";
            //save all info
            window.location.href = "degreeinclinaision.php";

            break;
        case "five":
            //hide  response text
            toggle_element('hide','#response');
            // hide section one
            toggle_element('hide','#step_one');
            //hide the Recherchez button also
            toggle_element('hide','#mainform > .btn-primary');
            //show section roof not found
            toggle_element('show','#sectionroofnotfound')
            break;


        case "one":
            //hide  response text
            toggle_element('show','#response');
            // hide section one
            toggle_element('show','#step_one');
            //hide the Recherchez button also
            toggle_element('show','#mainform > .btn-primary');
            //show section roof not found
            //hide section roof not found
            toggle_element('hide','#sectionroofnotfound')

            break


        default:
            window.location.href = "degreeinclinaision.php";

    }
}
function SaveChosenVertice(){
    // get the chosen polyline
    index = localStorage.getItem("chosenPolylineIndex");
}
function saveShape()
{}

function removePolygon()
{
    polygon.setMap(null);
}
function drawPolyLine()
{
    var bounds = polygon.getPath();
    var coord = bounds.getArray()
    $first = coord[0];
    //coord.push($first);
    var point = [];
    point.push([coord[0], coord[1]]);
    point.push([coord[1], coord[2]]);
    point.push([coord[2], coord[3]]);
    point.push([coord[3], coord[0]]);

    for(i = 0; i < point.length; i ++)
    {
        var options = {
            path: point[i],
            geodesic: true,
            strokeColor: "#5fd59d",
            strokeOpacity: 1.0,
            strokeWeight: 5,
            clickable: true,
            editable:false,

        }
        polyline = new google.maps.Polyline(options);
        polyline.setMap(map);
        polylines.push(polyline);
        //On commente cette parti la pour l'instant
        // google.maps.event.addListener(polyline, "click", changeColor(polyline,i));
    }
    return polyline;
}

function manageRadios(form= false)
{
    var checked = 0;
    if(form ===false)
    {
        checked = $('#orientation-error').data('val');
        if (checked && checked != '') {
            checked = 1;
            $('#orientation-error').addClass('hide');
        } else {
            //display error
            $('#orientation-error').removeClass('hide');
            return false;
        }
        return true;
    }
    else
    {
        var ele = $(form).attr('id');
        var radio_buttons =  $('#'+ ele).find("input[name='exposition']");
        selected = $('#'+ ele).find(".exposition-container").find('.invalid-feedback').data('val');

        if(selected) {
            checked = 1;
            toggle_element('hide', $('#'+ ele).find(".exposition-container").find('.invalid-feedback'));
        }

        else
        {
            //display error
            toggle_element('show', $('#'+ ele).find(".exposition-container").find('.invalid-feedback'));
            return false;
        }
        return true;
    }
}

function changeColor(polyline, i) {
    //save chosen polyline index
    localStorage.setItem("chosenPolylineIndex", i );
    return function (event) {
        var options = {}
        for (j = 0; j < polylines.length; j++) {
            if (j == i) {
                options = {
                    strokeColor: "#ffffff",
                }

            } else {
                options = {
                    strokeColor: "#5fd59d",
                }
            }
            polylines[j].setOptions(options);
        }
    }
}
function createButton(buttonName)
{
    const button = document.createElement("input")
    button.type = "button";
    button.value = buttonName;
    button.classList.add("button", "button-secondary");
    return button;
}
function update_polygon_closure(polygon, i){
    return function(event){
        polygon.getPath().setAt(i, event.latLng);
    }
}
function makerMovable(markers, status = true)
{
    for (let i = 0; i < markers.length; i++) {
        markers[i].setDraggable(status);
    }
    return markers;
}
function calculateArea()
{
    // Use the Google Maps geometry library to measure the area of the polygon
    var area = google.maps.geometry.spherical.computeArea(polygon.getPath());

    return area.toFixed(2);
}
function drawOnMap(newposition, bounds){

    var icon = {
//path: google.maps.SymbolPath.CIRCLE,
        path: "M -1 -1 L 1 -1 L 1 1 L -1 1 z",
        strokeColor: "#FF0000",
        strokeOpacity: 0,
        fillColor: "#FF0000",
        fillOpacity: 1,
        scale: 5
    };

    let marker_options = {};
    // map.controls[google.maps.ControlPosition.LEFT_TOP].push(responseDiv);

    for (var i=0; i<bounds.length; i++){
        marker_options.position = bounds[i];
        marker_options.map = map;
        marker_options.draggable = true;
        marker_options.raiseOnDrag = false;
        marker_options.flat = true;
        marker_options.icon = icon;
        var point = new google.maps.Marker(marker_options);
        markers.push(point);
        google.maps.event.addListener(point, "drag", update_polygon_closure(polygon, i));
    }

}
function clear() {
    for (let i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
    polygon.setMap(null);

}

function geocode(request) {
    clear();
    geocoder
        .geocode(request)
        .then((result) => {
            const {
                results
            } = result;

            map.setCenter(results[0].geometry.location);
            //set the geocode here also
            $('#step_two_form_geocode_lat').val(results[0].geometry.location.lat())
            $('#step_two_form_geocode_lng').val(results[0].geometry.location.lng())
            //map.setZoom(20);
            marker.setPosition(results[0].geometry.location);
            marker.setMap(map);
            // move marker where user clicks
            google.maps.event.addListener(map, "click", (event) => {
                marker.setPosition(event.latLng);
                // update the geocode of the input element
                $('#step_two_form_geocode_lat').val(event.latLng.lat())
                $('#step_two_form_geocode_lng').val(event.latLng.lng())
            });




            return results;
        })
        .catch((e) => {
            alert("Geocode was not successful for the following reason: " + e);
        });
}

$(document).ready(function() {
    // intialise the compass
    $('#nav-form').CompassRose({
        pos: 55,
        location: 'locations_form',
        arrow_id: 'arrow_form',
        compass_class: 'inputCompass_form',
        image_class: 'imgB1_form',
        image_classA: 'imgA1_form',
        compass: compass.rose,
        arrow: compass.arrow
    });
});


//function to validate form_roof_not_found
function issetField(ele)
{
    if($(ele).length > 0 && $(ele).val() !='')
    {
        //show error msg
        toggle_element('hide', $(ele).next('.invalid-feedback'));

        return true;
    }
    else
    {
        toggle_element('show', $(ele).next('.invalid-feedback'));

        return false;
    }
}
//function to validate form_roof_not_found
function validateForm_roof_not_found(form)
{
    var status = 0;
    //check if orrientation is set
    if(manageRadios(form))
    {
        status =1;
    }
    //longeur

    if(issetField('#longeur'))
    {
        status = 1;
    }
    else {
        status = 0;
    }
    if(issetField('#largeur'))
    {
        status = 1;
    }
    else {
        status = 0;
    }

    if(status)
    {
        //save data to local storage
        //go to next step
        //longeur
        localStorage.setItem("longeur", $('#longeur').val());
        //largeur
        localStorage.setItem("largeur", $('#largeur').val());
        //address
        localStorage.setItem("address", $('#address').val());
        //calcule la surface caré du toit
        localStorage.setItem("area", ($('#longeur').val() * $('#largeur').val()).toFixed(2));
        //exposition is already save in localstorage
        // redirect to inclinaison
        window.location.href = "degreeinclinaision.php";



    }
    return false;

}



