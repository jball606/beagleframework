<div id="public_container">

<h1>Sign In</h1>
<h2>Please enter your username and password below</h2>

<div class="dblock" style="width:300px;height:150px;margin:auto;margin-top:100px" id="logdata">
	<div class="dblock_header">User Login</div>
	<div class="dblock_container">
	<ul class="error" id="error"></ul>
		<table class="form">
			<tr>
				<td>Username:</td>
				<td>
					<input class="midfield" type="text" name="login" id="login"/>
				</td>
			</tr>
			<tr>
				<td>Password:</td>
				<td>
					<input class="midfield" type="password" name="password" id="password"/>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<input type="button" value="Sign In" onclick="beaglelogin.systemLogin(); return false;"/>
				</td>
			</tr>
		</table>
					
	
	</div>
</div>
</div>