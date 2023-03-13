<?php
include "./simple_html_dom.php";
interface iRadovi
{
    public function create($data);
    public function read();
    public function save();
}

class DiplomskiRadovi implements iRadovi
{
    private $naziv_rada = null;
    private $tekst_rada = null;
    private $link_rada = null;
    private $oib_tvrtke = null;

    function __construct()
    {
    }

    function create($data)
    {
        $this->naziv_rada = $data["naziv_rada"];
        $this->tekst_rada = $data["tekst_rada"];
        $this->link_rada = $data["link_rada"];
        $this->oib_tvrtke = $data["oib_tvrtke"];
    }

    function read()
    {
        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $db = "radovi";
        ($conn = new mysqli($dbhost, $dbuser, $dbpass, $db)) or
            die("Connection failed: %s\n" . $conn->error);
        $sql = "SELECT * FROM diplomski_radovi";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "ID: " . $row["ID"] . "<br>";
                echo "Naziv rada: " . $row["naziv_rada"] . "<br>";
                echo "Tekst rada: " . $row["tekst_rada"] . "<br>";
                echo "Link rada: " . $row["link_rada"] . "<br>";
                echo "OIB tvrtke: " . $row["oib_tvrtke"] . "<br>";
            }
        } else {
            echo "No data!";
        }
        $conn->close();
    }

    function save()
    {
        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $db = "radovi";
        ($conn = new mysqli($dbhost, $dbuser, $dbpass, $db)) or
            die("Connection failed: %s\n" . $conn->error);
        $ID = uniqid();
        $sql = "INSERT INTO `diplomski_radovi` 
        (`ID`, `naziv_rada`, `tekst_rada`, `link_rada`, `oib_tvrtke`) 
        VALUES ('$ID', '$this->naziv_rada', '$this->tekst_rada', '$this->link_rada', '$this->oib_tvrtke')";
        if ($conn->query($sql) === true) {
            echo "Value with ID: " . $ID . " and name: " 
            . $this->naziv_rada . " inserted!" . "<br>";
        } else {
            echo "Error! " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
}

function get_html($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $str = curl_exec($curl);
    curl_close($curl);

    $html = new simple_html_dom();
    $html->load($str);
    return $html;
}

$url = "https://stup.ferit.hr/index.php/zavrsni-radovi/page/4";
$html = get_html($url);

foreach ($html->find("article") as $article) {
    foreach ($article->find("ul.slides img") as $img) {
    }
    foreach ($article->find("h2.entry-title a") as $link) {
        $html = get_html($link->href);
        foreach ($html->find(".post-content") as $text) {
        }
    }
    $diplomskiRad = [
        "naziv_rada" => $link->plaintext,
        "tekst_rada" => $text->plaintext,
        "link_rada" => $link->href,
        "oib_tvrtke" => preg_replace("/[^0-9]/", "", $img->src),
    ];
    $diplomskiRadovi = new DiplomskiRadovi();
    $diplomskiRadovi->create($diplomskiRad);
    $diplomskiRadovi->save();
}
$html->clear();
unset($html);
?>
