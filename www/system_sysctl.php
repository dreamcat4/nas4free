<?php
/*
	system_sysctl.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies,
	either expressed or implied, of the NAS4Free Project.
*/
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("System"), gettext("Advanced"), gettext("sysctl.conf"));

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("sysctl", "sysctl_process_updatenotification");
			config_lock();
			$retval |= rc_update_service("sysctl");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("sysctl");
		}
	}
}

if (!isset($config['system']['sysctl']['param']) || !is_array($config['system']['sysctl']['param']))
	$config['system']['sysctl']['param'] = array();

array_sort_key($config['system']['sysctl']['param'], "name");
$a_sysctlvar = &$config['system']['sysctl']['param'];

if (isset($_GET['act']) && $_GET['act'] === "del") {
	if ($_GET['id'] === "all") {
		foreach ($a_sysctlvar as $sysctlvark => $sysctlvarv) {
			updatenotify_set("sysctl", UPDATENOTIFY_MODE_DIRTY, $a_sysctlvar[$sysctlvark]['uuid']);
		}
	} else {
		updatenotify_set("sysctl", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	}
	header("Location: system_sysctl.php");
	exit;
}

function sysctl_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['system']['sysctl']['param'])) {
				$index = array_search_ex($data, $config['system']['sysctl']['param'], "uuid");
				if (false !== $index) {
					unset($config['system']['sysctl']['param'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabinact"><a href="system_advanced.php"><span><?=gettext("Advanced");?></span></a></li>
      	<li class="tabinact"><a href="system_email.php"><span><?=gettext("Email");?></span></a></li>
      	<li class="tabinact"><a href="system_proxy.php"><span><?=gettext("Proxy");?></span></a></li>
      	<li class="tabinact"><a href="system_swap.php"><span><?=gettext("Swap");?></span></a></li>
        <li class="tabinact"><a href="system_rc.php"><span><?=gettext("Command scripts");?></span></a></li>
        <li class="tabinact"><a href="system_cron.php"><span><?=gettext("Cron");?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gettext("loader.conf");?></span></a></li>
        <li class="tabinact"><a href="system_rcconf.php"><span><?=gettext("rc.conf");?></span></a></li>
        <li class="tabact"><a href="system_sysctl.php" title="<?=gettext("Reload page");?>"><span><?=gettext("sysctl.conf");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
    	<form action="system_sysctl.php" method="post">
    		<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	    	<?php if (updatenotify_exists("sysctl")) print_config_change_box();?>
	      <table width="100%" border="0" cellpadding="0" cellspacing="0">
	        <tr>
	          <td width="40%" class="listhdrlr"><?=gettext("MIB");?></td>
	          <td width="20%" class="listhdrr"><?=gettext("Value");?></td>
	          <td width="30%" class="listhdrr"><?=gettext("Comment");?></td>
	          <td width="10%" class="list"></td>
	        </tr>
				  <?php foreach($a_sysctlvar as $sysctlvarv):?>
				  <?php $notificationmode = updatenotify_get_mode("sysctl", $sysctlvarv['uuid']);?>
	        <tr>
	        	<?php $enable = isset($sysctlvarv['enable']);?>
	          <td class="<?=$enable?"listlr":"listlrd";?>"><?=htmlspecialchars($sysctlvarv['name']);?>&nbsp;</td>
	          <td class="<?=$enable?"listr":"listrd";?>"><?=htmlspecialchars($sysctlvarv['value']);?>&nbsp;</td>
	          <td class="listbg"><?=htmlspecialchars($sysctlvarv['comment']);?>&nbsp;</td>
	          <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
	          <td valign="middle" nowrap="nowrap" class="list">
	            <a href="system_sysctl_edit.php?uuid=<?=$sysctlvarv['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit MIB");?>" border="0" alt="<?=gettext("Edit MIB");?>" /></a>
	            <a href="system_sysctl.php?act=del&amp;uuid=<?=$sysctlvarv['uuid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this MIB?");?>')"><img src="x.gif" title="<?=gettext("Delete MIB");?>" border="0" alt="<?=gettext("Delete MIB");?>" /></a>
	          </td>
	          <?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="del.gif" border="0" alt="" />
						</td>
						<?php endif;?>
	        </tr>
	        <?php endforeach;?>
					<tr>
	          <td class="list" colspan="3"></td>
	          <td class="list"><a href="system_sysctl_edit.php"><img src="plus.gif" title="<?=gettext("Add MIB");?>" border="0" alt="<?=gettext("Add MIB");?>" /></a>
	          	<?php if (!empty($a_sysctlvar)):?>
							<a href="system_sysctl.php?act=del&amp;id=all" onclick="return confirm('<?=gettext("Do you really want to delete all MIBs?");?>')"><img src="x.gif" title="<?=gettext("Delete all MIBs");?>" border="0" alt="<?=gettext("Delete all MIBs");?>" /></a>
							<?php endif;?>
						</td>
	        </tr>
	      </table>
	      <div id="remarks">
	      	<?php html_remark("note", gettext("Note"), gettext("These MIBs will be added to /etc/sysctl.conf. This allow you to make changes to a running system."));?>
	      </div>
	      <?php include("formend.inc");?>
			</form>
	  </td>
  </tr>
</table>
<?php include("fend.inc");?>
