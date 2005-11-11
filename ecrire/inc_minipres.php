<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2005                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


//
if (!defined("_ECRIRE_INC_VERSION")) return;

include_ecrire ("inc_lang.php3");
utiliser_langue_visiteur();

//
// Presentation des pages d'installation et d'erreurs
//

function install_debut_html($titre = 'AUTO') {
	global $attributes_body, $browser_verifForm;

	if ($titre=='AUTO')
		$titre=_T('info_installation_systeme_publication');

	include_ecrire('inc_headers.php');
	http_no_cache();
	echo  _DOCTYPE_ECRIRE .
	  "<html lang='".$GLOBALS['spip_lang'].
	  "' dir='".($GLOBALS['spip_lang_rtl'] ? 'rtl' : 'ltr')."'>\n" .
	  "<head>\n" .
	  "<title>$titre</title>\n" .
	  '<link rel="stylesheet" type="text/css" href="' .
	  _DIR_RESTREINT .
	  'spip_style.php3?couleur_claire=' .
	  urlencode('#FFCC66') .
	  '&amp;couleur_foncee=' .
	  urlencode('#000000') .
	  '&amp;left=' . 
	  $GLOBALS['spip_lang_left'] .
	  "\" >
</head>
<body $attributes_body>
<center><table style='margin-top:50px; width: 450px'>
<tr><th style='color: #970038;text-align: left;font-family: Verdana; font-weigth: bold; font-size: 18px'>".
	  $titre .
	  "</th></tr>
<tr><td  class='serif'>";
}

function install_fin_html() {

	echo '</td></tr></table></body></html>';
}

//
// Aide
//

// en hebreu le ? ne doit pas etre inverse
function aide_lang_dir($spip_lang,$spip_lang_rtl) {
	return ($spip_lang<>'he') ? $spip_lang_rtl : '';
}

function aide($aide='') {
	global $spip_lang, $spip_lang_rtl, $spip_display;

	if (!$aide OR $spip_display == 4) return;

	return "&nbsp;&nbsp;<a class='aide' href=\"". _DIR_RESTREINT
		. "aide_index.php3?aide=$aide&amp;"
		. "var_lang=$spip_lang\" target=\"spip_aide\" "
		. "onclick=\"javascript:window.open(this.href,"
		. "'spip_aide', 'scrollbars=yes, resizable=yes, width=740, "
		. "height=580'); return false;\">"
		. http_img_pack("aide".aide_lang_dir($spip_lang,$spip_lang_rtl).".gif",
			_T('info_image_aide'), "title=\""._T('titre_image_aide')
			. "\" width=\"12\" height=\"12\" border=\"0\" align=\"middle\"")
		. "</a>";
}

function info_copyright() {
	global $spip_version_affichee;

	echo _T('info_copyright', 
		   array('spip' => "<b>SPIP $spip_version_affichee</b> ",
			      'lien_gpl' => 
				"<a href='aide_index.php3?aide=licence&var_lang=".$GLOBALS['spip_lang']."' target='spip_aide' onClick=\"javascript:window.open(this.href, 'aide_spip', 'scrollbars=yes,resizable=yes,width=740,height=580'); return false;\">" . _T('info_copyright_gpl')."</a>"));

}

// Afficher le bouton "preview" dans l'espace public
function afficher_bouton_preview() {
		$x = majuscules(_T('previsualisation'));
		return '<div style="
		display: block;
		color: #eeeeee;
		background-color: #111111;
		padding-right: 5px;
		padding-top: 2px;
		padding-bottom: 5px;
		font-size: 20px;
		top: 0px;
		left: 0px;
		position: absolute;
		">' 
		  . http_img_pack('naviguer-site.png', $x, '')
		  ."&nbsp;$x</div>";
}

// Fabrique une balise A, avec un href conforme au validateur W3C
// attention au cas ou la href est du Javascript avec des "'"

function http_href($href, $clic, $title='', $style='', $class='', $evt='') {
	return '<a href="' .
		str_replace('&', '&amp;', $href) .
		'"' .
		(!$title ? '' : ("\ntitle=\"" . supprimer_tags($title)."\"")) .
		(!$style ? '' : ("\nstyle=\"" . $style . "\"")) .
		(!$class ? '' : ("\nclass=\"" . $class . "\"")) .
		($evt ? "\n$evt" : '') .
		'>' .
		$clic .
		'</a>';
}

// produit une balise img avec un champ alt d'office si vide
// attention le htmlentities et la traduction doivent etre appliques avant.

function http_img_pack($img, $alt, $att, $title='') {
	return "<img src='" . _DIR_IMG_PACK . $img
	  . ("'\nalt=\"" .
	     ($alt ? $alt : ($title ? $title : ereg_replace('\..*$','',$img)))
	     . '" ')
	  . ($title ? " title=\"$title\"" : '')
	  . $att . " />";
}

function http_href_img($href, $img, $att, $title='', $style='', $class='', $evt='') {
	return  http_href($href, http_img_pack($img, $title, $att), $title, $style, $class, $evt);
}

function http_script($script, $src='', $noscript='') {
	return '<script type="text/javascript"'
		. ($src ? " src=\"$src\"" : '')
		. ">"
		. ($script ? "<!--\n$script\n//-->" : '')
		. "</script>\n"
		. (!$noscript ? '' : "<noscript>\n\t$noscript\n</noscript>\n");
}

?>
