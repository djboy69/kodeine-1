<?php
	if($_REQUEST['id_type'] == NULL){
		$type = $app->apiLoad('content')->contentType(array('profile' => true));
		
		(sizeof($type) > 0)
			? header("Location: index?id_type=".$type[0]['id_type'])
			: header("Location: type?noData");
	}

	if(isset($_REQUEST['duplicate'])){
		$app->apiLoad('content')->contentDuplicate($_REQUEST['duplicate']);
	}
	if(sizeof($_POST['see']) > 0){
		foreach($_POST['see'] as $e => $v){
			$app->dbQuery("UPDATE k_content SET contentSee=".$v." WHERE id_content=".$e);
		}
	}
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('content')->contentRemove($_REQUEST['id_type'], $e, $_REQUEST['language']);
		}
	}

	// Type
	$type 		= $app->apiLoad('content')->contentType();
	$id_type	= $_REQUEST['id_type'];
	$cType		= $app->apiLoad('content')->contentType(array('id_type' => $id_type));

	// Filter (verifier content / album)
	if($id_type == NULL)		die("APP : id_type IS NULL");
	if($cType['is_gallery'])	header("Location: gallery-index?id_type=".$cType['is_type']);

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('content'.$id_type, $_GET);
		$filter = array_merge($app->filterGet('content'.$id_type), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('content'.$id_type, $_POST['filter']);
		$filter = array_merge($app->filterGet('content'.$id_type), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('content'.$id_type);
	}

	$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	
	<li><a onclick="filterToggle('content<?php echo $id_type ?>');" class="btn btn-small">Affichage</a></li>


	<li>
		<div class="btn-group">
			<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-list"></i> <?php echo $cType['typeName']; ?> <span class="caret"></span></a>
			<ul class="dropdown-menu"><?php
			foreach($app->apiLoad('content')->contentType(array('profile' => true)) as $e){
				echo '<li class="clearfix">';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-index' : 'index').'?id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
				echo '</li>';
			}
			?></ul>
		</div>

	</li><li><a href="<?php echo (($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$id_type; ?>" class="btn btn-small btn-success">Ajouter <?php echo $cType['typeName']; ?> </a></li>

</div>

<div id="app">
	
	<div class="quickForm" style="display:<?php echo $filter['open'] ? 'block' : 'none;' ?>;">
	<form action="index" method="post" class="form-horizontal">

		<input type="hidden" name="id_type"			value="<?php echo $id_type ?>" />
		<input type="hidden" name="filter[open]"	value="1" />
		<input type="hidden" name="filter[offset]"	value="0" />
		
		<label class="control-label" for="prependedInput">Combien</label>
		<input type="text" name="filter[limit]" class="input-small" placeholder="" value="<?php echo $filter['limit'] ?>" size="3" />

		<label class="control-label" for="prependedInput">Cat�gorie</label>
		<?php
			echo $app->apiLoad('category')->categorySelector(array(
				'name'		=> 'filter[id_category]',
				'value'		=> $filter['id_category'],
				'language'	=> 'fr',
				'one'		=> true,
				'empty'		=> true
			)); ?>

		<?php if($cType['is_business'] == '1'){ ?>
		<label class="control-label" for="prependedInput">Shop</label>
		<select name="filter[id_shop]">
			<option></option><?php
			$shop = $app->apiLoad('shop')->shopGet();
			foreach($shop as $e){
				echo "<option value=\"".$e['id_shop']."\"".(($filter['id_shop'] == $e['id_shop']) ? ' selected' : '').">".$e['shopName']."</option>";
			}
		?></select>
		<?php }�?>
		
		<label class="control-label" for="prependedInput">Langue</label>
		<select name="filter[language]"><?php
			foreach($app->countryGet(array('is_used' => 1)) as $e){
				$sel = ($e['iso'] == $filter['language']) ? ' selected' : NULL;
				echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryLanguage']."</option>";
			}		
		?></select>

		&nbsp;Tous 		<input type="radio" 	name="filter[viewChildren]" 	value="0" <?php if(!$filter['viewChildren']) echo ' checked'; ?> />
		&nbsp;Ordonner 	<input type="radio" 	name="filter[viewChildren]" 	value="1" <?php if( $filter['viewChildren']) echo ' checked'; ?> />
		&nbsp;H�ritage 	<input type="hidden" 	name="filter[categoryThrough]" 	value="0" />

		<input type="checkbox" name="filter[categoryThrough]" value="1" <?php if($filter['categoryThrough']) echo ' checked'; ?> />

		<button class="btn btn-mini" type="submit">Filter les r�sultats</button>
		<button class="btn btn-mini">Annuler</button>
	</form>
	</div>	

	
	<?php
		// Content
		$language	= ($filter['language'] != '') ? $filter['language'] : 'fr';
		$opt		= array(
			'debug'	 			=> false,
			'id_type' 			=> $id_type,
			'useChapter'		=> false,
			'useGroup'			=> false,
			'contentSee'		=> 'ALL',
		#	'assoUser'			=> true,
			'language'			=> $language,
			'id_category'		=> $filter['id_category'],
			'categoryThrough'	=> (($filter['categoryThrough'] && $filter['id_category'] != '') ? true : false),
			'limit'				=> $filter['limit'],
			'offset'			=> $filter['offset'],
			'search'			=> $filter['q'],
			'order'				=> $filter['order'],
			'direction'			=> $filter['direction'],
			'id_search'			=> $filter['id_search'],
		);
		
		if($filter['viewChildren']){
			$opt['id_parent'] = '0';
		}else{
			$opt['id_parent'] = '*';
		}
		
		if($filter['id_shop']) $opt['id_shop'] = $filter['id_shop'];
		
		$content= $app->apiLoad('content')->contentGet($opt);
		$total	= $app->apiLoad('content')->total;
		$limit	= $app->apiLoad('content')->limit;
	
		$fields = $app->apiLoad('field')->fieldGet(array('id_type' => $id_type, 'debug' => false));
		$lang	= $app->countryGet(array('is_used' => true));
	
		/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	
		function view($app, $cType, $filter, $e, $level=0, $count=NULL){

			if(intval($e['id_content']) == 0) return false;
		
			$version = $app->apiLoad('content')->contentVersionGet(array(
				'id_content'	=> $e['id_content'],
				'language'		=> $e['language']
			));
			
			$colspan = '';
			if(!$cType['is_business']) {
				$colspan = 'colspan="2"';
			}
	
			foreach($app->dbMulti("SELECT language FROM k_contentdata WHERE id_content=".$e['id_content']) as $l){
				$languages .= "<a href=\"data-language?id_content=".$e['id_content']."&language=".$l['language']."\" class=\"lang\">".strtoupper($l['language'])."</a> ";
			}
			
			$link = "data?id_content=".$e['id_content']."&language=".$e['language'];

			echo 
			"<tr>".
				"<td><input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_content']."\" class=\"chk cb\" id=\"chk_remove_".$count."\" /></td>".
				"<td>".
					"<input type=\"hidden\"		name=\"see[".$e['id_content']."]\" value=\"0\" />".
					"<input type=\"checkbox\"	name=\"see[".$e['id_content']."]\" value=\"1\" class=\"chk cs\" ".(($e['contentSee']) ? "checked" : '')." id=\"chk_see_".$count."\" />".
				"</td>".
				"<td class=\"icone\"><a href=\"javascript:duplicate(".$e['id_content'].");\"><i class=\"icon-tags\"></i></a></td>".
				"<td style=\"padding-left:5px;\">".sizeof($version)."</td>".
				"<td><a href=\"comment?id_content=".$e['id_content']."\">".$e['contentCommentCount']."</a></td>".
				"<td>".$languages."</td>".
				"<td><a href=\"".$link."\">".$e['id_content']."</a></td>".
				"<td class=\"dateTime\">".
					"<a href=\"".$link."\">".
						"<span class=\"date\">".$app->helperDate($e['contentDateCreation'], '%d.%m.%G')."</span> ".
						"<span class=\"time\">".$app->helperDate($e['contentDateCreation'], '%Hh%M')."</span>".
					"</a>".
				"</td>".
				"<td class=\"dateTime\">".
					"<a href=\"".$link."\">".
						"<span class=\"date\">".$app->helperDate($e['contentDateUpdate'], '%d.%m.%G')."</span> ".
						"<span class=\"time\">".$app->helperDate($e['contentDateUpdate'], '%Hh%M')."</span>".
					"</a>".
				"</td>".
				"<td style=\"padding-left:".(5 + ($level * 15))."px;\" ".$colspan."><a href=\"".$link."\" style=\"width:100%;display:block;\">".$e['contentName']."</a></td>";

				if($cType['is_business']){
					echo "<td><a href=\"".$link."\" style=\"width:100%;display:block;\">".$e['contentRef']."</a></td>";
				}

			echo "</tr>";
	
			if($filter['viewChildren']){
				$subs = $app->dbMulti("SELECT id_content FROM k_content WHERE id_parent=".$e['id_content']." ORDER BY pos_parent ASC");
	
				foreach($subs as $sub){
					$sub = $app->apiLoad('content')->contentGet(array(
						'debug'	 		=> false,
						'raw'			=> true,
						'language'		=> $e['language'],
						'id_content' 	=> $sub['id_content']
					));
	
					view($app, $cType, $filter, $sub, $level+1, null);
				}
			}
	
		}
	
	?>

	<form method="post" action="index" id="listing">
		<input type="hidden" name="id_type"		value="<?php echo $id_type ?>" />
		<input type="hidden" name="language"	value="<?php echo $language ?>" />
		
		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="10" class="icone"><i class="icon-remove icon-white"></i></th>
					<th width="20" class="icone order <?php if($filter['order'] == 'k_content.contentSee') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentSee&direction=<?php echo $dir ?>'"><span><i class="icon-eye-open icon-white"></i></span></th>
						
					<th width="20" class="icone"><i class="icon-tags icon-white"></i></th>
					<th width="20" class="icone"><i class="icon-file icon-white"></i></th>
					<th width="20" class="icone"><i class="icon-comment icon-white"></i></th>
					<th width="<?php echo 20 + (sizeof($lang) * 20) ?>"class="icone"><i class="icon-globe icon-white"></i></th>
					<th width="60" 	class="order <?php if($filter['order'] == 'k_content.id_content') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.id_content&direction=<?php echo $dir ?>'"><span>#</span></th>
					<th width="130" class="order <?php if($filter['order'] == 'k_content.contentDateCreation')  echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateCreation&direction=<?php echo $dir ?>'"><span>Cr�ation</span></th>
					<th width="130" class="order <?php if($filter['order'] == 'k_content.contentDateUpdate') 	echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateUpdate&direction=<?php echo $dir ?>'"><span>Mise � jour</span></th>
						
					<?php if($cType['is_business']){ ?>
					<th width="200" class="order <?php if($filter['order'] == 'k_content.contentRef') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentRef&direction=<?php echo $dir ?>'"><span>R&eacute;f&eacute;rence</span></th>
					<?php } ?>

					<th class="filter order <?php if($filter['order'] == 'k_contentdata.contentName') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_contentdata.contentName&direction=<?php echo $dir ?>'">
						<span>Nom</span>
						<input type="text" class="input-small" placeholder="filtrer..." id="filter"/>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if(sizeof($content) == 0){ ?>
				<tr>
					<td colspan="10" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">
						Aucun contenu disponible<br /><br />
						<a href="data?id_type=<?php echo $id_type ?>">Ajouter une page : <?php echo $cType['typeName'] ?></a>
					</td>
				</tr>	
			<?php }else{
					$count = 0;
					foreach($content as $e){
						$count++; // count pour les labels
						view($app, $cType, $filter, $e, 0, $count);
					}
				}
			?>
			</tbody>
			<?php if(sizeof($content) > 0){ ?>
			<tfoot>
				<tr>
					<td><input type="checkbox" onchange="cbchange($(this));" class="chk" id="chk_remove_all" /></td>
					<td><input type="checkbox" onchange="cschange($(this));" class="chk" id="chk_see_all" /></td>
					<td colspan="7" height="25"><a href="#" onClick="remove();" class="btn btn-mini"><span>Effectuer les changement sur la selection</span></a></td>
					<td colspan="<?php echo $cType['is_business'] ? 2 : 1 ?>" class="pagination"><?php
						echo 'Total: '.$total.' &nbsp; ';
						$app->pagination($total, $limit, $filter['offset'], 'index?cf&id_type='.$id_type.'&offset=%s');
					?></td>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
	</form>
</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/ui/_datatables/jquery.dataTables.js"></script>
<script src="/app/module/core/ui/_bootstrap/js/bootstrap-dropdown.js"></script>

<script>

	/*function showOpt() {
		shown = $('.menu-inline-left .form-horizontal').css('display');
		if (shown == 'block') {
			$('.menu-inline-left .form-horizontal').css('display', 'none');
			$('.showopt i').attr('class', 'icon-chevron-down icon-white');
		} else {
			$('.menu-inline-left .form-horizontal').fadeTo(218, 1);
			$('.showopt i').attr('class', 'icon-chevron-up icon-white');
		}
	}*/

	function duplicate(id){
		if(confirm("DUPLIQUER ?")){
			document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&duplicate='+id;
		}
	}

	function remove(){
		if(confirm("Confirmez-vous les changements sur la selection ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>