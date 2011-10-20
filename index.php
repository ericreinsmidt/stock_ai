<!DOCTYPE html>

<head>
<link rel="stylesheet" type="text/css" href="css/predictor.css" />
</head>

<body>

	<div id="wrapper">

		<div id="header">
			<h1>Stock Prediction Calculator</h1>
		</div>

		<div id="content">
			
			<div id="input">
				<form action="bayes.php" method="post">
					Stock symbol<br />
					<input type="text" name="stock" /><br />
					Symbol to test against<br />
					<input type="text" name="predictor" /><br />
					Choose testing year (1-5)<br />
					<input type="text" name="time" /><br />
					<input type="submit" value="Predict" />
				</form>
			</div>
			<br /><br /><br /><br />
		</div>

		<div id="footer">
			<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/3.0/80x15.png" /></a>
		</div>

	</div>

</body>

<html>