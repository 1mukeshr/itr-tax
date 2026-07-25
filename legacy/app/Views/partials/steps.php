<?php
use App\Core\Helper;
$steps = Helper::filingSteps($filing);
$current = Helper::currentStepIndex($filing);
?>
<div class="itr-steps">
<?php foreach ($steps as $i => $s): ?>
    <div class="itr-step-item <?= $i < $current ? 'itr-done' : ($i === $current ? 'itr-active' : '') ?>">
        <div class="n"><?= $i < $current ? Helper::icon('check') : ($i + 1) ?></div>
        <div class="l"><?= Helper::e($s['label']) ?></div>
    </div>
    <?php if ($i < count($steps) - 1): ?><div class="itr-step-line <?= $i < $current ? 'itr-done' : '' ?>"></div><?php endif; ?>
<?php endforeach; ?>
</div>
