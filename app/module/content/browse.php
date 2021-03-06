<?php
	$types = $app->apiLoad('content')->contentType(array('profile' => true));

	if(sizeof($types) == 0){
		header("Location: type?noData");
		exit();
	}

	$type 		= $app->apiLoad('content')->contentType();
	$id_type	= $_REQUEST['id_type'];
	$cType		= $app->apiLoad('content')->contentType(array('id_type' => $id_type));

	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('content')->contentRemove($_POST['id_type'], $e, $_POST['language']);
		}
		header("Location: browse?id_type=".$_POST['id_type']."#".$_POST['hash']);
	}else
	if(sizeof($_POST['see']) > 0){
		foreach($_POST['see'] as $e => $v){
			$app->dbQuery("UPDATE k_content SET contentSee=".$v." WHERE id_content=".$e);
		}
		header("Location: browse?id_type=".$_POST['id_type']."#".$_POST['hash']);
	}else
	if($_GET['duplicate'] != NULL && $_GET['id_type'] != NULL){
		$app->apiLoad('content')->contentDuplicate($_GET['duplicate']);
		header("Location: browse?id_type=".$_GET['id_type']."#".$_GET['hash']);
	}else
	if($_GET['hash'] != NULL){
		header("Location: browse?id_type=".$types[0]['id_type']."#".$_GET['hash']);
	}else
	if($_REQUEST['id_type'] == NULL){
		header("Location: browse?id_type=".$types[0]['id_type']);
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php
		echo $app->less('/admin/content/ui/css/browse.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<div class="btn-group">
			<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $cType['typeName']; ?> <span class="caret"></span></a>
			<ul class="dropdown-menu"><?php
			foreach($app->apiLoad('content')->contentType(array('profile' => true)) as $e){
				echo '<li class="clearfix">';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-index' : 'browse').'?id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
				echo '</li>';
			}
			?></ul>
		</div>

	</li>
</div>

<div id="app"><div class="wrapper">

	<div class="row-fluid">

	<!-- <div class="menu-inline clearfix" style="height:20px;"><?php
		if($_REQUEST['id_type'] == NULL) $_REQUEST['id_type'] = $types[0]['id_type'];
		foreach($types as $e){
			echo "<div class=\"item button button-".($e['id_type'] == $_REQUEST['id_type'] ? 'green' : 'blue')."\">";
			echo "<a href=\"browse?id_type=".$e['id_type']."\" class=\"text\">".$e['typeName']."</a>";
			echo "<a href=\"/admin/content/".(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type']."\" class=\"plus\"></a> | ";
			echo "</div>";
		}
	?></div> -->

	<br />

	<div style="padding:5px;">

		<div id="browse" class="clearfix">
			<div id="holder" class="clearfix"></div>
		</div>

		<form method="post" action="browse" id="listing">
		<input type="hidden" name="id_type" 	id="form_id_type" 	value="<?php echo $_REQUEST['id_type'] ?>" />
		<input type="hidden" name="hash" 		id="form_hash" 		value="" />
		<input type="hidden" name="language"						value="fr" />
	
		<table width="100%" border="0" cellpadding="0" cellspacing="0" id="view" class="listing">
			<thead style="display:none;">
				<tr>
					<th width="30"  class="icone"><i class="icon-remove icon-white"</th>
					<th width="30"  class="icone"><i class="icon-eye-open icon-white"></i></th>
					<th width="30"  class="icone"><i class="icon-tags icon-white"></i></th>
					<th width="90"  class="icone"><i class="icon-globe icon-white"</th>
					<th width="60" 	class="order">#</th>
					<th width="100" class="order">Cr�ation</th>
					<th width="100" class="order">Mise � jour</th>
					<th 			class="order">Nom</th>
				</tr>
			</thead>
			<tbody id="noData">
				<tr>
					<td colspan="8" style="text-align:center; font-weight:bold; padding:40px 0px 40px 0px;">Aucun document</td>
				</tr>
			</tbody>
			<tbody id="hasData">
			</tbody>
			<tfoot style="display:none;">
				<tr>
					<td width="30"><input type="checkbox" onchange="$('.cb').prop('checked', $(this).prop('checked'));" /></td>
					<td width="30"><input type="checkbox" onchange="$('.cs').prop('checked', $(this).prop('checked'));" /></td>
					<td colspan="6" height="25"><a href="#" onClick="remove();" class="btn btn-mini"><span>Effectuer les changement sur la selection</span></a></td>
				</tr>
			</tfoot>
		</table>
		</form>
	
	</div>
	
</div>
</div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/browse.js"></script> 
<script src="/app/module/core/ui/_bootstrap/js/bootstrap-dropdown.js"></script>
<script src="/app/module/core/ui/_datatables/jquery.dataTables.js"></script>
<script>
	id_type = <?php echo $_GET['id_type'] ?>;	
	hash 	= getHash();
	todo 	= (hash == '') ? [] : hash.split(',');
	todoC 	= 1;
	
	if(hash != null) $('#form_hash').val();

	first();
</script>


</body></html>