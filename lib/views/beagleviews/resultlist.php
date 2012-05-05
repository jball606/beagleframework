<? 
if((!isset($result) || count($result['records']) == 0) && $result['showemptyresult'] == false)
{ ?><ul class="erroralert"><li>No Search Results</li></ul> <? }
else 
{

//$list = new listclass();
$dates = array();
if(isset($result['dates']))
{
	$dates = $result['dates'];
}

$records = $result['records'];

$hc = count($result['headers']);
if(isset($result['sel']))
{
	$hc++;
}

$tdextra = 0;
if(isset($result['edit_pencil']))
{
	$tdextra = 1;
}

?>


<table class="dblock">
	<? include(getView("result_header.php",'beagleviews')); ?>
	<? include(getView("result_titles.php","beagleviews")); ?>
	<? 
	$x=0;
	
		foreach($records as $row => $i)
		{
			
			?>
			<tr <? if($x % 2 != 0) { ?> class="dblock_altrow" <? } ?>>
			<? 
				if(isset($result['sel']))
				{  
					$oldvalue = $result['orgdata'][$row][$result['sel']['key']];
					?><td class="ac">
						<input type="checkbox" onclick="<?=$result['lib'];?>.checkThis(this.value,this.checked);" name="<?=$result['sel']['name'];?>[]" <? if(isset($result['check'][$oldvalue])) { ?>checked<? } ?> class="<?=$result['lib'];?>" value="<?=$result['orgdata'][$row][$result['sel']['key']];?>"/>
					</td>
			<? } ?>
				
			<? foreach($i as $key => $v)
				{ 
					if($key != "edit_pencil" && ($key != $result['editaccess']['field']) && !isset($result['hiddencols'][$key]))
					{
						if(isPopArray($result['editsystem']) && isset($result['editsystem'][$key]))
						{
							$es = $result['editsystem'][$key];
							?>
							<td <? if($es->getHTMLType() == 3) { print('class = "ac" '); } ?>>
								<?=$es->showFormElement($result['orgdata'][$row][$result['editkey']],$v);?>
							</td>
							<? 
						}
						else 
						{
							if(is_array($v))
							{
								
								?><td <? if(is_numeric($v['value'])) { ?>class="ac"<? } ?>>
									<a href="#" onclick="<?=$result['lib'];?>.openLink('<?=$key?>','<?=implode("','",$v['params']);?>'); return false;"><?=$v['value'];?></a>
								</td> <? 
							}
							else 
							{
						?>
							<td <?if(is_numeric($v)) { ?>class="ac"<? } ?>>
							<? 
							if(isset($dates[$key]))
							{
								print(date($dates[$key],strtotime($v)));
							}
							else 
							{
								print($v);
							} 
						?></td>
					
					<? 		} 
						}
					}
					
					if($key == "edit_pencil")
					{ 
						?>
						<td class="ac">
							<div class="link edit" onclick="<?=$result['lib'];?>.openLink('<?=$key?>','<?=implode("','",$v['params']);?>'); return false;"></div>
						</td>
				<? 		
					}
					
				}
				if($result['editaccess'])
				{
					?>
					<td class="ac">
					<? 
						if($i[$result['editaccess']['field']] != "")
						{
							?><div class="link lock" onclick="<?=$result['lib'];?>.accessFunction('<?= $result['orgdata'][$row][$result['editaccess']['key']];?>'); return false;"></div><? 
						}
						else 
						{
							?><div class="link unlock" onclick="<?=$result['lib'];?>.accessFunction('<?= $result['orgdata'][$row][$result['editaccess']['key']];?>'); return false;"></div><? 
						}	
						
					?>			
					</td>
			<?	}
				?>
			</tr>
	<? 
			$x++;		
		}
		
		if(isset($result['bottommenu']) && $result['bottommenu'] != false)
		{ ?>
			<tr class="dblock_altrow">
				<td colspan = "<?=($hc+$tdextra);?>">
					<? include($result['bottommenu']); ?>
				
				</td>
			</tr>
	<? }
			
	?>
</table>



<? 
}

?>