<?php require 'header.php'; ?>

<h1>Wydarzenie "<?php print $this->event->getName() ?>"</h1>
<form action="<?php print MODULE_URI ?>saveEvent/<?php print $this->identifier . '/' . $this->index?>" method="post">
    <div id="columns">
        <div id="sectors">
            <h2>Sektory</h2>
            <?php if ($this->sectors): ?>
                <?php foreach ($this->sectors as $sector): ?>
                    <div>
                        <label><input type="checkbox" name="sectors[]" value="<?php print $sector['name'] ?>" /><?php print $sector['name'] . " (" . $sector['available'] . ")" ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
        </div>
    </div>
</form>
<?php
require 'footer.php';
