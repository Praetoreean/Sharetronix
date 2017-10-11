<?php
	
	$this->load_template('header.php');
	
?>
					<div id="settings">
						<div id="settings_left">
							<?php $this->load_template('admin_leftmenu.php') ?>
						</div>
						<div id="settings_right">
							<div class="ttl">
								<div class="ttl2">
									<h3><?= $this->lang('admtitle_ldap') ?></h3>
								</div>
							</div>
							<script type="text/javascript">
								function ldapform() {
									d.getElementById("ldapform").style.display	= d.lf.enable.checked ? "block" : "none";
									d.getElementById("ldapsbm2").style.display	= d.lf.enable.checked ? "none" : "block";
								}
							</script>
							<div class="greygrad" style="margin-top:5px;">
								<div class="greygrad2">
									<div class="greygrad3">
										<?= $this->lang('admldap_descr') ?>
										<?php if( ! $D->php_error ) { ?>
											<?php if($D->error) { ?>
											<?= errorbox($this->lang('admldap_ferror'), $this->lang($D->errmsg), TRUE, 'margin-top:10px;') ?>
											<?php } elseif($D->submit) { ?>
											<?= okbox($this->lang('admldap_okay'), $this->lang($D->LDAP_ON?'admldap_okay_txt':'admldap_okay_txt2'), TRUE, 'margin-top:10px;') ?>
											<?php } ?>
											<form name="lf" method="post" action="">
												<div style="margin-top:10px; margin-bottom:10px;">
													<label><input type="checkbox" onclick="ldapform();" onchange="ldapform();" onfocus="this.blur();" name="enable" value="1" <?= $D->LDAP_ON?'checked="checked"':'' ?> /> <span><?= $this->lang('admldap_f_on') ?></span></label>
													<input type="submit" id="ldapsbm2" name="sbm" value="<?= $this->lang('admldap_fsbm') ?>" style="display:none; clear:both; margin-top:5px; padding:4px; font-weight:bold;" />
												</div>
												<table id="setform" cellspacing="5">
													<tbody id="ldapform" style="<?= $D->LDAP_ON ? '' : 'display:none;' ?>" >
														<tr>
															<td class="setparam"><?= $this->lang('admldap_f_host') ?></td>
															<td><input type="text" name="ldap_host" value="<?= htmlspecialchars($D->LDAP_INFO->host) ?>" class="setinp" maxlength="200" /></td>
														</tr>
														<tr>
															<td class="setparam" valign="top"><?= $this->lang('admldap_f_port') ?></td>
															<td>
																<input type="text" name="ldap_port" value="<?= htmlspecialchars($D->LDAP_INFO->port) ?>" class="setinp" maxlength="10" />
																<br /><small style="color:#888;"><?= $this->lang('admldap_fd_port') ?></small>
															</td>
														</tr>
														<tr>
															<td class="setparam"><?= $this->lang('admldap_f_ver') ?></td>
															<td><select name="ldap_ver" class="setselect">
																<option value="3" <?= $D->LDAP_INFO->ver==3?'selected="selected"':'' ?>>3</option>
																<option value="2" <?= $D->LDAP_INFO->ver==2?'selected="selected"':'' ?>>2</option>
															</select></td>
														</tr>
														<tr>
															<td class="setparam" valign="top"><?= $this->lang('admldap_f_dn') ?></td>
															<td>
																<input type="text" name="ldap_dn" value="<?= htmlspecialchars($D->LDAP_INFO->dn) ?>" class="setinp" maxlength="200" />
																<br /><small style="color:#888;"><?= $this->lang('admldap_fd_dn') ?></small>
															</td>
														</tr>
														<tr>
															<td class="setparam" valign="top"><?= $this->lang('admldap_f_user') ?></td>
															<td>
																<input type="text" name="ldap_user" value="<?= htmlspecialchars($D->LDAP_INFO->user) ?>" class="setinp" maxlength="200" />
																<br /><small style="color:#888;"><?= $this->lang('admldap_fd_user') ?></small>
															</td>
														</tr>
														<tr>
															<td class="setparam"><?= $this->lang('admldap_f_pass') ?></td>
															<td><input type="password" name="ldap_pass" autocomplete="off" value="<?= htmlspecialchars($D->LDAP_INFO->pass) ?>" class="setinp" maxlength="200" /></td>
														</tr>
														<tr>
															<td></td>
															<td><input type="submit" name="sbm" value="<?= $this->lang('admldap_fsbm') ?>" style="padding:4px; font-weight:bold;" /></td>
														</tr>
													</tbody>
												</table>
											</form>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php if( $D->php_error ) { ?>
							<?= msgbox($this->lang('admldap_phperrorttl'), $this->lang('admldap_phperrormsg'), FALSE) ?>
							<?php } ?>
						</div>
					</div>
<?php
	
	$this->load_template('footer.php');
	
?>