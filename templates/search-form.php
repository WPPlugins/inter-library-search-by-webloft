<?php
  // er $trefflisteside satt i shortcode? I s&aring;fall m&aring; vi lage ny target for form
  $resources = (isset($resources) ) ? $resources : 'ALLE';
  $skjulesoketips = (isset($skjulesoketips) ) ? $skjulesoketips : '1';

if (@$trefflisteside > 0) {
  $resultatperma = get_permalink($trefflisteside);
  if ($resultatperma != "") { // klarte &aring; finne permalink?
    $sjokk = explode ("?" , $resultatperma); // alle query strings i $sjokk[1] HVIS DET ER NOEN
    $formaction = $sjokk[0];
    $formtarget = "_top";
    $spinnkode = "";
  }
}
else {
  $formaction = getTemplateUrl('search.php') ;
  $formtarget = "reglitre_treff_frame";
  $spinnkode = " onSubmit=\"showreglitreLoading();\"";
}
?>


<div class="ils-search-form">
  <form<?= $spinnkode ?> id="webloftform" target="<?= $formtarget ?>" action="<?= $formaction ?>" method="GET">
    <label for="search" class="wlkatalog_sokeord"><?php _e('S&oslash;keord:', 'inter-library-search-by-webloft'); ?>&nbsp;</label>
    <input type="text" value="<?= $has_get_query ?>" id="search" name="webloftsok_query" accept-charset="utf-8" />&nbsp;<input type="submit" value="<?php _e('S&oslash;k', 'inter-library-search-by-webloft');?>">
    <input type="hidden" name="library_id" value="<?= $library_id ?>" />
    <input type="hidden" name="omslagbokkilden" value="<?= $omslagbokkilden ?>" />
    <input type="hidden" name="omslagnb" value="<?= $omslagnb ?>" />
    <input type="hidden" name="treffbokhylla" value="<?= $treffbokhylla ?>" />
    <input type="hidden" name="hamedbilder" value="<?= $hamedbilder ?>" />
    <input type="hidden" name="treffperside" value="<?= $results_per_page ?>" />
    <input type="hidden" name="hoyretrunk" value="<?= $hoyretrunk ?>" />
    <input type="hidden" name="dobokhylla" value="0" />
    <input type="hidden" name="viseavansertlenke" value="<?= $viseavansertlenke ?>" />
    <input type="hidden" name="enkeltpostnyttvindu" value="<?= $enkeltpostnyttvindu ?>" />
    <input type="hidden" name="kilder" value="<?php echo $resources ?>" />
    <input type="hidden" name="skjulesoketips" value="<?php echo $skjulesoketips ?>" />

<?php
  if (isset($sjokk[1])) { // Fantes det parametre p&aring; den trefflistesiden?
    $parameters = explode ("&" , $sjokk[1]); // array med parametre
    if (is_array($parameters)) {
      foreach ($parameters as $parameter) {
        $ettparameter = explode ("=" , $parameter);
        echo "<input type=\"hidden\" name=\"" . $ettparameter[0] . "\" value=\"" . $ettparameter[1] . "\" />";
      }
    }
  }
?>

<?php if (trim($enkeltpost) != ""): ?>
  <input type="hidden" name="enkeltposturl" value="<?= base64_encode(get_permalink($enkeltpost)) ?>" />
<?php endif; ?>
  </form>
</div>

<div id="divreglitreLoading" style="text-align: center; margin-top: 20px;">
  <img style="border: none; box-shadow: none;" src="<?= getIconUrl('spinner.gif'); ?>" alt="<?php _e('Laster...', 'inter-library-search-by-webloft'); ?>" />
</div>

<div id="divreglitreFrameHolder" style="display:none; margin-top: 20px;">
  <iframe<?= $framekode ?> name="reglitre_treff_frame" onLoad="hidereglitreLoading();" id="ils_results_frame" frameborder="0" width="100%" style="padding: 0; border: 0;" webkitallowfullscreen mozallowfullscreen allowfullscreen>
  </iframe>
</div>

