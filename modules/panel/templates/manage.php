<?php require 'header.php'; ?>

<h1>Wydarzenie "<?php print $this->event['name'] ?>"</h1>

<div id="columns">
    <div id="sectors">
        <h2>Sektory</h2>
        <?php if ($this->sectors): ?>
            <?php foreach ($this->sectors as $sector): ?>
                <div>
                    <?php print $sector['name'] . " (" . $sector['available'] . ")" ?>
                </div>
            <?php endforeach; ?>
        <?php endif ?>
    </div>
</div>
<?php
require 'footer.php';
