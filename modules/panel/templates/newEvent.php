<?php require 'header.php'; ?>
<h1>Nowe wydarzenie</h1>

<form action="<?php print $this->getUri()?>saveEvent" method="post">
    <div>
        <label>Nazwa wydarzenia: <input type="text" name="name" /></label>
    </div>
    <div>
        <label>URL wydarzenia: <input type="text" name="url" /></label>
    </div>
    <div>
        <input type="submit" value="Zapisz" name="save" />
        <input type="submit" value="Powrót" name="back" />
    </div>
</form>

    
<?php require 'footer.php';