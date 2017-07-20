<?php


// turn on for debug
/*
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
*/

$system = (isset($treff['biblioteksystem']) ? $treff['biblioteksystem'] : '');

// _log_log($treff);
if ($system == 'koha') {
  $utgitt = null;
  if ( $value = _is($treff,'utgitthvem') ){
    $utgitt = $treff['utgitthvem'];
  }
  if ( $value = _is($treff, 'utgitthvor') ) {
   $utgitt .= ", " . $value;
  }
  if ( $value = _is($treff, 'utgittaar') ) {
   $utgitt .= ", " . $value;
 }
}
else {
  $utgitt = (((isset($treff['utgitthvem'])) && (trim($treff['utgitthvem']) != "")) ? $treff['utgitthvem'] : "[s.n.]") . ', ' .
  (((isset($treff['utgitthvor'])) && (trim($treff['utgitthvor']) != "")) ? $treff['utgitthvor'] : '[s.l.]')
  . (((isset($treff['utgittaar'])) && (trim($treff['utgittaar']) != "")) ? ', ' . $treff['utgittaar'] : '');
}

if ($system != 'koha') {
  $ledige = 0;
  $uklar = false;
  $items = ( isset($treff['bestand']) ) ? $treff['bestand'] : null ;

  if ( $items && is_array($items) ) {
    foreach ($items as $index => $bestand) {
      if ( _is($bestand, 'h') == "0" || $index == 'h' && $bestand == '0' ){
       $ledige++;
      }
    }
  }
  else {
    $uklar = true;
  }
}


$post_info = maybe_unserialize( base64_decode($_GET['enkeltpostinfo']) );

if ( $system ){
  if ( $system == 'koha'){
    $bestilleurl = str_replace ("show" , "acquire" , $treff['permalink']);
  }
  else{
    $Booking = new MBBooking($post_info);
    $bestilleurl = $Booking->get('Url');
  }
}



?>
<div class="ils-single-result wl-catalog">
    <div class="wl-image-container">
      <?php if ((isset($treff['omslag'])) && ($treff['omslag'] != "")): ?>
        <img src="<?= $treff['omslag'] ?>" alt="<?= _is($treff, 'tittelinfo') ?>" />
      <?php else: ?>
          <img src="<?= getIconUrl('ikke_digital.png'); ?>" alt="<?= _is($treff, 'tittelinfo') ?>" />
      <?php endif; ?>
    </div>

    <div class="infocontainer">
        <h2><?= str_replace(": :" , ":", _is($treff, 'tittelinfo') ) ?></h2>
        <p>
            <?php if ((isset ($treff['forfatter'])) && ($treff['forfatter'] != "")): ?>
                <strong><?php _e('Forfatter', 'inter-library-search-by-webloft'); ?> : </strong><?= $treff['forfatter'] ?><br>
            <?php endif; ?>

            <?php if (!empty($utgitt)): ?>
                <strong><?php _e('Utgitt', 'inter-library-search-by-webloft'); ?> : </strong><?= $utgitt ?><br>
            <?php endif; ?>

            <?php if (!empty($treff['omfang'])): ?>
                <strong><?php _e('Omfang', 'inter-library-search-by-webloft'); ?> :</strong> <?= $treff['omfang'] ?><br>
            <?php endif; ?>

            <?php if ($system != 'koha' && isset($treff['bestand'])):  ?>
              <?php if ($ledige > 0): ?>
                  <div class="green dot" title="<?php _e('Ledig!', 'inter-library-search-by-webloft'); ?>"></div>&nbsp;<?php _e('Ledig', 'inter-library-search-by-webloft'); ?><br><br>
              <?php elseif ($uklar):  ?>
                  <div class="red dot" title="<?php _e('Uklar bestand', 'inter-library-search-by-webloft'); ?>"></div>&nbsp;<?php _e('Uklar bestand - kontakt biblioteket!', 'inter-library-search-by-webloft'); ?><br><br>
              <?php else: ?>
                  <div class="red dot" title="<?php _e('Ingen ledige!', 'inter-library-search-by-webloft'); ?>"></div>&nbsp;<?php _e('Ingen ledige...', 'inter-library-search-by-webloft'); ?><br><br>
              <?php endif; ?>
            <?php endif; ?>


            <?php if ( $pdf_link = _is($treff, 'pdfutdrag') ): ?>
                [<a href="<?= $pdf_link; ?>"><?php _e('Les utdrag', 'inter-library-search-by-webloft'); ?></a>]<br>
            <?php endif; ?>

            <?php if (in_array($system, array('koha')) && _is($treff, 'beskrivelse') ): ?>
                <?= $treff['beskrivelse'] ?><br>
            <?php endif; ?>



            <?php if (isset($treff['fulltekst']) || $bestilleurl): ?>
                <div class="buttons">
                    <?php if (isset($treff['fulltekst'])): /* finnes den p&aring; nett? */ ?>
                        <button class="link-online" onclick="location.href='<?= $treff['fulltekst'] ?>'"><?php _e('Les p&aring; nett', 'inter-library-search-by-webloft'); ?></button>
                    <?php endif; ?>
                    <?php if ($bestilleurl): ?>
                        <button class="link-order" onclick="location.href='<?= $bestilleurl ?>'"><?php _e('Bestille/reservere', 'inter-library-search-by-webloft'); ?></button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php $bestilleurl = false; $uklar = false; /* m&aring; rydde opp */ ?>

        </p>
    </div><!-- /.infocontainer -->

    <div class="clear"></div>
    <?php if ($system == 'koha' ):  ?>
        <?php if ((isset($treff['bestand'])) && (is_array($treff['bestand']))): /* bare hvis vi har bestandinfo */ ?>
            <div class="bestandcontainer">
                <h3><?php _e('Eksemplarer', 'inter-library-search-by-webloft'); ?>:</h3>
                <p>
                    <?php foreach ($treff['bestand'] as $bestand): ?>
                        <?= $bestand->institution
                                . (isset($bestand->collection) ? "&nbsp;/&nbsp;{$bestand->collection}" : '')
                                . (isset($bestand->callnumber) ? "&nbsp;/&nbsp;{$bestand->callnumber}" : '') ?>

                        :
                        <?php
                            echo bestandsinfo ($bestand->circulationStatus , $bestand->useRestriction); // status, restriction
                            if (($bestand->circulationStatus == "4") || ($bestand->circulationStatus == "5")) { // UTL&Aring;NT
                                setlocale (LC_TIME , "nb_NO"); // norsk dato
                                echo __(' til ', 'inter-library-search-by-webloft') . strftime("%e. %B %G" , strtotime($bestand['y']));
                            }
                        ?>
                        <br>
                    <?php endforeach; ?>
                </p>
            </div><!-- /.bestandcontainer -->
        <?php endif; ?>


    <?php else: /* EKSPERIMENTELL TAB-L&Oslash;SNING */ ?>

        <div class="tabs">
          <ul style="position: static; margin: 0;">
            <!-- <li class="active"><a href="#tab1"><?php _e('Eksemplarer', 'inter-library-search-by-webloft'); ?></a></li> -->
            <li class="active"><a href="#tab2"><?php _e('Beskrivelse', 'inter-library-search-by-webloft'); ?></a></li>
            <li><a href="#tab3"><?php _e('Flere opplysninger', 'inter-library-search-by-webloft'); ?></a></li>
          </ul>

            <div class="tab-content" style="margin-top: 0px;">
              <!-- <div id="tab1" class="tab active"></div> -->
              <div id="tab2" class="tab active">
                <?php if (isset($treff['beskrivelse']) && trim($treff['beskrivelse']) != ''): ?>
                    <p><?= $treff['beskrivelse'] ?></p>
                <?php endif; ?>
                <p>
                  <?php if (isset($treff['omfang']) && trim($treff['omfang']) != ''): ?>
                    <strong><?php _e('Omfang:', 'inter-library-search-by-webloft'); ?></strong> <?= $treff['omfang'] ?><br>
                  <?php endif; ?>
                </p>


                <?php if (isset($treff['bestand']) && is_array($treff['bestand'])): ?>

                <h3 class="item-info-title"><?php _e('Eksemplarer', 'inter-library-search-by-webloft'); ?></h3>
                <?php //_log($treff['bestand']); ?>
                  <?php foreach ($treff['bestand'] as $bestand): ?>

                  <?php if ($system == 'tidemann' || $system == 'bibliofil' || $system == 'mikromarc'): ?>

                <?php
                $temp = '';
                if ((isset($bestand['bibnavn'])) && ($bestand['bibnavn'] != '')) {
                  $temp[] = $bestand['bibnavn'];
                }
                if ((isset($bestand['b'])) && ($bestand['b'] != '')) {
                  $temp[] = $bestand['b'];
                }
                if ((isset($bestand['c'])) && ($bestand['c'] != '')) {
                  $temp[] = $bestand['c'];
                }
                $ferdig = implode (" / " , $temp);
                echo $ferdig;

                if ((!isset($bestand['h'])) || (!isset($bestand['f']))) { // sett til ukjent hvis ikke satt
                  $bestand['h'] = "1";
                  $bestand['f'] = "-1";
                }
                ?>
                  : <strong><?= getStockInformationByStatusCode($bestand['h'], $bestand['f']) /* status, restriction */ ?></strong>
                  <?php if (($bestand['h'] == "4") || ($bestand['h'] == "5")): /* UTL&Aring;NT */ ?>
                      <?php setlocale (LC_TIME , "nb_NO"); // norsk dato ?>
                      til <?= strftime("%e.%m.%G" , strtotime($bestand['y'])) ?>
                  <?php endif; ?>

                      <br>
                  <?php endif; ?>
                  <?php endforeach; ?>
              <?php endif; ?>
              <?php

              // Dekker hvis ebokbib eller hvis ingen eksemplarer
              if (!isset($treff['bestand']) || !is_array($treff['bestand'])) {
                if (isset($treff['ebokbibid']) && ($treff['ebokbibid'] != '')) {
                  echo '<a href="http://open.ebokbib.no/cgi-bin/sendvidere?mode=ebokbib&tnr=' . $treff['ebokbibid'] . '"><img class="ebokbiblogo" src="' .getIconUrl('ebokbib.png') .'" alt="EbokBib" /></a>' . __('Dette er en ebok som du m&aring; ha appen eBokBib for &aring; lese p&aring; nettbrett eller smarttelefon. Appen f&aring;r du i App Store (iOS) eller Google Play (Android). Klikk p&aring; logoen for &aring; l&aring;ne boka eller f&aring; mer informasjon.', 'inter-library-search-by-webloft');
                }
              }
              //  else {
              //   // echo "Ingen eksemplarer finnes!";
              // }
              ?>

              </div><!-- /#tab2 -->



              <div id="tab3" class="tab">
                <p>
                  <?php if (isset($treff['originaltittel'])): ?>
                      <strong><?php _e('Originaltittel:', 'inter-library-search-by-webloft'); ?></strong> <?= $treff['originaltittel'] ?><br>
                  <?php endif; ?>
                  <?php if ( is_array( _is($treff, 'dewey')) ): ?>
                      <strong><?php _e('Dewey:', 'inter-library-search-by-webloft'); ?></strong><?= implode (" / " , $treff['dewey']) ?><br>
                  <?php endif; ?>

                  <?php if ($system == 'tidemann' || $system == 'bibliofil'): ?>
                      <?php if (isset($treff['generellnote'])): ?>
                          <strong><?php _e('Generell note:', 'inter-library-search-by-webloft'); ?></strong> <?= (is_array($treff['generellnote']) ? implode (". ", $treff['generellnote']) : $treff['generellnote']) ?><br>
                      <?php endif; ?>
                      <?php if (isset($treff['innholdsnote'])): ?>
                          <strong><?php _e('Innholdsnote:', 'inter-library-search-by-webloft'); ?></strong> <?= (is_array($treff['innholdsnote']) ? implode (". ", $treff['innholdsnote']) : $treff['innholdsnote']) ?><br>
                      <?php endif; ?>
                      <?php if (isset($treff['medarbeidere'])): ?>
                          <strong><?php _e('Medvirkende:', 'inter-library-search-by-webloft'); ?></strong> <?= (is_array($treff['medarbeidere']) ? implode (". ", $treff['medarbeidere']) : $treff['medarbeidere']) ?><br>
                      <?php endif; ?>
                  <?php endif; ?>

                  <?php if (isset($treff['titler'])): ?>
                      <strong><?php _e('Tittelinformasjon:', 'inter-library-search-by-webloft'); ?></strong> <?= (is_array($treff['titler']) ? implode (" ; ", $treff['titler']) : $treff['titler']) ?><br>
                  <?php endif; ?>
                  <?php if (isset($treff['emneord'])): ?>
                      <strong><?php _e('Emneord:', 'inter-library-search-by-webloft'); ?></strong> <?= (is_array($treff['emneord']) ? implode (" ; ", $treff['emneord']) : $treff['emneord']) ?><br>
                  <?php endif; ?>

                  <?php if (($system != 'koha') && (isset($treff['ansvarsangivelse'])) && ($treff['ansvarsangivelse'] != "")): ?>
                  <strong><?php _e('Opphav', 'inter-library-search-by-webloft'); ?> : </strong><?= $treff['ansvarsangivelse'] ?><br>
                  <?php endif; ?>

                  <?php if ((isset($treff['isbn'])) && ($treff['isbn'] != "")): ?>
                    <strong><?php _e('ISBN', 'inter-library-search-by-webloft'); ?> :</strong> <?= $treff['isbn'] ?><br>
                  <?php endif; ?>
                  </p>
              </div><!-- /#tab3 -->

            </div><!-- .tab-content -->
        </div><!-- .tabs -->

    <?php endif; ?>

    <br style="clear: both;">
</div>
