<?php 
	session_start();
	if (!isset($_SESSION["loket_client"])) 
	{
		$_SESSION["loket_client"] = 0;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <meta name="description" content="">
	    <meta name="author" content="">
	    <title>Client : Queue</title>
	    <link href="../dashboard.css" rel="stylesheet">
	    <link href="../css/bootstrap.min.css" rel="stylesheet">
	    <link href="/assert/css/jumbotron-narrow.css" rel="stylesheet">
	    <link rel="stylesheet" href="../font-awesome-4.7.0/css/font-awesome.min.css">
		<script src="../assert/js/jquery.min.js"></script>
	</head>
  	<body>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="#">Sistem Bank</a>



    </nav>

<div class="card text-center">
  <div class="card-header">
    Sistem Bank
  </div>
  <div class="card-body">
    <h4 class="card-title">Client Antrian</h4>


    <div class="container">
        <button class="btn btn-small btn-danger try_queue" type="button" style="float:right;padding:20px;">
            Ulangi Panggilan &nbsp;<span class="glyphicon glyphicon-volume-up"></span>    
        </button>
        <div class="jumbotron">
        <h1 class="counter">
        	0
        </h1>
        <p>
	        <a class="btn btn-lg btn-primary next_queue" href="#" role="button">
	        	Next &nbsp;<span class="fa fa-chevron-circle-right"></span>
	        </a>
        </p>
      	</div>
    	<form>
        	<label for="exampleInputEmail1" style="text-align: left;"><span class="glyphicon glyphicon-modal-window">&nbsp;</span>NOMOR LOKET</label> 
        	<select class="form-control loket" name="loket" required>
        		<option value="0">-PILIH NOMOR LOKET-</option>
			</select>

			<p>Silakan Screen Shoot No Antrian Anda</p>

        	<br/>
        	<div class="alert alert-danger peringatan" role="alert">
        		<strong>WARNING !!</strong>  Masukan Nomor Loket Anda.
        	</div>
    	</form>
      	<footer class="footer">
        <p><?php
        date_default_timezone_set('Asia/Jakarta');
         echo date("Y-m-d H:i:s");?></p>
      	</footer>
    </div>
</div>
</div>
</main>
  	</body>
  	<script type="text/javascript">
	$("document").ready(function()
	{
		// LIST LOKET
		$.post("../apps/admin_init.php", function( data ){
			for (var i = 1; i <= data['client']; i++) { 					
				if ( i == <?php echo $_SESSION["loket_client"];?>)
				$('.loket').append('<option value="'+i+'" selected>'+i+'</option>');
				else
				$('.loket').append('<option value="'+i+'">'+i+'</option>');
			}
		}, "json"); 

		// SET EXSIST session LOKET
		<?php if ($_SESSION["loket_client"] != 0) { ?>
		    	$(".peringatan").hide();
		<?php } else {?>
		    	$(".peringatan").show();
		<?php } ?>
		
		// GET LAST COUNTER
		var data = {"loket": <?php echo $_SESSION["loket_client"];?>};
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "../apps/last_stage.php",//request
			data: data,
			success: function(data) {
				$(".jumbotron h1").html(data["next"]);
			}
		});

		// NUMBER LOKET
	    $('form select').data('val',  $('form select').val() );
	    $('form select').change(function() {
	    	//set seassion or save
	    	var data = {"loket": $(".loket").val()};
	    	if ( $(".loket").val() != 0 ) {
	    		$(".peringatan").hide();
	    	}else{
	    		$(".peringatan").show();
	    	}
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "../apps/set_loket.php",//request
				data: data,
				success: function(data) {
					$(".jumbotron h1").html(data["next"]);
				}
			});
	    });
	    $('form select').keyup(function() {
	        if( $('form select').val() != $('form select').data('val') ){
	            $('form select').data('val',  $('form select').val() );
	            $(this).change();
	        }
	    });

	    // GET NEXT COUNTER
		$(".next_queue").click(function()
		{
			var loket = $(".loket").val();
			if (loket==0) {
				$(".peringatan").show();
			}else{
				var data = {"loket" : loket};
				$.ajax({
					type: "POST",
					dataType: "json",
					url: "../apps/daemon.php",//request
					data: data,
					success: function(data) {
						$(".jumbotron h1").html(data["next"]);
						if (data["idle"]=="TRUE") {
							$(".next_queue").hide();
							clearInterval(timerId_adik);
							adik_adudu(loket, data["next"]);
						}
					}
				});
				return false;
			}
			
		});

		var timerId=0;
		// ADUDU
		function adudu(loket, counter){
			timerId = setInterval(function() {
				 $.post("../apps/daemon_try_cek.php", { loket : loket, counter : counter }, function(msg){
					if(msg.huft == 2){
						$(".try_queue").show();
					}
				},'JSON');
			}, 1000);
		 }
		
		var timerId_adik=0;
		// ADIK_ADUDU
		function adik_adudu(loket, counter){
			timerId_adik = setInterval(function() {
				 $.post("../apps/daemon_cek.php", { loket : loket, counter : counter }, function(msg){
					if(msg.huft == 2){
						$(".next_queue").show();
					}
				},'JSON');
			}, 1000);
		}

		// TRY CALL
		$(".try_queue").click(function(){
			var loket = $(".loket").val();
			if (loket==0) {
	    		$(".peringatan").show();
			}else{
				var counter = $(".counter").text();
				$.post("../apps/daemon_try.php", { loket : loket, counter : counter }, function(msg){
					if(msg.huft == 0){
						$(".try_queue").hide();
						clearInterval(timerId);
						adudu(loket, counter);
					}
				},'JSON'); //request
				return false;
			}
		});	
		
	});
	</script>
</html>

