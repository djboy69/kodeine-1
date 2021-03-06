<?php
	if(isset($_REQUEST['duplicate'])){
		$app->apiLoad('newsletter')->newsletterDuplicate($_REQUEST['duplicate']);
		header("Location: /admin/newsletter/index");
	}else
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('newsletter')->newsletterRemove($e);
		}
		header("Location: /admin/newsletter/index");
	}

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('newsletter', $_GET);
		$filter = array_merge($app->filterGet('newsletter'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('newsletter', $_POST['filter']);
		$filter = array_merge($app->filterGet('newsletter'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('newsletter');
	}

	$newsletter = $app->apiLoad('newsletter')->newsletterGet(array(
		'search'	=> $filter['q'],
		'debug'		=> false,
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset'],
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction'],
		'debug'		=> false
	));

	$total	= $app->apiLoad('newsletter')->total;
	$limit	= $app->apiLoad('newsletter')->limit;
	$dir 	= ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';
?><!DOCTYPE html>
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	
	<li><a onclick="filterToggle('newsletter');" class="btn btn-mini">Options d'affichage</a></li>
	<li><a href="data-designer" class="btn btn-small btn-success">Utiliser le designer</a></li>
	<li><a href="data" class="btn btn-small btn-success">Ajouter une newsletter</a></li>
</div>

<div id="app">

	<div class="menu-inline-left clearfix">
		<form action="./" method="post" id="filter" class="form-horizontal" style="display:<?php echo $filter['open'] ? 'block' : 'none;' ?>;">

			<input type="hidden" name="id_type"			value="1" />
			<input type="hidden" name="filter[open]"	value="1" />
			<input type="hidden" name="filter[offset]"	value="0" />
			
			<label class="control-label" for="prependedInput">Recherche</label>
			<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />

			<label class="control-label" for="prependedInput">Combien</label>
			<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />

			<button class="btn btn-mini" type="submit">Filter les résultats</button>
			<button class="btn btn-mini">Annuler</button>
		</form>
	</div>

	<form method="post" action="index" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="20"	class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="20"	class="icone"><i class="icon-tags icon-white"></i></th>
				<th width="20"	class="icone"><i class="icon-signal icon-white"></i></th>
				<th width="80"	class="order <?php if($filter['order'] == 'k_newsletter.id_newsletter') echo 'order'.$dir; ?>" 	onClick="document.location='index?cf&order=k_newsletter.id_newsletter&direction=<?php echo $dir ?>'"><span>#</span></th>
				<th width="120"	class="order <?php if($filter['order'] == 'newsletterSendDate') echo 'order'.$dir; ?>"		 	onClick="document.location='index?cf&order=newsletterSendDate&direction=<?php echo $dir ?>'"><span>Date d'envois</span></th>
				<th 			class="order <?php if($filter['order'] == 'newsletterName')  echo 'order'.$dir; ?>" 			onClick="document.location='index?cf&order=newsletterName&direction=<?php echo $dir ?>'"><span>Nom</span></th>
				<th width="45%" class="order <?php if($filter['order'] == 'newsletterTitle') echo 'order'.$dir; ?>" 			onClick="document.location='index?cf&order=newsletterTitle&direction=<?php echo $dir ?>'"><span>Titre de l'email</span></th>
			</tr>
		</thead>
		<tbody>
		<?php if(sizeof($newsletter) > 0){ foreach($newsletter as $e){ ?>
			<tr>
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_newsletter'] ?>" class="cb" <?php echo $disabled ?> /></td>
				<td class="icone"><a href="javascript:duplicate(<?php echo $e['id_newsletter'] ?>);"><i class="icon-tags"></i></a></td>
				<td style="padding-left:3px;"><a href="analytic?id_newsletter=<?php echo $e['id_newsletter'] ?>"><i class="icon-signal"></i></a></td>
				<td><a href="data?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['id_newsletter'] ?></a></td>
				<td><?php echo ($e['newsletterSendDate'] != NULL) ? $app->helperDate($e['newsletterSendDate'], '%e %b %G %Hh%M') : '-' ?></td>
				<td><a href="data?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['newsletterName'] ?></a></td>
				<td><a href="data?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['newsletterTitle'] ?></a></td>
			</tr>
		<?php }}else{ ?>
			<tr>
				<td colspan="7" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">Aucun contenu disponible<br /><br /><a class="btn btn-mini" href="data">Ajouter une nouvelle Newsletter</a></td>
			</tr>
		<?php } ?>
		</tbody>
		<?php if(sizeof($newsletter) > 0){ ?>
		<tfoot>
			<tr>
				<td><input type="checkbox" onchange="cbchange($(this));" /></td>
				<td colspan="5">
					<a href="#" onClick="remove();" class="btn btn-mini">Supprimer la selection</a> 
					<span class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'index?cf&offset=%s'); ?></span>
				</td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
	</form>

</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/core/ui/_datatables/jquery.dataTables.js"></script>
<script>

	function duplicate(id){
		if(confirm("DUPLIQUER ?")){
			document.location='index?duplicate='+id;
		}
	}
	
	function remove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>