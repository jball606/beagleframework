<tr>
	<? 
		if(isset($result['sel']))
		{ ?> <th width="25px"><input type="checkbox" id="global_<?=$result['lib'];?>" onclick="<?=$result['lib'];?>.allCheck(this.checked);"/></th> <?  } ?>
	
	
	
		<? 
			$order = array();
			if(isset($result['order']))
			{
				$order = $result['order'];
			}
			foreach($result['headers'] as $k => $i)
			{
				if(strpos($i,$result['editaccess']['field']) === false)
				{
					?>
					<th id="<?=$k;?>" class="ac">
					<? 
						$o = 1;
						if(isset($order[$k]))
						{
							
							if($order[$k] == 1)
							{
								
								$o = 2;
							}
							if($order[$k] == 2)
							{
								$o = 0;
							}
						}			
					?>
						<?
							if(isset($order[$k]))
							{ 
								if($order[$k] == 1)
								{ ?><img style="float:right" class="up_arrow"/> <? }
								elseif($order[$k] == 2)
								{ ?><img style="float:right" class="down_arrow"/><? } 
							}
								
						
						?>
						<a href="#" onclick="<?=$result['lib'];?>.searchResult(0,'<?=$k;?>',<?=$o?>); return false;"><?=$i;?></a>
					</th>
					<? 
					}
			}
		
		
		if(isset($result['edit_pencil']))
		{
			?><th width="35px">edit</th><? 
		} 
		if($result['editaccess'])
		{
			?><th width="35px">Access</th><? 
		}
		if(isset($header_extras) && isPopArray($header_extras))
		{
			foreach($header_extras as $i)
			{
				?><th><?=$i;?></th><? 
			}
		}
		?>
		
	</tr>
