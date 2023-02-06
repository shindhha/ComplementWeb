
<?php
	// Url API
	$urlRecherche="https://geo.api.gouv.fr" ;
	
	// URLS
	//https://geo.api.gouv.fr/regions
	//https://geo.api.gouv.fr/regions/76
	//https://geo.api.gouv.fr/regions/76/departements
	//https://geo.api.gouv.fr/departements/
	//https://geo.api.gouv.fr/departements/12
	//https://geo.api.gouv.fr/departements/12/communes"
	//https://geo.api.gouv.fr/epcis/
	//https://geo.api.gouv.fr/epcis/241200187/communes
	
	
	function appelAPI($apiUrl) {
		// Interrogation de l'API
		// Retourne le résultat en format JSON
		$curl = curl_init();									// Initialisation

		curl_setopt($curl, CURLOPT_URL, $apiUrl);				// Url de l'API à appeler
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			// Retour dans une chaine au lieu de l'afficher
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 		// Désactive test certificat
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		
		// A utiliser sur le réseau des PC IUT, pas en WIFI, pas sur une autre connexion
		$proxy="http://cache.iut-rodez.fr:8080";
		curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
		curl_setopt($curl, CURLOPT_PROXY,$proxy ) ;
		///////////////////////////////////////////////////////////////////////////////
		$result = curl_exec($curl);								// Exécution
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);	// Récupération statut 
		// Si 404  indique qu'un serveur ne peut pas trouver la ressource demandée
		// Si 200 c'est OK
		
		curl_close($curl);										// Cloture curl
		
		if ($http_status=="200") {								// OK, l'appel s'est bien passé
			return json_decode($result,true); 					// Retourne la collection 
		} else {
			$result=[]; 										// retourne une collection Vide
			return $result;
		}
	}
	
	function pIsset($name,$valueExpected = null) {
		if ($valueExpected != null) {
			return isset($_GET[$name]) && $_GET[$name] != "" && $_GET[$name] == $valueExpected;
		}
		return isset($_GET[$name]) && $_GET[$name] != "";
	}
	function issetAll($array) {
		$isOk = true;
		foreach ($array as $value) {
			$isOk &= pIsset($value);
		}
		return $isOk;
	}
	function addVariable($name) {
		return $name . "=" . $_GET[$name]; 
	}
?>


<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<title>WEB avancé TP2</title>

		<!-- Bootstrap CSS -->
		<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
		
		<!-- Lien vers mon css -->
		<link href="css/monStyle.css" rel="stylesheet">
	
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-xs-12 cadresCom">
					<h1>-- Recherche des informations d'une commune --</h1>
				</div>
				<div class="col-xs-4 cadresCom hauteurMin">
					<form action="tp2.php" method="GET">
						<br/>
						<?php
							$regionsTempo = appelAPI("https://geo.api.gouv.fr/regions");
							$regions = new ArrayObject($regionsTempo);
						?>
						<label for="region">Région (<?php echo sizeof($regions); ?>) : </label>
						<select name="region"  class="form-control">
							<option value="">Choisir une région</option>
							<?php
								$regions->asort();
								foreach($regions as $reg){
									echo '<option value="'.$reg['code'].'"';
									if (pIsset("region",$reg['code'])) {
										echo ' selected';
									}
									echo '>'.$reg['nom'].'</option>';
								}

							?>
						</select>
						<br>
						<button type="submit" class="btn btn-block btn-primary">Afficher les départements de la région</button>
					</form>
				</div>
			
				<div class="col-xs-4 cadresCom hauteurMin">
					
					<?php 
						if (pIsset('region')){
							$departementTempo = appelAPI("https://geo.api.gouv.fr/regions/".$_GET["region"]."/departements");
							$departement = new ArrayObject($departementTempo);
					?>
					<!-- Région remplie, on cherche le département -->
					<form action="tp2.php" method="GET">
						<br/>
						
						<label for="departement">Département (<?php echo sizeof($departement); ?>) : </label>
						<input type="hidden" name="region" value="<?php echo $_GET["region"]; ?>">
						<select name="departement"  class="form-control">
							<option value="">Choisir un département</option>
							<?php
								$departement->asort();
								if (sizeof($departement) != 1) {
									foreach($departement as $dep){
										echo '<option value="'.$dep["code"].'"';
										if (pIsset('departement',$dep['code'])) {
											echo ' selected';
											
										}
										echo '>'.$dep['nom'].'</option>';
									}
								}else {
									echo '<option value="'.$departement[0]["code"].'" selected>'.$departement[0]["nom"].'</option>';
									$_GET["departement"] = $departement[0]["code"];
									$codeDepartement = $departement[0]["code"];
								}

							?>
						</select>
						<br>
						<button type="submit" class="btn btn-block btn-primary">Afficher les communes du département</button>
					</form>
					
					<?php
						}
					?>
				</div>
				
				<div class="col-xs-4 cadresCom hauteurMin">

					<?php 
						if (issetAll(['region','departement'])){
							$communesTempo = appelAPI("https://geo.api.gouv.fr/departements/". $_GET["departement"] . "/communes");
							$communes = new ArrayObject($communesTempo);
					?>

					<!-- département rempli, on cherche la commune -->
					<form action="tp2.php" method="GET">
						<br/>
						
						<label for="commune">Commune (<?php echo sizeof($communes); ?>) : </label>
						<input type="hidden" name="region" value="<?php echo $_GET["region"]; ?>">
						<input type="hidden" name="departement" value="<?php echo $_GET["departement"]; ?>">
						<select name="commune"  class="form-control">
							<option value="">Choisir une commune</option>
							<?php
								$communes->asort();
								if (sizeof($communes) != 1) {
									foreach($communes as $comm){
										echo '<option value="'.$comm['code'].'"';
										if (isset($_GET["commune"]) && $_GET["commune"] == $comm['code']) {
											echo ' selected';
											$communeOk = true;
										}
										echo '>'.$comm['nom'].'</option>';
									}
								}else {
									echo '<option value="'.$communes[0]["nom"].'" selected>'.$communes[0]["nom"].'</option>';
								}
								

							?>
						</select>
						<br>
						<button type="submit" value="Rechercher" class="btn btn-block btn-primary">Afficher les informations de la commune</button>
					</form>
					<?php
						}
					?>

				</div>
			</div>
			<br><br>

			<!-- Commune remplie, on affiche les renseignements -->
			
			<?php 
				if (issetAll(["commune","departement","region"])){
					$communeInfo = appelAPI("https://geo.api.gouv.fr/communes/".$_GET["commune"]);
					$communauteComm = appelAPI("https://geo.api.gouv.fr/epcis/".$communeInfo['codeEpci']);
					$communesCommunauteTempo = appelAPI("https://geo.api.gouv.fr/epcis/".$communeInfo['codeEpci']."/communes");
					$region = appelAPI("https://geo.api.gouv.fr/regions/".$_GET['region']);
					$departement = appelAPI("https://geo.api.gouv.fr/departements/".$_GET['departement']);
					
			?>
			<div class="row">
				<div class="col-xs-12 cadresCom hauteurMinResultat">
					<div class='row '>
						<div class='col-xs-12 '>
							<h1><?php echo $communeInfo['nom'] ;?></h1>
						</div>
						<div class='col-xs-4 cadreAGauche'>
							Région : <a href='tp2.php?region=<?php echo $_GET['region']; ?>'><?php echo $_GET['region'] . ' - '. $region['nom']; ?></a><br/><br/>
							Département : <a href='tp2.php?departement=<?php echo $_GET['departement']; ?>'><?php echo $_GET['region'] . ' - '. $departement['nom']; ?></a><br/><br/>
							Commune : <?php echo $_GET['commune']. ' - ' . $communeInfo['nom'] ;?><br/><br/>
							Code SIREN : <?php echo $communeInfo['siren'] ;?><br/>
						</div>
						<div class='col-xs-4 cadreAGauche'>
							Communauté de communes : <br>
							<?php echo $communeInfo['codeEpci'] . ' - '. $communauteComm['nom'];?><br>
							<?php echo $communauteComm["population"];?> habitants<br><br>
							Communes : 
							<ul>
								<?php
									$communesCommunaute = new ArrayObject($communesCommunauteTempo);
									$communesCommunaute->asort();
									foreach($communesCommunaute as $comm) {
										echo '<li><a href="tp2.php?region='.$comm[''].'commune='.$comm['code'].'">'.$comm['nom'].'</a></li>';
									}
								?>
							</ul>
						</div>
						<div class='col-xs-4'><?php echo $communeInfo["population"];?> habitants<br/><br/>
							Codes postaux : 
							<ul>
								<?php
									foreach($communeInfo['codesPostaux'] as $cp){

										echo "<li><a href='tp2.php?".addVariable("region")."&".addVariable("departement")."&commune=".$cp."'>".$cp.'</li>';
									}
								?>
							</ul>
						</div>
					</div>					
				</div>
			</div>
			<?php
				}
			?>
		</div>
		<br><br>
	</body>
</html>