<?php

/******************************************************************************
	Original By https://github.com/stolendata/rpc-ace 
	Tuned by http://www.blockpioneers.info - Bitcointalk : BanzaiBTC
******************************************************************************/

require_once( './config/config.php' );
require_once( './config/easybitcoin.php' );

class RPCAce
{
    private static $blockFields = [ 'hash', 'nextblockhash', 'previousblockhash', 'confirmations', 'size', 'height', 'version', 'merkleroot', 'time', 'nonce', 'bits', 'difficulty', 'mint', 'proofhash' ];

    private static function base()
    {
        $rpc = new Bitcoin( RPCUSER, RPCPASS, RPCHOST, RPCPORT );
        $info = $rpc->getinfo();
        if( $rpc->status !== 200 && $rpc->error !== '' )
            return [ 'err'=>'failed to connect - node not reachable, or user/pass incorrect' ];

        $output['rpcace_version'] = ACEVERSION;
        $output['coin_name'] = COINNAME;
        $output['num_blocks'] = $info['blocks'];
        $output['num_connections'] = $info['connections'];
		$output['version'] = $info['version'];
		$output['protocol'] = $info['protocolversion'];
		$output['moneysupply'] = $info['moneysupply'];

        if( COINPOS === true )
        {
            $output['current_difficulty_pow'] = $info['difficulty']['proof-of-work'];
            $output['current_difficulty_pos'] = $info['difficulty']['proof-of-stake'];
        }
        else
            $output['current_difficulty_pow'] = $info['difficulty'];

        if( !($hashRate = @$rpc->getmininginfo()['netmhashps']) && !($hashRate = @$rpc->getmininginfo()['networkhashps'] / 1000000) )
            $hashRate = $rpc->getnetworkhashps() / 1000000;
        $output['hashrate_mhps'] = sprintf( '%.2f', $hashRate );

		$mining = $rpc->getmininginfo();
		
        $output['stakeweight'] = $mining['netstakeweight'];
		
        return [ 'output'=>$output, 'rpc'=>$rpc ];
    }

    // enumerate block details from hash
    public static function getBlock( $hash )
    {
        if( preg_match('/^([[:xdigit:]]{64})$/', $hash) !== 1 )
            return RETURNJSON ? json_encode( ['err'=>'not a valid block hash'] ) : [ 'err'=>'not a valid block hash' ];

        $base = self::base();
        if( isset($base['err']) )
            return RETURNJSON ? json_encode( $base ) : $base;

        if( ($block = $base['rpc']->getblock($hash)) === false )
            return RETURNJSON ? json_encode( ['err'=>'no block with that hash'] ) : [ 'err'=>'no block with that hash' ];

        $total = 0;
        foreach( $block as $id => $val )
            if( $id === 'tx' )
                foreach( $val as $txid )
                {
                    $transaction = array();
                    $transaction['id'] = $txid;
                    if( ($tx = $base['rpc']->getrawtransaction($txid, 1)) === false )
                        continue;

                    if( isset($tx['vin'][0]['coinbase']) )
                        $transaction['coinbase'] = true;

                    foreach( $tx['vout'] as $entry )
                        if( $entry['value'] > 0.0 )
                        {
                            // nasty number formatting trick that hurts my soul, but it has to be done...
                            $total += ( $transaction['outputs'][$entry['n']]['value'] = rtrim(rtrim(sprintf('%.8f', $entry['value']), '0'), '.') );
                            $transaction['outputs'][$entry['n']]['address'] = $entry['scriptPubKey']['addresses'][0];
                        }
                    $base['output']['transactions'][] = $transaction;
                }
            elseif( in_array($id, self::$blockFields) )
                $base['output']['fields'][$id] = $val;

        $base['output']['total_out'] = $total;
        $base['rpc'] = null;
        return RETURNJSON ? json_encode( $base['output'] ) : $base['output'];
    }
	
	// enumerate block details from height
	public static function getBlockFromHeight( $height )
	{
        if( preg_match('/^[0-9]+$/s', $height) !== 1 )
            return RETURNJSON ? json_encode( ['err'=>'not a valid block height'] ) : [ 'err'=>'not a valid block height' ];

        $base = self::base();
        if( isset($base['err']) )
            return RETURNJSON ? json_encode( $base ) : $base;
		
        if( ($hash = $base['rpc']->getblockhash($height)) === false )
            return RETURNJSON ? json_encode( ['err'=>'no block with that height'] ) : [ 'err'=>'no block with that height' ];
		
		$base['rpc'] = null;
		
		return self::getBlock($hash);
	}
	
    // enumerate transaction details from id
    public static function getTransaction( $id )
    {
        if( preg_match('/^([[:xdigit:]]{64})$/', $id) !== 1 )
            return RETURNJSON ? json_encode( ['err'=>'not a valid transaction id'] ) : [ 'err'=>'not a valid transaction id' ];

        $base = self::base();
        if( isset($base['err']) )
            return RETURNJSON ? json_encode( $base ) : $base;

        if( ($transaction = $base['rpc']->getrawtransaction($id, 1)) === false )
            return RETURNJSON ? json_encode( ['err'=>'no transaction with that id'] ) : [ 'err'=>'no transaction with that id' ];
		
		$base['output']['txdetails'] = $transaction;
        $base['rpc'] = null;
		
        return RETURNJSON ? json_encode( $base['output'] ) : $base['output'];
    }

    // create summarized list from block number
    public static function getBlockList( $ofs, $n = BLOCKSPERLIST )
    {
        $base = self::base();
        if( isset($base['err']) )
            return RETURNJSON ? json_encode( $base ) : $base;
		
		$offset = (!is_numeric($ofs) ? $base['output']['num_blocks'] : ($base['output']['num_blocks'] - (BLOCKSPERLIST * (abs((int)$ofs) - 1))));
        if( $offset > $base['output']['num_blocks'] )
            return RETURNJSON ? json_encode( ['err'=>'block does not exist'] ) : [ 'err'=>'block does not exist' ];

        $i = $offset;
        while( $i >= 0 && $n-- )
        {
            $frame = array();
            $frame['hash'] = $base['rpc']->getblockhash( $i );
            $block = $base['rpc']->getblock( $frame['hash'] );
            $frame['height'] = $block['height'];
			$frame['mint'] = $block['mint'];
            $frame['difficulty'] = $block['difficulty'];
			$frame['flags'] = $block['flags'];
            $frame['time'] = $block['time'];
            $frame['date'] = gmdate( DATEFORMAT, $block['time'] );

            $txCount = 0;
            $valueOut = 0;
            foreach( $block['tx'] as $txid )
            {
                $txCount++;
                if( ($tx = $base['rpc']->getrawtransaction($txid, 1)) === false )
                    continue;
                foreach( $tx['vout'] as $vout )
                    $valueOut += $vout['value'];
            }
            $frame['tx_count'] = $txCount;
            $frame['total_out'] = $valueOut;

            $base['output']['blocks'][] = $frame;
            $i--;
			
        }

        $base['rpc'] = null;
        return RETURNJSON ? json_encode( $base['output'] ) : $base['output'];
    }
}


?>