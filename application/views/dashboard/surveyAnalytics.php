<h2>Survey Analytics Dashboard</h2>

<p><strong>Survey ID:</strong> <?= (int)$surveyid ?></p>
<p><strong>Total Responses:</strong> <?= (int)$totalResponses ?></p>

<h3>Final Risk Distribution</h3>

<ul>
<?php foreach ($finalBuckets as $row): ?>
    <li>
        <?= CHtml::encode($row['label']) ?>
        → <?= (int)$row['total'] ?>
    </li>
<?php endforeach; ?>
</ul>
