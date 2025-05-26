<?php
require __DIR__."/vendor/autoload.php";
$db = new MysqliDb();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="register.php">register</a> | 
    <a href="login.php">login</a>
    <hr>
    $allusers = $db->get("users");

echo "<pre>";
print_r($allusers);
echo "</pre>";
</body>
</html>