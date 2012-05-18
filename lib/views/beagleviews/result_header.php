<tr class="dblock_header"  <? if(getValue($result,'title') == false && getValue($result,'showperpage') == false && getValue($result,'showcount') == false) { ?> style="display:none" <? } ?>>
	<td colspan = "<?=($hc+$tdextra)?>" id="<?=$result['lib'];?>_result_title">
		 
	<?=getValue($result,'title');?>
		
		
	<?
		if(getValue($result,'showperpage') && $result['total_records'] > 10)
		{ ?>
			Items per Page:
			&nbsp;&nbsp;
			<a <? ($result['limit'] == 10) ? print('style="font-weight:bold"') :false; ?> href="#" onclick="<?=$result['lib'];?>.searchResult(0,'','',10); return false;">10</a>&nbsp;
			<a <? ($result['limit'] == 25) ? print('style="font-weight:bold"') :false; ?>  href="#" onclick="<?=$result['lib'];?>.searchResult(0,'','',25); return false;">25</a>&nbsp;
			<a <? ($result['limit'] == 50) ? print('style="font-weight:bold"') :false; ?>  href="#" onclick="<?=$result['lib'];?>.searchResult(0,'','',50); return false;">50</a>&nbsp;
			<a <? ($result['limit'] == 100) ? print('style="font-weight:bold"') :false; ?>  href="#" onclick="<?=$result['lib'];?>.searchResult(0,'','',100); return false;">100</a>&nbsp;
			<a <? ($result['limit'] == 250) ? print('style="font-weight:bold"') :false; ?>  href="#" onclick="<?=$result['lib'];?>.searchResult(0,'','',250); return false;">250</a>
	<? 	}	
		
	if(getValue($result,'showcount'))
		{ ?>
			&nbsp;&nbsp;(<?=$result['total_records'];?> Record<? if($result['total_records']>1) { print("s"); }?> Found)
	<? 	}	?>
	</td>
</tr>
<?
	$pages = ceil($result['total_records']/$result['limit']);
	$perpage = $result['limit'];
		
	if($pages > 1 || isset($result['lettermenu']))
	{
		$listlimit = 5;
		$first = ceil(($result['first']/$perpage));
			
		if($first >1 && $first < ($pages-2))
		{
			$start = $first-2;
			if($start < 0)
			{
				$start = 0;
			}
			$end = $first+2;
		}
		elseif($first <= 1 )
		{
			$start = 0;
			if($pages > 4)
			{
				$end = 4;
			}
			else 
			{
				$end = ($pages-1);
			}
		}
		else 
		{
			$start = ($pages-5);
			if($start < 0)
			{
				$start = 0;
			}
			$end = $pages-1;
		}
		
		
	?>
<tr class="dblock_altrow" >
	<td id="search_nav" colspan="<?=($hc+$tdextra);?>" style="font-size:12px;">
	<? 
		if($pages > 1)
		{ ?>
			Page <?=($first+1);?> of <?=$pages;?> |
			
		<? 
			if($first > 2)
			{ ?>
				<a href="#" onclick="<?=$result['lib'];?>.searchResult(0,''); return false;">1 ... </a>
			<? 
			} 
					
			for($a = $start; $a<=$end;$a++)
			{ ?>
				<a href="#" <? if($a == $first) { ?> style="font-weight:bold" <? } ?> onclick="<?=$result['lib'];?>.searchResult(<?=($a*$perpage);?>,''); return false;"><?=($a+1);?></a>&nbsp;
		<? 	}	?>
				
		<? 
			if($first != $pages && $pages > $listlimit && ($first+$listlimit) < $pages)
			{
			 ?><a href="#" onclick="<?=$result['lib'];?>.searchResult(<?=($result['total_records']-$perpage);?>,''); return false;">... <?=$pages;?></a> <? 
			}
			if($pages > 5)
			{
		?>
			&nbsp; | Go to Page <input type="text" id="gotobox" style="width:30px; border:1px solid #CCC"/> <input type="button" style="font-size:10px" value="GO" onClick="var val = $('#gotobox').val(); if(val) { <?=$result['lib'];?>.searchResult(((val-1)*<?=$perpage;?>)); return false; } else { alert('No page number selected'); }"/> 				
				
		<?
			}
		}
				 
		if(isset($result['lettermenu']))
		{ ?>
			&nbsp;&nbsp;
			<?=$result['lettermenu']['name'];?> :[
			<? 
				foreach($result['lettermenu']['list'] as $k => $i)
				{ 
					if(is_numeric($i) && $i >0)
					{?><a class="lettermenu" href="#" <? if($result['lettermenu']['sel'] == strtoupper($k)) { ?>style="font-weight:bold"<? } ?> onclick="<?=$result['lib'];?>.searchResult(0,'','','','<?=strtoupper($k);?>'); return false;"><?=strtoupper($k);?></a> <? }
					else 
					{ print(strtoupper($k))." "; }
				}
			?>
			<a href="#" class="lettermenu" onclick="<?=$result['lib'];?>.searchResult(0,'','','','all'); return false;">all</a>
		]
		<? 
		}
	?>
	</td>
</tr>
<? } ?>