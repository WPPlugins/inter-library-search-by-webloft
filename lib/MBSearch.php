<?php

class MBSearch{
  public $Query;
  public $Result;
  public $Library;
  public $LibraryId;
  public $LibraryIndex;
  public $AdvancedQueryUrl;
  public $CoverServer;


  function __construct( $library, $query  ){
    $this->Library = $library;
    $this->LibraryId = _is($library, 'department_id');
    $this->Query = $query;
    $this->CoverServer = COVER_SERVER;

    $this->sanatizeQuery();
  }


  function _get($Attribute){
    return $this->$Attribute;
  }


  function setLibraryIndex( $index ){
    $this->LibraryIndex = $index;
  }

  function getLibraryIndex(){
    return $this->LibraryIndex;
  }

  function setLibrarySystem($system){
    $this->Library['system'] = $system;
    $this->sanatizeQuery();
  }

  function getLibrarySystem(){
    return $this->Library['system'];
  }

  function getLibraryValue($index){
    if ( _is($this->Library, $index)  ){
      return _is($this->Library, $index);
    }
    else{
      return null;
    }
  }

  function sanatizeQuery(){
    $system = $this->Library['system'];

    if ($system == 'bibliofil') { // frases&oslash;k i Bibliofil
      $this->Query = trim ($this->Query);
      $this->Query = str_replace(" &aring; ", " ",  $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" i ", " ",  $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" en ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" et ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" ei ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" og ", " ", $this->Query); // korte ord g&aring;r ikke


      if ((stristr($this->Query , " ")) && (!stristr($this->Query , "\"")) && (!stristr($this->Query , ","))) { // hvis flere ord UTEN ANF&Oslash;RSEL ELLER KOMMA setter vi AND mellom
        $this->Query = str_replace(" ", "+AND+", $this->Query);
      }
      $this->Query = str_replace ("." , "" , $this->Query);
      $this->Query = str_replace(" ", "+", trim($this->Query)); // kan ikke ha mellomrom i URL
      $this->Query = str_replace(",", "%2C+" , $this->Query);
      $this->Query = str_replace("\"", "%22", $this->Query); // fikse fnutter

      // Hvis komma er der er det invertert. Da m&aring; vi ha "" HVIS IKKE FRA F&Oslash;R
      if (stristr($this->Query, "%2C+") && !stristr($this->Query, "%22")) {
        $this->Query = "%22" . $this->Query . "%22";
      }
    }



    if ($system == 'mikromarc') { // frases&oslash;k i Mikromarc
      $this->Query = trim ($this->Query);
      $this->Query = str_replace(" &aring; ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" i ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" en ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" et ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" ei ", " ", $this->Query); // korte ord g&aring;r ikke
      $this->Query = str_replace(" og ", " ", $this->Query); // korte ord g&aring;r ikke

      if ((stristr($this->Query , " ")) && (!stristr($this->Query , "\""))) { // hvis flere ord UTEN ANF&Oslash;RSEL setter vi AND mellom samt en ny cql.anywhere (spesielt for Mikromarc)
        $this->Query = str_replace(" ", "+AND+cql.anywhere%3d", $this->Query);
        $this->Query = str_replace("%2A", "", $this->Query); // m&aring; fjerne * igjen hvis AND-s&oslash;k
      }
      $this->Query = str_replace (". " , " " , $this->Query); // space for ikke &aring; &oslash;delegge cql.anywhere
      $this->Query = str_replace(" ", "+", trim($this->Query)); // kan ikke ha mellomrom i URL
      $this->Query = str_replace(",", "%2C+" , $this->Query);
      $this->Query = str_replace("\"", "%22", $this->Query); // fikse fnutter
    }



    if ($system == 'tidemann') { // frases&oslash;k i Tidemann

      // hvis flere ord UTEN ANF&Oslash;RSEL setter vi AND mellom
      if ((stristr($this->Query , " ")) && (!stristr($this->Query , "\""))) {
        $this->Query = str_replace(" ", "+AND+", $this->Query);
      }

      if (stristr($this->Query, "\"")) {
    //    $this->Query = str_replace("\"", "", $this->Query); // fjerne anf&oslash;rsel
      }
      $this->Query = str_replace(" ", "+", trim($this->Query)); // kan ikke ha mellomrom i URL
    }

    if ($system == 'koha') { // frases&oslash;k i Koha
      if (stristr($this->Query, "\"")) {
        $this->Query   = str_replace("\"", "", $this->Query); // fjerne anf&oslash;rsel
        $kohafrase = 1; // frases&oslash;k aktivt - se lenger ned n&aring;r URL defineres.
      }
      $this->Query = urlencode($this->Query);
    }

    if ($system == 'bokhylla') {
      $this->Query = utf8_decode($this->Query);
      $this->Query = str_replace(" ", "+AND+", trim($this->Query)); // Dette er semi-frases&oslash;k
    }
  }

  function runQuery( $posisjon, $treffperside ){

    if ( $this->getLibrarySystem() == 'tidemann') {
      $url                          = $this->getLibraryValue('server') . "?version=1.2&operation=searchRetrieve&maximumRecords=" . $treffperside . "&recordSchema=marcxchange&query=" . $this->Query;
      $this->Result['items']        = tidemann_sok($url, $posisjon);
      $this->Result['count-items']  = tidemann_antalltreff($url);
    }

    if ( $this->getLibrarySystem() == 'bibliofil') {
      $url = MBSearch::buildQuery(
          $this->getLibraryValue('server') ,
          array(
            'version' => '1.2',
            'operation' => 'searchRetrieve',
            'maximumRecords' => $treffperside,
            'query' => 'cql.anywhere='. $this->Query,
            )
          );

      //$url                          = $this->getLibraryValue('server') . "?version=1.2&operation=searchRetrieve&maximumRecords=" . $treffperside . "&query=cql.anywhere=" . $this->Query;
      // _log($url);
      $this->Result['items']        = bibliofil_sok($url, $posisjon); // bruk vanlig s&oslash;k
      $this->Result['count-items']  = bibliofil_antalltreff($url);
    }

    if ( $this->getLibrarySystem() == 'alma') {
      $url                          = $this->getLibraryValue('server') . "?version=1.2&operation=searchRetrieve&maximumRecords=" . $treffperside . "&recordSchema=marcxml&query=alma.all_for_ui=" . $this->Query;
      $this->Result['items']        = alma_sok($url, $posisjon); // bruk vanlig s&oslash;k
      $this->Result['count-items']  = alma_antalltreff($url);
    }

    if ( $this->getLibrarySystem() == 'mikromarc') {
      $url =
        MBSearch::buildQuery(
          $this->getLibraryValue('server'),
          array(
            'httpAccept'      => 'text/xml',
            'version'         => '1.1',
            'operation'       => 'searchRetrieve',
            'maximumRecords'  =>  ( $treffperside <= 50 ) ? $treffperside : 50,
            'query'           => 'cql.anywhere='. $this->Query
          )
        );

      $this->Result = mikromarc_sok($url, $posisjon);
    }


    if ( $this->getLibrarySystem() == 'koha') {
      $url = $this->getLibraryValue('server') . "/cgi-bin/koha/opac-search.pl?idx=kw&q=" . $this->Query . "&count=" . $treffperside . "&sort_by=relevance&format=rss2";
      if ((isset($kohafrase)) && ($kohafrase == 1)) {
        $url = str_replace("idx=kw", "idx=kw%2Cphr", $url); // goto frases&oslash;k
      }
      $this->Result['items']   = koha_sok($url, $posisjon);
      $this->Result['count-items'] = koha_antalltreff($url);
    }

    if ( $this->getLibrarySystem() == 'bokhylla') {
      $url = MBSearch::buildQuery(
          NB_NO,
          array(
            'q' => $this->Query,
            'fq' => array(
              'mediatype:(B%C3%B8ker)',
              'contentClasses:(bokhylla%20OR%20public)',
              'digital:Ja'
              )
            )
          );


      $url =  "http://www.nb.no/services/search/v2/search?q=" . $this->Query . "&fq=mediatype:(B%C3%B8ker)&fq=contentClasses:(bokhylla%20OR%20public)&fq=digital:Ja";

      $this->Result['items']        = bokhylla_sok($url , $posisjon);
      $this->Result['count-items']  = bokhylla_antalltreff($url);
    }

    // _log($this->Result);
    return $this->Result;
  }


  public static function buildQuery($host, $query_args=array() ){
    $query = $host;

    $query_string = null;
    if ( is_array($query_args) ){
      foreach ($query_args as $parameter => $value) {

        if (is_string($value)||is_numeric($value)){
          $query_string .= self::buildQueryParameter($parameter, $value);
        }
        else if ( is_array($value) ){
          foreach ($value as $key => $sub_value) {
            $query_string .= self::buildQueryParameter($parameter, $sub_value);
          }
        }
      }
    }

    if ( $query_string ){
      $query .= "?".$query_string;
    }


    return $query;
  }


  public static function buildQueryParameter( $parameter, $value ){
     return sprintf('%s=%s&', $parameter, $value );
  }


  function setAdvancedQueryUrl(){
    if ( $this->getLibrarySystem() == 'bibliofil') {
      // http://www.akershus.fylkesbibl.no/cgi-bin/websok?mode=sok&st=a#soket
      $this->AdvancedQueryUrl = str_replace ("/sru" , "/websok" ,  $this->getLibraryValue('server') ) . "?mode=sok&st=a&pubsok_txt_0=" . $this->Query;
    }
    elseif ( $this->getLibrarySystem() == 'mikromarc') {
      $this->AdvancedQueryUrl = sprintf( "http://websok.mikromarc.no/Mikromarc3/web/search.aspx?ST=Form&Unit=%s&db=%s&SW=%s", $this->getLibraryValue('department_id') , $this->getLibraryIndex(),  $this->Query );
      // $this->AdvancedQueryUrl = "http://websok.mikromarc.no/Mikromarc3/web/search.aspx?SC=FT&SW=" . $this->Query . "&Unit=" . $this->getLibraryValue('department_id') . "&db=" . $this->getLibraryIndex();
    }
    elseif ( $this->getLibrarySystem() == 'tidemann') {
      // http://asp.bibliotekservice.no/flesberg/doclist.aspx?fquery=fr%3dnorge*+and+ba%3d001
      // http://asp.bibliotekservice.no/flesberg/search.aspx?type=0
      $this->AdvancedQueryUrl = str_replace ("_sru/nome.aspx" , "" ,  $this->getLibraryValue('server') ) . "/search.aspx?&type=0&fquery=fr%3d" . $this->Query . "*+and+ba%3d001";
    }
    elseif ( $this->getLibrarySystem() == 'koha') {
      $this->AdvancedQueryUrl =  $this->getLibraryValue('server') . "/cgi-bin/koha/opac-search.pl";
    }
    elseif ( $this->getLibrarySystem() == 'bokhylla') {
      $this->AdvancedQueryUrl = "http://www.bokhylla.no";
    }
    elseif ( $this->getLibrarySystem() == 'alma') {
      $this->AdvancedQueryUrl = "http://bibsys-almaprimo.hosted.exlibrisgroup.com/primo_library/libweb/action/dlSearch.do?institution=HBV&vid=" . $this->LibraryId . "&search_scope=default_scope&query=any,contains," . $this->Query;
    }
    else{
      $this->AdvancedQueryUrl = null;
    }

    return $this->AdvancedQueryUrl;
  }

  function setItemArray( $item ){
    return array(
      'ebokbibid'         => (isset($treff['ebokbibid']) ? $item['ebokbibid'] : ''),
      'omslag'            => (empty($item['omslag']) ? getIconUrl( 'ikke_digital.png' )  : $item['omslag']),
      'tittel'            => $item['tittelinfo'],
      'aar'               => ((isset($item['utgittaar'])) && ($item['utgittaar'] != '') ? $item['utgittaar'] : false),
      'url'               => $item['permalink'],
      'opphav'            => (isset($item['opphav']) ? $item['opphav'] : ''),
      'pdflenke'          => (isset($item['pdflenke']) ? $item['pdflenke'] : ''),
      'pdfutdrag'         => (isset($item['pdfutdrag']) ? $item['pdfutdrag'] : ''),
      'lenke'             => (isset($item['lenke']) ? $item['lenke'] : ''),
      'ansvarsangivelse'  => (isset($item['ansvarsangivelse']) ? $item['ansvarsangivelse'] : ''),
      'status'            => _is($item, 'status'),
      'materialtype'      => _is($item, 'type'),
      // Set empty default values to avoid 'undefined index' errors
      'isbn'              => '',
      'dewey'             => '',
      'omfang'            => '',
      'titteloriginal'    => '',
      'fulltekst'         => ((isset($item['fulltekst']) && ($item['fulltekst'] != '')) ? $item['fulltekst'] : false),
      'description'       => (isset($item['beskrivelse']) ? trunc($item['beskrivelse'], 40) : ''),
      );
  }

  function getItemInfoFromNationalLibraryByIsbn($item){
    $tempisbn = cleanIsbn($item['isbn']);
    // $omslag   = $this->CoverServer . "/isbn/" . $tempisbn . ".jpg";
    // if ( url_exists($omslag) ) { // cover url exists
    //   $item['omslag'] = $omslag;
    // }
    $item_info = null;
    $Result = $this->getItemInfoFromNationalLibrary('isbn', $item['isbn'] );

    $entry = _is($Result, 'entry');
    if ( is_array($entry) ){
      foreach ($entry as $item) {
        $namespaces = $item->getNameSpaces(true);
        $nb         = $item->children($namespaces['nb']); // alle som er nb:ditten og nb:datten
        $item['fulltekst'] = "http://urn.nb.no/" . $nb->urn;
      }
    }

    if ( $item ){
      $item_info = $item;
    }


    return $item_info;
  }


  function getItemInfoFromNationalLibraryByTitle($item){
    $item_info = null;
    $Result = $this->getItemInfoFromNationalLibrary('title',  urlencode($item['tittel']) );

    $entry = _is($Result, 'entry');
    if ( is_array($entry) ){
      foreach ($entry as $item) {
        $namespaces = $item->getNameSpaces(true);
        $nb         = $item->children($namespaces['nb']); // alle som er nb:ditten og nb:datten

        $item['fulltekst'] = "http://urn.nb.no/" . $nb->urn; // grabber lenke ogs&aring; med det samme

        // $omslag     = $this->CoverServer . "/urn/" . substr(($nb->urn), 8) . ".jpg";
        // if ((url_exists($omslag)) && ($nb->urn != '')) {
        //   $item['omslag']    = $omslag;
        // }
      }
    }

    if ( $item ){
      $item_info = $item;
    }

    return $item;
   }


  function getItemInfoFromNationalLibrary($key, $value){
    $search_query = "http://www.nb.no/services/search/v2/search?q=*&fq=".$key.":%22" . $value . "%22&fq=contentClasses:(public%20OR%20bokhylla)";
    $tybring   = get_content($search_query);

    return  simplexml_load_string($tybring);
  }


  function getItemInfoFromBokkilden($item){
    $isbnsearch     = "http://partner.bokkilden.no/SamboWeb/partner.do?format=XML&uttrekk=5&ept=3&xslId=117&enkeltsok=" . $item['isbn'];
    $panda          = get_content($isbnsearch);
    $firsttry       = simplexml_load_string($panda);
    $item['omslag'] = $firsttry->Produkt->BildeURL;
    $item['omslag'] = str_replace("&width=80", "&width=300", $item['omslag']); // knegg, knegg
    if (!isset($item['beskrivelse'])) {
      $item['beskrivelse'] = (string)$firsttry->Produkt->Ingress;
    }

    return $item;
  }


}
?>
