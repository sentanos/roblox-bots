The example PHP file assumes that it is being called from inside the folder and includes files accordingly so that it would work if it were called from here.
This is just to show you how to use the functions, if you are actually using the APIs do it from an outside script and block the parent folder off entirely, also remember to put some autentication.
I recommend sending your request from the server in json and then decoding it for use on server (you can use Includes/getPostData.php for that).
