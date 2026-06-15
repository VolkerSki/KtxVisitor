<?php

use humhub\widgets\PanelMenu;
use yii\helpers\Html;
use yii\helpers\Url;

// Besucher nach Datum und Startzeit sortieren
usort($visitors, function($a, $b) {
    $dateA = strtotime($a['start']);
    $dateB = strtotime($b['start']);
    return $dateA <=> $dateB;
});

// Die Liste der Besucher auf maximal 3 Einträge begrenzen
$limitedVisitors = array_slice($visitors, 0, 3);

// Die Liste der Besucher auf aktuelle/zukünftige Einträge begrenzen
$heute = new DateTime();
$heute->setTime(0, 1, 0); // Setzt die Uhrzeit auf 00:01:00

// 1. Filtern
$aktuelleVisitors = array_filter($visitors, function($visitor) use ($heute) {
    try {
        $startDatum = DateTime::createFromFormat('Y-m-d H:i:s', $visitor['start']);
        $endDatum = isset($visitor['end']) ? DateTime::createFromFormat('Y-m-d H:i:s', $visitor['end']) : null;

        if (!$startDatum) {
            throw new Exception('Ungültiges Start-Datum: ' . $visitor['start']);
        }

        $startTimestamp = $startDatum->getTimestamp();
        $endTimestamp = $endDatum ? $endDatum->getTimestamp() : null;
        $heuteTimestamp = $heute->getTimestamp();

        return ($startTimestamp >= $heuteTimestamp) || ($endTimestamp !== null && $endTimestamp >= $heuteTimestamp);

    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
});

// 2. AnzeigeDatum setzen
$aktuelleVisitors = array_map(function($visitor) {
    $startDatum = DateTime::createFromFormat('Y-m-d H:i:s', $visitor['start']);
    $endDatum = isset($visitor['end']) ? DateTime::createFromFormat('Y-m-d H:i:s', $visitor['end']) : null;

    if ($startDatum && $endDatum) {
        // Prüfen, ob Start- und Enddatum am selben Tag liegen
        if ($startDatum->format('Y-m-d') === $endDatum->format('Y-m-d')) {
            // Nur Startdatum mit Uhrzeit anzeigen
            $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i');
        } else {
            // Start- und Enddatum mit Uhrzeit anzeigen
            $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i') . ' - ' . $endDatum->format('d.m.Y H:i');
        }
    } elseif ($startDatum) {
        $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i');
    } else {
        $visitor['anzeigeDatum'] = 'Unbekannt';
    }

    return $visitor;
}, $aktuelleVisitors);


// Anzahl der Besucher überprüfen und ausgeben
$visitorCount = count($aktuelleVisitors);

$limitedVisitors = array_slice($aktuelleVisitors, 0, 3);

if ($visitorCount > 0): // Überprüfen, ob Besucher vorhanden sind
?>

<style>

.icon-button {
    display: inline-block;
    width: 25px; /* Feste Breite */
    height: 25px; /* Feste Höhe */
    line-height: 25px; /* Vertikale Zentrierung */
    text-align: center; /* Horizontale Zentrierung */
    border-radius: 5px; /* Optionale abgerundete Ecken */
}

.required {
    content: " *";
    color: green;
}

</style>

<div class="panel panel-info" id="panel-visitors">
    <?= PanelMenu::widget(['id' => 'panel-visitors']) ?>
    <div class="panel-heading">
        
        <?= Html::a(Yii::t('VisitorModule.base', 'Add'), '#', [
            'class' => 'btn btn-success',
            'title' => Yii::t('VisitorModule.base', 'Create Visitor'),
            'data-toggle' => 'modal',
            'data-target' => '#createModal',
        ]) ?>

    </div>
    <div class="panel-body">
            <?= Yii::t('VisitorModule.base', 'Visitors expected') ?> (<?= Html::encode($visitorCount) ?>)
        <ul>
        <br>
            <?php foreach ($limitedVisitors as $visitor): ?>
                <li>
                    <strong><?php
                        // $datum = strtotime(Html::encode($visitor['start']));
                        // echo date('d.m.Y H:i', $datum); // Datum und Startzeit anzeigen
                        echo Html::encode($visitor['anzeigeDatum']);

                    ?></strong><br>
                    <?= Yii::t('VisitorModule.base', 'Company') ?>: <strong><?= Html::encode($visitor['company']) ?></strong><br>
                    <?= Yii::t('VisitorModule.base', 'Visitors') ?>: <strong><?= Html::encode($visitor['visitors']) ?></strong><br>
                    <?= Yii::t('VisitorModule.base', 'Supervisor') ?>: <strong><?= Html::encode($visitor['supervisor']) ?></strong><br>
                    

                        <?= Html::a('<i class="fa fa-info"></i>', '#', [
                            'class' => 'btn-warning icon-button',
                            'title' => Yii::t('VisitorModule.base', 'Info'),
                            'data-toggle' => 'modal',
                            'data-target' => '#infoModal' . $visitor['id'],
                        ]) ?>

                    <?php if (Yii::$app->user->id == $visitor['user_id']): ?>
                        <?= Html::a('<i class="fa fa-pencil"></i>', '#', [
                            'class' => 'btn-primary icon-button',
                            'title' => Yii::t('VisitorModule.base', 'Edit'),
                            'data-toggle' => 'modal',
                            'data-target' => '#editModal' . $visitor['id'],
                        ]) ?>
                        <?= Html::a('<i class="fa fa-trash"></i>', '#', [
                            'class' => 'btn-danger icon-button',
                            'title' => Yii::t('VisitorModule.base', 'Delete'),
                            'data-toggle' => 'modal',
                            'data-target' => '#deleteModal' . $visitor['id'],
                        ]) ?>
                    <?php endif; ?>
                </li>
                <br>
            <?php endforeach; ?>
        </ul>
        <?php if ($visitorCount > 3): ?>
            <div style="text-align: right;">
                <a href="#" data-toggle="modal" data-target="#visitorModal"><?= Yii::t('VisitorModule.base', 'Show all') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
else:
    ?>
    <div class="panel panel-info" id="panel-visitors">
    <div class="panel-body">
        <?=Yii::t('VisitorModule.base', 'AddNew')?>        
        <?= Html::a(Yii::t('VisitorModule.base', 'Add'), '#', [
            'class' => 'btn btn-success btn-xs',
            'title' => Yii::t('VisitorModule.base', 'Create Visitor'),
            'data-toggle' => 'modal',
            'data-target' => '#createModal',
        ]) ?>
    </div>
    </div>
<?php
endif;

?>

<!-- Modal für alle Besucher -->
<div id="visitorModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="visitorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visitorModalLabel"><?= Yii::t('VisitorModule.base', 'All Visitors') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    <?php foreach ($aktuelleVisitors as $visitor): ?>
                    <li class="visitor-item">
                    <strong><?php
                            $datum = strtotime(Html::encode($visitor['start']));
                            echo date('d.m.Y H:i', $datum);
                        ?></strong><br>
                        <div style="padding-left: 20px;">
                            <?= Yii::t('VisitorModule.base', 'Company') ?>: <strong><?= Html::encode($visitor['company']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Visitors') ?>: <strong><?= Html::encode($visitor['visitors']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Supervisor') ?>: <strong><?= Html::encode($visitor['supervisor']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Start') ?>: <strong><?= Html::encode($visitor['start']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'End') ?>: <strong><?= Html::encode($visitor['end']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Location') ?>: <strong><?= Html::encode($visitor['location']) ?></strong><br>

                            <?php if (!empty($visitor['remarks'])): ?>                                
                                <?= Yii::t('VisitorModule.base', 'Remarks') ?>: <strong><?= Html::encode($visitor['remarks']) ?></strong><br>
                            <?php endif; ?>

                            <?php if (Yii::$app->user->id == $visitor['user_id']): ?>
                                <?= Html::a('<i class="fa fa-pencil"></i>', '#', [
                                    'class' => 'btn-primary icon-button',
                                    'title' => Yii::t('VisitorModule.base', 'Edit'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#editModal' . $visitor['id'],
                                ]) ?>
                                <?= Html::a('<i class="fa fa-trash"></i>', '#', [
                                    'class' => 'btn-danger icon-button',
                                    'title' => Yii::t('VisitorModule.base', 'Delete'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#deleteModal' . $visitor['id'],
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </li>
                    <br>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Yii::t('VisitorModule.base', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Sicherheitsabfrage Modal für Löschen -->
<?php foreach ($aktuelleVisitors as $visitor): ?>
<div class="modal fade" id="deleteModal<?= $visitor['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $visitor['id'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?= $visitor['id'] ?>"><?= Yii::t('VisitorModule.base', 'Confirm Deletion') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= Yii::t('VisitorModule.base', 'Are you sure you want to delete this visitor?') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Yii::t('VisitorModule.base', 'Cancel') ?></button>
                <?= Html::a(Yii::t('VisitorModule.base', 'Delete'), ['/visitor/create/delete', 'id' => $visitor['id']], [
                    'class' => 'btn btn-danger',
                    'data-method' => 'post',
                ]) ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>


<?php foreach ($aktuelleVisitors as $visitor): ?>
<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $visitor['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $visitor['id'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $visitor['id'] ?>"><?= Yii::t('VisitorModule.base', 'Edit Visitor') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= Html::beginForm(['/visitor/create/update', 'id' => $visitor['id']], 'post') ?>
                
                <?= Html::label(Yii::t('VisitorModule.base', 'Company'), 'company') ?>
                <?= Html::input('text', 'Visitor[company]', $visitor['company'], ['class' => 'form-control']) ?><br>
                
                <?= Html::label(Yii::t('VisitorModule.base', 'Visitors'), 'visitors') ?>
                <?= Html::input('text', 'Visitor[visitors]', $visitor['visitors'], ['class' => 'form-control']) ?><br>

                
                <!-- <div class="container"> -->
                    <!-- <div class="row"> -->
                        <!-- <div class="col-md-6">    -->
                            <?= Html::label(Yii::t('VisitorModule.base', 'Start'), 'Start') ?>
                            <?= Html::input('datetime-local', 'Visitor[start]', $visitor['start'], ['class' => 'form-control']) ?><br>
                        <!-- </div> -->
                        <!-- <div class="col-md-6"> -->
                            <?= Html::label(Yii::t('VisitorModule.base', 'End'), 'End') ?>
                            <?= Html::input('datetime-local', 'Visitor[end]', $visitor['end'], ['class' => 'form-control']) ?><br>
                        <!-- </div> -->
                    <!-- </div> -->
                <!-- </div> -->

                
                <?= Html::label(Yii::t('VisitorModule.base', 'Supervisor'), 'supervisor') ?>
                <?= Html::input('text', 'Visitor[supervisor]', $visitor['supervisor'], ['class' => 'form-control']) ?><br>

                <?= Html::label(Yii::t('VisitorModule.base', 'Country'), 'country') ?>
                <?= Html::input('text', 'Visitor[country]', $visitor['country'], ['class' => 'form-control']) ?><br>

                <?= Html::label(Yii::t('VisitorModule.base', 'Location'), 'location') ?>
                <?= Html::input('text', 'Visitor[location]', $visitor['location'], ['class' => 'form-control']) ?><br>                
                <!-- Weitere Felder je nach Bedarf -->
               
                <div class="modal-footer">
                    <?= Html::submitButton(Yii::t('VisitorModule.base', 'Save Changes'), ['class' => 'btn btn-primary']) ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Yii::t('VisitorModule.base', 'Cancel') ?></button>
                </div>
                
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php foreach ($aktuelleVisitors as $visitor): ?>
<!-- info Modal -->
<div class="modal fade" id="infoModal<?= $visitor['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel<?= $visitor['id'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel<?= $visitor['id'] ?>"><?= Yii::t('VisitorModule.base', 'Show Visitor') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <ul>
                    <li class="visitor-item">
                    <strong><?php

                            $datum = strtotime(Html::encode($visitor['start']));
                            echo date('d.m.Y H:i', $datum);
                        ?></strong><br>
                        <div style="padding-left: 20px;">
                            <?= Yii::t('VisitorModule.base', 'Company') ?>: <strong><?= Html::encode($visitor['company']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Visitors') ?>: <strong><?= Html::encode($visitor['visitors']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Supervisor') ?>: <strong><?= Html::encode($visitor['supervisor']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Start') ?>: <strong><?= Html::encode($visitor['start']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'End') ?>: <strong><?= Html::encode($visitor['end']) ?></strong><br>
                            <?= Yii::t('VisitorModule.base', 'Location') ?>: <strong><?= Html::encode($visitor['location']) ?></strong><br>

                            <?php if (!empty($visitor['remarks'])): ?>                                
                                <?= Yii::t('VisitorModule.base', 'Remarks') ?>: <strong><?= Html::encode($visitor['remarks']) ?></strong><br>
                            <?php endif; ?>
                        </div>
                    </li>
                    <br>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>


<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel"><?= Yii::t('VisitorModule.base', 'Create Visitor') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= Html::beginForm(['/visitor/create/modal'], 'post') ?>
                
                

                <?= Html::label(Yii::t('VisitorModule.base', 'Company') . ' <span class="required">*</span>', 'company', ['class' => '']) ?>
                <?= Html::input('text', 'Visitor[company]', '', ['class' => 'form-control']) ?><br>

                <?= Html::label(Yii::t('VisitorModule.base', 'Country') , 'country') ?>
                <?= Html::input('text', 'Visitor[country]', '', ['class' => 'form-control']) ?><br>

                <?= Html::label(Yii::t('VisitorModule.base', 'Visitors') . ' <span class="required">*</span>', 'visitors', ['class' => '']) ?>
                <?= Html::textarea('Visitor[visitors]', '', ['class' => 'form-control', 'rows' => 3]) ?><br>


                    <div class="row">
                        <div class="col-md-6">    
                        <?= Html::label(Yii::t('VisitorModule.base', 'Supervisor') . ' <span class="required">*</span>', 'supervisor', ['class' => '']) ?>
                        <?= Html::input('text', 'Visitor[supervisor]', '', ['class' => 'form-control']) ?><br>
                        </div>

                        <div class="col-md-6">
                            <?= Html::label(Yii::t('VisitorModule.base', 'Location') . ' <span class="required">*</span>', 'location', ['class' => '']) ?>
                            <?= Html::input('text', 'Visitor[location]', '', ['class' => 'form-control']) ?><br>  
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= Html::label(Yii::t('VisitorModule.base', 'Start') . ' <span class="required">*</span>', 'start', ['class' => '']) ?>
                            <?= Html::input('datetime-local', 'Visitor[start]', '', ['class' => 'form-control']) ?><br>
                        </div>
                        <div class="col-md-6">
                            <?= Html::label(Yii::t('VisitorModule.base', 'End'), 'End') ?>
                            <?= Html::input('datetime-local', 'Visitor[end]', '', ['class' => 'form-control']) ?><br>
                        </div>
                    </div>

                <?= Html::label(Yii::t('VisitorModule.base', 'Remarks'), 'remarks') ?>
                <?= Html::input('text', 'Visitor[remarks]', '', ['class' => 'form-control']) ?><br>

                <div class="modal-footer">
                    <?= Html::submitButton(Yii::t('VisitorModule.base', 'Save'), ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Yii::t('VisitorModule.base', 'Cancel') ?></button>
                </div>

                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
