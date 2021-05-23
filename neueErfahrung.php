<?php
session_start();
require_once ('vendor/autoload.php');
include_once ('mailblacklist.php');

$salt = "TiExL4Qz245iuRPQ2sZSYFC8";

$mailAnfang = "Bitte klicke auf den folgenden Link um deinen Eintrag in der BEAM-Datenbank der KOMET zu speichern";
$mailEnde = "Solltest du diese Eintragung nicht veranlasst haben, brauchst du nichts zu tun. Deine Daten werden dann nicht gespeichert. Solltest du dich eingetragen haben und wieder gelöscht werden wollen, wende dich an beam@die-komet.org.";
$mailBetreff = "Deine Eintragung in der BEAM-Datenbank";
$mailAbsender = "beam@die-komet.org";

$client = Google_Spreadsheet::getClient('google-api-zugang-F7QZQDBdmBA8nU93ndVt2A4UF7QZQDBdmBA8nU93ndVt2A4U.json');
// Get the sheet instance by sheets_id and sheet name
$file = $client->file('19u3EKsPTS_gnafqjFBMqY_IIWVHbUOFumObeiUwGlD4');

$ungueltigeMail = false;

if ( isset($_REQUEST['e-mail']) && !filter_var(strip_tags($_REQUEST['e-mail']), FILTER_VALIDATE_EMAIL) ) {
    unset($_REQUEST['e-mail']);
    unset($_SESSION['captcha']);
    $ungueltigeMail = true;
}

if( (!(isset($_SESSION['captcha'])) || ($_REQUEST['captcha'] != $_SESSION['captcha'])) && !(isset($_REQUEST['hash'])) ):

    $unternehmensNamen = array_column($file->sheet('Unternehmen')->fetch()->items, 'Unternehmen');
    asort($unternehmensNamen);
    $taetigkeitsfelder = array_column($file->sheet('Branchen_Schlagworte')->fetch()->items, 'Tätigkeitsfelder');
    asort($taetigkeitsfelder);
    $taetigkeiten = array('Praktikum', 'Werkstudent', 'Abschlussarbeit', 'Arbeitnehmer');
    asort($taetigkeiten);

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>neuer Erfahrungskontakt</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>

    <form action="neueErfahrung.php" method="post">
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Name" required="required" value="<?php echo $_REQUEST['name'] ?>">
        </div>

        <div class="form-group">
            <label for="e-mail">E-Mail * <?php echo ( $ungueltigeMail ) ? "<b>Die eingegebene E-Mail-Adresse ist ungültig.</b>" : '' ?></label>
            <input type="text" class="form-control" name="e-mail" id="e-mail" placeholder="E-Mail" required="required" value="<?php echo $_REQUEST['e-mail'] ?>">
        </div>

        <div class="form-group">
            <label for="jahr">Startjahr *</label>
            <input type="text" class="form-control" name="jahr" id="jahr" placeholder="Startjahr" required="required" value="<?php echo date('Y'); ?>">
        </div>

        <div class="form-group">
            <label for="firma">Firma *     <small><a href="neuesUnternehmen.php">Deine Firma ist nicht dabei? Hier eintragen.</a></small></label>
            <select class="form-control" required="required" name="firma" id="firma">
                <option value="" selected>Bitte auswählen</option>
                <?php foreach ($unternehmensNamen as $unternehmensName):?>
                    <?php if ( $unternehmensName != '' ): ?>
                        <option value="<?php echo $unternehmensName; ?>" <?php echo ($unternehmensName == $_REQUEST['firma']) ? 'selected' : '' ?>><?php echo $unternehmensName; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="taetigkeit">Tätigkeit</label>
            <select class="form-control" name="taetigkeit" id="taetigkeit">
                <option selected>Bitte auswählen</option>
                <?php foreach ($taetigkeiten as $taetigkeit):?>
                    <?php if ( $taetigkeit != '' ): ?>
                        <option value="<?php echo $taetigkeit; ?>" <?php echo ($taetigkeit == $_REQUEST['taetigkeit']) ? 'selected' : '' ?>><?php echo $taetigkeit; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="taetigkeitsfeld">Tätigkeitsfeld</label>
            <select class="form-control" name="taetigkeitsfeld" id="taetigkeitsfeld">
                <option selected>Bitte auswählen</option>
                <?php foreach ($taetigkeitsfelder as $taetigkeitsfeld):?>
                    <?php if ( $taetigkeitsfeld != '' ): ?>
                        <option value="<?php echo $taetigkeitsfeld; ?>" <?php echo ($taetigkeitsfeld == $_REQUEST['taetigkeitsfeld']) ? 'selected' : '' ?>><?php echo $taetigkeitsfeld; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="notizen">Du willst noch weiteres Mitteilen? Probleme, Rechtschreibfehler etc?</label>
            <textarea class="form-control" name="notizen" id="notizen" placeholder="Notizen etc."><?php echo $_REQUEST['notizen'] ?></textarea>
        </div>

        <div class="form-group">
            <label for="captcha">Captcha * <?php echo ( isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha']) ) ? "<b>Das eingegebene Ergebnis war falsch. Bitte erneut versuchen.</b>" : '' ?></label><br>
            <img src="captcha/captcha.php" class="noframe" alt="" id="captchaImage" />
            <input type="text" class="form-control" name="captcha" id="captcha" placeholder="Bitte das Ergebnis der Rechnung im Bild eintippen" required="required">
        </div>

        <div>
            <input type="checkbox" required="required" id="datenschutz" name="datenschutz">
            <label for="datenschutz">Ich habe die <a href="https://die-komet.org/datenschutzerklaerung/" target="_blank">Datenschutzerklärung</a> gelesen und willige der Verarbeitung, Speicherung und Übermittlung meiner Daten auf einen Server außerhalb des EWR ein.</label>
        </div>

        <button type="submit">Absenden</button>

        <small>Mit * markierte Felder sind Pflichtfelder.</small>
    </form>

    <script>
        function getQueryVariable(variable)
        {
            const query = window.location.search.substring(1);
            const vars = query.split("&");
            for (let i=0; i<vars.length; i++)
            {
                const pair = vars[i].split("=");
                if (pair[0] === variable) {
                    return pair[1];
                }
            }
        }

        let optionName = getQueryVariable("firma");
        optionName = decodeURI(optionName);
        if (optionName !== undefined)
        {
            const s = document.getElementById("firma");

            for (let i = 0; i < s.options.length; i++ )
            {
                if ( s.options[i].text === optionName )
                {
                    s.options[i].selected = true;
                    break;
                }
            }
        }
    </script>
    </body>
    </html>
<?php
elseif( ($_REQUEST['captcha'] == $_SESSION['captcha']) || (isset($_REQUEST['hash']) && ($_REQUEST['hash'] == sha1($_REQUEST['e-mail'] . $salt))) ):
    $mailHeader = 'From: ' . $mailAbsender . "\r\n" .
        'Reply-To: ' . $mailAbsender . "\r\n";

    if ( isset($_REQUEST['hash']) && ($_REQUEST['hash'] == sha1($_REQUEST['e-mail'] . $salt)) ):

        $file->sheet('Erfahrungsberichte')->insert(array(
            'Name' => strip_tags($_REQUEST['name']),
            'E-Mail' => strip_tags($_REQUEST['e-mail']),
            'Firma' => strip_tags($_REQUEST['firma']),
            'Jahr' => strip_tags($_REQUEST['jahr']),
            'Tätigkeit' => strip_tags($_REQUEST['taetigkeit']),
            'Tätigkeitsfeld' => strip_tags($_REQUEST['taetigkeitsfeld']),
            'Freigegeben' => 'ja',
            'Notizen' => strip_tags($_REQUEST['notizen']),
        ));

        mail (
            $mailAbsender,
            "neuer Eintrag in der BEAM-Datenbank",
            "Bitte den neuen Eintrag von " . strip_tags($_REQUEST['name']) . " prüfen.\r\n" .
            "Folgende Inhalte wurden eingetragen:\r\n" .
            "Name des Eintragenden: " . strip_tags($_REQUEST['name']) . "\r\n" .
            "E-Mail des Eintragenden: " . strip_tags($_REQUEST['e-mail']) . "\r\n" .
            "Unternehmen: " . strip_tags($_REQUEST['firma']) . "\r\n" .
            "Jahr: " . strip_tags($_REQUEST['jahr']) . "\r\n" .
            "Tätigkeit: " . strip_tags($_REQUEST['taetigkeit']) . "\r\n" .
            "Tätigkeitsfeld: " . strip_tags($_REQUEST['taetigkeitsfeld']) . "\r\n" .
            "Notizen: " . strip_tags($_REQUEST['notizen']) . "\r\n",
            $mailHeader
        );

        $eintrag = true;

    else:
        if (in_array(strtolower(strip_tags($_REQUEST['e-mail'])), $mailblacklist)) {
            exit ("Mail geblockt");
        }

        $requestArray = $_REQUEST;
        $whitelist = array(
            'name',
            'e-mail',
            'firma',
            'jahr',
            'notizen',
            'taetigkeit',
            'taetigkeitsfeld',
        );
        $query = array_intersect_key($requestArray, array_flip($whitelist));
        $query['hash'] = sha1($_REQUEST['e-mail'] . $salt);

        $mailLink = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?" . http_build_query($query);
        $mailInhalt = $mailAnfang . "\r\n" . $mailLink . "\r\n" . $mailEnde;

        mail (
            strip_tags($_REQUEST['e-mail']),
            $mailBetreff,
            $mailInhalt,
            $mailHeader
        );

        $eintrag = false;

    endif;

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>neuen Erfahrungskontakt erfolgreich eingetragen</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>

    <p>
        <?php if ($eintrag): ?>
            Die folgenden Daten wurden übermittelt und gespeichert.
        <?php else: ?>
            Die folgenden Daten wurden übermittelt und werden gespeichert nachdem du den Link in der Bestätigungsmail angeklickt hast.
        <?php endif; ?>
    </p>

    <table>
        <tr style="font-weight: bold;">
            <td>Feld</td>
            <td>Wert</td>
        </tr>
        <tr>
            <td>Name</td>
            <td><?php echo strip_tags($_REQUEST['name']); ?></td>
        </tr>
        <tr>
            <td>E-Mail</td>
            <td><?php echo strip_tags($_REQUEST['e-mail']); ?></td>
        </tr>
        <tr>
            <td>Firma</td>
            <td><?php echo strip_tags($_REQUEST['firma']); ?></td>
        </tr>
        <tr>
            <td>Startjahr</td>
            <td><?php echo strip_tags($_REQUEST['jahr']); ?></td>
        </tr>
        <tr>
            <td>Tätigkeit</td>
            <td><?php echo strip_tags($_REQUEST['taetigkeit']); ?></td>
        </tr>
        <tr>
            <td>Tätigkeitsfeld</td>
            <td><?php echo strip_tags($_REQUEST['taetigkeitsfeld']); ?></td>
        </tr>
        <tr>
            <td>Notizen etc.</td>
            <td><?php echo strip_tags($_REQUEST['notizen']); ?></td>
        </tr>
    </table>

    <p>
        Damit andere auf diese Daten zugreifen können, müssen diese noch von uns freigeschaltet werden.
    </p>

    </body>
    </html>
<?php

else:
    exit('Unbekannter Status erreicht');
endif;
