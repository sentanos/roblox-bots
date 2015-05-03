local baseURL = 'http://www.example.com' -- This is your website URL, NOT including the file.
local POSTKey = '' -- Match with post key on your php file
local GETKey = '' -- Match with get key on your php file
-- Example: baseURL/receiver.php?key=GETKey
-- {"Validate":POSTKey ...}

-- Action and parameters are based on what you put in the receiver php file.
local send = function(sender,action,parameters)
	local array = {
		Validate = POSTKey,
		Action = action,
		Parameter1 = parameters[1],
		Parameter2 = parameters[2],
		Player = sender.userId
	}
	return game:GetService'HttpService':PostAsync(string.format('%s/receiver.php?key=%s',baseURL,GETKey),game:GetService'HttpService':JSONEncode(array))
end

send(game.Players.Froast,'setRank',{2470023,13}) -- MAKE SURE TO PUT PARAMETERS IN AN ARRAY, EVEN IF THERE IS ONLY ONE
-- If you want to do it for an offline player:
send({userId=2470023},'shout',{'KILLER IS BAD'})
