<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# The number of physical and virtual categories over time (#604).
	#
	# The data is in category_history, one row per ports commit which changed
	# either list, added to daily by scripts/category_history_update.pl.
	# The chart is drawn here as SVG, so it needs no javascript.
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	DEFINE('CATEGORY_HISTORY_CGIT', 'https://cgit.freebsd.org/ports/commit/?id=');

	# Postgres hands text[] back as {a,b,c}.  Category names never need quoting.
	function category_history_array($Value) {
		if ($Value === null) {
			return null;
		}

		$Value = trim($Value, '{}');

		return $Value === '' ? array() : explode(',', $Value);
	}

	# for the chart: a step line through ($time, $count) points, ending at $End
	function category_history_path($Points, $End, $X, $Y) {
		$Path = '';
		$Last = null;
		foreach ($Points as $Point) {
			list($Time, $Count) = $Point;
			if ($Last === null) {
				$Path = sprintf('M%.1f,%.1f', $X($Time), $Y($Count));
			} else {
				$Path .= sprintf(' H%.1f V%.1f', $X($Time), $Y($Count));
			}
			$Last = $Count;
		}

		if ($Last !== null) {
			$Path .= sprintf(' H%.1f', $X($End));
		}

		return $Path;
	}

	$Title = _('Categories over time');
	freshports_Start($Title,
					_('The number of physical and virtual categories in the FreeBSD ports tree over time'),
					'FreeBSD, ports, categories, history, graph');

	$Rows = array();
	$result = pg_query_params($db, 'SELECT commit_hash, extract(epoch FROM commit_date) AS commit_time, physical, virtual
	                                  FROM category_history
	                              ORDER BY commit_date, commit_hash', array());
	if ($result) {
		while ($myrow = pg_fetch_assoc($result)) {
			$Rows[] = array(
				'commit'   => $myrow['commit_hash'],
				'time'     => (int)$myrow['commit_time'],
				'physical' => category_history_array($myrow['physical']),
				'virtual'  => category_history_array($myrow['virtual']),
			);
		}
	}
?>

<style>
.category-history-chart { max-width: 800px; width: 100%; height: auto; }
.category-history-chart text { fill: var(--body-text); font-size: 12px; }
.category-history-chart .grid { stroke: var(--quote-border); stroke-width: 1; }
.category-history-chart .axis { stroke: var(--body-text); stroke-width: 1; }
.category-history-chart .series { fill: none; stroke-width: 2; }
.category-history-physical { stroke: #b2182b; color: #b2182b; }
.category-history-virtual  { stroke: #2166ac; color: #2166ac; }
@media (prefers-color-scheme: dark) {
  .category-history-physical { stroke: #f4a582; color: #f4a582; }
  .category-history-virtual  { stroke: #92c5de; color: #92c5de; }
}
.category-history-key { font-weight: bold; }
.category-history-changes td { vertical-align: top; }
</style>

	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

<tr>
	<?php echo freshports_PageBannerTitle($Title); ?>
</tr>

<tr><td>
<p>
<?php printf(_('The FreeBSD ports tree has two kinds of category. A %s category is a directory: every port lives in one. A %s category exists only in the CATEGORIES line of a port, such as %s or %s.'),
		'<span class="category-history-key category-history-physical">' . _('physical') . '</span>',
		'<span class="category-history-key category-history-virtual">'  . _('virtual')  . '</span>',
		'<a href="/python/">python</a>', '<a href="/kde/">kde</a>'); ?>
</p>
<p>
<?php printf(_('The physical categories are the %s entries in the top level %s. The virtual categories are %s in %s, less the physical ones; that list first appeared in August 1999. Both are read from the ports git history and updated daily.'),
		'<code class="code">SUBDIR</code>', '<span class="file">Makefile</span>',
		'<code class="code">VALID_CATEGORIES</code>', '<span class="file">Mk/bsd.port.mk</span>'); ?>
</p>
</td></tr>

<tr><td>
<?php
	if (!$Rows) {
		echo '<p>' . _('There is no category history yet. Is the category_history periodic script enabled?') . '</p>';
	} else {
		$Width  = 800;
		$Height = 360;
		$Left   = 40;
		$Right  = 15;
		$Top    = 15;
		$Bottom = 30;

		$Start = $Rows[0]['time'];
		$End   = time();

		$Max = 0;
		foreach ($Rows as $Row) {
			$Max = max($Max, count($Row['physical']), $Row['virtual'] === null ? 0 : count($Row['virtual']));
		}
		$Max = (int)ceil(($Max + 1) / 10) * 10;

		$X = function($Time) use ($Start, $End, $Left, $Right, $Width) {
			return $Left + ($Time - $Start) / ($End - $Start) * ($Width - $Left - $Right);
		};
		$Y = function($Count) use ($Max, $Top, $Bottom, $Height) {
			return $Height - $Bottom - $Count / $Max * ($Height - $Top - $Bottom);
		};

		$Physical = array();
		$Virtual  = array();
		foreach ($Rows as $Row) {
			$Physical[] = array($Row['time'], count($Row['physical']));
			if ($Row['virtual'] !== null) {
				$Virtual[] = array($Row['time'], count($Row['virtual']));
			}
		}

		$Latest = end($Rows);

		$Summary = sprintf(_('A chart of the number of categories from %s to today. Now: %d physical, %d virtual.'),
			gmdate('Y', $Start), count($Latest['physical']), count($Latest['virtual'] ?? array()));

		echo '<svg class="category-history-chart" viewBox="0 0 ' . $Width . ' ' . $Height . '" role="img" aria-label="' . htmlspecialchars($Summary) . '" xmlns="http://www.w3.org/2000/svg">' . "\n";

		# horizontal grid and the count axis
		for ($Count = 0; $Count <= $Max; $Count += 10) {
			$y = sprintf('%.1f', $Y($Count));
			echo '<line class="' . ($Count ? 'grid' : 'axis') . '" x1="' . $Left . '" x2="' . ($Width - $Right) . '" y1="' . $y . '" y2="' . $y . '"/>';
			echo '<text x="' . ($Left - 6) . '" y="' . $y . '" text-anchor="end" dominant-baseline="middle">' . $Count . '</text>' . "\n";
		}

		# a year line every five years
		for ($Year = (int)ceil(gmdate('Y', $Start) / 5) * 5; $Year <= (int)gmdate('Y', $End); $Year += 5) {
			$x = sprintf('%.1f', $X(gmmktime(0, 0, 0, 1, 1, $Year)));
			echo '<line class="grid" x1="' . $x . '" x2="' . $x . '" y1="' . $Top . '" y2="' . ($Height - $Bottom) . '"/>';
			echo '<text x="' . $x . '" y="' . ($Height - $Bottom + 18) . '" text-anchor="middle">' . $Year . '</text>' . "\n";
		}

		echo '<path class="series category-history-physical" d="' . category_history_path($Physical, $End, $X, $Y) . '"/>' . "\n";
		echo '<path class="series category-history-virtual" d="'  . category_history_path($Virtual,  $End, $X, $Y) . '"/>' . "\n";

		# label each line at its right hand end
		echo '<text x="' . ($Width - $Right - 4) . '" y="' . sprintf('%.1f', $Y(count($Latest['physical'])) - 8) . '" text-anchor="end">' . _('physical') . ' ' . count($Latest['physical']) . '</text>';
		if ($Latest['virtual'] !== null) {
			echo '<text x="' . ($Width - $Right - 4) . '" y="' . sprintf('%.1f', $Y(count($Latest['virtual'])) - 8) . '" text-anchor="end">' . _('virtual') . ' ' . count($Latest['virtual']) . '</text>';
		}

		echo "\n</svg>\n";
?>
</td></tr>

<tr><td>
<h2><?php echo _('Every change'); ?></h2>
<p>
<?php echo _('Newest first. A new physical category is often added to VALID_CATEGORIES a commit or two before its directory, so it can appear as a virtual category for a moment.'); ?>
</p>

<table class="borderless category-history-changes">
<tr><th><?php echo _('Date'); ?></th><th><?php echo _('Physical'); ?></th><th><?php echo _('Virtual'); ?></th><th><?php echo _('Added'); ?></th><th><?php echo _('Removed'); ?></th></tr>
<?php
		# names to link: only the categories which exist now have a page
		$Current = array_flip(array_merge($Latest['physical'], $Latest['virtual'] ?? array()));

		$Names = function($List, $Class) use ($Current) {
			$HTML = array();
			foreach ($List as $Name) {
				$Text = htmlspecialchars($Name);
				if (isset($Current[$Name])) {
					$Text = '<a href="/' . rawurlencode($Name) . '/">' . $Text . '</a>';
				}
				$HTML[] = '<span class="' . $Class . '">' . $Text . '</span>';
			}
			return implode(' ', $HTML);
		};

		for ($i = count($Rows) - 1; $i >= 0; $i--) {
			$Row      = $Rows[$i];
			$Previous = $i ? $Rows[$i - 1] : array('physical' => array(), 'virtual' => array());

			$Virtual         = $Row['virtual']      ?? array();
			$PreviousVirtual = $Previous['virtual'] ?? array();

			$Added   = $Names(array_values(array_diff($Row['physical'], $Previous['physical'])), 'category-history-physical') . ' '
			         . $Names(array_values(array_diff($Virtual, $PreviousVirtual)), 'category-history-virtual');
			$Removed = $Names(array_values(array_diff($Previous['physical'], $Row['physical'])), 'category-history-physical') . ' '
			         . $Names(array_values(array_diff($PreviousVirtual, $Virtual)), 'category-history-virtual');

			echo '<tr><td><a href="' . CATEGORY_HISTORY_CGIT . htmlspecialchars($Row['commit']) . '">' . gmdate('Y-m-d', $Row['time']) . '</a></td>'
			   . '<td class="numeric-cell">' . count($Row['physical']) . '</td>'
			   . '<td class="numeric-cell">' . ($Row['virtual'] === null ? '&ndash;' : count($Row['virtual'])) . '</td>'
			   . '<td>' . ($i ? trim($Added) : _('the first ports Makefile')) . '</td>'
			   . '<td>' . trim($Removed) . '</td></tr>' . "\n";
		}
?>
</table>
<?php
	}
?>
</td></tr>

</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
