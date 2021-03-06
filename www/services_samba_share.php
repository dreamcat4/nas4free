<?php
/*
	services_samba_share.php

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

$pgtitle = array(gettext("Services"), gettext("CIFS/SMB"), gettext("Shares"));

if ($_POST) {
	$pconfig = $_POST;

	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("smbshare", "smbshare_process_updatenotification");
		  config_lock();
			$retval |= rc_update_service("samba");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if (0 == $retval) {
			updatenotify_delete("smbshare");
		}
	}
}

if (!isset($config['samba']['share']) || !is_array($config['samba']['share']))
	$config['samba']['share'] = array();

array_sort_key($config['samba']['share'], "name");
$a_share = &$config['samba']['share'];

if (isset($_GET['act']) && $_GET['act'] === "del") {
	updatenotify_set("smbshare", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	header("Location: services_samba_share.php");
	exit;
}

function smbshare_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = array_search_ex($data, $config['samba']['share'], "uuid");
			if (FALSE !== $cnid) {
				unset($config['samba']['share'][$cnid]);
				write_config();
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
				<li class="tabinact"><a href="services_samba.php"><span><?=gettext("Settings");?></span></a></li>
				<li class="tabact"><a href="services_samba_share.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Shares");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr> 
    <td class="tabcont">
      <form action="services_samba_share.php" method="post">
        <?php if (!empty($savemsg)) print_info_box($savemsg); ?>
        <?php if (updatenotify_exists("smbshare")) print_config_change_box();?>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
          	<td width="30%" class="listhdrlr"><?=gettext("Path");?></td>
            <td width="20%" class="listhdrr"><?=gettext("Name");?></td>
            <td width="20%" class="listhdrr"><?=gettext("Comment");?></td>
            <td width="10%" class="listhdrr"><?=gettext("Browseable");?></td>
            <td width="10%" class="listhdrr"><?=gettext("Guest");?></td>
            <td width="10%" class="list"></td>
          </tr>
  			  <?php foreach($a_share as $sharev):?>
  			  <?php $notificationmode = updatenotify_get_mode("smbshare", $sharev['uuid']);?>
          <tr>
          	<td class="listlr"><?=htmlspecialchars($sharev['path']);?>&nbsp;</td>
            <td class="listr"><?=htmlspecialchars($sharev['name']);?>&nbsp;</td>
            <td class="listr"><?=htmlspecialchars($sharev['comment']);?>&nbsp;</td>
            <td class="listbg"><?=htmlspecialchars(isset($sharev['browseable'])?gettext("Yes"):gettext("No"));?></td>
            <td class="listbg"><?=htmlspecialchars(isset($sharev['guest'])?gettext("Yes"):gettext("No"));?></td>
            <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
            <td valign="middle" nowrap="nowrap" class="list">
              <a href="services_samba_share_edit.php?uuid=<?=$sharev['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit share");?>" border="0" alt="<?=gettext("Edit share");?>" /></a>
              <a href="services_samba_share.php?act=del&amp;uuid=<?=$sharev['uuid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this share?");?>')"><img src="x.gif" title="<?=gettext("Delete share");?>" border="0" alt="<?=gettext("Delete share");?>" /></a>
            </td>
            <?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="del.gif" border="0" alt="" />
						</td>
						<?php endif;?>
          </tr>
          <?php endforeach;?>
          <tr> 
            <td class="list" colspan="5"></td>
            <td class="list"><a href="services_samba_share_edit.php"><img src="plus.gif" title="<?=gettext("Add share");?>" border="0" alt="<?=gettext("Add share");?>" /></a></td>
          </tr>
        </table>
        <?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
