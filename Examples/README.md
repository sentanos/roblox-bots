<b>receiver.php</b>
This example can receive requests sent by send.lua. Here is a step by step for running this on your server:

1. Go to the main page (master branch) of this repo and click "Download as Zip".
2. Upload the unzipped folder <i>(the entire folder)</i> to your server.
3. Go to roblox-bots-master/Examples and move receiver.php to your root folder (out of roblox-bots-master).
4. Configure the username, password, get key, and post key in receiver.php
5. Configure the file in roblox-bots-master/Lua/send.lua with the same get key and post key, including your server URL.

Once you've done that you can use the send function in your ROBLOX game to execute commands on the server!

<b>working.php</b>
The example PHP file assumes that it is being called from inside the folder and includes files accordingly so that it would work if it were called from here. It does not include anything for receiving requests.
