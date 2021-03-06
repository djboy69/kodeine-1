<?php

	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('category')->categoryRemove($e);
		}
		$reload = true;
	}

	if($_POST['action']){
		$do = true;

		$def['k_category'] = array(
			'mid_category'			=> array('value' => $_POST['mid_category']),
			'categoryTemplate'		=> array('value' => $_POST['categoryTemplate'])
		);

		foreach($_POST['lan'] as $iso => $lan_){
			$dat['k_categorydata'] = array(
				'categoryUrl'			=> array('value' => $lan_['categoryUrl'], 	'check' => '.'),
				'categoryName'			=> array('value' => $lan_['categoryName'], 	'check' => '.'),
				'categoryMedia'			=> array('value' => $lan_['categoryMedia']),
				'categoryDescription'	=> array('value' => $lan_['categoryDescription'])
			);

			if(!$lan_['copy'] && !$app->formValidation($dat, array('suffix' => '-'.$iso))) $do = false;
			if(!$lan_['copy'] && !$app->apiLoad('field')->fieldValidation($lan_['field'])) $do = false;

			$lan[$iso] = array(
				'copy'	=> (($lan_['copy']) ? $_POST['ref'] : NULL),
				'sql'	=> $dat,
				'field'	=> $lan_['field']
			);
		}

		if($_POST['errorUrl'] != ''){
			$app->formError['categoryUrl-'.$iso] = true;
			$do = false;
		}

		if($do){
			$result  = $app->apiLoad('category')->categorySet($_POST['id_category'], $def, $lan);
			$message = ($result) ? 'OK: Enregistrement effectu�' : 'KO: Erreur APP:<br />'.$app->db_error;
			$reload  = 'id_category='.$app->apiLoad('category')->id_category.'&message='.urlencode($message);
		}else{
			$message = 'KO: Attention, les champs ne sont pas remplis correctement.';
		}
	}

	if(isset($reload)) header("Location: /admin/category/?".$reload.'&opened='.$_REQUEST['opened']);

	$fields = $app->apiLoad('field')->fieldGet(array(
		'category'	=> true,
		'debug'		=> false	
	));

	$languages = $app->countryGet(array('is_used' => 1));
	foreach($languages as $e){
		$isoJS[] = "'".$e['iso']."'";
	}

	if($_REQUEST['id_category'] != NULL){
		$data = $app->apiLoad('category')->categoryGet(array(
			'language' 		=> 'fr',
			'id_category' 	=> $_REQUEST['id_category'],
			'debug'			=> false
		));
		
		// Ajouter les autres LANGUES (celle demandee est inclue)
		foreach($languages as $e){
			$tmp = $app->apiLoad('category')->categoryGet(array(
				'language' 		=> $e['iso'],
				'id_category' 	=> $data['id_category']
			));
			$data['lan'][$e['iso']] = (sizeof($tmp) > 0) ? $tmp : array('is_copy' => true);
		}

		$title = $data['categoryName'];
	}else{
		$title = 'Nouvelle cat�gorie';
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function fieldTrace($app, $lan, $copy, $data, $e){
		$field	= $app->apiLoad('field')->fieldForm(
			$e['id_field'],
			$data,
			array(
				'name'		=> 'lan['.$lan.'][field]['.$e['id_field'].']',
				'id'		=> 'lan-'.$lan.'-field-'.$e['id_field'],
				'class'		=> 'item-'.$lan,
				'style' 	=> 'width:99%; '.$e['__fieldStyle__'],
				'disabled'	=> (($copy) ? 'disabled' : '')
			)
		);

		echo $field;
	}
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php
		echo $app->less('/admin/category/ui/css/category.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<div class="span6">
		<form action="./" method="post" id="items">
			<input type="hidden" name="opened" class="opened-memo" value="<?php echo $_REQUEST['opened'] ?>" />
			<table cellpadding="0" cellspacing="0" border="0" class="listing">
				<thead>
					<th width="5"></th>
					<th width="30"><i class="icon-remove icon-white"></i></th>
					<th>Nom</th>
				</thead>
			</table>
			<div id="category">
				<ul id="mid-0"></ul>
			</div>
		</form>

		<div class="clearfix">
			<div class="left">
				<a onclick="removeSelection()" class="btn btn-mini">Supprimer la selection</a>
				<a href="./" class="btn btn-mini">Annuler</a>
			</div>
			<div class="right">
				<a href="../field/asso?id_type=category" class="btn btn-mini">G�rer les champs</a>
			</div>
		</div>
	</div>

	<div class="span6">
	<?php
	
		if(!isset($message) && isset($_GET['message'])) $message = $_GET['message'];
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	
		?>
		<form action="./" method="post" id="data">
		<input type="hidden" name="action"		value="1" />
		<input type="hidden" name="opened"		value="<?php echo $_REQUEST['opened'] ?>" class="opened-memo" />
		<input type="hidden" name="id_category"	value="<?php echo $data['id_category'] ?>" id="id_category" />
		<input type="hidden" name="language"	value="fr" />
	
		<table border="0" cellspacing="0" cellpadding="0" class="form">
			<tr>
				<td width="150">Cat�gorie parente</td>
				<td><?php
					$category = $app->apiLoad('category')->categoryGet(array(
						'language' 			=> 'fr',
						'mid_category'		=> 0,
						'threadFlat'		=> true,
						'noid_category' 	=> $data['id_category'],
						'debug'				=> false
					));
	
					echo "<select name=\"mid_category\"><option value=\"0\"></option>";
					foreach($category as $e){
						$sel = ($e['id_category'] == $app->formValue($data['mid_category'], $_POST['mid_category'])) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_category']."\"".$sel.">".str_repeat('&nbsp; &nbsp;', $e['level']).' '.$e['categoryName']."</option>";
					}
					echo "</select>";
				?></td>
			</tr>
			<tr>
				<td>Mise en page</td>
				<td><?php
					echo $app->apiLoad('template')->templateSelector(array(
						'name'		=> 'categoryTemplate',
						'value'		=> $app->formValue($data['categoryTemplate'], $_POST['categoryTemplate']),
						'empty'		=> true
					));
				?></td>
			</tr>
		</table>
	
		<div class="tabset">
			<ul class="tab clearfix"><?php
				foreach($languages as $e){
					echo "<li class=\"is-tab ".$class."\" id=\"tab-".$e['iso']."\">";
					echo "<a href=\"javascript:tab('".$e['iso']."');\">".$e['countryLanguage']."</a>";
					echo "</li>";
				}
			?></ul>
			
			<?php foreach($languages as $i => $e){ $iso = $e['iso'] ?>
			<div class="view" id="view-<?php echo $iso ?>">
				<input type="hidden" name="<?php echo 'errorUrl-'.$iso ?>" id="<?php echo 'errorUrl-'.$iso ?>" value="" />
	
				<table border="0" cellpadding="0" cellspacing="0" class="form">
					<tr>
						<td colspan="2" class="view-split"><?php
							if($i == 0){
								echo "<input type=\"hidden\" name=\"ref\" value=\"".$iso."\" /> ";
								echo "Cette langue est la version de r�f�rence";
							}else{
								$copy = ($data['lan'][$iso]['is_copy'] == '1' && $i > 0) ? true : false;
								$chk  = ($copy || $_REQUEST['id_category'] == '') ? ' checked' : NULL;
	
								echo "<input type=\"hidden\"   name=\"lan[".$iso."][copy]\" value=\"0\" />";
								echo "<input type=\"checkbox\" name=\"lan[".$iso."][copy]\" value=\"1\" ".$chk." id=\"copy-".$iso."\" onChange=\"toggleCopy('".$iso."')\" /> Identique � la version de r�f�rence";
							}
						?></td>
					</tr>
					<tr>
						<td width="100" class="<?php echo $app->formError('categoryName-'.$iso, 'alertNeedToCheck') ?>">Nom</td>
						<td>
							<input type="text" name="lan[<?php echo $iso ?>][categoryName]" id="categoryName-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['categoryName'], $_POST['lan'][$iso]['categoryName']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:90%" />
							<input type="checkbox" id="transform-<?php echo $iso ?>" />
						</td>
					</tr>
					<tr>
						<td class="<?php echo $app->formError('categoryUrl-'.$iso, 'alertNeedToCheck') ?>">Url</td>
						<td><input type="text" name="lan[<?php echo $iso ?>][categoryUrl]" id="categoryUrl-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['categoryUrl'], $_POST['lan'][$iso]['categoryUrl']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:90%;" /></td>	
					</tr>
					<tr>
						<td></td>
						<td><span id="alert-<?php echo $iso ?>" style="display:none;">Doublon - Changer de nom</span>&nbsp;</td>
					</tr>
					<tr>
						<td>Image</td>
						<td>
							<input type="text" name="lan[<?php echo $iso ?>][categoryMedia]" id="categoryMedia-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['categoryMedia'], $_POST['lan'][$iso]['categoryMedia']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:75%" />
							<a href="#" onclick="mediaOpen('line', 'categoryMedia-<?php echo $iso ?>')">choisir</a>
						</td>
					</tr>
					<tr valign="top">
						<td>Description</td>
						<td><textarea name="lan[<?php echo $iso ?>][categoryDescription]" id="<?php echo 'categoryDescription-'.$iso ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:90%; height:80px;"><?php
							echo $app->formValue($data['lan'][$iso]['categoryDescription'], $_POST['lan'][$iso]['categoryDescription']);
						?></textarea></td>
					</tr>
					<?php if(sizeof($fields) > 0){ ?>
					<tr>
						<td colspan="2" class="view-split">Param�tres suppl�mentaires</td>
					</tr>
					<?php foreach($fields as $f){ ?>
					<tr valign="top">
						<td><?php echo $f['fieldName'] ?></td>
						<td><div class="field-list"><?php
	
							$tmp = $app->formValue($data['lan'][$iso]['field'][$f['fieldKey']], $_POST['lan'][$iso]['field'][$f['id_field']]);
							
							fieldTrace($app, $iso, $copy, $tmp, $f);
	
						?></div></td>
					</tr>
					<?php }} ?>
				</table>
	
			</div>
			<?php } ?>
		</div>
		
		<a onclick="$('#data').submit();" class="btn btn-mini">Enregistrer</a>
		<a href="/admin/category/" class="btn btn-mini">annuler</a>
	
		</form>
	</div>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
	
<script>
	doMove  		= false;
	useEditor		= true;
	replace 		= [];
	languages		= [<?php echo implode(',', $isoJS) ?>];
	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	datePick		= [<?php echo @implode(',', $GLOBALS['datePick']) ?>];
	opened			= [<?php echo str_replace('-', ',', $_GET['opened']) ?>];

	$(function(){
		boot();
		thread(0,0);
		explorer($('cat-explorer'), 'solo', 'mid_category', 0, 0, '<?php echo $path ?>');
	});
</script>

<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="/admin/category/ui/js/category.js"></script>
<script src="/admin/content/ui/js/content.js"></script>
<script src="/admin/category/ui/js/explorer.js"></script>
</body></html>