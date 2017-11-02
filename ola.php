<?php


/**
 *
 */
class Ola{

  private $url_ola_ws = 'http://www-qa1.ola.com.ar/nuevo/wsola/endpoint?wsdl';
  private $user_ws = 'userws';
  private $pass_ws = 'passws';
  private $cliente = '';

  function __construct(){
    $this->cliente =  new SoapClient($this->url_ola_ws);
  }

  //Front
  public function get_destinos(){

    $opciones = '<option value="%s">%s</option>';
    $grupo = '<optgroup label="%s">';
    $html = "";

    $result = $this->cliente->GetPackagesFaresDestinations(
    '    <GetPackagesFaresDestinationsRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <Outlet>1</Outlet>
          <PackageType>ALL</PackageType>
        </GetPackagesFaresDestinationsRequest>'
      );

    $xml = simplexml_load_string($result);

    foreach ($xml as $key => $value) {
      foreach ($value as $key => $zonas) {
        /*echo "Zonas: ";
        echo $zonas->Code . "</br>";
        echo $zonas->Name . "</br>";*/
        $html .= sprintf($grupo,  $zonas->Name);
        foreach ($zonas->Countries->Country as $key => $pais) {
        //  echo "Pais: " . $pais->Name . "</br>";
          $html .= sprintf($grupo,  $pais->Name);
          foreach ($pais->Cities->City as $key => $ciudad) {
          //  echo "Ciudad: " . $ciudad->Name . "</br>";
          $html .= sprintf($opciones, $ciudad->Code, $ciudad->Name);
          }
        }
      }
    }
    return $html;
  }

  public function get_salidas_disponibles($codigo_iata_destino){
    $result = $this->cliente->GetPackagesFaresDepartureDates(
    '<GetPackagesFaresDepartureDatesRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <Outlet>1</Outlet>
          <PackageType>ALL</PackageType>
          <ArrivalDestination>'.$codigo_iata_destino.'</ArrivalDestination>
        </GetPackagesFaresDepartureDatesRequest>'
    );
    $xml = simplexml_load_string($result);
    return $xml;
  }

  public function get_lista_paquetes_in_xml($destino,$fecha_disponible,$n_adultos,$n_menores){

    $passengers = "";

    for ($i=0; $i < $n_adultos ; $i++) {
      $passengers .= '<Passenger Type="ADL"/>"';
    }

    asort($n_menores);

    foreach ($n_menores as $key => $menor) {
      $passengers .= '<Passenger Type="CHD" Age="'.$menor.'"/>';
    }

    $result = $this->cliente->GetPackagesFares(
    '      <GetPackagesFaresRequest>
            <GeneralParameters>
              <Username>'.$this->user_ws.'</Username>
              <Password>'.$this->pass_ws.'</Password>
            </GeneralParameters>
            <DepartureDate>
             <From>'.$fecha_disponible.'</From>
             <To>'.$fecha_disponible.'</To>
           </DepartureDate>
           <Rooms>
             <Room>
              '.$passengers.'
             </Room>
           </Rooms>
            <ArrivalDestination>'.$destino.'</ArrivalDestination>
            <FareCurrency>ARS</FareCurrency>
            <Outlet>1</Outlet>
            <PackageType>ALL</PackageType>
          </GetPackagesFaresRequest>'
      );

     return simplexml_load_string($result);
  }

  public function get_paquete_individual_in_xml($booking_code){
    $result = $this->cliente->ValidateFare(
    '<ValidateFareRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <FareCode>'.$booking_code.'</FareCode>
          <FareCurrency>ARS</FareCurrency>
      </ValidateFareRequest>'
      );

      return simplexml_load_string($result);
  }

  public function set_reservar($array_info){
    $passengers_xml = '';
    for ($i=0; $i < count($array_info['tipo_persona']) ; $i++) {
      if ($array_info['tipo_persona'][$i] == 'adl') {
        if($i == 0){
          $passengers_xml .= '<Passenger Room="1" Type="ADL">
                                <DocumentType>'.$array_info['tipo_documento'][$i].'</DocumentType>
                                <DocumentNumber>'.$array_info['numero_documento'][$i].'</DocumentNumber>
                                <Gender>'.$array_info['sexo'][$i].'</Gender>
                                <BirthDate>'.$array_info['fecha_nacimiento'][$i].'</BirthDate>
                                <LastName>'.$array_info['apellido'][$i].'</LastName>
                                <FirstName>'.$array_info['nombre'][$i].'</FirstName>
                                <Nationality>'.$array_info['nacionalidad'][$i].'</Nationality>
                                <Residence>'.$array_info['pais_residencia'][$i].'</Residence>
                                <Phone>'.$array_info['telefono'].'</Phone>
                                <Cuit/>
                                <CuitType>CUIL</CuitType>
                              </Passenger>';

        }else{
          $passengers_xml .= '<Passenger Room="1" Type="ADL">
                                <DocumentType>'.$array_info['tipo_documento'][$i].'</DocumentType>
                                <DocumentNumber>'.$array_info['numero_documento'][$i].'</DocumentNumber>
                                <Gender>'.$array_info['sexo'][$i].'</Gender>
                                <BirthDate>'.$array_info['fecha_nacimiento'][$i].'</BirthDate>
                                <LastName>'.$array_info['apellido'][$i].'</LastName>
                                <FirstName>'.$array_info['nombre'][$i].'</FirstName>
                                <Nationality>'.$array_info['nacionalidad'][$i].'</Nationality>
                                <Residence>'.$array_info['pais_residencia'][$i].'</Residence>
                                <Cuit/>
                                <CuitType>CUIL</CuitType>
                              </Passenger>';

        }
      }elseif ($array_info['tipo_persona'][$i] == 'chd') {
        $passengers_xml .= '<Passenger Room="1" Type="CHD" Age="'.$array_info['edad'][$i].'">
                              <DocumentType>'.$array_info['tipo_documento'][$i].'</DocumentType>
                              <DocumentNumber>'.$array_info['numero_documento'][$i].'</DocumentNumber>
                              <Gender>'.$array_info['sexo'][$i].'</Gender>
                              <BirthDate>'.$array_info['fecha_nacimiento'][$i].'</BirthDate>
                              <LastName>'.$array_info['apellido'][$i].'</LastName>
                              <FirstName>'.$array_info['nombre'][$i].'</FirstName>
                              <Nationality>'.$array_info['nacionalidad'][$i].'</Nationality>
                              <Residence>'.$array_info['pais_residencia'][$i].'</Residence>
                              <Cuit/>
                              <CuitType>CUIL</CuitType>
                            </Passenger>';
      }
    }
    $result = $this->cliente->WriteBooking('
    <WriteBookingRequest>
        <GeneralParameters>
          <Username>'.$this->user_ws.'</Username>
          <Password>'.$this->pass_ws.'</Password>
        </GeneralParameters>
        <FareCode>'.$array_info['booking_code'].'</FareCode>
        <Passengers>
          '.$passengers_xml.'
        </Passengers>
        <Comment> </Comment>
      </WriteBookingRequest>
    ');
    return simplexml_load_string($result);
  }

  public function get_lista_reservas($pagina=1,$estado){
    $result = $this->cliente->GetBookings(
    '    <GetBookingsRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <Pagination>
            <CurrentPage>'.$pagina.'</CurrentPage>
            <ItemPerPage>5</ItemPerPage>
          </Pagination>
          <BookingStatus>'.$estado.'</BookingStatus>
          <ServiceType>PKG</ServiceType>
        </GetBookingsRequest>'
      );
    return simplexml_load_string($result);
  }

  //Gestion de Reservas
  public function cancelar_reserva($bookingcode){
    $result = $this->cliente->CancelBooking(
    '   <CancelBookingRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <BookingCode>'.$bookingcode.'</BookingCode>
        </CancelBookingRequest>'
      );

    $xml = simplexml_load_string($result);
    return $xml;
  }

  public function confirmar_reserva($bookingcode){
    $result = $this->cliente->ConfirmBooking(
    '   <ConfirmBookingRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <BookingCode>'.$bookingcode.'</BookingCode>
        </ConfirmBookingRequest>'
      );
    $xml = simplexml_load_string($result);
    return $xml;
  }

  //Outlet
  public function get_destinos_outlet(){
    $opciones = '<option value="%s">%s</option>';
    $grupo = '<optgroup label="%s">';
    $html = "";

    $result = $this->cliente->GetPackagesFaresDestinations(
    '    <GetPackagesFaresDestinationsRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <Outlet>2</Outlet>
          <PackageType>ALL</PackageType>
        </GetPackagesFaresDestinationsRequest>'
      );

    $xml = simplexml_load_string($result);
    $lista_destinos_outles = array();
    foreach ($xml as $key => $value) {
      foreach ($value as $key => $zonas) {
        foreach ($zonas->Countries->Country as $key => $pais) {
          foreach ($pais->Cities->City as $key => $ciudad) {
          //  echo "Ciudad: " . $ciudad->Name . "</br>";
          //$html .= sprintf($opciones, $ciudad->Code, $ciudad->Name);$ciudad->Code,$ciudad->Name
          array_push($lista_destinos_outles,array($ciudad->Code,$ciudad->Name));
          }
        }
      }
    }
    if(count($lista_destinos_outles)==1){
      $codigo_ciudad = $lista_destinos_outles[0][0];
    }elseif (count($lista_destinos_outles)==0) {
      $xml_destinos = 0;
    }else{
      $numero_final = count($lista_destinos_outles) - 1;
      $codigo_ciudad = $lista_destinos_outles[rand(0,$numero_final)][0];
    }
    return $codigo_ciudad;
  }

  public function get_salidas_disponibles_outlet($codigo_iata_destino){
    $result = $this->cliente->GetPackagesFaresDepartureDates(
    '<GetPackagesFaresDepartureDatesRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <Outlet>2</Outlet>
          <PackageType>ALL</PackageType>
          <ArrivalDestination>'.$codigo_iata_destino.'</ArrivalDestination>
        </GetPackagesFaresDepartureDatesRequest>'
    );
    $xml = simplexml_load_string($result);

    $numero_fechas = count($xml->DepartureDates->DepartureDate) - 1;
    $fecha_random = $xml->DepartureDates->DepartureDate[rand(0,$numero_fechas)];

    return $fecha_random;

  }

  public function get_lista_paquetes_outlet_in_xml($fecha,$destino){
    $result = $this->cliente->GetPackagesFares(
    '    <GetPackagesFaresRequest>
          <GeneralParameters>
            <Username>'.$this->user_ws.'</Username>
            <Password>'.$this->pass_ws.'</Password>
          </GeneralParameters>
          <DepartureDate>
            <From>'.$fecha.'</From>
            <To>'.$fecha.'</To>
          </DepartureDate>
          <Rooms>
            <Room>
              <Passenger Type="ADL"/>
              <Passenger Type="ADL"/>
            </Room>
          </Rooms>
          <ArrivalDestination>'.$destino.'</ArrivalDestination>
          <FareCurrency>ARS</FareCurrency>
          <Outlet>2</Outlet>
          <PackageType>ALL</PackageType>
        </GetPackagesFaresRequest>'
      );

     return simplexml_load_string($result);
  }

}
?>
