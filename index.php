<?php
// Calculamos la hora actual y hacemos los ajustes para poder encajar en el periodo de tiempo del archivo xml de aemet.
	date_default_timezone_set('Europe/Madrid');	
	$Hora = date("H")+1;
	$fechaactualizacion = date('d/m/Y h:i:s a', time());

	// echo "Variable Hora=";
	// echo $Hora;
	// echo "\n\t";
	// echo "Variable fechaactualizacion=";
	// echo $fechaactualizacion;

	$p = 0; //Variable para calcular el periodo del dia[0] (hoy);
	$p1 = 0; //Variable para calcular el periodo en temperatura y humedad relativa.
	if ($Hora <= 6) { $p=$p+3; }
	if ($Hora > 6 && $Hora <= 12 ) { $p=$p+4;$p1=$p1+1; }
	if ($Hora > 12 && $Hora <= 18 ) { $p=$p+5;$p1=$p1+1; }
	if ($Hora > 18) { $p=$p+6;$p1=$p1+2; }
// Consultamos el archivo xml de aemet y recogemos los datos.
	$url = 'http://www.aemet.es/xml/municipios/localidad_28115.xml';
	$tiempo = simplexml_load_file($url);
// Si valor cielo es nulo ajustamos con el siguiente periodo. 	
	$cielo = $tiempo->prediccion->dia[0]->estado_cielo[$p];
	$sig = $p;
	while ($cielo == "") {
		$sig=$sig+1;
		$cielo = $tiempo->prediccion->dia[0]->estado_cielo[$sig];
	}
// Ajustamos el icono del cielo en funcion del dia y la noche. Aemet lo contempla pero no lo usa.
// Primero comprobamos si el codigo no tiene una n, si es asi comprobamos la hora y ajustamos.
	$hayn = strpos($cielo,'n');

// Debug de variables. Descomentar para ejecutar
	// echo "Variable cielo=";
	// echo $cielo;
	// echo "Variable hayn=";
	// echo $hayn;

	if ($hayn === false) { if ($Hora-1 <= 7 || $Hora-1 >= 10 ) { $cielo=$cielo+"n"; } }
// Generamos un xml personalizado con las condiciones actuales.
	header("Content-type: text/xml");
	echo "<?xml version='1.0' encoding='UTF-8'?>\n";
	echo "<aemet>\n\t";
	echo "<poblacion>\n\t";
	echo $tiempo->nombre;echo"\n\t";
	echo "</poblacion>\n\t";
	echo "<provincia>\n\t";
	echo $tiempo->provincia;echo"\n\t";
	echo "</provincia>\n\t";
	echo "<fechaactualizacion>\n\t";
	echo $fechaactualizacion;echo"\n\t";
	echo "</fechaactualizacion>\n\t";
	echo "<probprecipitacion>\n\t";
	echo $tiempo->prediccion->dia[0]->prob_precipitacion[$p];echo"\n\t";
	echo "</probprecipitacion>\n\t";
	echo "<cotanieve>\n\t";
	// Si no se aplica la cota nieve cambiamos el resultado para que no sea nulo.
	$nieve = $tiempo->prediccion->dia[0]->cota_nieve_prov[$p];
	if ($nieve == "") {$nieve = "-1";}
	echo $nieve;echo"\n\t";
	echo "</cotanieve>\n\t";
	echo "<cielo>\n\t";
	echo $cielo;echo"\n\t";
	echo "</cielo>\n\t";
	echo "<viento>\n\t\t";
	echo "<direc>\n\t\t";
	echo $tiempo->prediccion->dia[0]->viento[$p]->direccion;echo"\n\t\t";
	echo "</direc>\n\t\t";
	echo "<vel>\n\t\t";
	echo $tiempo->prediccion->dia[0]->viento[$p]->velocidad;echo"\n\t\t";
	echo "</vel>\n\t\t";
	echo "</viento>\n\t";
	echo "<tempmax>\n\t";
	echo $tiempo->prediccion->dia[0]->temperatura->maxima;echo"\n\t";
	echo "</tempmax>\n\t";
	echo "<tempmin>\n\t";
	echo $tiempo->prediccion->dia[0]->temperatura->minima;echo"\n\t";
	echo "</tempmin>\n\t";
	echo "<temp>\n\t";
	echo $tiempo->prediccion->dia[0]->temperatura->dato[$p1];echo"\n\t";
	echo "</temp>\n\t";
	echo "<humedad>\n\t";
	echo $tiempo->prediccion->dia[0]->humedad_relativa->dato[$p1];echo"\n\t";
	echo "</humedad>\n\t";
	echo "<uv>\n\t";
	echo $tiempo->prediccion->dia[0]->uv_max;echo"\n\t";
	echo "</uv>\n";
	echo "</aemet>\n";
?>
