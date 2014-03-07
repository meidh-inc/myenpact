<?php require_once("../includes/connection.php"); ?>
<?php require_once("../includes/functions.php"); ?>
<?php include_once("../includes/form_functions.php"); ?>
<?php
// code run by server cron to prepare the email que

// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");
$sql = "SELECT id, name FROM campaigns";
$campaign_query = mysql_query($sql);
$campaigns = array();
while($row = mysql_fetch_assoc($campaign_query)) {
    $campaigns[] = $row;
}

if(isset($_POST['submit'])) {
    $sql = "SELECT * FROM results WHERE campaign_id = " . $_POST['campaign'] . " ORDER BY recipient_email ASC";
    $results_query = mysql_query($sql);
    $results = array();
    while($row = mysql_fetch_assoc($results_query)) {
        $results[] = $row;
    }
} else {
    $results = array();
}

?>
<html>
    <head>
        <title>Data Collection</title>
    </head>
    <body>
        <form id="campaign" action="data.php" method="POST">
        <select name="campaign">
        <?php
        foreach($campaigns as $campaign) { ?>
            Choose the campaign: <option value="<?php echo $campaign['id']; ?>"><?php echo $campaign['name']; ?></option>
        <?php } ?>
        </select>
            <input type="submit" name="submit" value="Give Details" />
        </form>
        <table>
            <?php foreach($results as $result) {
                $qsql = "SELECT question FROM questions WHERE id = " . $result['question_id'] . " LIMIT 1";
                $question_query = mysql_query($qsql);
                $question = mysql_result($question_query, 0);
                $asql = "SELECT answer FROM answers WHERE id = " . $result['answer_id'] . " LIMIT 1";
                $answer_query= mysql_query($asql);
                $answer= mysql_result($answer_query, 0); ?>
            <tr>
                <td style="border: #000 solid thin;">
                    <?php echo $result['recipient_email']; ?>
                </td>
                <td style="border: #000 solid thin;">
                    <?php echo $question; ?>
                </td>
                <td style="border: #000 solid thin;">
                    <?php echo $answer; ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </body>
</html>