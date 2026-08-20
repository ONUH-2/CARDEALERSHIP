<?php
    # localhost/folderName/fileName.php

    // $x = 5;
    // $y = 8;
    // $z = $x + $y;
    // echo $z . "<br>";



    // echo "Welcome to PHP!";

    $i = 1;

while ($i < 6) {
  echo $i * 2 . "<br>";
  $i++;
} 


$i = 1;

while ($i < 6) {
  echo $i;
  $i++;
} 


for ($i = 0; $i <= 100; $i += 10) {
    echo $i . "<br>";
}


$cars = array("Volvo", "BMW", "Toyota", "Audi"); 

echo $cars [0]; echo "<br>";
echo $cars [1]; echo "<br>";
echo $cars [2]; echo "<br>";
echo $cars [3]; echo "<br>";
  echo count($cars); echo "<br>";


  
$so = array("name"=>"bayne", "age"=>"12", "height"=>8);
echo $so["name"];  echo "<br>";



$jay = array (
  array("Volvo", 22, 18),
  array("BMW", 15 , 13),
  array("Saab", 5, 2),
  array("Land Rover", 17, 15)
);

echo $jay[1][0].": has sold: ".$jay[1][1].", cars.<br>";
asort($jay);
print_r($jay);
echo "<br>";



echo $_SERVER['PHP_SELF'];
echo "<br>";

echo $_SERVER['GATEWAY_INTERFACE'];
?>