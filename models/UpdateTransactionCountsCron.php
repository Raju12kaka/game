<?php

/** Database configuration **/
include_once ('common.php');

/**
 *  
 * params @$argv[1] comes from cron job set up url
 * based on parameter the counts will update to zero
 * 
 */
switch($argv[1]){
	case 'hour':
		$UPDATE_PLAYER_QUERY = "UPDATE counts_per_player set hour = 0";
		update_player_transaction_counts($UPDATE_PLAYER_QUERY, 'hour', 'player');
		$UPDATE_CARD_QUERY = "UPDATE counts_per_card set hour = 0";
		update_player_transaction_counts($UPDATE_CARD_QUERY, 'hour', 'card');
	break;
	
	case 'day':
		$UPDATE_PLAYER_QUERY = "UPDATE counts_per_player set day = 0";
		update_player_transaction_counts($UPDATE_PLAYER_QUERY, 'day', 'player');
		$UPDATE_CARD_QUERY = "UPDATE counts_per_card set day = 0";
		update_player_transaction_counts($UPDATE_CARD_QUERY, 'day', 'card');
	break;
	
	case 'week':
		$UPDATE_PLAYER_QUERY = "UPDATE counts_per_player set week = 0";
		update_player_transaction_counts($UPDATE_PLAYER_QUERY, 'week', 'player');
		$UPDATE_CARD_QUERY = "UPDATE counts_per_card set week = 0";
		update_player_transaction_counts($UPDATE_CARD_QUERY, 'week', 'card');
	break;
	
	case 'month':
		$UPDATE_PLAYER_QUERY = "UPDATE counts_per_player set month = 0";
		update_player_transaction_counts($UPDATE_PLAYER_QUERY, 'month', 'player');
		$UPDATE_CARD_QUERY = "UPDATE counts_per_card set month = 0";
		update_player_transaction_counts($UPDATE_CARD_QUERY, 'month', 'card');
	break;
	
	case 'always':
		$UPDATE_PLAYER_QUERY = "UPDATE counts_per_player set always = 0";
		update_player_transaction_counts($UPDATE_PLAYER_QUERY, 'always', 'player');
		$UPDATE_CARD_QUERY = "UPDATE counts_per_card set always = 0";
		update_player_transaction_counts($UPDATE_CARD_QUERY, 'always', 'card');
	break;
}

?>
