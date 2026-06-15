<?php

use humhub\widgets\PanelMenu;
use yii\helpers\Html;
use yii\helpers\Url;

// Hilfsfunktion: Datum für input type="datetime-local" formatieren
$formatDateTimeLocal = function ($value) {
    if (empty($value)) {
        return '';
    }

    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    if (!$dt) {
        return '';
    }

    return $dt->format('Y-m-d\TH:i');
};

// Besucher nach Datum und Startzeit sortieren
usort($visitors, function ($a, $b) {
    $dateA = strtotime($a['start'] ?? '');
    $dateB = strtotime($b['start'] ?? '');
    return $dateA <=> $dateB;
});

// Aktuelle/zukünftige Einträge filtern
$heute = new DateTime();
$heute->setTime(0, 1, 0); // 00:01:00

$aktuelleVisitors = array_filter($visitors, function ($visitor) use ($heute) {
    try {
        $startRaw = $visitor['start'] ?? null;
        $endRaw   = $visitor['end'] ?? null;

        $startDatum = $startRaw ? DateTime::createFromFormat('Y-m-d H:i:s', $startRaw) : null;
        $endDatum   = $endRaw ? DateTime::createFromFormat('Y-m-d H:i:s', $endRaw) : null;

        if (!$startDatum) {
            throw new Exception('Ungültiges Start-Datum: ' . ($startRaw ?? 'NULL'));
        }

        $startTimestamp = $startDatum->getTimestamp();
        $endTimestamp   = $endDatum ? $endDatum->getTimestamp() : null;
        $heuteTimestamp = $heute->getTimestamp();

        return ($startTimestamp >= $heuteTimestamp) || ($endTimestamp !== null && $endTimestamp >= $heuteTimestamp);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
});

// Anzeige-Datum setzen
$aktuelleVisitors = array_map(function ($visitor) {
    $startRaw = $visitor['start'] ?? null;
    $endRaw   = $visitor['end'] ?? null;

    $startDatum = $startRaw ? DateTime::createFromFormat('Y-m-d H:i:s', $startRaw) : null;
    $endDatum   = $endRaw ? DateTime::createFromFormat('Y-m-d H:i:s', $endRaw) : null;

    if ($startDatum && $endDatum) {
        if ($startDatum->format('Y-m-d') === $endDatum->format('Y-m-d')) {
            $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i');
        } else {
            $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i') . ' - ' . $endDatum->format('d.m.Y H:i');
        }
    } elseif ($startDatum) {
        $visitor['anzeigeDatum'] = $startDatum->format('d.m.Y H:i');
    } else {
        $visitor['anzeigeDatum'] = Yii::t('VisitorModule.base', 'Unknown');
    }

    return $visitor;
}, $aktuelleVisitors);

// Anzahl der Besucher
$visitorCount = count($aktuelleVisitors);

// Maximal 3 Einträge anzeigen
$limitedVisitors = array_slice($aktuelleVisitors, 0, 3);
?>

<style>
.icon-button {
    display: inline-flex;
    width: 28px;
    height: 28px;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    padding: 0;
    margin-right: 4px;
}

.required {
    color: green;
    font-weight: 600;
}
</style>

<?php if ($visitorCount > 0): ?>

    <div class="panel panel-info" id="panel-visitors">
        <?= PanelMenu::widget(['id' => 'panel-visitors']) ?>

        <div class="panel-heading d-flex justify-content-between align-items-center">
            <div>
                <strong><?= Yii::t('VisitorModule.base', 'Visitors expected') ?></strong>
                (<?= Html::encode($visitorCount) ?>)
            </div>

            <?= Html::button(Yii::t('VisitorModule.base', 'Add'), [
                'class' => 'btn btn-success btn-sm',
                'title' => Yii::t('VisitorModule.base', 'Create Visitor'),
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#createModal',
            ]) ?>
        </div>

        <div class="panel-body">
            <ul class="list-unstyled mb-0">
                <?php foreach ($limitedVisitors as $visitor): ?>
                    <li class="mb-3">
                        <strong><?= Html::encode($visitor['anzeigeDatum'] ?? '') ?></strong><br>
                        <?= Yii::t('VisitorModule.base', 'Company') ?>:
                        <strong><?= Html::encode($visitor['company'] ?? '') ?></strong><br>
                        <?= Yii::t('VisitorModule.base', 'Visitors') ?>:
                        <strong><?= Html::encode($visitor['visitors'] ?? '') ?></strong><br>
                        <?= Yii::t('VisitorModule.base', 'Supervisor') ?>:
                        <strong><?= Html::encode($visitor['supervisor'] ?? '') ?></strong><br>

                        <div class="mt-2">
                            <?= Html::a('<i class="fa fa-info"></i>', '#', [
                                'class' => 'btn btn-warning btn-sm icon-button',
                                'title' => Yii::t('VisitorModule.base', 'Info'),
                                'data-bs-toggle' => 'modal',
                                'data-bs-target' => '#infoModal' . $visitor['id'],
                                'aria-label' => Yii::t('VisitorModule.base', 'Info'),
                            ]) ?>

                            <?php if (Yii::$app->user->id == ($visitor['user_id'] ?? null)): ?>
                                <?= Html::a('<i class="fa fa-pencil"></i>', '#', [
                                    'class' => 'btn btn-primary btn-sm icon-button',
                                    'title' => Yii::t('VisitorModule.base', 'Edit'),
                                    'data-bs-toggle' => 'modal',
                                    'data-bs-target' => '#editModal' . $visitor['id'],
                                    'aria-label' => Yii::t('VisitorModule.base', 'Edit'),
                                ]) ?>

                                <?= Html::a('<i class="fa fa-trash"></i>', '#', [
                                    'class' => 'btn btn-danger btn-sm icon-button',
                                    'title' => Yii::t('VisitorModule.base', 'Delete'),
                                    'data-bs-toggle' => 'modal',
                                    'data-bs-target' => '#deleteModal' . $visitor['id'],
                                    'aria-label' => Yii::t('VisitorModule.base', 'Delete'),
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($visitorCount > 3): ?>
                <div class="text-end mt-2">
                    <?= Html::button(Yii::t('VisitorModule.base', 'Show all'), [
                        'class' => 'btn btn-link p-0',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#visitorModal',
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div class="panel panel-info" id="panel-visitors">
        <div class="panel-body">
            <div class="mb-2">
                <?= Yii::t('VisitorModule.base', 'AddNew') ?>
            </div>

            <?= Html::button(Yii::t('VisitorModule.base', 'Add'), [
                'class' => 'btn btn-success btn-sm',
                'title' => Yii::t('VisitorModule.base', 'Create Visitor'),
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#createModal',
            ]) ?>
        </div>
    </div>

<?php endif; ?>

<!-- Modal für alle Besucher -->
<div id="visitorModal" class="modal fade" tabindex="-1" aria-labelledby="visitorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visitorModalLabel">
                    <?= Yii::t('VisitorModule.base', 'All Visitors') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('VisitorModule.base', 'Close') ?>"></button>
            </div>

            <div class="modal-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach ($aktuelleVisitors as $visitor): ?>
                        <li class="visitor-item mb-3">
                            <strong>
                                <?php
                                $startRaw = $visitor['start'] ?? '';
                                $datum = $startRaw ? strtotime($startRaw) : false;
                                echo $datum ? date('d.m.Y H:i', $datum) : Html::encode(Yii::t('VisitorModule.base', 'Unknown'));
                                ?>
                            </strong><br>

                            <div class="ps-3">
                                <?= Yii::t('VisitorModule.base', 'Company') ?>:
                                <strong><?= Html::encode($visitor['company'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Visitors') ?>:
                                <strong><?= Html::encode($visitor['visitors'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Supervisor') ?>:
                                <strong><?= Html::encode($visitor['supervisor'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Start') ?>:
                                <strong><?= Html::encode($visitor['start'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'End') ?>:
                                <strong><?= Html::encode($visitor['end'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Location') ?>:
                                <strong><?= Html::encode($visitor['location'] ?? '') ?></strong><br>

                                <?php if (!empty($visitor['remarks'])): ?>
                                    <?= Yii::t('VisitorModule.base', 'Remarks') ?>:
                                    <strong><?= Html::encode($visitor['remarks']) ?></strong><br>
                                <?php endif; ?>

                                <?php if (Yii::$app->user->id == ($visitor['user_id'] ?? null)): ?>
                                    <?= Html::a('<i class="fa fa-pencil"></i>', '#', [
                                        'class' => 'btn btn-primary btn-sm icon-button',
                                        'title' => Yii::t('VisitorModule.base', 'Edit'),
                                        'data-bs-toggle' => 'modal',
                                        'data-bs-target' => '#editModal' . $visitor['id'],
                                        'aria-label' => Yii::t('VisitorModule.base', 'Edit'),
                                    ]) ?>

                                    <?= Html::a('<i class="fa fa-trash"></i>', '#', [
                                        'class' => 'btn btn-danger btn-sm icon-button',
                                        'title' => Yii::t('VisitorModule.base', 'Delete'),
                                        'data-bs-toggle' => 'modal',
                                        'data-bs-target' => '#deleteModal' . $visitor['id'],
                                        'aria-label' => Yii::t('VisitorModule.base', 'Delete'),
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= Yii::t('VisitorModule.base', 'Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sicherheitsabfrage Modal für Löschen -->
<?php foreach ($aktuelleVisitors as $visitor): ?>
    <div class="modal fade" id="deleteModal<?= Html::encode($visitor['id']) ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= Html::encode($visitor['id']) ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?= Html::encode($visitor['id']) ?>">
                        <?= Yii::t('VisitorModule.base', 'Confirm Deletion') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('VisitorModule.base', 'Close') ?>"></button>
                </div>

                <div class="modal-body">
                    <?= Yii::t('VisitorModule.base', 'Are you sure you want to delete this visitor?') ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= Yii::t('VisitorModule.base', 'Cancel') ?>
                    </button>

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
    <div class="modal fade" id="editModal<?= Html::encode($visitor['id']) ?>" tabindex="-1" aria-labelledby="editModalLabel<?= Html::encode($visitor['id']) ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= Html::encode($visitor['id']) ?>">
                        <?= Yii::t('VisitorModule.base', 'Edit Visitor') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('VisitorModule.base', 'Close') ?>"></button>
                </div>

                <div class="modal-body">
                    <?= Html::beginForm(['/visitor/create/update', 'id' => $visitor['id']], 'post') ?>

                    <div class="mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Company'), 'company', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[company]', $visitor['company'] ?? '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Visitors'), 'visitors', ['class' => 'form-label']) ?>
                        <?= Html::textarea('Visitor[visitors]', $visitor['visitors'] ?? '', ['class' => 'form-control', 'rows' => 3]) ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= Html::label(Yii::t('VisitorModule.base', 'Start'), 'start', ['class' => 'form-label']) ?>
                            <?= Html::input('datetime-local', 'Visitor[start]', $formatDateTimeLocal($visitor['start'] ?? ''), ['class' => 'form-control']) ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <?= Html::label(Yii::t('VisitorModule.base', 'End'), 'end', ['class' => 'form-label']) ?>
                            <?= Html::input('datetime-local', 'Visitor[end]', $formatDateTimeLocal($visitor['end'] ?? ''), ['class' => 'form-control']) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Supervisor'), 'supervisor', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[supervisor]', $visitor['supervisor'] ?? '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Country'), 'country', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[country]', $visitor['country'] ?? '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Location'), 'location', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[location]', $visitor['location'] ?? '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <?= Html::submitButton(Yii::t('VisitorModule.base', 'Save Changes'), ['class' => 'btn btn-primary']) ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <?= Yii::t('VisitorModule.base', 'Cancel') ?>
                        </button>
                    </div>

                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($aktuelleVisitors as $visitor): ?>
    <!-- Info Modal -->
    <div class="modal fade" id="infoModal<?= Html::encode($visitor['id']) ?>" tabindex="-1" aria-labelledby="infoModalLabel<?= Html::encode($visitor['id']) ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel<?= Html::encode($visitor['id']) ?>">
                        <?= Yii::t('VisitorModule.base', 'Show Visitor') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('VisitorModule.base', 'Close') ?>"></button>
                </div>

                <div class="modal-body">
                    <ul class="list-unstyled mb-0">
                        <li class="visitor-item">
                            <strong>
                                <?php
                                $startRaw = $visitor['start'] ?? '';
                                $datum = $startRaw ? strtotime($startRaw) : false;
                                echo $datum ? date('d.m.Y H:i', $datum) : Html::encode(Yii::t('VisitorModule.base', 'Unknown'));
                                ?>
                            </strong><br>

                            <div class="ps-3">
                                <?= Yii::t('VisitorModule.base', 'Company') ?>:
                                <strong><?= Html::encode($visitor['company'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Visitors') ?>:
                                <strong><?= Html::encode($visitor['visitors'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Supervisor') ?>:
                                <strong><?= Html::encode($visitor['supervisor'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Start') ?>:
                                <strong><?= Html::encode($visitor['start'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'End') ?>:
                                <strong><?= Html::encode($visitor['end'] ?? '') ?></strong><br>

                                <?= Yii::t('VisitorModule.base', 'Location') ?>:
                                <strong><?= Html::encode($visitor['location'] ?? '') ?></strong><br>

                                <?php if (!empty($visitor['remarks'])): ?>
                                    <?= Yii::t('VisitorModule.base', 'Remarks') ?>:
                                    <strong><?= Html::encode($visitor['remarks']) ?></strong><br>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <?= Yii::t('VisitorModule.base', 'Create Visitor') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Yii::t('VisitorModule.base', 'Close') ?>"></button>
            </div>

            <div class="modal-body">
                <?= Html::beginForm(['/visitor/create/modal'], 'post') ?>

                <div class="mb-3">
                    <?= Html::label(Yii::t('VisitorModule.base', 'Company') . ' <span class="required">*</span>', 'company', ['class' => 'form-label']) ?>
                    <?= Html::input('text', 'Visitor[company]', '', ['class' => 'form-control']) ?>
                </div>

                <div class="mb-3">
                    <?= Html::label(Yii::t('VisitorModule.base', 'Country'), 'country', ['class' => 'form-label']) ?>
                    <?= Html::input('text', 'Visitor[country]', '', ['class' => 'form-control']) ?>
                </div>

                <div class="mb-3">
                    <?= Html::label(Yii::t('VisitorModule.base', 'Visitors') . ' <span class="required">*</span>', 'visitors', ['class' => 'form-label']) ?>
                    <?= Html::textarea('Visitor[visitors]', '', ['class' => 'form-control', 'rows' => 3]) ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Supervisor') . ' <span class="required">*</span>', 'supervisor', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[supervisor]', '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Location') . ' <span class="required">*</span>', 'location', ['class' => 'form-label']) ?>
                        <?= Html::input('text', 'Visitor[location]', '', ['class' => 'form-control']) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'Start') . ' <span class="required">*</span>', 'start', ['class' => 'form-label']) ?>
                        <?= Html::input('datetime-local', 'Visitor[start]', '', ['class' => 'form-control']) ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <?= Html::label(Yii::t('VisitorModule.base', 'End'), 'end', ['class' => 'form-label']) ?>
                        <?= Html::input('datetime-local', 'Visitor[end]', '', ['class' => 'form-control']) ?>
                    </div>
                </div>

                <div class="mb-3">
                    <?= Html::label(Yii::t('VisitorModule.base', 'Remarks'), 'remarks', ['class' => 'form-label']) ?>
                    <?= Html::input('text', 'Visitor[remarks]', '', ['class' => 'form-control']) ?>
                </div>

                <div class="modal-footer px-0 pb-0">
                    <?= Html::submitButton(Yii::t('VisitorModule.base', 'Save'), ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= Yii::t('VisitorModule.base', 'Cancel') ?>
                    </button>
                </div>

                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>