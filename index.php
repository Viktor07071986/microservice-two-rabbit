<?php

    require_once "db.php";
    require_once __DIR__ . '/vendor/autoload.php';

    use PhpAmqpLib\Connection\AMQPStreamConnection;

    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    list($queue, $messageCount, $consumerCount) = $channel->queue_declare('RabbitMQQueue', false, true, false, false);
    //list($queue, $messageCount, $consumerCount) = $channel->queue_declare('RabbitMQQueue', true);

    $error_count = "";
    $pointer_events="";
    $opacity = "";

    if ($messageCount == 0) {
        $pointer_events = "pointer-events: none;";
        $opacity = "opacity: 0.5;";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($_POST["count_queue_message"] > $messageCount) {
            $error_count = "<strong style='color: #f00; font-weight: bold;'>Превышен лимит выгружаемых сообщений! Максимум можно выгрузить $messageCount сообщений!</strong>";
        } else {
            $rabbitFormCountConsumer = $_POST["count_queue_message"];
            for ($i = 0; $i < $rabbitFormCountConsumer; $i++) {
                $result = $channel->basic_get('RabbitMQQueue', true, null)->body;
                $result = json_decode($result, true);
                $query = "INSERT INTO add_message_rabbit_mq (firstname, header_message, text_message, date_message)
                                VALUES ('".$result["firstname"]."', '".$result["header_message"]."', '".$result["text_message"]."', '".$result["date_message"]."')";
                mysqli_query($mysqli, $query);
            }
        }
        if ($error_count == "") {
            header('Location: '.$_SERVER['REQUEST_URI']);
        }
    }

?>

<?=$error_count;?>

<form action="<?=$_SERVER["REQUEST_URI"];?>" method="POST">
    Сколько сообщений выгрузить из очереди RabbitMQ?<br/>
    <input type="text" name="count_queue_message" required placeholder="Выберите значение больше 0" size="25"><br/><br/>
    <input type="submit" value="Выгрузить" style="<?=$pointer_events . " " . $opacity;?>"/>
</form>


<?php

    $result_for_count = mysqli_query($mysqli, "SELECT * FROM add_message_rabbit_mq");
    $all_row_cnt = mysqli_num_rows($result_for_count);
    if ($all_row_cnt > 0) {
        $rez = $mysqli->query("SELECT * FROM add_message_rabbit_mq ORDER BY id DESC");
        $rows = $rez->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $row) {
            echo "Логин => " . $row["firstname"] . " Заголовок сообщения => " . $row["header_message"] . " Сообщение => " . nl2br($row["text_message"]) . " Дата отправки сообщения в очередь => " . date("d.m.Y H:i:s", strtotime($row["date_message"])) . "<hr/>";
        }
    }

?>