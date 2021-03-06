<?php

	if(isset($_GET['allow'])){
		$id = base64_decode($_GET['allow']);
		$app->dbQuery("UPDATE k_contentcomment SET is_moderate=1 WHERE id_comment=".$id);
		$reload = true;
	}else
	if(isset($_GET['remove'])){
		$id = base64_decode($_GET['remove']);
		$app->dbQuery("DELETE FROM k_contentcomment WHERE id_comment=".$id);
		$reload = true;
	}

	if(sizeof($_POST['moderate']) > 0){
		foreach($_POST['moderate'] as $id => $e){
			$app->dbQuery("UPDATE k_contentcomment SET is_moderate=".$e." WHERE id_comment=".$id);
		}
		$reload = true;
	}
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('comment')->commentRemove($e);
		}
		$reload = true;
	}

	if($reload){
		$app->go("./".((isset($_POST['id_content']) ? "?id_content=".$_POST['id_content'] : "")));
	}

	if($_REQUEST['id_content'] != ''){
		$comment = $app->apiLoad('comment')->commentGet(array(
			'id_content'	=> $_REQUEST['id_content'],
			'debug'			=> false
		));
	}else{
		$comment = $app->apiLoad('comment')->commentGet(array(
			'debug'			=> false
		));
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div id="app">

	<form method="post" action="./" id="listing">

	<?php if($_REQUEST['id_content'] != NULL){ ?>
	<div class="mar-top-20 mar-bot-20">
		<a href="../content/data?id_content=<?php echo $_REQUEST['id_content'] ?>" class="btn btn-mini">Revenir � la page</a>
		<input type="hidden" name="id_content" value="<?php echo $_REQUEST['id_content'] ?>" />
	</div>
	<?php } ?>

	<table border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="20" class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="20" class="icone"><i class="icon-ok icon-white"></i></th>
				<th width="140" class="icone"><i class="icon-calendar icon-white"></i></th>
				<th width="100">Note</th>
				<?php if($_REQUEST['id_content'] == NULL){ ?>
				<th width="300">Contenu</th>
				<?php } ?>
				<th>Commentaire</th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($comment) > 0){
			foreach($comment as $e){
	
				$page = $app->apiLoad('content')->contentGet(array(
					'id_content' 	=> $e['id_content'],
					'language'		=> 'fr',
					'raw'			=> true
				));
			?>
			<tr>
				<td><input type="checkbox"	name="remove[]" value="<?php echo $e['id_comment'] ?>" class="cb" /></td>
				<td><input type="hidden" 	name="moderate[<?php echo $e['id_comment'] ?>]" value="0" />
					<input type="checkbox"  name="moderate[<?php echo $e['id_comment'] ?>]" value="1" <?php if($e['is_moderate']) echo " checked" ?> class="cm" />
				</td>
				<td><?php echo $app->helperDate($e['commentDate'], '%e %B %G %Hh%M') ?></td>
				<td><?php echo $e['commentAvg'] ?></td>
				<?php if($_REQUEST['id_content'] == NULL){ ?>
				<td><?php echo "<a href=\"../content/data?id_content=".$e['id_content']."\">".$page['contentName']."</a>"; ?></td>
				<?php } ?>
				<td><?php echo substr(strip_tags($e['commentData']), 0, 125); ?></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="<?php echo ($_REQUEST['id_content'] == NULL) ? '6' : '5'; ?>" class="noData">
					Pas de commentaire
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<?php if(sizeof($comment) > 0){ ?>
			<tr>
				<td><input type="checkbox" onchange="$$('.cb').set('checked', this.checked);" /></td>
				<td><input type="checkbox" onchange="$$('.cm').set('checked', this.checked);" /></td>
				<td colspan="<?php echo ($_REQUEST['id_content'] == NULL) ? '5' : '3'; ?>">
					<a href="#" onClick="remove();" class="btn btn-mini">Effectuer les changements sur la selection</a> 
					<span class="pagination"><?php 
						$app->pagination($total, $limit, $filter['offset'], '/admin/comment/?cf&id_content='.$_GET['id_content'].'&offset=%s');
					?></span>
				</td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td colspan="<?php echo ($_REQUEST['id_content'] == NULL) ? '6' : '5'; ?>">&nbsp;</td>
			</tr>
			<?php } ?>
		</tfoot>
	</table>
	</form>
	
	<div id="mygrid"></div>

</div>

<?php include(COREINC.'/end.php'); ?>
<script>
	function remove(){
		if(confirm("Voulez vous supprimer ou moderer les commentaires ?")){
			$('#listing').submit();
		}
	}
</script>	

</body></html>