<?
// Dev Note: $row contains the relevant information for the article being cycled through herein.

// The mini-controller for pre-processing per article.
if(strlen($row['image'])) {
	$row['text_colspan']		= 9; // colspan of the td that will contain the text.
	$row['text_max']			= 9000; // char limit of text to display if the article has an image.
	$row['text_width']			= 430;
} else {
	$row['text_colspan']		= 12;
	$row['text_max']			= 9000;
	$row['text_width']			= 580;
}

?>

<br/>
<table cellpadding="0" cellspacing="0" style="width: 600px; table-layout:fixed; border-collapse: collapse; border-spacing:0;">
	<tr>
		<td colspan="12" valign="top" width="580px" style="width: <?= $row['text_width']; ?>px; padding:5px 10px; border:0;">
			<? if(strlen($row['link'])) { ?> <a href="<?= $row['link']; ?>" style="text-decoration:none;"> <? } ?>
				<h3 style="color:#C00 !important; font-size:28px; margin:0; padding:0; line-height:110%; text-decoration:none;">
					<?= $row['title']; ?>&nbsp;
				</h3>
			<? if(strlen($row['link'])) { ?> </a><? } ?>
			<? if(strlen($row['subtitle'])) { ?>
				<h4 style="color:#222; font-size:20px; margin:0; padding:0; line-height:110%; text-decoration:none;">
					<?= $row['subtitle']; ?>&nbsp;
				</h4>
			<? } ?>
		</td>
	</tr>
	<tr>
		<td class="w<?= $row['text_width']; ?>" colspan="<?= $row['text_colspan']; ?>" width="<?= $row['text_width']; ?>px" style="width: <?= $row['text_width']; ?>px; padding:0 10px; margin:0; border:0;">
			<?= mCut($row['text'], $row['text_max']); ?>&nbsp;
			<? if(strlen($row['link'])) { ?>
				<a href="<?= $row['link']; ?>">View full article online</a>.
			<? } ?>
		</td>
		<? if(strlen($row['image'])) { ?>
			<td colspan="3" valign="top" class="w150" width="150px" style="width:150px; padding:5px 0; border:0;">
				<? if(strlen($row['link'])) { ?><a href="<?= $row['link']; ?>" style="text-decoration:none;"><? } ?>
					<img style="width:140px; max-width:140px; display:block; clear:both; margin:0;" width="140px" src="<?= $row['image']; ?>" />
				<? if(strlen($row['link'])) { ?></a><? } ?>
			</td>
		<? } ?>
	</tr>
</table>
<hr width="80%" style="width:80%" />