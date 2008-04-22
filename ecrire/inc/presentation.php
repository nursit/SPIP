<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/agenda'); // inclut inc/layer, inc/texte, inc/filtre
include_spip('inc/boutons');
include_spip('inc/actions');
include_spip('inc/puce_statut');

define('_ACTIVER_PUCE_RAPIDE', true);
define('_SIGNALER_ECHOS', true);
define('_INTERFACE_ONGLETS', false);

// http://doc.spip.org/@echo_log
function echo_log($f, $ret) {
	spip_log("Page " . self() . " function $res: echo ".substr($ret,0,50)."...",'echo');
	echo 
	(_SIGNALER_ECHOS?"#Echo par $f#" :"")
		. $ret;
}
// Faux HR, avec controle de couleur

// http://doc.spip.org/@hr
function hr($color, $retour = false) {
	$ret = "\n<div style='height: 1px; margin-top: 5px; padding-top: 5px; border-top: 1px solid $color;'></div>";
	
	if ($retour) return $ret; else echo_log('hr',$ret);
}

//
// Cadres
//
// http://doc.spip.org/@afficher_onglets_pages
function afficher_onglets_pages($ordre,$onglets){
	static $onglet_compteur = 0;
	$res = "";
	$corps = "";
	$cpt = 0;
	$actif = 0;
	// ordre des onglets
	foreach($ordre as $id => $label) {
		$cpt++;
		$disabled = strlen(trim($onglets[$id]))?"":" class='tabs-disabled'";
		if (!$actif && !$disabled) $actif = $cpt;
		$res .= "<li$disabled><a rel='$cpt' href='#$id'><span>" . $label . "</span></a></li>";
	}
	$res = "<ul class='tabs-nav'>$res</ul>";
	foreach((_INTERFACE_ONGLETS ? array_keys($ordre):array_keys($onglets)) as $id){
		$res .= "<div id='$id' class='tabs-container'>" . $onglets[$id] . "<br class='nettoyeur' /></div>";
	}
	$onglet_compteur++;
	return "<div class='boite_onglets' id='boite_onglet_$onglet_compteur'>$res</div>"
	. (_INTERFACE_ONGLETS ?
	   http_script("$('#boite_onglet_$onglet_compteur').tabs(".($actif?"$actif,":"")."{ fxAutoHeight: true });
	 if (!$.browser.safari)
	 $('ul.tabs-nav li').hover(
	 	function(){
	 		\$('#boite_onglet_$onglet_compteur').triggerTab(parseInt(\$(this).attr('rel')));
	 		return false;
	 	}
	 	,
	 	function(){}
	 	);")
	   :"");
}

// http://doc.spip.org/@debut_cadre
function debut_cadre($style, $icone = "", $fonction = "", $titre = "", $id="", $class="", $padding=true) {
	global $spip_display, $spip_lang_left;
	static $accesskey = 97; // a

	//zoom:1 fixes all expanding blocks in IE, see authors block in articles.php
	//being not standard, next step can be putting this kind of hacks in a different stylesheet
	//visible to IE only using conditional comments.  
	
	$style_cadre = " style='";
	if ($spip_display != 1 AND $spip_display != 4 AND strlen($icone) > 1) {
		$style_gauche = "padding-$spip_lang_left: 38px;";
		$style_cadre .= "margin-top: 20px;'";
	} else {
		$style_cadre .= "'"; 
		$style_gauche = '';
	}
	
	// accesskey pour accessibilite espace prive
	if ($accesskey <= 122) // z
	{
		$accesskey_c = chr($accesskey++);
		$ret = "<a id='access-$accesskey_c' href='#access-$accesskey_c' accesskey='$accesskey_c'></a>";
	} else $ret ='';

	$ret .= "\n<div "
	. ($id?"id='$id' ":"")
	."class='cadre cadre-$style"
	. ($class?" $class":"")
	."'$style_cadre>";

	if ($spip_display != 1 AND $spip_display != 4 AND strlen($icone) > 1) {
		$ret .= "\n<div style='position: absolute; top: -16px; $spip_lang_left: 10px;z-index:100;'>";
		if ($fonction) {
			$ret .= "\n<div " . http_style_background($icone, "no-repeat; padding: 0px; margin: 0px") . ">"
			. http_img_pack($fonction, "", "")
			. "</div>";
		}
		else $ret .=  http_img_pack("$icone", "", "");
		$ret .= "</div>";

		$style_cadre = " style='position: relative; top: 15px; margin-bottom: 14px;'";
	}

	if (strlen($titre) > 0) {
		if (strpos($titre,'titrem')!==false) {
			$ret .= $titre;
		} elseif ($spip_display == 4) {
			$ret .= "\n<h3 class='cadre-titre'>$titre</h3>";
		} else {
			$ret .= bouton_block_depliable($titre,-1);
		}
	}

	$ret .= "<div". ($padding ?" class='cadre_padding'" : '') .">";

	return $ret;
}

// http://doc.spip.org/@fin_cadre
function fin_cadre($style='') {

	$ret = "<div class='nettoyeur'></div></div>".
	"</div>\n";

	/*if ($style != "forum" AND $style != "thread-forum")
		$ret .= "<div style='height: 5px;'></div>\n";*/

	return $ret;
}


// http://doc.spip.org/@debut_cadre_relief
function debut_cadre_relief($icone='', $return = false, $fonction='', $titre = '', $id="", $class=""){
	$retour_aff = debut_cadre('r', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo($retour_aff);
}

// http://doc.spip.org/@fin_cadre_relief
function fin_cadre_relief($return = false){
	$retour_aff = fin_cadre('r');

	if ($return) return $retour_aff; else echo($retour_aff);
}


// http://doc.spip.org/@debut_cadre_enfonce
function debut_cadre_enfonce($icone='', $return = false, $fonction='', $titre = '', $id="", $class=""){
	$retour_aff = debut_cadre('e', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo($retour_aff);
}

// http://doc.spip.org/@fin_cadre_enfonce
function fin_cadre_enfonce($return = false){

	$retour_aff = fin_cadre('e');

	if ($return) return $retour_aff; else echo_log('fin_cadre_enfonce',$retour_aff);
}


// http://doc.spip.org/@debut_cadre_sous_rub
function debut_cadre_sous_rub($icone='', $return = false, $fonction='', $titre = '', $id="", $class=""){
	$retour_aff = debut_cadre('sous_rub', $icone, $fonction, $titre, $id, $class);
	if ($return) return $retour_aff; else echo_log('debut_cadre_sous_rub',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_sous_rub
function fin_cadre_sous_rub($return = false){
	$retour_aff = fin_cadre('sous_rub');
	if ($return) return $retour_aff; else echo_log('fin_cadre_sous_rub',$retour_aff);
}



// http://doc.spip.org/@debut_cadre_forum
function debut_cadre_forum($icone='', $return = false, $fonction='', $titre = '', $id="", $class=""){
	$retour_aff = debut_cadre('forum', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo_log('debut_cadre_forum',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_forum
function fin_cadre_forum($return = false){
	$retour_aff = fin_cadre('forum');

	if ($return) return $retour_aff; else echo_log('fin_cadre_forum',$retour_aff);
}

// http://doc.spip.org/@debut_cadre_thread_forum
function debut_cadre_thread_forum($icone='', $return = false, $fonction='', $titre = '', $id="", $class=""){
	$retour_aff = debut_cadre('thread-forum', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo_log('debut_cadre_thread_forum',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_thread_forum
function fin_cadre_thread_forum($return = false){
	$retour_aff = fin_cadre('thread-forum');

	if ($return) return $retour_aff; else echo_log('fin_cadre_thread_forum',$retour_aff);
}


// http://doc.spip.org/@debut_cadre_couleur
function debut_cadre_couleur($icone='', $return = false, $fonction='', $titre='', $id="", $class=""){
	$retour_aff = debut_cadre('couleur', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo_log('debut_cadre_couleur',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_couleur
function fin_cadre_couleur($return = false){
	$retour_aff = fin_cadre('couleur');

	if ($return) return $retour_aff; else echo_log('fin_cadre_couleur',$retour_aff);
}


// http://doc.spip.org/@debut_cadre_couleur_foncee
function debut_cadre_couleur_foncee($icone='', $return = false, $fonction='', $titre='', $id="", $class=""){
	$retour_aff = debut_cadre('couleur-foncee', $icone, $fonction, $titre, $id, $class);

	if ($return) return $retour_aff; else echo_log('debut_cadre_couleur_foncee',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_couleur_foncee
function fin_cadre_couleur_foncee($return = false){
	$retour_aff = fin_cadre('couleur-foncee');

	if ($return) return $retour_aff; else echo_log('fin_cadre_couleur_foncee',$retour_aff);
}

// http://doc.spip.org/@debut_cadre_trait_couleur
function debut_cadre_trait_couleur($icone='', $return = false, $fonction='', $titre='', $id="", $class=""){
	$retour_aff = debut_cadre('trait-couleur', $icone, $fonction, $titre, $id, $class);
	if ($return) return $retour_aff; else echo_log('debut_cadre_trait_couleur',$retour_aff);
}

// http://doc.spip.org/@fin_cadre_trait_couleur
function fin_cadre_trait_couleur($return = false){
	$retour_aff = fin_cadre('trait-couleur');

	if ($return) return $retour_aff; else echo_log('fin_cadre_trait_couleur',$retour_aff);
}


//
// une boite alerte
//
// http://doc.spip.org/@debut_boite_alerte
function debut_boite_alerte() {
	return debut_cadre('alerte', '', '', '', '', '');
}

// http://doc.spip.org/@fin_boite_alerte
function fin_boite_alerte() {
	return fin_cadre('alerte');
}


//
// une boite info
//
// http://doc.spip.org/@debut_boite_info
function debut_boite_info($return=false) {
	$r = debut_cadre('info', '', '', '', '', 'verdana1');
	if ($return) return $r; else echo_log('debut_boite_info',$r);
}

// http://doc.spip.org/@fin_boite_info
function fin_boite_info($return=false) {
	$r = fin_cadre('info');
	if ($return) return $r; else echo_log('fin_boite_info',$r);
}


//
// La boite des raccourcis
// Se place a droite si l'ecran est en mode panoramique.

// http://doc.spip.org/@bloc_des_raccourcis
function bloc_des_raccourcis($bloc) {
	global $spip_display;

	return "\n<div>&nbsp;</div>"
	. creer_colonne_droite('',true)
	. debut_cadre_enfonce('',true)
	. (($spip_display != 4)
	     ? ("\n<div style='font-size: x-small' class='verdana1'><b>"
		._T('titre_cadre_raccourcis')
		."</b>")
	       : ( "<h3>"._T('titre_cadre_raccourcis')."</h3><ul>"))
	. $bloc
	. (($spip_display != 4) ? "</div>" :  "</ul>")
	. fin_cadre_enfonce(true);
}

// Afficher un petit "+" pour lien vers autre page

// http://doc.spip.org/@afficher_plus
function afficher_plus($lien) {
	global $spip_lang_right, $spip_display;
	
	if ($spip_display != 4) {
			return "\n<a href='$lien' style='float:$spip_lang_right; padding-right: 10px;'>" .
			  http_img_pack("plus.gif", "+", "") ."</a>";
	}
}



//
// Fonctions d'affichage
//

// http://doc.spip.org/@afficher_objets
function afficher_objets($type, $titre_table,$requete,$formater='',$force=false){
	$afficher_objets = charger_fonction('afficher_objets','inc');
	return $afficher_objets($type, $titre_table,$requete,$formater,$force);
}

// http://doc.spip.org/@afficher_liste
function afficher_liste($largeurs, $table, $styles = '') {
	global $spip_display;

	if (!$table OR !is_array($table)) return "";

	if ($spip_display != 4) {
		$res = '';
		foreach ($table as $t) {
			$res .= afficher_liste_display_neq4($largeurs, $t, $styles);
		}
	} else {
		$res = "\n<ul style='text-align: $spip_lang_left; background-color: white;'>";
		foreach ($table as $t) {
			$res .= afficher_liste_display_eq4($largeurs, $t, $styles);
		}
		$res .= "\n</ul>";
	}

	return $res;
}

// http://doc.spip.org/@afficher_liste_display_neq4
function afficher_liste_display_neq4($largeurs, $t, $styles = '') {

	global $spip_lang_left,$browser_name;

	$evt = (preg_match(",msie,i", $browser_name) ? " onmouseover=\"changeclass(this,'tr_liste_over');\" onmouseout=\"changeclass(this,'tr_liste');\"" :'');

	reset($largeurs);
	if ($styles) reset($styles);
	$res ='';
	while (list(, $texte) = each($t)) {
		$style = $largeur = "";
		list(, $largeur) = each($largeurs);
		if ($styles) list(, $style) = each($styles);
		if (!trim($texte)) $texte .= "&nbsp;";
		$res .= "\n<td" .
			($largeur ? (" style='width: $largeur" ."px;'") : '') .
			($style ? " class=\"$style\"" : '') .
			">" . lignes_longues($texte) . "\n</td>";
	}

	return "\n<tr class='tr_liste'$evt>$res</tr>";
}

// http://doc.spip.org/@afficher_liste_display_eq4
function afficher_liste_display_eq4($largeurs, $t, $styles = '') {
	global $spip_lang_left;

	$res = "\n<li>";
	reset($largeurs);
	if ($styles) reset($styles);
	while (list(, $texte) = each($t)) {
		$style = $largeur = "";
		list(, $largeur) = each($largeurs);
		if (!$largeur) $res .= $texte." ";
	}
	$res .= "</li>\n";
	return $res;
}

// http://doc.spip.org/@navigation_pagination
function navigation_pagination($num_rows, $nb_aff=10, $href=null, $on='', $tmp_var=null) {

	$texte = '';
	$self = self();
	$deb_aff = isset($tmp_var) ? intval(_request($tmp_var)) : 0;

	for ($i = 0; $i < $num_rows; $i += $nb_aff){
		$deb = $i + 1;

		// Pagination : si on est trop loin, on met des '...'
		if (abs($deb-$deb_aff)>101) {
			if ($deb<$deb_aff) {
				if (!isset($premiere)) {
					$premiere = '0 ... ';
					$texte .= $premiere;
				}
			} else {
				$derniere = ' | ... '.$num_rows;
				$texte .= $derniere;
				break;
			}
		} else {

			$fin = $i + $nb_aff;
			if ($fin > $num_rows)
				$fin = $num_rows;

			if ($deb > 1)
				$texte .= " |\n";
			if ($deb_aff + 1 >= $deb AND $deb_aff + 1 <= $fin) {
				$texte .= "<b>$deb</b>";
			}
			else {
				$script = parametre_url($self, $tmp_var, $deb-1);
				if ($on) {
					$on = "\nonclick=\"return charger_id_url('"
					. parametre_url($href, $tmp_var, $deb-1)
					. "','"
					. $tmp_var
					. '\');"';
				}
				$texte .= "<a href=\"$script\"$on>$deb</a>";
			}
		}
	}
	
	return $texte;
}

// http://doc.spip.org/@afficher_tranches_requete
function afficher_tranches_requete($num_rows, $tmp_var, $url='', $nb_aff = 10, $old_arg=NULL) {
	static $ancre = 0;
	global $browser_name, $spip_lang_right, $spip_display;
	if ($old_arg!==NULL){ // eviter de casser la compat des vieux appels $cols_span ayant disparu ...
		$tmp_var = $url;		$url = $nb_aff; $nb_aff=$old_arg;
	}

	$ancre++;
	$self = self();
	$ie_style = ($browser_name == "MSIE") ? "height:1%" : '';

	$texte = "\n<div style='$ie_style;' class='arial1 tranches' id='a$ancre'>";
	$texte .= navigation_pagination($num_rows, $nb_aff, $url, $onclick=true, $tmp_var);

	$on ='';
	$script = parametre_url($self, $tmp_var, -1);
	if ($url) {
				$on = "\nonclick=\"return charger_id_url('"
				. $url
				. "&amp;"
				. $tmp_var
				. "=-1','"
				. $tmp_var
				. '\');"';
	}
	$l = htmlentities(_T('lien_tout_afficher'));
	$texte .= "<a href=\"$script#a$ancre\"$on class='plus'><img\nsrc='". _DIR_IMG_PACK . "plus.gif' title=\"$l\" alt=\"$l\" /></a>";

	$texte .= "</div>\n";
	return $texte;
}

// $fg et $bg ne sont plus utilisees
// http://doc.spip.org/@affiche_tranche_bandeau
function affiche_tranche_bandeau($requete, $icone, $fg, $bg, $tmp_var,  $titre, $force, $largeurs, $styles, $skel, $own='')
{
	global $spip_display ;
	$res = "";

	$voir_logo = ($spip_display != 1 AND $spip_display != 4 AND isset($GLOBALS['meta']['image_process'])) ? ($GLOBALS['meta']['image_process'] != "non") : false;

	if (!isset($requete['GROUP BY'])) $requete['GROUP BY'] = '';

	$cpt = sql_countsel($requete['FROM'], $requete['WHERE'], $requete['GROUP BY']);

	if (!($cpt OR $force)) return '';

	$nb_aff = floor(1.5 * _TRANCHES);

	if (isset($requete['LIMIT'])) $cpt = min($requete['LIMIT'], $cpt);

	else if (!($deb_aff = intval(_request($tmp_var))))
		 $requete['LIMIT'] = $nb_aff;

	$tranches = "";
	if ($cpt > $nb_aff) {
		$nb_aff = (_TRANCHES); 
		$tranches = afficher_tranches_requete($cpt, $tmp_var, '', $nb_aff);
	}

	$result = sql_select((isset($requete["SELECT"]) ? $requete["SELECT"] : "*"), $requete['FROM'], $requete['WHERE'], $requete['GROUP BY'], $requete['ORDER BY'], ($deb_aff > 0 ? "$deb_aff, $nb_aff" : ($requete['LIMIT'] ? $requete['LIMIT'] : "99999")));

	$id_liste = 't'.substr(md5(join(',',$requete)),0,8);

	$bouton = bouton_block_depliable($titre,true,$id_liste);

	$table = $tous_id = array(); // $tous_id obsolete.
	while ($row = sql_fetch($result)) {
		if ($a = $skel($row, $tous_id, $voir_logo, $own))
			$table[] = $a;
	}
	sql_free($result);

	$t = afficher_liste($largeurs, $table, $styles);
	if (($spip_display != 4) AND $t)
	  $t = "<table width='100%' cellpadding='2' cellspacing='0' border='0'>"
	    . $t
	    . "</table>";
	return
	  debut_cadre('liste',$icone,'',$bouton)
	  . debut_block_depliable(true,$id_liste)
	  . $tranches
	  . $t
	  . fin_block()
	  . fin_cadre();
}


// http://doc.spip.org/@afficher_liste_debut_tableau
function afficher_liste_debut_tableau() {
	global $spip_display;

	if ($spip_display != 4) return "<table width='100%' cellpadding='2' cellspacing='0' border='0'>";
	else return '<ul>';
}

// http://doc.spip.org/@afficher_liste_fin_tableau
function afficher_liste_fin_tableau() {
	global $spip_display;
	if ($spip_display != 4) return "</table>";
	else return '</ul>';
}

// http://doc.spip.org/@avoir_visiteurs
function avoir_visiteurs($past=false) {
	if ($GLOBALS['meta']["forums_publics"] == 'abo') return true;
	if (sql_countsel('spip_articles', "accepter_forum='abo'"))return true;
	if (!$past) return false;
	return sql_countsel('spip_auteurs',  "statut NOT IN ('0minirezo','1comite', 'nouveau', '5poubelle')");
}


// http://doc.spip.org/@forum_logo
function forum_logo($statut)
{
	if ($statut == "prive") return "forum-interne-24.gif";
	else if ($statut == "privadm") return "forum-admin-24.gif";
	else if ($statut == "privrac") return "forum-interne-24.gif";
	else return "forum-public-24.gif";
}


// http://doc.spip.org/@envoi_link
function envoi_link($nom_site_spip, $minipres=false) {
	global $visiteur_session, $spip_display, $spip_lang;

	$c = (is_array($visiteur_session)
	AND is_array($visiteur_session['prefs']))
		? $visiteur_session['prefs']['couleur']
		: 1;

	$couleurs = charger_fonction('couleurs', 'inc');
	$paramcss = 'ltr='
	. $GLOBALS['spip_lang_left'] . '&'
	. $couleurs($c);

	// CSS de secours en cas de non fonct de la suivante
	$res = '<link rel="stylesheet" type="text/css" href="'
	  . url_absolue(find_in_path('style_prive_defaut.css'))
	. '" />'  . "\n"
	
	// CSS calendrier
	. '<link rel="stylesheet" type="text/css" href="'
	  . url_absolue(find_in_path('agenda.css')) .'" />' . "\n"
	
	// CSS imprimante (masque des trucs, a completer)
	. '<link rel="stylesheet" type="text/css" href="'
	  . url_absolue(find_in_path('spip_style.css'))
	. '" media="all" />' . "\n"
  
	// CSS imprimante (masque des trucs, a completer)
	. '<link rel="stylesheet" type="text/css" href="'
	  . url_absolue(find_in_path('spip_style_print.css'))
	. '" media="print" />' . "\n"

	// CSS "visible au chargement" differente selon js actif ou non

	. '<link rel="stylesheet" type="text/css" href="'
	  . url_absolue(find_in_path('spip_style_'
				     . (_SPIP_AJAX ? 'invisible' : 'visible')
				     . '.css'))
	.'" />' . "\n"
	
	// CSS espace prive : la vraie
	. '<link rel="stylesheet" type="text/css" href="'
	. generer_url_public('style_prive', $paramcss) .'" id="cssprivee" />' . "\n"
  . "<!--[if lt IE 8]>\n"
  . '<link rel="stylesheet" type="text/css" href="'
  . generer_url_public('style_prive_ie', $paramcss) .'" />' . "\n"
  . "<![endif]-->\n"
  
	// CSS optionelle minipres
	. ($minipres?'<link rel="stylesheet" type="text/css" href="'
	   . url_absolue(find_in_path('minipres.css')).'" />' . "\n":"")

	// favicon.ico
	. '<link rel="shortcut icon" href="'
	. url_absolue(find_in_path('favicon.ico'))
	. "\" />\n";

	$js = debut_javascript();

	if ($spip_display == 4) return $res . $js;

	$nom = entites_html($nom_site_spip);

	$res .= "<link rel='alternate' type='application/rss+xml' title=\"$nom\" href='"
			. generer_url_public('backend') . "' />\n";
	$res .= "<link rel='help' type='text/html' title=\""._T('icone_aide_ligne') . 
			"\" href='"
			. generer_url_ecrire('aide_index',"var_lang=$spip_lang")
			."' />\n";
	if ($GLOBALS['meta']["activer_breves"] != "non")
		$res .= "<link rel='alternate' type='application/rss+xml' title=\""
			. $nom
			. " ("._T("info_breves_03")
			. ")\" href='" . generer_url_public('backend-breves') . "' />\n";

	return $res . $js;
}

// http://doc.spip.org/@debut_javascript
function debut_javascript()
{
	global $spip_lang_left, $browser_name, $browser_version;
	include_spip('inc/charsets');

	// tester les capacites JS :

	// On envoie un script ajah ; si le script reussit le cookie passera a +1
	// on installe egalement un <noscript></noscript> qui charge une image qui
	// pose un cookie valant -1

	$testeur = generer_url_ecrire('test_ajax', 'var_ajaxcharset=utf-8&js=1');

	if (_SPIP_AJAX AND !defined('_TESTER_NOSCRIPT')) {
	  // pour le pied de page (deja defini si on est validation XML)
		define('_TESTER_NOSCRIPT',
			"<noscript>\n<div style='display:none;'><img src='"
		        . generer_url_ecrire('test_ajax', 'var_ajaxcharset=utf-8&js=-1')
		        . "' width='1' height='1' alt='' /></div></noscript>\n"); 
	}

	return 
	// envoi le fichier JS de config si browser ok.
		$GLOBALS['browser_layer'] .
	 	http_script(
			((isset($_COOKIE['spip_accepte_ajax']) && $_COOKIE['spip_accepte_ajax'] >= 1)
			? ''
			: "jQuery.ajax({'url':'$testeur'});") .
			(_OUTILS_DEVELOPPEURS ?"var _OUTILS_DEVELOPPEURS=true;":"") .
			"\nvar ajax_image_searching = \n'<div style=\"float: ".$GLOBALS['spip_lang_right'].";\"><img src=\"".url_absolue(_DIR_IMG_PACK."searching.gif")."\" alt=\"\" /></div>';" .
			"\nvar stat = " . (($GLOBALS['meta']["activer_statistiques"] != 'non') ? 1 : 0) .
			"\nvar largeur_icone = " .
			intval(_LARGEUR_ICONES_BANDEAU) .
			"\nvar  bug_offsetwidth = " .
// uniquement affichage ltr: bug Mozilla dans offsetWidth quand ecran inverse!
			((($spip_lang_left == "left") &&
			  (($browser_name != "MSIE") ||
			   ($browser_version >= 6))) ? 1 : 0) .
			"\nvar confirm_changer_statut = '" .
			unicode_to_javascript(addslashes(html2unicode(_T("confirm_changer_statut")))) . 
			"';\n") .
		//plugin needed to fix the select showing through the submenus o IE6  
    (($browser_name == "MSIE" && $browser_version <= 6) ? http_script('', 'bgiframe.js'):'' ) .
    http_script('', 'presentation.js');
}

// Fonctions onglets


// http://doc.spip.org/@debut_onglet
function debut_onglet(){

	return "
\n<div style='padding: 7px;'><table cellpadding='0' cellspacing='0' border='0' class='centered'><tr>
";
}

// http://doc.spip.org/@fin_onglet
function fin_onglet(){
	return "</tr></table></div>\n";
}

// http://doc.spip.org/@onglet
function onglet($texte, $lien, $onglet_ref, $onglet, $icone=""){
	global $spip_display, $spip_lang_left ;

	$res = "<td>";
	$res .= "\n<div style='position: relative;'>";
	if ($spip_display != 1) {
		if (strlen($icone) > 0) {
			$res .= "\n<div style='z-index: 2; position: absolute; top: 0px; $spip_lang_left: 5px;'>" .
			  http_img_pack("$icone", "", "") . "</div>";
			$style = " top: 7px; padding-$spip_lang_left: 32px; z-index: 1;";
		} else {
			$style = " top: 7px;";
		}
	}

	if ($onglet != $onglet_ref) {
		$res .= "\n<div onmouseover=\"changeclass(this, 'onglet_on');\" onmouseout=\"changeclass(this, 'onglet');\" class='onglet' style='position: relative;$style'><a href='$lien'>$texte</a></div>";
		$res .= "</div>";
	} else {
		$res .= "\n<div class='onglet_off' style='position: relative;$style'>$texte</div>";
		$res .= "</div>";
	}
	$res .= "</td>";
	return $res;
}

// http://doc.spip.org/@icone
function icone($texte, $lien, $fond, $fonction="", $align="", $echo=false){
	$retour = "<div style='padding-top: 20px;width:100px' class='icone36'>" . icone_inline($texte, $lien, $fond, $fonction, $align) . "</div>";
	if ($echo) echo_log('icone',$retour); else return $retour;
}

// http://doc.spip.org/@icone_inline
function icone_inline($texte, $lien, $fond, $fonction="", $align="", $ajax=false){	
	global $spip_display;

	if ($fonction == "supprimer.gif") {
		$style = 'icone36 danger';
	} else {
		$style = 'icone36';
		if (strlen($fonction) < 3) $fonction = "rien.gif";
	}
	$style .= " " . substr(basename($fond),0,-4);

	if ($spip_display == 1){
		$hauteur = 20;
		$largeur = 100;
		$title = $alt = "";
	}
	else if ($spip_display == 3){
		$hauteur = 30;
		$largeur = 30;
		$title = "\ntitle=\"$texte\"";
		$alt = $texte;
	}
	else {
		$hauteur = 70;
		$largeur = 100;
		$title = '';
		$alt = $texte;
	}

	$size = 24;
	if (preg_match("/-([0-9]{1,3})[.](gif|png)$/i",$fond,$match))
		$size = $match[1];
	if ($spip_display != 1 AND $spip_display != 4){
		if ($fonction != "rien.gif"){
		  $icone = http_img_pack($fonction, $alt, "$title width='$size' height='$size'\n" .
					  http_style_background($fond, "no-repeat center center"));
		}
		else {
			$icone = http_img_pack($fond, $alt, "$title width='$size' height='$size'");
		}
	} else $icone = '';

	// cas d'ajax_action_auteur: faut defaire le boulot 
	// (il faudrait fusionner avec le cas $javascript)
	if (preg_match(",^<a\shref='([^']*)'([^>]*)>(.*)</a>$,i",$lien,$r))
		list($x,$lien,$atts,$texte)= $r;
	else $atts = '';
	
	if ($align && $align!='center') $align = "float: $align; ";

	$icone = "<a style='$align' class='$style'"
	. $atts
	. (!$ajax ? '' : (' onclick=' . ajax_action_declencheur($lien,$ajax)))
	. "\nhref='"
	. $lien
	. "'>"
	. $icone
	. (($spip_display == 3)	? '' : "<span>$texte</span>")
	  . "</a>\n";

	if ($align <> 'center') return $icone;
	$style = " style='text-align:center;'";
	return "<div$style>$icone</div>";
}

// http://doc.spip.org/@icone_horizontale
function icone_horizontale($texte, $lien, $fond = "", $fonction = "", $af = true, $javascript='') {
	global $spip_display;

	$retour = '';
	// cas d'ajax_action_auteur: faut defaire le boulot 
	// (il faudrait fusionner avec le cas $javascript)
	if (preg_match(",^<a href='([^']*)'([^>]*)>(.*)</a>$,i",$lien,$r))
	  list($x,$lien,$atts,$texte)= $r;
	else $atts = '';
	$lien = "\nhref='$lien'$atts";

	if ($spip_display != 4) {
	
		if ($spip_display != 1) {
			$retour .= "\n<table class='cellule-h-table' cellpadding='0' style='vertical-align: middle'>"
			. "\n<tr><td><a $javascript$lien class='cellule-h'>"
			. "<span class='cell-i'>" ;
			if ($fonction){
				$retour .= http_img_pack($fonction, $texte, http_style_background($fond, "center center no-repeat"));
			}
			else {
				$retour .= http_img_pack($fond, $texte, "");
			}
			$retour .= "</span></a></td>"
			. "\n<td class='cellule-h-lien'><a $javascript$lien class='cellule-h'>"
			. $texte
			. "</a></td></tr></table>\n";
		}
		else {
			$retour .= "\n<div><a class='cellule-h-texte' $javascript$lien>$texte</a></div>\n";
		}
		if ($fonction == "supprimer.gif")
			$retour = "\n<div class='danger'>$retour</div>";
	} else {
		$retour = "\n<li><a$lien>$texte</a></li>";
	}

	if ($af) echo_log('icone_horizontale',$retour); else return $retour;
}

// http://doc.spip.org/@icone_horizontale_display
function icone_horizontale_display($texte, $lien, $fond = "", $fonction = "", $af = true, $js='') {
	global $spip_display, $spip_lang_left;
	$img = icone_horizontale($texte, $lien, $fond, $fonction, $af, $js);
	if ($spip_display != 4)
		return "<div style='float: $spip_lang_left; width:140px;'>$img</div>\n";
	else return "<ul>$img</ul>";
}

// Fonction standard pour le pipeline 'boite_infos'
// http://doc.spip.org/@f_boite_infos
function f_boite_infos($flux) {
	$boite = $flux['data'];
	$args = $flux['args'];
	$type = $args['type'];
	$id = $args['id'];
	$row = $args['row'];

	unset($args['row']);
	include_spip('public/assembler');
	$boite .= recuperer_fond("prive/infos/$type",$args);

	$flux['data'] = $boite;
	return $flux;
}


// http://doc.spip.org/@gros_titre
function gros_titre($titre, $ze_logo='', $aff=true){
	global $spip_display;
	$res = "\n<h1>";
	if ($spip_display != 4) {
		$res .= $ze_logo.' ';
	}
	$res .= typo($titre)."</h1>\n";
	if ($aff) echo_log('gros_titre',$res); else return $res;
}


//
// Cadre centre (haut de page)
//

// http://doc.spip.org/@debut_grand_cadre
function debut_grand_cadre($return=false){
	$res =  "\n<div class='table_page'>\n";
	if ($return) return $res; else echo_log('debut_grand_cadre',$res);
}

// http://doc.spip.org/@fin_grand_cadre
function fin_grand_cadre($return=false){
	$res = "\n</div>";
	if ($return) return $res; else echo_log('fin_grand_cadre',$res);
}

// Cadre formulaires

// http://doc.spip.org/@debut_cadre_formulaire
function debut_cadre_formulaire($style='', $return=false){
	$x = "\n<div class='cadre-formulaire'" .
	  (!$style ? "" : " style='$style'") .
	   ">";
	if ($return) return  $x; else echo_log('debut_cadre_formulaire',$x);
}

// http://doc.spip.org/@fin_cadre_formulaire
function fin_cadre_formulaire($return=false){
	if ($return) return  "</div>\n"; else echo_log('fin_cadre_formulaire', "</div>\n");
}


// http://doc.spip.org/@formulaire_recherche
function formulaire_recherche($page, $complement=""){
	$recherche = _request('recherche');
	$recherche_aff = entites_html($recherche);
	if (!strlen($recherche)) {
		$recherche_aff = _T('info_rechercher');
		$onfocus = " onfocus=\"this.value='';\"";
	} else $onfocus = '';

	$form = '<input type="text" size="10" value="'.$recherche_aff.'" name="recherche" class="recherche" accesskey="r"' . $onfocus . ' />';
	$form .= "<input type='image' src='"._DIR_IMG_PACK."loupe.png' name='submit' class='submit' alt='"._T('info_rechercher')."' />";
	return "<div class='spip_recherche'>".generer_form_ecrire($page, $form . $complement, " method='get'")."</div>";
}

//
// Debut de la colonne de gauche
//

// http://doc.spip.org/@debut_gauche
function debut_gauche($rubrique = "accueil", $return=false) {
	global $spip_display;
	global $spip_ecran, $spip_lang_rtl, $spip_lang_left;

	// div navigation fermee par creer_colonne_droite qui ouvre
	// div extra lui-meme ferme par debut_droite qui ouvre 
	// div contenu lui-meme ferme par fin_gauche() ainsi que
	// div conteneur

	$res = "<br /><div id='conteneur' class='".(_INTERFACE_ONGLETS ? "onglets" : "no_onglets")  ."'>
		\n<div id='navigation'>\n";
		
	if ($spip_display == 4) $res .= "<!-- ";

	if ($return) return $res; else echo_log('debut_gauche',$res);
}

// http://doc.spip.org/@fin_gauche
function fin_gauche()
{
	return "</div></div><br class='nettoyeur' />";
}

//
// Presentation de l''interface privee, marge de droite
//

// http://doc.spip.org/@creer_colonne_droite
function creer_colonne_droite($rubrique="", $return= false){
	static $deja_colonne_droite;
	global $spip_ecran, $spip_lang_rtl, $spip_lang_left;

	if ((!($spip_ecran == "large")) OR $deja_colonne_droite) return '';
	$deja_colonne_droite = true;

	$res = "\n</div><div id='extra'>";

	if ($return) return $res; else echo_log('creer_colonne_droite',$res);
}

// http://doc.spip.org/@formulaire_large
function formulaire_large()
{
	return isset($_GET['exec'])?preg_match(',^((articles|breves|rubriques)_edit|poster_forum_prive),', $_GET['exec']):false;
}

// http://doc.spip.org/@debut_droite
function debut_droite($rubrique="", $return= false) {
	global $spip_ecran, $spip_display, $spip_lang_left; 

	$res = '';

	if ($spip_display == 4) $res .= " -->";

	$res .= liste_articles_bloques();

	$res .= creer_colonne_droite($rubrique, true)
	. "</div>";

	$res .= "\n<div id='contenu' class='serif'>";

	// touche d'acces rapide au debut du contenu : z
	// Attention avant c'etait 's' mais c'est incompatible avec
	// le ctrl-s qui fait "enregistrer"
	$res .= "\n<a id='saut' href='#saut' accesskey='z'></a>\n";

	if ($return) return $res; else echo_log('debut_droite',$res);
}

// http://doc.spip.org/@liste_articles_bloques
function liste_articles_bloques()
{
	global $connect_id_auteur;

	$res = '';
	if ($GLOBALS['meta']["articles_modif"] != "non") {
		include_spip('inc/drapeau_edition');
		$articles_ouverts = liste_drapeau_edition ($connect_id_auteur, 'article');
		if (count($articles_ouverts)) {
			$res .= 
				debut_cadre('bandeau-rubriques',"article-24.gif",'',_T('info_cours_edition'))
				. "\n<div class='plan-articles-bloques'>";
			foreach ($articles_ouverts as $row) {
				$ze_article = $row['id_article'];
				$ze_titre = $row['titre'];
				$statut = $row["statut"];

				$res .= "\n<div class='$statut'>"
				. "\n<div style='float:right; '>"
				. debloquer_article($ze_article,_T('lien_liberer'))
				. "</div>"
				. "<a  href='" 
				. generer_url_ecrire("articles","id_article=$ze_article")
				. "'>$ze_titre</a>"
				. "</div>";
			}

			if (count($articles_ouverts) >= 4) {
				$res .= "\n<div style='text-align:right; '>"
				. debloquer_article('tous', _T('lien_liberer_tous'))
				. "</div>";
			}
			$res .= fin_cadre('bandeau-rubriques') . "</div>";
		}
	}
	return $res;
}
	
//
// Fin de page de l'interface privee. 
// Elle comporte une image invisible declenchant une tache de fond

// http://doc.spip.org/@fin_page
function fin_page()
{
	global $spip_display;

	// avec &var_profile=1 on a le tableau de mesures SQL
	if (@count($GLOBALS['tableau_des_temps'])) {
		include_spip('public/debug');
		$chrono = chrono_requete($GLOBALS['tableau_des_temps']);
	} else $chrono = '';

	include_spip('public/assembler');

	// cf. public/assembler, fonction f_msie()
	// test si MSIE et sinon quitte
	if (
		strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'msie')
		AND preg_match('/MSIE /i', $_SERVER['HTTP_USER_AGENT'])
		AND $msiefix = charger_fonction('msiefix', 'inc')
	)
		$fix_png = presentation_msiefix();
	else
		$fix_png = '';

	return debut_grand_cadre(true)
	. (($spip_display == 4)
		? ("<div><a href='"
		   	. parametre_url(self(),'set_disp', '2')
			. "'>"
			.  _T("access_interface_graphique")
			. "</a></div>")
		: ("<div style='text-align: right; ' class='verdana1 spip_xx-small'>"
			. info_copyright()
			. "<br />"
			. _T('info_copyright_doc')
			. '</div>'))

	. fin_grand_cadre(true)
	. "</div>\n" // cf. div centered ouverte dans conmmencer_page()
	. $fix_png
	. $GLOBALS['rejoue_session']
	. '<div style="background-image: url(\''
	. generer_url_action('cron')
	. '\');"></div>'
	. (defined('_TESTER_NOSCRIPT') ? _TESTER_NOSCRIPT : '')
	. $chrono
	. "</body></html>\n";
}

// http://doc.spip.org/@info_copyright
function info_copyright() {
	global $spip_version_affichee, $spip_lang;

	$version = $spip_version_affichee;

	//
	// Mention, le cas echeant, de la revision SVN courante
	//
	if ($svn_revision = version_svn_courante(_DIR_RACINE)) {
		$version .= ' ' . (($svn_revision < 0) ? 'SVN ':'')
		. "[<a href='http://trac.rezo.net/trac/spip/changeset/"
		. abs($svn_revision) . "' onclick=\"window.open(this.href); return false;\">"
		. abs($svn_revision) . "</a>]";
	}

	return _T('info_copyright', 
		   array('spip' => "<b>SPIP $version</b> ",
			 'lien_gpl' => 
			 "<a href='". generer_url_ecrire("aide_index", "aide=licence&var_lang=$spip_lang") . "' onclick=\"window.open(this.href, 'spip_aide', 'scrollbars=yes,resizable=yes,width=740,height=580'); return false;\">" . _T('info_copyright_gpl')."</a>"));

}

// http://doc.spip.org/@debloquer_article
function debloquer_article($arg, $texte) {

	// cas d'un article pas liberable : on esst sur sa page d'edition
	if (_request('exec') == 'articles_edit'
	AND $arg == _request('id_article'))
		return '';

	$lien = parametre_url(self(), 'debloquer_article', $arg, '&');
	return "<a href='" .
	  generer_action_auteur('instituer_collaboration',$arg, _DIR_RESTREINT_ABS . $lien) .
	  "' title=\"" .
	  attribut_html($texte) .
	  "\">"
	  . ($arg == 'tous' ? "$texte&nbsp;" : '')
	  . http_img_pack("croix-rouge.gif", ($arg=='tous' ? "" : "X"),
			"width='7' height='7' ") .
	  "</a>";
}


//
// Afficher la hierarchie des rubriques
//

// http://doc.spip.org/@afficher_hierarchie
function afficher_hierarchie($id_rubrique) {
	global $spip_lang_left;

	$parents = '';
	$style1 = "$spip_lang_left center no-repeat; padding-$spip_lang_left: 15px";
	$style2 = "margin-$spip_lang_left: 15px;";
	$tag = "a";
	$on = ' on';

	while ($id_rubrique) {

		$res = sql_fetsel("id_parent, titre, lang", "spip_rubriques", "id_rubrique=".intval($id_rubrique));

		if (!$res) break; // rubrique inexistante

		$id_parent = $res['id_parent'];
		changer_typo($res['lang']);

		$class = (!$id_parent) ? "secteur"
		: (acces_restreint_rubrique($id_rubrique)
		? "admin" : "rubrique");

		$parents = "<ul><li><span><em> &gt; </em><$tag class='$class$on'"
		. ($tag=='a'?" href='". generer_url_ecrire("naviguer","id_rubrique=$id_rubrique")."'":"")
		. ">"
		. supprimer_numero(typo(sinon($res['titre'], _T('ecrire:info_sans_titre'))))
		. "</$tag></span>"
		. $parents
		. "</li></ul>";

		$id_rubrique = $id_parent;
		$tag = 'a';
		$on = '';
	}

	return "\n<ul id='chemin' class='verdana3' dir='".lang_dir()."'" 
	  //. http_style_background("racine-site-12.gif", $style1)
	  . "><li><span><$tag class='racine$on'" 
		. ($tag=='a'?" href='". generer_url_ecrire("naviguer","id_rubrique=$id_rubrique")."'":"")
	  . ">"._T('info_racine_site')."</$tag>"
 	  . "</span>"
	  . $parents
 	  . aide ("rubhier")
 	  . "</li></ul>";
}

// http://doc.spip.org/@enfant_rub
function enfant_rub($collection){
	global $spip_display, $spip_lang_left, $spip_lang_right, $spip_lang;

	$voir_logo = ($spip_display != 1 AND $spip_display != 4 AND isset($GLOBALS['meta']['image_process']) AND $GLOBALS['meta']['image_process'] != "non");
		
	if ($voir_logo) {
		$voir_logo = "float: $spip_lang_right; margin-$spip_lang_right: 0px; margin-top: 0px;";
		$chercher_logo = charger_fonction('chercher_logo', 'inc');
	} else $logo ='';

	$res = "";

	$result = sql_select("id_rubrique, id_parent, titre, descriptif, lang ", "spip_rubriques", "id_parent=$collection",'', '0+titre,titre');

	while($row=sql_fetch($result)){
		$id_rubrique=$row['id_rubrique'];
		$id_parent=$row['id_parent'];
		$titre=$row['titre'];

		if (autoriser('voir','rubrique',$id_rubrique)){
	
			$les_sous_enfants = sous_enfant_rub($id_rubrique);
	
			changer_typo($row['lang']);
			$lang_dir = lang_dir($row['lang']);	
			$descriptif=propre($row['descriptif']);
	
			if ($voir_logo) {
				if ($logo = $chercher_logo($id_rubrique, 'id_rubrique', 'on')) {
					list($fid, $dir, $nom, $format) = $logo;
					include_spip('inc/filtres_images');
					$logo = image_reduire("<img src='$fid' alt='' />", 48, 36);
					if ($logo)
						$logo =  "\n<div style='$voir_logo'>$logo</div>";
				}
			}
	
			$lib_bouton = (!acces_restreint_rubrique($id_rubrique) ? "" :
			   http_img_pack("admin-12.gif", '', " width='12' height='12'", _T('image_administrer_rubrique'))) .
			  " <span dir='$lang_dir'><a href='" . 
			  generer_url_ecrire("naviguer","id_rubrique=$id_rubrique") .
			  "'>".
			  typo($titre) .
			  "</a></span>";
			  
			  $titre = (is_string($logo) ? $logo : '') .
				  bouton_block_depliable($lib_bouton,$les_sous_enfants ?false:-1,"enfants$id_rubrique");
			  
			$les_enfants = "\n<div class='enfants'>" .
			  debut_cadre_sous_rub(($id_parent ? "rubrique-24.gif" : "secteur-24.gif"), true, "", $titre) .
			  (!$descriptif ? '' : "\n<div class='verdana1'>$descriptif</div>") .
			  (($spip_display == 4) ? '' : $les_sous_enfants) .
			  "\n<div style='clear:both;'></div>"  .
			  fin_cadre_sous_rub(true) .
			  "</div>";
	
			$res .= ($spip_display != 4)
			? $les_enfants
			: "\n<li>$les_enfants</li>";
		}
	}

	changer_typo($spip_lang); # remettre la typo de l'interface pour la suite
	return (($spip_display == 4) ? "\n<ul>$res</ul>\n" :  $res);

}

// http://doc.spip.org/@sous_enfant_rub
function sous_enfant_rub($collection2){
	global $spip_lang_left;

	$result3 =  sql_select("*", "spip_rubriques", "id_parent=$collection2",'', '0+titre,titre');

	if (!sql_count($result3)) return '';
	$retour = debut_block_depliable(false,"enfants$collection2")."\n<ul style='margin: 0px; padding: 0px; padding-top: 3px;'>\n";
	while($row=sql_fetch($result3)){
		$id_rubrique2=$row['id_rubrique'];
		$id_parent2=$row['id_parent'];
		$titre2=$row['titre'];
		changer_typo($row['lang']);
		$lang_dir = lang_dir($row['lang']);
		if (autoriser('voir','rubrique',$id_rubrique2))
			$retour.="\n<li><div class='arial11' " .
			  http_style_background('rubrique-12.gif', "left center no-repeat; padding: 2px; padding-$spip_lang_left: 18px; margin-$spip_lang_left: 3px") . "><a href='" . generer_url_ecrire("naviguer","id_rubrique=$id_rubrique2") . "'><span dir='$lang_dir'>".typo($titre2)."</span></a></div></li>\n";
	}
	$retour .= "</ul>\n\n".fin_block()."\n\n";
	
	return $retour;
}

// http://doc.spip.org/@afficher_enfant_rub
function afficher_enfant_rub($id_rubrique, $bouton=false, $return=false) {
	global  $spip_lang_left,$spip_lang_right, $spip_display;
	
	$les_enfants = enfant_rub($id_rubrique);
	$n = strlen(trim($les_enfants));
	
	if (!$n && !$bouton) return "";
	
	if (!($x = strpos($les_enfants,"\n<div class='enfants'>",round($n/2)))) {
		$les_enfants2="";
	}else{
		$les_enfants2 = substr($les_enfants, $x);
		$les_enfants = substr($les_enfants,0,$x);
		if ($spip_display == 4) {
		  $les_enfants .= '</li></ul>';
		  $les_enfants2 = '<ul><li>' . $les_enfants2;
		}
	}

	$res = 
	"<div class='gauche'>"
	. $les_enfants
	. "</div>"
	. "<div class='droite'>"
	. $les_enfants2
	. "</div>"
	. "&nbsp;"
	. "<div style='float:"
	. $spip_lang_right
	. ";position:relative;'>"
	. (!$bouton ? ''
		 : (!$id_rubrique
		    ? icone(_T('icone_creer_rubrique'), generer_url_ecrire("rubriques_edit","new=oui&retour=nav"), "secteur-24.gif", "creer.gif",$spip_lang_right, false)
		    : icone(_T('icone_creer_sous_rubrique'), generer_url_ecrire("rubriques_edit","new=oui&retour=nav&id_parent=$id_rubrique"), "rubrique-24.gif", "creer.gif",$spip_lang_right,false)))
	. "</div>";

	if ($return) return $res; else echo_log('afficher_enfant_rub',$res);
}

// Pour construire des menu avec SELECTED
// http://doc.spip.org/@mySel
function mySel($varaut,$variable, $option = NULL) {
	$res = ' value="'.$varaut.'"' . (($variable==$varaut) ? ' selected="selected"' : '');

	return  (!isset($option) ? $res : "<option$res>$option</option>\n");
}


// Voir en ligne, ou apercu, ou rien (renvoie tout le bloc)
// http://doc.spip.org/@voir_en_ligne
function voir_en_ligne ($type, $id, $statut=false, $image='racine-24.gif', $af = true, $inline=true) {

	$en_ligne = $message = '';
	switch ($type) {
		case 'article':
			if ($statut == "publie" AND $GLOBALS['meta']["post_dates"] == 'non') {
				$n = sql_fetsel("id_article", "spip_articles", "id_article=$id AND date<=NOW()");
				if (!$n) $statut = 'prop';
			}
			if ($statut == 'publie')
				$en_ligne = 'calcul';
			else if ($statut == 'prop')
				$en_ligne = 'preview';
			break;
		case 'rubrique':
			if ($id > 0)
				if ($statut == 'publie')
					$en_ligne = 'calcul';
				else
					$en_ligne = 'preview';
			break;
		case 'breve':
		case 'site':
			if ($statut == 'publie')
				$en_ligne = 'calcul';
			else if ($statut == 'prop')
				$en_ligne = 'preview';
			break;
		case 'mot':
		case 'auteur':
			$n = sql_countsel('spip_auteurs_articles AS lien, spip_articles AS articles', "lien.id_auteur=".sql_quote($id)." AND lien.id_article=articles.id_article AND articles.statut='publie'");
			if ($n) $en_ligne = 'calcul';
			else $en_ligne = 'preview';
			break;
	}

	if ($en_ligne == 'calcul')
		$message = _T('icone_voir_en_ligne');
	else if ($en_ligne == 'preview'
	AND autoriser('previsualiser'))
		$message = _T('previsualiser');
	else
		return '';

	return
		$inline ? icone_inline($message, generer_url_action('redirect', "id_$type=$id&var_mode=$en_ligne"), $image, "rien.gif", $GLOBALS['spip_lang_left'])		: 
		icone_horizontale($message, generer_url_action('redirect', "id_$type=$id&var_mode=$en_ligne"), $image, "rien.gif",$af);

}

//
// Creer un bouton qui renvoie vers la bonne url spip_rss
// http://doc.spip.org/@bouton_spip_rss
function bouton_spip_rss($op, $args, $fmt='rss') {

	include_spip('inc/acces');
	$a = '';
	if (is_array($args))
		foreach ($args as $val => $var)
			if ($var) $a .= ':' . $val.'-'.$var;
	$a = substr($a,1);

	$url = generer_url_action('rss', "op=$op" 
			    . (!$a ? "" : "&args=$a")
			    . ('&id=' . $GLOBALS['connect_id_auteur'])
			    . ('&cle=' . afficher_low_sec($GLOBALS['connect_id_auteur'], "rss $op $a"))
			    . ('&lang=' . $GLOBALS['spip_lang']));

	switch($fmt) {
		case 'ical':
			$url = preg_replace(',^.*?://,', 'webcal://', $url)
			  . "&amp;fmt=ical";
			$button = 'iCal';
			break;
		case 'atom':
			$button = 'atom';
			break;
		case 'rss':
		default:
		  
			$button = 'RSS';
			break;
	}

	return "<a href='"
	. $url
	. "'>"
	. http_img_pack('feed.png', $button, '', 'RSS')
	. "</a>";
}
?>
