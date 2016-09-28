<?php require 'header.php'; ?>
<h1>Wydarzenia</h1>
<div class="func">
    <a href="<?php print $this->getUri()?>newEvent">[Dodaj wydarzenie]</a>
</div>

<?php if ($this->events):?>
    <table>
        <tr>
            <th>Identyfikator</th>
            <th>Nazwa</th>
            <th>URL</th>
            <th>Funkcje</th>
        </tr>
    <?php foreach($this->events as $eventArray):?>
        <?php foreach ($eventArray as $index => $event):?>
            <tr>
                <td><?php print $event->getIdentifier()?></td>
                <td><?php print $event->getName()?></td>
                <td><?php print $event->getUrl()?></td>
                <td><a href="<?php print $this->getUri() . 'manage/' . $event->getIdentifier() . '/' . $index?>">[ZARZĄDZAJ]</a></td>
            </tr>
        <?php endforeach;?>
    <?php endforeach;?>
    </table>
<?php else:?>
    Brak zdefiniowanych wydarzeń
<?php endif;?>



    
<?php require 'footer.php';