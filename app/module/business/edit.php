<?php
	/*require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();*/

	if(!$app->userIsAdmin) header("Location: ./");

	# Data
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_REQUEST['id_cart'],
		'debug'		=> false
	));

	# Update
	if($_POST['update']){	
		
		$def['k_businesscart'] = array(
			'cartStatus'	=> array('value' => $_POST['cartStatus']),
			'cartSerial'	=> array('value' => $_POST['cartSerial'])
		);
		
		if($app->formValidation($def)){
			$app->dbQuery($app->dbUpdate($def)." WHERE id_cart=".$_POST['id_cart']);
		}	
		
		# EVENT TRIGGER
		$mailSent = $app->eventTrigger('business', 'businessCmdPaymentMail', array(
			'id_cart'		=> $_POST['id_cart'],
			'cartStatus'	=> $myCmd['cartStatus']
		));
		
		$reload = true;		
	}
	
	# Data
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_REQUEST['id_cart'],
		'debug'		=> false
	));

	
	if($_POST['mailTemplate'] != '' && !$mailSent){
		
		$message 	= file_get_contents(KROOT.'/user/mail/business/'.$_POST['mailTemplate']);
		$message	= $app->helperReplace($message, $myCmd);

		require_once(KROOT.'/app/plugin/phpmailer/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->SetFrom("noreply@".$_SERVER['HTTP_HOST']);
		$mail->AddReplyTo("noreply@".$_SERVER['HTTP_HOST']);
		$mail->AddAddress($myCmd['cartEmail']);
		$mail->Subject	= "[".$_SERVER['HTTP_HOST']."] Votre commande";
		$mail->AltBody	= "Pour voir ce message, merci d'utiliser un client compatible html";
		$body = eregi_replace("[\]",'', $message);
		$mail->MsgHTML($body);
		
	//	$app->pre($mail);
	//	die();
		
		if(!$mail->Send()) {
			die("Mailer Error: " .$mail->ErrorInfo);
		}
		 
		 
	}
	if ($reload) {
		header("Location: ./");
		exit(0);
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
    include(__DIR__.'/ui/menu.php');
    ?></header>



<?php //include('ressource/ui/menu.business.php'); ?>

<div class="app">

	<form action="edit" method="post">

	<input type="hidden" name="update" value="1" />
	<input type="hidden" name="id_cart" value="<?php echo $myCmd['id_cart'] ?>" />

	<div style="width:900px; margin:0 auto;">
		<h1>Edition de la commande #<?php echo $_REQUEST['id_cart'] ?></h1>

        <div class="clearfix"></div>

		<p>Statut : <select name="cartStatus"><?php
			foreach($app->apiLoad('business')->businessStatusGet() as $e){
				$sel = ($myCmd['cartStatus'] == $e) ? ' selected' : NULL;
				echo "<option value=\"".$e."\"".$sel.">".$e."</option>";
			}
		?></select></p>

		<p>Num&eacute;ro de suivi : <input type="text" name="cartSerial" value="<?php echo $myCmd['cartSerial'] ?>" /></p>

		<p>Mod&egrave;le de mail <select name="mailTemplate">
			<option value="">Ne pas envoyer de mail</option><?php
			$files = $app->fsFile(KROOT.'/user/mail/business/', 'business*.html');
			foreach($files as $e){
				$e = basename($e);
				echo "<option value=\"".$e."\">".$e."</option>";
			}
		?></select></p>

		<p><input type="submit" /></p>
	</div>
	</form>


</div>

<?php include(COREINC.'/end.php'); ?>
<script>
</script>


</body></html>