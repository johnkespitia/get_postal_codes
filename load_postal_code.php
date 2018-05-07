<?php
echo file_get_contents('http://www.codigopostal.gov.co/glow/param/?municipio='. urlencode($_GET['municipio']).'&departamento='. urlencode($_GET['departamento']).'&direccion='. urlencode($_GET['direccion']).'');
