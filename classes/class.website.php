<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of website
 *
 * @author Dark
 */
class website {

    var $db;
    var $session;
    var $logger;
    var $user = "";
    const mainConfigFile = "configs/config.php";

    function __construct() {
        require website::mainConfigFile;
        require "classes/class.database.php";
        require "classes/class.logger.php";
        require "classes/class.session.php";
        $this->logger = new logger();
        $this->db = new database($this->logger);
        $this->session = session::getInstance();
    }

    function getHead() {
        $head = '
            <title>PROFolio</title>
            <meta name="robots" content="index, follow">
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <link href="css/style.css" rel="stylesheet" type="text/css">
            <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
            <script type="text/javascript">
            if (typeof jQuery == "undefined") { // Als jQuery niet bestaat op dit punt, is de file niet bereikbaar
                var fileref = document.createElement("script"); //  Maak een nieuw script object
                fileref.setAttribute("type","text/javascript"); //  Definieer het als een javascript file
                fileref.setAttribute("src", "./js/jquery.js");  //  Laad de lokale versie in de src attribuut
                if (typeof fileref != "undefined") {            //  Als ons net aangemaakte script object nog correc is
                    document.getElementsByTagName("head")[0].appendChild(fileref);  // Stop het in de head (laad de file)
                }
            }
            </script>
        ';
        return $head;
    }

    function getFooter() {
        return 'Profolio is onderdeel van een groep HvA Informatica studenten.</br>
            Onder deze groep vallen Dymion Fritz, Giedo Terol, Ramon Vloon, Wouter Kievit en Tom Hoogeveen.';
    }

    function getLoginForm() {
        if ($this->getCurrentUser() == false) {
            $loginform = '
                <form action="index.php" method="POST">
                    <table align="right">
                        <tr>		
                            <td>
                                <input type="text" name="studentnr" class="login-field" value="Leerlingnummer" onclick="this.value=\'\';">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="password" name="password" class="login-field" value="Wachtwoord" onclick="this.value=\'\';">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="submit" name="login" class="login-submit" value="Login">
                                <input type="submit" name="register" class="login-submit" value="Register">
                            </td>
                        </tr>				
                    </table>
                </form>
            ';
        } else {
            $loginform = '
                <form action="index.php" method="POST">
                    <table align="right">
                        <tr>
                            <td>
                                <input type="submit" name="logout" class="login-submit" value="Logout">
                            </td>
                        </tr>
                    </table>
                </form>
            ';
        }
        return $loginform;
    }

    function getRegisterForm() {
        $registerform = '
            <div align="center">
                <form action="index.php" method="POST">
                    <table>
                        <tr>
                            <td>Voornaam: </td> 
                            <td><input type="text" name="firstname"></td>
                        </tr>
                        <tr>
                            <td>Tussenvoegsel: </td>
                            <td><input type="text" name="insertion"></td>
                        </tr>
                        <tr>
                            <td>Achternaam: </td>
                            <td><input type="text" name="lastname"></td>
                        </tr>
                        <tr>
                            <td>Email-adres: </td>
                            <td><input type="text" name="email"></td>
                        </tr>
                        <tr>
                            <td>Leerling Nummer: </td>
                            <td><input type="text" name="llnr"></td>
                        </tr>
                        <tr>
                            <td>Studiejaar: </td>
                            <td><input type="text" name="year"></td>
                        </tr>
                        <tr>
                            <td>Wachtwoord: </td>
                            <td><input type="password" name="password"></td>
                        </tr>
                        <tr>
                            <td> </td>
                            <td><input type="submit" name="registreer" value="Registreer"></td>
                        </tr>
                    </table>
                </form>
            </div>
        ';
        return $registerform;
    }

    function getNavMenu() {
        $navmenu = '
            <ul class="submenu">
                <li><a href="index.php?showcase='.$this->getCurrentUser()->id.'">Showcase</a></li>
                <li><a href="index.php?pop='.$this->getCurrentUser()->id.'">POP</a></li>
                <li><a href="index.php?info='.$this->getCurrentUser()->id.'">Wie?</a></li>
            </ul>
        ';
        return $navmenu;
    }

    function getUserInfo() {
        $userinfo = '
            <div id="avatar">
                <img src="/profolio/images/no-pic.bmp"/>
            </div>
            </br>Hier komt de gebruikerinfo';
        return $userinfo;
    }
    
    function getShowcase() {
        $showcase = '
            Dit is de showcase van 
            '.$this->getCurrentUser()->firstname.' '.$this->getCurrentUser()->insertion.' '.$this->getCurrentUser()->lastname.'.
        ';
        return $showcase;
    }
    
    function getPOP() {
        $showcase = '
            Dit is het Persoonlijk Onwikkelings plan van 
            '.$this->getCurrentUser()->firstname.' '.$this->getCurrentUser()->insertion.' '.$this->getCurrentUser()->lastname.'.
        ';
        return $showcase;
    }
    
    function getInfo() {
        $showcase = '
            Dit is de overige informatie van 
            '.$this->getCurrentUser()->firstname.' '.$this->getCurrentUser()->insertion.' '.$this->getCurrentUser()->lastname.'.
        ';
        return $showcase;
    }

    function login($id, $password) {
        $id = stripslashes(mysql_real_escape_string($id));
        $query = "SELECT `password` FROM `studenten` WHERE `id` = '$id';";
        $result = $this->db->doQuery($query);
        if ($result != false) {         // Account bestaat...
            $password = sha1($password . " : " . $id);
            if (mysql_result($result, 0) == $password) {    // Correct password
                require website::mainConfigFile;
                setcookie($cookiename, $id . "," . $password, time() + ($cookietime * 60));
                $this->session->id = $id;
                $this->session->password = $password;
                $this->getCurrentUser();
            } else {
                return 'Onjuist wachtwoord';
            }
        } else {
            return 'Onbekend leerlingnummer.';
        }
    }

    function logout() {
        require website::mainConfigFile;
        setcookie($cookiename, "", time() - 600);
        $this->session->destroy();
        return '<script type="text/javascript">window.location="index.php";</script>';
    }

    function register($_POST) {
        $id = stripslashes(mysql_real_escape_string($_POST['llnr']));
        $firstname = stripslashes(mysql_real_escape_string($_POST['firstname']));
        $insertion = stripslashes(mysql_real_escape_string($_POST['insertion']));
        $lastname = stripslashes(mysql_real_escape_string($_POST['lastname']));
        $email = stripslashes(mysql_real_escape_string($_POST['email']));
        $year = stripslashes(mysql_real_escape_string($_POST['year']));
        $password = sha1($_POST['password'] . " : " . $id);
        $query = "INSERT INTO `studenten` (id, firstname, insertion, lastname, password, email, year)
                VALUES('$id', '$firstname', '$insertion', '$lastname',
                '$password', '$email', '$year')";
        $result = $this->db->doQuery($query);
        return $this->login($id, $_POST['password']);
    }

    function getResult($search) {
        $result = "Als ik daadwerkelijk hier code zou neerzetten ipv text, dan zou hier je zoekresultaat komen.";
        return $result;
    }

    function getHomepage() {
        $homepage = '
            <h1>Profolio</h1>
            <h3>Een online portfolio voor informatica studenten</h3>
            <p>
                Hallo en welkom op deze site. </br>
                Om gebruik te maken van al onze diensten raden wij U aan een account aan te maken.</br>
                Zodra U dit gedaan heeft kunt U uw Portfolio, Persoonlijk Ontwikkeling Plan en extra informatie over jezelf op deze site plaatsen.</br>
            </p>
            <p>
                Als U alleen de Portfolio\'s of Persoonlijke Ontwikkelings Plannen wilt bekijken verwijzen wij U graag door naar de zoek functie van onze site.</br>
                </br>
                Wij hopen dat U kunt vinden wat U zoekt.
            </p>
        ';
        return $homepage;
    }

    function getUser($id) {
        if (!class_exists('user')) {
            require "classes/class.user.php";
        }
        $query = "SELECT * FROM `studenten WHERE `id` = '$id';";
        $result = $this->db->doQuery($query);
        if ($result != false) {
            return new user($this->db, $id);
        } else {
            return false;
        }
    }

    function getCurrentUser() {
        require website::mainConfigFile;
        if (!class_exists('user')) {
            require "classes/class.user.php";
        }
        if ($this->user == "") {
            $user = "";
            if (isset($this->session->id) && isset($this->session->password)) {
                $user = new user($this->db, $this->session->id, $this->session->password);
            } else if (isset($_COOKIE[$cookiename])) {
                $pieces = explode(",", $_COOKIE[$cookiename]);
                $id = $pieces[0];
                $password = $pieces[1];
                $user = new user($this->db, $id, $password);
            } else {
                return false;
            }

            if ($user->exists == true) {
                $this->user = $user;
                return $this->user;
            }
        } else {
            return $this->user;
        }
        return false;
    }

    function uploadImage($_FILES) {
        if (isset($_FILES)) {
            if ($_FILES["img"]["error"] > 0) {
                echo "Bestand is corrupt.";
            } else {
                if ($_FILES["img"]["size"] < 1000000) {
                    require website::mainConfigFile;
                    if (in_array($_FILES["img"]["type"], $AvatarAllowedFiletypes)) {
                        $orimg = $_FILES["img"]["tmp_name"];
                        $orsize = getimagesize($orimg);
                        $orw = $orsize[0];
                        $orh = $orsize[1];
                        $xscale = 100 / $orw;
                        $yscale = 150 / $orh;
                        $scale = min($xscale, $yscale);
                        $new = ($orw * $scale);
                        $neh = ($orh * $scale);
                        switch ($_FILES["img"]["type"]) {
                            case "image/gif":
                                $image = imagecreatefromgif($orimg);
                                break;
                            case "image/png":
                                $image = imagecreatefrompng($orimg);
                                break;
                            default:
                                $image = imagecreatefromjpeg($orimg);
                                break;
                        }
                        $destination = imagecreatetruecolor(100, 150);
                        imagecopyresampled($destination, $image, ((($orw * $xscale) - $new) / 2), ((($orh * $yscale) - $neh) / 2), 0, 0, $new, $neh, $orw, $orh);
                        header('Content-Type: image/png');
                        $testr = imagepng($destination, $this->getCurrentUser()->id . "_img.png", 100);
                        imagedestroy($image);
                        imagedestroy($destination);
                        echo "<img src='" . $this->getCurrentUser()->id . "_img.png' width='100' height='150'>";
                    } else {
                        echo "Verkeerd bestandstype.";
                    }
                } else {
                    echo "Bestand is te groot";
                }
            }
        }
    }

}