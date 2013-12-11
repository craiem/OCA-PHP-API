<?php

class Oca
{
	protected $webservice_url = 'http://webservice.oca.com.ar';

	function __construct($user,$pass,$cuit,$nrocuenta,$userdireccion,$centrocosto,$operativas){
            $this->usr = $user;
            $this->psw = $pass;
            $this->cuit = $cuit;
            $this->nrocuenta = $nrocuenta;
            $this->userdireccion = $userdireccion;
            $this->centrocosto = $centrocosto;
            $this->operativas = $operativas;

            if(!$this->usr){
	            trigger_error('User error');
            }
            if(!$this->psw){
	            trigger_error('Password error');
            }
    }

	// =========================================================================

	public function IngresoOR( $ConfirmarRetiro,$DiasRetiro,$FranjaHoraria,$operativa,$nroremito,$apellido,$nombre,$direccionenvio,$email,$observaciones,$paquetes)
	{
		//armo XML
		$xml = '<ROWS>
				<cabecera ver="1.0" nrocuenta="'.$this->nrocuenta.'" />
				<retiro calle="'.$this->userdireccion['calle'].'" nro="'.$this->userdireccion['nro'].'" piso="'.$this->userdireccion['piso'].'" depto="'.$this->userdireccion['depto'].'" cp="'.$this->userdireccion['cp'].'" localidad="'.$this->userdireccion['localidad'].'" provincia="'.$this->userdireccion['provincia'].'" contacto="'.$this->userdireccion['solicitante'].'" email="'.$this->userdireccion['email'].'" solicitante="'.$this->userdireccion['solicitante'].'" observaciones="" centrocosto="0" />
				<envios>
					<envio idoperativa="'.$this->operativas[$operativa].'" nroremito="'.$nroremito.'">
						<destinatario apellido="'.$apellido.'" nombre="'.$nombre.'" calle="'.$direccionenvio['calle'].'" nro="'.$direccionenvio['nro'].'" piso="'.$direccionenvio['piso'].'" depto="'.$direccionenvio['depto'].'" cp="'.$direccionenvio['cp'].'" localidad="'.$direccionenvio['localidad'].'" provincia="'.$direccionenvio['provincia'].'" telefono="" email="'.$email.'" idci="1" celular="" observaciones="'.$observaciones.'"/>
						<paquetes>';
						foreach ($paquetes as $paquete) {
							$xml .= '<paquete alto="'.$paquete['alto'].'" ancho="'.$paquete['ancho'].'" largo="'.$paquete['largo'].'" peso="'.$paquete['peso'].'" valor="'.$paquete['valor'].'" cant="'.$paquete['cant'].'" />';
						}
				$xml.='</paquetes>
					</envio>
				</envios>
			</ROWS>';


		
		$ch = curl_init();
		$postfields = array('usr'=>$this->usr,'psw'=>$this->psw,'XML_Retiro'=>$xml,'ConfirmarRetiro'=>$ConfirmarRetiro,'DiasRetiro'=>$DiasRetiro,'FranjaHoraria'=>$FranjaHoraria);

		$url = "{$this->webservice_url}/oep_tracking/Oep_Track.asmx/IngresoOR";

		curl_setopt_array($ch,	array(	CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_URL				=> $url,
										CURLOPT_POST			=> TRUE,
										CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_POSTFIELDS		=> http_build_query($postfields)));

		$result = curl_exec($ch);

		$dom = new DOMDocument();
		$dom->loadXML($result);


		$c_imp[] = array(	'CodigoOperacion'	=> $dom->getElementsByTagName('CodigoOperacion')->item(0)->nodeValue,
							'FechaIngreso'		=> $dom->getElementsByTagName('FechaIngreso')->item(0)->nodeValue,
							'mailUsuario'		=> $dom->getElementsByTagName('mailUsuario')->item(0)->nodeValue,
							'OrdenRetiro'		=> $dom->getElementsByTagName('OrdenRetiro')->item(0)->nodeValue,
							'NumeroEnvio'		=> $dom->getElementsByTagName('NumeroEnvio')->item(0)->nodeValue,
							'Remito'			=> $dom->getElementsByTagName('Remito')->item(0)->nodeValue,
							'Estado'			=> $dom->getElementsByTagName('Estado')->item(0)->nodeValue,
							'Operativa'			=> $dom->getElementsByTagName('Operativa')->item(0)->nodeValue,
							'Etiqueta'			=> '<a href="https://www1.oca.com.ar/ocaepak/Envios/EtiquetasCliente.asp?IdOrdenRetiro='.$dom->getElementsByTagName('OrdenRetiro')->item(0)->nodeValue.'&CUIT='.$this->cuit.'" target="_blank">Imprimir Etiqueta</a>'
							);
		return $c_imp;
	}

	// =========================================================================

	public function List_Envios($cuit = NULL,$FechaDesde = NULL, $FechaHasta = NULL)
	{
		if ( ! $cuit) return;
		
		$ch = curl_init();
		
		curl_setopt_array($ch,	array(	CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_HEADER			=> FALSE,
										CURLOPT_CONNECTTIMEOUT	=> 5,
										CURLOPT_POST			=> TRUE,
										CURLOPT_POSTFIELDS		=> 'CUIT='.$cuit.'&FechaDesde='.$FechaDesde.'&FechaHasta='.$FechaHasta,
										CURLOPT_URL				=> "{$this->webservice_url}/oep_tracking/Oep_Track.asmx/List_Envios",
										CURLOPT_FOLLOWLOCATION	=> TRUE));

		

		$dom = new DOMDocument();
		@$dom->loadXML(curl_exec($ch));
		$xpath = new DOMXpath($dom);
	
		$c_imp = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $ci)
		{
			$c_imp[] = array(	'NroProducto'	=> $ci->getElementsByTagName('NroProducto')->item(0)->nodeValue,
								'NumeroEnvio'			=> $ci->getElementsByTagName('NumeroEnvio')->item(0)->nodeValue
							);
		}
		
		return $c_imp;
	}

	// =========================================================================
	
	/**
	 * Devuelve todos los Centros de Imposición existentes cercanos al CP
	 * 
	 * @param integer $CP Código Postal
	 * @return type 
	 */
	public function getCentrosImposicionPorCP($CP = NULL)
	{
		if ( ! $CP) return;
		
		$ch = curl_init();
		
		curl_setopt_array($ch,	array(	CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_HEADER			=> FALSE,
										CURLOPT_CONNECTTIMEOUT	=> 5,
										CURLOPT_POST			=> TRUE,
										CURLOPT_POSTFIELDS		=> 'CodigoPostal='.(int)$CP,
										CURLOPT_URL				=> "{$this->webservice_url}/oep_tracking/Oep_Track.asmx/GetCentrosImposicionPorCP",
										CURLOPT_FOLLOWLOCATION	=> TRUE));

		$dom = new DOMDocument();
		@$dom->loadXML(curl_exec($ch));
		$xpath = new DOMXpath($dom);
	
		$c_imp = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $ci)
		{
			$c_imp[] = array(	'idCentroImposicion'	=> $ci->getElementsByTagName('idCentroImposicion')->item(0)->nodeValue,
								'IdSucursalOCA'			=> $ci->getElementsByTagName('IdSucursalOCA')->item(0)->nodeValue,
								'Sigla'					=> $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
								'Descripcion'			=> $ci->getElementsByTagName('Descripcion')->item(0)->nodeValue,
								'Calle'					=> $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
								'Numero'				=> $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
								'Torre'					=> $ci->getElementsByTagName('Torre')->item(0)->nodeValue,
								'Piso'					=> $ci->getElementsByTagName('Piso')->item(0)->nodeValue,
								'Depto'					=> $ci->getElementsByTagName('Depto')->item(0)->nodeValue,
								'Localidad'				=> $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
								'IdProvincia'			=> $ci->getElementsByTagName('IdProvincia')->item(0)->nodeValue,
								'idCodigoPostal'		=> $ci->getElementsByTagName('idCodigoPostal')->item(0)->nodeValue,
								'Telefono'				=> $ci->getElementsByTagName('Telefono')->item(0)->nodeValue,
								'eMail'					=> $ci->getElementsByTagName('eMail')->item(0)->nodeValue,
								'Provincia'				=> $ci->getElementsByTagName('Provincia')->item(0)->nodeValue,
								'CodigoPostal'			=> $ci->getElementsByTagName('CodigoPostal')->item(0)->nodeValue
							);
		}
		
		return $c_imp;
	}
	// =========================================================================
	
	/**
	 * Devuelve todos los Centros de Imposición existentes cercanos al CP
	 * 
	 * @param integer $CP Código Postal
	 * @return type 
	 */
	public function Tracking_Pieza($NumeroEnvio)
	{
		if ( ! $NumeroEnvio) return;

		$ch = curl_init();
		
		curl_setopt_array($ch,	array(	CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_HEADER			=> FALSE,
										CURLOPT_CONNECTTIMEOUT	=> 5,
										CURLOPT_POST			=> TRUE,
										CURLOPT_POSTFIELDS		=> 'pieza='.$NumeroEnvio.'&CUIT='.$this->cuit.'&NroDocumentoCliente=',
										CURLOPT_URL				=> "{$this->webservice_url}/oep_tracking/Oep_Track.asmx/Tracking_Pieza",
										CURLOPT_FOLLOWLOCATION	=> TRUE));
		$result =curl_exec($ch);
		$dom = new DOMDocument();
		@$dom->loadXML($result);
		$xpath = new DOMXpath($dom);
	
		$c_imp = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $ci)
		{
			$c_imp[] = array(	'Descripcion_Motivo'	=> $ci->getElementsByTagName('Descripcion_Motivo')->item(0)->nodeValue,
								'Desdcripcion_Estado'	=> $ci->getElementsByTagName('Desdcripcion_Estado')->item(0)->nodeValue,
								'SUC'					=> $ci->getElementsByTagName('SUC')->item(0)->nodeValue,
								'fecha'					=> $ci->getElementsByTagName('fecha')->item(0)->nodeValue);
		}
		
		return $c_imp;
	}
	
	// =========================================================================
	
	/**
	 * Devuelve todos los Centros de Imposición existentes
	 * 
	 * @return array $c_imp
	 */
	public function getCentrosImposicion()
	{
		$ch = curl_init();
		
		curl_setopt_array($ch,	array(	CURLOPT_RETURNTRANSFER	=> TRUE,
										CURLOPT_HEADER			=> FALSE,
										CURLOPT_CONNECTTIMEOUT	=> 5,
										CURLOPT_URL				=> "{$this->webservice_url}/oep_tracking/Oep_Track.asmx/GetCentrosImposicion",
										CURLOPT_FOLLOWLOCATION	=> TRUE));

		$dom = new DOMDocument();
		@$dom->loadXML(curl_exec($ch));
		$xpath = new DOMXpath($dom);
	
		$c_imp = array();
		foreach (@$xpath->query("//NewDataSet/Table") as $ci)
		{
			$c_imp[] = array(	'idCentroImposicion'	=> $ci->getElementsByTagName('idCentroImposicion')->item(0)->nodeValue,
								'Sigla'					=> $ci->getElementsByTagName('Sigla')->item(0)->nodeValue,
								'Descripcion'			=> $ci->getElementsByTagName('Descripcion')->item(0)->nodeValue,
								'Calle'					=> $ci->getElementsByTagName('Calle')->item(0)->nodeValue,
								'Numero'				=> $ci->getElementsByTagName('Numero')->item(0)->nodeValue,
								'Piso'					=> $ci->getElementsByTagName('Piso')->item(0)->nodeValue,
								'Localidad'				=> $ci->getElementsByTagName('Localidad')->item(0)->nodeValue,
							);
		}
		
		return $c_imp;
	}

}
