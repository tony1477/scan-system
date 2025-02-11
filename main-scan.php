<!-- views/layouts/scan.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Scan</title>
    <link href="<?=Yii::app()->baseUrl;?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=Yii::app()->baseUrl;?>/css/fontawesome.min.css" rel="stylesheet">
    <link href="<?=Yii::app()->baseUrl;?>/css/regular.min.css" rel="stylesheet">
    <link href="<?=Yii::app()->baseUrl;?>/css/solid.min.css" rel="stylesheet">
    <style>
        .scan-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        .scan-header {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .scan-input {
            font-size: 18px;
            padding: 15px;
            margin: 10px 0;
            width: 100%;
        }
        .scan-history {
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .scan-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }
        .scan-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .scan-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Scan System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo Yii::app()->createUrl('production/scanhp'); ?>">Production</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo Yii::app()->createUrl('production/scantfs'); ?>">Transfer</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php echo $content; ?>
    </div>

    <!-- Audio elements -->
    <!-- <audio id="scanSuccess" src="<?php// echo Yii::app()->baseUrl; ?>/sounds/success.mp3" preload="auto"></audio>
    <audio id="scanError" src="<?php //echo Yii::app()->baseUrl; ?>/sounds/error.mp3" preload="auto"></audio> -->

    <script src="<?=Yii::app()->baseUrl;?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->baseUrl; ?>/js/scanner.js"></script>
</body>
</html>