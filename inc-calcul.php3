<?php

//
// Ce fichier ne sera execute qu'une fois
if (defined("_INC_CALCUL")) return;
define("_INC_CALCUL", "1");


if (file_exists("mes_fonctions.php3")) {
	include_local ("mes_fonctions.php3");
}

include_ecrire("inc_index.php3");
include_ecrire("inc_texte.php3");
include_ecrire("inc_filtres.php3");

tester_variable('espace_logos',3);  // HSPACE=xxx VSPACE=xxx pour les logos (#LOGO_ARTICLE)
tester_variable('espace_images',3);  // HSPACE=xxx VSPACE=xxx pour les images integrees (<IMG1>)

include_local("inc-forum.php3");

if (file_exists("inc-urls.php3")) {
	include_local ("inc-urls.php3");
}
else {
	include_local ("inc-urls-dist.php3");
}



function transformer_lien_logo($contexte, $lien) {
	$lien = ereg_replace("#ID_RUBRIQUE", $contexte['id_rubrique'], $lien);
	$lien = ereg_replace("#ID_ARTICLE", $contexte['id_article'], $lien);
	$lien = ereg_replace("#ID_SECTEUR", $contexte['id_secteur'], $lien);
	$lien = ereg_replace("#ID_BREVE", $contexte['id_breve'], $lien);
	$lien = ereg_replace("#ID_FORUM", $contexte['id_forum'], $lien);
	$lien = ereg_replace("#ID_DOCUMENT", $contexte['id_document'], $lien);
	$lien = ereg_replace("#ID_AUTEUR", $contexte['id_auteur'], $lien);
	$lien = ereg_replace("#URL_ARTICLE", generer_url_article($contexte['id_article']), $lien);
	$lien = ereg_replace("#URL_RUBRIQUE", generer_url_rubrique($contexte['id_rubrique']), $lien);
	$lien = ereg_replace("#URL_SITE", $contexte['url_site'], $lien);
	$lien = ereg_replace("#URL_SECTEUR", generer_url_rubrique($contexte['id_secteur']), $lien);
	$lien = ereg_replace("#URL_BREVE", generer_url_breve($contexte['id_breve']), $lien);
	$lien = ereg_replace("#URL_FORUM", generer_url_forum($contexte['id_forum']), $lien);
	$lien = ereg_replace("#URL_DOCUMENT", generer_url_document($contexte['id_document']), $lien);
	return $lien;
}


//
// Retrouver l'image d'un objet (et son survol)
//

// chercher une image nommee dans tous les formats
function cherche_image_nommee($nom) {
	$formats = array ('gif', 'jpg', 'png');
	while (list(, $format) = each($formats))
		if (file_exists('IMG/'.$nom.'.'.$format))
			return ($nom.'.'.$format);
}

function cherche_image($id_objet, $type_objet) {
	$image = array('', '');

	// cherche l'image liee a l'objet
	$image[0] = cherche_image_nommee($type_objet.'on'.$id_objet);

	// cherche un survol
	if ($image[0]) {
		$image[1] = cherche_image_nommee($type_objet.'off'.$id_objet);
	}

	return $image;
}


function image_document($id_document){
	$query = "SELECT * FROM spip_documents WHERE id_document = $id_document";
	$result = spip_query($query);
	if ($row = mysql_fetch_array($result)) {
		$id_document = $row['id_document'];
		$id_type = $row['id_type'];
		$titre = propre($row ['titre']);
		$descriptif = propre($row['descriptif']);
		$fichier = $row['fichier'];
		$largeur = $row['largeur'];
		$hauteur = $row['hauteur'];
		$taille = $row['taille'];
		$mode = $row['mode'];
		$id_vignette = $row['id_vignette'];

		// recuperer la vignette pour affichage inline
		if ($id_vignette) {
			$query_vignette = "SELECT * FROM spip_documents WHERE id_document = $id_vignette";
			$result_vignette = spip_query($query_vignette);
			if ($row_vignette = @mysql_fetch_array($result_vignette)) {
				$fichier_vignette = $row_vignette['fichier'];
				$largeur_vignette = $row_vignette['largeur'];
				$hauteur_vignette = $row_vignette['hauteur'];
			}
		}
		else if ($mode == 'vignette') {
			$fichier_vignette = $fichier;
			$largeur_vignette = $largeur;
			$hauteur_vignette = $hauteur;
		}
		// si pas de vignette, utiliser la vignette par defaut du type du document
		if (!$fichier_vignette) {
			// on construira le lien en fonction du type de doc
			$result_type = spip_query("SELECT * FROM spip_types_documents WHERE id_type = $id_type");
			if ($type = @mysql_fetch_object($result_type)) {
				$extension = $type->extension;
			}
			list($fichier_vignette, $largeur_vignette, $hauteur_vignette) = vignette_par_defaut($extension);
		}
		
		// ajuster chemin d'acces au fichier
		if ($GLOBALS['flag_ecrire']) {
			if ($fichier) $fichier = "../$fichier";
			if ($fichier_vignette) $fichier_vignette = "../$fichier_vignette";
		}

		$fichier_vignette = ereg_replace("^IMG", "", $fichier_vignette);
		
		$image[0] = $fichier_vignette;
		return $image;
	}

}

function image_article($id_article){
	return cherche_image($id_article,'art');
}

function image_auteur($id_auteur){
	return cherche_image($id_auteur,'aut');
}

function image_breve($id_breve){
	return cherche_image($id_breve,'breve');
}

function image_site($id_syndic){
	return cherche_image($id_syndic,'site');
}

function image_mot($id_mot){
	return cherche_image($id_mot,'mot');
}

// recursif vers les rubriques parents
function image_rubrique($id_rubrique) {
	while ($id_rubrique) {
		$image = cherche_image($id_rubrique, 'rub');
		if ($image[0]) break;
		$result = spip_query("SELECT id_parent FROM spip_rubriques WHERE id_rubrique='$id_rubrique'");
		if ($row = mysql_fetch_array($result)) {
			$id_rubrique = $row['id_parent'];
		}
	}

	// idee : si on n'a toujours rien -> image de rubrique par defaut
	return $image;
}


// renvoie le html pour afficher le logo, avec ou sans survol, avec ou sans lien, etc.
function affiche_logos($arton, $artoff, $lien, $align) {
	global $num_survol;
	global $espace_logos;

	$num_survol++;
	if ($arton) {
		$imgsize = @getimagesize("IMG/$arton");
		$taille_image = ereg_replace("\"","'",$imgsize[3]);
		$milieu = "<IMG SRC='IMG/$arton' ALIGN='$align' ".
			" NAME='image$num_survol' ".$taille_image." BORDER='0' ALT=''".
			" HSPACE='$espace_logos' VSPACE='$espace_logos' class='spip_logos'>";

		if ($artoff) {
			if ($lien) {
				$afflien = "<A HREF='$lien'";
				$afflien2 = "A>";
			}
			else {
				$afflien = "<DIV";
				$afflien2 = "DIV>";
			}
			$milieu = "$afflien onMouseOver=\"image$num_survol.src=".
				"'IMG/$artoff'\" onMouseOut=\"image$num_survol.src=".
				"'IMG/$arton'\">$milieu</$afflien2";
		}
		else if ($lien) {
			$milieu = "<A HREF='$lien'>$milieu</A>";
		}
	} else {
		$milieu="";
	}
	return $milieu;
}


function construire_hierarchie($id_rubrique) {
	$hierarchie = "";
	$id_rubrique = intval($id_rubrique);
	while ($id_rubrique) {
		$hierarchie = $id_rubrique."-".$hierarchie;
		$query = "SELECT a.id_parent AS ida, b.id_parent AS idb ".
			"FROM spip_rubriques AS a LEFT JOIN spip_rubriques AS b ON (b.id_rubrique = a.id_parent) ".
			"WHERE a.id_rubrique = $id_rubrique";
		$result = spip_query($query);
		if ($row = mysql_fetch_array($result)) {
			if ($id_parent = $row['ida']) $hierarchie = $id_parent."-".$hierarchie;
			$id_grand_parent = $row['idb'];
		}
		else break;
		$id_rubrique = $id_grand_parent;
	}
	return $hierarchie;
}


//
// Critere {branche} : les descendants d'une rubrique
// On procede par generation - tous les fils, puis tous les petits-fils, etc.
//
function calcul_generation ($generation) {
	$lesfils = array();
	$result = spip_query("SELECT id_rubrique FROM spip_rubriques WHERE id_parent IN ($generation)"); 
	while ($row = mysql_fetch_array($result))
		$lesfils[] = $row['id_rubrique'];
	return join(",",$lesfils);
}
function calcul_branche ($generation) {
	if ($generation) {
		$branche[] = $generation;
		while ($generation = calcul_generation ($generation))
			$branche[] = $generation;
		return join(",",$branche);
	} else
		return '0';
}


//////////////////////////////////////////////////////////////////////////////
//
//              Calcul de la page
//
//////////////////////////////////////////////////////////////////////////////


//
// Classe utilisee pour l'execution des boucles
//

class InstanceBoucle {
	var $id_instance;

	// Proprietes de la boucle
	var $id_boucle;
	var $requete;
	var $type_requete;
	var $separateur;
	var $doublons;
	var $partie, $total_parties;

	// Stockage des resultats
	var $row, $num_rows;
	var $compteur_boucle, $total_boucle;
}


//
// Executer un squelette dans un contexte donne
//

function executer_squelette($squelette, $contexte) {
	global $pile_boucles;
	global $ptr_pile_boucles;
	static $fonctions_squelettes = '';

	$pile_boucles = '';
	$ptr_pile_boucles = 0;

	// Si squelette pas deja inclus, l'inclure
	if (!$fonctions_squelettes[$squelette]) {
		$squelette_cache = 'CACHE/skel_'.rawurlencode($squelette).'.php3';
		$use_cache = false;
		if (file_exists($squelette_cache)) {
			$t = filemtime($squelette_cache);
			if ((filemtime("$squelette.html") < $t)
			AND (filemtime("inc-calcul-squel.php3") < $t)
			AND (!file_exists("mes_fonctions.php3") OR (filemtime("mes_fonctions.php3") < $t))) {
				$use_cache = true;
			}
		}
		if ($GLOBALS['recalcul_squelettes'] == 'oui') {
			$use_cache = false;
		}

		// Au besoin, recompiler le squelette
		if (!$use_cache) {
			include_local ("inc-calcul-squel.php3");
			calculer_squelette($squelette, $squelette_cache);
		}

		// L'inclusion du squelette permet de definir les fonctions associees
		// aux boucles, et de recuperer le nom de la fonction principale	
		include($squelette_cache);
		$fonctions_squelettes[$squelette] = $func_squelette_executer;
		if ($GLOBALS['flag_apc']) {
			apc_rm($squelette_cache);
		}
	}

	// Executer la fonction principale du squelette
	// (i.e. racine de l'arbre d'execution)
	$f = $fonctions_squelettes[$squelette];
	return $f($contexte);
}


//
// Recherche recursive du squelette
//

function chercher_squelette_hierarchie($fond, $id_rubrique) {
	if (!$id_rubrique) {
		if (file_exists("$fond.html")) {
			return $fond;
		} else if (file_exists("$fond-dist.html")) {
			return "$fond-dist";
		} else {
			// erreur webmaster : $fond ne correspond a rien
			include_local ("ecrire/inc_presentation.php3");
			install_debut_html("Erreur sur le site");
			echo "<P>Aucun squelette <b>$fond</b> n'est disponible...</P>";
			install_fin_html();
			exit;
		}
	}
	else {
		if (file_exists($fond."-$id_rubrique.html")) {
			return "$fond-$id_rubrique";
		} else {
			$query = "SELECT id_parent FROM spip_rubriques WHERE id_rubrique='$id_rubrique'";
			$result = spip_query($query);
			while($row = mysql_fetch_array($result)) {
				$id_parent=$row['id_parent'];
			}
			return chercher_squelette_hierarchie($fond, $id_parent);
		}
	}
}

function chercher_squelette($fond, $id_rubrique) {
	// On selectionne, dans l'ordre :
	// fond=10.html, fond-10.html, fond-<rubriques parentes>.html, fond.html puis fond-dist.html
	if (($id_rubrique > 0) AND (file_exists($fond."=$id_rubrique.html"))) {
		return "$fond=$id_rubrique";
	}
	else {
		return chercher_squelette_hierarchie($fond, $id_rubrique); // recursif le long de la hierarchie
	}
}


//
// Calculer la page courante
//

function calculer_page($fond, $contexte) {
	global $id_doublons;

	static $pile_doublons = '';
	static $n_doublons = 0;

	$pile_doublons[++$n_doublons] = $id_doublons;

	$id_doublons = '';
	$id_doublons['articles'] = '0';
	$id_doublons['rubriques'] = '0';
	$id_doublons['breves'] = '0';
	$id_doublons['auteurs'] = '0';
	$id_doublons['forums'] = '0';
	$id_doublons['mots'] = '0';
	$id_doublons['syndication'] = '0';
	$id_doublons['documents'] = '0';

	$texte = executer_squelette($fond, $contexte);

	$id_doublons = $pile_doublons[$n_doublons--];

	return $texte;
}


function calculer_page_globale($fond) {
	global $id_doublons;
	global $contexte;
	global $fichier_requete;
	global $id_rubrique_fond;

	$contexte = '';
	$contexte_defaut = array('id_parent', 'id_rubrique', 'id_article', 'id_auteur',
		'id_breve', 'id_forum', 'id_secteur', 'id_syndic', 'id_mot', 'id_groupe', 'id_document');
	reset($contexte_defaut);
	while (list(, $val) = each($contexte_defaut)) {
		if ($GLOBALS[$val]) {
			$contexte[$val] = (int) $GLOBALS[$val];
		}
	}
	if ($GLOBALS["date"]) {
		$contexte["date"] = $GLOBALS["date"];
	}

	// Calcul de la rubrique associee a la requete
	// (selection de squelette specifique)

	if ($id_rubrique = $contexte['id_rubrique']) {
		$id_rubrique_fond = $id_rubrique;
	}
	else if ($id_breve  = $contexte['id_breve']) {
		$query = "SELECT id_rubrique FROM spip_breves WHERE id_breve='$id_breve'";
		$result = spip_query($query);
		while($row = mysql_fetch_array($result)) {
			$id_rubrique_fond = $row['id_rubrique'];
		}
	}
	else if ($id_syndic = $contexte['id_syndic']) {
		$query = "SELECT id_rubrique FROM spip_syndic WHERE id_syndic='$id_syndic'";
		$result = spip_query($query);
		while($row = mysql_fetch_array($result)) {
			$id_rubrique_fond = $row['id_rubrique'];
		}
	}
	else if ($id_article = $contexte['id_article']) {
		$query = "SELECT id_rubrique FROM spip_articles WHERE id_article='$id_article'";
		$result = spip_query($query);
		while($row = mysql_fetch_array($result)) {
			$id_rubrique_fond = $row['id_rubrique'];
		}
	}
	else {
		$id_rubrique_fond = 0;
	}

	$fond = chercher_squelette($fond, $id_rubrique_fond);

	recuperer_parametres_url($fond, $fichier_requete);

	// Special stats et boutons admin
	reset($contexte_defaut);
	while (list($key, $val) = each($contexte_defaut)) {
		if ($contexte[$val]) {
			$GLOBALS[$val] = $contexte[$val];
			$signale_globals .= '<'.'?php $GLOBALS[\''.$val.'\'] = '.(int) $contexte[$val]."; ?".">\n";
		}
	}

	return $signale_globals.calculer_page($fond, $contexte);
}

?>
