# roblox-bots
This repository is for ROBLOX Web APIs commonly referred to as bots.
Mostly consists of group APIs (but includes others).

You have to set up your own file for communicating with your roblox server, I will only help with function usage.
<i>Please remember to look at the Examples/example.php file.</i>

Local UsernameFromId, IDFromUsername, and getRankInGroup functions are available in Includes/misc.php.

<b>change-rank.php</b>
This API can change the rank of a user in a group by their userId. It does NOT require the roleSetId, it can automatically convert a rank into a roleSetId by using group's roleset array (which can also be automatically gotten using the groupId). You can apply a rank limit to prevent someone from changing the rank of someone over the rank limit.

<b>exile.php</b>
This API can exile a user in a group by their userId, with the option of deleitng their posts when doing so. This is the ONLY API which required manual input and required the roleSetId of the user executing the request (read comment in the file for more).

<b>handleJoinRequest.php</b>
Can accept or deny a user into a group by their username.

<b>shout.php</b>
This API can shout a message in a group.

<b>getPlayers.php</b>
Miscellaneous non-action API that can get users in a group based on their rank (or all users) and exports the username and userId in json format.

<b>message.php</b>
Miscellaneous API that can send messages to other users.

<b>upload.php</b>
Miscellaneous API that can upload a ROBLOX asset in XML form to a specific assetId.
