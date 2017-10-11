<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('signin');
	}
	$db2->query('SELECT 1 FROM users WHERE id="'.$this->user->id.'" AND is_network_admin=1 LIMIT 1');
	if( 0 == $db2->num_rows() ) {
		$this->redirect('dashboard');
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/admin.php');
	
	$D->page_title	= $this->lang('admpgtitle_ldap', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	
	$D->php_error	= FALSE;
	if( ! function_exists('ldap_connect') ) {
		$D->php_error	= TRUE;
		$this->load_template('admin_ldap.php');
		return;
	}
	
	$D->LDAP_ON		= FALSE;
	$D->LDAP_INFO	= (object) array(
		'host'	=> '',
		'port'	=> 389,
		'ver'		=> 3,
		'dn'		=> '',
		'user'	=> '',
		'pass'	=> '',
	);
	if( isset($C->LDAP_ON, $C->LDAP_INFO) ) {
		$D->LDAP_ON		= $C->LDAP_ON;
		$D->LDAP_INFO	= unserialize($C->LDAP_INFO);
	}
	
	$D->submit	= FALSE;
	$D->error	= FALSE;
	$D->errmsg	= '';
	if( isset($_POST['sbm']) ) {
		$D->submit	= TRUE;
		$D->LDAP_INFO->host	= isset($_POST['ldap_host']) ? trim($_POST['ldap_host']) : '';
		$D->LDAP_INFO->port	= isset($_POST['ldap_port']) ? intval($_POST['ldap_port']) : 389;
		$D->LDAP_INFO->ver	= isset($_POST['ldap_ver'])&&$_POST['ldap_ver']==2 ? 2 : 3;
		$D->LDAP_INFO->dn		= isset($_POST['ldap_dn']) ? trim($_POST['ldap_dn']) : '';
		$D->LDAP_INFO->user	= isset($_POST['ldap_user']) ? trim($_POST['ldap_user']) : '';
		$D->LDAP_INFO->pass	= isset($_POST['ldap_pass']) ? trim($_POST['ldap_pass']) : '';
		$D->LDAP_ON	= isset($_POST['enable'])&&intval($_POST['enable'])==1;
		if( ! $D->LDAP_ON ) {
			$db2->query('REPLACE INTO settings SET word="LDAP_ON", value="0" ');
			$db2->query('REPLACE INTO settings SET word="LDAP_INFO", value="'.$db2->e(serialize($D->LDAP_INFO)).'" ');
			$this->network->load_network_settings();
		}
		else {
			ini_set('max_execution_time', 120);
			$c	= FALSE;
  			if( empty($D->LDAP_INFO->host) || empty($D->LDAP_INFO->port) ) {
  				$D->error	= TRUE;
  				$D->errmsg	= 'admldap_ferr_fields';
  			}
  			if( ! $D->error ) {
  				@ldap_set_option($c, LDAP_OPT_TIMELIMIT, 15);
  				@ldap_set_option($c, LDAP_OPT_NETWORK_TIMEOUT, 15);
  				$c	= @ldap_connect($D->LDAP_INFO->host, $D->LDAP_INFO->port);
  				if( ! $c ) {
  					$D->error	= TRUE;
  					$D->errmsg	= 'admldap_ferr_host';
  				}
  			}
  			if( ! $D->error ) {
				@ldap_set_option($c, LDAP_OPT_REFERRALS, 0);
				@ldap_set_option($c, LDAP_OPT_PROTOCOL_VERSION, $D->LDAP_INFO->ver);
				if( empty($D->LDAP_INFO->user) && empty($D->LDAP_INFO->pass) ) {
					$b	= @ldap_bind($c);
				}
				else {
					$b	= @ldap_bind($c, $D->LDAP_INFO->user, $D->LDAP_INFO->pass);
				}
  				if( ! $b ) {
  					$D->error	= TRUE;
  					$D->errmsg	= 'admldap_ferr_bind';
  				}
  			}
  			if( ! $D->error ) {
  				@ldap_set_option($c, LDAP_OPT_TIMELIMIT, 90);
  				@ldap_set_option($c, LDAP_OPT_NETWORK_TIMEOUT, 90);
				$u	= preg_replace('/[\*\(\)\\\\]/iu', '', $D->LDAP_INFO->user);
  				$s	= @ldap_search($c, $D->LDAP_INFO->dn, '(&(objectClass=organizationalPerson)(distinguishedname='.$u.'))');
				if( ! $s ) {
					$D->error	= TRUE;
					$D->errmsg	= 'admldap_ferr_search';
				}
  			}
  			if( ! $D->error ) {
				$db2->query('REPLACE INTO settings SET word="LDAP_ON", value="1" ');
				$db2->query('REPLACE INTO settings SET word="LDAP_INFO", value="'.$db2->e(serialize($D->LDAP_INFO)).'" ');
				$this->network->load_network_settings();
  			}
  			if( $c ) {
  				@ldap_unbind($c);
			}
		}
	}
	
	$this->load_template('admin_ldap.php');
	
?>