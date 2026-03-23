<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset="utf-8"/>
</head>
<body>

<?php
$blad = "";
$polaczenie = new mysqli("localhost","root","","pracownicy");
if($polaczenie->connect_error) {
	die("błąd połaczeniaa");
}
 
 $dane= ["Id"=>"","Imie"=>"","Nazwisko"=>"","Stanowisko"=>""];
 
if(isset($_POST["zapisz"])){

	$id = (int)($_POST["id"] ?? 0);
	$imie = trim($_POST["imie"] ?? "");
	$nazwisko = trim($_POST["nazwisko"] ?? "");
	$stanowisko = trim($_POST["stanowisko"] ?? "");
	
	if($id <= 0 || $imie == "" || $nazwisko == "" || $stanowisko == "" ) {
			$blad = "Kliknij pole Edycja";
			
	}
	else {
		$stmt = $polaczenie->prepare("UPDATE lista set Imie=? , Nazwisko=? , Stanowisko=? WHERE Id=?");
		
		if(!$stmt) {
			die("Bład zapytania: " . $polaczenie->error);
		}
			
		$stmt->bind_param("sssi" , $imie, $nazwisko, $stanowisko, $id);
		$stmt->execute();
		$stmt->close();
	header("location: zadanie4.php");
	exit();
	}
	
}	

	
if(isset($_POST["dodaj"]) ){
	
	$imie = trim($_POST["imie"] ?? "");
	$nazwisko = trim($_POST["nazwisko"] ?? "");
	$stanowisko = trim($_POST["stanowisko"] ?? "");
	
	if($imie == "" || $nazwisko == "" || $stanowisko == "") {
		$blad = "Wypełnij wszystkie pola";
	}
	else {
		$stmt = $polaczenie->prepare("INSERT INTO lista(Imie , Nazwisko , Stanowisko) VALUES (? , ? , ? )");
	
		if(!$stmt) {
			die("Bład zapytania: " . $polaczenie->error);
		}
		$stmt->bind_param("sss" , $imie, $nazwisko, $stanowisko);
		$stmt->execute();
	
		$stmt->close();
	header("location: zadanie4.php");
	exit();
	}
	
}

if(isset($_GET["usun"])){
	
	$usun = (int)($_GET["usun"] ?? 0);
	
	if($usun <= 0) {
		header("location: zadanie4.php");
		exit();
	}
	
	$stmt = $polaczenie->prepare("DELETE  FROM lista WHERE Id=?");
	
	if(!$stmt) {
			die("Bład zapytania: " . $polaczenie->error);
	}
	$stmt->bind_param("i" , $usun);
	$stmt->execute();
	$stmt->close();
	
	header("location: zadanie4.php");
	exit();
	
}

elseif(isset($_GET["edytuj"])){
	
	$edytuj = (int)($_GET["edytuj"] ?? 0);
	if($edytuj <= 0) {
		header("location: zadanie4.php");
		exit();
	}
	
	$stmt = $polaczenie->prepare("SELECT * FROM lista WHERE Id=?");
		if(!$stmt) {
			die("Bład zapytania: " . $polaczenie->error);
		}
		$stmt->bind_param("i" , $edytuj);
		$stmt->execute();
		
	$rezultat = $stmt->get_result();
	$dane = $rezultat->fetch_assoc();
	$rezultat->free_result();
	$stmt->close();
	
}	

?>
<?php if($blad != "") echo "<p style='color:red;'>$blad</p>"; ?>

<form method="POST">
Znajdz nazwiko: 
<input type="text" name="szukaj">
<input type="submit" value="szukaj">


</form>
<br>
<form method = "POST">
<input type="hidden" name="id" value="<?php echo $dane["Id"]; ?>">

 Imie edytuj: 
<input type="text" name="imie" value="<?php echo $dane["Imie"]; ?>">
Nazwisko edytuj:
<input type="text" name="nazwisko" value="<?php echo $dane["Nazwisko"]; ?>">
Stanowisko edytuj:
<input type="text" name="stanowisko" value="<?php echo $dane["Stanowisko"]; ?>">
<input type="submit" name="zapisz" value="zapisz">

</form>
<br>

<form method = "POST">


Imie: <input type = "text" name="imie">
Nazwisko: <input type = "text" name= "nazwisko">
Stanowisko: <input type = "text" name= "stanowisko">
<input type = "submit" name= "dodaj" value= "Dodaj">


</form>	
<br>

<table border="1">

<tr>
<th>ID</th>
<th>Imie</th>
<th>Nazwisko</th>
<th>Stanowisko</th>
<th>Edytuj</th>
<th>Usuń</th>
</tr>

<?php

	$rezultat = $polaczenie->query("SELECT * FROM lista");

	if (!$rezultat) {
		die("Błąd zapytania: " . $polaczenie->error);
	}
	
	if($rezultat->num_rows == 0) {
		echo "brak wyników";
		
	}

	while($wiersz = $rezultat->fetch_assoc()) {
	
		echo "<tr>";
		echo "<td>". $wiersz["Id"]."</td>";
		echo "<td>". $wiersz["Imie"]."</td>";
		echo "<td>". $wiersz["Nazwisko"]."</td>";
		echo "<td>". $wiersz["Stanowisko"]."</td>";
		echo "<td><a href='?edytuj=".$wiersz["Id"]."'>Edytuj</a></td>";
		echo "<td><a href='?usun=".$wiersz["Id"]."'>Usun</a></td>";
		echo "</tr>";

	}

	$rezultat->free_result();
	
?>

</table>

<br><br>
<table border=1>
<tr>
<th>ID</th>
<th>Imie</th>
<th>Nazwisko</th>
<th>Stanowisko</th>

</tr>

<?php
 if(isset($_POST["szukaj"])) {
	 $szukaj = trim($_POST["szukaj"] ?? "");
	 if($szukaj === "") {
		echo "<span style='color:red;'>Wpisz szukane nazwisko</span>";
	}
	else {	
		$stmt = $polaczenie->prepare("SELECT * FROM lista WHERE Nazwisko LIKE ?");
		if(!$stmt) {
			die("Bład zapytania: " . $polaczenie->error);
		}
	$szukaj = "%" . $szukaj . "%";

	$stmt->bind_param("s" , $szukaj);
	$stmt->execute();
	
	$rezultat = $stmt->get_result();	
 	
	if ($rezultat->num_rows == 0) {
		echo "brak wyników";
	}
	else {
		while($znalezione = $rezultat->fetch_assoc()) {
			echo "<tr>";
			echo "<td>". $znalezione["Id"] . "</td>";
			echo "<td>". $znalezione["Imie"]. "</td>";
			echo "<td>". $znalezione["Nazwisko"]. "</td>";
			echo "<td>". $znalezione["Stanowisko"]. "</td>";
			echo "</tr>";
		}
	}
	
	
	$stmt->close();
	$rezultat->free_result();
	}
	
	
 }
?>

</table>


</body>
</html>