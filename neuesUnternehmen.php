<?php
session_start();
require_once ('vendor/autoload.php');

$mailAbsender = "beam@die-komet.org";

$client = Google_Spreadsheet::getClient('google-api-zugang-F7QZQDBdmBA8nU93ndVt2A4UF7QZQDBdmBA8nU93ndVt2A4U.json');
// Get the sheet instance by sheets_id and sheet name
$file = $client->file('19u3EKsPTS_gnafqjFBMqY_IIWVHbUOFumObeiUwGlD4');

if( !(isset($_SESSION['captcha'])) || ($_REQUEST['captcha'] != $_SESSION['captcha']) ):

    $branchen = array_column($file->sheet('Branchen_Schlagworte')->fetch()->items, 'Branchen');
    asort($branchen);

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>neues Unternehmen</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>

    <form action="neuesUnternehmen.php" method="post">
        <div class="form-group">
            <label for="name">Name des Unternehmens *</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Name" required="required" value="<?php echo $_REQUEST['name'] ?>">
        </div>

        <div class="form-group">
            <label for="branche">Branche</label>
            <select class="form-control" name="branche" id="branche">
                <?php foreach ($branchen as $branche):?>
                    <?php if ( $branche != '' ): ?>
                        <option value="<?php echo $branche; ?>" <?php echo ($branche == $_REQUEST['branche']) ? 'selected' : '' ?>><?php echo $branche; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="homepage">Homepage *</label>
            <input type="text" class="form-control" name="homepage" id="homepage" placeholder="Homepage" required="required" value="<?php echo $_REQUEST['homepage'] ?>">
        </div>

        <div class="form-group">
            <label for="captcha">Captcha * <?php echo ( isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha'])) ? "<b>Das eingegebene Ergebnis war falsch. Bitte erneut versuchen.</b>" : '' ?></label><br>
            <img src="captcha/captcha.php" class="noframe" alt="" id="captchaImage" />
            <input type="text" class="form-control" name="captcha" id="captcha" placeholder="Bitte das Ergebnis der Rechnung im Bild eintippen" required="required">
        </div>

        <button type="submit">Absenden</button>

        <small>Mit * markierte Felder sind Pflichtfelder.</small>
    </form>
    </body>
    </html>
<?php
elseif( $_REQUEST['captcha'] == $_SESSION['captcha'] ):
    $mailHeader = 'From: ' . $mailAbsender . "\r\n" .
        'Reply-To: ' . $mailAbsender . "\r\n";


    $file->sheet('Unternehmen')->insert(array(
        'Unternehmen' => strip_tags($_REQUEST['name']),
        'Branche' => strip_tags($_REQUEST['branche']),
        'Homepage' => strip_tags($_REQUEST['homepage']),
    ));

    mail (
        $mailAbsender,
        "neues Unternehmen in der BEAM-Datenbank",
        "Bitte bei Gelegenheit die Angaben zur Firma " . strip_tags($_REQUEST['name']) . " prüfen. \r\n".
        'Unternehmen: ' . strip_tags($_REQUEST['name']) . "\r\n" .
        'Branche: ' . strip_tags($_REQUEST['branche']) . "\r\n" .
        'Homepage: ' . strip_tags($_REQUEST['homepage']),
        $mailHeader
    );

    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <title>neues Unternehmen erfolgreich eingetragen</title>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>

    <p>
        Die folgenden Daten wurden übermittelt und gespeichert. Kehre jetzt über diesen Link NEUERLINK zurück und Trage deinen Daten ein.
    </p>

    <table>
        <tr style="font-weight: bold;">
            <td>Feld</td>
            <td>Wert</td>
        </tr>
        <tr>
            <td>Name des Unternehmens</td>
            <td><?php echo strip_tags($_REQUEST['name']); ?></td>
        </tr>
        <tr>
            <td>Branche</td>
            <td><?php echo strip_tags($_REQUEST['branche']); ?></td>
        </tr>
        <tr>
            <td>Homepage</td>
            <td><?php echo strip_tags($_REQUEST['homepage']); ?></td>
        </tr>
    </table>

    </body>
    </html>
<?php

else:
    exit('Unbekannter Status erreicht');
endif;
