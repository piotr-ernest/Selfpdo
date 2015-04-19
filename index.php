<?php
require 'connector.php';
?>

<!DOCTYPE html>

<html>
    <head>
        <title><?php echo 'PDO'; ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="public/bootstrap/css/bootstrap-theme.min.css" />
        <link rel="stylesheet" href="public/css/style.css" />
        <script type="text/javascript" src="public/jquery/jquery-1.11.2.js"></script>
        <script type="text/javascript" src="public/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="public/js/script.js"></script>
    </head>
    <body>
        
        <?php
        
        $db = Selfpdo::getInstance();
        
        $db->select('a.category_id, a.title, a.id, ac.name')
                ->from('articles a')
                ->rightJoin('articles_categories ac', 'ac.id = a.category_id')
                ->where('a.id >', 50)
                ->where('a.id IN', array(52,53,54,75,76, 77, 76))
                ->orWhere('a.id IN', array(500, 560, 600,609))
                ->order(array('a.category_id' => 'ASC', 'a.id' => 'ASC'));
        
        $res = $db->createQuery();
        $results = $db->fetchAssoc($res);
        desc($db->getLastInfo($res));
        desc($results);
        ?>

    </body>
</html>

