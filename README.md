PHP class for OCA Express Pak. For more information and official support: http://webservice.oca.com.ar/oep_tracking/

The following operations are supported in the original API. For a formal definition, please review the Service Description.

- AnularOrdenGenerada 
Anulación de Orden de Retiro u Orden de Admisión
- GetCentroCostoPorOperativa 
Devuelve los Centros de Costo del cliente habilitas para ser utilizadon con la operativa indicada
- GetCentrosImposicion 
Devuelve todos los Centros de Imposición existentes
- GetCentrosImposicionPorCP 
Devuelve todos los Centros de Imposición existentes cercanos al CP
- GetEnviosUltimoEstado 
Detalle envíos entre fechas
- IngresoOR 
Ingreso de archivo de OR
- List_Envios 
Dado el CUIT del cliente con un rango de fechas se devuelve una lista con todos los Envíos realizados en dicho período
- Tarifar_Envio_Corporativo 
Tarifar un Envío Corporativo
- Tracking_OrdenRetiro 
Dado un nro. de Orden de Retiro, devuelve todas sus guías
- Tracking_Pieza 
Dado un envío se devuelven todos los eventos


header("Content-Type: text/html; charset=utf-8");
error_reporting(0);
include_once('oca.php');

$oca = new OCA($user=e-mail',
			   $pass='password',
			   $cuit='CUIT',
			   $nrocuenta='nrocuenta',
			   $userdireccion=array('calle'=>'','nro'=>'','piso'=>'','depto'=>'','cp'=>'','localidad'=>' ','provincia'=>' ','email'=>'','solicitante'=>' '),
			   $centrocosto='0', //139 = CI JOSE HERNANDEZ
			   $operativas=array('estandar'=>'nrooperativa','prioritario'=>'nrooperativa','inversa'=>'nrooperativa'));
				
// $centros = $oca->getCentrosImposicionPorCP('cp');
// print_r($centros);


// $listadoenvios = $oca->List_Envios('cuit','01-01-13','31-12-13');
// print_r($listadoenvios);


$paquetesarray[] = array('alto'=>'11','ancho'=>'11','largo'=>'11','peso'=>'0.200','valor'=>'100','cant'=>'1');

$IngresoOR = $oca->IngresoOR($ConfirmarRetiro = 'true',
							 $DiasRetiro = '1',
							 $FranjaHoraria = '1', //Debe indicar una franja horaria válida ([1]- de 8 a 17, [2]- de 8 a 12, [3]- de 14 a 17)
							 $operativa = 'estandar',
							 $nroremito = '123',
							 $apellido = 'Craiem',
							 $nombre = 'Mariano',
							 $direccionenvio = array('calle'=>'','nro'=>'','piso'=>'','depto'=>'','cp'=>'','localidad'=>' ','provincia'=>' '),
							 $email = 'mariano@bullpix.com',
							 $observaciones = '',
							 $paquetes = $paquetesarray);
print_r( $IngresoOR);

$tracking = $oca->Tracking_Pieza($IngresoOR[0]['NumeroEnvio']);
print_r($tracking);



