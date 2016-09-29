<?php require 'header.php'; ?>
<h1>Nowe wydarzenie</h1>
<?php if (!$this->event):?>
    <form action="<?php print $this->getUri()?>saveNewEvent" method="post">
<?php else:?>
    <form action="<?php print MODULE_URI ?>saveEditedEvent/<?php print $this->event->getIdentifier(true) . '/' . $this->index?>" method="post">
<?php endif?>
        <div>
            <label>Nazwa wydarzenia: <input type="text" value="<?php print $this->event ? $this->event->getName() : ''?>" name="name" /></label>
        </div>
        <div>
            <label>URL wydarzenia: <input type="text" name="url" value="<?php print $this->event ? $this->event->getUrl() : ''?>" /></label>
        </div>
        <div>
            <input type="submit" value="Zapisz" name="save" />
            <input type="submit" value="Powrót" name="back" />
        </div>
</form>

    
<?php require 'footer.php';