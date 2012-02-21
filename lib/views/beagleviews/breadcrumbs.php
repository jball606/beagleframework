<style type="text/css">
p.breadcrumb
{
	list-style: none; 
	padding-left:0;
	font-size:10px;
}

p.breadcrumb a
{
	text-decoration:none;
	color:#0359d8;
	
}
</style>
<script type="text/javascript">
function goToBC(id,uber_parent)
{
	if(!isNaN(id))
	{
		$.ajax({
				url:"/ajax/breadcrumb_ajax.php",
				data:{id:'breadcrumb',bcid:id,uber_parent:uber_parent},
				success:function(json)
				{
					timer.stopWait();
					window.location.href = json.url;
				}
		});
	}
}
			

</script>

<p class="breadcrumb">
<?
	$x=1;
	foreach($result as $i)
	{	
		if(count($result) != $x)
		{
		?>
		<a href="#" onclick="goToBC(<?=$i['id'];?>,<?=$i['uber_parent'];?>); return false;"><?=$i['name'];?></a> &raquo;
<? 		}
		else
		{
			print($i['name']);
		}
		$x++;
		
	}	?>

</p>
