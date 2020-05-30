<?php
/**
 * Created by PhpStorm.
 * User: hurtige
 * Date: 7/31/2014
 * Time: 1:39 PM
 */


?>
<!DOCTYPE html>
<html>
<head>
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script type="text/javascript">
		alert('init');

		jQuery(function ($) {
			var keyOut;
			$('#upc').focus();
			$('#upc').keyup(function () {
				var barcode = $(this).val();
				$('#result').append(barcode + '<br />')
				clearTimeout(keyOut);
				keyOut = setTimeout(function () {
					$('#result').append('GET http://www.outpan.com/api/get_product.php?barcode=' + barcode + '<br />')

					$.get('http://www.outpan.com/api/get_product.php?barcode=' + barcode, function (msg) {
						alert(msg);
						$('#result').append(msg);
					});
					$('#upc').val('');
				}, 200)

			});
		})
	</script>
</head>
<body>

<form>
	<label>
		Barcode

		<input id="upc" type="text" />
	</label>
	<code id="result">

	</code>
</form>

</body>
</html>
