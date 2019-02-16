<html>
<head>
<title>Web Instagram</title>
</head>
<body>
<!--ACCESS CONTROL-->
<?php
session_start();
if(isset($_SESSION['error'])) echo $_SESSION['error'];
$_SESSION['error'] = '';

if(isset($_SESSION['mode'])){
    echo "<p>Hi, " . $_SESSION["user"] . "!</p><br>";
    echo "<a href=\"logout.php\">LOGOUT</a><br>";
}else{
    echo "<p>Hi, Guest!</p><br>";
    echo "<a href=\"login.php\">LOGIN</a><br>";
}
$db = parse_url(getenv("DATABASE_URL"));
$dbpath = ltrim($db["path"], "/");
$conn = new PDO("pgsql:" . sprintf(
    "host=%s;port=%s;user=%s;password=%s;dbname=%s"
    $db["host"],
    $db["port"],
    $db["user"],
    $db["pass"],
    $dbpath
    ));
//$conn = new mysqli("localhost", "user", "user", "CSCI4140");
//if(mysqli_connect_error()){
//    die('Connect Error(' . mysqli_connect_errorno() . ')' . mysqli_connect_error());
//}
$q = "SELECT mode FROM account WHERE user=?";
$sql = $conn->prepare($q);
$sql->bind_param('s', $_SESSION["user"]);
$result = $sql->execute();
if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    if($row['mode'] == 1){echo '<a href="admin.php">System Initialization</a><br>'; }
}
?>

<!--IMAGE DISPLAY-->
<?php
if(isset($_SESSION['mode'])){
    $q = 'SELECT COUNT(*) FROM image WHERE temp=0 ORDER BY time DESC';
}else{
    $q = 'SELECT COUNT(*) FROM image WHERE temp=0 AND access=\'public\' ORDER BY time DESC';
}
$sql = $conn->query($q);
if($sql->num_rows > 0){
    $row = $sql->fetch_assoc();
    $totalrow = $row[0];
}
$totalpages = ceil(isset($totalrow)?$totalrow:0 / 8);

if (isset($_GET['current']) && is_numeric($_GET['current'])) {
    $currentpage = $_GET['current'];
}else{
    $currentpage = 1;
}
if ($currentpage > $totalpages) {$currentpage = $totalpages;}
if ($currentpage < 1) {$currentpage = 1;}

$sql = "SELECT name FROM image WHERE temp=0 ORDER BY time DESC LIMIT " . ($currentpage-1)*8 . ", 8";
$result = $conn->query($sql);

if($result->num_rows > 0){
  while($row = $result->fetch_assoc()){
    echo "<img src=\"img/upload/" . $row['name'] . "\"><br>";
  }
}

if ($currentpage > 1) {
    echo " <a href='index.php?current=". $currentpage-1 ."'> < </a> ";
}
for ($i=($currentpage-3); $i < (($currentpage+3)+1); $i++) {
    if (($i > 0) && ($i <= $totalpages)) {
        if ($i == $currentpage) {
            echo " [<b>" . $i . "</b>] ";
        } else {
            echo " <a href='index.php?current=" . $i . "'>" . $i . "</a> ";
        }
    }
}
if ($currentpage != $totalpages) {
    echo " <a href='index.php?current=" . $currentpage+1 . "'> > </a> ";
}
?>

<!--UPload-->
<?php
if(!isset($_SESSION['mode'])){
    echo '<!--';
}
?>
<h3> Upload photo</h3><br>
<form method="POST" action="editor.php" ectype="multipart/form-data">
<p>Mode:</p>
<select name="access">
<option value="public">Public</option>
<option value="private">Public</option>
</select>
<br>
<input type="file" name="image" required/>
<br>
<input type="submit" value="Upload" />
</form>
<?php
if(!isset($_SESSION['mode'])){
    echo '-->';
}
?>
</body>
</html>
