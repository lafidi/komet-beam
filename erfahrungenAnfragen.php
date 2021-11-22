<?php
session_start();
require_once ('vendor/autoload.php');
include_once ('mailblacklist.php');

$salt = "CCBL5amPqJHL3H9CanTM65t5";

$mailAnfang = "Bitte klicke auf den folgenden Link, um deine Anfrage über die BEAM-Datenbank der KOMET an die entsprechenden Erfahrungskontakte abzusenden";
$mailEnde = "Solltest du diese Anfrage nicht veranlasst haben, brauchst du nichts zu tun. Deine Daten werden dann nicht gespeichert.";
$mailBetreff = "Deine Anfrage über BEAM-Datenbank";
$mailAnfrageBetreff = "BEAM-Datenbank :: Anfrage zu deinem Erfahrungskontakt für die Firma";
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

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>Anfrage Erfahrungskontakt</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>

    <form action="erfahrungenAnfragen.php" method="post">
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Name" required="required" value="<?php echo $_REQUEST['name'] ?>">
        </div>

        <div class="form-group">
            <label for="e-mail">E-Mail * <?php echo ( $ungueltigeMail ) ? "<b>Die eingegebene E-Mail-Adresse ist ungültig.</b>" : '' ?></label>
            <input type="text" class="form-control" name="e-mail" id="e-mail" placeholder="E-Mail" required="required" value="<?php echo $_REQUEST['e-mail'] ?>">
        </div>

        <div class="form-group">
            <label for="firma">Firma</label>
            <select class="form-control" name="firma" id="firma">
                <option selected>Bitte auswählen</option>
                <?php foreach ($unternehmensNamen as $unternehmensName):?>
                    <?php if ( $unternehmensName != '' ): ?>
                        <option value="<?php echo $unternehmensName; ?>" <?php echo ($unternehmensName == $_REQUEST['firma']) ? 'selected' : '' ?>><?php echo $unternehmensName; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="fragen">Fragen oder Notizen an die Erfahrungs-Kontakte</label>
            <textarea class="form-control" name="fragen" id="fragen" placeholder="Fragen oder Notizen an die Erfahrungs-Kontakte"><?php echo $_REQUEST['fragen'] ?></textarea>
        </div>

        <div class="form-group">
            <label for="captcha">Captcha * <?php echo ( isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha'])) ? "<b>Das eingegebene Ergebnis war falsch. Bitte erneut versuchen.</b>" : '' ?></label><br>
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
    </body>
    </html>
<?php
elseif( ($_REQUEST['captcha'] == $_SESSION['captcha']) || (isset($_REQUEST['hash']) && ($_REQUEST['hash'] == sha1($_REQUEST['e-mail'] . $salt))) ):
    if (in_array(strtolower(strip_tags($_REQUEST['e-mail'])), $mailblacklist)) {
        exit ("Mail geblockt");
    }

    $verschickteAnzahl = 0;

    if ( isset($_REQUEST['hash']) && ($_REQUEST['hash'] == sha1($_REQUEST['e-mail'] . $salt)) ):
        $mailHeader = 'From: ' . $mailAbsender . "\r\n" .
            'Reply-To: ' . strip_tags($_REQUEST['e-mail']) . "\r\n";

        $fragen = strip_tags($_REQUEST['fragen']) ? ("Persönliche Nachricht (optional):\r\n" . strip_tags($_REQUEST['fragen']) . "\r\n\r\n") : "";

        $mailInhalt = "du bekommst diese E-Mail, da du dich auf die-komet.org bereit erklärt hast, deine Erfahrungen aus einem Medizintechnik-Unternehmen zu teilen. Auf die-komet.org hat sich eine Person gemeldet, die sich gerne mit dir über deine Erfahrungen bei " . strip_tags($_REQUEST['firma']) . " austauschen möchte.\r\n\r\n" .
            $fragen .
            "Jetzt liegt es an dir! Kontaktiere " . strip_tags($_REQUEST['name']) . ", damit ihr euch austauschen könnt. Zur Erinnerung: Wir geben deine Kontaktdaten nicht weiter. Du entscheidest, ob du dich nach dieser Anfrage mit der Person in Verbindung setzen möchtest.\r\n\r\n" .
            "Kontaktdaten\r\n" .
            "Name des Anfragenden: " . strip_tags($_REQUEST['name']) . "\r\n" .
            "E-Mail des Anfragenden: " . strip_tags($_REQUEST['e-mail']) . "\r\n\r\n" .
            "Vielen Dank und viele Grüße\r\ndein BEAM-Team der KOMET\r\n\r\nWende dich an beam@die-komet.org, wenn du nicht mehr Teil unseres Erfahrungsschatzes sein möchtest.\r\n\r\n--\r\nBEAM - Beruf.Erfahrung.Austausch.Medizintechnik";

        $kontakte = $file->sheet('Erfahrungsberichte')->fetch()->items;

        foreach ($kontakte as $kontakt):

            if ( ($kontakt['Freigegeben'] == 'ja') && ($kontakt['Firma'] == strip_tags($_REQUEST['firma'])) ):

            mail (
                $kontakt['E-Mail'],
                $mailAnfrageBetreff . " " . strip_tags($_REQUEST['firma']),
                "Dies ist eine automatische E-Mail\r\n\r\nHallo " . $kontakt['Name'] . ",\r\n\r\n" . $mailInhalt,
                $mailHeader
            );
            ++$verschickteAnzahl;

            endif;

        endforeach;

        mail (
            $mailAbsender,
            "Anfrage abgeschickt: " . $mailAnfrageBetreff . " " . strip_tags($_REQUEST['firma']),
            "",
            $mailHeader
        );

        $anfrageAbgeschickt = true;

    else:
        $requestArray = $_REQUEST;
        $whitelist = array(
            'name',
            'e-mail',
            'firma',
            'fragen',
        );
        $query = array_intersect_key($requestArray, array_flip($whitelist));
        $query['hash'] = sha1($_REQUEST['e-mail'] . $salt);

        $mailHeader = 'From: ' . $mailAbsender . "\r\n" .
            'Reply-To: ' . $mailAbsender . "\r\n";

        $mailLink = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?" . http_build_query($query);
        $mailInhalt = $mailAnfang . "\r\n" . $mailLink . "\r\n" . $mailEnde;

        mail (
            strip_tags($_REQUEST['e-mail']),
            $mailBetreff,
            $mailInhalt,
            $mailHeader
        );

        $anfrageAbgeschickt = false;

    endif;

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>Erfahrungskontakt angefragt</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body <?php if (! $anfrageAbgeschickt) {echo ('onload="alert(\'Bitte den Link in deiner E-Mail anklicken.\')"');} ?>>

    <p>
        <?php if ( $anfrageAbgeschickt ): ?>
            Die folgenden Daten wurden übermittelt und an <?php echo $verschickteAnzahl ?> Kontakte versandt.
        <?php else: ?>
            Die folgenden Daten wurden übermittelt und werden versandt, nachdem du den Link in der Bestätigungsmail angeklickt hast. Solltest du die Mail nicht finden schaue bitte auch in deinem Spamordner nach.
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
            <td>Fragen oder Notizen an die Erfahrungskontakte</td>
            <td><?php echo strip_tags($_REQUEST['fragen']); ?></td>
        </tr>
    </table>

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

else:
    exit('Unbekannter Status erreicht');
endif;
