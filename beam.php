<?php
require_once ('vendor/autoload.php');

$client = Google_Spreadsheet::getClient('google-api-zugang-F7QZQDBdmBA8nU93ndVt2A4UF7QZQDBdmBA8nU93ndVt2A4U.json');
// Get the sheet instance by sheets_id and sheet name
$file = $client->file('19u3EKsPTS_gnafqjFBMqY_IIWVHbUOFumObeiUwGlD4');

$branchen = array_column($file->sheet('Branchen_Schlagworte')->fetch()->items, 'Branchen');
asort($branchen);

$erfahrungen = $file->sheet('Erfahrungsberichte')->fetch()->items;

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Medizintechnik-Unternehmensdatenbank</title>
    <meta charset="utf-8"/>
    <style>
        * {
            box-sizing: border-box;
            font-family: Helvetica, Arial, Geneva, sans-serif;
        }

        #myInputBranche {
            background-image: url('searchicon.png');
            background-position: 10px 10px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }

        #myUnternehmen {
            background-image: url('searchicon.png');
            background-position: 10px 10px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }

        #myTable {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #ddd;
            font-size: 18px;
        }

        #myTable th {
            cursor: pointer;
        }

        #myTable th, #myTable td {
            text-align: left;
            padding: 12px;
        }

        #myTable tr {
            border-bottom: 1px solid #ddd;
        }

        #myTable tr.header, #myTable tr:hover {
            background-color: #f1f1f1;
        }
    </style>

    <script>
        function filter() {
            let table, tr, i;
            let inputBranche = document.getElementById("myInputBranche");
            let inputUnternehmen = document.getElementById("myUnternehmen");
            let filterBranche = inputBranche.value.toUpperCase();
            let filterUnternehmen = inputUnternehmen.value.toUpperCase();
            table = document.getElementById("myTable");
            tr = table.getElementsByTagName("tr");
            let tdBranche, txtValueBranche;
            let tdUnternehmen, txtValueUnternehmen;
            for (i = 0; i < tr.length; i++) {
                tdBranche = tr[i].getElementsByTagName("td")[1];
                tdUnternehmen = tr[i].getElementsByTagName("td")[0];
                if (tdBranche) {
                    txtValueBranche = tdBranche.textContent || tdBranche.innerText;
                    txtValueUnternehmen = tdUnternehmen.textContent || tdUnternehmen.innerText;
                    if (txtValueBranche.toUpperCase().indexOf(filterBranche) > -1 &&
                        txtValueUnternehmen.toUpperCase().indexOf(filterUnternehmen) > -1 ) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

    <script>
        function sortTable(n) {
            let table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("myTable");
            switching = true;
            //Set the sorting direction to ascending:
            dir = "asc";
            /*Make a loop that will continue until
            no switching has been done:*/
            while (switching) {
                //start by saying: no switching is done:
                switching = false;
                rows = table.rows;
                /*Loop through all table rows (except the
                first, which contains table headers):*/
                for (i = 1; i < (rows.length - 1); i++) {
                    //start by saying there should be no switching:
                    shouldSwitch = false;
                    /*Get the two elements you want to compare,
                    one from current row and one from the next:*/
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    /*check if the two rows should switch place,
                    based on the direction, asc or desc:*/
                    if (dir === "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    /*If a switch has been marked, make the switch
                    and mark that a switch has been done:*/
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    //Each time a switch is done, increase this count by 1:
                    switchcount++;
                } else {
                    /*If no switching has been done AND the direction is "asc",
                    set the direction to "desc" and run the while loop again.*/
                    if (switchcount === 0 && dir === "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</head>


<body>

<img src="Beam_Logo.svg" alt="KOMET-Logo" style="float: right;" width="400"><br>

<h1>BEAM - Beruf.Erfahrung.Austausch.Medizintechnik</h1>
<h3>Die Medizintechnik-Unternehmensdatenbank</h3>
<h3>Ein Projekt der Konferenz der Medizintechnikfachschaften (KOMET)</h3>
<p><a href="erfahrungenAnfragen.php" target="_blank">Hier geht es zur Anfrage von Erfahrungskontakten. Einfach hier klicken oder unten auf das Unternehmen.</a></p>

<p style="margin-bottom:2cm;"></p>
<hr>

<div style="width: 49%; float: left;">
    <h3>Branche</h3>
    <select id="myInputBranche" onchange="filter()"
            title="Branche eingeben">
        <option value="">bitte wählen</option>
        <?php foreach ($branchen as $branche):?>
            <?php if ( $branche != '' ): ?>
                <option value="<?php echo $branche; ?>" <?php echo (isset($_REQUEST['branche']) && $branche == $_REQUEST['branche']) ? 'selected' : '' ?>><?php echo $branche; ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
</div>
<div style="width: 49%; float: right;">
    <h3>Namenssuche</h3>
    <input type="text" id="myUnternehmen" onkeyup="filter()" placeholder="Unternehmen suchen"
           title="Namen oder Namensbestandteile eingeben">
</div>
<div style="clear: both"></div>

<div style="overflow-x: auto;">

    <!--Ab hier Tabelle -->

    <table class="table table-bordered table-hover table-condensed" id="myTable">
        <thead>
        <tr>
            <th onclick="sortTable(0)" title="Field Unternehmen">Unternehmen</th>
            <th onclick="sortTable(1)" title="Field Branche">Branche</th>
            <th onclick="sortTable(2)" title="Field Erfahrungen">Erfahrungskontakte vorhanden</th>
        </tr>
        </thead>
        <tbody>
        <?php

        //write line by line
        foreach ($file->sheet('Unternehmen')->fetch()->items as $row):
            if ( $row['Unternehmen'] != '' ):
                $scheme = parse_url($row['Homepage'], PHP_URL_SCHEME);
                if (empty($scheme)) {
                    $link = 'http://' . ltrim($row['Homepage'], '/');
                }

                ?>
            <tr>
                <td>
                    <a href="erfahrungenAnfragen.php?firma=<?php echo urlencode($row['Unternehmen']) ?>" target="_blank"><?php echo $row['Unternehmen'] ?></a>
                </td>
                <td>
                    <?php echo $row['Branche'] ?>
                </td>
                <td>
                    <?php
                    $erfahrungVorhanden = false;
                    foreach ($erfahrungen as $erfahrung) {
                        if ($erfahrung['Firma'] == $row['Unternehmen']){
                            $erfahrungVorhanden = true;
                            break;
                        }
                    }

                    echo ($erfahrungVorhanden) ? 'ja' : 'nein';
                    ?>
                </td>
             </tr>
        <?php
            endif;
        endforeach;
        ?>
        </tbody>
    </table>

    <!--Tabelle bis hier ersetzen -->

</div>

<script>
    sortTable(0);
</script>

</body>
</html>