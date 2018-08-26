<?php
/*
   This is the example block explorer of RPC Ace. If you intend to use just
   the RPCAce class itself to fetch and process the array or JSON output on
   your own, you should remove this entire PHP section.
*/

if (isset($_GET['block_hash']) && preg_match('/^[0-9a-f]{64}$/is', $_GET['block_hash']))
{
	$query = $_GET['block_hash'];
	$ace = RPCAce::getBlock($query);
} else
if (isset($_GET['block_height']) && preg_match('/^[0-9]+$/s', $_GET['block_height']))
{
	$query = abs((int)$_GET['block_height']);
	$ace = RPCAce::getBlockFromHeight($query);
} else
if (isset($_GET['transaction']) && preg_match('/^[0-9a-f]{64}$/is', $_GET['transaction']))
{
	$query = $_GET['transaction'];
	$ace = RPCAce::getTransaction($query);
} else {
	if (isset($_GET['page']) && preg_match('/^[0-9]+$/s', $_GET['page']))
	{
		$query = $_GET['page'];
	} else {
		$query = NULL;
	}
	
	$ace = RPCAce::getBlockList($query, BLOCKSPERLIST);
	if (is_null($query)) $query = 1;
}

if (isset($ace['err']) || RETURNJSON === true) die('RPC Ace error: ' . (RETURNJSON ? $ace : $ace['err']));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
<?php
if (!count($_GET)) echo "<meta http-equiv=\"refresh\" content=\"" . REFRESHTIME . "; url=index.php\" />";
?>
		<title><?php echo ((isset($_GET['block_hash']) || isset($_GET['block_height'])) ? "Block Detail Page" : (isset($_GET['transaction']) ? "Transaction Detail Page" : "Block List")); ?></title>
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	</head>
	<body style="background-color:<?php echo BACKGROUND_COLOR; ?>;color:<?php echo TEXT_COLOR; ?>">
		<center><h1><?php echo COINNAME; ?> Block Explorer</h1></center>
		<center>
			<table class="table table-bordered" style="table-layout: fixed; width: 95%;">
				<colgroup>
					<col span="1" style="width: 15%;">
					<col span="1" style="width: 15%;">
					<col span="1" style="width: 40%;">
					<col span="1" style="width: 15%;">
					<col span="1" style="width: 15%;">
				</colgroup>
				<tbody style="font-size: 9pt">
					<tr>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_1; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><strong>Connections</strong></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_1; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><strong>Version</strong></td>
						<td rowspan="2" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><img src="./css/logo.png" height="200" width="200" alt="Merge Logo"></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_1; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><strong>Total Coins</strong></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_1; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><strong>Protocol</strong></td>
					</tr>
					<tr>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_2; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $ace['num_connections']; ?></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_2; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $ace['version']; ?></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_2; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo sprintf('%.8f', $ace['moneysupply']); ?></td>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_2; ?>" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $ace['protocol']; ?></td>
					</tr>
					<tr>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_1; ?>" colspan="5" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><strong>Total Blocks</strong></td>
					</tr>
					<tr>
						<td bgcolor="<?php echo COIN_STATS_TABLE_COLOR_2; ?>" colspan="5" style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $ace['num_blocks']; ?></td>
					</tr>
				</tbody>
			</table>
		</center>
		<hr>
<?php
if (isset($_GET['block_hash']) || isset($_GET['block_height']) || isset($_GET['transaction']))
{
?>
		<center>
			<h5>
				<form action="index.php">
					<input type="submit" value="Go back home" />
				</form>
			</h5>
		</center>
<?php
} else {
?>
		<center>
			<h5>
				<div class="menu_item">
					<span class="menu_desc">Enter a Block Index / Height</span><br>
					<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
						<input type="text" name="block_height" size="40">
						<input type="submit" value="Jump to Block">
					</form>
				</div>
				<br>
				<div class="menu_item">
					<span class="menu_desc">Enter a Block Hash</span><br>
					<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
						<input type="text" name="block_hash" size="40">
						<input type="submit" value="Jump to Block">
					</form>
				</div>
				<br>
				<div class="menu_item">
					<span class="menu_desc">Enter a Transaction ID</span><br>
					<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
						<input type="text" name="transaction" size="40">
						<input type="submit" value="Jump to TX">
					</form>
				</div>
			</h5>
		</center>
<?php
}
?>

		<hr>
