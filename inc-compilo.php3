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
// Fichier principal du compilateur de squelettes
//

// Ce fichier ne sera execute qu'une fois
if (defined("_INC_COMPILO")) return;
define("_INC_COMPILO", "1");


// Definition de la structure $p, et fonctions de recherche et de reservation
// dans l'arborescence des boucles
include_local("inc-compilo-index.php3");  # index ? structure ? pile ?

// definition des boucles
include_local("inc-boucles.php3");

// definition des criteres
include_local("inc-criteres.php3");

// definition des balises
include_local("inc-balises.php3");

// definition de l'API
include_local("inc-compilo-api.php3");

# definition des tables
include_ecrire('inc_serialbase.php3');

// outils pour debugguer le compilateur
#include_local("inc-compilo-debug.php3"); # desactive

//
// Calculer un <INCLURE()>
//
function calculer_inclure($struct, $descr, &$boucles, $id_boucle) {
	$fichier = $struct->texte;

	if (!($path = find_in_path($fichier)))
	  {
		spip_log("ERREUR: <INCLURE($fichier)> impossible");
		erreur_squelette(_T('zbug_info_erreur_squelette'),
				 "&lt;INCLURE($fichier)&gt; - "
				 ._T('fichier_introuvable', array('fichier' => $fichier)));
		return "'<!-- Erreur INCLURE(".texte_script($fichier).") -->'";
	}

	$l = array();
	foreach($struct->param as $val) {
		$var = array_shift($val);
		$l[] = "\'$var\' => \'' . addslashes(" .
		    ($val ? calculer_liste($val[0], $descr, $boucles, $id_boucle) :
		    (($var =='lang') ?
		     '$GLOBALS["spip_lang"]' :
		     index_pile($id_boucle, $var, $boucles)))
		    . ") . '\\'";
		}

	return "\n'<".
		"?php\n\t\$contexte_inclus = array(" .
		join(",\n\t",$l) .
		");" .
		"\n\tinclude(\\'$path\\');" .
		"\n?'." . "'>'";
 }

//
// calculer_boucle() produit le corps PHP d'une boucle Spip 
// (sauf les recursives)
// Ce corps est essentiellement une boucle while
// remplissant une variable $t0 retournee en valeur
//
function calculer_boucle($id_boucle, &$boucles) {

	$boucle = &$boucles[$id_boucle];
	$type_boucle = $boucle->type_requete;
	$return = $boucle->return;
	$id_table = $boucle->id_table;
	$primary = $boucle->primary;
	if($p = strpos($primary, ',')) {
		$id_field = $id_table . "." . substr($primary, 0, $p);
	} else {
		$id_field = $id_table . "." . $primary;
	}

	// La boucle doit-elle selectionner la langue ?
	// 1. par defaut, les boucles suivantes le font
	// "peut-etre", c'est-a-dire si forcer_lang == false.
	if (
		$type_boucle == 'articles'
		OR $type_boucle == 'rubriques'
		OR $type_boucle == 'hierarchie'
		OR $type_boucle == 'breves'
	) $lang_select = 'maybe';
	else
		$lang_select = false;

	// 2. a moins d'une demande explicite
	if ($boucle->lang_select == 'oui') $lang_select = 'oui';
	if ($boucle->lang_select == 'non') $lang_select = false;

	// Penser a demander le champ lang
	if ($lang_select)
		$boucle->select[] = 
			// cas des tables SPIP
			($id_table ? $id_table.'.' : '')
			// cas general ({lang_select} sur une table externe)
			. 'lang';

	// Calculer les invalideurs si c'est une boucle non constante
	$constant = ereg("^\(?'[^']*'\)?$",$return);

	if ((!$primary) || $constant)
		$invalide = '';
	else {
		$boucle->select[] = $id_field;

		$invalide = "\n			\$Cache['$primary']";
		if ($primary != 'id_forum')
			$invalide .= "[\$Pile[\$SP]['$primary']] = 1;";
		else
			$invalide .= "[calcul_index_forum(" . 
				// Retournera 4 [$SP] mais force la demande du champ a MySQL
				index_pile($id_boucle, 'id_article', $boucles) . ',' .
				index_pile($id_boucle, 'id_breve', $boucles) .  ',' .
				index_pile($id_boucle, 'id_rubrique', $boucles) .',' .
				index_pile($id_boucle, 'id_syndic', $boucles) .  ")] = 1;";
		$invalide .= ' // invalideurs';
	}

	// Cas {1/3} {1,4} {n-2,1}...

	$flag_cpt = $boucle->mode_partie || // pas '$compteur' a cause du cas 0
	  strpos($return,'compteur_boucle');

	//
	// Creer le debut du corps de la boucle :
	//
	$debut = '';
	if ($flag_cpt)
		$debut = "\n		\$Numrows['$id_boucle']['compteur_boucle']++;";

	if ($boucle->mode_partie)
		$debut .= "
		if (\$Numrows['$id_boucle']['compteur_boucle']-1 >= \$debut_boucle
		AND \$Numrows['$id_boucle']['compteur_boucle']-1 <= \$fin_boucle) {";
	
	if ($lang_select AND !$constant) {
		$selecteur = 
			(($lang_select == 'maybe') ? 
			'if (!$GLOBALS["forcer_lang"]) ':'')
			. 'if ($x = $Pile[$SP]["lang"]) $GLOBALS[\'spip_lang\'] = $x;'
			. ' // lang_select';
		$debut .= "\n			".$selecteur;
	}

	$debut .= $invalide;

	if ($boucle->doublons)
		$debut .= "\n			\$doublons['".$boucle->doublons."'] .= ','. " .
		index_pile($id_boucle, $primary, $boucles)
		. "; // doublons";


	if (count($boucle->separateur))
	  $code_sep = ("'" . ereg_replace("'","\'",join('',$boucle->separateur)) . "'"); 

	// gestion optimale des separateurs et des boucles constantes

	$corps = $debut . 
		((!$boucle->separateur) ? 
			(($constant && !$debut) ? $return :
			 	("\n\t\t" . '$t0 .= ' . $return . ";")) :
		 ("\n\t\t\$t1 " .
			((strpos($return, '$t1.') === 0) ? 
			 (".=" . substr($return,4)) :
			 ('= ' . $return)) .
		  ";\n\t\t" .
		  '$t0 .= (($t1 && $t0) ? ' . $code_sep . " : '') . \$t1;"));
     
	// Fin de parties
	if ($boucle->mode_partie)
		$corps .= "\n		}\n";

	$texte = '';

	// Gestion de la hierarchie (voir inc-boucles.php3)
	if ($boucle->hierarchie)
		$texte .= "\n	".$boucle->hierarchie;


	// si le corps est une constante, ne pas appeler le serveur N fois!
	if (ereg("^\(?'[^']*'\)?$",$corps)) {
		// vide ?
		if (($corps == "''") || ($corps == "('')")) {
			if (!$boucle->numrows)
				return 'return "";';
			else
				$corps = "";
		} else {
			$boucle->numrows = true;
			$corps = "\n		".'for($x=$Numrows["'.$id_boucle.'"]["total"];$x>0;$x--)
			$t0 .= ' . $corps .';';
		}
	} else {

	$corps = '

	// RESULTATS
	while ($Pile[$SP] = @spip_abstract_fetch($result,"' .
		  $boucle->sql_serveur .
		  '")) {'. "\n$corps\n	}\n";
		 

		// Memoriser la langue avant la boucle pour la restituer apres
		if ($lang_select) {
			$texte .= "\n	\$old_lang = \$GLOBALS['spip_lang'];";
			$corps .= "\n	\$GLOBALS['spip_lang'] = \$old_lang;";
		}
	}

	//
	// Requete
	//

	// hack critere recherche : ignorer la requete en cas de hash vide
	// Recherche : recuperer les hash a partir de la chaine de recherche
	if ($boucle->hash)
		$init =  '
	// RECHERCHE
	list($rech_select, $rech_where) = prepare_recherche($GLOBALS["recherche"], "'.$boucle->primary.'", "'.$boucle->id_table.'");
	if ($rech_select) ';

	if (!$order = $boucle->order
	AND !$order = $boucle->default_order)
		$order = "''";

	$init .= "\n\n	// REQUETE
	\$result = spip_abstract_select(\n\t\tarray(\"". 
		# En absence de champ c'est un decompte : 
	  	# prendre la primary pour avoir qqch
	  	# (COUNT incompatible avec le cas general
		(($boucle->select) ? 
			join("\",\n\t\t\"", array_unique($boucle->select)) :
			$id_field) .
		'"), # SELECT
		array("' .
		join('","', array_unique($boucle->from)) .
		'"), # FROM
		array(' .
		(!$boucle->where ? '' : ( '"' . join('",
		"', $boucle->where) . '"')) .
		"), # WHERE
		'".addslashes($boucle->group)."', # GROUP
		" . $order .", # ORDER
		" . (strpos($boucle->limit, 'intval') === false ?
			"'".$boucle->limit."'" :
			$boucle->limit). ", # LIMIT
		'".$boucle->sous_requete."', # sous
		".$boucle->compte_requete.", # compte
		'".$id_table."', # table
		'".$boucle->id_boucle."', # boucle
		'".$boucle->sql_serveur."'); # serveur";

	$init .= "\n	".'$t0 = "";
	$SP++;';
	if ($flag_cpt)
		$init .= "\n	\$Numrows['$id_boucle']['compteur_boucle'] = 0;";

	if ($boucle->mode_partie)
		$init .= calculer_parties($boucle->partie,
			$boucle->mode_partie,
			$boucle->total_parties,
			$id_boucle);
	else if ($boucle->numrows)
		$init .= "\n	\$Numrows['" .
			$id_boucle .
			"']['total'] = @spip_abstract_count(\$result,'" .
			$boucle->sql_serveur .
			"');";

	//
	// Conclusion et retour
	//
	$conclusion = "\n	@spip_abstract_free(\$result,'" .
	  $boucle->sql_serveur . "');";

	return $texte . $init . $corps . $conclusion;
}


//
// fonction traitant les criteres {1,n} (analyses dans inc-criteres)
//
## a deplacer dans inc-criteres ??
function calculer_parties($partie, $mode_partie, $total_parties, $id_boucle) {

	// Notes :
	// $debut_boucle et $fin_boucle sont les indices SQL du premier
	// et du dernier demandes dans la boucle : 0 pour le premier,
	// n-1 pour le dernier ; donc total_boucle = 1 + debut - fin

	// nombre total avant partition
	$retour = "\n\n	// Partition\n	" .
		'$nombre_boucle = @spip_abstract_count($result,"' .
		$boucle->sql_serveur .
		'");';

	ereg("([+-/])([+-/])?", $mode_partie, $regs);
	list(,$op1,$op2) = $regs;

	// {1/3}
	if ($op1 == '/') {
		$retour .= "\n	"
			.'$debut_boucle = ceil(($nombre_boucle * '
			. ($partie - 1) . ')/' . $total_parties . ");\n	"
			. '$fin_boucle = ceil (($nombre_boucle * '
			. $partie . ')/' . $total_parties . ") - 1;";
	}

	// {1,x}
	if ($op1 == '+') {
		$retour .= "\n	"
			. '$debut_boucle = ' . $partie . ';';
	}
	// {n-1,x}
	if ($op1 == '-') {
		$retour .= "\n	"
			. '$debut_boucle = $nombre_boucle - ' . $partie . ';';
	}
	// {x,1}
	if ($op2 == '+') {
		$retour .= "\n	"
			. '$fin_boucle = $debut_boucle + ' . $total_parties . ' - 1;';
	}
	// {x,n-1}
	if ($op2 == '-') {
		$retour .= "\n	"
			.'$fin_boucle = $debut_boucle + $nombre_boucle - '.$total_parties.' - 1;';
	}

	// Rabattre $fin_boucle sur le maximum
	$retour .= "\n	"
		.'$fin_boucle = min($fin_boucle, $nombre_boucle - 1);';

	// calcul du total boucle final
	$retour .= "\n	"
		.'$Numrows[\''.$id_boucle.'\']["total"] = max(0,$fin_boucle - $debut_boucle + 1);';

	return $retour;
}

// Production du code PHP a partir de la sequence livree par le phraseur
// $boucles est passe par reference pour affectation par index_pile.
// Retourne une expression PHP,
// (qui sera argument d'un Return ou la partie droite d'une affectation).

function calculer_liste($tableau, $descr, &$boucles, $id_boucle='') {
	if (!$tableau) return "''";
        $codes = array();
	$descr['niv']++;
	for ($i=0; $i<=$descr['niv']; $i++) $tab .= "\t";

	foreach ($tableau as $p) {

		switch($p->type) {
		// texte seul
		case 'texte':
			$code = "'".ereg_replace("([\\\\'])", "\\\\1", $p->texte)."'";

			$commentaire='';
			$avant='';
			$apres='';
			$altern = "''";
			break;

		case 'polyglotte':
			$code = "";
			foreach($p->traductions as $k => $v) {
			  $code .= ",'" .
			    ereg_replace("([\\\\'])", "\\\\1", $k) .
			    "' => '" .
			    ereg_replace("([\\\\'])", "\\\\1", $v) .
			    "'";
			}
			$code = "multi_trad(array(" .
 			  substr($code,1) .
			  "))";
			$commentaire= '';
			$avant='';
			$apres='';
			$altern = "''";
			break;

		// inclure
		case 'include':
			$code = calculer_inclure($p, $descr, $boucles, $id_boucle);
			$commentaire = '<INCLURE('.$p->texte.')>';
			$avant='';
			$apres='';
			$altern = "''";
			break;

		// boucle
		case 'boucle':
			$nom = $p->id_boucle;
			$newdescr = $descr;
			$newdescr['id_mere'] = $nom;
			$newdescr['niv']++;
			$code = 'BOUCLE' .
			  ereg_replace("-","_", $nom) . $descr['nom'] .
			  '($Cache, $Pile, $doublons, $Numrows, $SP)';
			$commentaire='';
			$avant = calculer_liste($p->avant,
				$newdescr, $boucles, $id_boucle);
			$apres = calculer_liste($p->apres,
				$newdescr, $boucles, $id_boucle);
			$newdescr['niv']--;
			$altern = calculer_liste($p->altern,
				$newdescr, $boucles, $id_boucle);
			break;

		case 'idiome':
			$p->code = "_T('" . $p->module . ":" .$p->nom_champ . "')";
			$p->id_boucle = $id_boucle;
			$p->boucles = &$boucles;
			$p->statut = 'php'; // ne pas manger les espaces avec trim()
			$commentaire = "";
			$code = applique_filtres($p);
			$avant='';
			$apres='';
			$altern = "''";
			break;

		default: 

			// cette structure pourrait etre completee des le phrase' (a faire)
			$p->id_boucle = $id_boucle;
			$p->boucles = &$boucles;
			$p->descr = $descr;
			$p->statut = 'html';
			$p->type_requete = $boucles[$id_boucle]->type_requete;

			$code = calculer_champ($p);
			$commentaire = '#' . $p->nom_champ . $p->etoile;
			$avant = calculer_liste($p->avant,
				$descr, $boucles, $id_boucle);
			$apres = calculer_liste($p->apres,
				$descr, $boucles, $id_boucle);
			$altern = "''";
			break;

		} // switch

		if ($avant == "''") $avant = '';
		if ($apres == "''") $apres = '';
		if ($avant||$apres||($altern!="''"))
		  {
		    $t = '$t' . $descr['niv'];
		    $res = (!$avant ? "" : "$avant . ") . 
		      $t .
		      (!$apres ? "" : " . $apres");
		    $code = "(($t = $code) ?\n\t$tab($res) :\n\t$tab($altern))";
		  }
		if ($code != "''")
		  $codes[]= $code .
		    // produire les commentaires � la fin,
		    // pour l'optimiseur (peut-etre plus vrai; a verifier)
		    (!$commentaire ? "" : ("/* $commentaire */ "));
	} // foreach

	$n = count($codes);
	return (!$n  ?  "''" : (($n==1) ? $codes[0] : 
				"(" . join ("\n$tab. ", $codes) . ")"));
}

// Prend en argument le source d'un squelette, sa grammaire et un nom.
// Retourne une fonction PHP/SQL portant ce nom et calculant une page HTML.
// Pour appeler la fonction produite, lui fournir 2 tableaux de 1 e'le'ment:
// - 1er: element 'cache' => nom (du fichier ou` mettre la page)
// - 2e: element 0 contenant un environnement ('id_article => $id_article, etc)
// Elle retourne alors un tableau de 4 e'le'ments:
// - 'texte' => page HTML, application du squelette a` l'environnement;
// - 'squelette' => le nom du squelette
// - 'process_ins' => 'html' ou 'php' selon la pre'sence de PHP dynamique
// - 'invalideurs' =>  de'pendances de cette page, pour invalider son cache.
// (voir son utilisation, optionnelle, dans invalideur.php)
// En cas d'erreur, elle retourne un tableau des 2 premiers elements seulement

function calculer_squelette($squelette, $nom, $gram, $sourcefile) {
# 3 variables qui sont en fait des constantes apr�s chargement
  global $table_primary, $table_des_tables, $tables_des_serveurs_sql;
	// Phraser le squelette, selon sa grammaire
	// pour le moment: "html" seul connu (HTML+balises BOUCLE)
	$boucles = '';
	spip_timer('calcul_skel');

	include_local("inc-$gram-squel.php3");

	$racine = phraser($squelette, '',$boucles, $nom);

	// tableau des informations sur le squelette
	$descr = array('nom' => $nom, 'documents' => false, 'sourcefile' => $sourcefile);

	if ($boucles) {
	  // une boucle documents est conditionnee par tout le reste!
		foreach($boucles as $idb => $boucle) {
			if ($boucle->param) {
				if (($boucle->type_requete == 'documents') && 
				     $boucle->doublons)
				  { $descr['documents'] = true; break; }
			}
		  }
	// Commencer par reperer les boucles appelees explicitement 
	// car elles indexent les arguments de maniere derogatoire
		foreach($boucles as $id => $boucle) { 
			if ($boucle->type_requete == 'boucle') {
				$rec = &$boucles[$boucle->param[0]];
				if (!$rec) {
					return array(_T('zbug_info_erreur_squelette'),
						($boucle->param[0]
						. ' '. _T('zbug_boucle_recursive_undef')));
				} else {
					$rec->externe = $id;
					$descr['id_mere'] = $id;
					$boucles[$id]->return =
						calculer_liste(array($rec),
							 $descr,
							 $boucles,
							 $boucle->param);
				}
			}
		}
		foreach($boucles as $id => $boucle) { 
			$type = $boucle->type_requete;
			if ($type != 'boucle') {
				$boucles[$id]->id_table = $table_des_tables[$type];
				if ($boucles[$id]->id_table) {
					$boucles[$id]->primary = $table_primary[$type];
				} else { 
					// table non Spip.
					$boucles[$id]->id_table = $type;
					$serveur = $boucle->sql_serveur;
					$x = &$tables_des_serveurs_sql[$serveur ? $serveur : 'localhost'][$type]['key'];
					$boucles[$id]->primary = ($x["PRIMARY KEY"] ? $x["PRIMARY KEY"] : $x["KEY"]);
				}
				if ($boucle->param) {
					$res = calculer_criteres($id, $boucles);
					if (is_array($res))
						return $res; # erreur
				}
				$descr['id_mere'] = $id;
				$boucles[$id]->return =
				calculer_liste($boucle->milieu,
					 $descr,
					 $boucles,
					 $id);
			}
		}
	}

	// idem pour la racine
	$descr['id_mere'] = '';
	$corps = calculer_liste($racine, $descr, $boucles);

	// Corps de toutes les fonctions PHP,
	// en particulier les requetes SQL et TOTAL_BOUCLE
	// de'terminables seulement maintenant
	// Les 3 premiers parame`tres sont passe's par re'fe'rence
	// (sorte d'environnements a` la Lisp 1.5)
	// sauf pour la fonction principale qui recoit les initialisations

	$code = '';
	if ($boucles) {

		foreach($boucles as $id => $boucle) {
			// appeler la fonction de definition de la boucle
			$f = 'boucle_'.strtoupper($boucle->type_requete);
			// si pas de definition perso, definition spip
			if (!function_exists($f)) $f = $f.'_dist';
			// laquelle a une definition par defaut
			if (!function_exists($f)) $f = 'boucle_DEFAUT';
			$boucles[$id]->return = $f($id, $boucles);
		}

		foreach($boucles as $id => $boucle) {

			// Indiquer la boucle en commentaire
			$pretty = "BOUCLE$id ".strtoupper($boucle->type_requete);
			// anachronique. A refaire.
			/*    if ($boucle->param && is_array($boucle->param)) 
			 $pretty .= " {".join("} {", $boucle->param)."}";
			// sans oublier les parametres traites en amont
		    if ($boucle->separateur)
		      foreach($boucle->separateur as $v)
			$pretty .= ' {"'. htmlspecialchars($v) . '"}';
		    if ($boucle->tout)
			  $pretty .= '{tout}';
			if ($boucle->plat)
			  $pretty .= '{plat}'; */
			$pretty = ereg_replace("[\r\n]", " ", $pretty);

			// Puis envoyer son code
			$codeboucle = "\n//\n// $pretty\n//\n"
			."function BOUCLE" . ereg_replace("-","_",$id) . $nom .
			'(&$Cache, &$Pile, &$doublons, &$Numrows, $SP) {' .
			$boucle->return;

			$fincode = "\n	return \$t0;"
			."\n}\n\n";

			## inserer les elements pour le debuggueur, a deux niveaux :
			## 1) apres le calcul d'une boucle compilee, envoyer le code
			##    compile vers boucle_debug_compile()
			## 2) le resultat de la boucle, lui, sera plus tard envoye vers
			##    boucle_debug_resultat()
			if ($GLOBALS['var_mode'] == 'debug') {
				boucle_debug_compile ($id, $nom, $pretty,
					$sourcefile, $codeboucle.$fincode);
				$codedebug = "
	boucle_debug_resultat('$id', '$nom', \$t0);";
			}

			$code .= $codeboucle.$codedebug.$fincode;
		}
	}

	$secondes = spip_timer('calcul_skel');
	spip_log("calcul skel $sourcefile ($secondes)");

	if (is_array($boucles))
		$aff_boucles = join (', ', array_keys($boucles));
	else
		$aff_boucles = "pas de boucle";

	$squelette_compile = "<"."?php
/*
 * Squelette : $sourcefile
 * Date :      ".http_gmoddate(@filemtime($sourcefile))." GMT
 * Compile :   ".http_gmoddate(time())." GMT ($secondes)
 * Boucles :   ".$aff_boucles."
 */
$code

//
// Fonction principale du squelette $sourcefile
//
function $nom (\$Cache, \$Pile, \$doublons=array(), \$Numrows='', \$SP=0) {
\$t0 = $corps;

	return array(
		'texte' => \$t0,
		'squelette' => '$nom',
		'process_ins' => ((strpos(\$t0,'<'.'?')=== false) ? 'html' : 'php'),
		'invalideurs' => \$Cache
	);
}

?".">";

	if ($GLOBALS['var_mode'] == 'debug')
		squelette_debug_compile($nom, $sourcefile, $squelette_compile);
	return $squelette_compile;

}

?>
