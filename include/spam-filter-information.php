<?php
	#
	# $Id: spam-filter-information.php,v 1.2 2006-12-17 11:55:54 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

?>
<h2><?php echo _('SPAM FILTERS:'); ?></h2>
<p>
<?php echo _('If you are using spam filters which require confirmation of incoming email, these reports will be coming from the following domains:'); ?>
</p>

<table class="spam-info bordered">
<tr><td><?php echo _('Domain'); ?></td><td><?php echo _('Reason'); ?></td></tr>
<tr><td><code class="code">freshports.org</code></td><td><?php echo _('All reports originate from that domain.'); ?></td></tr>
<tr><td><code class="code">unixathome.org</code></td><td><?php echo _('The websites are hosted on a box in that domain.'); ?></td></tr>
<tr><td><code class="code">langille.org</code></td><td><?php echo _('If I need to write to you, it will come from that domain.'); ?></td></tr>
</table>

<p>
<?php echo _("Ensure that you update your spam filters to allow such incoming messages.  If your spam tools require that a confirmation be sent, you'll have to modify things as no confirmations will be sent for FreshPorts messages."); ?>
</p>
