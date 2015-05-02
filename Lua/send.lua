local baseURL = 'http://www.example.com' -- This is your website URL, NOT including the file.
local POSTKey = '' -- Match with post key on your php file
local GETKey = '' -- Match with get key on your php file
-- Example: baseURL/receiver.php?key=GETKey
-- {"Validate":POSTKey ...}

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
