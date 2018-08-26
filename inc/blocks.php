<?php
/******************************************************************************
	Original By https://github.com/stolendata/rpc-ace 
	Tuned by http://www.blockpioneers.info - Bitcointalk : BanzaiBTC
******************************************************************************/

if (isset($ace['blocks']))
{
	// Block List
?>
		<center>
			<table class="table table-bordered<?php echo (USE_DARK_DATA_TABLE ? " table-dark" : ""); ?>" style="table-layout: fixed; width: 95%;">
				<colgroup>
					<col span="1" style="width: 10%;">
					<col span="1" style="width: 20%;">
					<col span="1" style="width: 10%;">
					<col span="1" style="width: 20%;">
					<col span="1" style="width: 40%;">
				</colgroup>
				<thead>
					<tr>
						<th style="text-align:center;vertical-align:middle;word-wrap:break-word;">Block</td>
						<th style="text-align:center;vertical-align:middle;word-wrap:break-word;">Date</td>
						<th style="text-align:center;vertical-align:middle;word-wrap:break-word;">TXs</td>
						<th style="text-align:center;vertical-align:middle;word-wrap:break-word;">Total Value Out</td>
						<th style="text-align:center;vertical-align:middle;word-wrap:break-word;">Hash</td>
					</tr>
				</thead>
				<tbody style="font-size: 9pt">
<?php
	foreach($ace['blocks'] as $block)
	{
?>
					<tr>
						<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?block_height=<?php echo $block['height']; ?>"><?php echo $block['height']; ?></a></td>
						<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><p title="<?php echo $block['time']; ?>"><?php echo $block['date']; ?></p></td>
						<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $block['tx_count']; ?></td>
						<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo sprintf('%.8f', $block['total_out']); ?></td>
						<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?block_hash=<?php echo $block['hash']; ?>"><?php echo $block['hash']; ?></a></td>
					</tr>
<?php
	}
	
	$pagecnt = intval(ceil($ace['num_blocks'] / BLOCKSPERLIST));
	
	$newer = ($query > 1 ? ('<a href="?page=' . ($query - 1) . '">&lt; Newer</a>') : '&lt; Newer');
	$older = ($query < $pagecnt ? ('<a href="?page=' . ($query + 1) . '">Older &gt;</a>') : 'Older &gt;');
?>
					<tr>
						<td colspan="3" align="left"><?php echo $newer; ?></td>
						<td colspan="2" align="right"><?php echo $older; ?></td>
					</tr>
				</tbody>
			</table>
		</center>
<?php
} else
if (isset($ace['transactions']))
{
	// Block Details
?>
	<center>
		<table class="table table-bordered<?php echo (USE_DARK_DATA_TABLE ? " table-dark" : ""); ?>" style="table-layout: fixed; width: 95%;">
			<tbody style="font-size: 9pt">
<?php
	foreach($ace['fields'] as $field => $val)
	{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $field; ?></td>
<?php
		if ($field == 'previousblockhash' || $field == 'nextblockhash')
		{
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?block_hash=<?php echo $val; ?>"><?php echo $val; ?></a></td>
<?php
		} else
		if ($field == 'time')
		{
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo date("Y-m-d H:i:s", $val); ?></td>
<?php
		} else {
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $val; ?></td>
<?php
		}
?>
				</tr>
<?php
	}
	
	$i = 0;
	
	foreach($ace['transactions'] as $tx)
	{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx #<?php echo $i; ?></td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?transaction=<?php echo $tx['id']; ?>"><?php echo $tx['id']; ?></a></td>
				</tr>
<?php
		foreach($tx['outputs'] as $output)
		{
?>
				<tr>
					<td></td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $output['value'] . (isset($tx['coinbase']) ? "*" : "") . " -&gt; " . $output['address']; ?></td>
				</tr>
<?php
		}
		
		$i++;
	}
?>
			</tbody>
		</table>
	</center>
<?php
} else
if (isset($ace['txdetails']))
{
	// Transaction Details
?>
	<center>
		<table class="table table-bordered<?php echo (USE_DARK_DATA_TABLE ? " table-dark" : ""); ?>" style="table-layout: fixed; width: 95%;">
			<tbody style="font-size: 9pt">
<?php
	$txvin = $txvout = [];
	
	if (isset($ace['txdetails']['vin']))
	{
		$txvin = $ace['txdetails']['vin'];
		unset($ace['txdetails']['vin']);
	}
	
	if (isset($ace['txdetails']['vout']))
	{
		$txvout = $ace['txdetails']['vout'];
		unset($ace['txdetails']['vout']);
	}
	
	foreach($ace['txdetails'] as $field => $val)
	{
		if ($field == 'hex' || $field == 'blocktime' || $field == 'locktime') continue;
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $field; ?></td>
<?php
		
		if ($field == 'blockhash')
		{
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?block_hash=<?php echo $val; ?>"><?php echo $val; ?></a></td>
<?php
		} else
		if ($field == 'time')
		{
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo date("Y-m-d H:i:s", $val); ?></td>
<?php
		} else {
?>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $val; ?></td>
<?php
		}
?>
				</tr>
<?php
	}
	
	if (count($txvin))
	{
		foreach($txvin as $vinfield => $val)
		{
?>
				<tr>
					<td colspan="2" style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx input #<?php echo $vinfield; ?></td>
				</tr>
<?php
			if (isset($val['txid']))
			{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx id</td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><a href="?transaction=<?php echo $val['txid']; ?>"><?php echo $val['txid']; ?></a></td>
				</tr>
<?php
			}
			
			if (isset($val['vout']))
			{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx output</td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $val['vout']; ?></td>
				</tr>
<?php
			}
			
			if (isset($val['coinbase']))
			{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx coinbase</td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $val['coinbase']; ?></td>
				</tr>
<?php
			}
			
			//if (isset($val['sequence'])) echo "<tr>\n\t\t\t\t\t<td class=\"key\">tx sequence</td>\n\t\t\t\t\t<td class=\"value\">" . $val['sequence'] . "</td></tr>\n\t\t\t\t";
		}
	}
	
	if (count($txvout))
	{
		foreach($txvout as $voutfield => $val)
		{
?>
				<tr>
					<td colspan="2" style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx output #<?php echo $voutfield; ?></td>
				</tr>
<?php
			if (isset($val['value']))
			{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">tx value</td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $val['value']; ?></td>
				</tr>
<?php
			}
			
			//if (isset($val['scriptPubKey']['type'])) echo "<tr>\n\t\t\t\t\t<td class=\"key\">tx type</td>\n\t\t\t\t\t<td class=\"value\">" . $val['scriptPubKey']['type'] . "</td></tr>\n\t\t\t\t";
			
			if (isset($val['scriptPubKey']['addresses']))
			{
				foreach($val['scriptPubKey']['addresses'] as $addressNum => $address)
				{
?>
				<tr>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;">address #<?php echo $addressNum; ?></td>
					<td style="text-align:center;vertical-align:middle;word-wrap:break-word;"><?php echo $address; ?></td>
				</tr>
<?php
				}
			}
		}
	}
?>
			</tbody>
		</table>
	</center>
<?php
}
?>
		<footer>
			<div style="float: right !important;">
				<img src="./css/footer.png" height="50" width="50" alt="Merge Logo">
				<strong>POWERED BY MERGE</strong>&nbsp;&nbsp;&nbsp;&nbsp;
				<br>
				<br>
			</div>
		</footer>
		<script>
			$(document).ready(function(){
				$('a').css('color', '<?php echo COIN_STATS_TABLE_COLOR_1; ?>');
			});
		</script>
	</body>
</html>
