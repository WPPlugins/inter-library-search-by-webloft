<?php

class MBBooking{
  protected $System;
  protected $PostId;
  protected $LibraryId;
  protected $Url;


  function __construct( $post_info  ){
    if ( $system  = _is($post_info,'bibsystem') ){
      $this->set('System', $system);
    }

    if ( $postid  = _is($post_info,'postid') ){
      $this->set('PostId', $postid);
    }

    if ( $library_id  = _is($post_info,'bibkode') ){
      $this->set('LibraryId', $library_id);
    }


    $this->setUrl();
  }

  function set($Attribute, $value){
    $this->$Attribute = $value;
  }

  function get($Attribute){
    return $this->$Attribute;
  }

  function setUrl(){
    if ( $system = $this->get('System') ){
      if ( $system == 'mikromarc' ){
        $this->Url = $this->buildMikromarkUrl();
      }
      elseif ( $system == 'bibliofil' ){
        $this->Url = $this->buildBibliofilUrl();
      }
      elseif ( $system == 'tidemann' ){
        $this->Url = $this->buildTidemannUrl();
      }
      elseif ( $system == 'alma' ){
        $this->Url = '"http://bibsys-almaprimo.hosted.exlibrisgroup.com/primo_library/libweb/action/dlSearch.do?institution=HBV&vid=HBV&search_scope=default_scope&query=any,contains,71499882780002201"';
      }
      else{
        $this->Url = false;
      }
    }
  }


  function buildTidemannUrl(){
    global $libraries;

    include ( getConfigPath("library_list.php") );

    if ( _is($libraries, $this->LibraryId) ){

      $booking_url = $libraries[$this->LibraryId]['booking'];
      // i. e. http://www.bodo.folkebibl.no/cgi-bin/sru
      // $resource = 'mappami';
      // $query_string = '?jumpmode=reservering&tnr='.$this->PostId;

      // $base_url = str_replace('sru', $resource.$query_string, $base_url);

      return $booking_url;
    }
  }


  function buildBibliofilUrl(){
    global $libraries;

    include ( getConfigPath("library_list.php") );

    if ( _is($libraries, $this->LibraryId) ){

      $base_url = $libraries[$this->LibraryId]['server'];
      // i. e. http://www.bodo.folkebibl.no/cgi-bin/sru
      $resource = 'mappami';
      $query_string = '?jumpmode=reservering&tnr='.$this->PostId;

      $base_url = str_replace('sru', $resource.$query_string, $base_url);

      return $base_url;
    }
  }



  function buildMikromarkUrl(){
    $url = null;
    include ( getConfigPath("library_list.php") );
    if ( $library = _is($libraries, $this->LibraryId) ){

      // _log($library);
      $base_url = 'https://websok.mikromarc.no/Mikromarc3/Web/login.aspx';

      $query_args = array(
        'ReturnUrl' => '/Mikromarc3/web/member.aspx?Unit='.$library['department_id'].'&db='.$this->LibraryId,
        'Unit'      => $library['department_id'],
        'db'        => $this->LibraryId,
        'cookieset' => '1'
      );

      if ( $query_string = http_build_query($query_args) ){
        $url = $base_url.'?'.$query_string;
      }
    }
    // _log($url);
    return $url;
  }


}
?>
