<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <!-- Compiled and minified CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">

        <!-- Compiled and minified JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
        <style>
            #map{
                width: 100%;
                height: 200px;
            }
        </style>
    </head>
    <body>
        <div class="row">
            <div class="col s12 m6">
                <div class="card white darken-1">
                    <div class="card-content black-text">
                        <span class="card-title">Card Title</span>
                        <div class="row">
                            <div class="col s12">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <i class="material-icons prefix">t</i>
                                        <input type="text" id="autocomplete-input" class="autocomplete">
                                        <label for="autocomplete-input">Autocomplete</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-action">
                        <a id="submit" href="#">This is a link</a>
                        <a href="#">This is a link</a>
                    </div>
                </div>
            </div>

            <div class="col s12 m6">
                <div class="card white darken-1">
                    <div class="card-content black-text">
                        <div id="map"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col s12 m6">
                <div class="card white darken-1">
                    <div class="card-content black-text">
                        <ul id="results" class="collection">
                            <li class="collection-item">Alvin</li>
                            <li class="collection-item">Alvin</li>
                            <li class="collection-item">Alvin</li>
                            <li class="collection-item">Alvin</li>
                        </ul> 
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="" id="street_number" name="street_number"  />
        <input type="hidden" value="" id="route" name="route"  />
        <input type="hidden" value="" id="locality" name="locality" >
        <input type="hidden" value="" id="administrative_area_level_1" name="administrative_area_level_1" />
        <input type="hidden" id="postal_code" name="postal_code" value="" />
        <input type="hidden" id="postal_code_amp" name="postal_code_amp" value="" />

        <input type="hidden" id="country" name="country" value="" />
        <input type="hidden" id="ciudad" name="ciudad" value="" />
        <input type="hidden" id="address" name="address" value="" />
        <input type="hidden" id="lat" name="lat" value="" />
        <input type="hidden" id="lng" name="lng" value="" />
        <script>
            function initMap() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: {lat: -34.397, lng: 150.644}
                });
                var geocoder = new google.maps.Geocoder();

                document.getElementById('submit').addEventListener('click', function () {
                    codeAddress(geocoder, map);
                });
            }
            var placeSearch, autocomplete;
            var componentForm = {
                street_number: 'short_name',
                route: 'long_name',
                locality: 'long_name',
                administrative_area_level_1: 'short_name',
                country: 'long_name',
                postal_code: 'short_name'
            };

            function codeAddress(geocoder, map) {
                var address = document.getElementById('autocomplete-input').value;
                geocoder.geocode({'address': address}, function (results, status) {
                    if (status == 'OK') {
                        var object = {};
                        document.getElementById('results').innerHTML = "";
                        for (var i = 0; i < results.length; i++) {

                            object[results[i].formatted_address] = results[i].formatted_address;
                            map.setCenter(results[i].geometry.location);
                            var marker = new google.maps.Marker({
                                map: map,
                                position: results[i].geometry.location
                            });
                            var split = results[i].formatted_address.split(",");
                           
                            for (var a = 0; a < results[i].address_components.length; a++) {
                                var addressType = results[i].address_components[a].types[0];
                                if (componentForm[addressType]) {
                                    var val = results[i].address_components[a][componentForm[addressType]];
                                    document.getElementById(addressType).value = val;
                                }
                            }
                            $.get('load_postal_code.php', {
                                'municipio': encodeURIComponent( document.getElementById("locality").value ),
                                'departamento': encodeURIComponent( document.getElementById("administrative_area_level_1").value ),
                                'direccion': encodeURIComponent( split[0] )
                            }, function (res) {
                                if(res.cp != null){
                                    document.getElementById('results').innerHTML += '<li class="collection-item">: ' + res.cp + '</li>';
                                }
                            }, 'json');
                            
                            geocoder.geocode({'location': results[i].geometry.location}, function (resultsb, status) {
                                for (var a = 0; a < resultsb[0].address_components.length; a++) {
                                    var addressType = resultsb[0].address_components[a].types[0];
                                    if (componentForm[addressType]) {
                                        var val = resultsb[0].address_components[a][componentForm[addressType]];
                                        console.log(val);
                                        document.getElementById(addressType).value = val;
                                    }
                                }
                            });
                            document.getElementById('results').innerHTML += '<li class="collection-item">: ' + document.getElementById('postal_code').value + '</li>';
//                            $.ajax({
//                                url:"http://www.codigopostal.gov.co/glow/param/"
//                            ,data:{
//                                'municipio':municipio,
//                                'departamento':departamento,
//                                'direccion':direccion
//                            },success:function (res){
//                                console.log(res);
//                                document.getElementById('results').innerHTML+='<li class="collection-item">'+results[i].formatted_address+'</li>';
//                            },type:'json');
//                            document.getElementById('results').innerHTML+='<li class="collection-item">'+results[i].formatted_address+'</li>';
                        }
                    } else {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                var elems = document.querySelectorAll('.autocomplete');
                var instances = M.Autocomplete.init(elems, {accion: 2, biner: 3, cortagrasa: 4});
            });
        </script>
        <script 
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCBsxpACJ13R7K1wusoi_R1owPIfxKEV4A&callback=initMap">
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    </body>
</html>