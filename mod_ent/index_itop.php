<?php
/*
 *
 * Copyright 2001-2012 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Stephane Boireau
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPI; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Initialisations files
require_once("../lib/initialisations.inc.php");

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}


$sql="SELECT 1=1 FROM droits WHERE id='/mod_ent/index_itop.php';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/mod_ent/index_itop.php',
administrateur='V',
professeur='F',
cpe='F',
scolarite='F',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='Rapprochement des comptes ENT/GEPI : ENT ITOP',
statut='';";
$insert=mysql_query($sql);
}
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}

$msg="";
$mode=isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : NULL);

function formate_date2($chaine, $from, $to) {
	$retour=$chaine;

	if($from=="jj/mm/aaaa") {
		$tab=explode("/", $chaine);

		if(isset($tab[2])) {
			if($to=="aaaammjj") {
				if($tab[2]>100) {
					$retour=$tab[2].sprintf($tab[1], "%1$02d").sprintf($tab[0], "%1$02d");
				}
			}
		}
	}

	return $retour;
}

// Menage:
$sql="DELETE FROM sso_table_correspondance WHERE login_gepi='' OR login_sso='';";
$menage=mysql_query($sql);

if((isset($_POST['temoin_suhosin_1']))&&(!isset($_POST['temoin_suhosin_2']))) {
	$msg.="Il semble que certaines variables n'ont pas été transmises.<br />Cela peut arriver lorsqu'on tente de transmettre (<em>cocher trop de cases</em>) trop de variables.<br />Vous devriez tenter de cocher moins de cases et vous y prendre en plusieurs fois.<br />";
}

if(isset($_POST['recherche'])) {
	check_token();

	$nom_rech=isset($_POST['nom_rech']) ? $_POST['nom_rech'] : "";
	$prenom_rech=isset($_POST['prenom_rech']) ? $_POST['prenom_rech'] : "";

	$nom_rech=preg_replace("/[^A-Za-z\-]/","%",$nom_rech);
	$prenom_rech=preg_replace("/[^A-Za-z\-]/","%",$prenom_rech);

	if(($nom_rech!="")||($prenom_rech!="")) {

		if($_POST['recherche']=='recherche_eleve') {

			$sql="SELECT login,nom,prenom,naissance FROM eleves WHERE nom LIKE '%".$nom_rech."%' AND prenom LIKE '%".$prenom_rech."%';";

			$res=@mysql_query($sql);

			if(mysql_num_rows($res)==0){
				$chaine="Aucun résultat retourné.";
			}
			else {
				$chaine="<table class='boireaus'>";
				$chaine.="<tr style='background-color: white;'>";

				$chaine.="<th>";
				$chaine.="Nom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Prenom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Naissance";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Classe";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Login";
				$chaine.="</th>";

				$chaine.="</tr>";

				$alt=-1;
				while($lig=mysql_fetch_object($res)){
					//$chaine.="<tr>";

					$alt=$alt*(-1);
					$chaine.="<tr style='background-color:";
					if($alt==1){
						$chaine.="silver";
					}
					else{
						$chaine.="white";
					}
					$chaine.="; text-align: center;'>";

					$chaine.="<td>";
					$chaine.="$lig->nom";
					$chaine.="</td>";

					$chaine.="<td>";
					$chaine.="$lig->prenom";
					$chaine.="</td>";

					$chaine.="<td>";
					$chaine.=formate_date($lig->naissance);
					$chaine.="</td>";

					$chaine.="<td>";
					if($lig->login!=""){
						$tmp_tab_class=get_class_from_ele_login($lig->login);
						if(isset($tmp_tab_class['liste_nbsp'])) {
							$chaine.=$tmp_tab_class['liste_nbsp'];
						}
					}
					$chaine.="</td>";

					$chaine.="<td>";
					if($lig->login!=""){
						$chaine.="<a href='#' onClick=\"document.getElementById(document.getElementById('login_recherche').value).value='$lig->login';cacher_div('div_search');return false;\">$lig->login</a>";
					}
					else{
						$chaine.="<span style='color:red'>Non renseigné</span>";
					}
					$chaine.="</td>";

					$chaine.="</tr>";
				}
				$chaine.="</table>";
				//$chaine.="$sql";
			}
			echo $chaine;
		}
		elseif($_POST['recherche']=='recherche_responsable') {

			$sql="SELECT login,nom,prenom,pers_id FROM resp_pers WHERE nom LIKE '%".$nom_rech."%' AND prenom LIKE '%".$prenom_rech."%';";

			$res=@mysql_query($sql);

			if(mysql_num_rows($res)==0){
				$chaine="Aucun résultat retourné.";
			}
			else {
				$chaine="<table class='boireaus'>";
				$chaine.="<tr style='background-color: white;'>";

				$chaine.="<th>";
				$chaine.="Nom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Prenom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Resp.de";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Login";
				$chaine.="</th>";

				$chaine.="</tr>";

				$alt=-1;
				while($lig=mysql_fetch_object($res)){
					//$chaine.="<tr>";

					$alt=$alt*(-1);
					$chaine.="<tr style='background-color:";
					if($alt==1){
						$chaine.="silver";
					}
					else{
						$chaine.="white";
					}
					$chaine.="; text-align: center;'>";

					$chaine.="<td>";
					$chaine.="$lig->nom";
					$chaine.="</td>";

					$chaine.="<td>";
					$chaine.="$lig->prenom";
					$chaine.="</td>";

					$chaine.="<td style='font-size:x-small'>";
					$tmp_tab_ele=get_enfants_from_pers_id($lig->pers_id,'avec_classe');
					$cpt_ele=0;
					for($loop=0;$loop<count($tmp_tab_ele);$loop++) {
						if($cpt_ele>0) {
							$chaine.=", ";
						}
						$chaine.=$tmp_tab_ele[$loop];
					}
					$chaine.="</td>";

					$chaine.="<td>";
					if($lig->login!=""){
						$chaine.="<a href='#' onClick=\"document.getElementById(document.getElementById('login_recherche').value).value='$lig->login';cacher_div('div_search');return false;\">$lig->login</a>";
					}
					else{
						$chaine.="<span style='color:red'>Non renseigné</span>";
					}
					$chaine.="</td>";

					$chaine.="</tr>";
				}
				$chaine.="</table>";
				//$chaine.="$sql";
			}
			echo $chaine;
		}
		elseif($_POST['recherche']=='recherche_personnel') {

			$sql="SELECT login,nom,prenom,statut FROM utilisateurs WHERE nom LIKE '%".$nom_rech."%' AND prenom LIKE '%".$prenom_rech."%';";

			$res=@mysql_query($sql);

			if(mysql_num_rows($res)==0){
				$chaine="Aucun résultat retourné.";
			}
			else {
				$chaine="<table class='boireaus'>";
				$chaine.="<tr style='background-color: white;'>";

				$chaine.="<th>";
				$chaine.="Nom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Prenom";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Statut";
				$chaine.="</th>";

				$chaine.="<th>";
				$chaine.="Login";
				$chaine.="</th>";

				$chaine.="</tr>";

				$alt=-1;
				while($lig=mysql_fetch_object($res)){
					//$chaine.="<tr>";

					$alt=$alt*(-1);
					$chaine.="<tr style='background-color:";
					if($alt==1){
						$chaine.="silver";
					}
					else{
						$chaine.="white";
					}
					$chaine.="; text-align: center;'>";

					$chaine.="<td>";
					$chaine.="$lig->nom";
					$chaine.="</td>";

					$chaine.="<td>";
					$chaine.="$lig->prenom";
					$chaine.="</td>";

					$chaine.="<td style='font-size:x-small'>";
					$chaine.=$lig->statut;
					$chaine.="</td>";

					$chaine.="<td>";
					if($lig->login!=""){
						$chaine.="<a href='#' onClick=\"document.getElementById(document.getElementById('login_recherche').value).value='$lig->login';cacher_div('div_search');return false;\">$lig->login</a>";
					}
					else{
						$chaine.="<span style='color:red'>Non renseigné</span>";
					}
					$chaine.="</td>";

					$chaine.="</tr>";
				}
				$chaine.="</table>";
				//$chaine.="$sql";
			}
			echo $chaine;
		}
	}
	die();
}

if(isset($_POST['enregistrement_eleves'])) {
	check_token();

	$ligne=isset($_POST['ligne']) ? $_POST['ligne'] : NULL;

	$nb_reg=0;
	$nb_pas_dans_eleves=0;

	if(!isset($ligne)) {
		$msg="Aucun enregistrement d'association n'a été demandé.<br />";
	}
	else {
		$ligne_tempo2=array();
		$sql="SELECT * FROM tempo2_sso;";
		$res=mysql_query($sql);
		while($lig=mysql_fetch_object($res)) {
			$ligne_tempo2[$lig->col1]=$lig->col2;
		}

		for($loop=0;$loop<count($ligne);$loop++) {
			// On a eu un choix de login
			if((isset($_POST['login_'.$ligne[$loop]]))&&($_POST['login_'.$ligne[$loop]]!='')) {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT login FROM eleves WHERE login='".mysql_real_escape_string($_POST['login_'.$ligne[$loop]])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$msg.="Le login élève Gepi ".$_POST['login_'.$ligne[$loop]]." proposé n'existe pas.<br />\n";
					}
					else {
						$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
						$test=mysql_query($sql);
						if(mysql_num_rows($test)==0) {
							$sql="SELECT * FROM sso_table_correspondance WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
							$test=mysql_query($sql);
							if(mysql_num_rows($test)==0) {
								$sql="INSERT INTO sso_table_correspondance SET login_gepi='".$_POST['login_'.$ligne[$loop]]."', login_sso='".mysql_real_escape_string($tab[0])."';";
								$insert=mysql_query($sql);
								if(!$insert) {
									$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
							else {
								$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
								$update=mysql_query($sql);
								if(!$update) {
									$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
						}
						else {
							$lig=mysql_fetch_object($test);
							$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
						}
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
			else {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$naissance=(isset($tab[5])) ? $tab[5] : "";
						if(!preg_match("#[0-9]{2}/[0-9]{2}/[0-9]{4}#", $naissance)) {$naissance="";}

						/*
						if($naissance!="") {
							$sql="SELECT * FROM eleves WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' AND naissance='".formate_date2($naissance, "jj/mm/aaaa", "aaaammjj")."'";
						}
						else {
							$sql="SELECT * FROM eleves WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' ORDER BY naissance;";
						}
						*/
						// Les accents ne sont pas correctement interprétés
						$nom_remplacement=preg_replace("/[^A-Za-z]/","%",$tab[1]);
						$prenom_remplacement=preg_replace("/[^A-Za-z]/","%",$tab[2]);
						if($naissance!="") {
							$sql="SELECT * FROM eleves WHERE nom LIKE '".$nom_remplacement."' AND prenom LIKE '".$prenom_remplacement."' AND naissance='".formate_date2($naissance, "jj/mm/aaaa", "aaaammjj")."'";
						}
						else {
							$sql="SELECT * FROM eleves WHERE nom LIKE '".$nom_remplacement."' AND prenom LIKE '".$prenom_remplacement."' ORDER BY naissance;";
						}
						//echo "$sql<br />";
						$res=mysql_query($sql);
						if(mysql_num_rows($res)==0) {
							$msg.="Aucun enregistrement dans la table 'eleves' pour ".$tab[1]." ".$tab[2]." !<br />\n";
							$nb_pas_dans_eleves++;
						}
						elseif(mysql_num_rows($res)==1) {
							// Un seul élève correspond
							$lig=mysql_fetch_object($res);

							$sql="SELECT 1=1 FROM sso_table_correspondance WHERE login_gepi='$lig->login';";
							//echo "$sql<br />";
							$test=mysql_query($sql);
							if(mysql_num_rows($test)==0) {
								$sql="INSERT INTO sso_table_correspondance SET login_gepi='$lig->login', login_sso='".mysql_real_escape_string($tab[0])."';";
								//echo "$sql<br />";
								$insert=mysql_query($sql);
								if(!$insert) {
									$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$lig->login."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
							else {
								$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='$lig->login';";
								//echo "$sql<br />";
								$update=mysql_query($sql);
								if(!$update) {
									$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$lig->login."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
						}
						else {
							// On ne doit pas arriver là
							$msg.="Plusieurs enregistrements dans la table 'eleves' pour ".$tab[1]." ".$tab[2]." !<br />\n";
						}
					}
					else {
						$lig=mysql_fetch_object($test);
						$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
		}
	}

	if($nb_pas_dans_eleves>0) {
		$msg.="<br />$nb_pas_dans_eleves comptes de l'ENT n'ont pas été trouvés dans la table 'eleves' de Gepi.<br />Sont-ce des élèves de l'année précédente ?<br />\n";
	}

	if($nb_reg>0) {
		$msg.="<br />$nb_reg enregistrement(s) effectué(s).<br />\n";
	}

	$mode="consult_eleves";
}


if(isset($_POST['enregistrement_responsables'])) {
	check_token();

	$ligne=isset($_POST['ligne']) ? $_POST['ligne'] : NULL;

	$nb_reg=0;

	if(!isset($ligne)) {
		$msg="Aucun enregistrement d'association n'a été demandée.<br />";
	}
	else {
		$ligne_tempo2=array();
		$sql="SELECT * FROM tempo2_sso;";
		$res=mysql_query($sql);
		while($lig=mysql_fetch_object($res)) {
			$ligne_tempo2[$lig->col1]=$lig->col2;
		}

		for($loop=0;$loop<count($ligne);$loop++) {
			// On a eu un choix de login
			if((isset($_POST['login_'.$ligne[$loop]]))&&($_POST['login_'.$ligne[$loop]]!='')) {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT login FROM resp_pers WHERE login='".mysql_real_escape_string($_POST['login_'.$ligne[$loop]])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$msg.="Le login responsable Gepi ".$_POST['login_'.$ligne[$loop]]." proposé n'existe pas.<br />\n";
					}
					else {
						$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
						$test=mysql_query($sql);
						if(mysql_num_rows($test)==0) {
							$sql="SELECT * FROM sso_table_correspondance WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
							$test=mysql_query($sql);
							if(mysql_num_rows($test)==0) {
								$sql="INSERT INTO sso_table_correspondance SET login_gepi='".$_POST['login_'.$ligne[$loop]]."', login_sso='".mysql_real_escape_string($tab[0])."';";
								$insert=mysql_query($sql);
								if(!$insert) {
									$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
							else {
								$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
								$update=mysql_query($sql);
								if(!$update) {
									$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
						}
						else {
							$lig=mysql_fetch_object($test);
							$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
						}
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
			else {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {

						$chaine="";
						if((isset($tab[5]))&&($tab[5]!="")) {
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[5])."'";
						}
						if((isset($tab[6]))&&($tab[6]!="")) {
							if($chaine!="") {$chaine.=" OR ";}
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[6])."'";
						}
						if((isset($tab[7]))&&($tab[7]!="")) {
							if($chaine!="") {$chaine.=" OR ";}
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[7])."'";
						}

						$cpt_resp=0;
						$tab_resp=array();
						$tab_resp_login=array();
						if($chaine!="") {
							$sql="SELECT e.* FROM eleves e, sso_table_correspondance s WHERE ($chaine) AND e.login=s.login_gepi ORDER BY e.nom, e.prenom;";
							//echo "$sql<br />";
							$res_ele=mysql_query($sql);
							if(mysql_num_rows($res_ele)>0) {
								$cpt_ele=0;
								while($lig_ele=mysql_fetch_object($res_ele)) {
									$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.pers_id=rp.pers_id AND r.ele_id='$lig_ele->ele_id' AND rp.nom='".mysql_real_escape_string($tab[1])."' AND rp.prenom='".mysql_real_escape_string($tab[2])."' AND rp.login!='';";
									//echo "$sql<br />";
									$res_resp=mysql_query($sql);
									if(mysql_num_rows($res_resp)>0) {
										while($lig_resp=mysql_fetch_object($res_resp)) {
											if(!in_array($lig_resp->login, $tab_resp_login)) {
												$tab_resp_login[]=$lig_resp->login;
												$tab_resp[$cpt_resp]['login']=$lig_resp->login;
												$tab_resp[$cpt_resp]['civilite']=$lig_resp->civilite;
												$tab_resp[$cpt_resp]['nom']=$lig_resp->nom;
												$tab_resp[$cpt_resp]['prenom']=$lig_resp->prenom;
												$tab_resp[$cpt_resp]['resp_legal']=$lig_resp->resp_legal;
												$tab_resp[$cpt_resp]['info']=$lig_resp->civilite." ".casse_mot($lig_resp->nom,'maj')." ".casse_mot($lig_resp->prenom,'majf2');
												$tab_resp[$cpt_resp]['info'].=" (N° ".$lig_resp->pers_id.")";

												$tab_resp[$cpt_resp]['info'].=" (Légal ".$lig_resp->resp_legal.")";
												$cpt_resp++;
											}
										}
									}
									else {
										$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.pers_id=rp.pers_id AND r.ele_id='$lig_ele->ele_id' AND rp.login!='';";
										//echo "$sql<br />";
										$res_resp=mysql_query($sql);
										if(mysql_num_rows($res_resp)>0) {
											while($lig_resp=mysql_fetch_object($res_resp)) {
												if(!in_array($lig_resp->login, $tab_resp_login)) {
													$tab_resp_login[]=$lig_resp->login;
													$tab_resp[$cpt_resp]['login']=$lig_resp->login;
													$tab_resp[$cpt_resp]['civilite']=$lig_resp->civilite;
													$tab_resp[$cpt_resp]['nom']=$lig_resp->nom;
													$tab_resp[$cpt_resp]['prenom']=$lig_resp->prenom;
													$tab_resp[$cpt_resp]['resp_legal']=$lig_resp->resp_legal;
													$tab_resp[$cpt_resp]['info']=$lig_resp->civilite." ".casse_mot($lig_resp->nom,'maj')." ".casse_mot($lig_resp->prenom,'majf2');
													$tab_resp[$cpt_resp]['info'].=" (N° ".$lig_resp->pers_id.")";
													$tab_resp[$cpt_resp]['info'].=" (Légal ".$lig_resp->resp_legal.")";
													$cpt_resp++;
												}
											}
										}
									}
								}
							}
							/*
							echo "<pre>";
							print_r($tab_resp);
							echo "</pre>";
							*/
							if(count($tab_resp)==1) {
								// Un seul responsable correspond
								$lig=mysql_fetch_object($res);

								$sql="SELECT 1=1 FROM sso_table_correspondance WHERE login_gepi='".$tab_resp_login[0]."';";
								$test=mysql_query($sql);
								if(mysql_num_rows($test)==0) {
									$sql="INSERT INTO sso_table_correspondance SET login_gepi='".$tab_resp_login[0]."', login_sso='".mysql_real_escape_string($tab[0])."';";
									$insert=mysql_query($sql);
									if(!$insert) {
										$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$tab_resp_login[0]."<br />\n";
									}
									else {
										$nb_reg++;
									}
								}
								else {
									$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='".$tab_resp_login[0]."';";
									$update=mysql_query($sql);
									if(!$update) {
										$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$tab_resp_login[0]."<br />\n";
									}
									else {
										$nb_reg++;
									}
								}
							}
							else {
								$msg.="Responsable ".$tab[1]." ".$tab[2]." non identifié.<br />\n";
							}
						}
						else {
							$msg.="Aucun élève associé n'a été trouvé.<br />\n";
						}
					}
					else {
						$lig=mysql_fetch_object($test);
						$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
		}
	}

	if($nb_reg>0) {
		$msg.="$nb_reg enregistrement(s) effectué(s).<br />\n";
	}

	$mode="consult_responsables";
}


if(isset($_POST['enregistrement_personnels'])) {
	check_token();

	$ligne=isset($_POST['ligne']) ? $_POST['ligne'] : NULL;

	$nb_reg=0;

	if(!isset($ligne)) {
		$msg="Aucun enregistrement d'association n'a été demandée.<br />";
	}
	else {
		$ligne_tempo2=array();
		$sql="SELECT * FROM tempo2_sso;";
		$res=mysql_query($sql);
		while($lig=mysql_fetch_object($res)) {
			$ligne_tempo2[$lig->col1]=$lig->col2;
		}

		for($loop=0;$loop<count($ligne);$loop++) {
			// On a eu un choix de login
			if((isset($_POST['login_'.$ligne[$loop]]))&&($_POST['login_'.$ligne[$loop]]!='')) {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT login FROM utilisateurs WHERE login='".mysql_real_escape_string($_POST['login_'.$ligne[$loop]])."' AND statut!='eleve' AND statut!='responsable';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$msg.="Le login de personnel Gepi ".$_POST['login_'.$ligne[$loop]]." proposé n'existe pas.<br />\n";
					}
					else {
						$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
						$test=mysql_query($sql);
						if(mysql_num_rows($test)==0) {
							$sql="SELECT * FROM sso_table_correspondance WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
							$test=mysql_query($sql);
							if(mysql_num_rows($test)==0) {
								$sql="INSERT INTO sso_table_correspondance SET login_gepi='".$_POST['login_'.$ligne[$loop]]."', login_sso='".mysql_real_escape_string($tab[0])."';";
								$insert=mysql_query($sql);
								if(!$insert) {
									$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
							else {
								$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='".$_POST['login_'.$ligne[$loop]]."';";
								$update=mysql_query($sql);
								if(!$update) {
									$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$_POST['login_'.$ligne[$loop]]."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
						}
						else {
							$lig=mysql_fetch_object($test);
							$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
						}
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
			else {
				if(isset($ligne_tempo2[$ligne[$loop]])) {
					$tab=explode(";", $ligne_tempo2[$ligne[$loop]]);

					$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {

						$sql="SELECT * FROM utilisateurs WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' AND statut!='eleve' AND statut!='responsable';";
						$res=mysql_query($sql);
						if(mysql_num_rows($res)==1) {
							// Un seul élève correspond
							$lig=mysql_fetch_object($res);

							$sql="SELECT 1=1 FROM sso_table_correspondance WHERE login_gepi='$lig->login';";
							$test=mysql_query($sql);
							if(mysql_num_rows($test)==0) {
								$sql="INSERT INTO sso_table_correspondance SET login_gepi='$lig->login', login_sso='".mysql_real_escape_string($tab[0])."';";
								$insert=mysql_query($sql);
								if(!$insert) {
									$msg.="Erreur lors de l'insertion de l'association ".$tab[0]." &gt; ".$lig->login."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
							else {
								$sql="UPDATE sso_table_correspondance SET login_sso='".mysql_real_escape_string($tab[0])."' WHERE login_gepi='$lig->login';";
								$update=mysql_query($sql);
								if(!$update) {
									$msg.="Erreur lors de la mise à jour de l'association ".$tab[0]." &gt; ".$lig->login."<br />\n";
								}
								else {
									$nb_reg++;
								}
							}
						}
						elseif(mysql_num_rows($res)==0) {
							$msg.="Aucun enregistrement dans la table 'utilisateurs' pour ".$tab[1]." ".$tab[2]." !<br />\n";
						}
						else {
							// On ne doit pas arriver là
							$msg.="Plusieurs enregistrements dans la table 'utilisateurs' pour ".$tab[1]." ".$tab[2]." !<br />\n";
						}
					}
					else {
						$lig=mysql_fetch_object($test);
						$msg.="Le GUID $tab[0] est déjà associé à $lig->login_gepi<br />\n";
					}
				}
				else {
					$msg.="Aucun enregistrement pour la ligne $loop<br />";
				}
			}
		}
	}

	if($nb_reg>0) {
		$msg.="$nb_reg enregistrement(s) effectué(s).<br />\n";
	}

	$mode="consult_personnels";
}

if(isset($_POST['enregistrement_saisie_manuelle'])) {
	check_token();

	$login_gepi=isset($_POST["login_gepi"]) ? $_POST["login_gepi"] : NULL;
	$login_sso=isset($_POST["login_sso"]) ? $_POST["login_sso"] : NULL;

	$nb_reg=0;
	if((!isset($login_gepi))||(!isset($login_gepi))||($login_sso=='')||($login_gepi=='')) {
		$msg.="Un des champs login_gepi ou guid n'a pas été transmis.<br />\n";
	}
	else {
		$sql="SELECT * FROM sso_table_correspondance WHERE login_sso='".$login_sso."';";
		$test=mysql_query($sql);
		if(mysql_num_rows($test)==0) {
			$sql="SELECT statut FROM utilisateurs WHERE login='$login_gepi';";
			$res=mysql_query($sql);
			if(mysql_num_rows($res)==1) {
				$statut_compte=mysql_result($res, 0, "statut");

				$sql="SELECT 1=1 FROM sso_table_correspondance WHERE login_gepi='$login_gepi';";
				$test=mysql_query($sql);
				if(mysql_num_rows($test)==0) {
					$sql="INSERT INTO sso_table_correspondance SET login_gepi='$login_gepi', login_sso='$login_sso';";
					$insert=mysql_query($sql);
					if(!$insert) {
						$msg.="Erreur lors de l'insertion de l'association ".$login_sso." &gt; ".$login_gepi."<br />\n";
					}
					else {
						$nb_reg++;
					}
				}
				else {
					$sql="UPDATE sso_table_correspondance SET login_sso='".$login_sso."' WHERE login_gepi='$login_gepi';";
					$update=mysql_query($sql);
					if(!$update) {
						$msg.="Erreur lors de la mise à jour de l'association ".$login_sso." &gt; ".$login_gepi."<br />\n";
					}
					else {
						$nb_reg++;
					}
				}
			}
			elseif(mysql_num_rows($res)==0) {
				$msg.="Aucun enregistrement dans la table 'eleves' pour $login_gepi<br />\n";
			}
			else {
				$msg.="Anomalie : Plusieurs enregistrements dans la table 'eleves' pour $login_gepi<br />\n";
			}
		}
		else {
			$lig=mysql_fetch_object($test);
			$msg.="Le GUID $login_sso est déjà associé à $lig->login_gepi<br />\n";
		}
	}

	if($nb_reg>0) {
		$msg.="$nb_reg enregistrement effectué.<br />\n";

		if($statut_compte=='eleve') {
			$mode="consult_eleves";
		}
		elseif($statut_compte=='responsable') {
			$mode="consult_eleves";
		}
		else {
			$mode="consult_personnels";
		}
	}
}

if(isset($_POST['suppr'])) {
	check_token();

	$cpt_suppr=0;
	$suppr=$_POST['suppr'];
	for($loop=0;$loop<count($suppr);$loop++) {
		$sql="DELETE FROM sso_table_correspondance WHERE login_gepi='".$suppr[$loop]."';";
		$menage=mysql_query($sql);
		if(!$menage) {
			$msg.="Erreur lors de la suppression de ".$suppr[$loop]."<br />";
		}
		else {
			$cpt_suppr++;
		}
	}

	if($cpt_suppr>0) {
		$msg.="$cpt_suppr association(s) supprimée(s).<br />";
	}
}

if($mode=='vider') {
	check_token();

	$sql="TRUNCATE sso_table_correspondance;";
	$menage=mysql_query($sql);
	if($menage) {
		$msg.="Table sso_table_correspondance vidée.<br />";
	}
	else {
		$msg.="Erreur lors du 'vidage' de sso_table_correspondance.<br />";
	}
	unset($mode);
}

//**************** EN-TETE *****************
$titre_page = "ENT ITOP : Rapprochement";
require_once("../lib/header.inc.php");
//**************** FIN EN-TETE *****************

if($msg!="") {
	echo "<div style='position:absolute; top:100px; left:20px; width:18px;' class='noprint'><a href='#lien_retour'><img src='../images/down.png' width='18' height='18' title='Passer les messages' /></a></div>\n";
}

$module_suhosin_actif="n";
$suhosin_post_max_totalname_length=ini_get('suhosin.post.max_totalname_length');
if($suhosin_post_max_totalname_length!='') {
	$module_suhosin_actif="y";
}

//debug_var();
$sql="CREATE TABLE IF NOT EXISTS tempo2_sso ( col1 varchar(100) NOT NULL default '', col2 TEXT NOT NULL default '') ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$create_table=mysql_query($sql);

?>

<script type="text/javascript" language="JavaScript">

	function recherche_utilisateur(statut) {
		// Ce n'est pas tout à fait le statut, mais 'eleve', 'responsable' ou 'personnel'
		recherche="recherche_"+statut;
		nom_rech=document.getElementById('nom_rech').value;
		prenom_rech=document.getElementById('prenom_rech').value;
		csrf_alea=document.getElementById('csrf_alea').value

		new Ajax.Updater($('div_resultat'),"<?php echo $_SERVER['PHP_SELF']?>",{method: 'post',
		parameters: {
			nom_rech: nom_rech,
			prenom_rech: prenom_rech,
			recherche: recherche,
			csrf_alea: csrf_alea
		}});
	}

</script>

<?php

echo "<a name='lien_retour'></a>
<p class='bold noprint'>
<a href=\"../accueil.php\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";

if(!isset($mode)) {
	echo "
</p>

<h2>Rapprochement des comptes ENT ITOP/GEPI</h2>

<p><a href='".$_SERVER['PHP_SELF']."?mode=saisie_manuelle'>Saisir manuellement une association</a></p>
<p>Ou importer un CSV&nbsp;:</p>
<ul>
	<li><a href='".$_SERVER['PHP_SELF']."?mode=import_eleves'>Importer un CSV élèves</a></li>
	<li><a href='".$_SERVER['PHP_SELF']."?mode=import_responsables'>Importer un CSV responsables</a></li>
	<li><a href='".$_SERVER['PHP_SELF']."?mode=import_personnels'>Importer un CSV personnels de l'établissement</a></li>
</ul>
<p> ou consulter&nbsp;:</p>
<ul>";

	//$sql="SELECT e.*, s.* FROM eleves e, sso_table_correspondance s WHERE s.login_gepi=e.login ORDER BY e.nom, e.prenom LIMIT 1;";
	$sql="SELECT 1=1 FROM eleves e, sso_table_correspondance s WHERE s.login_gepi=e.login;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association élève n'est encore enregistrée.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=consult_eleves'>Consulter les associations élèves</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	//$sql="SELECT rp.*, s.* FROM resp_pers rp, sso_table_correspondance s WHERE s.login_gepi=rp.login ORDER BY rp.nom, rp.prenom LIMIT 1;";
	$sql="SELECT 1=1 FROM resp_pers rp, sso_table_correspondance s WHERE s.login_gepi=rp.login;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association responsable n'est encore enregistrée.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=consult_responsables'>Consulter les associations responsables</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	//$sql="SELECT u.*, s.* FROM utilisateurs u, sso_table_correspondance s WHERE s.login_gepi=u.login AND u.statut!='eleve' AND u.statut!='responsable' LIMIT 1;";
	$sql="SELECT 1=1 FROM utilisateurs u, sso_table_correspondance s WHERE s.login_gepi=u.login AND u.statut!='eleve' AND u.statut!='responsable';";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association n'est encore enregistrée pour les personnels de l'établissement.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=consult_personnels'>Consulter les associations personnels</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	echo "
</ul>";

	//======================================================================

	//﻿Nom;Prénom;Login;Numéro de jointure;Mot de passe;Email;Classe;Etat;Date de désactivation
	//DUPRE;Thomas;thomas.dupre;MENESR$12345;mdp&*;Thomas.DUPRE@ent27.fr;6 A;Actif
	echo "<p> ou générer des Fiches Bienvenue&nbsp;:</p>
<ul>";

	$sql="SELECT 1=1 FROM eleves e, sso_table_correspondance s WHERE s.login_gepi=e.login;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association élève n'est encore enregistrée.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=publipostage_eleves'>Générer les Fiches Bienvenue élèves</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	//$sql="SELECT rp.*, s.* FROM resp_pers rp, sso_table_correspondance s WHERE s.login_gepi=rp.login ORDER BY rp.nom, rp.prenom LIMIT 1;";
	$sql="SELECT 1=1 FROM resp_pers rp, sso_table_correspondance s WHERE s.login_gepi=rp.login;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association responsable n'est encore enregistrée.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=publipostage_responsables'>Générer les Fiches Bienvenue responsables</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	//$sql="SELECT u.*, s.* FROM utilisateurs u, sso_table_correspondance s WHERE s.login_gepi=u.login AND u.statut!='eleve' AND u.statut!='responsable' LIMIT 1;";
	$sql="SELECT 1=1 FROM utilisateurs u, sso_table_correspondance s WHERE s.login_gepi=u.login AND u.statut!='eleve' AND u.statut!='responsable';";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "
	<li>Aucune association n'est encore enregistrée pour les personnels de l'établissement.</li>";
	}
	else {
		echo "
	<li><a href='".$_SERVER['PHP_SELF']."?mode=publipostage_personnels'>Générer les Fiches Bienvenue personnels</a> (<em>".mysql_num_rows($res)." association(s) enregistrée(s)</em>)</li>";
	}

	echo "
</ul>
<p>Cette rubrique permet de fournir les fichiers CSV de rénitialisation de mots de passe générés par l'ENT.</p>";



	$sql="SELECT 1=1 FROM sso_table_correspondance;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)>0) {
		echo "
<p>ou <a href='".$_SERVER['PHP_SELF']."?mode=vider".add_token_in_url()."' onclick=\"return confirmlink(this, 'ATTENTION !!! Êtes-vous vraiment sûr de vouloir vider la table sso_table_correspondance ?', 'Confirmation du vidage')\">vider la table des correspondances</a></p>";
		echo "<p>La table de correspondances contient actuellement ".mysql_num_rows($res)." enregistrements.</p>\n";
	}

	echo "
<br />
<p style='color:red'>A FAIRE : Détecter et permettre de supprimer des associations pour des élèves,... qui ne sont plus dans le CSV.</p>
<br />
\n";

	if($module_suhosin_actif=="y") {
		echo "<p style='text-indent:-4em; margin-left:4em;'><em>NOTE&nbsp;:</em> Le module PHP <span style='color:red'>Suhosin</span> est actif.<br />Il peut perturber l'enregistrement des associations lorsqu'un grand nombre de champs est envoyé.<br />Vous devrez peut-être soumettre plusieurs fois les mêmes fichiers CSV pour enregistrer par tranches les associations.</p>
<br />
";
	}

	echo "<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em> Les CSV réclamés dans les pages d'importation sont accessibles en suivant le cheminement suivant&nbsp;:<br />
	Se connecter avec un compte administrateur de l'ENT.<br />
	Menu Administration puis Gérer les utilisateurs puis Outils puis Traitement en masse puis Action (<em>Choisir Exportation SSO au format CSV</em>) puis dans Profil sélectionner le profil (<em>Elève, Parent,...</em>)<br />
	puis Traiter cette action puis Valider.</p>\n";

	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="saisie_manuelle") {
		echo " | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>

<h2>Rapprochement des comptes ENT ITOP/GEPI</h2>

<p>Saisir manuellement une association Login_gepi / Guid&nbsp;:</p>
<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	".add_token_field()."
	<input type='hidden' name='mode' value='saisie_manuelle' />
	<input type='hidden' name='enregistrement_saisie_manuelle' value='y' />
	<table border='0'>
		<tr><th style='text-align:left;'>Login Gepi&nbsp;:</th><td><input type=\"text\" name=\"login_gepi\" size=\"50\" value=\"".(isset($_GET['login_gepi']) ? $_GET['login_gepi'] : "")."\" /></td></tr>
		<tr><th style='text-align:left;'>Guid ENT&nbsp;:</th><td><input type=\"text\" name=\"login_sso\" size=\"50\" value=\"".(isset($_GET['login_sso']) ? $_GET['login_sso'] : "")."\" /></td></tr>
	</table>
	<input type='submit' value='Valider' />
</form>

";
	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="import_eleves") {
	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;
		echo " | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	if(!isset($csv_file)) {
		echo "
<h2>Rapprochement des comptes ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<input type='hidden' name='mode' value='import_eleves' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type=\"checkbox\" id=\"exclure_classes_anormales\" name=\"exclure_classes_anormales\" value=\"y\" checked /><label for='exclure_classes_anormales'>Exclure les classes nommées BASE_20XXXXXX (<em>plus précisément contenant la chaine 'BASE_20'</em>)</label><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li>Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	Guid;Nom;Prénom;Profil;Classes;Groupe;Naissance<br />
	f73d0f72-0958-4b8f-85f7-a58a96d95220;DISSOIR;Alain;National_1;0310000Z$1L1;16/06/1987<br />
	...</li>
	<li>Il peut arriver que le CSV fourni contienne des élèves de l'année précédente.<br />
	La classe est alors par exemple&nbsp;: BASE_2011-2012<br />
	Proposer d'effectuer un rapprochement pour des élèves qui ne sont plus là n'est pas souhaitable.<br />
	Vous pouvez aussi avoir un même élève qui apparaît avec deux lignes dans le fichier&nbsp;:<br />
	Une ligne avec le GUID de l'année passée et associé à une classe BASE_20XX-20XX<br />
	et une ligne avec le GUID de cette année (<em>c'est celui qu'il faut retenir</em>).</li>
	<li>Il est recommandé d'envoyer deux fois le même CSV.<br />
	La première fois pour prendre en compte les élèves correctement identifiés.<br />
	La deuxième pour voir quels élèves du CSV n'ont pas été associés à un login Gepi.</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$sql="TRUNCATE tempo2_sso;";
		$menage=mysql_query($sql);

		echo creer_div_infobulle("div_search","Formulaire de recherche dans la table 'eleves'","","<p>Saisir une portion du nom à rechercher...</p>
<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
".add_token_field(true)."
<input type='hidden' name='login_recherche' id='login_recherche' value='' />
<table border='0' summary='Recherche'>
	<tr>
		<th>Nom: </th>
		<td><input type='text' name='nom_rech' id='nom_rech' value='' onBlur='recherche_utilisateur(\"eleve\")' /></td>
		<td rowspan='2'><input type='button' name='chercher' value='Chercher' onClick='recherche_utilisateur(\"eleve\")' /></td>
	</tr>
	<tr>
		<th>Prénom: </th>
		<td><input type='text' name='prenom_rech' id='prenom_rech' value='' onBlur='recherche_utilisateur(\"eleve\")' /></td>
	</tr>
</table>
</form>

<div id='div_resultat' style='margin: 1px;'></div>

","",32,0,"y","y","n","n");

		echo "
<h2>Rapprochement des comptes élèves ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' name='form_import' enctype='multipart/form-data'>
".add_token_field()."
<input type='hidden' name='mode' value='import_eleves' />
<input type='hidden' name='enregistrement_eleves' value='y' />
<input type='hidden' name='temoin_suhosin_1' value='eleve' />

<table class='boireaus'>
	<tr>
		<th rowspan='2'>
			Enregistrer
			<span id='tout_cocher_decocher' style='display:none;'>
				<br />
				<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
				/
				<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
			</span>
		</th>
		<th colspan='6'>Informations ENT</th>
		<th rowspan='2'>Login Gepi</th>
	</tr>
	<tr>
		<th>Guid</th>
		<th>Nom</th>
		<th>Prénom</th>
		<th>Profil</th>
		<th>Classe</th>
		<th>Naissance</th>
	</tr>
";
		$alt=1;
		$cpt=0;
		$cpt_deja_enregistres=0;
		$tab_nom_prenom_deja_aff=array();
		$tab_doublon_possible=array();
		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Guid;/i", trim($ligne))) {

					// Si un élève a déjà une association enregistrée, ne pas proposer de refaire l'association.
					// Juste stocker ce qui est déjà associé et l'afficher dans un 2è tableau.
					// Pouvoir supprimer une association du 2è tableau (ou voir dans mode=consult_eleves)

					$sql="SELECT login_gepi FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)>0) {
						$cpt_deja_enregistres++;
					}
					elseif((!isset($_POST['exclure_classes_anormales']))||(!preg_match("/BASE_20/", $tab[4]))) {
						$naissance=(isset($tab[5])) ? $tab[5] : "";
						if(!preg_match("#[0-9]{2}/[0-9]{2}/[0-9]{4}#", $naissance)) {$naissance="";}

						if(in_array_i($tab[1]." ".$tab[2], $tab_nom_prenom_deja_aff)) {
							$chaine_tmp=$cpt."_".remplace_accents($tab[1]." ".$tab[2],"all");
							$tab_doublon_possible[]=$chaine_tmp;
							$ancre_doublon_ou_pas="<a name='doublon_possible_$chaine_tmp'></a>";
							$style_css=" style='background-color:red' title=\"Il existe au moins un homonyme dans le CSV.
Si les homonymes correspondent à un même élève, vous allez devoir identifier le bon GUID
(choisir un des homonymes au hasard, et demander à l'élève de tester.
si cela ne fonctionne pas, corriger l'association élève en mettant le GUID de l'homonyme).\"";
						}
						else {
							$ancre_doublon_ou_pas="";
							$style_css="";
						}
						$tab_nom_prenom_deja_aff[]=$tab[1]." ".$tab[2];

						$alt=$alt*(-1);
						echo "
	<tr class='lig$alt white_hover'$style_css>
		<td><input type='checkbox' name='ligne[]' id='ligne_$cpt' value='$cpt' onchange=\"change_graisse($cpt)\" />$ancre_doublon_ou_pas</td>
		<td><label for='ligne_$cpt'>".$tab[0]."</label></td>
		<td><label for='ligne_$cpt'><span id='nom_$cpt'>".$tab[1]."</span></label></td>
		<td><label for='ligne_$cpt'><span id='prenom_$cpt'>".$tab[2]."</span></label></td>
		<td><label for='ligne_$cpt'>".$tab[3]."</label></td>
		<td><label for='ligne_$cpt'>".preg_replace("/".getSettingValue('gepiSchoolRne')."\\$/", "", $tab[4])."</label></td>
		<td><label for='ligne_$cpt'>".$naissance."</label></td>";

						// Recherche dans la table eleves de personnes pouvant correspondre à la ligne courante du CSV.
						if($naissance!="") {
							$sql="SELECT * FROM eleves WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' AND naissance='".formate_date2($naissance, "jj/mm/aaaa", "aaaammjj")."'";
						}
						else {
							$sql="SELECT * FROM eleves WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' ORDER BY naissance;";
						}
						$res=mysql_query($sql);
						if(mysql_num_rows($res)==1) {
							// Un seul élève correspond
							$lig=mysql_fetch_object($res);

							echo "
		<td title=\"$lig->nom $lig->prenom ".formate_date($lig->naissance)."\">
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>
			$lig->login";

							// On vérifie quand même la classe?
							$tab_classe=get_class_from_ele_login($lig->login);
							if(isset($tab_classe['liste_nbsp'])) {
								echo " (<em>".$tab_classe['liste_nbsp']."</em>)";
							}
							else {
								echo " (<em style='color:red'>Aucune classe???</em>)";
							}

							echo "
			<span id='saisie_$cpt'></span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
						}
						else {
							// Plusieurs élèves correspondent
							// Il va falloir choisir
							$chaine_options="";
							while($lig=mysql_fetch_object($res)) {
								$chaine_options.="				<option value=\"$lig->login\">$lig->nom $lig->prenom (".formate_date($lig->naissance).")";
								$tab_classe=get_class_from_ele_login($lig->login);
								if(isset($tab_classe['liste'])) {
									$chaine_options.=" en ".$tab_classe['liste'];
								}
								$chaine_options.="</option>\n";
							}

							if($chaine_options!="") {
								echo "
		<td>
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'>
			<select name='login_$cpt' id='login_$cpt' onchange=\"if(document.getElementById('login_$cpt').options[if(document.getElementById('login_$cpt').selectedIndex].value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\">
				<option value=''>---</option>
				$chaine_options
			</select>
			</span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
							}
							else {
								echo "
		<td>";
								if($module_suhosin_actif!="y") {
									echo "
			<input type='text' name='login_$cpt' id='login_$cpt' value=\"\" onchange=\"if(document.getElementById('login_$cpt').value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\" />";
								}
								else {
									echo "
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'></span>";
								}

								// On renseigne le formulaire de recherche avec le nom et le prénom:
								// ...
								// On fait le nettoyage pour ne pas laisser les traces d'une précédente requête:
								echo "
			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
							}
						}

						$sql="INSERT INTO tempo2_sso SET col1='$cpt', col2='".mysql_real_escape_string($ligne)."';";
						$insert=mysql_query($sql);
						if(!$insert) {
							echo "<span style='color:red'>ERREUR</span>";
						}
						$cpt++;
					}
				}
			}
		}

		echo "
</table>
<input type='hidden' name='temoin_suhosin_2' value='eleve' />
<input type='submit' id='bouton_submit_import' value='Enregistrer' />
<input type='button' id='bouton_button_import' value='Enregistrer' style='display:none;' onclick='verif_puis_submit()' />
</form>
";

		if(count($tab_doublon_possible)>0) {
			echo "<p style='text-indent:-8em; margin-left:8em;'><strong style='color:red'>ATTENTION&nbsp:</strong> Un ou des doublons possibles ont été repérés.<br />
Veuillez contrôler manuellement s'il s'agit ou non de doublons&nbsp;:<br />";
			for($loop=0;$loop<count($tab_doublon_possible);$loop++) {
				echo "<a href='#doublon_possible_".$tab_doublon_possible[$loop]."'>".$tab_doublon_possible[$loop]."</a><br />\n";
			}
			echo "
</p>\n";
		}

		echo "<p>$cpt ligne(s) affichée(s).</p>\n";

		if($cpt_deja_enregistres>0) {
			echo "<p><a href='".$_SERVER['PHP_SELF']."?mode=consult_eleves'>$cpt_deja_enregistres association(s) élève(s) déjà enregistrée(s)</a>.<br />\n";
		}
		else {
			echo "<p>Aucune association élève n'est encore enregistrée.<br />\n";
		}

		echo "
<p style='color:red'>A FAIRE : Pouvoir trier par classe</p>

<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';
	document.getElementById('bouton_button_import').style.display='';
	document.getElementById('bouton_submit_import').style.display='none';

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=true;
				change_graisse(i);
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=false;
				change_graisse(i);
			}
		}
	}

	function ajout_champ_saisie_login(num) {
		if(document.getElementById('saisie_'+num)) {
			document.getElementById('saisie_'+num).innerHTML='<input type=\"text\" name=\"login_'+num+'\" id=\"login_'+num+'\" value=\"\" onchange=\"if(document.getElementById(\'login_'+num+'\').value!=\'\') {document.getElementById(\'ligne_'+num+'\').checked=true;} else {document.getElementById(\'ligne_'+num+'\').checked=false;}\" />';
		}
	}

	function verif_puis_submit() {
		temoin='n';
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				if(document.getElementById('ligne_'+i).checked==true) {
					temoin='y';
					break;
				}
			}
		}
		if(temoin=='y') {
			document.forms['form_import'].submit();
		}
		else {
			alert('Vous n avez rien coché!?');
		}
	}

	function change_graisse(num) {
		if((document.getElementById('ligne_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('ligne_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}
</script>
";
		// En fin d'enregistrement, renvoyer vers consult_eleves pour afficher les associations
		// Compter/afficher ce total des associations avant... et après dans consult_eleves
	}
	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="consult_eleves") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
 | <a href='".$_SERVER['PHP_SELF']."?mode=import_eleves'>Importer un CSV élève</a>
</p>

<h2>Rapprochement actuels des comptes élèves ENT ITOP/GEPI</h2>
";

	$sql="SELECT e.*, s.* FROM eleves e, sso_table_correspondance s WHERE s.login_gepi=e.login ORDER BY e.nom, e.prenom";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>Aucun rapprochement élève n'est enregistré.</p>\n";

		require("../lib/footer.inc.php");
		die();
	}

	echo "
<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<p>Les rapprochements enregistrés (".mysql_num_rows($res).") sont les suivants&nbsp;:</p>
	".add_token_field()."
	<input type='hidden' name='mode' value='consult_eleves' />
	<input type='hidden' name='temoin_suhosin_1' value='eleve' />

	<table class='boireaus'>
		<tr>
			<th>
				<input type='submit' value='Supprimer' />
				<span id='tout_cocher_decocher' style='display:none;'>
					<br />
					<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
					/
					<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
				</span>
			</th>
			<th>Guid</th>
			<th>Login</th>
			<th title=\"Pour une connexion via un ENT, le champ auth_mode doit en principe avoir pour valeur 'sso'\">Auth_mode</th>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Classe</th>
			<th>Naissance</th>
			<th>Corriger</th>
		</tr>
";

	$cpt=0;
	$alt=1;
	while($lig=mysql_fetch_object($res)) {
		$tab_classe=get_class_from_ele_login($lig->login);
		$classe=isset($tab_classe['liste_nbsp']) ? $tab_classe['liste_nbsp'] : "";

		$alt=$alt*(-1);
		echo "
		<tr class='lig$alt white_hover'>
			<td><input type='checkbox' name='suppr[]' id='suppr_$cpt' value=\"$lig->login\" onchange=\"change_graisse($cpt)\" /></td>
			<td><label for='suppr_$cpt'>$lig->login_sso</label></td>
			<td><label for='suppr_$cpt'>$lig->login</label></td>
			<td>";
		$sql="SELECT auth_mode, etat FROM utilisateurs WHERE login='".$lig->login."' AND statut='eleve';";
		$test_u=mysql_query($sql);
		if(mysql_num_rows($test_u)==0) {
			echo "<a href='../utilisateurs/create_eleve.php?filtrage=Afficher&amp;critere_recherche=".preg_replace("/[^A-Za-z]/", "%", $lig->nom)."&amp;afficher_tous_les_eleves=n' title='Pas de compte utilisateur dans Gepi pour cet élève.
Créer le compte?' target='_blank'>-</a>";
		}
		else {
			echo "<label for='suppr_$cpt'>";
			$lig_u=mysql_fetch_object($test_u);
			if($lig_u->etat=='actif') {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy.png' width='16' height='16' title='Compte actif' /></div>\n";
			}
			else {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy_no.png' width='16' height='16' title='Compte inactif' /></div>\n";
			}
			echo $lig_u->auth_mode;
			echo "</label>";
		}
		echo "</td>
			<td><label for='suppr_$cpt'><span id='nom_$cpt'>$lig->nom</span></label></td>
			<td><label for='suppr_$cpt'><span id='prenom_$cpt'>$lig->prenom</span></label></td>
			<td><label for='suppr_$cpt'>$classe</label></td>
			<td><label for='suppr_$cpt'>".formate_date($lig->naissance)."</label></td>
			<td><a href='".$_SERVER['PHP_SELF']."?login_gepi=$lig->login_gepi&amp;login_sso=$lig->login_sso&amp;mode=saisie_manuelle'><img src='../images/edit16.png' width='16' height='16' title=\"Corriger l'association\" /></label></td>
		</tr>
";
		$cpt++;
	}

	echo "
	</table>
	<input type='hidden' name='temoin_suhosin_2' value='eleve' />
</form>

<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=true;
				change_graisse(i);
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=false;
				change_graisse(i);
			}
		}
	}

	function change_graisse(num) {
		if((document.getElementById('suppr_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('suppr_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}
</script>
";

	require("../lib/footer.inc.php");
	die();
}
//==================================================================================

if($mode=="import_responsables") {
	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	if(!isset($csv_file)) {
		echo "
<h2>Rapprochement des comptes responsables ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<input type='hidden' name='mode' value='import_responsables' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li>Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	﻿Guid;Nom;Prénom;Profil;Classes;Groupe;Guid_Enfant1;Guid_Enfant2;Guid_Enfant3<br />
	f7ebe441-14e0-4c48-b9ec-53e603829fb3;DISSOIR;Amar;National_2;;;f73d0f72-0958-4b8f-85f7-a58a96d95220<br />
	...</li>
	<li>Il est recommandé d'envoyer une première fois le CSV, d'enregistrer les associations correctement détectées (<em>en contrôlant tout de même les éventuels doublons repérés</em>).<br />
	Puis, envoyer à nouveau le même fichier pour traiter les indéterminés restants.<br />
	Le deuxième envoi permet aussi de repérer ce qui n'a pas été enregistré au premier envoi.</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$sql="TRUNCATE tempo2_sso;";
		$menage=mysql_query($sql);

		echo creer_div_infobulle("div_search","Formulaire de recherche dans la table 'resp_pers'","","<p>Saisir une portion du nom à rechercher...</p>
<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
".add_token_field(true)."
<input type='hidden' name='login_recherche' id='login_recherche' value='' />
<table border='0' summary='Recherche'>
	<tr>
		<th>Nom: </th>
		<td><input type='text' name='nom_rech' id='nom_rech' value='' onBlur='recherche_utilisateur(\"responsable\")' /></td>
		<td rowspan='2'><input type='button' name='chercher' value='Chercher' onClick='recherche_utilisateur(\"responsable\")' /></td>
	</tr>
	<tr>
		<th>Prénom: </th>
		<td><input type='text' name='prenom_rech' id='prenom_rech' value='' onBlur='recherche_utilisateur(\"responsable\")' /></td>
	</tr>
</table>
</form>

<div id='div_resultat' style='margin: 1px;'></div>

","",32,0,"y","y","n","n");

		echo "
<h2>Rapprochement des comptes responsables ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' name='form_import' enctype='multipart/form-data'>
".add_token_field()."
<input type='hidden' name='mode' value='import_responsables' />
<input type='hidden' name='enregistrement_responsables' value='y' />
<input type='hidden' name='temoin_suhosin_1' value='responsable' />

<table class='boireaus'>
	<tr>
		<th rowspan='2'>
			Enregistrer
			<span id='tout_cocher_decocher' style='display:none;'>
				<br />
				<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
				/
				<a href=\"javascript:cocher_tous_les_resp_avec_login_et_eleve_associe()\" title='Cocher les responsables avec login et élève associé'><img src='../images/icons/wizard.png' width='20' height='20' /></a>
				/
				<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
			</span>
		</th>
		<th colspan='6'>Informations ENT</th>
		<th rowspan='2'>Login Gepi</th>
	</tr>
	<tr>
		<th>Guid</th>
		<th>Nom</th>
		<th>Prénom</th>
		<th>Profil</th>
		<th>Groupe</th>
		<th>Enfants</th>
	</tr>
";
		$alt=1;
		$cpt=0;
		$cpt_deja_enregistres=0;
		$tab_nom_prenom_deja_aff=array();
		$tab_doublon_possible=array();
		$chaine_resp_avec_login_et_eleve_associe="";
		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Guid;/i", trim($ligne))) {

					// Si un élève a déjà une association enregistrée, ne pas proposer de refaire l'association.
					// Juste stocker ce qui est déjà associé et l'afficher dans un 2è tableau.
					// Pouvoir supprimer une association du 2è tableau (ou voir dans mode=consult_eleves)

					$sql="SELECT login_gepi FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)>0) {
						$cpt_deja_enregistres++;
					}
					else {

						if(in_array_i($tab[1]." ".$tab[2], $tab_nom_prenom_deja_aff)) {
							$chaine_tmp=$cpt."_".remplace_accents($tab[1]." ".$tab[2],"all");
							$tab_doublon_possible[]=$chaine_tmp;
							$ancre_doublon_ou_pas="<a name='doublon_possible_$chaine_tmp'></a>";
							$style_css=" style='background-color:red' title=\"Il existe au moins un homonyme dans le CSV.
Si les homonymes correspondent à un même élève, vous allez devoir identifier le bon GUID
(choisir un des homonymes au hasard, et demander à l'élève de tester.
si cela ne fonctionne pas, corriger l'association élève en mettant le GUID de l'homonyme).\"";
						}
						else {
							$ancre_doublon_ou_pas="";
							$style_css="";
						}
						$tab_nom_prenom_deja_aff[]=$tab[1]." ".$tab[2];

						$alt=$alt*(-1);
						echo "
	<tr class='lig$alt white_hover'$style_css>
		<td><input type='checkbox' name='ligne[]' id='ligne_$cpt' value='$cpt' onchange=\"change_graisse($cpt)\" />$ancre_doublon_ou_pas</td>
		<td><label for='ligne_$cpt'>".$tab[0]."</label></td>
		<td><label for='ligne_$cpt'><span id='nom_$cpt'>".$tab[1]."</span></label></td>
		<td><label for='ligne_$cpt'><span id='prenom_$cpt'>".$tab[2]."</span></label></td>
		<td><label for='ligne_$cpt'>".$tab[3]."</label></td>
		<td><label for='ligne_$cpt'>".(isset($tab[4])?$tab[4]:"")."</label></td>
		<td><label for='ligne_$cpt'>";

						$chaine="";
						if((isset($tab[5]))&&($tab[5]!="")) {
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[5])."'";
						}
						if((isset($tab[6]))&&($tab[6]!="")) {
							if($chaine!="") {$chaine.=" OR ";}
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[6])."'";
						}
						if((isset($tab[7]))&&($tab[7]!="")) {
							if($chaine!="") {$chaine.=" OR ";}
							$chaine.="s.login_sso='".mysql_real_escape_string($tab[7])."'";
						}

						$temoin_eleve_associe="n";
						$tab_resp=array();
						$tab_resp_login=array();
						$cpt_resp=0;
						// Si la chaine est vide, proposer un champ TEXT
						if($chaine!="") {
							$sql="SELECT e.* FROM eleves e, sso_table_correspondance s WHERE ($chaine) AND e.login=s.login_gepi ORDER BY e.nom, e.prenom;";
							//echo "$sql<br />";
							$res_ele=mysql_query($sql);
							if(mysql_num_rows($res_ele)>0) {
								$temoin_eleve_associe="y";
								$cpt_ele=0;
								while($lig_ele=mysql_fetch_object($res_ele)) {
									if($cpt_ele>0) {echo "<br />";}
									echo $lig_ele->nom." ".$lig_ele->prenom;
									$tab_classe=get_class_from_ele_login($lig_ele->login);
									if(isset($tab_classe['liste_nbsp'])) {echo " (<em>".$tab_classe['liste_nbsp']."</em>)";}

									$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.pers_id=rp.pers_id AND r.ele_id='$lig_ele->ele_id' AND rp.nom='".mysql_real_escape_string($tab[1])."' AND rp.prenom='".mysql_real_escape_string($tab[2])."' AND rp.login!='';";
									//echo "$sql<br />";
									$res_resp=mysql_query($sql);
									if(mysql_num_rows($res_resp)>0) {
										while($lig_resp=mysql_fetch_object($res_resp)) {
											if(!in_array($lig_resp->login, $tab_resp_login)) {
												$tab_resp_login[]=$lig_resp->login;
												$tab_resp[$cpt_resp]['login']=$lig_resp->login;
												$tab_resp[$cpt_resp]['civilite']=$lig_resp->civilite;
												$tab_resp[$cpt_resp]['nom']=$lig_resp->nom;
												$tab_resp[$cpt_resp]['prenom']=$lig_resp->prenom;
												$tab_resp[$cpt_resp]['resp_legal']=$lig_resp->resp_legal;
												$tab_resp[$cpt_resp]['info']=$lig_resp->civilite." ".casse_mot($lig_resp->nom,'maj')." ".casse_mot($lig_resp->prenom,'majf2');
												$tab_resp[$cpt_resp]['info'].=" (N° ".$lig_resp->pers_id.")";

												$tab_resp[$cpt_resp]['info'].=" (Légal ".$lig_resp->resp_legal.")";
												$cpt_resp++;
											}
										}
									}
									else {
										$sql="SELECT rp.*, r.resp_legal FROM resp_pers rp, responsables2 r WHERE r.pers_id=rp.pers_id AND r.ele_id='$lig_ele->ele_id' AND rp.login!='';";
										$res_resp=mysql_query($sql);
										if(mysql_num_rows($res_resp)>0) {
											while($lig_resp=mysql_fetch_object($res_resp)) {
												if(!in_array($lig_resp->login, $tab_resp_login)) {
													$tab_resp_login[]=$lig_resp->login;
													$tab_resp[$cpt_resp]['login']=$lig_resp->login;
													$tab_resp[$cpt_resp]['civilite']=$lig_resp->civilite;
													$tab_resp[$cpt_resp]['nom']=$lig_resp->nom;
													$tab_resp[$cpt_resp]['prenom']=$lig_resp->prenom;
													$tab_resp[$cpt_resp]['resp_legal']=$lig_resp->resp_legal;
													$tab_resp[$cpt_resp]['info']=$lig_resp->civilite." ".casse_mot($lig_resp->nom,'maj')." ".casse_mot($lig_resp->prenom,'majf2');
													$tab_resp[$cpt_resp]['info'].=" (N° ".$lig_resp->pers_id.")";
													$tab_resp[$cpt_resp]['info'].=" (Légal ".$lig_resp->resp_legal.")";
													$cpt_resp++;
												}
											}
										}
									}
									$cpt_ele++;
								}
							}
						}
						echo "</label></td>\n";

						if(count($tab_resp)==0) {
							echo "
		<td>";
								if($module_suhosin_actif!="y") {
									echo "
			<input type='text' name='login_$cpt' id='login_$cpt' value=\"\" onchange=\"if(document.getElementById('login_$cpt').value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\" />";
								}
								else {
									echo "
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'></span>";
								}

								// On renseigne le formulaire de recherche avec le nom et le prénom:
								// ...
								// On fait le nettoyage pour ne pas laisser les traces d'une précédente requête:
								echo "
			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
						}
						elseif(count($tab_resp)==1) {
							echo "
		<td title=\"".$tab_resp[0]['info']."\">
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>
			".$tab_resp[0]['login']."
			<span id='saisie_$cpt'></span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
							if($temoin_eleve_associe=="y") {
								if($chaine_resp_avec_login_et_eleve_associe!="") {
									$chaine_resp_avec_login_et_eleve_associe.=", ";
								}
								$chaine_resp_avec_login_et_eleve_associe.=$cpt;
							}
						}
						else {
							$chaine_options="";
							for($loop=0;$loop<count($tab_resp);$loop++) {
								$chaine_options.="				<option value=\"".$tab_resp[$loop]['login']."\">".$tab_resp[$loop]['info']."</option>\n";
							}
							echo "
		<td>
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'>
			<select name='login_$cpt' id='login_$cpt' onchange=\"if(document.getElementById('login_$cpt').options[if(document.getElementById('login_$cpt').selectedIndex].value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\">
				<option value=''>---</option>
				$chaine_options
			</select>
			</span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
						}

						$sql="INSERT INTO tempo2_sso SET col1='$cpt', col2='".mysql_real_escape_string($ligne)."';";
						$insert=mysql_query($sql);
						if(!$insert) {
							echo "<span style='color:red'>ERREUR</span>";
						}
						$cpt++;
					}
				}
			}
		}

		echo "
</table>
<input type='hidden' name='temoin_suhosin_2' value='responsable' />
<input type='submit' id='bouton_submit_import' value='Enregistrer' />
<input type='button' id='bouton_button_import' value='Enregistrer' style='display:none;' onclick='verif_puis_submit()' />

</form>
";

		if(count($tab_doublon_possible)>0) {
			echo "<p style='text-indent:-8em; margin-left:8em;'><strong style='color:red'>ATTENTION&nbsp:</strong> Un ou des doublons possibles ont été repérés.<br />
Veuillez contrôler manuellement s'il s'agit ou non de doublons&nbsp;:<br />";
			for($loop=0;$loop<count($tab_doublon_possible);$loop++) {
				echo "<a href='#doublon_possible_".$tab_doublon_possible[$loop]."'>".$tab_doublon_possible[$loop]."</a><br />\n";
			}
			echo "
</p>\n";
		}

		echo "<p>$cpt ligne(s) affichée(s).</p>\n";

		if($cpt_deja_enregistres>0) {
			echo "<p><a href='".$_SERVER['PHP_SELF']."?mode=consult_responsables'>$cpt_deja_enregistres association(s) responsable(s) déjà enregistrée(s)</a>.<br />\n";
		}
		else {
			echo "<p>Aucune association responsable n'est encore enregistrée.<br />\n";
		}

		echo "
<p style='color:red'>A FAIRE:<br />
Pouvoir trier par classe<br />
Ajouter une variable en fin de formulaire pour détecter les pb de transmission de trop de variables avec suhosin.</p>

<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';
	document.getElementById('bouton_button_import').style.display='';
	document.getElementById('bouton_submit_import').style.display='none';

	function change_graisse(num) {
		if((document.getElementById('ligne_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('ligne_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=true;
				change_graisse(i);
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=false;
				change_graisse(i);
			}
		}
	}

	var tab_resp_avec_login_et_eleve_associe=new Array($chaine_resp_avec_login_et_eleve_associe);
	function cocher_tous_les_resp_avec_login_et_eleve_associe() {
		for(i=0;i<tab_resp_avec_login_et_eleve_associe.length;i++) {
			if(document.getElementById('ligne_'+tab_resp_avec_login_et_eleve_associe[i])) {
				document.getElementById('ligne_'+tab_resp_avec_login_et_eleve_associe[i]).checked=true;
				change_graisse(tab_resp_avec_login_et_eleve_associe[i]);
			}
		}
	}

	function ajout_champ_saisie_login(num) {
		if(document.getElementById('saisie_'+num)) {
			document.getElementById('saisie_'+num).innerHTML='<input type=\"text\" name=\"login_'+num+'\" id=\"login_'+num+'\" value=\"\" onchange=\"if(document.getElementById(\'login_'+num+'\').value!=\'\') {document.getElementById(\'ligne_'+num+'\').checked=true;} else {document.getElementById(\'ligne_'+num+'\').checked=false;}\" />';
		}
	}

	function verif_puis_submit() {
		temoin='n';
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				if(document.getElementById('ligne_'+i).checked==true) {
					temoin='y';
					break;
				}
			}
		}
		if(temoin=='y') {
			document.forms['form_import'].submit();
		}
		else {
			alert('Vous n avez rien coché!?');
		}
	}

</script>
";
		// En fin d'enregistrement, renvoyer vers consult_eleves pour afficher les associations
		// Compter/afficher ce total des associations avant... et après dans consult_eleves
	}
	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="consult_responsables") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
 | <a href='".$_SERVER['PHP_SELF']."?mode=import_responsables'>Importer un CSV responsable</a>
</p>

<h2>Rapprochement actuels des comptes responsables ENT ITOP/GEPI</h2>
";

	$sql="SELECT rp.*, s.* FROM resp_pers rp, sso_table_correspondance s WHERE s.login_gepi=rp.login ORDER BY rp.nom, rp.prenom";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>Aucun rapprochement élève n'est enregistré.</p>\n";

		require("../lib/footer.inc.php");
		die();
	}

	echo "
<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<p>Les rapprochements enregistrés (".mysql_num_rows($res).") sont les suivants&nbsp;:</p>
	".add_token_field()."
	<input type='hidden' name='mode' value='consult_responsables' />
	<input type='hidden' name='temoin_suhosin_1' value='responsable' />

	<table class='boireaus'>
		<tr>
			<th>
				<input type='submit' value='Supprimer' />
				<span id='tout_cocher_decocher' style='display:none;'>
					<br />
					<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
					/
					<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
				</span>
			</th>
			<th>Guid</th>
			<th>Login</th>
			<th title=\"Pour une connexion via un ENT, le champ auth_mode doit en principe avoir pour valeur 'sso'\">Auth_mode</th>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Responsable de</th>
			<th>Corriger</th>
		</tr>
";

	$cpt=0;
	$alt=1;
	while($lig=mysql_fetch_object($res)) {
		$tab_ele=get_enfants_from_resp_login($lig->login, 'avec_classe');
		$chaine_ele="";
		for($loop=1;$loop<count($tab_ele);$loop+=2) {
			if($loop>1) {
				$chaine_ele.=", ";
			}
			$chaine_ele.=$tab_ele[$loop];
		}

		$alt=$alt*(-1);
		echo "
		<tr class='lig$alt white_hover'>
			<td><input type='checkbox' name='suppr[]' id='suppr_$cpt' value=\"$lig->login\" onchange=\"change_graisse($cpt)\" /></td>
			<td><label for='suppr_$cpt'>$lig->login_sso</label></td>
			<td><label for='suppr_$cpt'>$lig->login</label></td>
			<td><label for='suppr_$cpt'>";
		$sql="SELECT auth_mode, etat FROM utilisateurs WHERE login='".$lig->login."' AND statut='responsable';";
		$test_u=mysql_query($sql);
		if(mysql_num_rows($test_u)==0) {
			echo "<span title='Pas de compte utilisateur pour ce responsable.'>-</span>";
		}
		else {
			$lig_u=mysql_fetch_object($test_u);
			if($lig_u->etat=='actif') {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy.png' width='16' height='16' title='Compte actif' /></div>\n";
			}
			else {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy_no.png' width='16' height='16' title='Compte inactif' /></div>\n";
			}
			echo $lig_u->auth_mode;
		}
		echo "</label></td>
			<td><label for='suppr_$cpt'><span id='nom_$cpt'>$lig->nom</span></label></td>
			<td><label for='suppr_$cpt'><span id='prenom_$cpt'>$lig->prenom</span></label></td>
			<td><label for='suppr_$cpt'>$chaine_ele</label></td>
			<td><a href='".$_SERVER['PHP_SELF']."?login_gepi=$lig->login_gepi&amp;login_sso=$lig->login_sso&amp;mode=saisie_manuelle'><img src='../images/edit16.png' width='16' height='16' title=\"Corriger l'association\" /></label></td>
		</tr>
";
		$cpt++;
	}

	echo "
	</table>
	<input type='hidden' name='temoin_suhosin_2' value='responsable' />
</form>


<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';
	document.getElementById('bouton_button_import').style.display='';
	document.getElementById('bouton_submit_import').style.display='none';

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=true;
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=false;
			}
		}
	}

	function change_graisse(num) {
		if((document.getElementById('suppr_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('suppr_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}
</script>
";

	require("../lib/footer.inc.php");
	die();
}
//==================================================================================

if($mode=="import_personnels") {
	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

	echo " | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	if(!isset($csv_file)) {
		echo "
<h2>Rapprochement des comptes ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<input type='hidden' name='mode' value='import_personnels' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li> Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	Guid;Nom;Prénom;Profil;Classes;Groupe<br />
	f73d0f72-0958-4b8f-85f7-a58a96d95220;BACQUET;Michel;National_3;;<br />
	...</li>
	<li>Il est recommandé d'envoyer une première fois le CSV, d'enregistrer les associations correctement détectées (<em>en contrôlant tout de même les éventuels doublons repérés</em>).<br />
	Puis, envoyer à nouveau le même fichier pour traiter les indéterminés restants.<br />
	Le deuxième envoi permet aussi de repérer ce qui n'a pas été enregistré au premier envoi.</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$sql="TRUNCATE tempo2_sso;";
		$menage=mysql_query($sql);

		echo creer_div_infobulle("div_search","Formulaire de recherche dans la table 'utilisateurs'","","<p>Saisir une portion du nom à rechercher...</p>
<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
".add_token_field(true)."
<input type='hidden' name='login_recherche' id='login_recherche' value='' />
<table border='0' summary='Recherche'>
	<tr>
		<th>Nom: </th>
		<td><input type='text' name='nom_rech' id='nom_rech' value='' onBlur='recherche_utilisateur(\"personnel\")' /></td>
		<td rowspan='2'><input type='button' name='chercher' value='Chercher' onClick='recherche_utilisateur(\"personnel\")' /></td>
	</tr>
	<tr>
		<th>Prénom: </th>
		<td><input type='text' name='prenom_rech' id='prenom_rech' value='' onBlur='recherche_utilisateur(\"personnel\")' /></td>
	</tr>
</table>
</form>

<div id='div_resultat' style='margin: 1px;'></div>

","",32,0,"y","y","n","n");

		echo "
<h2>Rapprochement des comptes de personnels ENT ITOP/GEPI</h2>

<form action='".$_SERVER['PHP_SELF']."' method='post' name='form_import' enctype='multipart/form-data'>
".add_token_field()."
<input type='hidden' name='mode' value='import_personnels' />
<input type='hidden' name='enregistrement_personnels' value='y' />
<input type='hidden' name='temoin_suhosin_1' value='personnel' />

<table class='boireaus'>
	<tr>
		<th rowspan='2'>
			Enregistrer
			<span id='tout_cocher_decocher' style='display:none;'>
				<br />
				<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
				/
				<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
			</span>
		</th>
		<th colspan='4'>Informations ENT</th>
		<th rowspan='2'>Login Gepi</th>
	</tr>
	<tr>
		<th>Guid</th>
		<th>Nom</th>
		<th>Prénom</th>
		<th>Profil</th>
	</tr>
";
		$alt=1;
		$cpt=0;
		$cpt_deja_enregistres=0;
		$tab_nom_prenom_deja_aff=array();
		$tab_doublon_possible=array();
		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Guid;/i", trim($ligne))) {

					$sql="SELECT login_gepi FROM sso_table_correspondance WHERE login_sso='".mysql_real_escape_string($tab[0])."';";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)>0) {
						$cpt_deja_enregistres++;
					}
					else {

						if(in_array_i($tab[1]." ".$tab[2], $tab_nom_prenom_deja_aff)) {
							$chaine_tmp=$cpt."_".remplace_accents($tab[1]." ".$tab[2],"all");
							$tab_doublon_possible[]=$chaine_tmp;
							$ancre_doublon_ou_pas="<a name='doublon_possible_$chaine_tmp'></a>";
							$style_css=" style='background-color:red' title=\"Il existe au moins un homonyme dans le CSV.
Si les homonymes correspondent à un même élève, vous allez devoir identifier le bon GUID
(choisir un des homonymes au hasard, et demander à l'élève de tester.
si cela ne fonctionne pas, corriger l'association élève en mettant le GUID de l'homonyme).\"";
						}
						else {
							$ancre_doublon_ou_pas="";
							$style_css="";
						}
						$tab_nom_prenom_deja_aff[]=$tab[1]." ".$tab[2];

						$alt=$alt*(-1);
						echo "
	<tr class='lig$alt white_hover'$style_css>
		<td><input type='checkbox' name='ligne[]' id='ligne_$cpt' value='$cpt' onchange=\"change_graisse($cpt)\" />$ancre_doublon_ou_pas</td>
		<td><label for='ligne_$cpt'>".$tab[0]."</label></td>
		<td><label for='ligne_$cpt'><span id='nom_$cpt'>".$tab[1]."</span></label></td>
		<td><label for='ligne_$cpt'><span id='prenom_$cpt'>".$tab[2]."</span></label></td>
		<td><label for='ligne_$cpt'>".$tab[3]."</label></td>";

						$sql="SELECT * FROM utilisateurs WHERE nom='".mysql_real_escape_string($tab[1])."' AND prenom='".mysql_real_escape_string($tab[2])."' AND statut!='eleve' AND statut!='responsable' ORDER BY statut;";
						$res=mysql_query($sql);
						if(mysql_num_rows($res)==1) {
							// Un seul personnel correspond
							$lig=mysql_fetch_object($res);

							echo "
		<td title=\"$lig->nom $lig->prenom ($lig->statut)\">
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>
			$lig->login
			<span id='saisie_$cpt'></span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
						}
						else {
							// Plusieurs personnels correspondent
							// Il va falloir choisir
							$chaine_options="";
							while($lig=mysql_fetch_object($res)) {
								$chaine_options.="				<option value=\"$lig->login\">$lig->nom $lig->prenom (".$lig->statut.")";
								$chaine_options.="</option>\n";
							}

							if($chaine_options!="") {
								echo "
		<td>
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'>
			<select name='login_$cpt' id='login_$cpt' onchange=\"if(document.getElementById('login_$cpt').options[if(document.getElementById('login_$cpt').selectedIndex].value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\">
				<option value=''>---</option>
				$chaine_options
			</select>
			</span>

			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
							}
							else {
								echo "
		<td>";
								if($module_suhosin_actif!="y") {
									echo "
			<input type='text' name='login_$cpt' id='login_$cpt' value=\"\" onchange=\"if(document.getElementById('login_$cpt').value!='') {document.getElementById('ligne_$cpt').checked=true;} else{document.getElementById('ligne_$cpt').checked=false;}\" />";
								}
								else {
									echo "
			<div style='float:right; width:16px'><a href='javascript:ajout_champ_saisie_login($cpt)' title='Ajouter un champ de saisie de login'><img src='../images/icons/wizard.png' /></a></div>

			<span id='saisie_$cpt'></span>";
								}

								// On renseigne le formulaire de recherche avec le nom et le prénom:
								// ...
								// On fait le nettoyage pour ne pas laisser les traces d'une précédente requête:
								echo "
			<a href='#' onClick=\"document.getElementById('nom_rech').value=document.getElementById('nom_$cpt').innerHTML;
									document.getElementById('prenom_rech').value=document.getElementById('prenom_$cpt').innerHTML;
									document.getElementById('login_recherche').value='login_$cpt';
									document.getElementById('div_resultat').innerHTML='';
									afficher_div('div_search','y',-400,20);
									if(!document.getElementById('login_$cpt')) {ajout_champ_saisie_login($cpt);};
									return false;\">
				<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' title='Effectuer une recherche' />
			</a>
		</td>
	</tr>
";
								// A FAIRE: Mettre un dispositif de recherche comme dans mod_annees_anterieures
							}
						}

						$sql="INSERT INTO tempo2_sso SET col1='$cpt', col2='".mysql_real_escape_string($ligne)."';";
						$insert=mysql_query($sql);
						if(!$insert) {
							echo "<span style='color:red'>ERREUR</span>";
						}
						$cpt++;
					}
				}
			}
		}

		echo "
</table>
<input type='hidden' name='temoin_suhosin_2' value='personnel' />
<input type='submit' id='bouton_submit_import' value='Enregistrer' />
<input type='button' id='bouton_button_import' value='Enregistrer' style='display:none;' onclick='verif_puis_submit()' />
</form>
";

		if(count($tab_doublon_possible)>0) {
			echo "<p style='text-indent:-8em; margin-left:8em;'><strong style='color:red'>ATTENTION&nbsp:</strong> Un ou des doublons possibles ont été repérés.<br />
Veuillez contrôler manuellement s'il s'agit ou non de doublons&nbsp;:<br />";
			for($loop=0;$loop<count($tab_doublon_possible);$loop++) {
				echo "<a href='#doublon_possible_".$tab_doublon_possible[$loop]."'>".$tab_doublon_possible[$loop]."</a><br />\n";
			}
			echo "
</p>\n";
		}

		echo "<p>$cpt ligne(s) affichée(s).</p>\n";

		if($cpt_deja_enregistres>0) {
			echo "<p><a href='".$_SERVER['PHP_SELF']."?mode=consult_personnels'>$cpt_deja_enregistres association(s) personnel(s) déjà enregistrée(s)</a>.<br />\n";
		}
		else {
			echo "<p>Aucune association de compte personnel n'est encore enregistrée.<br />\n";
		}

		echo "
<p style='color:red'>A FAIRE:<br />
Ajouter une variable en fin de formulaire pour détecter les pb de transmission de trop de variables avec suhosin.</p>

<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';
	document.getElementById('bouton_button_import').style.display='';
	document.getElementById('bouton_submit_import').style.display='none';

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=true;
				change_graisse(i);
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				document.getElementById('ligne_'+i).checked=false;
				change_graisse(i);
			}
		}
	}

	function ajout_champ_saisie_login(num) {
		if(document.getElementById('saisie_'+num)) {
			document.getElementById('saisie_'+num).innerHTML='<input type=\"text\" name=\"login_'+num+'\" id=\"login_'+num+'\" value=\"\" onchange=\"if(document.getElementById(\'login_'+num+'\').value!=\'\') {document.getElementById(\'ligne_'+num+'\').checked=true;} else {document.getElementById(\'ligne_'+num+'\').checked=false;}\" />';
		}
	}

	function verif_puis_submit() {
		temoin='n';
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('ligne_'+i)) {
				if(document.getElementById('ligne_'+i).checked==true) {
					temoin='y';
					break;
				}
			}
		}
		if(temoin=='y') {
			document.forms['form_import'].submit();
		}
		else {
			alert('Vous n avez rien coché!?');
		}
	}

	function change_graisse(num) {
		if((document.getElementById('ligne_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('ligne_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}
</script>
";
		// En fin d'enregistrement, renvoyer vers consult_eleves pour afficher les associations
		// Compter/afficher ce total des associations avant... et après dans consult_eleves
	}
	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="consult_personnels") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
 | <a href='".$_SERVER['PHP_SELF']."?mode=import_personnels'>Importer un CSV personnels</a>
</p>

<h2>Rapprochement actuels des comptes de personnels ENT ITOP/GEPI</h2>
";

	$sql="SELECT u.*, s.* FROM utilisateurs u, sso_table_correspondance s WHERE s.login_gepi=u.login AND u.statut!='eleve' AND u.statut!='responsable' ORDER BY u.nom, u.prenom";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>Aucun rapprochement de personnel n'est enregistré.</p>\n";

		require("../lib/footer.inc.php");
		die();
	}

	echo "
<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<p>Les rapprochements enregistrés (".mysql_num_rows($res).") sont les suivants&nbsp;:</p>
	".add_token_field()."
	<input type='hidden' name='mode' value='consult_personnels' />
	<input type='hidden' name='temoin_suhosin_1' value='personnel' />

	<table class='boireaus'>
		<tr>
			<th>
				<input type='submit' value='Supprimer' />
				<span id='tout_cocher_decocher' style='display:none;'>
					<br />
					<a href=\"javascript:tout_cocher()\" title='Tout cocher'><img src='../images/enabled.png' width='20' height='20' /></a>
					/
					<a href=\"javascript:tout_decocher()\" title='Tout décocher'><img src='../images/disabled.png' width='20' height='20' /></a>
				</span>
			</th>
			<th>Guid</th>
			<th>Login</th>
			<th title=\"Pour une connexion via un ENT, le champ auth_mode doit en principe avoir pour valeur 'sso'\">Auth_mode</th>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Statut</th>
			<th>Corriger</th>
		</tr>
";

	$cpt=0;
	$alt=1;
	while($lig=mysql_fetch_object($res)) {
		$alt=$alt*(-1);
		echo "
		<tr class='lig$alt white_hover'>
			<td><input type='checkbox' name='suppr[]' id='suppr_$cpt' value=\"$lig->login\" onchange=\"change_graisse($cpt)\" /></td>
			<td><label for='suppr_$cpt'>$lig->login_sso</label></td>
			<td><label for='suppr_$cpt'>$lig->login</label></td>
			<td><label for='suppr_$cpt'>";
		$sql="SELECT auth_mode, etat FROM utilisateurs WHERE login='".$lig->login."';";
		$test_u=mysql_query($sql);
		if(mysql_num_rows($test_u)==0) {
			echo "<span title='Pas de compte utilisateur pour ce personnel.' style='color:red'>???</span>";
		}
		else {
			$lig_u=mysql_fetch_object($test_u);
			if($lig_u->etat=='actif') {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy.png' width='16' height='16' title='Compte actif' /></div>\n";
			}
			else {
				echo "<div style='float:right;width:16px;'><img src='../images/icons/buddy_no.png' width='16' height='16' title='Compte inactif' /></div>\n";
			}
			echo $lig_u->auth_mode;
		}
		echo "</label></td>
			<td><label for='suppr_$cpt'><span id='nom_$cpt'>$lig->nom</label></span></td>
			<td><label for='suppr_$cpt'><span id='prenom_$cpt'>$lig->prenom</span></label></td>
			<td><label for='suppr_$cpt'>$lig->statut</label></td>
			<td><a href='".$_SERVER['PHP_SELF']."?login_gepi=$lig->login_gepi&amp;login_sso=$lig->login_sso&amp;mode=saisie_manuelle'><img src='../images/edit16.png' width='16' height='16' title=\"Corriger l'association\" /></label></td>
		</tr>
";
		$cpt++;
	}

	echo "
	</table>
	<input type='hidden' name='temoin_suhosin_2' value='personnel' />
</form>


<script type='text/javascript'>
	document.getElementById('tout_cocher_decocher').style.display='';

	function tout_cocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=true;
				change_graisse(i);
			}
		}
	}

	function tout_decocher() {
		for(i=0;i<$cpt;i++) {
			if(document.getElementById('suppr_'+i)) {
				document.getElementById('suppr_'+i).checked=false;
				change_graisse(i);
			}
		}
	}

	function change_graisse(num) {
		if((document.getElementById('suppr_'+num))&&(document.getElementById('nom_'+num))&&(document.getElementById('prenom_'+num))) {
			if(document.getElementById('suppr_'+num).checked==true) {
				document.getElementById('nom_'+num).style.fontWeight='bold';
				document.getElementById('prenom_'+num).style.fontWeight='bold';
			}
			else {
				document.getElementById('nom_'+num).style.fontWeight='normal';
				document.getElementById('prenom_'+num).style.fontWeight='normal';
			}
		}
	}
</script>
";

	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="publipostage_eleves") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

	echo "
<h2 class='noprint'>Fiches bienvenue élèves</h2>";

	if(!isset($csv_file)) {
		echo "<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<p>Veuillez fournir le fichier ".getSettingValue("gepiSchoolRne")."_MiseaJour_Motdepasse_Eleve_JJ_MM_AAAA_HH_MM_SS.csv généré par l'ENT.</p>
		<input type='hidden' name='mode' value='publipostage_eleves' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li>Cette rubrique est destinée à générer des Fiches Bienvenue avec compte et mot de passe de l'ENT.<br />
	<!--
		Ne seront pris en compte que les comptes ENT pour lesquels le rapprochement avec un compte Gepi est effectué.
		Ce n'est pas le cas.
		Il faudrait enregistrer davantage d'informations dans sso_table_correspondance pour s'en assurer.
	-->
	</li>
	<li>Modifier les <a href='../gestion/modify_impression.php?fiche=eleves'>Fiches Bienvenue élèves</a></li>
	<li>Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	﻿Nom;Prénom;Login;Numéro de jointure;Mot de passe;Email;Classe;Etat;Date de désactivation<br />
	DUPRE;Thomas;thomas.dupre;MENESR$12345;mdp&*;Thomas.DUPRE@ent27.fr;6 A;Actif<br />
	...</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		$impression=getSettingValue('ImpressionFicheEleve');

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$cpt=0;
		$tab_classe_eleve=array();
		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Nom;Pr/i", trim($ligne))) {
					if(!isset($tab_classe_eleve[$tab[6]])) {
						$cpt=0;
					}
					else {
						$cpt=count($tab_classe_eleve[$tab[6]]);
					}
					$tab_classe_eleve[$tab[6]][$cpt]['nom_prenom']=$tab[0]." ".$tab[1];
					//echo "\$tab_classe_parent[$tab[11]][$cpt]['nom_prenom']=".$tab_classe_parent[$tab[11]][$cpt]['nom_prenom']."<br />";
					$tab_classe_eleve[$tab[6]][$cpt]['login_ent']=$tab[2];
					$tab_classe_eleve[$tab[6]][$cpt]['mdp_ent']=$tab[4];
					$tab_classe_eleve[$tab[6]][$cpt]['email_ent']=$tab[5];
					//$cpt++;
				}
			}
		}

		foreach($tab_classe_eleve as $classe => $tab_eleve) {
			/*
			echo "<pre>";
			print_r($tab_parent);
			echo "</pre>";
			*/
			for($loop=0;$loop<count($tab_eleve);$loop++) {
				echo "<table>
	<tr>
		<th style='text-align:left;'>A l'attention de </th>
		<th>: </th>
		<td>".$tab_eleve[$loop]['nom_prenom']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Login ENT</th>
		<th>: </th>
		<td>".$tab_eleve[$loop]['login_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Mot de passe ENT</th>
		<th>: </th>
		<td>".$tab_eleve[$loop]['mdp_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Email ENT</th>
		<th>: </th>
		<td>".$tab_eleve[$loop]['email_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Classe</th>
		<th>: </th>
		<td>".$classe."</td>
	</tr>
</table>
$impression
<p class='saut'></p>";
			}
		}

	}

	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="publipostage_responsables") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

	echo "
<h2 class='noprint'>Fiches bienvenue responsables</h2>";

	if(!isset($csv_file)) {
		echo "<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<p>Veuillez fournir le fichier ".getSettingValue("gepiSchoolRne")."_MiseaJour_Motdepasse_Parent_JJ_MM_AAAA_HH_MM_SS.csv généré par l'ENT.</p>
		<input type='hidden' name='mode' value='publipostage_responsables' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li>Cette rubrique est destinée à générer des Fiches Bienvenue avec compte et mot de passe de l'ENT.<br />
	<!--
		Ne seront pris en compte que les comptes ENT pour lesquels le rapprochement avec un compte Gepi est effectué.
		Ce n'est pas le cas.
		Il faudrait enregistrer davantage d'informations dans sso_table_correspondance pour s'en assurer.
	-->
	</li>
	<li>Modifier les <a href='../gestion/modify_impression.php?fiche=responsables'>Fiches Bienvenue responsables</a></li>
	<li>Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	﻿﻿Nom;Prénom;Login;Numéro de jointure;Mot de passe;Email;Adresse;Code postal;Ville;Nom enfant 1;Prénom enfant 1;Classe enfant 1;Etat;Date de désactivation<br />
	DUPRE;Denis;denis.dupre1;MENESR$1234567;azerty&*;Denis.DUPRE1@ent27.fr;3 RUE DES PRIMEVERES;27300;BERNAY;DUPRE;Thomas;6 A;Actif<br />
	...</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		$impression=getSettingValue('ImpressionFicheParent');

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$cpt=0;
		//$classe_precedente="";
		$tab_classe_parent=array();
		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Nom;Pr/i", trim($ligne))) {
					/*
					if($tab[11]!=$classe_precedente) {
						$cpt=0;
						$classe_precedente=$tab[11];
					}
					*/
					if(!isset($tab_classe_parent[$tab[11]])) {
						$cpt=0;
					}
					else {
						$cpt=count($tab_classe_parent[$tab[11]]);
					}
					$tab_classe_parent[$tab[11]][$cpt]['nom_prenom']=$tab[0]." ".$tab[1];
					//echo "\$tab_classe_parent[$tab[11]][$cpt]['nom_prenom']=".$tab_classe_parent[$tab[11]][$cpt]['nom_prenom']."<br />";
					$tab_classe_parent[$tab[11]][$cpt]['login_ent']=$tab[2];
					$tab_classe_parent[$tab[11]][$cpt]['mdp_ent']=$tab[4];
					$tab_classe_parent[$tab[11]][$cpt]['email_ent']=$tab[5];
					$tab_classe_parent[$tab[11]][$cpt]['adresse']=$tab[6]."<br />".$tab[7]." ".$tab[8];
					$tab_classe_parent[$tab[11]][$cpt]['resp_de']=$tab[9]." ".$tab[10]." (".$tab[11].")";
					//$cpt++;
				}
			}
		}

		foreach($tab_classe_parent as $classe => $tab_parent) {
			/*
			echo "<pre>";
			print_r($tab_parent);
			echo "</pre>";
			*/
			for($loop=0;$loop<count($tab_parent);$loop++) {
				echo "<table>
	<tr>
		<th style='text-align:left;'>A l'attention de </th>
		<th>: </th>
		<td>".$tab_parent[$loop]['nom_prenom']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Login ENT</th>
		<th>: </th>
		<td>".$tab_parent[$loop]['login_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Mot de passe ENT</th>
		<th>: </th>
		<td>".$tab_parent[$loop]['mdp_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Email ENT</th>
		<th>: </th>
		<td>".$tab_parent[$loop]['email_ent']."</td>
	</tr>
	<tr>
		<th style='text-align:left; vertical-align:top;'>Adresse</th>
		<th style='vertical-align:top;'>: </th>
		<td>".$tab_parent[$loop]['adresse']."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Responsable notamment de</th>
		<th>: </th>
		<td>".$tab_parent[$loop]['resp_de']."</td>
	</tr>
</table>
$impression
<p class='saut'></p>";
					//}
				flush();
			}
		}

	}

	require("../lib/footer.inc.php");
	die();
}

//==================================================================================
if($mode=="publipostage_personnels") {
	echo "
 | <a href='".$_SERVER['PHP_SELF']."'>Index rapprochement ENT ITOP</a>
</p>";

	$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

	echo "
<h2 class='noprint'>Fiches bienvenue professeurs</h2>";

	if(!isset($csv_file)) {
		echo "<form action='".$_SERVER['PHP_SELF']."' method='post' enctype='multipart/form-data'>
	<fieldset style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\");'>
		".add_token_field()."
		<input type='hidden' name='mode' value='publipostage_personnels' />
		<input type=\"file\" size=\"65\" name=\"csv_file\" style='border: 1px solid grey; background-image: url(\"../images/background/opacite50.png\"); padding:5px; margin:5px;' /><br />
		<input type='submit' value='Envoyer' />
	</fieldset>
</form>

<p><br /></p>

<p style='text-indent:-4em; margin-left:4em;'><em>NOTES&nbsp;:</em></p>
<ul>
	<li>Cette rubrique est destinée à générer des Fiches Bienvenue avec compte et mot de passe de l'ENT.<br />
	<!--
		Ne seront pris en compte que les comptes ENT pour lesquels le rapprochement avec un compte Gepi est effectué.
		Ce n'est pas le cas.
		Il faudrait enregistrer davantage d'informations dans sso_table_correspondance pour s'en assurer.
	-->
	</li>
	<li>Modifier les <a href='../gestion/modify_impression.php'>Fiches Bienvenue professeurs</a></li>
	<li>Le fichier CSV attendu doit avoir le format suivant&nbsp;:<br />
	﻿Nom;Prénom;Login;Numéro de jointure;Mot de passe;Email;Classe(s);Etat;Date de désactivation<br />
ZETOFREY;Melanie;melanie.zetofrey;MENESR$12345;azerty&*;Melanie.ZETOFREY@ent27.fr;4 B, 4 D, 5 B, 6 B, 6 D;Actif
<br />
	...</li>
</ul>\n";
	}
	else {
		check_token(false);
		$fp=fopen($csv_file['tmp_name'],"r");

		$impression=getSettingValue('Impression');

		if(!$fp){
			echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
			echo "<p><a href='".$_SERVER['PHP_SELF']."'>Cliquer ici </a> pour recommencer !</center></p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		while (!feof($fp)) {
			$ligne = trim(fgets($fp, 4096));
			if((substr($ligne,0,3) == "\xEF\xBB\xBF")) {
				$ligne=substr($ligne,3);
			}

			if($ligne!='') {
				$tab=explode(";", ensure_utf8($ligne));
				if(!preg_match("/^Nom;Pr/i", trim($ligne))) {
					/*
					$sql="SELECT e.* FROM eleves e, sso_table_correspondance stc WHERE stc.login_gepi=e.login AND ;";
					$res_ele=mysql_query($sql);
					if(mysql_fetch_object($res_ele)>0) {
					*/
						echo "<table>
	<tr>
		<th style='text-align:left;'>A l'attention de </th>
		<th>: </th>
		<td>".$tab[0]." ".$tab[1]."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Login ENT</th>
		<th>: </th>
		<td>".$tab[2]."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Mot de passe ENT</th>
		<th>: </th>
		<td>".$tab[4]."</td>
	</tr>";
						/*
						echo "
	<tr>
		<th style='text-align:left;'>Email Gepi</th>
		<th>: </th>
		<td>".$tab[4]."</td>
	</tr>";
						*/
						echo "
	<tr>
		<th style='text-align:left;'>Email ENT</th>
		<th>: </th>
		<td>".$tab[5]."</td>
	</tr>
	<tr>
		<th style='text-align:left;'>Classe(s)</th>
		<th>: </th>
		<td>".$tab[6]."</td>
	</tr>
</table>
$impression
<p class='saut'></p>";
					//}

				}
			}
		}

	}

	require("../lib/footer.inc.php");
	die();
}



//==================================================================================

echo "<p style='color:red'>Mode non encore développé</p>\n";
echo "<p><br /></p>\n";

require("../lib/footer.inc.php");
?>
