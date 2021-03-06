<?php

	if(sizeof($_POST['removeMaster']) > 0){
		foreach($_POST['removeMaster'] as $e){
			$app->dbQuery("DELETE FROM k_localisation WHERE label LIKE '".$e."_%'");
		}
		$goto = "./";
	}else
	if(sizeof($_POST['removeSlave']) > 0){
		foreach($_POST['removeSlave'] as $e){
			$app->dbQuery("DELETE FROM k_localisation WHERE label = '".$_POST['master']."_".$e."'");
		}
		$goto = "./";
	}else	
	if($_REQUEST['addLabel'] != NULL && substr_count($_REQUEST['addLabel'], '_') > 0){
		$app->dbQuery("INSERT INTO k_localisation (language, label) VALUES ('fr', '".$_REQUEST['addLabel']."')");
		list($master, $slave) = explode('_', $_REQUEST['addLabel'], 2);
		$goto = "./?master=".$master."&slave=".$slave;
	}else
	if($_POST['action'] && $_POST['label'] != NULL){
		if(sizeof($_POST['data']) > 0){
			foreach($_POST['data'] as $language => $value){
				$exists = $app->dbOne("SELECT 1 FROM k_localisation WHERE language='".$language."' AND label='".$_POST['label']."'");
				$query	= ($exists[1])
					? "UPDATE k_localisation SET translation='".addslashes($value)."' WHERE language='".$language."' AND label='".$_POST['label']."'"
					: "INSERT INTO k_localisation (language, label, translation) VALUES ('".$language."', '".$_POST['label']."', '".addslashes($value)."')";
					
				$app->dbQuery($query);
			}
		}

		if(sizeof($_POST['kill']) > 0){
			foreach($_POST['kill'] as $e){
				$app->dbQuery("DELETE FROM k_localisation WHERE language='".$e."' AND label='".$_POST['label']."'");
			}
		}

		$goto = "./?master=".$_POST['master']."&slave=".$_POST['slave'];
	}
	
	if(!empty($goto)){

		# Cache Localisation
		$raw = $app->dbMulti("SELECT * FROM k_localisation");
		foreach($raw as $e){
			$all[$e['language']][$e['label']] = base64_encode($e['translation']);
		}

		$app->configSet('boot', 'jsonCacheLocalisation', json_encode($all));
		$app->go($goto);
	}






	$master = $app->apiLoad('localisation')->localisationGet(array(
		'getMaster'	=> true,
		'debug' 	=> false
	));

	if($_REQUEST['master'] == NULL && sizeof($master) > 0){
		$app->go("./?master=".$master[0]);
	}

	if($_REQUEST['master'] != NULL){
		$slave = $app->apiLoad('localisation')->localisationGet(array(
			'getSlave'	=> true,
			'master'	=> $_REQUEST['master'],
			'debug' 	=> false
		));
		
		if($_REQUEST['slave'] == NULL){
			$app->go("./?master=".$_REQUEST['master']."&slave=".$slave[0]);
		}
	}

	$country = $app->countryGet(array('is_used' => true));
	foreach($country as $e){
		$languages[$e['iso']] = utf8_encode($e['countryLanguage']);
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php
		echo $app->less('/admin/localisation/ui/css/localisation.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<form action="./" method="post" id="form-master" class="span3">
		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<tbody>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>

			<?php foreach($master as $e){ $countmaster++; ?>
				<tr class="<?php if($e == $_REQUEST['master']) echo "selected" ?>">
					<td width="20"><input id="delcm-<?php echo $countmaster ?>" type="checkbox" name="removeMaster[]" class="chk" value="<?php echo $e ?>" /></td>
					<td width="20"><a href="grid?master=<?php echo $e ?>"><i class="icon-flag"></i></a></td>
					<td><a href="./?master=<?php echo $e ?>"><?php echo $e ?></a></td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="20"></td>
					<td colspan="2"><div class="clearfix">
						<a onclick="remove('master');" class="btn btn-mini left">Supprimer la selection</a>
						<a onclick="addLabel();" class="btn btn-mini right">Ajouter un label</a>
					</div></td>
				</tr>
			</tfoot>
		</table>
	</form>



	<?php if($_REQUEST['master'] != NULL){ ?>
	<form action="./" method="post" id="form-slave" class="span3">
		<input type="hidden" name="master" value="<?php echo $_REQUEST['master'] ?>" />
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<tbody>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<?php foreach($slave as $e){ $countslave++; ?>
				<tr class="<?php if($e == $_REQUEST['slave']) echo "selected" ?>">
					<td width="20"><input type="checkbox" id="delcs-<?php echo $countslave ?>" class="chk" name="removeSlave[]" value="<?php echo $e ?>" /></td>
					<td><a href="./?master=<?php echo $_REQUEST['master'] ?>&slave=<?php echo $e ?>"><?php echo $e ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="20"></td>
					<td><a href="javascript:remove('slave');" class="btn btn-mini">Supprimer la selection</a></td>
				</tr>
			</tfoot>
		</table>
	</form>
	<?php }  ?>
	
	
	<?php if($_REQUEST['master'] != NULL && $_REQUEST['slave'] != NULL){ ?>
	<form action="./" method="post" name="label" id="label" class="span6">
		<input type="hidden" name="action" 	value="1" />
		<input type="hidden" name="master" 	value="<?php echo $_REQUEST['master'] ?>" />
		<input type="hidden" name="slave" 	value="<?php echo $_REQUEST['slave'] ?>" />
		<input type="hidden" name="label" 	value="<?php echo $_REQUEST['master'].'_'.$_REQUEST['slave'] ?>" />

		<div id="dataItem"><?php

			$data = $app->apiLoad('localisation')->localisationGet(array(
				'label'	=> $_REQUEST['master'].'_'.$_REQUEST['slave'],
				'empty'	=> false,
				'debug' => false
			));

			foreach($data as $d){
				$used[] = $d['language'];
			}

			foreach($country as $e){
				if(!in_array($e['iso'], $used)){
					$plus[] = $e['iso'];
				}
			}

			echo '<div id="items">';
			foreach($data as $e){
				echo "<div class=\"item item-".$e['language']."\">".

					"<div class=\"top clearfix\">".
						"<span class=\"left\">".utf8_decode($languages[$e['language']])."</span>".
						"<a class=\"btn btn-mini right toggle\" onclick=\"on('".$e['language']."')\">Activer/Désactiver l'éditeur de texte</a>".
						"<a class=\"btn btn-mini right\" onclick=\"kill('".$e['language']."');\" style=\"margin-right:10px;\">Supprimer cette version</a>".
					"</div>".
					
					"<div class=\"textarea\">".
					"<textarea id=\"".$e['language']."\" name=\"data[".$e['language']."]\">".$e['translation']."</textarea>".
					"</div>".

				"</div>";
			}
			echo '</div>';

			echo '<div class="add clearfix">';
			if(sizeof($plus) > 0){
				foreach($plus as $e){
					echo '<a onClick="setup(\''.$e.'\', $(this));" class="btn btn-mini">Ajouter '.utf8_decode($languages[$e]).'</a>';
				}
			}
			echo '</div>';

			?>

			<div class="clearfix">
				<a onclick="$('#label').submit()" class="btn btn-mini">Valider</a>
				<a onclick="searchInTheme('<?php echo $_REQUEST['master'].'_'.$_REQUEST['slave'] ?>')" class="btn btn-mini">Localiser ce label dans les fichiers</a>
			</div>
		
			<ul class="searchResult"></ul>

		</div>
	</form>
	<?php } ?>

</div></div></div>

<form method="get" action="./" id="addForm">
	<input type="hidden" name="addLabel" id="addLabel" value="" />
</form>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="/app/module/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="ui/js/localisation.js"></script>
<script>
	languages = <?php echo json_encode($languages); ?>;
</script>

</body></html>