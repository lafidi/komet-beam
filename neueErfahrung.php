<?php
session_start();
require_once ('vendor/autoload.php');

$salt = "TiExL4Qz245iuRPQ2sZSYFC8";

$mailAnfang = "Bitte klicke auf den folgenden Link um deinen Eintrag in der BEAM-Datenbank der KOMET zu speichern";
$mailEnde = "Solltest du diese Eintragung nicht veranlasst haben, brauchst du nichts zu tun. Deine Daten werden dann nicht gespeichert.";
$mailBetreff = "Deine Eintragung in der BEAM-Datenbank";
$mailAbsender = "beam@die-komet.org";

$client = Google_Spreadsheet::getClient('google-api-zugang-F7QZQDBdmBA8nU93ndVt2A4UF7QZQDBdmBA8nU93ndVt2A4U.json');
// Get the sheet instance by sheets_id and sheet name
$file = $client->file('19u3EKsPTS_gnafqjFBMqY_IIWVHbUOFumObeiUwGlD4');

if ( !filter_var(strip_tags($_REQUEST['e-mail']), FILTER_VALIDATE_EMAIL) ) {
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
        <title>neuer Erfahrungsbericht</title>
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
            <label for="firma">Firma</label>
            <select class="form-control" name="firma" id="firma">
                <?php foreach ($unternehmensNamen as $unternehmensName):?>
                    <?php if ( $unternehmensName != '' ): ?>
                        <option value="<?php echo $unternehmensName; ?>" <?php echo ($unternehmensName == $_REQUEST['firma']) ? 'selected' : '' ?>><?php echo $unternehmensName; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="erfahrungsbericht">Erfahrungsbericht</label>
            <textarea class="form-control" name="erfahrungsbericht" id="erfahrungsbericht" placeholder="Erfahrungsbericht"><?php echo $_REQUEST['erfahrungsbericht'] ?></textarea>
        </div>

        <div class="form-group">
            <label for="taetigkeit">Tätigkeit</label>
            <select class="form-control" name="taetigkeit" id="taetigkeit">
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
                <?php foreach ($taetigkeitsfelder as $taetigkeitsfeld):?>
                    <?php if ( $taetigkeitsfeld != '' ): ?>
                        <option value="<?php echo $taetigkeitsfeld; ?>" <?php echo ($taetigkeitsfeld == $_REQUEST['taetigkeitsfeld']) ? 'selected' : '' ?>><?php echo $taetigkeitsfeld; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="captcha">Captcha * <?php echo ( isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha']) ) ? "<b>Das eingegebene Ergebnis war falsch. Bitte erneut versuchen.</b>" : '' ?></label><br>
            <img src="captcha/captcha.php" class="noframe" alt="" id="captchaImage" />
            <input type="text" class="form-control" name="captcha" id="captcha" placeholder="Bitte das Ergebnis der Rechnung im Bild eintippen" required="required">
        </div>

        <button type="submit">Absenden</button>

        <small>Mit * markierte Felder sind Pflichtfelder.</small>
    </form>
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
            'Erfahrung' => strip_tags($_REQUEST['erfahrungsbericht']),
            'Tätigkeit' => strip_tags($_REQUEST['taetigkeit']),
            'Tätigkeitsfeld' => strip_tags($_REQUEST['taetigkeitsfeld']),
            'Freigegeben' => 'nein',
        ));

        mail (
            $mailAbsender,
            "neuer Eintrag in der BEAM-Datenbank",
            "Bitte den neuen Eintrag von " . strip_tags($_REQUEST['name']) . " freischalten.",
            $mailHeader
        );

        $eintrag = true;

    else:

        $requestArray = $_REQUEST;
        $whitelist = array(
            'name',
            'e-mail',
            'firma',
            'erfahrungsbericht',
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
        <title>neuen Erfahrungsbericht erfolgreich eingetragen</title>
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
            <td>Erfahrung</td>
            <td><?php echo strip_tags($_REQUEST['erfahrungsbericht']); ?></td>
        </tr>
        <tr>
            <td>Firma</td>
            <td><?php echo strip_tags($_REQUEST['firma']); ?></td>
        </tr>
        <tr>
            <td>Tätigkeit</td>
            <td><?php echo strip_tags($_REQUEST['taetigkeit']); ?></td>
        </tr>
        <tr>
            <td>Tätigkeitsfeld</td>
            <td><?php echo strip_tags($_REQUEST['taetigkeitsfeld']); ?></td>
        </tr>
    </table>

    <p>
        Damit andere auf diese Daten zugreifen können, müssen diese noch von uns freigeschaltet werden.
    </p>

    </body>
    </html>
<?php

else:
    exit('Unbekanter Status erreicht');
endif;
