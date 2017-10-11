<?php
	
	function set_user_default_notification_rules($user_id)
	{
		global $db2, $network;
		$rules	= array (
			// 0 - off, 1 - on
			'ntf_them_if_i_follow_usr'	=> 1,
			'ntf_them_if_i_comment'		=> 1,
			'ntf_them_if_i_edt_profl'	=> 1,
			'ntf_them_if_i_edt_pictr'	=> 1,
			'ntf_them_if_i_create_grp'	=> 1,
			'ntf_them_if_i_join_grp'	=> 1,
			
			// 0 - off, 2 - post, 3 - email, 1 - both
			'ntf_me_if_u_follows_me'	=> 3,
			'ntf_me_if_u_follows_u2'	=> 2,
			'ntf_me_if_u_commments_me'	=> 3,
			'ntf_me_if_u_commments_m2'	=> 3,
			'ntf_me_if_u_edt_profl'		=> 2,
			'ntf_me_if_u_edt_pictr'		=> 2,
			'ntf_me_if_u_creates_grp'	=> 2,
			'ntf_me_if_u_joins_grp'		=> 2,
			'ntf_me_if_u_invit_me_grp'	=> 1,
			'ntf_me_if_u_posts_qme'		=> 3,
			'ntf_me_if_u_posts_prvmsg'	=> 3,
			'ntf_me_if_u_registers'		=> 0,
		);
		$in_sql	= array();
		$in_sql[]	= '`user_id`="'.$user_id.'"';
		foreach($rules as $k=>$v) {
			$in_sql[]	= '`'.$k.'`="'.$v.'"';
		}
		$in_sql	= implode(', ', $in_sql);
		$db2->query('REPLACE INTO users_notif_rules SET '.$in_sql);
	}
	
	function user_try_login_in_ldap($user, $pass, $rememberme_if_valid=FALSE)
	{
		global $C;
		if( ! function_exists('ldap_connect') ) {
			return FALSE;
		}
		if( !isset($C->LDAP_ON, $C->LDAP_INFO) || !$C->LDAP_ON  || !$C->LDAP_INFO ) {
			return FALSE;
		}
		$C->LDAP_INFO	= @unserialize($C->LDAP_INFO);
		$users		= array();
		$c	= @ldap_connect($C->LDAP_INFO->host, $C->LDAP_INFO->port);
		if( $c ) {
			@ldap_set_option($c, LDAP_OPT_REFERRALS, 0);
			@ldap_set_option($c, LDAP_OPT_PROTOCOL_VERSION, $C->LDAP_INFO->ver);
			$b	= @ldap_bind($c, $C->LDAP_INFO->user, $C->LDAP_INFO->pass);
			if( $b ) {
				$u	= preg_replace('/[\*\(\)\\\\]/iu', '', $user);
				$s	= @ldap_search($c, $C->LDAP_INFO->dn, '(&(objectClass=organizationalPerson)(|(username='.$u.')(cn='.$u.')(userPrincipalName='.$u.')(mail='.$u.')))');
				if( $s ) {
					$i	= @ldap_get_entries($c, $s);
					if( $i && $i['count']>0 ) {
						for($j=0; $j<$i['count']; $j++) {
							$users[]	= $i[$j];
						}
					}
				}
			}
		}
		if( ! count($users) ) {
			@ldap_unbind($c);
			return FALSE;
		}
		$info	= FALSE;
		foreach($users as $u) {
			if( ! isset($u['distinguishedname'][0]) ) { continue; }
			$b	= @ldap_bind($c, $u['distinguishedname'][0], $pass);
			if( $b ) {
				$info	= (object) array (
					'username'	=> '',
					'password'	=> $pass,
					'email'	=> '',
					'fullname'	=> '',
					'location'	=> '',
					'phone'	=> '',
				);
				foreach(array('name','cn') as $tmp) {
					if( ! isset($u[$tmp]['count']) ) { continue; }
					for($j=0; $j<$u[$tmp]['count']; $j++) {
						$t	= trim($u[$tmp][$j]);
						if( !empty($t) && !preg_match('/[^a-z0-9-\s]/iu',$t) ) {
							$t	= trim(preg_replace('/\s+/', '', $t));
							if( strlen($t) >= 3 ) {
								$info->username	= strtolower($t);
								break;
							}
						}
					}
				}
				foreach(array('displayName','name','cn') as $tmp) {
					if( ! isset($u[$tmp]['count']) ) { continue; }
					for($j=0; $j<$u[$tmp]['count']; $j++) {
						$t	= trim($u[$tmp][$j]);
						if( !empty($t) ) {
							$info->fullname	= $t;
							break;
						}
					}
				}
				foreach(array('mail') as $tmp) {
					if( ! isset($u[$tmp]['count']) ) { continue; }
					for($j=0; $j<$u[$tmp]['count']; $j++) {
						$t	= trim($u[$tmp][$j]);
						if( is_valid_email($t) ) {
							$info->email = $t;
							break;
						}
					}
				}
				foreach(array('workPhone','homePhone','mobile') as $tmp) {
					if( ! isset($u[$tmp]['count']) ) { continue; }
					for($j=0; $j<$u[$tmp]['count']; $j++) {
						$t	= trim($u[$tmp][$j]);
						if( !empty($t) ) {
							$info->phone = $t;
							break;
						}
					}
				}
				foreach(array('l','c','st') as $tmp) {
					if( ! isset($u[$tmp]['count']) ) { continue; }
					for($j=0; $j<$u[$tmp]['count']; $j++) {
						$t	= trim($u[$tmp][$j]);
						if( !empty($t) ) {
							$info->location = $t;
							break;
						}
					}
				}
			}
		}
		@ldap_unbind($c);
		if( ! $info ) {
			return FALSE;
		}
		global $user;
		if( ! empty($info->username) ) {
			$tmp	= $user->login($info->username, md5($info->password), $rememberme_if_valid);
			if( $tmp ) {
				return TRUE;
			}
		}
		if( ! empty($info->email) ) {
			$tmp	= $user->login($info->email, md5($info->password), $rememberme_if_valid);
			if( $tmp ) {
				return TRUE;
			}
		}
		return $info;
	}
	
?>