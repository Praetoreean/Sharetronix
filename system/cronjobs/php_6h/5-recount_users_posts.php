<?php
	
	$public_groups	= array(0);
	$db2->query('SELECT id FROM groups WHERE is_public=1');
	while($obj = $db2->fetch_object()) {
		$public_groups[]	= $obj->id;
	}
	$public_groups	= implode(', ', $public_groups);
	
	$res	= $db2->query('SELECT user_id, COUNT(id) AS nm FROM posts WHERE user_id<>0 AND api_id<>2 AND group_id IN('.$public_groups.') GROUP BY user_id');
	while($tmp = $db2->fetch_object($res)) {
		$db2->query('UPDATE users SET num_posts="'.$tmp->nm.'" WHERE id="'.$tmp->user_id.'" LIMIT 1');
	}
	
	$res	= $db2->query('SELECT group_id, COUNT(id) AS nm FROM posts WHERE group_id<>0 AND (user_id<>0 OR api_id=2) GROUP BY group_id');
	while($tmp = $db2->fetch_object($res)) {
		$db2->query('UPDATE groups SET num_posts="'.$tmp->nm.'" WHERE id="'.$tmp->group_id.'" LIMIT 1');
	}
	
?>