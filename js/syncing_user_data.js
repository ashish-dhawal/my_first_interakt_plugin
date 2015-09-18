jQuery(document).ready(function()
{
	var href = syncUserScript.pluginsUrl + '/wp-interakt-integration/syncing_user_data.php';
	var totalUsers;
	var offset;
	var interakt_app_id;
	var interakt_app_key;
	var sendData=function()
	{
		jQuery('#reload_msg').text("Please do not reload page, It will take some time, have patient.");
		if(parseInt(offset)<parseInt(totalUsers))
		{
			jQuery.post(href,{totalUsers:totalUsers,interaktAppId:interakt_app_id,interaktAppKey:interakt_app_key},function(data,status){
				if(data)
				{
					jQuery('#msg').html('<p>Users synced from   '+offset+' to  '+data+'</p>');
					if(data=='synced')
					{
						jQuery('#reload_msg').text("");
						jQuery('#msg').html('<p>Users already synced</p>');
					}
					else
					{
						offset=data;
						sendData();
					}

				}
			});
		}
		else
		{
			jQuery('#reload_msg').text("");
			jQuery('#msg').text('All data has been sent');
			return false;
		}
	}

	jQuery('#sink_btn').unbind().on('click',function()
	{
		interakt_app_id=syncUserScript.interakt_app_id;
		interakt_app_key=syncUserScript.interakt_app_key;
		if(interakt_app_id=="" && interakt_app_key=="")
		{
			jQuery('#msg').html("<p>Please insert app key and app id</p>");
		}
		else
		{
			var isUserCount="yes";
			jQuery.post(href,{isUserCount:isUserCount},function(data,status){
				if(data)
				{
					jQuery('#msg').html("<p>Total no. of users = "+data+"</br>Syncing User Data</p>");
					totalUsers=data;
					offset=0;
					sendData();
				}
			});
		}
	});
});